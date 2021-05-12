<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Support\Facades\Log;

class AutomationInfo extends Notification
{
    use Queueable;

    protected $autos;
    //protected $teste;


    public function __construct($autos)
    {
        $this->autos = $autos;
        //$this->teste = $teste;
        //dd($teste);
    }


    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        $url = 'teste';

        return (new SlackMessage)
            ->warning()
            ->to('general')
            ->from('Salesforce Automation', ':white_check_mark')
            ->content('*Info - Automation:* ' . $notifiable->automation)
            ->attachment(function ($attachment) use ($url, $notifiable) {
                $attachment->title('Dados', $url)
                    ->fields([
                        'Automation' => $notifiable->automation,
                        'Last Run' => date('d-m-Y H:i:s', strtotime('+3 hours', strtotime($notifiable->startTime))),
                        'CustomerKey' => $notifiable->customerKey,
                        'Status' => $notifiable->statusMessage,
                        'Data Extension' => $notifiable->dataExtension,
                        'Rows Data Extension' => $notifiable->dataExtension_count,
                    ]);
            });
    }
}
