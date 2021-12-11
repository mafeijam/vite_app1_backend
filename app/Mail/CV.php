<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CV extends Mailable
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this->markdown('emails.cv')
            ->bcc('mafeijam@gmail.com')
            ->subject('J&C Employment Consultant - Interview Confirmation');
    }
}
