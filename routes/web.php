<?php

use App\Http\Controllers\AdvertisementController;

use App\Http\Controllers\AreaMeasurementController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\BedroomController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PropertController;
use App\Http\Controllers\PropertysInquiryController;
use App\Http\Controllers\PersonalizedController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\HouseTypeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ParameterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\ReportReasonController;
use App\Models\Payments;
use App\Models\PropertysInquiry;
use Illuminate\Support\Facades\Artisan;
use Spatie\Browsershot\Browsershot;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('export-pdf/{file}',function ($file) {
    $url = request('url');

    if(!$url) return 'Error';

    Browsershot::url($url)->setIncludePath('$PATH:/usr/local/lib/node_modules')->save('export-pdf/'.$file);

    return url('export-pdf/'.$file);
});


Route::get('/', function () {
    return view('auth.login');
});

Route::get('/clear', function () {
    Artisan::call('cache:clear');
    return redirect()->back();
});

/*Route::get('/new-migrate', function () {
    Artisan::call('migrate');
    return redirect()->back();
});


Route::get('/fresh-migrate', function () {
    Artisan::call('migrate:fresh');
    return redirect()->back();
});*/

Route::get('customer-privacy-policy', [SettingController::class, 'show_privacy_policy'])->name('customer-privacy-policy');


Route::get('customer-terms-conditions', [SettingController::class, 'show_terms_conditions'])->name('customer-terms-conditions');


Route::get('privacypolicy', [HomeController::class, 'privacy_policy']);
Route::post('/webhook/razorpay', [WebhookController::class, 'razorpay']);
Route::post('/webhook/paystack', [WebhookController::class, 'paystack']);
Route::post('/webhook/paypal', [WebhookController::class, 'paypal']);
Route::post('/webhook/stripe', [WebhookController::class, 'stripe']);



Route::middleware(['auth', 'checklogin'])->group(function () {
    Route::group(['middleware' => 'language'], function () {

        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('view:cache');


        Route::get('dashboard', [App\Http\Controllers\HomeController::class, 'blank_dashboard'])->name('dashboard');
        Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
        Route::get('about-us', [SettingController::class, 'index']);
        Route::get('privacy-policy', [SettingController::class, 'index']);
        Route::get('terms-conditions', [SettingController::class, 'index']);
        Route::get('system-settings', [SettingController::class, 'index']);
        Route::get('firebase_settings', [SettingController::class, 'index']);
        Route::get('system_version', [SettingController::class, 'index']);
        Route::post('firebase-settings', [SettingController::class, 'firebase_settings']);
        Route::get('system_version', [SettingController::class, 'system_version']);

        Route::post('system_version_setting', [SettingController::class, 'system_version_setting']);

        /// STAT :: PASSWORD ROUTE
        Route::get('change-password', [App\Http\Controllers\HomeController::class, 'change_password'])->name('changepassword');
        Route::post('check-password', [App\Http\Controllers\HomeController::class, 'check_password'])->name('checkpassword');
        Route::post('store-password', [App\Http\Controllers\HomeController::class, 'store_password'])->name('changepassword.store');
        Route::get('changeprofile', [HomeController::class, 'changeprofile'])->name('changeprofile');
        Route::post('updateprofile', [HomeController::class, 'update_profile'])->name('updateprofile');
        Route::post('firebase_messaging_settings', [HomeController::class, 'firebase_messaging_settings'])->name('firebase_messaging_settings');

        /// END :: PASSWORD ROUTE

        Route::post('settings', [SettingController::class, 'settings']);
        Route::post('set_settings', [SettingController::class, 'system_settings']);
        Route::resource('language', LanguageController::class);
        Route::get('language_list', [LanguageController::class, 'show']);
        Route::post('language_update', [LanguageController::class, 'update'])->name('language_update');
        Route::get('language-destory/{id}', [LanguageController::class, 'destroy'])->name('language.destroy');
        Route::get('set-language/{lang}', [LanguageController::class, 'set_language']);
        Route::get('downloadPanelFile', [LanguageController::class, 'downloadPanelFile'])->name('downloadPanelFile');
        Route::get('downloadAppFile', [LanguageController::class, 'downloadAppFile'])->name('downloadAppFile');


        Route::get('calculator', function () {
            return view('Calculator.calculator');
        });

        Route::get('getPaymentList', [PaymentController::class, 'get_payment_list']);
        Route::get('payment', [PaymentController::class, 'index']);

        Route::resource('users', UserController::class);
        Route::post('users-update', [UserController::class, 'update']);
        Route::post('users-reset-password', [UserController::class, 'resetpassword']);

        Route::get('userList', [UserController::class, 'userList']);
        Route::resource('customer', CustomersController::class);
        Route::get('customerList', [CustomersController::class, 'customerList']);
        Route::post('customerstatus', [CustomersController::class, 'update'])->name('customer.customerstatus');
        Route::post('updatePackage', [CustomersController::class, 'updatePackage'])->name('updatePackage');

        Route::resource('slider', SliderController::class);
        Route::post('slider-order', [SliderController::class, 'update'])->name('slider.slider-order');
        Route::get('slider-destory/{id}', [SliderController::class, 'destroy'])->name('slider.destroy');
        Route::get('get-property-by-category', [SliderController::class, 'getPropertyByCategory'])->name('slider.getpropertybycategory');
        Route::get('sliderList', [SliderController::class, 'sliderList']);

        Route::resource('article', ArticleController::class);
        Route::get('article_list', [ArticleController::class, 'show']);
        Route::get('article-destory/{id}', [ArticleController::class, 'destroy'])->name('article.destroy');


        Route::resource('advertisement', AdvertisementController::class);
        Route::get('customer-properties/{id}', [AdvertisementController::class, 'customerProperties']);
        Route::get('advertisement_list', [AdvertisementController::class, 'show']);
        Route::post('advertisement-status', [AdvertisementController::class, 'updateStatus'])->name('advertisement.updateadvertisementstatus');
        Route::post('adv-status-update', [AdvertisementController::class, 'update'])->name('adv-status-update');

        Route::get('package-requests', [PackageController::class,'packageRequests']);
        Route::get('package_request_list', [PackageController::class, 'showRequests']);
        Route::get('subscription-request/{id}/{status}', [PackageController::class, 'changeRequestStatus']);


        Route::get('verification-requests', [CustomersController::class,'verificationRequests']);
        Route::get('verification_request_list', [CustomersController::class, 'showRequests']);
        Route::get('verification-request/{id}/{status}', [CustomersController::class, 'changeRequestStatus']);


        Route::resource('package', PackageController::class);
        Route::get('package_list', [PackageController::class, 'show']);
        Route::post('package-update', [PackageController::class, 'update']);
        Route::post('package-status', [PackageController::class, 'updatestatus'])->name('package.updatestatus');

        Route::resource('categories', CategoryController::class);
        Route::get('categoriesList', [CategoryController::class, 'categoryList']);
        Route::post('categories-update', [CategoryController::class, 'update']);
        Route::post('categories-status', [CategoryController::class, 'updateCategory'])->name('customer.categoriesstatus');

        Route::resource('manufacturers', ManufacturerController::class);
        Route::get('manufacturersList', [ManufacturerController::class, 'manufacturerList']);
        Route::post('manufacturers-update', [ManufacturerController::class, 'update']);
        Route::post('manufacturers-status', [ManufacturerController::class, 'updateManufacturer'])->name('customer.manufacturersstatus');

        Route::resource('models', ModelController::class);
        Route::get('modelsList', [ModelController::class, 'modelList']);
        Route::post('models-update', [ModelController::class, 'update']);
        Route::post('models-status', [ModelController::class, 'updateModel'])->name('customer.modelsstatus');

        Route::resource('parameters', ParameterController::class);
        Route::get('parameter-list', [ParameterController::class, 'show']);
        Route::post('parameter-update', [ParameterController::class, 'update']);
        Route::post('parameter-findIt', [ParameterController::class, 'updateParameter'])->name('customer.parameterFindIt');

        Route::resource('housetype', HouseTypeController::class);
        Route::get('housetypeList', [HouseTypeController::class, 'typeList']);
        Route::post('house-type-update', [HouseTypeController::class, 'update']);


        Route::resource('measurement', UnitController::class);
        Route::get('measurementList', [UnitController::class, 'measurementList']);
        Route::post('measurement-update', [UnitController::class, 'update']);

        /// STAT :: PROPERTY ROUTE
        Route::resource('property', PropertController::class);
        Route::get('getPropertyList', [PropertController::class, 'getPropertyList']);
        Route::post('property-status', [PropertController::class, 'updateStatus'])->name('property.updatepropertystatus');
        Route::post('property-gallery', [PropertController::class, 'removeGalleryImage'])->name('property.removeGalleryImage');
        Route::get('get-state-by-country', [PropertController::class, 'getStatesByCountry'])->name('property.getStatesByCountry');
        Route::get('property-destory/{id}', [PropertController::class, 'destroy'])->name('property.destroy');

        Route::get('updateFCMID', [UserController::class, 'updateFCMID']);
        /// END :: PROPERTY ROUTE

        /// STAT :: PROPERTY INQUIRY
        Route::resource('property-inquiry', PropertysInquiryController::class);
        Route::get('getPropertyInquiryList', [PropertysInquiryController::class, 'getPropertyInquiryList']);
        Route::post('property-inquiry-status', [PropertysInquiryController::class, 'updateStatus'])->name('property-inquiry.updateStatus');
        /// ENND :: PROPERTY INQUIRY

        /// STAT :: Presonalized
        Route::resource('personalized', PersonalizedController::class);
        Route::get('getPersonalizedList', [PersonalizedController::class, 'getPersonalizedList']);
        /// ENND :: Presonalized

        /// START :: REPORTREASON
        Route::resource('report-reasons', ReportReasonController::class);
        Route::get('report-reasons-list', [ReportReasonController::class, 'show']);
        Route::post('report-reasons-update', [ReportReasonController::class, 'update']);
        Route::get('report-reasons-destroy/{id}', [ReportReasonController::class, 'destroy'])->name('reasons.destroy');
        Route::get('users_reports', [ReportReasonController::class, 'users_reports']);
        Route::get('user_reports_list', [ReportReasonController::class, 'user_reports_list']);
        /// END :: REPORTREASON

        /// START :: CHAT ROUTE
        Route::get('chat', function () {
            return view('chat');
        });
        Route::get('getChatList', [ChatController::class, 'getChats']);
        Route::post('store_chat', [ChatController::class, 'store']);
        Route::get('getAllMessage', [ChatController::class, 'getAllMessage']);
        /// END :: PROPERTY INQUIRY

        Route::get('chat', function () {
            return view('chat');
        });
        /// START :: NOTIFICATION
        Route::resource('notification', NotificationController::class);
        Route::get('notificationList', [NotificationController::class, 'notificationList']);
        Route::get('notification-delete', [NotificationController::class, 'destroy']);
        Route::post('notification-multiple-delete', [NotificationController::class, 'multiple_delete']);
        /// END :: NOTIFICATION
    });
});

Auth::routes();
