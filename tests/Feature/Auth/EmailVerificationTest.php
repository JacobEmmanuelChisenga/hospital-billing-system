<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Email verification is not used — staff accounts are created by administrators.
 */
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_is_not_available(): void
    {
        $this->get('/verify-email')->assertNotFound();
    }
}
