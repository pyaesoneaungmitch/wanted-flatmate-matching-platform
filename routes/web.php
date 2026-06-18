<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\ProfileDetailController;
use App\Http\Controllers\SwipeController;
use App\Http\Controllers\MatchesController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\MyProfileController;
use App\Http\Controllers\ListingsController;
use App\Http\Controllers\MyListingsController;
use App\Http\Controllers\QuizWizardController;

Route::get('/profile', [MyProfileController::class, 'show'])->name('profile');
Route::post('/profile/public', [MyProfileController::class, 'updatePublic'])->name('profile.public.update');
Route::post('/profile/about', [MyProfileController::class, 'updateAbout'])->name('profile.about.update');
Route::post('/profile/private', [MyProfileController::class, 'updatePrivate'])->name('profile.private.update');

Route::post('/profile/photos', [MyProfileController::class, 'uploadPhoto'])->name('profile.photos.upload');
Route::post('/profile/photos/{photo_id}/delete', [MyProfileController::class, 'deletePhoto'])->name('profile.photos.delete');


// Public (no login required)
Route::get('/', function () {
    return view('welcome');
});
Route::post('/swipe', [SwipeController::class, 'store'])->name('swipe.store');
// Auth pages
Route::get('/register', [AuthController::class, 'showRegister'])->name('register.form');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Quiz (login required)
Route::middleware('auth')->group(function () {
    Route::get('/quiz', [QuizWizardController::class, 'step1'])->name('quiz.show');

    Route::get('/quiz/step1', [QuizWizardController::class, 'step1'])->name('quiz.step1');
    Route::post('/quiz/step1', [QuizWizardController::class, 'saveStep1'])->name('quiz.step1.save');

    Route::get('/quiz/step2', [QuizWizardController::class, 'step2'])->name('quiz.step2');
    Route::post('/quiz/step2', [QuizWizardController::class, 'saveStep2'])->name('quiz.step2.save');

    Route::get('/quiz/step3', [QuizWizardController::class, 'step3'])->name('quiz.step3');
    Route::post('/quiz/step3', [QuizWizardController::class, 'saveStep3'])->name('quiz.step3.save');

    Route::get('/quiz/step4', [QuizWizardController::class, 'step4'])->name('quiz.step4');
    Route::post('/quiz/step4', [QuizWizardController::class, 'saveStep4'])->name('quiz.step4.save');

    Route::post('/quiz/reset', [QuizWizardController::class, 'reset'])->name('quiz.reset');
});

// Discover (login + quiz required)
Route::middleware(['auth','require.quiz'])
    ->get('/discover', [DiscoverController::class, 'index'])
    ->name('discover');

Route::get('/home', function () {
    return redirect()->route('discover');
})->middleware('auth')->name('home');

Route::middleware(['auth', 'require.quiz'])->group(function () {
    Route::get('/listings', fn() => view('listings'))->name('listings');
    Route::get('/profiles/{user_id}', [ProfileDetailController::class, 'show'])->name('profiles.show');
});

Route::middleware(['auth','require.quiz'])->group(function () {
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
    Route::get('/inbox/{inbox_id}', [InboxController::class, 'show'])->name('inbox.show');

    Route::post('/inbox/{inbox_id}/send', [InboxController::class, 'send'])->name('inbox.send');
    Route::post('/inbox/{inbox_id}/share-private', [InboxController::class, 'sharePrivate'])->name('inbox.sharePrivate');
    Route::post('/inbox/{inbox_id}/unmatch', [InboxController::class, 'unmatch'])->name('inbox.unmatch');
});

Route::middleware(['auth','require.quiz'])
  ->get('/matches', [MatchesController::class, 'index'])
  ->name('matches');

Route::middleware(['auth','require.quiz'])->group(function () {
    Route::get('/profile', [MyProfileController::class, 'show'])->name('profile');
    Route::post('/profile/public', [MyProfileController::class, 'updatePublic'])->name('profile.public.update');
    Route::post('/profile/about', [MyProfileController::class, 'updateAbout'])->name('profile.about.update');
    Route::post('/profile/private', [MyProfileController::class, 'updatePrivate'])->name('profile.private.update');
    Route::post('/profile/photos', [MyProfileController::class, 'uploadPhoto'])->name('profile.photos.upload');
    Route::post('/profile/photos/{photo_id}/delete', [MyProfileController::class, 'deletePhoto'])->name('profile.photos.delete');
    Route::post('/ai/bio', [\App\Http\Controllers\AiBioController::class, 'generate'])->name('ai.bio');
});

Route::middleware(['auth','require.quiz'])->group(function () {
    Route::get('/listings', [ListingsController::class, 'index'])->name('listings');
    Route::get('/listings/create', [ListingsController::class, 'create'])->name('listings.create');
    Route::post('/listings', [ListingsController::class, 'store'])->name('listings.store');
    Route::post('/listings/{listing_id}/photos', [ListingsController::class, 'uploadPhoto'])->name('listings.photos.upload');
    Route::post('/listings/{listing_id}/enquire', [ListingsController::class, 'enquire'])->name('listings.enquire');
    Route::post('/listings/photos/{photo_id}/delete', [ListingsController::class, 'deletePhoto'])->name('listings.photos.delete');
    });

    Route::middleware(['auth','require.quiz'])->group(function () {
    Route::get('/my-listings', [MyListingsController::class, 'index'])->name('my.listings');
    Route::get('/my-listings/{listing_id}/edit', [MyListingsController::class, 'edit'])->name('my.listings.edit');
    Route::post('/my-listings/{listing_id}/edit', [MyListingsController::class, 'update'])->name('my.listings.update');
    Route::post('/my-listings/{listing_id}/delete', [MyListingsController::class, 'destroy'])->name('my.listings.delete');

    Route::get('/my-listings/{listing_id}/enquiries', [MyListingsController::class, 'enquiries'])->name('my.listings.enquiries');
    Route::get('/my-listings/{listing_id}/enquiries/{inbox_id}', [MyListingsController::class, 'enquiriesShow'])->name('my.listings.enquiries.show');
    Route::post('/my-listings/{listing_id}/enquiries/{inbox_id}/send', [MyListingsController::class, 'enquiriesSend'])->name('my.listings.enquiries.send');
});