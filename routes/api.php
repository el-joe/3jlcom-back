<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
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


Route::post('send-mobile-code',[ApiController::class, 'sendVerificationCodeToPhone']);
Route::post('verify-mobile-code',[ApiController::class, 'verifyCode']);



Route::get('stripe/status', [ApiController::class, 'stripeStatus']);

// Artisan::call('migrate');
Route::get('get_system_settings', [ApiController::class, 'get_system_settings']);
Route::post('user_signup', [ApiController::class, 'user_signup']);
Route::get('get_languages', [ApiController::class, 'get_languages']);
Route::get('app_payment_status', [ApiController::class, 'app_payment_status']);
Route::post('contct_us', [ApiController::class, 'contct_us']);

Route::get('get_slider', [ApiController::class, 'get_slider']);
Route::get('get_categories', [ApiController::class, 'get_categories']);
Route::get('get_manufacturers', [ApiController::class, 'get_manufacturers']);
Route::get('get_models', [ApiController::class, 'get_models']);
Route::get('get_years', [ApiController::class, 'get_years']);
Route::get('get_cities', [ApiController::class, 'get_cities']);
Route::get('get_areas', [ApiController::class, 'get_areas']);
Route::get('get_advertisement', [ApiController::class, 'get_advertisement']);
Route::get('get_package', [ApiController::class, 'get_package']);

Route::get('get_articles', [ApiController::class, 'get_articles']);
Route::get('get_count_by_cities_categoris', [ApiController::class, 'get_count_by_cities_categoris']);
Route::get('get_property', [ApiController::class, 'get_property']);

Route::post('set_property_total_click', [ApiController::class, 'set_property_total_click']);
Route::get('get_user_recommendation', [ApiController::class, 'get_user_recommendation']);

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('post_property', [ApiController::class, 'post_property']);
    Route::get('get_my_property', [ApiController::class, 'get_my_property']);
    Route::post('update_post_property', [ApiController::class, 'update_post_property']);
    Route::post('delete_property', [ApiController::class, 'delete_property']);
    Route::post('remove_post_images', [ApiController::class, 'remove_post_images']);
    Route::post('update_property_status', [ApiController::class, 'update_property_status']);
    Route::get('get_nearby_properties', [ApiController::class, 'get_nearby_properties']);

    Route::post('request_package',[ApiController::class,'requestPacakge']);


    Route::get('get_user_by_id', [ApiController::class, 'get_user_by_id']);
    Route::post('update_profile', [ApiController::class, 'update_profile']);
    Route::post('delete_user', [ApiController::class, 'delete_user']);

    Route::post('add_edit_user_interest', [ApiController::class, 'add_edit_user_interest']);
    Route::get('user_interests', [ApiController::class, 'user_interests']);
    Route::post('delete_interest', [ApiController::class, 'delete_interest']);


    Route::post('interested_users', [ApiController::class, 'interested_users']);
    Route::get('user_interested_property', [ApiController::class, 'user_interested_property']);

    Route::post('add_favourite', [ApiController::class, 'add_favourite']);
    Route::get('get_favourite_property', [ApiController::class, 'get_favourite_property']);

    Route::post('store_advertisement', [ApiController::class, 'store_advertisement']);
    Route::post('advertisement-request/{id}',[ApiController::class, 'advertisementRequest']);
    Route::post('delete_advertisement', [ApiController::class, 'delete_advertisement']);

    Route::post('set_property_inquiry', [ApiController::class, 'set_property_inquiry']);
    Route::get('get_property_inquiry', [ApiController::class, 'get_property_inquiry']);
    Route::post('delete_inquiry', [ApiController::class, 'delete_inquiry']);

    Route::post('send_message', [ApiController::class, 'send_message']);
    // Route::post('delete_chat_message', [ApiController::class, 'delete_chat_message']);
    Route::get('get_messages', [ApiController::class, 'get_messages']);
    Route::get('get_chats', [ApiController::class, 'get_chats']);

    Route::get('get_report_reasons', [ApiController::class, 'get_report_reasons']);
    Route::post('add_reports', [ApiController::class, 'add_reports']);

    Route::post('user_purchase_package', [ApiController::class, 'user_purchase_package']);
    // Route::post('createPaymentIntent', [ApiController::class, 'createPaymentIntent']);
    // Route::post('confirmPayment', [ApiController::class, 'confirmPayment']);

    Route::post('stripe/generate-payment-url',[ApiController::class, 'generatePaymentUrl']);

    Route::get('get_payment_details', [ApiController::class, 'get_payment_details']);
    Route::get('get_payment_settings', [ApiController::class, 'get_payment_settings']);
    Route::get('paypal', [ApiController::class, 'paypal']);

    Route::get('get_notification_list', [ApiController::class, 'get_notification_list']);

    Route::get('get_limits', [ApiController::class, 'get_limits']);

    Route::get('get_agents_details', [ApiController::class, 'get_agents_details']);


});
