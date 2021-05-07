<?php

use App\Http\Controllers\AutomationController;
use App\Models\Automation;
use App\Notifications\AutomationNotification;
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

    $automations = Automation::yesterday()->OrderBy('automation', 'asc')->get();
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
        $autos = Automation::where('automation', $name)->yesterday()->orderBy('startTime','desc')->first();

        $autos->notify(new AutomationNotification($autos));
    }


    // $automations = new Automation();
    // $automations = $automations::select([DB::raw('MAX(startTime) as startTime, id, warning, automation, customerKey, status, statusMessage')])
    //     ->groupBy('id', 'warning', 'automation', 'customerKey', 'status', 'statusMessage')
    //     ->get();

    // foreach ($automations as $automation) {
    //     //dd($automations);

    //     $teste = 'BBBBBBB';
    //     $automation->notify(new WarningAutomation($automation, $teste));
    //     // $automation::update(['warning' => true]);
    // }
    // $teste = 'BBBBBBB';
    // $user = new Automation();
    // $user::first();
    // $user->notify((new AutomationNotification($teste)));
});

Route::get('/teste',  [AutomationController::class, 'runAutomationMonitor']);
