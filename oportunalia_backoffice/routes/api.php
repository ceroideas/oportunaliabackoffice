<?php

use App\Http\Controllers\Admin\ActiveController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AuctionController;
use App\Http\Controllers\reportController;
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

Route::get('/deposits', [\App\Http\Controllers\Web\DepositController::class, 'all']);

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth',
], function () {
   
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login/admin', [AuthController::class, 'loginAdmin']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/register', [AuthController::class, 'register']);
    //Route::post('/register-campaign', [AuthController::class, 'registerCampaign']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Route::get('/user-profile', [AuthController::class, 'userProfile']);
    Route::post('/contact', [AuthController::class, 'contact']);

    Route::get('/activate/{token}', [\App\Http\Controllers\Web\UserController::class, 'confirm']);

    Route::post('/recover-password', [AuthController::class, 'petitionRecover']);
    Route::post('/reset-password/{token}', [AuthController::class, 'recoverPassword']);

});

Route::group([
    'middleware' => 'adminRequired',
    'prefix' => 'admin'
], function () {

    // Route::get('/migrate/users', [\App\Http\Controllers\Admin\MigrationController::class, 'migrateUsers']);
    // Route::get('/migrate/representations', [\App\Http\Controllers\Admin\MigrationController::class, 'migrateRepresentations']);
    // Route::get('/migrate/active_categories', [\App\Http\Controllers\Admin\MigrationController::class, 'migrateActiveCategories']);
    // Route::get('/migrate/auctions', [\App\Http\Controllers\Admin\MigrationController::class, 'migrateAuctions']);
    // Route::get('/migrate/urls', [\App\Http\Controllers\Admin\MigrationController::class, 'changeUrlBase']);
    // Route::get('/migrate/deposits', [\App\Http\Controllers\Admin\MigrationController::class, 'migrateDeposits']);
    // Route::get('/migrate/bids', [\App\Http\Controllers\Admin\MigrationController::class, 'migrateBids']);

    Route::get('/user', [UserController::class, 'listAll']);
    Route::post('/user', [UserController::class, 'create']);
    Route::get('/user/{id}', [UserController::class, 'detail']);
    Route::get('/user/{id}/representation', [UserController::class, 'representations']);
    Route::get('/user/{id}/offers', [UserController::class, 'directOffers']);
    Route::get('/user/{id}/bids', [UserController::class, 'auctions']);
    Route::get('/user/{id}/deposits', [UserController::class, 'deposits']);
    Route::post('/user/{id}', [UserController::class, 'edit']);
    Route::put('/user/{id}/confirm', [UserController::class, 'confirm']);
    Route::put('/user/{id}/validate', [UserController::class, 'validate']);
    Route::delete('/user/{id}', [UserController::class, 'deletePermanentAdmin']);
    Route::delete('/user/{id}/documentone', [UserController::class, 'deleteDocumentOne']);
    Route::delete('/user/{id}/documenttwo', [UserController::class, 'deleteDocumentTwo']);
    Route::get('/user/export/interests', [UserController::class, 'exportInterests']);
    Route::get('/user/export/{type}', [UserController::class, 'export']);
    Route::get('/role', [RoleController::class, 'list']);

    Route::get('/representation', [\App\Http\Controllers\Admin\RepresentationController::class, 'list']);
    Route::post('/representation', [\App\Http\Controllers\Admin\RepresentationController::class, 'create']);
    Route::get('/representation/{id}', [\App\Http\Controllers\Admin\RepresentationController::class, 'detail']);
    Route::post('/representation/{id}', [\App\Http\Controllers\Admin\RepresentationController::class, 'edit']);
    Route::put('/representation/{id}/validate', [\App\Http\Controllers\Admin\RepresentationController::class, 'validate']);
    Route::delete('/representation/{id}', [\App\Http\Controllers\Admin\RepresentationController::class, 'delete']);
    Route::get('/representation/export/{type}', [\App\Http\Controllers\Admin\RepresentationController::class, 'export']);

    Route::get('/active_category', [\App\Http\Controllers\Admin\ActiveCategoryController::class, 'listAll']);
    Route::post('/active_category', [\App\Http\Controllers\Admin\ActiveCategoryController::class, 'create']);
    Route::get('/active_category/{id}', [\App\Http\Controllers\Admin\ActiveCategoryController::class, 'detail']);
    Route::delete('/active_category/{id}', [\App\Http\Controllers\Admin\ActiveCategoryController::class, 'deleteSoft']);
    Route::post('/active_category/{id}', [\App\Http\Controllers\Admin\ActiveCategoryController::class, 'edit']);
    Route::get('/active_category/export/{type}', [\App\Http\Controllers\Admin\ActiveCategoryController::class, 'export']);
    Route::get('/active/conditions', [\App\Http\Controllers\Admin\ActiveConditionController::class, 'list']);

    Route::get('/active', [\App\Http\Controllers\Admin\ActiveController::class, 'listAll']);
    Route::post('/active', [\App\Http\Controllers\Admin\ActiveController::class, 'create']);
    Route::get('/active/{id}', [\App\Http\Controllers\Admin\ActiveController::class, 'detail']);
    Route::post('/active/{id}', [\App\Http\Controllers\Admin\ActiveController::class, 'edit']);
    Route::delete('/active/{id}', [\App\Http\Controllers\Admin\ActiveController::class, 'delete']);
    Route::delete('/active/image/{id}', [\App\Http\Controllers\Admin\ActiveController::class, 'deleteImage']);
    Route::get('/active/export/{type}', [\App\Http\Controllers\Admin\ActiveController::class, 'export']);
    Route::get('/active/duplicate/{id}', [\App\Http\Controllers\Admin\ActiveController::class, 'duplicate']);

    Route::get('/max_id', [\App\Http\Controllers\Admin\ActiveController::class, 'maxId']);

    Route::get('auction', [\App\Http\Controllers\Admin\AuctionController::class, 'listAll']);
    Route::post('auction', [\App\Http\Controllers\Admin\AuctionController::class, 'create']);
    Route::get('auction/status', [\App\Http\Controllers\Admin\AuctionStatusController::class, 'list']);
    Route::get('auction/{id}', [\App\Http\Controllers\Admin\AuctionController::class, 'detail']);
    Route::get('auction/{id}/bids', [\App\Http\Controllers\Admin\AuctionController::class, 'bids']);
    Route::get('auction/{id}/history', [\App\Http\Controllers\Admin\AuctionController::class, 'history']);
    Route::get('auction/{id}/deposits', [\App\Http\Controllers\Admin\AuctionController::class, 'deposits']);
    Route::get('auction/{id}/activity', [\App\Http\Controllers\Admin\AuctionController::class, 'activity']);
    Route::put('auction/{id}/featured', [\App\Http\Controllers\Admin\AuctionController::class, 'featured']);
    Route::put('auction/{id}/asignado', [\App\Http\Controllers\Admin\AuctionController::class, 'asignado']);
    Route::post('auction/{id}', [\App\Http\Controllers\Admin\AuctionController::class, 'edit']);
    Route::delete('auction/{id}', [\App\Http\Controllers\Admin\AuctionController::class, 'delete']);
    Route::get('auction/{id}/final_report', [\App\Http\Controllers\Admin\AuctionController::class, 'report']);
    Route::get('auction/{id}/direct_sale_final_report', [\App\Http\Controllers\Admin\AuctionController::class, 'direct_sale_report']);
    Route::get('auction/{id}/cesion_final_report', [\App\Http\Controllers\Admin\AuctionController::class, 'cesion_report']);
    Route::get('auction/export/{type}', [\App\Http\Controllers\Admin\AuctionController::class, 'export']);
    Route::get('auction/duplicate/{id}', [\App\Http\Controllers\Admin\AuctionController::class, 'duplicate']);
    Route::put('auction/{id}/deletedocument', [\App\Http\Controllers\Admin\AuctionController::class, 'deleteDocument']);


    Route::get('direct_sale', [\App\Http\Controllers\Admin\DirectSaleOfferController::class, 'listAll']);
    Route::post('direct_sale', [\App\Http\Controllers\Admin\DirectSaleOfferController::class, 'create']);
    Route::get('direct_sale/status', [\App\Http\Controllers\Admin\AuctionStatusController::class, 'listPayment']);
    Route::get('direct_sale/{id}', [\App\Http\Controllers\Admin\DirectSaleOfferController::class, 'detail']);
    Route::get('direct_sale/{id}/offers', [\App\Http\Controllers\Admin\DirectSaleOfferController::class, 'listUsers']);
    Route::get('direct_sale/{id}/history', [\App\Http\Controllers\Admin\DirectSaleOfferController::class, 'history']);
    Route::put('direct_sale/{id}/featured', [\App\Http\Controllers\Admin\DirectSaleOfferController::class, 'featured']);
    Route::put('direct_sale/{id}/asignado', [\App\Http\Controllers\Admin\DirectSaleOfferController::class, 'asignado']);
    Route::post('direct_sale/{id}', [\App\Http\Controllers\Admin\DirectSaleOfferController::class, 'edit']);
    Route::delete('direct_sale/{id}', [\App\Http\Controllers\Admin\DirectSaleOfferController::class, 'delete']);
    Route::get('direct_sale/export/{type}', [\App\Http\Controllers\Admin\DirectSaleOfferController::class, 'export']);
    Route::get('direct_sale/duplicate/{id}', [\App\Http\Controllers\Admin\DirectSaleOfferController::class, 'duplicate']);
    Route::put('offer/{id}/status', [\App\Http\Controllers\Admin\DirectSaleOfferController::class, 'validate']);


    //Cesiones de remate
    Route::get('cesion/status', [\App\Http\Controllers\Admin\AuctionStatusController::class, 'listPaymentCesion']);

    Route::get('cesion', [\App\Http\Controllers\Admin\CesionController::class, 'listAll']);
    Route::post('cesion', [\App\Http\Controllers\Admin\CesionController::class, 'create']);
    Route::get('cesion/{id}', [\App\Http\Controllers\Admin\CesionController::class, 'detail']);
    Route::get('cesion/{id}/offers', [\App\Http\Controllers\Admin\CesionController::class, 'listUsers']);
    Route::get('cesion/{id}/history', [\App\Http\Controllers\Admin\CesionController::class, 'history']);
    Route::put('cesion/{id}/featured', [\App\Http\Controllers\Admin\CesionController::class, 'featured']);
    Route::put('cesion/{id}/asignado', [\App\Http\Controllers\Admin\CesionController::class, 'asignado']);
    Route::post('cesion/{id}', [\App\Http\Controllers\Admin\CesionController::class, 'edit']);
    Route::delete('cesion/{id}', [\App\Http\Controllers\Admin\CesionController::class, 'delete']);
    Route::get('cesion/export/{type}', [\App\Http\Controllers\Admin\CesionController::class, 'export']);
    Route::get('cesion/duplicate/{id}', [\App\Http\Controllers\Admin\CesionController::class, 'duplicate']);
    Route::put('offer_cesion/{id}/status', [\App\Http\Controllers\Admin\CesionController::class, 'validate']);

    //Cesiones de crédito
    Route::get('credit-assignment', [\App\Http\Controllers\Admin\CreditAssignmentController::class, 'listAll']);
    Route::post('credit-assignment', [\App\Http\Controllers\Admin\CreditAssignmentController::class, 'create']);
    Route::get('credit-assignment/{id}', [\App\Http\Controllers\Admin\CreditAssignmentController::class, 'detail']);
    Route::get('credit-assignment/{id}/offers', [\App\Http\Controllers\Admin\CreditAssignmentController::class, 'listUsers']);
    Route::get('credit-assignment/{id}/history', [\App\Http\Controllers\Admin\CreditAssignmentController::class, 'history']);
    Route::put('credit-assignment/{id}/featured', [\App\Http\Controllers\Admin\CreditAssignmentController::class, 'featured']);
    Route::put('credit-assignment/{id}/asignado', [\App\Http\Controllers\Admin\CreditAssignmentController::class, 'asignado']);
    Route::post('credit-assignment/{id}', [\App\Http\Controllers\Admin\CreditAssignmentController::class, 'edit']);
    Route::delete('credit-assignment/{id}', [\App\Http\Controllers\Admin\CreditAssignmentController::class, 'delete']);
    Route::get('credit-assignment/export/{type}', [\App\Http\Controllers\Admin\CreditAssignmentController::class, 'export']);
    Route::get('credit-assignment/duplicate/{id}', [\App\Http\Controllers\Admin\CreditAssignmentController::class, 'duplicate']);
    Route::put('offer_credit_assignment/{id}/status', [\App\Http\Controllers\Admin\CreditAssignmentController::class, 'validate']);
    Route::get('credit-assignment/{id}/deposits', [\App\Http\Controllers\Admin\CreditAssignmentController::class, 'deposits']);

    Route::get('deposit/', [\App\Http\Controllers\Admin\DepositController::class, 'list']);
    Route::put('deposit/{id}/verify', [\App\Http\Controllers\Admin\DepositController::class, 'validate']);
    Route::get('deposit/export/{type}', [\App\Http\Controllers\Admin\DepositController::class, 'export']);

    Route::get('/blog', [\App\Http\Controllers\Admin\BlogController::class, 'listAll']);
    Route::post('/blog', [\App\Http\Controllers\Admin\BlogController::class, 'create']);
    Route::get('/blog/{id}', [\App\Http\Controllers\Admin\BlogController::class, 'detail']);
    Route::post('/blog/{id}', [\App\Http\Controllers\Admin\BlogController::class, 'edit']);
    Route::delete('/blog/{id}', [\App\Http\Controllers\Admin\BlogController::class, 'delete']);
    Route::get('/blog/export/{type}', [\App\Http\Controllers\Admin\BlogController::class, 'export']);

    Route::get('/membresia', [\App\Http\Controllers\Admin\MembresiaController::class, 'listAll']);
    Route::post('/membresia', [\App\Http\Controllers\Admin\MembresiaController::class, 'create']);
    Route::delete('/membresia/{id}', [\App\Http\Controllers\Admin\MembresiaController::class, 'delete']);
    Route::get('/membresia/users', [\App\Http\Controllers\Admin\MembresiaController::class, 'users']);
    Route::get('/membresia/auctions', [\App\Http\Controllers\Admin\MembresiaController::class, 'auctions']);

    Route::get('/newsletter_template', [\App\Http\Controllers\Admin\NewsletterTemplateController::class, 'listAll']);
    Route::post('/newsletter_template', [\App\Http\Controllers\Admin\NewsletterTemplateController::class, 'create']);
    Route::get('/newsletter_template/{id}', [\App\Http\Controllers\Admin\NewsletterTemplateController::class, 'detail']);
    Route::post('/newsletter_template/{id}', [\App\Http\Controllers\Admin\NewsletterTemplateController::class, 'edit']);
    Route::delete('/newsletter_template/{id}', [\App\Http\Controllers\Admin\NewsletterTemplateController::class, 'delete']);
    Route::get('/newsletter_template/export/{type}', [\App\Http\Controllers\Admin\NewsletterTemplateController::class, 'export']);

    Route::get('/newsletter', [\App\Http\Controllers\Admin\NewsletterController::class, 'listAll']);
    Route::post('/newsletter', [\App\Http\Controllers\Admin\NewsletterController::class, 'create']);
    Route::get('/newsletter/{id}', [\App\Http\Controllers\Admin\NewsletterController::class, 'detail']);
    Route::post('/newsletter/{id}', [\App\Http\Controllers\Admin\NewsletterController::class, 'edit']);
    Route::delete('/newsletter/{id}', [\App\Http\Controllers\Admin\NewsletterController::class, 'delete']);
    Route::get('/newsletter/export/{type}', [\App\Http\Controllers\Admin\NewsletterController::class, 'export']);

    Route::get('/notification', [\App\Http\Controllers\Admin\NotificationController::class, 'listAll']);
    Route::post('/notification/all/{type}', [\App\Http\Controllers\Admin\NotificationController::class, 'statusAll']);
    Route::post('/notification/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'status']);

});

Route::group([
    'middleware' => 'userRequired'
], function () {

    // Route::get('/user/profile', [\App\Http\Controllers\Web\UserController::class, 'profile']);
    Route::get('/user', [\App\Http\Controllers\Web\UserController::class, 'detail']);
    // Route::get('/user/auction', [\App\Http\Controllers\Web\UserController::class, 'auctions']);
    Route::get('/user/favorite', [\App\Http\Controllers\Web\UserController::class, 'favorites']);
    Route::put('/user', [\App\Http\Controllers\Web\UserController::class, 'edit']);
    
    Route::post('/interests', [\App\Http\Controllers\Web\UserController::class, 'interests']);

    Route::post('/user/upload_dni', [\App\Http\Controllers\Web\UserController::class, 'uploadDni']);
    Route::post('/user/upload_dni_two', [\App\Http\Controllers\Web\UserController::class, 'uploadDniTwo']);
    Route::put('/user/change-password', [\App\Http\Controllers\Web\UserController::class, 'changePassword']);
    Route::put('/user/notifications', [\App\Http\Controllers\Web\UserController::class, 'updateNotifications']);
    Route::put('/user/preferences', [\App\Http\Controllers\Web\UserController::class, 'updatePreferences']);
    Route::get('/user/get_preferences', [\App\Http\Controllers\Web\UserController::class, 'getPreferences']);
    Route::post('/user/set_preferences', [\App\Http\Controllers\Web\UserController::class, 'setPreferences']);
    Route::delete('/user', [\App\Http\Controllers\Web\UserController::class, 'deletePermanent']);

    Route::get('/representation/list', [\App\Http\Controllers\Web\RepresentationController::class, 'list']);
    Route::post('/representation', [\App\Http\Controllers\Web\RepresentationController::class, 'create']);
    Route::get('/representation/{guid}', [\App\Http\Controllers\Web\RepresentationController::class, 'detail']);
    Route::post('/representation/{guid}', [\App\Http\Controllers\Web\RepresentationController::class, 'edit']);
    Route::delete('/representation/{guid}', [\App\Http\Controllers\Web\RepresentationController::class, 'delete']);

    Route::post('/direct_sale/{guid}', [\App\Http\Controllers\Web\DirectSaleOfferController::class, 'create']);

    Route::get('/auction/list', [\App\Http\Controllers\Web\AuctionController::class, 'listAll']);
    Route::post('/auction/{guid}/bid', [\App\Http\Controllers\Web\BidController::class, 'create']);
    Route::get('/auction/{guid}/detail', [\App\Http\Controllers\Web\AuctionController::class, 'detail']);
    Route::post('/auction/{guid}/deposit', [\App\Http\Controllers\Web\AuctionController::class, 'createDeposit']);
    Route::put('/auction/{guid}/favorite', [\App\Http\Controllers\Web\AuctionController::class, 'putFavorite']);
    Route::get('/auction/{guid}/check-membresia', [\App\Http\Controllers\Web\AuctionController::class, 'checkMembresia']);

    Route::get('/auction/{guid}/check-deposit', [\App\Http\Controllers\Web\DepositController::class, 'checkDeposit']);

});

Route::post('/contact', [\App\Http\Controllers\Web\UserController::class, 'contact']);


Route::get('/country', [\App\Http\Controllers\CountryController::class, 'list']);
Route::get('/province/{country?}', [\App\Http\Controllers\ProvinceController::class, 'list']);
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'listAll']);
Route::get('/blog/{guid}', [\App\Http\Controllers\BlogController::class, 'detail']);
Route::get('/representation_type', [\App\Http\Controllers\RepresentationTypeController::class, 'list']);

Route::get('/auction', [\App\Http\Controllers\Web\AuctionController::class, 'listAll']);
Route::get('/auction_last', [\App\Http\Controllers\Web\AuctionController::class, 'listLast']);
Route::get('/auction_soon', [\App\Http\Controllers\Web\AuctionController::class, 'listSoon']);
Route::get('/auction_offers', [\App\Http\Controllers\Web\AuctionController::class, 'listOffers']);
Route::get('/auction_finished', [\App\Http\Controllers\Web\AuctionController::class, 'listFinished']);
Route::get('/auction/{guid}', [\App\Http\Controllers\Web\AuctionController::class, 'detail']);
Route::get('/active_category/list', [\App\Http\Controllers\Web\ActiveCategoryController::class, 'list']);

Route::post('/saveLatLng', [\App\Http\Controllers\Web\AuctionController::class, 'saveLatLng']);

/* Rutas informes */
/*Route::get('reporting', [reportController::class, 'report']);
Route::get('subzup7kh6wvxh84epfq', [reportController::class, 'subastas']);
Route::get('venc7tfwc5qkqhw4fu78', [reportController::class, 'ventas']);
Route::get('dep765gqumyxhr9xh7z5', [reportController::class, 'depositos']);

Route::get('act65gqumyxhxh84hr9d', [reportController::class, 'activos']);
Route::get('useh6wvumyxfu78uuntt', [reportController::class, 'usuarios']);
Route::get('bidumyxfu7uid8h6wvun', [reportController::class, 'pujas']);*/
Route::get('qumyxhxh8unumyd8uxfuntt', [reportController::class, 'participants']);


Route::get('testEmail/{email?}/{template?}', [AuthController::class, 'testEmail']);

Route::get('import-auctions', [AuctionController::class, 'importAuctions']);
Route::post('store-actives', [AuctionController::class, 'storeActives']);
Route::post('upload-files',  [AuctionController::class, 'uploadFiles']);
Route::get('repeat-auctions', [AuctionController::class, 'repeatAuctions']);
Route::post('store-auctions', [AuctionController::class, 'storeAuctions']);

/* Route::get('register-campaign', [\App\Http\Controllers\Web\UserController::class, 'registerCampaign']);*/
Route::post('store-participant', [\App\Http\Controllers\Web\UserController::class, 'storeParticipant']);

Route::get('/testlist', [\App\Http\Controllers\Web\AuctionController::class, 'listAll']);
Route::get('/testemail', [\App\Http\Controllers\Web\AuctionController::class, 'testEmail']);

Route::get('test-pipe/{id?}', [AuctionController::class, 'testPipe']);

Route::get('test', [\App\Http\Controllers\ApiController::class, 'test']);

Route::post('activesImport', [AuctionController::class, 'activesImport']);
Route::post('auctionsImport', [AuctionController::class, 'auctionsImport']);

