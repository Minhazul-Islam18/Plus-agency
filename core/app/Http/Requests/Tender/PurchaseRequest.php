<?php

namespace App\Http\Requests\Tender;

use App\Http\Helpers\Countries;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a tender checkout submission.
 *
 * Buyer identity + agreement are required; the receipt (offline payment proof)
 * is MIME-checked because it is moved into the public assets directory — a
 * disguised script must never reach it. Optional buyer fields are length-bounded
 * to match their columns and to keep unvalidated input out of the database.
 */
class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public checkout endpoint. Blacklist / duplicate-payment checks that
        // need custom messaging are handled in the controller.
        return true;
    }

    /**
     * Canonicalise before validation:
     *  - registration number: trimmed + uppercased, so "ab12" is stored/matched as "AB12";
     *    any leftover disallowed character is then caught by the regex rule below.
     *  - phone number: digits only. The dialling code comes from `phone_code`, so any
     *    code the buyer re-types into the number field is stripped here.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('company_registration_no')) {
            $this->merge([
                'company_registration_no' => strtoupper(trim((string) $this->input('company_registration_no'))),
            ]);
        }

        if ($this->has('phone_number')) {
            $this->merge([
                'phone_number' => preg_replace('/\D/', '', (string) $this->input('phone_number')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'tender_id'    => 'required|exists:tenders,id',
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'email'        => 'required|email|max:150',
            'gateway'      => 'required|string|max:100',
            'agree_terms'  => 'accepted',

            // Country and dialling code must come from the canonical list — both are
            // <select>s, so anything else is a tampered or stale submission.
            'country' => ['required', Rule::in(Countries::names())],

            // Strict comparison, NOT Rule::in: "+880" and "880" are both numeric
            // strings, so the `in` rule's loose in_array() would treat them as equal
            // and accept a code with the leading "+" stripped.
            'phone_code' => ['required', function ($attribute, $value, $fail) {
                if (!in_array((string) $value, Countries::dialCodes(), true)) {
                    $fail(__('Please select a valid phone country code.'));
                }
            }],

            // National number only (digits; prepareForValidation() has stripped the rest).
            // 4–14 digits keeps code + number inside E.164's 15-digit limit.
            'phone_number' => 'required|digits_between:4,14',

            // Sole duplicate-purchase key. Uppercase alphanumeric only — no spaces
            // or special characters (prepareForValidation() has already uppercased).
            'company_registration_no' => 'required|string|max:100|regex:/^[A-Z0-9]+$/',

            // Payment proof — the mimes rule checks the real MIME type, so a
            // script renamed to .jpg is rejected before it is stored publicly.
            'receipt'      => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',

            // Optional buyer / order fields, bounded to their storage columns.
            'city'                  => 'nullable|string|max:100',
            'company_name'          => 'nullable|string|max:200',
            'company_address'       => 'nullable|string|max:1000',
            'payment_reference'     => 'nullable|string|max:100',
            'gateway_type'          => 'nullable|string|max:50',
            'selected_module_ids'   => 'nullable|array',
            'selected_module_ids.*' => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
            'company_registration_no.required' => __('Company Registration No. is required.'),
            'company_registration_no.regex'    => __('Company Registration No. may contain only letters and numbers — no spaces or special characters.'),
            'country.in'                       => __('Please select a country from the list.'),
            'phone_code.required'              => __('Please select a phone country code.'),
            'phone_number.digits_between'      => __('Enter a valid phone number (4–14 digits, without the country code).'),
        ];
    }
}
