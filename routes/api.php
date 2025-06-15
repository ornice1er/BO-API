<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['namespace' => 'App\Http\Controllers'], function () {

    Route::post('/login', 'UserAuthController@login');
    Route::post('/forgot-password', 'UserAuthController@sendResetPasswordLink');
    Route::post('/recovery-password', 'UserAuthController@recoveryPassword');
    // Route::post('/register', ["UserAuthController@ 'register']);
    Route::post('/verify-otp', 'OtpController@verifyOTP');

    Route::post('/user-notify', 'UserAuthController@notify');
    Route::get('/users-notified', 'UserController@getNotified');
    Route::get('settings', 'SettingController@index');
    Route::post('settings', 'SettingController@update');

    Route::middleware('basic.auth')->group(function () {

        Route::apiResources([
            'users' => 'UserController',
            'municipalities' => 'MunicipalityController',
        ]);
        Route::get('municipalities-ghm', 'MunicipalityController@indexGhm');
    });

    Route::middleware('auth:api')->group(function () {
        Route::get('/user', 'UserAuthController@user');
        Route::get('/logout', 'UserAuthController@logout');
        Route::post('/change-password', 'UserAuthController@changePassword');
        Route::post('/change-first-password', 'UserAuthController@ChangeFirstPassword');
        Route::post('/reset-password', 'UserAuthController@resetPassword');
        Route::post('/user-update', 'UserAuthController@update');
        Route::post('/user-permissions', 'UserAuthController@userPermission');

        Route::post('/user-push-token', 'UserAuthController@addPushToken');
        Route::get('/user-push-token-delete/{id}', 'UserAuthController@deletePushToken');

        Route::post('/upload-file', 'UserAuthController@uploadFile');
        Route::get('/delete-file', 'UserAuthController@deleteFile');

        Route::apiResources([
            'countries' => 'CountryController',
            'departments' => 'DepartmentController',
            'districts' => 'DistrictController',
            'villages' => 'VillageController',
            'roles' => 'RoleController',
            'permissions' => 'PermissionController',
            'project-categories' => 'ProjectCategoryController',
            'projects' => 'ProjectController',
            'user-projects' => 'UserProjectController',
            'menus' => 'MenuController',
            'notifications' => 'NotificationController',
            'promoters' => 'PromoterController',
        ]);

        Route::get('/logs', 'LogController@index');

        Route::get('municipalities-format', 'MunicipalityController@downloadFormat');
        Route::post('municipalities-import', 'MunicipalityController@import');

        Route::get('departments-format', 'DepartmentController@downloadFormat');
        Route::post('departments-import', 'DepartmentController@import');

        Route::post('send-otp', 'OtpController@sendOTP');

        Route::post('generate-pdf', 'ExportController@generatePDF');
        Route::post('generate-excel', 'ExportController@generateExcel');

        Route::get('notifications/{id}/state/{state}', 'NotificationController@changeState');
        Route::post('notifications-search', 'NotificationController@search');

        Route::get('countries/{id}/state/{state}', 'CountryController@changeState');
        Route::post('countries-search', 'CountryController@search');

        Route::get('countries/{id}/state/{state}', 'CountryController@changeState');
        Route::post('countries-search', 'CountryController@search');
        Route::post('/generate-pdf', 'ExportController@generatePDF');

        Route::get('project-categories/{id}/state/{state}', 'ProjectCategoryController@changeState');
        Route::post('project-categories-search', 'ProjectCategoryController@search');

        Route::get('municipalities/{id}/state/{state}', 'MunicipalityController@changeState');
        Route::post('municipalities-search', 'MunicipalityController@search');
        Route::post('municipalities-store-ghm', 'MunicipalityController@storeGhm');
        Route::put('municipalities-update-ghm/{id}', 'MunicipalityController@updateGhm');

        Route::get('districts/{id}/state/{state}', 'DistrictController@changeState');
        Route::post('districts-search', 'DistrictController@search');

        Route::get('villages/{id}/state/{state}', 'VillageController@changeState');
        Route::post('villages-search', 'VillageController@search');

        Route::post('roles-search', 'RoleController@search');
        Route::post('permissions-search', 'PermissionController@search');

        Route::get('user-settings', 'UserSettingController@index');
        Route::put('user-settings', 'UserSettingController@update');

        Route::get('user-projects/{id}/state/{state}', 'UserProjectController@changeState');

        Route::get('menus/{id}/state/{state}', 'MenuController@changeState');

        Route::post('projects-search', 'ProjectController@search');
        Route::get('projects/{id}/state/{state}', 'ProjectController@changeState');

        Route::get('users/{id}/state/{state}', 'UserController@changeState');
        Route::post('users-search', 'UserController@search');
        Route::post('users-candidate', 'UserController@createUser');
        Route::post('users-pr', 'UserController@createUserPR');

        Route::get('users-rh', 'UserController@indexRH');
        Route::get('users-rh/{id}', 'UserController@showRH');
    });
});
