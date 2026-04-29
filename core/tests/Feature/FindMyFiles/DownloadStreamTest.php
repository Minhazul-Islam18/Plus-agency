<?php

namespace Tests\Feature\FindMyFiles;

use App\AccessLog;

class DownloadStreamTest extends FindMyFilesTestCase
{
    private string $testFilePath;
    private string $testFileName = 'test_module_file.pdf';

    protected function setUp(): void
    {
        parent::setUp();

        $dir = '/tmp/fmf_test_modules';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $this->testFilePath = $dir . '/' . $this->testFileName;
        file_put_contents($this->testFilePath, '%PDF test content');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFilePath)) {
            @unlink($this->testFilePath);
        }
        parent::tearDown();
    }

    /** @test */
    public function missing_token_returns_403()
    {
        $response = $this->get('/find-my-files/download/stream');
        $response->assertForbidden();
    }

    /** @test */
    public function invalid_token_returns_403()
    {
        $response = $this->get(route('find_my_files.stream', ['t' => 'fake-token']));
        $response->assertForbidden();
    }

    /** @test */
    public function expired_token_returns_403()
    {
        $purchase = $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);
        ['raw' => $raw] = $this->makeActiveToken($purchase, ['status' => 'expired']);

        $response = $this->get('/find-my-files/download/stream?t=' . $raw);
        $response->assertForbidden();
    }

    /** @test */
    public function revoked_token_returns_403()
    {
        $purchase = $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);
        ['raw' => $raw] = $this->makeActiveToken($purchase, ['status' => 'revoked']);

        $response = $this->get('/find-my-files/download/stream?t=' . $raw);
        $response->assertForbidden();
    }

    /** @test */
    public function exhausted_token_returns_403()
    {
        $purchase = $this->makePurchase(['email' => 'buyer@example.com', 'order_number' => 'ORD-001']);
        ['raw' => $raw] = $this->makeActiveToken($purchase, [
            'download_count' => 3,
            'max_downloads'  => 3,
        ]);

        $response = $this->get('/find-my-files/download/stream?t=' . $raw);
        $response->assertForbidden();
    }

    /** @test */
    public function valid_token_with_no_files_returns_404()
    {
        $purchase = $this->makePurchase([
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
            'tender_id'    => 99,
        ]);
        ['raw' => $raw] = $this->makeActiveToken($purchase);

        // No TenderModule records for tender_id=99
        $response = $this->get('/find-my-files/download/stream?t=' . $raw);
        $response->assertNotFound();
    }

    /** @test */
    public function valid_token_with_files_returns_zip_download()
    {
        $purchase = $this->makePurchase([
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
            'tender_id'    => 1,
        ]);

        $this->makeTenderModule(1, $this->testFileName);

        ['raw' => $raw] = $this->makeActiveToken($purchase);

        $response = $this->get('/find-my-files/download/stream?t=' . $raw);

        $response->assertOk();
        $this->assertEquals('application/zip', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function zip_filename_includes_order_number()
    {
        $purchase = $this->makePurchase([
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
            'tender_id'    => 1,
        ]);

        $this->makeTenderModule(1, $this->testFileName);

        ['raw' => $raw] = $this->makeActiveToken($purchase);

        $response = $this->get('/find-my-files/download/stream?t=' . $raw);

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('tender_documents_ORD-001.zip', $disposition);
    }

    /** @test */
    public function valid_stream_logs_download_success()
    {
        $purchase = $this->makePurchase([
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
            'tender_id'    => 1,
        ]);

        $this->makeTenderModule(1, $this->testFileName);

        ['raw' => $raw] = $this->makeActiveToken($purchase);

        $this->get('/find-my-files/download/stream?t=' . $raw);

        $this->assertDatabaseHas('access_logs', [
            'event_type' => AccessLog::DOWNLOAD_SUCCESS,
            'order_id'   => 'ORD-001',
            'result'     => 'OK',
        ]);
    }

    /** @test */
    public function invalid_stream_logs_download_failed()
    {
        $this->get(route('find_my_files.stream', ['t' => 'bad-token']));

        $this->assertDatabaseHas('access_logs', [
            'event_type' => AccessLog::DOWNLOAD_FAILED,
            'result'     => 'TOKEN_INVALID_AT_STREAM',
        ]);
    }

    /** @test */
    public function inactive_modules_are_excluded_from_zip()
    {
        $purchase = $this->makePurchase([
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
            'tender_id'    => 1,
        ]);

        $this->makeTenderModule(1, $this->testFileName, ['name' => 'Inactive Module', 'status' => 0]);

        ['raw' => $raw] = $this->makeActiveToken($purchase);

        $response = $this->get('/find-my-files/download/stream?t=' . $raw);

        // 0 active files → 404
        $response->assertNotFound();
    }

    /** @test */
    public function stream_does_not_increment_download_count()
    {
        $purchase = $this->makePurchase([
            'email'        => 'buyer@example.com',
            'order_number' => 'ORD-001',
            'tender_id'    => 1,
        ]);

        $this->makeTenderModule(1, $this->testFileName);

        ['raw' => $raw, 'token' => $token] = $this->makeActiveToken($purchase);

        $this->get('/find-my-files/download/stream?t=' . $raw);

        // download_count must still be 0 — only download() page increments it
        $this->assertEquals(0, $token->fresh()->download_count);
    }
}
