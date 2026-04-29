<?php

namespace Tests\Feature\FindMyFiles;

class StaticPagesTest extends FindMyFilesTestCase
{
    /** @test */
    public function find_my_files_index_loads()
    {
        $this->get('/find-my-files')
            ->assertOk()
            ->assertSee('Secure File Recovery');
    }

    /** @test */
    public function security_info_page_loads()
    {
        $this->get('/find-my-files/security-verification')
            ->assertOk()
            ->assertSee('Server-side Verification');
    }

    /** @test */
    public function link_sent_page_loads()
    {
        $this->get('/find-my-files/link-sent')
            ->assertOk()
            ->assertSee('Request Received');
    }

    /** @test */
    public function download_page_without_token_shows_error()
    {
        $this->get('/find-my-files/download')
            ->assertOk()
            ->assertSee('Link Invalid or Expired');
    }
}
