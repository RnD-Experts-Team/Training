<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * This is an invite-only internal tool and new accounts default to the
     * `manager` role, so a public /register would let anyone provision a
     * privileged account. Super admins create users from the Management page.
     */
    public function test_self_registration_is_disabled(): void
    {
        $this->assertFalse(Features::enabled(Features::registration()));

        $this->post('/register', [
            'name' => 'Uninvited User',
            'email' => 'uninvited@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'uninvited@example.com']);
    }
}
