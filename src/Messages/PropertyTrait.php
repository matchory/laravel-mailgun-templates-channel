<?php

/**
 * This file is part of laravel-mailgun-templated-messages, a Matchory application.
 *
 * @copyright 2020–2022 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\MailgunTemplatedMessages\Messages;

use JetBrains\PhpStorm\Pure;

/**
 * @template T of MailgunTemplatedMessage
 * @bundle   Matchory\MailgunTemplatedMessages
 */
trait PropertyTrait
{
    private string|null $blindCarbonCopy = null;

    private string|null $carbonCopy = null;

    private string|null $domain = null;

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
     * @param string|array|null $blindCarbonCopy BCC address.
     */
    public function setBlindCarbonCopy(string|array|null $blindCarbonCopy): void
    {
        $this->blindCarbonCopy = $this->resolveTarget($blindCarbonCopy);
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
     * @param string|array|null $carbonCopy CC address.
     */
    public function setCarbonCopy(string|array|null $carbonCopy): void
    {
        $this->carbonCopy = $this->resolveTarget($carbonCopy);
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
     * @param string|array|null $recipient Recipient to set. If null, the
     *                                     current recipient will be removed.
     *                                     Unless a new recipient is given while
     *                                     sending the message, the message will
     *                                     not be sent.
     */
    public function setRecipient(string|array|null $recipient): void
    {
        $this->recipient = $this->resolveTarget($recipient);
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
     * @param string|array|null $sender Sender email address and/or name.
     */
    public function setSender(string|array|null $sender): void
    {
        $this->sender = $this->resolveTarget($sender);
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
     * Checks whether the message has a sender configured.
     *
     * @return bool Whether the message has a sender configured.
     */
    #[Pure]
    public function hasSender(): bool
    {
        return $this->getSender() !== null;
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
     * Resolves a mail target to an addressable format.
     *
     * @param string|array|null $target Mail target in any known format.
     *
     * @return string|null Address and name, if given. Null if none of both.
     *
     * @example resolveTarget('john@example.com'); // 'john@example.com'
     * @example resolveTarget(
     *              'john@example.com <John Smith>'
     *          ); // 'john@example.com <John Smith>'
     * @example resolveTarget([
     *              'address' => 'john@example.com',
     *              'name' => 'John Smith'
     *          ]); // 'john@example.com <John Smith>'
     * @example resolveTarget([
     *              'address' => 'john@example.com'
     *          ]); // 'john@example.com'
     * @example resolveTarget([ 'john@example.com' ]); // 'john@example.com'
     * @example resolveTarget([
     *              'john@example.com' => 'John Smith'
     *          ]); // 'john@example.com <John Smith>'
     * @example resolveTarget(''); // null
     * @example resolveTarget(null); // null
     */
    abstract protected function resolveTarget(
        string|array|null $target
    ): string|null;
}
