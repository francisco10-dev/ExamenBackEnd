<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Middleware\ApiAuthMiddleware;

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
Route::prefix('api')->group(
    function(){
        //RUTAS ESPECÍFICAS
        Route::post('/user/upload',[UserController::class,'uploadImage']);
        Route::get('/user/getimage/{filename}',[UserController::class,'getImage']);
        Route::post('/post/upload',[PostController::class,'upload']);
        Route::get('/post/getimage/{filename}',[PostController::class,'getImage']);
        Route::post('/user/login',[UserController::class,'login']);
        Route::get('/user/getidentity',[UserController::class,'getIdentity']);
        Route::get('post/filter/{title}', [PostController::class, 'searchByLike']);
        //RUTAS AUTOMÁTICAS Restful
        Route::resource('/category',CategoryController::class,['except'=>['create','edit']]);
        Route::resource('/user',UserController::class,['except'=>['create','edit']]);
        Route::resource('/post',PostController::class,['except'=>['create','edit']]);
    }
);

