<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/signup',[UserController::class,'create'])->name('signup.get');
Route::post('/signup',[UserController::class,'store'])->name('signup.post');

Route::get('/login',function(){return response()->json([],200);})->name('login.get');
Route::post('/login',[UserController::class,'login'])->name('login.post');

Route::get('/users',[UserController::class,'index'])->middleware('roleCheck')->name('users.index');
Route::get('/users/{id}',[UserController::class,'show'])->middleware('roleCheck')->name('users.show');

Route::get('/users/{id}/edit',[UserController::class,'edit'])->middleware('roleCheck')->name('users.edit');
Route::put('/users/{id}',[UserController::class,'update'])->middleware('roleCheck')->name('users.update');

Route::get('/users/{id}/delete',[UserController::class,'delete'])->middleware('roleCheck')->name('users.delete');
Route::delete('/users/{id}',[UserController::class,'destroy'])->middleware('roleCheck')->name('users.destroy');

Route::get('/profile',[UserController::class,'profile'])->middleware('loginCheck')->name('profile.get');
Route::get('/profile/edit',[UserController::class,'editProfile'])->middleware('loginCheck')->name('profile.edit');
Route::put('/profile',[UserController::class,'updateProfile'])->middleware('loginCheck')->name('profile.update');

Route::get('/check',[UserController::class,'check']);
Route::get("/verify/{id}",[UserController::class,'verify'])->name("verify.mail");
