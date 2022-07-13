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
trait FluentTrait
{
    /**
     * Sets the blind carbon copy (BCC) address.
     *
     * @param string|null $blindCarbonCopy BCC address.
     *
     * @return T
     */
    public function bcc(string|null $blindCarbonCopy): static
    {
        $this->setBlindCarbonCopy($blindCarbonCopy);

        return $this;
    }

    /**
     * Sets the carbon copy (CC) address.
     *
     * @param string|null $carbonCopy CC address.
     *
     * @return T
     */
    public function cc(string|null $carbonCopy): static
    {
        $this->setCarbonCopy($carbonCopy);

        return $this;
    }

    /**
     * Sets the sender.
     *
     * @param string|null $sender Sender email address and/or name.
     *
     * @return T
     */
    public function from(string|null $sender): static
    {
        $this->setSender($sender);

        return $this;
    }

    /**
     * Checks whether the message has a domain configured.
     *
     * @return bool Whether the domain is currently set.
     */
    #[Pure]
    public function hasDomain(): bool
    {
        return $this->getDomain() !== null;
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
     * Checks whether the message has a given template parameter configured.
     *
     * @param string $name Name of the template parameter.
     *
     * @return bool Whether the template parameter is currently set.
     */
    public function hasParam(string $name): bool
    {
        return Arr::has($this->parameters, $name);
    }

    /**
     * Checks whether the message has a recipient configured.
     *
     * @return bool Whether the recipient is currently set.
     */
    #[Pure]
    public function hasRecipient(): bool
    {
        return $this->getRecipient() !== null;
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
     * Sets a parameter.
     *
     * @param string      $name  Name of the parameter.
     * @param scalar|null $value Value of the parameter.
     *
     * @return T
     * @see self::addParam()
     */
    public function param(string $name, mixed $value): static
    {
        $this->addParam($name, $value);

        return $this;
    }

    /**
     * Sets multiple parameters.
     *
     * @param array<string, scalar|null> $parameters Parameters as key-value
     *                                               pairs.
     *
     * @return T
     * @see self::setParams()
     */
    public function params(array $parameters): static
    {
        $this->setParams($parameters);

        return $this;
    }

    /**
     * Sets the subject.
     *
     * @param string $subject Message subject.
     *
     * @return T
     */
    public function subject(string $subject): static
    {
        $this->setSubject($subject);

        return $this;
    }

    /**
     * Sets the recipient.
     *
     * @param string|null $recipient Recipient to set. If null, the current
     *                               recipient will be removed. Unless a new
     *                               recipient is given while sending the
     *                               message, the message will not be sent.
     *
     * @return T
     */
    public function to(string|null $recipient): static
    {
        $this->setRecipient($recipient);

        return $this;
    }

    /**
     * Sets the sending domain.
     *
     * @param string $domain Domain to send over. Must be configured on Mailgun.
     *
     * @return T
     */
    public function via(string $domain): static
    {
        $this->setDomain($domain);

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
     * Removes a template parameter.
     *
     * @param string $name Name of the template parameter to remove.
     *
     * @return T
     * @see self::removeParam()
     */
    public function withoutParam(string $name): static
    {
        $this->removeParam($name);

        return $this;
    }
}
