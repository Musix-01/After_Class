<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\MemoryController;
use App\Http\Controllers\MysteryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TheoryController;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------
// Public: login / register / logout
// ---------------------------------------------------------------

// Login page
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

// Process login
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Redirect the homepage to the login page
Route::get('/', function () {
    return redirect()->route('login');
});

// Registration page
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register.form');

// Process registration
Route::post('/register', [RegisterController::class, 'register'])->name('register');

// Logout
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');


// ---------------------------------------------------------------
// Students (and admins): the Freedom Wall, memories and mysteries.
// EnsureUserIsActive signs out suspended accounts on their next request.
// ---------------------------------------------------------------
Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/posts/{post}', [PostController::class, 'show'])
        ->whereNumber('post')
        ->name('posts.show');

    Route::get('/memories', [MemoryController::class, 'index'])
        ->name('memories.index');

    Route::get('/memories/{memory}', [MemoryController::class, 'show'])
        ->whereNumber('memory')
        ->name('memories.show');

    Route::get('/mysteries', [MysteryController::class, 'index'])
        ->name('mysteries.index');

    Route::get('/mysteries/{mystery}', [MysteryController::class, 'show'])
        ->whereNumber('mystery')
        ->name('mysteries.show');

    // Anything that writes data is rate limited
    // (40 requests / minute / user)
    Route::middleware('throttle:40,1')->group(function () {

        Route::post('/posts', [PostController::class, 'store'])
            ->name('posts.store');

        Route::put('/posts/{post}', [PostController::class, 'update'])
            ->name('posts.update');

        Route::delete('/posts/{post}', [PostController::class, 'destroy'])
            ->name('posts.destroy');

        Route::post('/posts/{post}/repost', [PostController::class, 'repost'])
            ->name('posts.repost');

        Route::post('/posts/{post}/report', [ReportController::class, 'store'])
            ->name('posts.report');

        Route::post('/posts/{post}/like', [LikeController::class, 'toggle'])
            ->name('posts.like');

        Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
            ->name('comments.store');

        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
            ->name('comments.destroy');

        Route::post('/mysteries/{mystery}/theories', [TheoryController::class, 'store'])
            ->name('theories.store');

        Route::delete('/theories/{theory}', [TheoryController::class, 'destroy'])
            ->name('theories.destroy');
    });
});


// ---------------------------------------------------------------
// Admin panel → /admin (role = admin only)
// ---------------------------------------------------------------
Route::middleware([
    'auth',
    EnsureUserIsActive::class,
    EnsureUserIsAdmin::class
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', [Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        // Manage users
        Route::get('/users', [Admin\UserController::class, 'index'])
            ->name('users.index');

        Route::get('/users/{user}', [Admin\UserController::class, 'show'])
            ->whereNumber('user')
            ->name('users.show');

        Route::post('/users/{user}/suspend', [Admin\UserController::class, 'suspend'])
            ->name('users.suspend');

        Route::post('/users/{user}/unsuspend', [Admin\UserController::class, 'unsuspend'])
            ->name('users.unsuspend');

        Route::post('/users/{user}/role', [Admin\UserController::class, 'role'])
            ->name('users.role');


        // Manage posts + community posts
        Route::get('/posts', [Admin\PostController::class, 'index'])
            ->name('posts.index');

        Route::post('/posts', [Admin\PostController::class, 'store'])
            ->name('posts.store');

        Route::get('/posts/{post}', [Admin\PostController::class, 'show'])
            ->whereNumber('post')
            ->name('posts.show');

        Route::post('/posts/{post}/pin', [Admin\PostController::class, 'pin'])
            ->whereNumber('post')
            ->name('posts.pin');

        Route::post('/posts/{post}/remove', [Admin\PostController::class, 'remove'])
            ->whereNumber('post')
            ->name('posts.remove');

        Route::post('/posts/{post}/restore', [Admin\PostController::class, 'restore'])
            ->whereNumber('post')
            ->name('posts.restore');

        Route::delete('/comments/{comment}', [Admin\PostController::class, 'destroyComment'])
            ->name('comments.destroy');


        // Moderation queue
        Route::get('/reports', [Admin\ReportController::class, 'index'])
            ->name('reports.index');

        Route::post('/reports/{report}/dismiss', [Admin\ReportController::class, 'dismiss'])
            ->name('reports.dismiss');

        Route::post('/reports/{report}/remove', [Admin\ReportController::class, 'remove'])
            ->name('reports.remove');


        // Memory capsules
        Route::resource('memories', Admin\MemoryController::class)
            ->except('show');

        Route::post('/memories/{memory}/open', [Admin\MemoryController::class, 'open'])
            ->name('memories.open');


        // Mysteries
        Route::resource('mysteries', Admin\MysteryController::class);

        Route::post('/mysteries/{mystery}/clues', [Admin\MysteryController::class, 'addClue'])
            ->name('mysteries.clues.store');

        Route::delete('/clues/{clue}', [Admin\MysteryController::class, 'destroyClue'])
            ->name('clues.destroy');

        Route::post('/mysteries/{mystery}/resolve', [Admin\MysteryController::class, 'resolve'])
            ->name('mysteries.resolve');

        Route::post('/mysteries/{mystery}/reopen', [Admin\MysteryController::class, 'reopen'])
            ->name('mysteries.reopen');


        // Announcements
        Route::get('/announcements', [Admin\AnnouncementController::class, 'index'])
            ->name('announcements.index');

        Route::post('/announcements', [Admin\AnnouncementController::class, 'store'])
            ->name('announcements.store');

        Route::post('/announcements/{announcement}/toggle', [Admin\AnnouncementController::class, 'toggle'])
            ->name('announcements.toggle');

        Route::delete('/announcements/{announcement}', [Admin\AnnouncementController::class, 'destroy'])
            ->name('announcements.destroy');


        // Notifications, analytics, audit log
        Route::get('/notifications', [Admin\NotificationController::class, 'index'])
            ->name('notifications.index');

        Route::post('/notifications/read', [Admin\NotificationController::class, 'read'])
            ->name('notifications.read');

        Route::get('/analytics', [Admin\AnalyticsController::class, 'index'])
            ->name('analytics');

        Route::get('/logs', [Admin\LogController::class, 'index'])
            ->name('logs.index');
    });