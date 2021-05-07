<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class AutomationNotification extends Notification
{
    use Queueable;

    protected $autos;
    //protected $teste;


    public function __construct($autos) {
        $this->autos = $autos;
        //$this->teste = $teste;
        //dd($teste);
    }


    public function via($notifiable) {
        return ['slack'];
    }

    public function toSlack($notifiable) {
        $url = 'teste';

        // return (new SlackMessage)
        //     ->error()
        //     ->to('learning')
        //     ->content('*Automação:*  *não rodou*');

        return (new SlackMessage)
            ->error()
            ->to('learning')
            ->from('Salesforce Automation', ':warning')
            ->content('*Automação:* ' . $notifiable->automation . ' *não rodou*')
            ->attachment(function ($attachment) use ($url, $notifiable) {
                $attachment->title('Dados', $url)
                ->fields([
                    'Automation' => $notifiable->automation,
                    'CustomerKey' => $notifiable->customerKey,
                    'Status' => $notifiable->statusMessage,
                    'Date' => date('d-m-Y H-i-s', strtotime($notifiable->startTime)),
                ]);
            });
    }
}
