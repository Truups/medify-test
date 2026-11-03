<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MasterItemsController;
use App\Http\Controllers\CategoryItemController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/', [HomeController::class, 'index'])->name('home');

// Master items
Route::get('/master-items', [MasterItemsController::class, 'index'])->name('master-items-list');
Route::get('/master-items/search', [MasterItemsController::class, 'search']);
Route::get('/master-items/export-pdf', [MasterItemsController::class, 'exportPdf'])->name('master-items-export-pdf');
Route::get('/master-items/export-excel', [MasterItemsController::class, 'exportExcel'])->name('master-items-export-excel');
Route::get('/master-items/export/{export}/status', [MasterItemsController::class, 'exportStatus'])->name('master-items-export-status');
Route::get('/master-items/export/{export}/download', [MasterItemsController::class, 'downloadExport'])->name('master-items-export-download');
Route::get('/master-items/form/{method}/{id?}', [MasterItemsController::class, 'formView']);
Route::post('/master-items/form/{method}/{id?}', [MasterItemsController::class, 'formSubmit']);
Route::get('/master-items/view/{kode}', [MasterItemsController::class, 'singleView']);
Route::get('/master-items/delete/{id}', [MasterItemsController::class, 'delete']);
Route::get('/master-items/update-random-data', [MasterItemsController::class, 'updateRandomData']);

// Category items
Route::get('/category-items', [CategoryItemController::class, 'index'])->name('category-list');
Route::get('/category-items/search', [CategoryItemController::class, 'search']);
Route::get('/category-items/view/{kode}', [CategoryItemController::class, 'singleView'])->name('category-view');
Route::get('/category-items/form/{method}/{id?}', [CategoryItemController::class, 'formView']);
Route::post('/category-items/form/{method}/{id?}', [CategoryItemController::class, 'formSubmit']);
Route::get('/category-items/delete/{id}', [CategoryItemController::class, 'delete']);
