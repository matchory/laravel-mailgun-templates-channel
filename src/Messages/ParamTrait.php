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

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\Pure;
use JsonException;

use function json_encode;
use function substr;

use const JSON_THROW_ON_ERROR;

/**
 * @template T of MailgunTemplatedMessage
 * @bundle   Matchory\MailgunTemplatedMessages
 */
trait ParamTrait
{
    /**
     * @var array<string, scalar|null>
     */
    private array $parameters = [];

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
     * Adds a template parameter to the message.
     *
     * @param string      $name  Name of the template parameter.
     * @param scalar|null $value Value of the template parameter.
     *
     * @psalm-suppress MixedPropertyTypeCoercion
     */
    public function addParam(string $name, mixed $value): void
    {
        if (str_starts_with('v:', $name)) {
            $name = substr($name, 2);
        }

        Arr::set($this->parameters, $name, $value);
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

    /**
     * Retrieves the encoded parameters.
     *
     * @return array<string, string> Encoded parameters.
     * @throws JsonException If value encoding fails.
     */
    protected function getEncodedParams(): array
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
}
