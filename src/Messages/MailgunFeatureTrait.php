<?php

/**
 * This file is part of laravel-mailgun-templated-messages, a Matchory application.
 *
 * @copyright 2020–2022 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\MailgunTemplatedMessages\Messages;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\Pure;
use JsonException;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @bundle Matchory\MailgunTemplatedMessages
 */
trait MailgunFeatureTrait
{
    private string $templateName;

    private string|null $templateVersion = null;

    /**
     * Retrieves the template name
     *
     * @return string Template name.
     */
    #[Pure]
    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * Sets the template name. This is only intended to be used within the
     * message itself, as the template name should not be changed after
     * construction.
     *
     * @param string $templateName Name of the template.
     */
    protected function setTemplateName(string $templateName): void
    {
        $this->templateName = $templateName;
    }

    /**
     * Retrieves the template version.
     *
     * @return string|null Template version.
     */
    public function getTemplateVersion(): string|null
    {
        return $this->templateVersion;
    }

    /**
     * Sets the template version.
     *
     * @param string $version Template version to use.
     */
    public function setTemplateVersion(string $version): void
    {
        $this->templateVersion = $version;
    }

    /**
     * Sets the delivery time option.
     *
     * @param DateTimeInterface|string $dateTime Date and time after which to
     *                                           deliver the message.
     * @param DateTimeZone|string|null $timezone Timezone to create the date in.
     *
     * @return static Instance for chaining.
     * @throws Exception If date construction fails.
     */
    public function deliverAt(
        DateTimeInterface|string $dateTime,
        DateTimeZone|string|null $timezone = null
    ): static {
        $timezoneInstance = $this->inferTimezone($timezone);

        if ($dateTime instanceof DateTimeInterface) {
            $dateTime = $dateTime->setTimeZone($timezoneInstance);
            $dateTimeInstance = $dateTime;
        } else {
            $dateTimeInstance = new DateTime(
                $dateTime,
                $timezoneInstance
            );
        }

        $formattedDateTime = $dateTimeInstance->format(
            DateTimeInterface::RFC2822
        );

        return $this->option('deliverytime', $formattedDateTime);
    }

    /**
     * Sets the template version.
     *
     * @param string $version Template version to use.
     *
     * @return static Instance for chaining.
     */
    public function version(string $version): static
    {
        $this->setTemplateVersion($version);

        return $this;
    }

    /**
     * Retrieves the encoded options.
     *
     * @return array<string, scalar> Encoded options.
     * @throws JsonException If value encoding fails.
     */
    private function getEncodedOptions(): array
    {
        return Collection
            ::make($this->getOptions())
            ->filter()
            ->mapWithKeys(fn(mixed $value, string $name): array => [
                "o:{$name}" => $value,
            ])
            ->all();
    }

    /**
     * Retrieves the encoded parameters.
     *
     * @return array<string, string> Encoded parameters.
     * @throws JsonException If value encoding fails.
     */
    private function getEncodedParameters(): array
    {
        return Collection
            ::make($this->getParams())
            ->mapWithKeys(fn(mixed $value, string $name): array => [
                "v:{$name}" => json_encode(
                    $value,
                    JSON_THROW_ON_ERROR
                ),
            ])
            ->all();
    }

    /**
     * Infers the timezone from the given value.
     *
     * @param DateTimeZone|string|null $timezone Timezone as a DateTimeZone
     *                                           instance, a string containing a
     *                                           valid timezone identifier, or
     *                                           null if the default timezone
     *                                           should be used.
     *
     * @return DateTimeZone Resolved timezone.
     */
    private function inferTimezone(
        DateTimeZone|string|null $timezone = null
    ): DateTimeZone {
        if ($timezone === null) {
            return new DateTimeZone(self::DEFAULT_DELIVERY_TIMEZONE);
        }

        if ($timezone instanceof DateTimeZone) {
            return $timezone;
        }

        return new DateTimeZone($timezone);
    }
}
