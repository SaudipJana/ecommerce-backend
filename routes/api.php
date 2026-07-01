<?php

use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\ShippingController;
use App\Http\Controllers\admin\SizeController;
use App\Http\Controllers\admin\TempImageController;
use App\Http\Controllers\front\AccountController;
use App\Http\Controllers\front\OrderController as FrontOrderController;
use App\Http\Controllers\front\ProductController as FrontProductController;
use App\Http\Controllers\front\ShippingController as FrontShippingController;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('admin/login', [AuthController::class, 'authenticate']);

Route::get('get-latest-products', [FrontProductController::class, 'latestProducts']);
Route::get('get-featured-products', [FrontProductController::class, 'featuredProducts']);
Route::get('get-categories', [FrontProductController::class, 'getCategories']);
Route::get('get-brands', [FrontProductController::class, 'getBrands']);
Route::get('get-products', [FrontProductController::class, 'getProducts']);
Route::get('get-product/{id}', [FrontProductController::class, 'getProduct']);

Route::post('register', [AccountController::class, 'register']);
Route::post('login', [AccountController::class, 'authenticate']);

Route::get('get-shipping-charges', [FrontShippingController::class, 'getShipping']);

Route::group(['middleware' => ['auth:sanctum', 'checkUserRole']], function(){
    Route::post('save-order', [FrontOrderController::class, 'saveOrder']);
    Route::get('get-orders', [AccountController::class, 'getOrders']);
    Route::get('get-order-details/{id}', [AccountController::class, 'getOrderDetails']);
    Route::post('update-profile', [AccountController::class, 'updateProfile']);
    Route::get('get-profile-details', [AccountController::class, 'getAccountDetails']);
    Route::post('create-payment-intent', [FrontOrderController::class, 'createPaymentIntent']);
});

Route::group(['middleware' => ['auth:sanctum', 'checkAdminRole']], function(){
    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class);
    Route::get('sizes', [SizeController::class, 'index']);
    Route::resource('products', ProductController::class);
    Route::post('temp-images', [TempImageController::class, 'store']);
    Route::post('add-product-image', [ProductController::class, 'addProductImage']);
    Route::get('update-product-default-image', [ProductController::class, 'updateDefaultImage']);
    Route::delete('delete-product-image/{id}', [ProductController::class, 'deleteProductImage']);

    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{id}', [OrderController::class, 'details']);
    Route::post('orders/update/{id}', [OrderController::class, 'updateOrder']);

    Route::get('get-shipping', [ShippingController::class, 'getShipping']);
    Route::post('save-shipping', [ShippingController::class, 'saveShipping']);

}); 