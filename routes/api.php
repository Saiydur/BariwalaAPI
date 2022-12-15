<?php

use App\Http\Controllers\FlatController;
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

Route::get('/users',[UserController::class,'index'])->name('users.index');
Route::get('/users/{id}',[UserController::class,'show'])->name('users.show');
Route::get('/users/{id}/edit',[UserController::class,'edit'])->name('users.edit');
Route::put('/users/{id}',[UserController::class,'update'])->name('users.update');
Route::get('/users/{id}/delete',[UserController::class,'delete'])->name('users.delete');
Route::delete('/users/{id}',[UserController::class,'destroy'])->name('users.destroy');

Route::get('/profile',[UserController::class,'profile'])->name('profile.get');
Route::get('/profile/edit',[UserController::class,'editProfile'])->name('profile.edit');
Route::put('/profile/edit/{id}',[UserController::class,'updateProfile'])->name('profile.update');
Route::put("profile/change-password",[UserController::class,'changePassword'])->name("profile.change.password");
Route::post('/logout',[UserController::class,'logout'])->name('profile.logout');

Route::get("/flat",[FlatController::class,'showCreate'])->name("flat.create.show");
Route::post("/flat/create",[FlatController::class,'create'])->name("flat.create");
Route::get("/flats",[FlatController::class,'show'])->name("flat.show");
Route::get("/flat/{id}",[FlatController::class,'showEdit'])->name("flat.show.edit");
Route::put("/flat/{id}",[FlatController::class,'update'])->name("flat.show.update");
Route::delete("/flat/{id}",[FlatController::class,'delete'])->name("flat.show.delete");

Route::get('/roles',[UserController::class,'check']);
Route::get("/verify/{id}",[UserController::class,'verify'])->name("verify.mail");
