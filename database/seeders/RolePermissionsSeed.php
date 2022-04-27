<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;    

use DB;

use App\Models\User;

class RolePermissionsSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        

        Schema::disableForeignKeyConstraints();

            User::truncate();
        
            $superAdminUser = User::create([
                'name'  => 'Super ADMIN',
                'email'     => 'email@email.com',
                'phone'     => '1234567',
                'document'     => '123456789',
                'password'  => bcrypt('12345678')
            ]);
            $adminUser = User::create([
                'name'  => 'Admin',
                'email'     => 'email@admin.com',
                'phone'     => '1234567',
                'document'     => '12345678',
                'password'  => bcrypt('12345678')
            ]);
            $querierUser = User::create([
                'name'  => 'Querier',
                'email'     => 'email@querier.com',
                'phone'     => '1234567',
                'document'     => '1234567',
                'password'  => bcrypt('12345678')
            ]);
            $vigilantUser = User::create([
                'name'  => 'Vigilant',
                'email'     => 'email@vigilant.com',
                'phone'     => '1234567',
                'document'     => '123456',
                'password'  => bcrypt('12345678'),
                'parkings_id' => 1
            ]);


            DB::table('model_has_permissions')->truncate();
            DB::table('role_has_permissions')->truncate();
            DB::table('model_has_roles')->truncate();
            Role::truncate();
            Permission::truncate();

        Schema::enableForeignKeyConstraints();
            
            $userIndex = Permission::create(['name' => 'UserController@index']);
            $userShow = Permission::create(['name' => 'UserController@show']);
            $userStore = Permission::create(['name' => 'UserController@store']);
            $userUpdate = Permission::create(['name' => 'UserController@update']);
            $userDestroy = Permission::create(['name' => 'UserController@destroy']);

            $vigilantUserIndex = Permission::create(['name' => 'VigilantUserController@index']);
            $vigilantUserShow = Permission::create(['name' => 'VigilantUserController@show']);
            $vigilantUserStore = Permission::create(['name' => 'VigilantUserController@store']);
            $vigilantUserUpdate = Permission::create(['name' => 'VigilantUserController@update']);
            $vigilantUserDestroy = Permission::create(['name' => 'VigilantUserController@destroy']);

            $querierUserIndex = Permission::create(['name' => 'QuerierUserController@index']);
            $querierUserShow = Permission::create(['name' => 'QuerierUserController@show']);
            $querierUserStore = Permission::create(['name' => 'QuerierUserController@store']);
            $querierUserUpdate = Permission::create(['name' => 'QuerierUserController@update']);
            $querierUserDestroy = Permission::create(['name' => 'QuerierUserController@destroy']);



            $typeDocumentIndex = Permission::create(['name' => 'TypeDocumentController@index']);
            $typeDocumentShow = Permission::create(['name' => 'TypeDocumentController@show']);
            $typeDocumentStore = Permission::create(['name' => 'TypeDocumentController@store']);
            $typeDocumentUpdate = Permission::create(['name' => 'TypeDocumentController@update']);
            $typeDocumentDestroy = Permission::create(['name' => 'TypeDocumentController@destroy']);


            $bicyStatusIndex = Permission::create(['name' => 'BicyStatusController@index']);
            $bicyStatusShow = Permission::create(['name' => 'BicyStatusController@show']);
            $bicyStatusStore = Permission::create(['name' => 'BicyStatusController@store']);
            $bicyStatusUpdate = Permission::create(['name' => 'BicyStatusController@update']);
            $bicyStatusDestroy = Permission::create(['name' => 'BicyStatusController@destroy']);

            $bikerStatusIndex = Permission::create(['name' => 'BikerStatusController@index']);
            $bikerStatusShow = Permission::create(['name' => 'BikerStatusController@show']);
            $bikerStatusStore = Permission::create(['name' => 'BikerStatusController@store']);
            $bikerStatusUpdate = Permission::create(['name' => 'BikerStatusController@update']);
            $bikerStatusDestroy = Permission::create(['name' => 'BikerStatusController@destroy']);


            $brandIndex = Permission::create(['name' => 'BrandController@index']);
            $brandShow = Permission::create(['name' => 'BrandController@show']);
            $brandStore = Permission::create(['name' => 'BrandController@store']);
            $brandUpdate = Permission::create(['name' => 'BrandController@update']);
            $brandDestroy = Permission::create(['name' => 'BrandController@destroy']);

            $colorIndex = Permission::create(['name' => 'ColorController@index']);
            $colorShow = Permission::create(['name' => 'ColorController@show']);
            $colorStore = Permission::create(['name' => 'ColorController@store']);
            $colorUpdate = Permission::create(['name' => 'ColorController@update']);
            $colorDestroy = Permission::create(['name' => 'ColorController@destroy']);


            $genderIndex = Permission::create(['name' => 'GenderController@index']);
            $genderShow = Permission::create(['name' => 'GenderController@show']);
            $genderStore = Permission::create(['name' => 'GenderController@store']);
            $genderUpdate = Permission::create(['name' => 'GenderController@update']);
            $genderDestroy = Permission::create(['name' => 'GenderController@destroy']);


            $jobIndex = Permission::create(['name' => 'JobController@index']);
            $jobShow = Permission::create(['name' => 'JobController@show']);
            $jobStore = Permission::create(['name' => 'JobController@store']);
            $jobUpdate = Permission::create(['name' => 'JobController@update']);
            $jobDestroy = Permission::create(['name' => 'JobController@destroy']);


            $levelIndex = Permission::create(['name' => 'LevelController@index']);
            $levelShow = Permission::create(['name' => 'LevelController@show']);
            $levelStore = Permission::create(['name' => 'LevelController@store']);
            $levelUpdate = Permission::create(['name' => 'LevelController@update']);
            $levelDestroy = Permission::create(['name' => 'LevelController@destroy']);


            $neighborhoodIndex = Permission::create(['name' => 'NeighborhoodController@index']);
            $neighborhoodShow = Permission::create(['name' => 'NeighborhoodController@show']);
            $neighborhoodStore = Permission::create(['name' => 'NeighborhoodController@store']);
            $neighborhoodUpdate = Permission::create(['name' => 'NeighborhoodController@update']);
            $neighborhoodDestroy = Permission::create(['name' => 'NeighborhoodController@destroy']);


            $TireControllerIndex = Permission::create(['name' => 'TireController@index']);
            $TireControllerShow = Permission::create(['name' => 'TireController@show']);
            $TireControllerStore = Permission::create(['name' => 'TireController@store']);
            $TireControllerUpdate = Permission::create(['name' => 'TireController@update']);
            $TireControllerDestroy = Permission::create(['name' => 'TireController@destroy']);

            $TypeBicyControllerIndex = Permission::create(['name' => 'TypeBicyController@index']);
            $TypeBicyControllerShow = Permission::create(['name' => 'TypeBicyController@show']);
            $TypeBicyControllerStore = Permission::create(['name' => 'TypeBicyController@store']);
            $TypeBicyControllerUpdate = Permission::create(['name' => 'TypeBicyController@update']);
            $TypeBicyControllerDestroy = Permission::create(['name' => 'TypeBicyController@destroy']);
            


            $TypeParkingControllerIndex = Permission::create(['name' => 'TypeParkingController@index']);
            $TypeParkingControllerShow = Permission::create(['name' => 'TypeParkingController@show']);
            $TypeParkingControllerStore = Permission::create(['name' => 'TypeParkingController@store']);
            $TypeParkingControllerUpdate = Permission::create(['name' => 'TypeParkingController@update']);
            $TypeParkingControllerDestroy = Permission::create(['name' => 'TypeParkingController@destroy']);


            $TypeVisitControllerIndex = Permission::create(['name' => 'TypeVisitController@index']);
            $TypeVisitControllerShow = Permission::create(['name' => 'TypeVisitController@show']);
            $TypeVisitControllerStore = Permission::create(['name' => 'TypeVisitController@store']);
            $TypeVisitControllerUpdate = Permission::create(['name' => 'TypeVisitController@update']);
            $TypeVisitControllerDestroy = Permission::create(['name' => 'TypeVisitController@destroy']);


            $VisitStatusControllerIndex = Permission::create(['name' => 'VisitStatusController@index']);
            $VisitStatusControllerShow = Permission::create(['name' => 'VisitStatusController@show']);
            $VisitStatusControllerStore = Permission::create(['name' => 'VisitStatusController@store']);
            $VisitStatusControllerUpdate = Permission::create(['name' => 'VisitStatusController@update']);
            $VisitStatusControllerDestroy = Permission::create(['name' => 'VisitStatusController@destroy']);

            $bikersIndex = Permission::create(['name' => 'BikerController@index']);
            $bikersShow = Permission::create(['name' => 'BikerController@show']);
            $bikersStore = Permission::create(['name' => 'BikerController@store']);
            $bikersUpdate = Permission::create(['name' => 'BikerController@update']);
            $bikersDestroy = Permission::create(['name' => 'BikerController@destroy']);
            $bikerExport = Permission::create(['name' => 'BikerController@export']);
            $bikersMassiveStore = Permission::create(['name' => 'BikerController@massiveStore']);
            $bikersUnblock = Permission::create(['name' => 'BikerController@unblockBiker']);
            $bikerGetCode = Permission::create(['name' => 'BikerController@getVerificationCode']);
            $bikerMassiveRawSMS = Permission::create(['name' => 'BikerController@massiveRawTextMessage']);

            $bikerAuthIndex = Permission::create(['name' => 'BikerAuthController@index']);
            $bikerAuthStore = Permission::create(['name' => 'BikerAuthController@store']);
            $bikerAuthEdit = Permission::create(['name' => 'BikerAuthController@edit']);
            $bikerAuthDestroy = Permission::create(['name' => 'BikerAuthController@destroy']);

            $bikeDestroy = Permission::create(['name' => 'BicyController@destroy']);
            $bikeIndex = Permission::create(['name' => 'BicyController@index']);
            $bikeCreate = Permission::create(['name' => 'BicyController@create']);
            $bikeShow = Permission::create(['name' => 'BicyController@show']);
            $bikeStore = Permission::create(['name' => 'BicyController@store']);
            $bikeUpdate = Permission::create(['name' => 'BicyController@update']);
            $bicyQR = Permission::create(['name' => 'BicyController@returnQRInfo']);
            $bicyDetailedShow = Permission::create(['name' => 'BicyController@detailedShow']);
            $bikeMassiveStore = Permission::create(['name' => 'BicyController@massiveStore']);

            $parentIndex = Permission::create(['name' => 'ParentsController@index']);
            $parentShow = Permission::create(['name' => 'ParentsController@show']);
            $parentStore = Permission::create(['name' => 'ParentsController@store']);
            $parentUpdate = Permission::create(['name' => 'ParentsController@update']);
            $parentAuthVefCode = Permission::create(['name' => 'ParentsController@getParentVerificationCode']);
            $parentDestroy = Permission::create(['name' => 'Parents@destroy']);

            $ParkingMaintenancesIndex = Permission::create(['name' => 'ParkingMaintenancesController@index']);
            $ParkingMaintenancesShow = Permission::create(['name' => 'ParkingMaintenancesController@show']);
            $ParkingMaintenancesStore = Permission::create(['name' => 'ParkingMaintenancesController@store']);
            $ParkingMaintenancesUpdate = Permission::create(['name' => 'ParkingMaintenancesController@update']);
            $ParkingMaintenancesDestroy = Permission::create(['name' => 'ParkingMaintenancesController@destroy']);
            
            $ParkingIndex = Permission::create(['name' => 'ParkingController@index']);
            $ParkingShow = Permission::create(['name' => 'ParkingController@show']);
            $ParkingStore = Permission::create(['name' => 'ParkingController@store']);
            $ParkingUpdate = Permission::create(['name' => 'ParkingController@update']);
            $ParkingDestroy = Permission::create(['name' => 'ParkingController@destroy']);

            $provisionalStickerOrderIndex = Permission::create(['name' => 'ProvisionalStickerOrdersController@index']);
            $provisionalStickerOrderShow = Permission::create(['name' => 'ProvisionalStickerOrdersController@show']);
            $provisionalStickerOrderStore = Permission::create(['name' => 'ProvisionalStickerOrdersController@store']);
            $provisionalStickerOrderUpdate = Permission::create(['name' => 'ProvisionalStickerOrdersController@update']);
            $provisionalStickerOrderDestroy = Permission::create(['name' => 'ProvisionalStickerOrdersController@destroy']);
            
            $detailedStickerOrderIndex = Permission::create(['name' => 'DetailedStickerOrderController@index']);
            $detailedStickerOrderShow = Permission::create(['name' => 'DetailedStickerOrderController@show']);
            $detailedStickerOrderStore = Permission::create(['name' => 'DetailedStickerOrderController@store']);
            $detailedStickerOrderUpdate = Permission::create(['name' => 'DetailedStickerOrderController@update']);
            $detailedStickerOrderDestroy = Permission::create(['name' => 'DetailedStickerOrderController@destroy']);

            $InventoryIndex = Permission::create(['name' => 'InventoryController@index']);
            $InventoryShow = Permission::create(['name' => 'InventoryController@show']);
            $InventoryStore = Permission::create(['name' => 'InventoryController@store']);
            $InventoryExtended = Permission::create(['name' => 'InventoryController@storeInventoryStoreBiciesUpdateInventoryShowInventory']);
            $InventoryShowByDateAndParking = Permission::create(['name' => 'InventoryController@showByDateAndParking']);
            $InventoryUpdate = Permission::create(['name' => 'InventoryController@update']);
            $InventoryDestroy = Permission::create(['name' => 'InventoryController@destroy']);

            $inventoryBiciesIndex = Permission::create(['name' => 'InventoryBiciesController@index']);
            $inventoryBiciesShow = Permission::create(['name' => 'InventoryBiciesController@show']);
            $inventoryBiciesStore = Permission::create(['name' => 'InventoryBiciesController@store']);
            $inventoryBiciesUpdate = Permission::create(['name' => 'InventoryBiciesController@update']);
            $inventoryBiciesDestroy = Permission::create(['name' => 'InventoryBiciesController@destroy']);


            $reportsShow = Permission::create(['name' => 'ReportsController@show']);
            $reportsDailyVisitsByMonths = Permission::create(['name' => 'ReportsController@dailyVisitsByMonths']);
            $reportsGeneralBikerVisitsByMonths = Permission::create(['name' => 'ReportsController@generalBikerVisitsByMonths']);
            $reportsDetailedBikerVisitsByMonths = Permission::create(['name' => 'ReportsController@detailedBikerVisitsByMonths']);
            $reportsVisitAbandonedBicies = Permission::create(['name' => 'ReportsController@visitAbandonedBicies']);
            $reportsHourlyVisitsByDays = Permission::create(['name' => 'ReportsController@hourlyVisitsByDays']);
            $reportsWebMapService = Permission::create(['name' => 'ReportsController@webMapService']);


            $permissionsIndex = Permission::create(['name' => 'PermissionsController@index']);
            // El store no se utiliza pues para que sea funcional debe haber un método/controlador codificado
            $permissionsDestroy = Permission::create(['name' => 'PermissionsController@destroy']);
        
        $visitsIndex = Permission::create(['name' => 'VisitController@index']);
        $visitsStore = Permission::create(['name' => 'VisitController@store']);
        $visitsMassiveStorage = Permission::create(['name' => 'VisitController@massiveStorage']);
        $visitsCreate = Permission::create(['name' => 'VisitController@create']);
        $visitsUpdate = Permission::create(['name' => 'VisitController@update']);
        $visitsUpdateByBicyCode = Permission::create(['name' => 'VisitController@updateByBicyCode']);

        $authsIndex = Permission::create(['name' => 'AuthsController@index']);
        $authsStore = Permission::create(['name' => 'AuthsController@store']);
        $authsDestroy = Permission::create(['name' => 'AuthsController@destroy']);

        $rolesIndex = Permission::create(['name' => 'RolesController@index']);
        $rolesStore = Permission::create(['name' => 'RolesController@store']);
        $rolesDestroy = Permission::create(['name' => 'RolesController@destroy']);
        $rolesAsign = Permission::create(['name' => 'RolesController@asignToUser']);
        $rolesRevoke = Permission::create(['name' => 'RolesController@revokeToUser']);
        $rolesAuthRoleTo = Permission::create(['name' => 'RolesController@authRoleTo']);
        $rolesUnauthorizeRoleTo = Permission::create(['name' => 'RolesController@unauthorizeRoleTo']);

        

        $admin = Role::create(['name' => 'admin']);
        $superAdmin = Role::create(['name' => 'superAdmin']);
        $querier = Role::create(['name' => 'querier']);
        $vigilant = Role::create(['name' => 'vigilant']);


        $adminPermissions = [
            $provisionalStickerOrderIndex,
            $provisionalStickerOrderShow,
            $provisionalStickerOrderStore,
            $provisionalStickerOrderUpdate,
            $provisionalStickerOrderDestroy,
            
            $detailedStickerOrderIndex,
            $detailedStickerOrderShow,
            $detailedStickerOrderStore,
            $detailedStickerOrderUpdate,
            $detailedStickerOrderDestroy,

            $bikersDestroy, 
            $bikerExport,
            $bikersIndex, 
            $bikersShow, 
            $bikersStore, 
            $bikersUpdate, 
            $bikerGetCode,
            $bikerMassiveRawSMS,
            $bikersUnblock,
            $bikersMassiveStore,

            $bikerAuthIndex,
            $bikerAuthStore,
            $bikerAuthEdit,
            $bikerAuthDestroy,

            $permissionsIndex, 
            $permissionsDestroy,
            
            $authsIndex,
            $authsStore,
            $authsDestroy,

            $visitsIndex,
            $visitsStore,
            $visitsUpdate,
            $visitsCreate,
            $visitsMassiveStorage,
            $visitsUpdateByBicyCode,

            $rolesIndex,
            $rolesStore,
            $rolesDestroy,
            $rolesAsign,
            $rolesAuthRoleTo,
            $rolesUnauthorizeRoleTo,
            $rolesRevoke,
            

            $bikeDestroy,
            $bikeIndex,
            $bikeShow,
            $bikeStore,
            $bikeUpdate,
            $bicyQR,
            $bikeCreate,
            $bikeMassiveStore,
            $bicyDetailedShow,

            $parentIndex,
            $parentShow,
            $parentStore,
            $parentUpdate,
            $parentAuthVefCode,
            $parentDestroy,

            $bicyStatusIndex,
            $bicyStatusShow,
            $bicyStatusStore,
            $bicyStatusUpdate,
            $bicyStatusDestroy,
            $bikerStatusIndex,
            $bikerStatusShow,
            $bikerStatusStore,
            $bikerStatusUpdate,
            $bikerStatusDestroy,
            $brandIndex,
            $brandShow,
            $brandStore,
            $brandUpdate,
            $brandDestroy,
            $colorIndex,
            $colorShow,
            $colorStore,
            $colorUpdate,
            $colorDestroy,
            $genderIndex,
            $genderShow,
            $genderStore,
            $genderUpdate,
            $genderDestroy,
            $jobIndex,
            $jobShow,
            $jobStore,
            $jobUpdate,
            $jobDestroy,
            $levelIndex,
            $levelShow,
            $levelStore,
            $levelUpdate,
            $levelDestroy,
            $neighborhoodIndex,
            $neighborhoodShow,
            $neighborhoodStore,
            $neighborhoodUpdate,
            $neighborhoodDestroy,
            $TireControllerIndex,
            $TireControllerShow,
            $TireControllerStore,
            $TireControllerUpdate,
            $TireControllerDestroy,
            $TypeBicyControllerIndex,
            $TypeBicyControllerShow,
            $TypeBicyControllerStore,
            $TypeBicyControllerUpdate,
            $TypeBicyControllerDestroy,
            $TypeParkingControllerIndex,
            $TypeParkingControllerShow,
            $TypeParkingControllerStore,
            $TypeParkingControllerUpdate,
            $TypeParkingControllerDestroy,
            $TypeVisitControllerIndex,
            $TypeVisitControllerShow,
            $TypeVisitControllerStore,
            $TypeVisitControllerUpdate,
            $TypeVisitControllerDestroy,
            $VisitStatusControllerIndex,
            $VisitStatusControllerShow,
            $VisitStatusControllerStore,
            $VisitStatusControllerUpdate,
            $VisitStatusControllerDestroy,

            $ParkingMaintenancesIndex,
            $ParkingMaintenancesShow,
            $ParkingMaintenancesStore,
            $ParkingMaintenancesUpdate,
            $ParkingMaintenancesDestroy,
            $ParkingIndex,
            $ParkingShow,
            $ParkingStore,
            $ParkingUpdate,
            $ParkingDestroy,
            $InventoryIndex,
            $InventoryShow,
            $InventoryStore,
            $InventoryExtended,
            $InventoryShowByDateAndParking,
            $InventoryUpdate,
            $InventoryDestroy,

            $inventoryBiciesStore,
            $inventoryBiciesUpdate,
            $inventoryBiciesDestroy,
            
                        
            $userIndex,
            $userShow,

            $querierUserIndex,
            $querierUserShow,

            $vigilantUserIndex,
            $vigilantUserShow,
            $vigilantUserStore,
            $vigilantUserUpdate,
            $vigilantUserDestroy,


            $reportsShow,
            $reportsDailyVisitsByMonths,
            $reportsGeneralBikerVisitsByMonths,
            $reportsDetailedBikerVisitsByMonths,
            $reportsVisitAbandonedBicies,
            $reportsHourlyVisitsByDays,
            $reportsWebMapService,
        ];



        $admin->syncPermissions($adminPermissions);

        $superAdmin->syncPermissions(array_merge($adminPermissions,[
            $userStore,
            $userUpdate,
            $userDestroy,

            $querierUserStore,
            $querierUserUpdate,
            $querierUserDestroy,
        ]));

        $vigilant->syncPermissions([ 
            $bikersIndex, 
            $bikersShow,
            $bikersStore, 
            $bikersUpdate, 
            $bikerGetCode,
            $bikersDestroy,
            $bikerExport,

            $bikerAuthIndex,
            $bikerAuthStore,
            $bikerAuthEdit,
            // $bikerAuthDestroy,

            $brandIndex,
            $brandShow,
            $colorIndex,
            $colorShow,
            $TireControllerIndex,
            $TireControllerShow,
            $TypeBicyControllerIndex,
            $TypeBicyControllerShow,

            $bikeIndex,
            $bikeShow,
            $bikeStore,
            $bikeUpdate,
            $bikeCreate,
            $bicyDetailedShow,

            $visitsIndex,
            $visitsCreate,
            $visitsStore,
            $visitsUpdate,
            $visitsUpdateByBicyCode,
            $visitsMassiveStorage,

            $parentIndex,
            $parentShow,
            $parentStore,
            $parentUpdate,
            $parentAuthVefCode,

            // Foreign Biker related tables
            $typeDocumentIndex,
            $typeDocumentShow,
            $genderIndex,
            $genderShow,
            $jobIndex,
            $jobShow,
            $neighborhoodIndex,
            $neighborhoodShow,       
            $levelIndex,
            $levelShow,

            $InventoryIndex,
            $InventoryShow,
            $InventoryStore,
            $InventoryExtended,
            $InventoryShowByDateAndParking,
            $InventoryUpdate,

            $inventoryBiciesStore,
            $inventoryBiciesUpdate,

            $detailedStickerOrderIndex,
            $detailedStickerOrderShow,
            $detailedStickerOrderStore,
        ]);

        $querier->syncPermissions([
            // $reportsShow,
            $reportsDailyVisitsByMonths,
            $reportsGeneralBikerVisitsByMonths,
            $reportsDetailedBikerVisitsByMonths,
            $reportsVisitAbandonedBicies,
            $reportsHourlyVisitsByDays,
            $reportsWebMapService,
        ]);

        $superAdminUser->assignRole('superAdmin');
        $adminUser->assignRole('admin');
        $querierUser->assignRole('querier');
        $vigilantUser->assignRole('vigilant');


    }
}
