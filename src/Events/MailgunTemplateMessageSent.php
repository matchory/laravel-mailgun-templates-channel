<?php

/**
 * This file is part of laravel-mailgun-templated-messages, a Matchory application.
 *
 * @copyright 2020–2022 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\MailgunTemplatedMessages\Events;

/**
 * MailgunTemplateMessageSent
 *
 * @bundle Matchory\MailgunTemplatedMessages
 */
class MailgunTemplateMessageSent
{
    public function __construct(
        private readonly string $messageId,
        private readonly string $message,
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }
}
