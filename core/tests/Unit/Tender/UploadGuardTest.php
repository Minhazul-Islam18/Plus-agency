<?php

namespace Tests\Unit\Tender;

use App\Http\Controllers\Admin\TenderModuleController;
use Illuminate\Http\Request;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

/**
 * Locks in the tender-module upload guard: only whitelisted document types from
 * this host may be copied into the public assets directory. Regression net for
 * the RCE (executable extension) and SSRF (stream wrapper / off-site URL) fixes.
 */
class UploadGuardTest extends TestCase
{
    private ReflectionMethod $method;

    protected function setUp(): void
    {
        parent::setUp();

        // safeUploadFilename() compares against the current request host.
        $this->app->instance('request', Request::create('http://localhost/', 'GET'));

        $this->method = new ReflectionMethod(TenderModuleController::class, 'safeUploadFilename');
        $this->method->setAccessible(true);
    }

    private function resolve(string $ref): string
    {
        return $this->method->invoke(new TenderModuleController(), $ref);
    }

    /** @test */
    public function accepts_a_local_pdf_and_decodes_the_filename()
    {
        $this->assertSame(
            'report final.pdf',
            $this->resolve('http://localhost/assets/front/files/x/report%20final.pdf')
        );
    }

    /** @test */
    public function accepts_a_bare_document_reference_without_scheme_or_host()
    {
        $this->assertSame('brief.docx', $this->resolve('brief.docx'));
    }

    /** @test */
    public function extension_check_is_case_insensitive()
    {
        $this->assertSame('archive.ZIP', $this->resolve('https://localhost/assets/archive.ZIP'));
    }

    /** @test */
    public function rejects_an_executable_extension()
    {
        $this->expectException(RuntimeException::class);
        $this->resolve('http://localhost/assets/x/shell.php');
    }

    /** @test */
    public function rejects_a_file_stream_wrapper()
    {
        $this->expectException(RuntimeException::class);
        $this->resolve('file:///etc/passwd');
    }

    /** @test */
    public function rejects_a_php_stream_wrapper()
    {
        $this->expectException(RuntimeException::class);
        $this->resolve('php://filter/read=convert.base64-encode/resource=index.php');
    }

    /** @test */
    public function rejects_an_off_site_host()
    {
        $this->expectException(RuntimeException::class);
        $this->resolve('http://evil.example.com/x.pdf');
    }
}
