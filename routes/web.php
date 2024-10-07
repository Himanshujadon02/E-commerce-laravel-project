<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\TempImagesController;
use App\Http\Controllers\admin\subCategoryController;
use App\Http\Controllers\admin\BrandController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


Route::get('/', function () {
    return view('welcome');
});




Route::group(['prefix' => 'admin'], function () {


    Route::group(['middleware' => 'admin.guest'], function () {
        Route::get('/login', [AdminLoginController::class, 'index'])->name('admin.login');
        Route::post('/authenticate', [AdminLoginController::class, 'authenticate'])->name('admin.authenticate');
    });


    Route::group(['middleware' => 'admin.auth'], function () {
        Route::get('/dashboard', [HomeController::class, 'index'])->name('admin.dashboard');
        Route::get('/logout', [HomeController::class, 'logout'])->name('admin.logout');

        // category
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.list');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories/store', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}/', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}/', [CategoryController::class, 'destroy'])->name('categories.delete');

        Route::post('/upload-temp-image', [TempImagesController::class, 'create'])->name('temp-images-create');
        Route::get('/getSlug', function (Request $request) {
            $slug = '';
            if (!empty($request->title)) {
                $slug = Str::slug($request->title);
            }

            return response()->json([
                'status' => true,  // return Boolean instead of string
                'slug' => $slug
            ]);
        })->name('getSlug');


        // SubCategory
        Route::get('/sub-categories',[SubCategoryController::class,'index'])->name('sub-categories.list');
        Route::get('/sub-categories/create',[SubCategoryController::class,'create'])->name('sub-categories.create');
        Route::post('/sub-categories/store', [SubCategoryController::class, 'store'])->name('sub-categories.store');
        Route::get('/sub-categories/{subCategory}/edit', [SubCategoryController::class, 'edit'])->name('sub-categories.edit');
        Route::put('/sub-categories/{subCategory}/', [SubCategoryController::class, 'update'])->name('sub-categories.update');
        Route::delete('/sub-categories/{subCategory}/', [SubCategoryController::class, 'destroy'])->name('sub-categories.delete');



        //brands
        Route::get('/brand',[BrandController::class,'index'])->name('brand.list');
        Route::get('/brand/create',[BrandController::class,'create'])->name('brand.create');
        Route::post('/brand/store', [BrandController::class, 'store'])->name('brand.store');
        Route::get('/brand/{brand}/edit', [BrandController::class, 'edit'])->name('brand.edit');
        Route::put('/brand/{brand}/', [BrandController::class, 'update'])->name('brand.update');
        Route::delete('/brand/{brand}/', [BrandController::class, 'destroy'])->name('brand.delete');
        Route::delete('/brand/{brand}/', [BrandController::class, 'destroy'])->name('brand.delete');
    });
});