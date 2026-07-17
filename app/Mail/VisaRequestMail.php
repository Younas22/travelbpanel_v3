<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\VisaRequest;
class VisaRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $visa;

    public function __construct(VisaRequest $visa)
    {
        $this->visa = $visa;
    }


        public function build()
    {
        return $this->subject('New Visa Application Received')
                    ->view('emails.visa_request');
    }


    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }


    public function attachments(): array
    {
        return [];
    }
}
