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


    Route::post('auth/signin', 'AuthController@signin');
    Route::post('auth/signin2', 'AuthController@signin2');
    Route::get('auth', 'AuthController@certifier');
    Route::get('files/by-prestation/{name}', 'FilesController@getFromPrestationName');

    Route::post('eservice', 'EServiceController@store');
    Route::get('eservice/zip', 'EServiceController@getZip');
    Route::post('eservice/post-2', 'EServiceController@store2');
    Route::post('eservice/files/deleting', 'EServiceController@removeFile');
    Route::get('eservice/{code}/{slug}/{edition}', 'EServiceController@show');
    Route::get('detab/{id}', 'DetabController@show');
    Route::post('eservice/requete/update', 'EServiceController@update');

    Route::get('eservice/with-prestations', 'EServiceController@getEntityWithPrestations');
    Route::post('eservice/zip-files', 'EServiceController@downloadZip'); 


    Route::middleware('basic.auth')->group(function () { });

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

        Route::get('auth/change-password', 'AuthController@changePassword');
        Route::get('unity-admin/principal/all', 'UniteAdminController@principal');

        Route::apiResources([
            'countries' => 'CountryController',
            'departments' => 'DepartmentController',
            'districts' => 'DistrictController',
            'villages' => 'VillageController',
            'roles' => 'RoleController',
            'permissions' => 'PermissionController',
            'notifications' => 'NotificationController',
            "type-entity"=>"TypeEntiteController",
            "entity"=>"EntiteAdminController",
            "type-unity-admin"=>"TypeUniteAdminController",
            "unity-admin"=>"UniteAdminController",
            "officers"=>"AgentController",
            "users"=>"UserController",
            "profile"=>"ProfileController",
            "prestation"=>"PrestationController",
            "files"=>"FilesController",
            "fonction-agent"=>"FonctionAgentController",
            "requete"=>"RequeteController",
            "response"=>"ReponseController",
            "affectation"=>"AffectationController",
            "dash"=>"DashboardController",
            "detab"=>"DetabController",
            "company"=>"CompanyController",
            "establish-personal"=>"PersonalController",
            "contrat-p"=>"ContratPController",
            "agenda"=>"AgendaController",
        ]);

        Route::get('/logs', 'LogController@index');

        Route::get('municipalities-format', 'MunicipalityController@downloadFormat');
        Route::post('municipalities-import', 'MunicipalityController@import');

        Route::get('departments-format', 'DepartmentController@downloadFormat');
        Route::post('departments-import', 'DepartmentController@import');

        Route::post('send-otp', 'OtpController@sendOTP');

        Route::get('notifications/{id}/state/{state}', 'NotificationController@changeState');
        Route::post('notifications-search', 'NotificationController@search');

        Route::get('countries/{id}/state/{state}', 'CountryController@changeState');
        Route::post('countries-search', 'CountryController@search');

        Route::get('countries/{id}/state/{state}', 'CountryController@changeState');
        Route::post('countries-search', 'CountryController@search');

        Route::get('municipalities/{id}/state/{state}', 'MunicipalityController@changeState');
        Route::post('municipalities-search', 'MunicipalityController@search');

        Route::get('districts/{id}/state/{state}', 'DistrictController@changeState');
        Route::post('districts-search', 'DistrictController@search');

        Route::get('villages/{id}/state/{state}', 'VillageController@changeState');
        Route::post('villages-search', 'VillageController@search');

        Route::post('roles-search', 'RoleController@search');
        Route::post('permissions-search', 'PermissionController@search');

        Route::get('user-settings', 'UserSettingController@index');
        Route::put('user-settings', 'UserSettingController@update');

        Route::get('user-projects/{id}/state/{state}', 'UserProjectController@changeState');


        Route::get('users/{id}/state/{state}', 'UserController@changeState');
        Route::post('users-search', 'UserController@search');

        Route::post('eservice', 'EServiceController@store');
        Route::get('eservice/zip', 'EServiceController@getZip');
        Route::post('eservice/post-2', 'EServiceController@store2');
        Route::post('eservice/files/deleting', 'EServiceController@removeFile');
        Route::get('eservice/{code}/{slug}/{edition}', 'EServiceController@show');
        Route::get('detab/{id}', 'DetabController@show');
        Route::post('eservice/requete/update', 'EServiceController@update');

        Route::get('eservice/with-prestations', 'EServiceController@getEntityWithPrestations');
        Route::post('eservice/zip-files', 'EServiceController@downloadZip');


        Route::get('unity-admin/principal/all', 'UniteAdminController@principal');

        Route::get('requete/byPrestationPending/{id}', 'RequeteController@getByPrestationPending');
        Route::get('requete/byPrestation/{slug}', 'RequeteController@getByPrestation');
        Route::get('requete/byPrestation/{slug}/all', 'RequeteController@getByPrestationAll');
        Route::get('requete/byPrestation/{slug}/agenda', 'RequeteController@getForAgenda');
        Route::get('requete/relance/{id}', 'RequeteController@relance');

        Route::get('requete/correction/byPrestation/{slug}', 'RequeteController@getCorrectionByPrestation');
        Route::get('prestation-details/{id}', 'PrestationController@show');

        Route::post('requete/response/store', 'RequeteController@storeResponse');
        Route::get('requete/get-one/{code}/{slug}', 'RequeteController@getOne');
        Route::get('requete/treatment/{id}/{slug}', 'RequeteController@show');
        Route::post('requete/generate/{id}/{slug}', 'RequeteController@createPDF');
        Route::post('contrat-p/file/upload', 'ContratPController@storeFile');

        Route::post('response/decline/store', 'ReponseController@decline');

        Route::post('response/need-correction', 'ReponseController@needCorrection');

        Route::post('download', 'RequeteController@getDownload');
    });
});
