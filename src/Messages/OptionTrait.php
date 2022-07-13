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
     * @var array<string, scalar|scalar[]|null>
     */
    private array $options = [];

    /**
     * Retrieves the message options.
     *
     * @return array<string, scalar|scalar[]|null> Message options as key-value
     *                                             pairs.
     */
    #[Pure]
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Sets multiple options.
     *
     * @param array<string, scalar|scalar[]|null> $options Message options as
     *                                                     key-value pairs.
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
     * @param string               $name  Name of the option.
     * @param scalar|scalar[]|null $value Value of the option.
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
     * Enables/disables DKIM signatures on per-message basis.
     *
     * @param bool $enabled Whether to enabled DKIM.
     *
     * @return $this Instance for chaining.
     * @see https://documentation.mailgun.com/en/latest/user_manual.html#sending-via-smtp
     */
    public function dkim(bool $enabled = true): static
    {
        $this->addOption('dkim', $enabled ? 'yes' : 'no');

        return $this;
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
     * @param string               $name  Name of the option.
     * @param scalar|scalar[]|null $value Value of the option.
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
     * @param array<string, scalar|scalar[]|null> $options Options as key-value
     *                                                     pairs.
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
     * Configures the message to require TLS.
     *
     * @param bool $enabled Whether to require TLS.
     *
     * @return T Instance for chaining.
     * @see self::addOption()
     * @see https://documentation.mailgun.com/en/latest/user_manual.html#tls-sending-connection-settings
     */
    public function requireTls(bool $enabled = true): static
    {
        $this->addOption('require-tls', $enabled);

        return $this;
    }

    /**
     * Configures the message to skip verification.
     *
     * @param bool $enabled Whether to skip verification.
     *
     * @return T Instance for chaining.
     * @see self::addOption()
     * @see https://documentation.mailgun.com/en/latest/user_manual.html#tls-sending-connection-settings
     */
    public function skipVerification(bool $enabled = true): static
    {
        $this->addOption('skip-verification', $enabled);

        return $this;
    }

    /**
     * Adds tags to the message.
     *
     * @param string|string[] $tag Tag or multiple tags to set on the message.
     *
     * @return T Instance for chaining.
     * @see self::addOption()
     * @see https://documentation.mailgun.com/en/latest/user_manual.html#tagging-1
     */
    public function tag(string|array $tag): static
    {
        $this->addOption('tag', $tag);

        return $this;
    }

    /**
     * Enables sending in test mode.
     *
     * @param bool $enabled Whether to enabled DKIM.
     *
     * @return $this Instance for chaining.
     * @see https://documentation.mailgun.com/en/latest/user_manual.html#manual-testmode
     */
    public function testMode(bool $enabled = true): static
    {
        $this->addOption('testmode', $enabled ? 'yes' : 'no');

        return $this;
    }

    /**
     * Configures the message to use tracking.
     *
     * @param bool $enabled Whether to use tracking.
     *
     * @return T Instance for chaining.
     * @see self::addOption()
     * @see https://documentation.mailgun.com/en/latest/user_manual.html#tracking-messages-1
     */
    public function tracking(bool $enabled = true): static
    {
        $this->addOption('tracking', $enabled);

        return $this;
    }

    /**
     * Configures the message to use click tracking.
     *
     * @param bool $enabled Whether to use click tracking.
     *
     * @return T Instance for chaining.
     * @see self::addOption()
     * @see https://documentation.mailgun.com/en/latest/user_manual.html#tracking-messages-1
     */
    public function trackingClicks(bool $enabled = true): static
    {
        $this->addOption('tracking-clicks', $enabled);

        return $this;
    }

    /**
     * Configures the message to use open tracking.
     *
     * @param bool $enabled Whether to use open tracking.
     *
     * @return T Instance for chaining.
     * @see self::addOption()
     * @see https://documentation.mailgun.com/en/latest/user_manual.html#tracking-messages-1
     */
    public function trackingOpens(bool $enabled = true): static
    {
        $this->addOption('tracking-opens', $enabled);

        return $this;
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
     * @return array<string, scalar|scalar[]|null> Encoded options.
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
