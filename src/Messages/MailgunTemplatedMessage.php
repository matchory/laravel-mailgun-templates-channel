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
use function array_key_first;
use function array_merge;
use function is_string;

/**
 * Mailgun Templated Message
 *
 * Represents a message template on the Mailgun API, making it easy to send an
 * existing message template with a given set of parameters.
 *
 * @use HeaderTrait<MailgunTemplatedMessage>
 * @use OptionTrait<MailgunTemplatedMessage>
 * @use ParamTrait<MailgunTemplatedMessage>
 * @use PropertyTrait<MailgunTemplatedMessage>
 * @use TemplateTrait<MailgunTemplatedMessage>
 * @bundle Matchory\MailgunTemplatedMessages
 */
class MailgunTemplatedMessage implements Arrayable
{
    use HeaderTrait;
    use OptionTrait;
    use ParamTrait;
    use PropertyTrait;
    use TemplateTrait;

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
            $this->getEncodedHeaders(),
            $this->getEncodedParams(),
            [
                'bcc' => $this->getBlindCarbonCopy(),
                'cc' => $this->getCarbonCopy(),
                'from' => $this->getSender(),
                'subject' => $this->getSubject(),
                't:version' => $this->getVersion(),
                'template' => $this->getTemplateName(),
                'to' => $this->getRecipient(),
            ]
        ));
    }

    /**
     * Resolves a mail target to an addressable format.
     *
     * @param string|array|null $target Mail target in any known format.
     *
     * @return string|null Address and name, if given. Null if none of both.
     *
     * @example        resolveTarget('john@example.com'); // 'john@example.com'
     * @example        resolveTarget(
     *                     'John Smith <john@example.com>'
     *                 ); // 'John Smith <john@example.com>'
     * @example        resolveTarget([
     *                     'address' => 'john@example.com',
     *                     'name' => 'John Smith'
     *                 ]); // 'John Smith <john@example.com>'
     * @example        resolveTarget([
     *                     'address' => 'john@example.com'
     *                 ]); // 'john@example.com'
     * @example        resolveTarget([
     *                     'john@example.com'
     *                 ]); // 'john@example.com'
     * @example        resolveTarget([
     *                     'john@example.com' => 'John Smith'
     *                 ]); // 'John Smith <john@example.com>'
     * @example        resolveTarget(''); // null
     * @example        resolveTarget(null); // null
     * @psalm-suppress MixedReturnStatement
     * @psalm-suppress MixedInferredReturnType
     */
    protected function resolveTarget(string|array|null $target): string|null
    {
        /**
         * Psalm doesn't handle match arms correctly currently
         *
         * @psalm-suppress PossiblyInvalidArrayOffset
         * @psalm-suppress PossiblyNullArrayAccess
         * @psalm-suppress PossiblyInvalidArgument
         */
        return match (true) {
            // 'john@example.com', 'John Smith <john@example.com>'
            $target && is_string($target) => $target,

            // [ 'address' => 'john@example.com', 'name' => 'John Smith' ]
            isset(
                $target['address'],
                $target['name']
            ) => "{$target['name']} <{$target['address']}>",

            // [ 'address' => 'john@example.com' ]
            isset($target['address']) => $target['address'],

            // [ 'john@example.com' ]
            isset($target[0]) && $target[0] => $target[0],

            // [ 'john@example.com' => 'John Smith' ]
            is_string($key = array_key_first(
                $target
            )) => "{$target[$key]} <{$key}>",

            // [], '', 42, false
            default => null
        };
    }
}
