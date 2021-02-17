<?php

namespace Tests\Unit;

use App\Models\User;
use App\Enums\Gender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function itCastsGenderToEnum()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(Gender::class, $user->gender);
    }
}
