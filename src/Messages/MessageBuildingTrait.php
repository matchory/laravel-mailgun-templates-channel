<?php

/**
 * This file is part of laravel-mailgun-templated-messages, a Matchory application.
 *
 * @copyright 2020–2022 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\MailgunTemplatedMessages\Messages;

use Illuminate\Support\Arr;
use JetBrains\PhpStorm\Pure;

/**
 * @template T of MailgunTemplatedMessage
 * @bundle   Matchory\MailgunTemplatedMessages
 */
trait MessageBuildingTrait
{
    private string|null $blindCarbonCopy = null;

    private string|null $carbonCopy = null;

    private string|null $domain = null;

    /**
     * @var array<string, scalar|null>
     */
    private array $options = [];

    /**
     * @var array<string, scalar|null>
     */
    private array $parameters = [];

    private string|null $recipient = null;

    private string|null $sender = null;

    private string|null $subject = null;

    /**
     * Retrieves the blind carbon copy (BCC) address.
     *
     * @return string|null BCC address if set, null otherwise.
     */
    #[Pure]
    public function getBlindCarbonCopy(): string|null
    {
        return $this->blindCarbonCopy;
    }

    /**
     * Sets the blind carbon copy (BCC) address.
     *
     * @param string|null $blindCarbonCopy BCC address.
     */
    public function setBlindCarbonCopy(string|null $blindCarbonCopy): void
    {
        $this->blindCarbonCopy = $blindCarbonCopy;
    }

    /**
     * Retrieves the carbon copy (CC) address.
     *
     * @return string|null CC address if set, null otherwise.
     */
    #[Pure]
    public function getCarbonCopy(): string|null
    {
        return $this->carbonCopy;
    }

    /**
     * Sets the carbon copy (CC) address.
     *
     * @param string|null $carbonCopy CC address.
     */
    public function setCarbonCopy(string|null $carbonCopy): void
    {
        $this->carbonCopy = $carbonCopy;
    }

    /**
     * Retrieves the sending domain.
     *
     * @return string|null Sending domain if set, null otherwise.
     */
    #[Pure]
    public function getDomain(): string|null
    {
        return $this->domain;
    }

    /**
     * Sets the sending domain.
     *
     * @param string $domain Domain to send over. Must be configured on Mailgun.
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

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
     * Retrieves the template parameters.
     *
     * @return array<string, scalar|null> Template parameters as key-value
     *                                    pairs.
     */
    #[Pure]
    public function getParams(): array
    {
        return $this->parameters;
    }

    /**
     * Retrieves the message recipient.
     *
     * @return string|null Recipient if set, null otherwise.
     */
    #[Pure]
    public function getRecipient(): string|null
    {
        return $this->recipient;
    }

    /**
     * Sets the recipient.
     *
     * @param string|null $recipient Recipient to set. If null, the current
     *                               recipient will be removed. Unless a new
     *                               recipient is given while sending the
     *                               message, the message will not be sent.
     */
    public function setRecipient(string|null $recipient): void
    {
        $this->recipient = $recipient;
    }

    /**
     * Retrieves the message sender.
     *
     * @return string|null Sender if set, null otherwise.
     */
    #[Pure]
    public function getSender(): string|null
    {
        return $this->sender;
    }

    /**
     * Sets the sender.
     *
     * @param string|null $sender Sender email address and/or name.
     */
    public function setSender(string|null $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * Retrieves the message subject.
     *
     * @return string|null Subject if set, null otherwise.
     */
    #[Pure]
    public function getSubject(): string|null
    {
        return $this->subject;
    }

    /**
     * Sets the subject.
     *
     * @param string $subject Message subject.
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * Sets multiple template parameters.
     *
     * @param array<string, scalar|null> $parameters Template parameters as
     *                                               key-value pairs.
     */
    public function setParams(array $parameters): void
    {
        foreach ($parameters as $name => $value) {
            $this->addParam($name, $value);
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
        Arr::set($this->options, $name, $value);
    }

    /**
     * Adds a template parameter to the message.
     *
     * @param string      $name  Name of the template parameter.
     * @param scalar|null $value Value of the template parameter.
     *
     * @psalm-suppress MixedPropertyTypeCoercion
     */
    public function addParam(string $name, mixed $value): void
    {
        Arr::set($this->parameters, $name, $value);
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
     * Removes a parameter from the configured template parameters.
     *
     * @param string $name Name of the parameter.
     *
     * @psalm-suppress MixedPropertyTypeCoercion
     */
    public function removeParam(string $name): void
    {
        Arr::forget($this->parameters, $name);
    }
}
