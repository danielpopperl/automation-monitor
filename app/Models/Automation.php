<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class Automation extends Model
{
    use Notifiable;

    protected $fillable = [
        'automation',
        'customerKey',
        'status',
        'statusMessage',
        'startTime'
    ];

    public $casts = [
        'warning'=> 'bool',
        'startTime'=> 'datetime'
    ];

    public function routeNotificationForSlack($notification) {
        return env('LOG_SLACK_WEBHOOK_URL');
    }


    public function scopeNotToday($query) {
        return $query->whereDate('startTime','<>', now());
    }

    public function scopeNotWarning($query) {
        return $query->where('warning', false);
    }

    public function scopeYesterday($query) {
        return $query->whereDate('startTime', now()->subDay(1));
    }

    public function scopeToday($query) {
        return $query->whereDate('startTime', now());
    }

    public function scopeMonitorDaily($query) {
        return $query->whereIn('automation', ['Automation_Import_Flat']);
    }

    public function scopeNotMonitorDaily($query) {
        return $query->whereIn('automation', ['PR_Aviso_Boleto_Massivo'])
                     ->whereDay('startTime', '=', '1', 'or', '2', 'or', '3', 'or','6', 'or','7', 'or', '8',
                                'or','11', 'or', '12', 'or', '13', 'or', '16', 'or', '17', 'or', '18');
    }
}
