<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Log kayıtlarına asla yazılmaması gereken istek alanları.
     *
     * @var array<int, string>
     */
    protected array $dontLogInput = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'api_key',
        'secret',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Exception'ı HTTP yanıtına çevirir.
     *
     * API istekleri (api/* veya JSON bekleyen istekler) her zaman tutarlı bir
     * JSON gövdesi alır:
     *
     *   {
     *     "message": "İnsan tarafından okunabilir açıklama",
     *     "error":   "message ile aynı — mevcut istemcilerle uyumluluk için",
     *     "errors":  { "alan": ["hata mesajı"] }   // yalnızca 422 doğrulama hatalarında
     *   }
     */
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->renderApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * API isteklerinde exception türüne göre doğru HTTP durum kodunu üretir.
     */
    protected function renderApiException($request, Throwable $e): JsonResponse
    {
        // 422 — Doğrulama hatası. Alan bazlı hata listesi de döner.
        if ($e instanceof ValidationException) {
            return $this->apiResponse(
                $e->validator->errors()->first() ?: 'Gönderilen bilgiler geçersiz.',
                422,
                ['errors' => $e->errors()]
            );
        }

        // 401 — Kimlik doğrulanmamış
        if ($e instanceof AuthenticationException) {
            return $this->apiResponse('Oturum açmanız gerekiyor.', 401);
        }

        // 403 — Yetkisiz işlem
        if ($e instanceof AuthorizationException) {
            return $this->apiResponse($e->getMessage() ?: 'Bu işlem için yetkiniz yok.', 403);
        }

        // 404 — Kayıt bulunamadı
        if ($e instanceof ModelNotFoundException) {
            return $this->apiResponse('Kayıt bulunamadı.', 404);
        }

        // 404 — Adres bulunamadı
        if ($e instanceof NotFoundHttpException) {
            return $this->apiResponse('İstenen adres bulunamadı.', 404);
        }

        // 405 — Yanlış HTTP metodu
        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->apiResponse('Bu adres için geçersiz istek yöntemi.', 405);
        }

        // 429 — Hız sınırı aşıldı
        if ($e instanceof TooManyRequestsHttpException) {
            return $this->apiResponse('Çok fazla istek gönderildi. Lütfen biraz bekleyin.', 429);
        }

        // abort(4xx/5xx) ile fırlatılan diğer HTTP exception'ları
        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();

            return $this->apiResponse(
                $e->getMessage() ?: 'İşlem tamamlanamadı.',
                ($status >= 400 && $status < 600) ? $status : 500
            );
        }

        // 500 — Beklenmeyen hata.
        // Ayrıntı her durumda log'a yazılır; dışarıya yalnızca APP_DEBUG açıkken verilir.
        Log::error('API hatası: ' . $e->getMessage(), [
            'exception' => get_class($e),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'file' => $e->getFile() . ':' . $e->getLine(),
            'input' => $request->except($this->dontLogInput),
        ]);

        if (config('app.debug')) {
            return $this->apiResponse(
                $e->getMessage() ?: 'Sunucu hatası.',
                500,
                [
                    'exception' => get_class($e),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'trace' => collect($e->getTrace())->take(10)->all(),
                ]
            );
        }

        return $this->apiResponse(
            'Beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyin.',
            500
        );
    }

    /**
     * Tüm API hata yanıtları için tek biçimli gövde.
     */
    protected function apiResponse(string $message, int $status, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'message' => $message,
            // Mevcut frontend kodu `error` alanını okuyor — bozmamak için ikisi de gönderilir.
            'error' => $message,
        ], $extra), $status);
    }

    /**
     * Kimlik doğrulanmamış istekler: API'de 401 JSON, web'de login yönlendirmesi.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->apiResponse('Oturum açmanız gerekiyor.', 401);
        }

        return parent::unauthenticated($request, $exception);
    }
}
