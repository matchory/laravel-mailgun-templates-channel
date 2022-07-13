<?php

/**
 * This file is part of laravel-mailgun-templated-messages, a Matchory application.
 *
 * @copyright 2020–2022 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\MailgunTemplatedMessages\Messages;

use Illuminate\Contracts\Support\Arrayable;
use JsonException;

use function array_filter;
use function array_merge;

/**
 * Mailgun Templated Message
 *
 * Represents a message template on the Mailgun API, making it easy to send an
 * existing message template with a given set of parameters.
 *
 * @use FluentTrait<MailgunTemplatedMessage>
 * @use MailgunFeatureTrait<MailgunTemplatedMessage>
 * @use MessageBuildingTrait<MailgunTemplatedMessage>
 * @bundle Matchory\MailgunTemplatedMessages
 */
class MailgunTemplatedMessage implements Arrayable
{
    use FluentTrait;
    use MailgunFeatureTrait;
    use MessageBuildingTrait;

    private const DEFAULT_DELIVERY_TIMEZONE = 'UTC';

    /**
     * @param string                          $templateName Name of the
     *                                                      message template.
     * @param array<string, scalar|null>|null $params       Parameters to set.
     */
    final public function __construct(
        string $templateName,
        array|null $params = null
    ) {
        $this->setTemplateName($templateName);

        if ($params) {
            $this->params($params);
        }
    }

    /**
     * Creates a new templated message instance.
     *
     * Shorthand for using the constructor.
     *
     * @param string $templateName Name of the template.
     *
     * @return static
     */
    public static function for(string $templateName): static
    {
        return new static($templateName);
    }

    /**
     * Converts the message to an array.
     *
     * @inheritdoc
     * @throws JsonException If parameter encoding fails
     */
    public function toArray(): array
    {
        return array_filter(array_merge(
            $this->getEncodedOptions(),
            $this->getEncodedParameters(),
            [
                'bcc' => $this->getBlindCarbonCopy(),
                'cc' => $this->getCarbonCopy(),
                'from' => $this->getSender(),
                'subject' => $this->getSubject(),
                't:version' => $this->getTemplateVersion(),
                'template' => $this->getTemplateName(),
                'to' => $this->getRecipient(),
            ]
        ));
    }
}
