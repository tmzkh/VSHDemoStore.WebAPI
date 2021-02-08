<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itAuthenticatesApi()
    {
        $this->get('api/user')
            ->assertStatus(401);
    }
}
