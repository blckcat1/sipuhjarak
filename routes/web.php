<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PuhjarakController;

Route::get('/', [PuhjarakController::class, 'index'])->name('home');
Route::post('/login', [PuhjarakController::class, 'login'])->name('login');
Route::post('/logout', [PuhjarakController::class, 'logout'])->name('logout');
Route::post('/aduan', [PuhjarakController::class, 'storeAduan'])->name('aduan.store');
Route::patch('/aduan/{id}/status', [PuhjarakController::class, 'updateAduanStatus'])->name('aduan.update-status');
Route::post('/surat', [PuhjarakController::class, 'storeSurat'])->name('surat.store');
Route::patch('/surat/{id}/status', [PuhjarakController::class, 'updateSuratStatus'])->name('surat.update-status');
Route::post('/berita', [PuhjarakController::class, 'storeNews'])->name('news.store');
Route::post('/berita/{id}', [PuhjarakController::class, 'updateNews'])->name('news.update');
Route::delete('/berita/{id}', [PuhjarakController::class, 'deleteNews'])->name('news.delete');

