<?php
namespace App\Http\Controllers;

use App\Models\Reference;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScreenshotController extends Controller
{
    public function take(Request $request)
    {
        $ref = Reference::find($request->referenceId);
        if (!$ref) return response()->json(['success' => false, 'error' => 'Referans bulunamadı'], 404);
        if (!$ref->url || $ref->url === '#') return response()->json(['success' => false, 'error' => 'URL yok'], 400);

        // Status'u pending yap
        $ref->update([
            'screenshot_status' => 'pending',
            'screenshot_error' => null,
            'screenshot_updated_at' => now(),
        ]);

        // Screenshot dosya yolu
        $filename = 'screenshot-' . Str::slug($ref->title) . '-' . time() . '.png';
        $relativePath = 'uploads/references/screenshots/' . $filename;
        $absolutePath = public_path($relativePath);

        // Klasoru olustur
        $dir = dirname($absolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        // Puppeteer script'i calistir (proc_open ile temiz ortam)
        $scriptPath = base_path('scripts/capture-screenshot.cjs');
        $nodeCommand = sprintf(
            'node %s %s %s',
            escapeshellarg($scriptPath),
            escapeshellarg($ref->url),
            escapeshellarg($absolutePath)
        );

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $env = array_merge($_ENV, $_SERVER, [
            'HOME' => getenv('HOME') ?: '/tmp',
            'PATH' => '/usr/local/bin:/usr/bin:/bin:/opt/homebrew/bin:' . (getenv('PATH') ?: ''),
        ]);

        $process = proc_open($nodeCommand, $descriptors, $pipes, base_path(), $env);
        $output = '';
        if (is_resource($process)) {
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            if (!$output && $stderr) {
                $output = $stderr;
            }
        }

        // Sonucu parse et
        $result = json_decode($output, true);

        if ($result && isset($result['success']) && $result['success'] === true && file_exists($absolutePath)) {
            $ref->update([
                'screenshot_url' => '/' . $relativePath,
                'screenshot_status' => 'success',
                'screenshot_error' => null,
                'screenshot_updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'screenshot_url' => '/' . $relativePath,
                'message' => 'Screenshot başarıyla alındı',
            ]);
        }

        // Basarisiz
        $errorMsg = $result['error'] ?? ($output ?: 'Bilinmeyen hata');
        $ref->update([
            'screenshot_status' => 'failed',
            'screenshot_error' => mb_substr($errorMsg, 0, 500),
            'screenshot_updated_at' => now(),
        ]);

        return response()->json([
            'success' => false,
            'status' => 'failed',
            'error' => $errorMsg,
        ], 500);
    }

    public function status(Request $request)
    {
        $ref = Reference::find($request->referenceId);
        if (!$ref) return response()->json(['success' => false, 'error' => 'Referans bulunamadı'], 404);

        return response()->json([
            'success' => true,
            'screenshot_status' => $ref->screenshot_status,
            'screenshot_url' => $ref->screenshot_url,
            'screenshot_error' => $ref->screenshot_error,
            'screenshot_updated_at' => $ref->screenshot_updated_at,
        ]);
    }
}
