<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewModerationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\VerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:api', 'verified'])->group(function () {
    // Authenticated routes requiring email verification
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('logout', [AuthController::class, 'logout']);

    Route::post('users/{id}/assign-role', [RoleController::class, 'assignRole'])->name('users.assignRole');
    Route::get('user/roles', [RoleController::class, 'getUserRoles'])->name('users.getUserRoles');

    // Admin routes
    Route::middleware('role:Admin')->group(function () {
        Route::post('users/{id}/remove-role', [RoleController::class, 'removeRole'])->name('users.removeRole');
        Route::get('reviews/pending', [ReviewModerationController::class, 'index']);
        Route::patch('reviews/{review}/approve', [ReviewModerationController::class, 'approve']);
        Route::patch('reviews/{review}/reject', [ReviewModerationController::class, 'reject']);
    });

    // Vendor routes
    Route::middleware('role:Vendor')->group(function () {
        Route::put('orders/{id}', [OrderController::class, 'updateStatus']);
    });

    // Customer routes
    Route::middleware('role:Customer')->group(function () {
        Route::get('orders/{id}', [OrderController::class, 'show']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::patch('orders/{id}/cancel', [OrderController::class, 'cancel']);
        Route::post('create-payment-intent', [PaymentController::class, 'createPaymentIntent']);
        Route::post('confirm-payment', [PaymentController::class, 'confirmPayment']);
        Route::post('products/{productId}/reviews', [ReviewController::class, 'store']);
    });

    // General routes
    Route::get('orders', [OrderController::class, 'index']);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('tags', TagController::class);
    Route::get('search', [ProductController::class, 'search'])->name('products.search');
    Route::post('/products/filter', [ProductController::class, 'filter']);
    Route::get('products/{productId}/reviews', [ReviewController::class, 'show']);
    Route::get('notifications', [NotificationController::class, 'getNotifications']);
    Route::post('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('products.index');
        Route::post('/', [ProductController::class, 'store'])->name('products.store');
        Route::get('/{id}', [ProductController::class, 'show'])->name('products.show');
        Route::put('/{id}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // Password Reset Routes
    // routes/web.php (or routes/api.php if you use API routes)
    Route::get('password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset-link', [PasswordResetController::class, 'sendResetLinkEmail']);
    Route::post('password/reset', [PasswordResetController::class, 'resetPassword']);
});

Route::get('/email/verify/{id}', [VerificationController::class, 'verify'])->name('verification.verify');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);


Route::get('check-auth', function (Request $request) {
    return $request->user() ? response()->json(['user' => $request->user()]) : response()->json(['error' => 'Unauthenticated.'], 401);
})->middleware('auth:api');



// Route::get('/test-email', function () {
//     try {
//         Mail::raw('This is a test email', function ($message) {
//             $message->to('test@example.com')
//                     ->subject('Test Email');
//         });

//         return 'Test email sent!';
//     } catch (\Exception $e) {
//         return 'Error sending test email: ' . $e->getMessage();
//     }
// });

