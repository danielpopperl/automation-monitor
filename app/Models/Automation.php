<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


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

    public function scopeNotInAutomation($query) {
        return $query->whereNotIn('automation', ['PR_Aviso_Boleto_Massivo']);
    }

    public function scopeInAutomation($query) {
        return $query->whereIn('automation', ['PR_Aviso_Boleto_Massivo'])->whereDate();
    }
}
