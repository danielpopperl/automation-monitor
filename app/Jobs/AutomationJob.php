<?php

namespace App\Jobs;

use App\Http\Controllers\AutomationController;
use App\Models\Automation;
use App\Notifications\AutomationError;
use App\Notifications\AutomationInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutomationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $automations = Automation::OrderBy('automation', 'asc')->get();
        $names = [];
        $name = null;

        foreach ($automations as $automation) {
            if (!$name) {
                $name = $automation->automation;
                array_push($names, $name);
            } else {
                if ($name !== $automation->automation) {
                    $name = $automation->automation;
                    array_push($names, $name);
                }
            }
        }

        foreach ($names as $name) {
            $autos = new Automation();

            // Automações Diárias
            $autos = Automation::where('automation', $name)
            ->MonitorDaily()
                ->today()
                ->orderBy('startTime', 'desc')
                ->first();

            if ($autos) {
                $autos->notify(new AutomationInfo($autos));
            } else {
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
            ->today()
                ->notMonitorDaily()
                ->first();

            if ($autos) {
                $autos->notify(new AutomationInfo($autos));
            }
        }
    }
}
