<?php

/**
 * This file is part of laravel-mailgun-templated-messages, a Matchory application.
 *
 * @copyright 2020–2022 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\MailgunTemplatedMessages;

use Illuminate\Contracts\Events\Dispatcher;
use JsonException;
use Mailgun\Mailgun;
use Mailgun\Model\Message\SendResponse;
use Matchory\MailgunTemplatedMessages\Events\MailgunTemplateMessageSent;
use Matchory\MailgunTemplatedMessages\Messages\MailgunTemplatedMessage;

use function assert;
use function sprintf;

/**
 * Mailgun Templates Client
 *
 * Allows sending templated messages via the Mailgun API. The client wraps
 * around the Mailgun SDK essentially, performing type checking and event
 * dispatching after messages are sent.
 *
 * As soon as the Mailgun SDK supports managing messages templates, the client
 * will get support for that, too.
 *
 * @see    https://github.com/mailgun/mailgun-php/issues/832
 * @bundle Matchory\MailgunTemplatedMessages
 */
class MailgunTemplatesClient
{
    public function __construct(
        private readonly Mailgun $mailgun,
        private readonly Dispatcher $dispatcher,
        private readonly string $domain
    ) {
    }

    /**
     * Sends a Mailgun templated message.
     *
     * @param MailgunTemplatedMessage $message Message instance to send.
     *
     * @return SendResponse Send response as received from the Mailgun client.
     * @throws JsonException If parameter serialization fails.
     */
    public function send(MailgunTemplatedMessage $message): SendResponse
    {
        $response = $this->mailgun->messages()->send(
            $message->getDomain() ?? $this->getDomain(),
            $message->toArray()
        );

        assert($response instanceof SendResponse, sprintf(
            'Expected %s instance, got %s',
            SendResponse::class,
            get_debug_type($response)
        ));

        $this->dispatcher->dispatch(new MailgunTemplateMessageSent(
            $response->getId(),
            $response->getMessage()
        ));

        return $response;
    }

    /**
     * Retrieves the sending domain.
     *
     * @return string
     */
    final protected function getDomain(): string
    {
        return $this->domain;
    }
}
