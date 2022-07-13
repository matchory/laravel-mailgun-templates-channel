<?php

/**
 * This file is part of laravel-mailgun-templated-messages, a Matchory application.
 *
 * @copyright 2020–2022 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\MailgunTemplatedMessages\Tests;

use Illuminate\Events\Dispatcher;
use Mailgun\Api\Message;
use Mailgun\Mailgun;
use Mailgun\Model\Message\SendResponse;
use Matchory\MailgunTemplatedMessages\Events\MailgunTemplateMessageSent;
use Matchory\MailgunTemplatedMessages\MailgunTemplatesClient;
use Matchory\MailgunTemplatedMessages\Messages\MailgunTemplatedMessage;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\IncompatibleReturnValueException;
use PHPUnit\Framework\MockObject\MethodCannotBeConfiguredException;
use PHPUnit\Framework\MockObject\MethodNameAlreadyConfiguredException;
use PHPUnit\Framework\MockObject\MethodNameNotConfiguredException;
use PHPUnit\Framework\MockObject\MethodParametersAlreadyConfiguredException;

class MailgunTemplatesClientTest extends TestCase
{
    /**
     * @covers \Matchory\MailgunTemplatedMessages\MailgunTemplatesClient::send
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws IncompatibleReturnValueException
     * @throws MethodCannotBeConfiguredException
     * @throws MethodNameAlreadyConfiguredException
     * @throws MethodNameNotConfiguredException
     * @throws MethodParametersAlreadyConfiguredException
     */
    public function testSend(): void
    {
        $message = new MailgunTemplatedMessage('foo');

        /**
         * @noinspection PhpUnitInvalidMockingEntityInspection
         * @noinspection ClassMockingCorrectnessInspection
         */
        $sendResponseMock = $this->createMock(SendResponse::class);
        $messagesEndpoint = $this->createMock(Message::class);
        $messagesEndpoint->expects($this->once())
                         ->method('send')
                         ->with('test', $message->toArray())
                         ->willReturn($sendResponseMock);

        $mailgun = $this->createMock(Mailgun::class);
        $mailgun->expects($this->once())
                ->method('messages')
                ->willReturn($messagesEndpoint);

        $dispatcher = $this->createMock(Dispatcher::class);
        $client = new MailgunTemplatesClient(
            $mailgun,
            $dispatcher,
            'test'
        );

        $client->send($message);
    }

    /**
     * @covers \Matchory\MailgunTemplatedMessages\MailgunTemplatesClient::send
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws IncompatibleReturnValueException
     * @throws MethodCannotBeConfiguredException
     * @throws MethodNameAlreadyConfiguredException
     * @throws MethodNameNotConfiguredException
     * @throws MethodParametersAlreadyConfiguredException
     */
    public function testSendEmitsEventAfterSending(): void
    {
        $message = new MailgunTemplatedMessage('foo');
        $messagesEndpoint = $this->createMock(Message::class);

        /**
         * @noinspection PhpUnitInvalidMockingEntityInspection
         * @noinspection ClassMockingCorrectnessInspection
         */
        $messagesEndpoint->method('send')->willReturn(
            $this->createMock(SendResponse::class)
        );

        $mailgun = $this->createMock(Mailgun::class);
        $mailgun->method('messages')
                ->willReturn($messagesEndpoint);

        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->once())
                   ->method('dispatch')
                   ->with($this->isInstanceOf(
                       MailgunTemplateMessageSent::class
                   ));

        $client = new MailgunTemplatesClient(
            $mailgun,
            $dispatcher,
            'test'
        );

        $client->send($message);
    }
}
