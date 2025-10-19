<?php

namespace App\Mail;

use SendGrid;
use SendGrid\Mail\Mail as SendGridMail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class SendGridTransport extends AbstractTransport
{
    /**
     * @var \SendGrid
     */
    protected $sendGrid;

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->sendGrid = new SendGrid($apiKey);
    }

    protected function doSend(SentMessage $sentMessage): void
    {
        $raw = $sentMessage->getOriginalMessage();

        // Laravel/Symfony typically pass an Email instance here
        if (! $raw instanceof Email) {
            $sg = new SendGridMail();
            $sg->setFrom(config('mail.from.address'), config('mail.from.name'));
            $sg->setSubject('(no subject)');
            $sg->addContent('text/plain', (string) $raw->toString());
            $this->sendGrid->send($sg);
            return;
        }

        $sgEmail = new SendGridMail();

        // From
        $from = $raw->getFrom()[0] ?? null;
        if ($from) {
            $sgEmail->setFrom($from->getAddress(), $from->getName() ?? null);
        } else {
            $sgEmail->setFrom(config('mail.from.address'), config('mail.from.name'));
        }

        // To, Cc, Bcc
        foreach ($raw->getTo() ?? [] as $addr) {
            $sgEmail->addTo($addr->getAddress(), $addr->getName() ?? null);
        }
        foreach ($raw->getCc() ?? [] as $addr) {
            $sgEmail->addCc($addr->getAddress(), $addr->getName() ?? null);
        }
        foreach ($raw->getBcc() ?? [] as $addr) {
            $sgEmail->addBcc($addr->getAddress(), $addr->getName() ?? null);
        }

        // Reply-To
        $replyTo = $raw->getReplyTo()[0] ?? null;
        if ($replyTo) {
            $sgEmail->setReplyTo($replyTo->getAddress(), $replyTo->getName() ?? null);
        }

        // Subject
        $sgEmail->setSubject($raw->getSubject() ?? '');

        // Content: prefer HTML then text
        $html = $raw->getHtmlBody();
        $text = $raw->getTextBody();
        if ($text) {
            $sgEmail->addContent('text/plain', $text);
        }
        if ($html) {
            $sgEmail->addContent('text/html', $html);
        }

        // Attachments (basic support)
        foreach ($raw->getAttachments() ?? [] as $attachment) {
            $body = $attachment->getBody();
            $content = is_string($body) ? $body : stream_get_contents($body);
            $sgEmail->addAttachment(
                base64_encode($content ?: ''),
                $attachment->getMediaType().'/'.$attachment->getMediaSubtype(),
                $attachment->getFilename() ?: 'attachment',
                'attachment'
            );
        }

        // Send via SendGrid API
        $this->sendGrid->send($sgEmail);
    }

    public function __toString(): string
    {
        return 'sendgrid';
    }
}
