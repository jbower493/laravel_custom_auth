<?php

namespace App\Mail;

use App\Models\Recipe;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SharedRecipe extends Mailable
{
    use Queueable, SerializesModels;

    private $sharerName;
    private $recipientIsExistingUser;
    private $shareRequestId;
    private $recipe;
    private $recipientEmail;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($sharerName, $recipientEmail, $recipientIsExistingUser, $shareRequestId, Recipe $recipe)
    {
        $this->sharerName = $sharerName;
        $this->recipientIsExistingUser = $recipientIsExistingUser;
        $this->shareRequestId = $shareRequestId;
        $this->recipe = $recipe;
        $this->recipientEmail = $recipientEmail;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Shared Recipe',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        $urlActionSearchParam = $this->recipientIsExistingUser ? 'login' : 'register';

        return new Content(
            markdown: 'emails.sharedRecipe',
            with: [
                'url' => 'http://localhost:3000/recipes/accept-shared/' . $this->shareRequestId . '?action=' . $urlActionSearchParam . '&recipient-email=' . $this->recipientEmail,
                'recipeName' => $this->recipe->name,
                'recipientEmail' => $this->recipientEmail,
                'sharerName' => $this->sharerName
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
