<?php

namespace Tests\Unit;

use App\Support\SecureUploadValidator;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SecureUploadValidatorTest extends TestCase
{
    public function test_accepts_valid_pdf_signature(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($path, '%PDF-1.4 test');

        $file = new UploadedFile($path, 'document.pdf', 'application/pdf', null, true);

        $this->assertTrue(SecureUploadValidator::isAllowed($file, 'pdf'));
    }

    public function test_rejects_php_disguised_as_pdf(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'php');
        file_put_contents($path, '<?php echo "hack";');

        $file = new UploadedFile($path, 'evil.pdf', 'application/pdf', null, true);

        $this->assertFalse(SecureUploadValidator::isAllowed($file, 'pdf'));
    }
}
