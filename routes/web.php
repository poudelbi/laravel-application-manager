<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Protected application routes
    Route::resource('applications', ApplicationController::class);
    Route::post('/applications/{name}/deploy', [ApplicationController::class, 'deploy'])->name('applications.deploy');
    Route::post('/applications/deploy-official-laravel', [ApplicationController::class, 'deployOfficialLaravel'])->name('applications.deploy.official');
    Route::get('/applications/{name}/setup', [ApplicationController::class, 'setup'])->name('applications.setup');
    Route::post('/applications/{name}/setup', [ApplicationController::class, 'storeSetup'])->name('applications.store.setup');
    Route::get('/applications/{name}/nginx-config', [ApplicationController::class, 'nginxConfig'])->name('applications.nginx-config');
});

Route::middleware('auth')->group(function () {
    Route::resource('sites', \App\Http\Controllers\SitesController::class);
    Route::post('/sites/{name}/refresh', [\App\Http\Controllers\SitesController::class, 'refresh'])->name('sites.refresh');
    Route::post('/sites/{name}/start', [\App\Http\Controllers\SitesController::class, 'start'])->name('sites.start');
    Route::post('/sites/{name}/stop', [\App\Http\Controllers\SitesController::class, 'stop'])->name('sites.stop');
    Route::post('/sites/{name}/nginx-config', [\App\Http\Controllers\SitesController::class, 'createNginxConfig'])->name('sites.nginx-config');

    // Docker Management Routes
    Route::get('/docker', [\App\Http\Controllers\DockerController::class, 'index'])->name('docker.index');
    Route::post('/docker/applications/{name}/build', [\App\Http\Controllers\DockerController::class, 'buildAppImage'])->name('docker.build-app-image');
    Route::post('/docker/applications/{name}/start', [\App\Http\Controllers\DockerController::class, 'startAppContainer'])->name('docker.start-app-container');
    Route::post('/docker/containers/{id}/stop', [\App\Http\Controllers\DockerController::class, 'stopAppContainer'])->name('docker.stop-app-container');
    Route::get('/docker/applications/{name}/status', [\App\Http\Controllers\DockerController::class, 'getAppDockerStatus'])->name('docker.app-status');

    // Linux Management Routes
    Route::get('/linux', [\App\Http\Controllers\LinuxController::class, 'index'])->name('linux.index');
    Route::post('/linux/run-command', [\App\Http\Controllers\LinuxController::class, 'runCommand'])->name('linux.run-command');

    Route::get('/test-dashboard', [App\Http\Controllers\TestDashboardController::class, 'index'])->name('test-dashboard.index');
    Route::post('/test-dashboard/run/{testName}', [App\Http\Controllers\TestDashboardController::class, 'runTest'])->name('test-dashboard.run');
    Route::post('/test-dashboard/artisan', [App\Http\Controllers\TestDashboardController::class, 'runArtisanCommand'])->name('test-dashboard.artisan');
    Route::post('/test-dashboard/npm', [App\Http\Controllers\TestDashboardController::class, 'runNpmCommand'])->name('test-dashboard.npm');

    // Page and Category Management Routes
    Route::resource('pages', \App\Http\Controllers\PageController::class);
    Route::resource('page-categories', \App\Http\Controllers\PageCategoryController::class);
    Route::resource('menus', \App\Http\Controllers\MenuController::class);
    Route::resource('images', \App\Http\Controllers\ImageController::class);
});


require __DIR__.'/auth.php';