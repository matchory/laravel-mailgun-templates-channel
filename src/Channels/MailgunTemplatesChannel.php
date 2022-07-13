<?php

/**
 * This file is part of laravel-mailgun-templated-messages, a Matchory application.
 *
 * @copyright 2020–2022 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\MailgunTemplatedMessages\Channels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use JsonException;
use Mailgun\Model\Message\SendResponse;
use Matchory\MailgunTemplatedMessages\MailgunTemplatesChannelServiceProvider as ServiceProvider;
use Matchory\MailgunTemplatedMessages\MailgunTemplatesClient;
use Matchory\MailgunTemplatedMessages\Messages\MailgunTemplatedMessage;

use function assert;
use function get_debug_type;
use function is_array;
use function is_callable;
use function is_null;
use function is_object;
use function is_string;
use function sprintf;

/**
 * MailgunTemplatesChannel
 *
 * @bundle Matchory\MailgunTemplatedMessages
 */
class MailgunTemplatesChannel
{
    public function __construct(
        private readonly MailgunTemplatesClient $client
    ) {
    }

    /**
     * Send the given notification.
     *
     * @param Model&Notifiable $notifiable
     * @param Notification     $notification
     *
     * @return SendResponse|null
     * @throws JsonException
     */
    public function send(
        mixed $notifiable,
        Notification $notification
    ): ?SendResponse {
        assert(
            is_callable([$notification, 'toMailgun']),
            sprintf(
                'Expected %s::toMailgun() to be callable',
                get_debug_type($notification)
            )
        );

        $message = $notification->toMailgun($notifiable);
        assert(
            $message instanceof MailgunTemplatedMessage,
            sprintf(
                'Expected %s instance, got %s',
                MailgunTemplatedMessage::class,
                get_debug_type($notification),
            )
        );

        if ( ! $message->hasRecipient()) {
            $recipient = $this->routeNotification(
                $notifiable,
                $notification
            );

            if ( ! $recipient) {
                return null;
            }

            $message->to($recipient);
        }

        return $this->client->send($message);
    }

    /**
     * @param mixed|Model&Notifiable $notifiable
     * @param Notification           $notification
     *
     * @return string|null
     */
    private function routeNotification(
        mixed $notifiable,
        Notification $notification
    ): string|null {
        if (is_string($notifiable)) {
            return $notifiable;
        }

        assert(is_object($notifiable), sprintf(
            'Expected notifiable to be an email address or a class ' .
            'using the Notifiable trait, got a "%s" instead',
            get_debug_type($notifiable)
        ));

        $address = null;

        if (is_callable([$notifiable, 'routeNotificationFor'])) {
            $address = $notifiable->routeNotificationFor(
                ServiceProvider::NOTIFICATION_DRIVER,
                $notification
            );

            // Fallback to the email routing
            if ( ! $address) {
                $address = $notifiable->routeNotificationFor(
                    'mail',
                    $notification
                );
            }

            assert(
                is_string($address) ||
                is_array($address) ||
                is_null($address),
                sprintf(
                    'Expected $address to be a string, array, or ' .
                    'null, got "%s" instead',
                    get_debug_type($address)
                )
            );
        }

        if ( ! $address && $email = $notifiable->email ?? null) {
            assert(is_string($email), sprintf(
                'Expected %s->email to be a string, got "%s" instead',
                get_debug_type($notifiable),
                get_debug_type($email)
            ));

            $address = $email;
        }

        // Laravel allows the name of the mail recipient to be specified as
        // [ 'some@email.com' => 'Jane Smith' ], so we'll need to handle that
        if (is_array($address) && ! empty($address)) {
            $key = array_key_first($address);
            $address = is_string($key) ? $key : $address[0];
        }

        assert(is_string($address) || $address === null, sprintf(
            'Expected resolved address to be a string or null, ' .
            'got %s instead',
            get_debug_type($address)
        ));

        return $address;
    }
}
