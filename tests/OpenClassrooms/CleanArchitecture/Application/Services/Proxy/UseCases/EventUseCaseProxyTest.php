<?php

namespace OpenClassrooms\Tests\CleanArchitecture\Application\Services\Proxy\UseCases;

use OpenClassrooms\Tests\CleanArchitecture\BusinessRules\Exceptions\UseCaseException;
use OpenClassrooms\Tests\CleanArchitecture\BusinessRules\Requestors\UseCaseRequestStub;
use OpenClassrooms\Tests\CleanArchitecture\BusinessRules\Responders\UseCaseResponseStub;
use OpenClassrooms\Tests\CleanArchitecture\BusinessRules\UseCases\Event\OnExceptionEventUseCaseStub;
use
    OpenClassrooms\Tests\CleanArchitecture\BusinessRules\UseCases\Event\OnlyEventNameEventUseCaseStub;
use OpenClassrooms\Tests\CleanArchitecture\BusinessRules\UseCases\Event\PostEventUseCaseStub;
use OpenClassrooms\Tests\CleanArchitecture\BusinessRules\UseCases\Event\PreEventUseCaseStub;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class EventUseCaseProxyTest extends AbstractUseCaseProxyTest
{
    /**
     * @test
     */
    public function OnlyEventName_ReturnResponse()
    {
        $this->useCaseProxy->setUseCase(new OnlyEventNameEventUseCaseStub());
        $response = $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertEquals(new UseCaseResponseStub(), $response);
        $this->assertTrue($this->event->sent);
        $this->assertEquals(1, $this->event->sentCount);
        $this->assertEquals(OnlyEventNameEventUseCaseStub::EVENT_NAME, $this->event->event);
        $this->assertEquals(new UseCaseRequestStub(), $this->eventFactory->useCaseRequest);
        $this->assertEquals(new UseCaseResponseStub(), $this->eventFactory->useCaseResponse);

    }

    /**
     * @test
     */
    public function PreEvent_ReturnResponse()
    {
        $this->useCaseProxy->setUseCase(new PreEventUseCaseStub());
        $response = $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertEquals(new UseCaseResponseStub(), $response);
        $this->assertTrue($this->event->sent);
        $this->assertEquals(1, $this->event->sentCount);
        $this->assertEquals(PreEventUseCaseStub::EVENT_NAME, $this->event->event);
        $this->assertEquals(new UseCaseRequestStub(), $this->eventFactory->useCaseRequest);
        $this->assertNull($this->eventFactory->useCaseResponse);
    }

    /**
     * @test
     */
    public function PostEvent_ReturnResponse()
    {
        $this->useCaseProxy->setUseCase(new PostEventUseCaseStub());
        $response = $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertEquals(new UseCaseResponseStub(), $response);
        $this->assertTrue($this->event->sent);
        $this->assertEquals(1, $this->event->sentCount);
        $this->assertEquals(PostEventUseCaseStub::EVENT_NAME, $this->event->event);
        $this->assertEquals(new UseCaseRequestStub(), $this->eventFactory->useCaseRequest);
        $this->assertEquals(new UseCaseResponseStub(), $this->eventFactory->useCaseResponse);
    }

    /**
     * @test
     */
    public function OnException_ReturnResponse()
    {
        try {
            $this->useCaseProxy->setUseCase(new OnExceptionEventUseCaseStub());
            $this->useCaseProxy->execute(new UseCaseRequestStub());
        } catch (UseCaseException $e) {
            $this->assertTrue($this->event->sent);
            $this->assertEquals(1, $this->event->sentCount);
            $this->assertEquals(OnExceptionEventUseCaseStub::EVENT_NAME, $this->event->event);
            $this->assertEquals(new UseCaseRequestStub(), $this->eventFactory->useCaseRequest);
            $this->assertNull($this->eventFactory->useCaseResponse);
            $this->assertEquals(new UseCaseException(), $this->eventFactory->exception);
        }
    }
}
