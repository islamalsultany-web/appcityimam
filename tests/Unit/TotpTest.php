<?php

namespace Tests\Unit;

use App\Support\Totp;
use PHPUnit\Framework\TestCase;

class TotpTest extends TestCase
{
    public function test_generates_and_verifies_current_code(): void
    {
        $secret = Totp::generateSecret();
        $code = Totp::currentCode($secret);

        $this->assertTrue(Totp::verify($secret, $code));
    }

    public function test_rejects_invalid_code(): void
    {
        $secret = Totp::generateSecret();

        $this->assertFalse(Totp::verify($secret, '000000'));
    }
}
