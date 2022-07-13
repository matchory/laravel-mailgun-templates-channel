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
trait TemplateTrait
{
    private string $templateName;

    private string|null $version = null;

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
    final protected function setTemplateName(string $templateName): void
    {
        $this->templateName = $templateName;
    }

    /**
     * Retrieves the template version.
     *
     * @return string|null Template version.
     */
    public function getVersion(): string|null
    {
        return $this->version;
    }

    /**
     * Sets the template version.
     *
     * @param string $version Template version to use.
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * Sets the template version.
     *
     * @param string $version Template version to use.
     *
     * @return T Instance for chaining.
     */
    public function version(string $version): static
    {
        $this->setVersion($version);

        return $this;
    }
}
