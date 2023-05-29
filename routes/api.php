<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JournalAccountController;
use App\Http\Controllers\JournalCategoryController;
use App\Http\Controllers\JournalTransactionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TripayController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Artisan;
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

//START OF PUBLIC API

//AUTH
Route::controller(AuthController::class)->group((function () {
    Route::post('/auth/register', 'register');
    Route::post('/auth/login', 'login');
    Route::post('/auth/loginadmin', 'loginAdmin');
}));

//PRODUCT
Route::controller(ProductController::class)->group((function () {
    Route::get('/product/getall', 'getAll');
    Route::get('/product/get', 'get');
    Route::get('/product/getbycode', 'getByCode');
    Route::get('/product/getbycategory', 'getByCategory');
}));

//CATEGORY
Route::controller(CategoryController::class)->group((function () {
    Route::get('/category', 'get');
    Route::get('/category/getbyid', 'getbyid');
}));

//TRIPAY
Route::controller(TripayController::class)->group((function () {
    Route::post('/tripay/callback', 'callback');
}));
//END OF PUBLIC API

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:sanctum')->group(function () {
    //START ACCESS LEVEL USER AUTH (CLIENT, ADMIN, SUPER USER)
    Route::get('/test', function () {
        return 'Hello World';
    });

    Route::controller(AuthController::class)->group((function () {
        Route::POST('/auth/checkpassword', 'checkPassword');
    }));

    Route::controller(UserController::class)->group((function () {
        Route::get('/user/getuserlogin', 'getUserLogin');
        Route::PUT('/user/updateme', 'updateMe');
    }));

    Route::controller(ProductController::class)->group((function () {
        Route::post('/product/downloadbycode', 'downloadByCode');
        Route::post('/product/downloadbyinvoice', 'downloadByInvoice');
    }));

    Route::controller(TransactionController::class)->group((function () {
        Route::post('/transaction/store', 'store');
        Route::get('/transaction/getmy', 'getMy');
    }));

    Route::controller(BankController::class)->group((function () {
        Route::get('/bank', 'get');
        Route::get('/bank/getall', 'getAll');
        Route::get('/bank/getbyid', 'getbyid');
        Route::post('/bank', 'store');
        Route::put('/bank/update', 'update');
        Route::delete('/bank/{id}', 'destroy');
    }));
    //END OF ACCESS LEVEL USER AUTH (CLIENT, ADMIN, SUPER USER)


    //START OF ACCESS ADMIN & SUPERUSER ONLY
    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::controller(HomeController::class)->group((function () {
            Route::get('/home/getall', 'getAll');
        }));

        Route::controller(SettingController::class)->group((function () {
            Route::post('/setting/store', 'store');
            Route::get('/setting/getall', 'getAll');
            Route::get('/setting/getbyid', 'getById');
            Route::PUT('/setting/update', 'update');
            Route::DELETE('/setting/destroy', 'destroy');
        }));

        Route::controller(UserController::class)->group((function () {
            Route::post('/user/store', 'store');
            Route::get('/user/getall', 'getAll');
            Route::get('/user/get', 'get');
            Route::get('/user/getbyid', 'getById');
            Route::PUT('/user/update', 'update');
            Route::PUT('/user/setactive', 'setActive');
            Route::PUT('/user/setdisable', 'setDisable');
            Route::DELETE('/user/destroy', 'destroy');
        }));

        Route::controller(CategoryController::class)->group((function () {
            Route::get('/category/getall', 'getAll');
            Route::post('/category', 'store');
            Route::PUT('/category/update', 'update');
            Route::PUT('/category/setactive/{category_id}', 'setActive');
            Route::PUT('/category/setdisable/{category_id}', 'setDisable');
            Route::DELETE('/category/{category_id}', 'destroy');
        }));

        Route::controller(ProductController::class)->group((function () {
            Route::post('/product/', 'store');
            Route::post('/product/update', 'update');
            Route::PUT('/product/setactive', 'setActive');
            Route::PUT('/product/setdisable', 'setDisable');
            Route::DELETE('/product/destroy', 'destroy');

            //stock
            Route::get('/product/getstock', 'getStock');
            Route::post('/product/storestock', 'storeStock');
        }));

        Route::controller(TransactionController::class)->group((function () {
            Route::get('/transaction/get', 'get');
            Route::get('/transaction/getbyinvoice', 'getByInvoice');
            Route::get('/transaction/getbyrange', 'getByRange');
            Route::get('/transaction/getbyrecord/{i}', 'getByRecord');
            Route::get('/transaction/getactive', 'getActive'); //Belum dibayar
            Route::get('/transaction/getdone', 'getDone'); //Sudah dibayar
            Route::get('/transaction/getcancel', 'getCancel');
            Route::get('/transaction/getrefund', 'getRefund');
            Route::get('/transaction/getexpired', 'getExpired');
            // Route::PUT('/transaction/update','update');
            Route::PUT('/transaction/setpending', 'setPending');
            Route::PUT('/transaction/setconfirm', 'setConfirm');
            Route::PUT('/transaction/setrefund', 'setRefund');
            Route::PUT('/transaction/setexpired', 'setExpired');
            Route::PUT('/transaction/setcancel', 'setCancel');
        }));

        Route::controller(CommentController::class)->group((function () {
            Route::get('/comment/getbyinvoice', 'getByInvoice');
            Route::get('/comment/getfilebyinvoice', 'getFileByInvoice');
            Route::post('/comment/store', 'store');
            Route::PUT('/comment/setactive', 'setActive');
            Route::PUT('/comment/setdisable', 'setDisable');
            Route::PUT('/comment/delete', 'delete');
            Route::DELETE('/comment/destroy', 'destroy');

            Route::post('/comment/downloadbycode', 'downloadByCode');
        }));

        Route::controller(FileController::class)->group((function () {
            Route::get('/file', 'get');
            Route::get('/file/getbyinvoice', 'getByInvoice');
            Route::get('/file/getbyproduct', 'getByProduct');
            Route::get('/file/getpreviewfile', 'getPreviewFile');
        }));

        Route::controller(JournalAccountController::class)->group((function () {
            Route::get('/journal-account', 'get');
            Route::get('/journal-account/getbycategory', 'getByCategory');
            Route::post('/journal-account/store', 'store');
            Route::put('/journal-account/update', 'update');
            Route::delete('/journal-account/delete', 'delete');
        }));

        Route::controller(JournalCategoryController::class)->group((function () {
            Route::get('/journal-category', 'get');
            Route::post('/journal-category/store', 'store');
            Route::put('/journal-category/update', 'update');
            Route::delete('/journal-category/delete', 'delete');
        }));

        Route::controller(JournalTransactionController::class)->group((function () {
            Route::get('/journal-transaction', 'get');
            Route::get('/journal-transaction/gettxid', 'getTxId');
            Route::get('/journal-transaction/getbyrange', 'getByRange');
            Route::post('/journal-transaction/store', 'store');
            Route::put('/journal-transaction/update', 'update');
            Route::delete('/journal-transaction/delete', 'delete');
        }));
    });
    //END OF ACCESS ADMIN & SUPERUSER ONLY
});



//CLEANSING ROUTE FOR DEVELOPMENT
Route::get('/cleansing', function () {
    Artisan::call('cache:clear');
    Artisan::call('optimize');
    Artisan::call('route:cache');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
    Artisan::call('config:clear');
    return response()->json([
        'msg' => "Cleansing completed",
        'isSuccess' => true
    ]);
});

//Clear cache via URL

//Clear Cache facade value:
Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('cache:clear');
    return '<h1>Cache facade value cleared</h1>';
});

//Reoptimized class loader:
Route::get('/optimize', function () {
    $exitCode = Artisan::call('optimize');
    return '<h1>Reoptimized class loader</h1>';
});

//Route cache:
Route::get('/route-cache', function () {
    $exitCode = Artisan::call('route:cache');
    return '<h1>Routes cached</h1>';
});

//Clear Route cache:
Route::get('/route-clear', function () {
    $exitCode = Artisan::call('route:clear');
    return '<h1>Route cache cleared</h1>';
});

//Clear View cache:
Route::get('/view-clear', function () {
    $exitCode = Artisan::call('view:clear');
    return '<h1>View cache cleared</h1>';
});

//Clear Config cache:
Route::get('/config-cache', function () {
    $exitCode = Artisan::call('config:cache');
    return '<h1>Clear Config cleared</h1>';
});

//END OF CLEANSING FOR DEVELOPMENT