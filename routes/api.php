<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Models\Transaction;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/test', function () {
        return 'Hello World';
    });

    Route::controller(HomeController::class)->group((function() {
        Route::get('/home/getall','getAll');
    }));

    Route::controller(SettingController::class)->group((function() {
        Route::post('/setting/store','store');
        Route::get('/setting/getall','getAll');
        Route::get('/setting/getbyid','getById');
        Route::PUT('/setting/update','update');
        Route::DELETE('/setting/destroy','destroy');
    }));

    Route::controller(UserController::class)->group((function() {
        Route::post('/user/store','store');
        Route::get('/user/getall','getAll');
        Route::get('/user/get','get');
        Route::get('/user/getuserlogin','getUserLogin');
        Route::get('/user/getbyid','getById');
        Route::PUT('/user/update','update');
        Route::PUT('/user/setactive','setActive');
        Route::PUT('/user/setdisable','setDisable');
        Route::DELETE('/user/destroy','destroy');
    }));

    Route::controller(CategoryController::class)->group((function() {
        Route::post('/category','store');
        Route::get('/category','getAll');
        Route::get('/category/getbyid','getbyid');
        Route::PUT('/category/update','update');
        Route::PUT('/category/setactive/{category_id}','setActive');
        Route::PUT('/category/setdisable/{category_id}','setDisable');
        Route::DELETE('/category/{category_id}','destroy');
    }));

    Route::controller(ProductController::class)->group((function() {
        Route::post('/product/','store');
        Route::get('/product/getall','getAll');
        Route::get('/product/get','get');
        Route::get('/product/getbycode','getByCode');
        Route::get('/product/getbycategory','getByCategory');
        Route::post('/product/update','update');
        Route::PUT('/product/setactive','setActive');
        Route::PUT('/product/setdisable','setDisable');
        Route::DELETE('/product/destroy','destroy');

        //stock
        Route::get('/product/getstock','getStock');
        Route::post('/product/storestock','storeStock');

        Route::post('/product/downloadbycode','downloadByCode');
        Route::post('/product/downloadbyinvoice','downloadByInvoice');

    }));

    Route::controller(TransactionController::class)->group((function() {
        Route::post('/transaction/store','store');
        Route::get('/transaction/get','get');
        Route::get('/transaction/getbyinvoice','getByInvoice');
        Route::get('/transaction/getactive','getActive'); //Belum dibayar
        Route::get('/transaction/getdone','getDone'); //Sudah dibayar
        Route::get('/transaction/getcancel','getCancel'); 
        Route::get('/transaction/getrefund','getRefund');
        Route::get('/transaction/getexpired','getExpired');
        // Route::PUT('/transaction/update','update');
        Route::PUT('/transaction/setpending','setPending');
        Route::PUT('/transaction/setconfirm','setConfirm');
        Route::PUT('/transaction/setrefund','setRefund');
        Route::PUT('/transaction/setexpired','setExpired');
        Route::PUT('/transaction/setcancel','setCancel');
    }));

    Route::controller(CommentController::class)->group((function() {
        Route::get('/comment/getbyinvoice','getByInvoice');
        Route::get('/comment/getfilebyinvoice','getFileByInvoice');
        Route::post('/comment/store','store');
        Route::PUT('/comment/setactive','setActive');
        Route::PUT('/comment/setdisable','setDisable');
        Route::PUT('/comment/delete','delete');
        Route::DELETE('/comment/destroy','destroy');

        Route::post('/comment/downloadbycode','downloadByCode');
    }));
 
});

Route::controller(AuthController::class)->group((function() {
    Route::post('/auth/register','register');
    Route::post('/auth/login','login');
}));
