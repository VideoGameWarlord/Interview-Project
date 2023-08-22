<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication Routes
Auth::routes();

Route::get('/', function () {
    return view('welcome');
});

// Clients routes
Route::middleware(['auth'])->group(function () {
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');

    //Create client
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');

    //Edit client
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');

    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    //Assign user to client
    Route::post('/clients/{client}/assign', [ClientController::class, 'assignUser'])->name('clients.assign');

    //Import and export clients
    Route::get('/clients/import', [ClientController::class, 'showImportForm'])->name('clients.showImport');
    Route::post('/clients/import', [ClientController::class, 'importClients'])->name('clients.import');
    Route::get('/clients/export', [ClientController::class, 'exportClients'])->name('clients.export');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
