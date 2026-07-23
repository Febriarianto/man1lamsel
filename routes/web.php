<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\InfographicController as AdminInfographicController;
use App\Http\Controllers\Admin\LinkController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SsoController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfographicController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/berita', [PostController::class, 'news'])->name('posts.news');
Route::get('/artikel', [PostController::class, 'articles'])->name('posts.articles');
Route::get('/pengumuman', [PostController::class, 'announcements'])->name('posts.announcements');
Route::get('/prestasi', [PostController::class, 'achievements'])->name('posts.achievements');
Route::get('/konten/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/cari', [PostController::class, 'search'])->name('search');
Route::get('/profil/{page}', [PageController::class, 'show'])->name('pages.show');
Route::get('/guru', [StaffController::class, 'teachers'])->name('staff.teachers');
Route::get('/tenaga-kependidikan', [StaffController::class, 'employees'])->name('staff.employees');
Route::get('/galeri-foto', [GalleryController::class, 'photos'])->name('galleries.photos');
Route::get('/galeri-video', [GalleryController::class, 'videos'])->name('galleries.videos');
Route::get('/hubungi-kami', [ContactController::class, 'create'])->name('contact');
Route::post('/hubungi-kami', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:5,1');
Route::get('/infografis', [InfographicController::class, 'index'])->name('infographics.index');
Route::get('/infografis/{infographic}', [InfographicController::class, 'show'])->name('infographics.show');
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store')->middleware('throttle:5,1');
    Route::get('/sso/kemenag', [SsoController::class, 'redirect'])->name('sso.redirect')->middleware('throttle:10,1');
    Route::get('/sso/kemenag/callback', [SsoController::class, 'callback'])->name('sso.callback')->middleware('throttle:10,1');

    Route::middleware('cms')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        Route::resource('posts', AdminPostController::class)->except('show');
        Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
        Route::put('/account', [AccountController::class, 'update'])->name('account.update');

        Route::middleware('admin')->group(function () {
            Route::resource('pages', AdminPageController::class)->except('show');
            Route::resource('staff', AdminStaffController::class)->except('show');
            Route::resource('galleries', AdminGalleryController::class)->except('show');
            Route::resource('banners', BannerController::class)->except('show');
            Route::resource('links', LinkController::class)->except('show');
            Route::resource('menus', MenuController::class)->except('show');
            Route::post('/menus/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');
            Route::resource('infographics', AdminInfographicController::class)->except('show');
            Route::resource('events', EventController::class)->except('show');
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
            Route::get('/messages', [ContactMessageController::class, 'index'])->name('messages.index');
            Route::get('/messages/{message}', [ContactMessageController::class, 'show'])->name('messages.show');
            Route::delete('/messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');
        });
    });
});
