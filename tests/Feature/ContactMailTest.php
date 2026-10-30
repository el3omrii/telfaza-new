<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactMailTest extends TestCase
{
    private const VALIDATION_SECRET = 'test-contact-validation-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'contact.validation_secret' => self::VALIDATION_SECRET,
            'contact.mail_to' => 'contact@example.com',
        ]);
    }

    public function test_valid_contact_message_is_sent_to_the_configured_recipient(): void
    {
        Mail::fake();

        $this->postJson($this->endpoint(), [
            'email' => 'visitor@example.net',
            'subject' => 'Question about the service',
            'body' => 'I would like to know more about your service.',
        ], ['X-Validation-Secret' => self::VALIDATION_SECRET])
            ->assertOk()
            ->assertExactJson(['ok' => true]);

        Mail::assertSent(ContactMessage::class);

        $mail = Mail::sent(ContactMessage::class)->first();
        $this->assertNotNull($mail);
        $this->assertTrue($mail->hasTo('contact@example.com'));
        $this->assertTrue($mail->hasReplyTo('visitor@example.net'));
        $this->assertSame('Contact form: Question about the service', $mail->envelope()->subject);
        $this->assertSame('I would like to know more about your service.', $mail->messageBody);

        $rendered = $mail->render();
        $this->assertStringContainsString('visitor@example.net', $rendered);
        $this->assertStringContainsString('I would like to know more about your service.', $rendered);
    }

    public function test_request_without_the_validation_secret_is_rejected(): void
    {
        Mail::fake();

        $this->postJson($this->endpoint(), [
            'email' => 'visitor@example.net',
            'subject' => 'A question',
            'body' => 'This message is long enough.',
        ])->assertUnauthorized();

        Mail::assertNothingSent();
    }

    public function test_invalid_contact_message_is_not_sent(): void
    {
        Mail::fake();

        $this->postJson($this->endpoint(), [
            'email' => 'not-an-email',
            'subject' => "Invalid\r\nSubject",
            'body' => 'Short',
        ], ['X-Validation-Secret' => self::VALIDATION_SECRET])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'subject', 'body']);

        Mail::assertNothingSent();
    }

    private function endpoint(): string
    {
        $domain = env('APP_API_DOMAIN') ?: 'localhost';

        return 'http://'.$domain.'/api/sendmail';
    }
}
