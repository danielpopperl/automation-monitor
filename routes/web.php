<?php

use App\Http\Controllers\AutomationController;
use App\Models\Automation;
use App\Notifications\AutomationError;
use App\Notifications\AutomationInfo;
use Illuminate\Support\Facades\Route;

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

Route::get('/a', function () {

    $automations = Automation::OrderBy('automation', 'asc')->get();
    $names = [];
    $name = null;

    foreach($automations as $automation){

        if(!$name) {
            $name = $automation->automation;
            array_push($names, $name);
        } else {
            if($name !== $automation->automation) {
                $name = $automation->automation;
                array_push($names, $name);
            }
        }
    }

    foreach($names as $name) {
        $autos = new Automation();

        // Automações Diárias
        $autos = Automation::where('automation', $name)
                ->MonitorDaily()
                ->today()
                ->orderBy('startTime', 'desc')
                ->first();

        if($autos){
            $autos->notify(new AutomationInfo($autos));
        }else{
            $autos = Automation::where('automation', $name)
                    ->MonitorDaily()
                    ->yesterday()
                    ->orderBy('startTime', 'desc')
                    ->first();
            if ($autos) {
                $autos->notify(new AutomationError($autos));
            }
        }

        //Automações não Diárias
        $autos = Automation::where('automation', $name)
                ->NotMonitorDaily()
                ->first();

        if ($autos) {
            $autos->notify(new AutomationInfo($autos));
        }
    }

});

Route::get('/teste',  [AutomationController::class, 'runAutomationMonitor']);
