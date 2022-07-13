<?php

/**
 * This file is part of laravel-mailgun-templated-messages, a Matchory application.
 *
 * @copyright 2020–2022 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\MailgunTemplatedMessages;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use LogicException;
use Mailgun\Mailgun;
use Matchory\MailgunTemplatedMessages\Channels\MailgunTemplatesChannel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;
use function is_array;
use function is_string;

/**
 * Mailgun Templates Channel Service Provider
 *
 * @bundle Matchory\MailgunTemplatedMessages
 */
class MailgunTemplatesChannelServiceProvider extends ServiceProvider
{
    final public const NOTIFICATION_DRIVER = 'mailgun';

    /**
     * Register the application services.
     *
     * @throws ContainerExceptionInterface
     * @throws LogicException
     * @throws NotFoundExceptionInterface
     */
    public function register(): void
    {
        $this->registerSdk();
        $this->registerClient();
        $this->extendNotificationChannels();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function extendNotificationChannels(): void
    {
        Notification::resolved(static fn(ChannelManager $service) => $service->extend(
            self::NOTIFICATION_DRIVER,
            function (Container $app): MailgunTemplatesChannel {
                $channel = $app->get(MailgunTemplatesChannel::class);

                assert($channel instanceof MailgunTemplatesChannel);

                return $channel;
            }
        ));
    }

    private function registerClient(): void
    {
        $this->app->bind(MailgunTemplatesClient::class);
        $this->app
            ->when(MailgunTemplatesClient::class)
            ->needs('$domain')
            ->giveConfig('services.mailgun.domain');
    }

    /**
     * @throws LogicException
     */
    private function registerSdk(): void
    {
        $this->app->bind(Mailgun::class, static function (
            Container $app
        ): Mailgun {
            $repository = $app->get('config');
            assert($repository instanceof ConfigRepository);
            $config = $repository->get('services.mailgun', []);
            assert(is_array($config));
            $secret = $config['secret'] ?? null;
            $endpoint = $config['endpoint'] ?? null;

            assert(
                is_string($secret),
                'Expected Mailgun secret to be set'
            );

            // This is technically necessary, as the endpoint parameter of the
            // Mailgun SDK is typed as string only, so we should not pass NULL
            if ($endpoint) {
                assert(is_string($endpoint));

                return Mailgun::create($secret, $endpoint);
            }

            return Mailgun::create($secret);
        });

        $this->app->alias(Mailgun::class, 'mailgun');
    }
}
