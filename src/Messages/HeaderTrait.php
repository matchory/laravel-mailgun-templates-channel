<?php

/**
 * This file is part of laravel-mailgun-templated-messages, a Matchory application.
 *
 * @copyright 2020–2022 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\MailgunTemplatedMessages\Messages;

use Illuminate\Support\Collection;
use JetBrains\PhpStorm\Pure;

use function is_array;
use function str_starts_with;
use function strtolower;
use function substr;

/**
 * @template T of MailgunTemplatedMessage
 * @bundle   Matchory\MailgunTemplatedMessages
 */
trait HeaderTrait
{
    /**
     * @var array<string, string[]>
     */
    private array $headers = [];

    /**
     * Retrieves the message headers.
     *
     * @return array<string, string[]> Message headers as key-value pairs.
     */
    #[Pure]
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Sets multiple headers.
     *
     * @param array<string, string|string[]> $headers Message headers as
     *                                                key-value pairs.
     */
    public function setHeaders(array $headers): void
    {
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }
    }

    /**
     * Sets a reply to address header.
     *
     * @param string|array $replyTo Reply to address.
     */
    public function setReplyTo(string|array $replyTo): void
    {
        $target = $this->resolveTarget($replyTo);

        if ($target) {
            $this->addHeader('reply-to', $target, true);
        }
    }

    /**
     * Sets a return path address header.
     *
     * @param string|array $returnPath Return path address.
     */
    public function setReturnPath(string|array $returnPath): void
    {
        $target = $this->resolveTarget($returnPath);

        if ($target) {
            $this->addHeader('return-path', $target, true);
        }
    }

    /**
     * Adds a header to the message.
     *
     * @param string          $name    Name of the header.
     * @param string|string[] $value   Value of the header, or multiple values
     *                                 as an array.
     * @param bool            $replace Whether any existing header value should
     *                                 be replaced with the new value.
     *
     * @psalm-suppress MixedPropertyTypeCoercion
     */
    public function addHeader(
        string $name,
        string|array $value,
        bool $replace = false
    ): void {
        $name = $this->normalizeHeaderName($name);

        if ($replace || ! $this->hasHeader($name)) {
            $this->headers[$name] = [];
        }

        if ( ! is_array($value)) {
            $value = [$value];
        }

        foreach ($value as $item) {
            $this->headers[$name][] = $item;
        }
    }

    /**
     * Checks whether the message has a given header configured.
     *
     * @param string $name Name of the header.
     *
     * @return bool Whether the header is currently set.
     */
    public function hasHeader(string $name): bool
    {
        $name = $this->normalizeHeaderName($name);

        return isset($this->headers[$name]);
    }

    /**
     * Checks whether the message has a reply to header set.
     *
     * @return bool Whether the message has a reply to header set.
     */
    public function hasReplyTo(): bool
    {
        return $this->hasHeader('reply-to');
    }

    /**
     * Checks whether the message has a return path header set.
     *
     * @return bool Whether the message has a return path header set.
     */
    public function hasReturnPath(): bool
    {
        return $this->hasHeader('return-path');
    }

    /**
     * Sets a single header.
     *
     * @param string          $name    Name of the header.
     * @param string|string[] $value   Value of the header, or multiple values
     *                                 as an array.
     * @param bool            $replace Whether any existing header value should
     *                                 be replaced with the new value.
     *
     * @return T Instance for chaining.
     * @see self::addHeader()
     */
    public function header(
        string $name,
        string|array $value,
        bool $replace = false
    ): static {
        $this->addHeader($name, $value, $replace);

        return $this;
    }

    /**
     * Sets multiple headers.
     *
     * @param array<string, string|string[]> $headers Headers as key-value
     *                                                pairs.
     *
     * @return T Instance for chaining.
     * @see self::setHeaders()
     */
    public function headers(array $headers): static
    {
        $this->setHeaders($headers);

        return $this;
    }

    /**
     * Removes a header from the configured headers.
     *
     * @param string $name Name of the header.
     *
     * @psalm-suppress MixedPropertyTypeCoercion
     */
    public function removeHeader(string $name): void
    {
        $name = $this->normalizeHeaderName($name);

        unset($this->headers[$name]);
    }

    /**
     * Sets a reply to header.
     *
     * @param string|array $replyTo Reply to address.
     *
     * @return T Instance for chaining.
     * @see self::setReplyTo()
     */
    public function replyTo(string|array $replyTo): static
    {
        $this->setReplyTo($replyTo);

        return $this;
    }

    /**
     * Sets a return path header.
     *
     * @param string|array $returnPath Reply to address.
     *
     * @return T Instance for chaining.
     * @see self::setReturnPath()
     */
    public function returnPath(string|array $returnPath): static
    {
        $this->setReturnPath($returnPath);

        return $this;
    }

    /**
     * Removes a header.
     *
     * @param string $name Name of the header to remove.
     *
     * @return T
     * @see self::removeHeader()
     */
    public function withoutHeader(string $name): static
    {
        $this->removeHeader($name);

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

    /**
     * Retrieves the encoded headers.
     *
     * @return array<string, string[]> Encoded headers.
     */
    private function getEncodedHeaders(): array
    {
        return Collection
            ::make($this->getHeaders())
            ->filter(fn(array $values) => (bool)$values)
            ->mapWithKeys(fn(array $values, string $name): array => [
                "h:{$name}" => $values,
            ])
            ->all();
    }

    /**
     * Normalizes a header name by removing the prefix, if any, and converting
     * the name to lowercase.
     *
     * @param string $name Name of the header.
     *
     * @return string Normalized header name.
     */
    private function normalizeHeaderName(string $name): string
    {
        if (str_starts_with($name, 'h:')) {
            $name = substr($name, 2);
        }

        return strtolower($name);
    }
}
