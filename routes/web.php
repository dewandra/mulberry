<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ProjectAttachmentController;
use App\Http\Controllers\ProjectPreviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard if authenticated, otherwise to login
Route::get('/', function () {
    return Auth::check() 
        ? redirect()->route('dashboard') 
        : redirect()->route('login');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Projects - readable by all authenticated users
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

    // Feedback - all authenticated users can submit feedback
    Route::post('/projects/{project}/feedbacks', [FeedbackController::class, 'store'])->name('projects.feedbacks.store');
    Route::delete('/projects/{project}/feedbacks/{feedback}', [FeedbackController::class, 'destroy'])->name('projects.feedbacks.destroy');

    // Attachment view inline (semua role bisa, access control di controller)
    Route::get('/projects/{project}/attachments/{attachment}/view', [ProjectAttachmentController::class, 'view'])->name('projects.attachments.view');


    // Projects - write operations for admin/super_admin only
    Route::middleware(['role:super_admin,admin'])->group(function () {
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        // Previews
        Route::post('/projects/{project}/previews', [ProjectPreviewController::class, 'store'])->name('projects.previews.store');
        Route::delete('/projects/{project}/previews/{preview}', [ProjectPreviewController::class, 'destroy'])->name('projects.previews.destroy');
        // Attachments
        Route::post('/projects/{project}/previews/{preview}/attachments', [ProjectAttachmentController::class, 'store'])->name('projects.previews.attachments.store');
        Route::delete('/projects/{project}/attachments/{attachment}', [ProjectAttachmentController::class, 'destroy'])->name('projects.attachments.destroy');
    });
    
    // Super Admin & Admin routes
    Route::middleware(['role:super_admin'])->group(function () {
        // User Management
        Route::patch('users/{user}/toggle-status', [App\Http\Controllers\UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::resource('users', App\Http\Controllers\UserController::class)->names('users');
        Route::patch('clients/{client}/toggle-status', [App\Http\Controllers\ClientController::class, 'toggleStatus'])->name('clients.toggle-status');
        Route::resource('clients', App\Http\Controllers\ClientController::class)->names('clients');
    });
});

require __DIR__.'/auth.php';
