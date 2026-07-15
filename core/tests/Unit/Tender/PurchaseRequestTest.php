<?php

namespace Tests\Unit\Tender;

use App\Http\Requests\Tender\PurchaseRequest;
use App\TenderPurchase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Locks the checkout validation contract: agreement required, receipt MIME
 * enforced, buyer fields length-bounded. DB-backed rules (exists) are covered
 * by the checkout flow, not here.
 */
class PurchaseRequestTest extends TestCase
{
    private function rules(): array
    {
        return (new PurchaseRequest())->rules();
    }

    private function passes(string $field, $value): bool
    {
        return Validator::make([$field => $value], [$field => $this->rules()[$field]])->passes();
    }

    /** @test */
    public function receipt_rejects_a_script_disguised_as_an_image()
    {
        $path = sys_get_temp_dir() . '/pr_evil.jpg';
        file_put_contents($path, '<?php system($_GET[0]); ?>');
        $evil = new UploadedFile($path, 'evil.jpg', null, null, true);

        $this->assertFalse($this->passes('receipt', $evil));
    }

    /** @test */
    public function receipt_accepts_a_real_image()
    {
        $path = sys_get_temp_dir() . '/pr_real.png';
        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));
        $png = new UploadedFile($path, 'real.png', null, null, true);

        $this->assertTrue($this->passes('receipt', $png));
    }

    /** @test */
    public function agreement_must_be_accepted()
    {
        $this->assertFalse($this->passes('agree_terms', '0'));
        $this->assertTrue($this->passes('agree_terms', 'on'));
    }

    /** @test */
    public function company_name_is_bounded_to_its_column()
    {
        $this->assertTrue($this->passes('company_name', str_repeat('a', 200)));
        $this->assertFalse($this->passes('company_name', str_repeat('a', 201)));
    }

    /** @test */
    public function registration_number_rejects_spaces_and_special_characters()
    {
        $this->assertTrue($this->passes('company_registration_no', 'AB12CD34'));
        $this->assertTrue($this->passes('company_registration_no', '12345'));
        $this->assertFalse($this->passes('company_registration_no', 'AB 12'));
        $this->assertFalse($this->passes('company_registration_no', 'AB-12'));
        $this->assertFalse($this->passes('company_registration_no', 'ab12')); // lowercase not yet uppercased
        $this->assertFalse($this->passes('company_registration_no', ''));
    }

    /** @test */
    public function registration_number_is_canonicalised_to_uppercase_alphanumeric()
    {
        $this->assertSame('AB12', TenderPurchase::normalizeRegNo('ab-12 '));
        $this->assertSame('AB12', TenderPurchase::normalizeRegNo(' A B 1 2 '));
        $this->assertSame('', TenderPurchase::normalizeRegNo(null));
    }

    /** @test */
    public function country_must_come_from_the_canonical_list()
    {
        $this->assertTrue($this->passes('country', 'Bangladesh'));
        $this->assertFalse($this->passes('country', 'Freetext Land'));
        $this->assertFalse($this->passes('country', ''));
    }

    /** @test */
    public function phone_code_must_be_a_known_dialling_code()
    {
        $this->assertTrue($this->passes('phone_code', '+880'));
        $this->assertFalse($this->passes('phone_code', '+999'));
        $this->assertFalse($this->passes('phone_code', '880'));
    }

    /** @test */
    public function phone_number_is_digits_only_and_length_bounded()
    {
        $this->assertTrue($this->passes('phone_number', '1712345678'));
        $this->assertFalse($this->passes('phone_number', '+8801712345678')); // code belongs in phone_code
        $this->assertFalse($this->passes('phone_number', '123'));            // too short
        $this->assertFalse($this->passes('phone_number', '123456789012345')); // too long
    }

    /** @test */
    public function selected_module_ids_must_be_integers()
    {
        $rules = [
            'selected_module_ids'   => $this->rules()['selected_module_ids'],
            'selected_module_ids.*' => $this->rules()['selected_module_ids.*'],
        ];

        $this->assertTrue(Validator::make(['selected_module_ids' => [1, 2, 3]], $rules)->passes());
        $this->assertFalse(Validator::make(['selected_module_ids' => ['x']], $rules)->passes());
    }
}
