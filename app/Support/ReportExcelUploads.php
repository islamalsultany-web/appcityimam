<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ReportExcelUploads
{
    public function start(Request $request): array
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'size' => 'required|integer|min:1']);
        abort_unless(in_array(strtolower(pathinfo($data['name'], PATHINFO_EXTENSION)), ['xls', 'xlsx'], true), 422, 'اختر XLSX أو XLS.');
        $id = (string) Str::uuid();
        $path = $this->path($id);
        File::ensureDirectoryExists($path, 0700);
        File::put($path.'/meta.json', json_encode(['name' => basename(str_replace('\\', '/', $data['name'])), 'size' => (int) $data['size'], 'owner' => $this->owner($request)]));
        File::put($path.'/data', '');

        return ['id' => $id, 'chunk_size' => 512 * 1024];
    }

    public function append(Request $request, string $id): array
    {
        $meta = $this->metadata($request, $id);
        $data = $request->validate(['offset' => 'required|integer|min:0', 'chunk' => 'required|file|max:1024']);
        $handle = fopen($this->path($id).'/data', 'c+b');
        abort_unless($handle !== false, 507, 'تعذر فتح مساحة الرفع المؤقتة.');
        try {
            abort_unless(flock($handle, LOCK_EX), 409);
            $offset = fstat($handle)['size'];
            abort_unless($offset === (int) $data['offset'], 409, 'موضع الجزء المرفوع لا يطابق الملف. أعد المحاولة.');
            $bytes = $request->file('chunk')->getSize();
            abort_unless($bytes > 0 && $offset + $bytes <= $meta['size'], 422, 'حجم الجزء لا يطابق حجم الملف المعلن.');
            fseek($handle, 0, SEEK_END);
            $input = fopen($request->file('chunk')->getRealPath(), 'rb');
            try {
                $copied = stream_copy_to_stream($input, $handle);
                if ($copied !== $bytes) {
                    ftruncate($handle, $offset);
                    abort(507, 'مساحة التخزين لا تكفي لإكمال الرفع.');
                }
            } finally {
                fclose($input);
            }
            touch($this->path($id).'/meta.json');

            return ['received' => $offset + $bytes, 'complete' => $offset + $bytes === $meta['size']];
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public function resolve(Request $request, string $id): UploadedFile
    {
        $meta = $this->metadata($request, $id);
        $path = $this->path($id).'/data';
        clearstatcache(true, $path);
        abort_unless(is_file($path) && filesize($path) === $meta['size'], 422, 'انتظر اكتمال رفع جميع أجزاء الملف قبل التحليل.');

        return new UploadedFile($path, $meta['name'], null, null, true);
    }

    public function remove(Request $request, string $id): void
    {
        $this->metadata($request, $id);
        File::deleteDirectory($this->path($id));
    }

    public function purgeExpired(): int
    {
        $root = storage_path('framework/report-uploads');
        if (! is_dir($root)) {
            return 0;
        }
        $removed = 0;
        foreach (File::directories($root) as $path) {
            $meta = $path.'/meta.json';
            if (is_file($meta) && filemtime($meta) < time() - 86400) {
                File::deleteDirectory($path);
                $removed++;
            }
        }

        return $removed;
    }

    private function metadata(Request $request, string $id): array
    {
        $path = $this->path($id).'/meta.json';
        abort_unless(is_file($path), 404);
        $meta = json_decode(File::get($path), true);
        abort_unless(is_array($meta) && hash_equals($meta['owner'], $this->owner($request)), 404);

        return $meta;
    }

    private function owner(Request $request): string
    {
        if (! $request->session()->has('report_excel_upload_owner')) {
            $request->session()->put('report_excel_upload_owner', bin2hex(random_bytes(32)));
        }

        return hash('sha256', $request->session()->get('report_excel_upload_owner'));
    }

    private function path(string $id): string
    {
        abort_unless(Str::isUuid($id), 404);

        return storage_path('framework/report-uploads/'.$id);
    }
}
