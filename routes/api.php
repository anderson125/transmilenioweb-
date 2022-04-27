<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\BicyController;
use App\Http\Controllers\BicyStatusController;
use App\Http\Controllers\BikerAuthController;
use App\Http\Controllers\BikerController;
use App\Http\Controllers\BikerStatusController;
use App\Http\Controllers\GenderController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\NeighborhoodController;
use App\Http\Controllers\ParentsController;
use App\Http\Controllers\ParkingController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\TypeBicyController;
use App\Http\Controllers\TypeDocumentController;
use App\Http\Controllers\TypeParkingController;
use App\Http\Controllers\TypeVisitController;
use App\Http\Controllers\UserAppController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\VisitStatusController;
use App\Http\Controllers\CasualController;
use App\Http\Controllers\ParkingMaintenancesController;
use App\Http\Controllers\InventoryBiciesController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\AuthsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\ProvisionalStickerOrdersController;
use App\Http\Controllers\DetailedStickerOrderController;
use App\Http\Controllers\Auth\QuerierUserController;
use App\Http\Controllers\Auth\VigilantUserController;
use App\Http\Controllers\Auth\LoginController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\PermissionsChecker;

use App\Models\Biker;
use App\Models\VerificationCode;

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

\Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
    Log::info( json_encode($query->sql) );
    Log::info( json_encode($query->bindings) );
    Log::info( json_encode($query->time)   );
});


Route::middleware(['auth:sanctum',PermissionsChecker::class])->group(function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });
    Route::post('checkToken', function (Request $request) {
        return response()->json(['test'=>$request->user()]);
    });

    Route::post('logout', [LoginController::class,'logoutApp']);

    Route::name('type.')->prefix('type')->group(function () {
        Route::resource('bicy', TypeBicyController::class);
        Route::resource('document', TypeDocumentController::class);
        Route::resource('parking', TypeParkingController::class);
        Route::resource('parent', ParentsController::class);
    });
    Route::name('status.')->prefix('status')->group(function () {
        Route::resource('bicy', BicyStatusController::class);
        Route::resource('visit', VisitStatusController::class);
    });
    Route::name('data.')->prefix('data')->group(function () {
        Route::resource('permissions', PermissionsController::class)->only(['index','destroy']);
        Route::resource('auths', AuthsController::class)->only(['index','store','destroy']);
        Route::resource('stickers', ProvisionalStickerOrdersController::class)->only(['index','show','store','update']);
        Route::resource('Dstickers', DetailedStickerOrderController::class)->only(['index','show','store','update']);
        Route::resource('maintenance', ParkingMaintenancesController::class);
        
        Route::resource('roles', RolesController::class)->only(['index','store','destroy']);
        Route::post('roles/asignToUser/{id}', [RolesController::class,'asignToUser']);
        Route::post('roles/revokeToUser/{id}', [RolesController::class,'revokeToUser']);
        Route::post('roles/authRoleTo/{id}', [RolesController::class,'authRoleTo']);
        Route::post('roles/unauthorizeRoleTo/{id}', [RolesController::class,'unauthorizeRoleTo']);

    
        Route::get('bicy/detailed/{id}', [BicyController::class,'detailedShow']);
        Route::resource('bicy', BicyController::class)->except(['create']);
        Route::get('bicy/{parking_id}/create',[BicyController::class,'create']);
        Route::post('bicy/{id}', [BicyController::class,'update']);
        Route::get('qr/bicy', [BicyController::class,'returnQRInfo']);       

        Route::post('biker/massiveRawMsg', [BikerController::class,'massiveRawTextMessage']);
        Route::resource('biker', BikerController::class);
        Route::post('biker/{id}', [BikerController::class,'update']);
        Route::get('biker/verificationCode/{phone}', [BikerController::class,'getVerificationCode'] );        
        Route::get('biker-export', [BikerController::class,'export'] );        
        Route::post('biker/parentVerificationCode/{id}', [ParentsController::class,'getParentVerificationCode']);
        Route::put('biker/{id}/unblock', [BikerController::class,'unblockBiker']);


        Route::resource('gender', GenderController::class);
        Route::resource('job', JobController::class);
        Route::resource('neighborhood', NeighborhoodController::class);
        Route::resource('level', LevelController::class);
        Route::resource('parking', ParkingController::class);
        Route::resource('station', StationController::class);
        Route::put('visit/updateByBicyCode/{id}', [VisitController::class,'updateByBicyCode']);
        Route::resource('visit', VisitController::class);
        Route::post('visit/offlineStorage/{parkingId}', [VisitController::class, 'massiveStorage']);
        Route::resource('young', BikerAuthController::class);
        Route::resource('inventory', InventoryController::class);
        Route::post('inventoryExtended', [InventoryController::class, 'storeInventoryStoreBiciesUpdateInventoryShowInventory']);
        Route::get('showInventoryByDateAndParking', [InventoryController::class, 'showByDateAndParking']);
        Route::resource('inventoryBicy', InventoryBiciesController::class)->only(['store','destroy']);

        Route::post('reports',[ReportsController::class, 'show']);
        Route::get('reports/visits/dailyByMonths',[ReportsController::class, 'dailyVisitsByMonths']);
        Route::get('reports/visits/generalBikerByMonths',[ReportsController::class, 'generalBikerVisitsByMonths']);
        Route::get('reports/visits/detailedBikerByMonths',[ReportsController::class, 'detailedBikerVisitsByMonths']);
        Route::get('reports/visits/hourlyByDays',[ReportsController::class, 'hourlyVisitsByDays']);
        Route::get('reports/visits/abandonedBicies',[ReportsController::class, 'visitAbandonedBicies']);
        Route::get('reports/visits/webMapService',[ReportsController::class, 'webMapService']);
    });

    Route::name('user.')->group(function(){
        Route::resource('user',UserController::class);
        Route::resource('vigilant',VigilantUserController::class);
        Route::resource('querier',QuerierUserController::class);
    });
});

Route::post('login',[LoginController::class,'loginAPP'] );
Route::get('restorePasswordCode',[VigilantUserController::class,'getRestorePasswordCode'] );
Route::put('restorePassword',[VigilantUserController::class,'restorePassword'] );


Route::post('code/{code}', function(Request $request, $code){
    return VerificationCode::validate($code);
});

 Route::name('cronable.')->prefix('cronable')->group(function () {

    Route::put('bikerActiveness',[BikerController::class, 'checkActiveness']);
    Route::put('bikeExpiration',[BicyController::class, 'checkBiciesExpirations']);
    Route::put('bikeAbandoning',[BicyController::class, 'checkAbandonedBicies']);
    
});

Route::get('/{any}', [CasualController::class,'returnNotFound'])->where('any','.*');
Route::post('/{any}', [CasualController::class,'returnNotFound'])->where('any','.*');
Route::put('/{any}', [CasualController::class,'returnNotFound'])->where('any','.*');
Route::delete('/{any}', [CasualController::class,'returnNotFound'])->where('any','.*');

