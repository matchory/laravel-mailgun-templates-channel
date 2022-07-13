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

namespace Matchory\MailgunTemplatedMessages\Messages;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\Pure;

use function substr;

/**
 * @template T of MailgunTemplatedMessage
 * @bundle   Matchory\MailgunTemplatedMessages
 */
trait OptionTrait
{
    /**
     * @var array<string, scalar|null>
     */
    private array $options = [];

    /**
     * Retrieves the message options.
     *
     * @return array<string, scalar|null> Message options as key-value pairs.
     */
    #[Pure]
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Sets multiple options.
     *
     * @param array<string, scalar|null> $options Message options as key-value
     *                                            pairs.
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $name => $value) {
            $this->addOption($name, $value);
        }
    }

    /**
     * Adds an option to the message.
     *
     * @param string      $name  Name of the option.
     * @param scalar|null $value Value of the option.
     *
     * @psalm-suppress MixedPropertyTypeCoercion
     */
    public function addOption(string $name, mixed $value): void
    {
        if (str_starts_with($name, 'o:')) {
            $name = substr($name, 2);
        }

        Arr::set($this->options, $name, $value);
    }

    /**
     * Sets the delivery time option.
     *
     * @param DateTimeInterface|string $dateTime Date and time after which to
     *                                           deliver the message.
     * @param DateTimeZone|string|null $timezone Timezone to create the date in.
     *
     * @return T Instance for chaining.
     * @throws Exception If date construction fails.
     */
    public function deliverAt(
        DateTimeInterface|string $dateTime,
        DateTimeZone|string|null $timezone = null
    ): static {
        $timezoneInstance = $this->inferTimezone($timezone);

        if (
            $dateTime instanceof DateTime ||
            $dateTime instanceof DateTimeImmutable
        ) {
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
     * Checks whether the message has a given option configured.
     *
     * @param string $name Name of the option.
     *
     * @return bool Whether the option is currently set.
     */
    public function hasOption(string $name): bool
    {
        return Arr::has($this->options, $name);
    }

    /**
     * Sets a single option.
     *
     * @param string      $name  Name of the option.
     * @param scalar|null $value Value of the option.
     *
     * @return T
     * @see self::addOption()
     */
    public function option(string $name, mixed $value): static
    {
        $this->addOption($name, $value);

        return $this;
    }

    /**
     * Sets multiple options.
     *
     * @param array<string, scalar|null> $options Options as key-value pairs.
     *
     * @return T
     * @see self::setOptions()
     */
    public function options(array $options): static
    {
        $this->setOptions($options);

        return $this;
    }

    /**
     * Removes an option from the configured options.
     *
     * @param string $name Name of the option.
     *
     * @psalm-suppress MixedPropertyTypeCoercion
     */
    public function removeOption(string $name): void
    {
        Arr::forget($this->options, $name);
    }

    /**
     * Removes an option.
     *
     * @param string $name Name of the option to remove.
     *
     * @return T
     * @see self::removeOption()
     */
    public function withoutOption(string $name): static
    {
        $this->removeOption($name);

        return $this;
    }

    /**
     * Retrieves the encoded options.
     *
     * @return array<string, scalar|null> Encoded options.
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
