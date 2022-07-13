<?php

/**
 * This file is part of laravel-mailgun-templated-messages, a Matchory application.
 *
 * Unauthorized copying of this file, via any medium, is strictly prohibited.
 * Its contents are strictly confidential and proprietary.
 *
 * @copyright 2020–2022 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\MailgunTemplatedMessages\Tests\Channels;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use JsonException;
use Matchory\MailgunTemplatedMessages\Channels\MailgunTemplatesChannel;
use Matchory\MailgunTemplatedMessages\MailgunTemplatesClient;
use Matchory\MailgunTemplatedMessages\Messages\MailgunTemplatedMessage;
use Matchory\MailgunTemplatedMessages\Tests\TestCase;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\MethodCannotBeConfiguredException;
use PHPUnit\Framework\MockObject\MethodNameAlreadyConfiguredException;
use PHPUnit\Framework\MockObject\MethodNameNotConfiguredException;
use PHPUnit\Framework\MockObject\MethodParametersAlreadyConfiguredException;

class MailgunTemplatesChannelTest extends TestCase
{
    /**
     *
     *
     * @param mixed  $notifiable
     * @param string $expected
     *
     * @dataProvider notifiables
     * @covers       MailgunTemplatesChannel::send
     *
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws MethodCannotBeConfiguredException
     * @throws MethodNameAlreadyConfiguredException
     * @throws MethodNameNotConfiguredException
     * @throws MethodParametersAlreadyConfiguredException
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSending(mixed $notifiable, string $expected): void
    {
        $message = new MailgunTemplatedMessage('foo');
        $notification = $this->buildNotification($message);

        $client = $this->createMock(MailgunTemplatesClient::class);
        $client->expects($this->once())
               ->method('send')
               ->with($message);

        $channel = new MailgunTemplatesChannel($client);
        self::assertNull($message->getRecipient());
        $channel->send($notifiable, $notification);
        self::assertSame($expected, $message->getRecipient());
    }

    protected function notifiables(): iterable
    {
        yield 'String notifiable' => [
            'notifiable' => 'foo@bar.com',
            'expected' => 'foo@bar.com',
        ];

        $model = new class extends Model {
            use Notifiable;
        };
        /** @noinspection PhpUndefinedFieldInspection */
        $model->email = 'foo@bar.com';

        yield 'Model with email property' => [
            'notifiable' => $model,
            'expected' => 'foo@bar.com',
        ];

        $model = new class extends Model {
            use Notifiable;

            protected function email(): Attribute
            {
                return Attribute::get(static fn() => 'foo@bar.com');
            }
        };

        yield 'Model with email attribute' => [
            'notifiable' => $model,
            'expected' => 'foo@bar.com',
        ];

        $model = new class extends Model {
            use Notifiable;

            protected function routeNotificationForMailgun(): string
            {
                return 'foo@bar.com';
            }
        };

        yield 'Model with mailgun route' => [
            'notifiable' => $model,
            'expected' => 'foo@bar.com',
        ];

        $model = new class extends Model {
            use Notifiable;

            protected function routeNotificationForMail(): string
            {
                return 'foo@bar.com';
            }
        };

        yield 'Model with mail route' => [
            'notifiable' => $model,
            'expected' => 'foo@bar.com',
        ];

        $model = new class extends Model {
            use Notifiable;

            protected function routeNotificationForMail(): array
            {
                return [
                    'foo@bar.com' => 'Foo Bar',
                ];
            }
        };

        yield 'Model with mail route in array form' => [
            'notifiable' => $model,
            'expected' => 'foo@bar.com',
        ];
    }

    private function buildNotification(MailgunTemplatedMessage $message): Notification
    {
        return new class($message) extends Notification {
            public function __construct(
                private readonly MailgunTemplatedMessage $message
            ) {
            }

            public function toMailgun(): MailgunTemplatedMessage
            {
                return $this->message;
            }
        };
    }
}
