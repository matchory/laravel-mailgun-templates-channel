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

use Illuminate\Support\Collection;
use JetBrains\PhpStorm\Pure;
use JsonException;

use function array_filter;
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
     * Adds a header to the message.
     *
     * @param string          $name  Name of the header.
     * @param string|string[] $value Value of the header, or multiple values as
     *                               an array.
     *
     * @psalm-suppress MixedPropertyTypeCoercion
     */
    public function addHeader(string $name, string|array $value): void
    {
        $name = $this->normalizeHeaderName($name);

        if ( ! $this->hasHeader($name)) {
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
     * Sets a single header.
     *
     * @param string       $name  Name of the header.
     * @param string|array $value Value of the header, or multiple values as an
     *                            array.
     *
     * @return T Instance for chaining.
     * @see self::addHeader()
     */
    public function header(string $name, string|array $value): static
    {
        $this->addHeader($name, $value);

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
     * Retrieves the encoded headers.
     *
     * @return array<string, string[]> Encoded headers.
     */
    private function getEncodedHeaders(): array
    {
        return Collection
            ::make($this->getHeaders())
            ->filter(fn(array $values) => array_filter($values))
            ->mapWithKeys(fn(array $values, string $name): array => [
                "h:{$name}" => $values,
            ])
            ->all();
    }

    private function normalizeHeaderName(string $name): string
    {
        if (str_starts_with('h:', $name)) {
            $name = substr($name, 2);
        }

        return strtolower($name);
    }
}
