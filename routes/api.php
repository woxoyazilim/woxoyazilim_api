<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\ReferenceCategoryController;
use App\Http\Controllers\ScreenshotController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\ServiceTagController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\DemoRequestController;
use App\Http\Controllers\ChatLeadController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\MessageNoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SiteSettingsController;

// ==========================================
// AUTH
// ==========================================
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/session', [AuthController::class, 'session']);
Route::post('/setup-admin', [AuthController::class, 'setupAdmin']);

// ==========================================
// REFERENCES
// ==========================================
Route::get('/references', [ReferenceController::class, 'index']);
Route::get('/references/{id}', [ReferenceController::class, 'show']);
Route::post('/references', [ReferenceController::class, 'store'])->middleware('auth:sanctum');
Route::put('/references/{id}', [ReferenceController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/references/{id}', [ReferenceController::class, 'destroy'])->middleware('auth:sanctum');

// Screenshot
Route::post('/references/screenshot', [ScreenshotController::class, 'take'])->middleware('auth:sanctum');
Route::get('/references/screenshot/status', [ScreenshotController::class, 'status'])->middleware('auth:sanctum');

// Upload
Route::post('/references/upload-image', [UploadController::class, 'referenceImage'])->middleware('auth:sanctum');

// ==========================================
// REFERENCE CATEGORIES
// ==========================================
Route::get('/reference-categories', [ReferenceCategoryController::class, 'index']);
Route::post('/reference-categories', [ReferenceCategoryController::class, 'store'])->middleware('auth:sanctum');
Route::patch('/reference-categories/{id}', [ReferenceCategoryController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/reference-categories/{id}', [ReferenceCategoryController::class, 'destroy'])->middleware('auth:sanctum');

// ==========================================
// SERVICES
// ==========================================
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);
Route::post('/services', [ServiceController::class, 'store'])->middleware('auth:sanctum');
Route::put('/services/{id}', [ServiceController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->middleware('auth:sanctum');

// ==========================================
// SECTORS
// ==========================================
Route::get('/sectors', [SectorController::class, 'index']);
Route::post('/sectors', [SectorController::class, 'store'])->middleware('auth:sanctum');

// ==========================================
// SUBCATEGORIES
// ==========================================
Route::get('/subcategories', [SubcategoryController::class, 'index']);
Route::post('/subcategories', [SubcategoryController::class, 'store'])->middleware('auth:sanctum');

// ==========================================
// SERVICE TAGS
// ==========================================
Route::get('/service-tags', [ServiceTagController::class, 'index']);
Route::post('/service-tags', [ServiceTagController::class, 'store'])->middleware('auth:sanctum');

// ==========================================
// MESSAGES (Contact Form)
// ==========================================
Route::get('/messages', [MessageController::class, 'index'])->middleware('auth:sanctum');
Route::get('/messages/{id}', [MessageController::class, 'show'])->middleware('auth:sanctum');
Route::patch('/messages/{id}', [MessageController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/messages/{id}', [MessageController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('/messages/bulk', [MessageController::class, 'bulkAction'])->middleware('auth:sanctum');
Route::post('/messages/{id}/erp-sync', [MessageController::class, 'syncToErp'])->middleware('auth:sanctum');
Route::post('/contact-form', [MessageController::class, 'store']);
Route::post('/contact', [MessageController::class, 'store']);

// Message Notes
Route::get('/messages/{messageId}/notes', [MessageNoteController::class, 'index'])->middleware('auth:sanctum');
Route::post('/messages/{messageId}/notes', [MessageNoteController::class, 'store'])->middleware('auth:sanctum');
Route::patch('/messages/{messageId}/notes/{noteId}', [MessageNoteController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/messages/{messageId}/notes/{noteId}', [MessageNoteController::class, 'destroy'])->middleware('auth:sanctum');
Route::patch('/messages/{messageId}/notes/{noteId}/pin', [MessageNoteController::class, 'togglePin'])->middleware('auth:sanctum');

// ==========================================
// DEMO REQUESTS
// ==========================================
Route::get('/demo-requests', [DemoRequestController::class, 'index'])->middleware('auth:sanctum');
Route::post('/demo-requests', [DemoRequestController::class, 'store']);
Route::post('/demo-request', [DemoRequestController::class, 'store']); // alias for modal
Route::patch('/demo-requests/{id}', [DemoRequestController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/demo-requests/{id}', [DemoRequestController::class, 'destroy'])->middleware('auth:sanctum');

// ==========================================
// CHAT
// ==========================================
Route::post('/chat-leads', [ChatLeadController::class, 'store']);
Route::get('/chat-leads', [ChatLeadController::class, 'index'])->middleware('auth:sanctum');
Route::post('/chat-message', [ChatLeadController::class, 'message']);

// ==========================================
// PAGES & CMS
// ==========================================
Route::get('/pages', [PageController::class, 'index']);
Route::get('/pages/{slug}', [PageController::class, 'show']);
Route::get('/page-content/{slug}', [PageController::class, 'content']);
Route::put('/page-content/{slug}', [PageController::class, 'updateContent'])->middleware('auth:sanctum');

// ==========================================
// LANDING PAGES
// ==========================================
Route::get('/landing-pages', [LandingPageController::class, 'index']);
Route::get('/landing-pages/{id}', [LandingPageController::class, 'show']);
Route::post('/landing-pages', [LandingPageController::class, 'store'])->middleware('auth:sanctum');
Route::put('/landing-pages/{id}', [LandingPageController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/landing-pages/{id}', [LandingPageController::class, 'destroy'])->middleware('auth:sanctum');

// ==========================================
// NOTIFICATIONS
// ==========================================
Route::get('/notifications', [NotificationController::class, 'index'])->middleware('auth:sanctum');
Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->middleware('auth:sanctum');
Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead'])->middleware('auth:sanctum');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->middleware('auth:sanctum');

// ==========================================
// USERS / PERSONNEL
// ==========================================
Route::get('/users', [UserController::class, 'index'])->middleware('auth:sanctum', 'role:super_admin,admin');
Route::get('/users/assignable', [UserController::class, 'assignableUsers'])->middleware('auth:sanctum');
Route::get('/users/{id}', [UserController::class, 'show'])->middleware('auth:sanctum', 'role:super_admin,admin');
Route::patch('/users/{id}', [UserController::class, 'update'])->middleware('auth:sanctum', 'role:super_admin');

// ==========================================
// STATS
// ==========================================
Route::get('/stats', [StatsController::class, 'index'])->middleware('auth:sanctum');
Route::get('/stats/trends', [StatsController::class, 'trends'])->middleware('auth:sanctum');
Route::get('/stats/personnel', [StatsController::class, 'personnel'])->middleware('auth:sanctum');

// ==========================================
// SITE SETTINGS
// ==========================================
Route::get('/settings', [SiteSettingsController::class, 'index']);
Route::put('/settings', [SiteSettingsController::class, 'update'])->middleware('auth:sanctum');

// ==========================================
// HEALTH CHECK
// ==========================================
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});
