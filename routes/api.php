<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SpecializationController;
use App\Http\Controllers\ServiceProviderController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['api'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-email', [AuthController::class, 'VerifyEmail']);
    Route::post('/send-reset-code', [AuthController::class, 'sendResetCode']);
    Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::get('/service-provider/{userId}', [ServiceProviderController::class, 'getServiceProviderProfile']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/user-profile', [ProfileController::class, 'userProfile']);
        Route::post('/upload-profile-image', [ProfileController::class, 'uploadProfileImage']);


        Route::post('/save-service-provider-details', [ServiceProviderController::class, 'saveServiceProviderDetails']);
        Route::post('/upload-user-document', [ServiceProviderController::class, 'uploadUserDocument']);
        // Route::get('/user-profile', [ServiceProviderController::class, 'getUserProfile']);
        Route::delete('/user-document/{documentId}', [ServiceProviderController::class, 'deleteUserDocument']);


        Route::post('/save-basic-details', [ProfileController::class, 'saveBasicDetails']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);


        Route::post('/save-facility', [FacilityController::class, 'saveFacility']);
        Route::post('/save-facility-specialties', [FacilityController::class, 'saveFacilitySpecialties']);
        Route::post('/upload-facility-logo', [FacilityController::class, 'uploadFacilityLogo']);
        Route::post('/upload-facility-cover-image', [FacilityController::class, 'uploadFacilityCoverImage']);
        Route::get('/facilities', [FacilityController::class, 'getFacilities']);
        Route::get('/facilities/{id}', [FacilityController::class, 'getFacility']);
        Route::put('/facilities/{id}', [FacilityController::class, 'updateFacility']);
        Route::delete('/facilities/{id}', [FacilityController::class, 'deleteFacility']);


        Route::get('/specializations/active-for-facility', [SpecializationController::class, 'getActiveForFacility']);
        Route::get('/specializations', [SpecializationController::class, 'getSpecializations']);
    });
});
