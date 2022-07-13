<?php

/**
 * This file is part of laravel-mailgun-templated-messages, a Matchory application.
 *
 * @copyright 2020–2022 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\MailgunTemplatedMessages\Tests\Hooks;

use DG\BypassFinals;
use PHPUnit\Runner\BeforeTestHook;

/**
 * Bypass Final Hook
 *
 * Hooks into the PhpUnit process to make final classes non-final. This is
 * required to properly test Mailgun responses.
 *
 * @bundle Matchory\MailgunTemplatedMessages\Tests\Hooks
 */
final class BypassFinalHook implements BeforeTestHook
{
    public function executeBeforeTest(string $test): void
    {
        BypassFinals::enable();
    }
}
