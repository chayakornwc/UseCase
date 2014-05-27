<?php

namespace OpenClassrooms\Tests\UseCase\Application\Services\Proxy\UseCases;

use Doctrine\Common\Annotations\AnnotationReader;
use OpenClassrooms\UseCase\Application\Services\Proxy\UseCases\Impl\UseCaseProxyImpl;
use OpenClassrooms\Tests\UseCase\BusinessRules\Requestors\UseCaseRequestStub;
use OpenClassrooms\Tests\UseCase\BusinessRules\Responders\UseCaseResponseStub;
use OpenClassrooms\Tests\UseCase\BusinessRules\UseCases\Cache\OnlyCacheUseCaseStub;
use
    OpenClassrooms\Tests\UseCase\BusinessRules\UseCases\Event\OnlyEventNameEventUseCaseStub;
use
    OpenClassrooms\Tests\UseCase\BusinessRules\UseCases\Security\OnlyRoleSecurityUseCaseStub;
use
    OpenClassrooms\Tests\UseCase\BusinessRules\UseCases\Transaction\OnlyTransactionUseCaseStub;
use OpenClassrooms\Tests\UseCase\BusinessRules\UseCases\UseCaseStub;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ConfigurationUseCaseProxyTest extends AbstractUseCaseProxyTest
{
    /**
     * @test
     */
    public function WithoutAnnotationWithoutConfiguration_ReturnResponse()
    {
        $this->useCaseProxy = new UseCaseProxyImpl();
        $this->useCaseProxy->setReader(new AnnotationReader());

        $this->useCaseProxy->setUseCase(new UseCaseStub());
        $this->executeAndAssert();
    }

    protected function executeAndAssert()
    {
        $response = $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertEquals(new UseCaseResponseStub(), $response);
    }

    /**
     * @test
     */
    public function WithSecurityAnnotationWithSecurityConfiguration_ReturnResponse()
    {
        $this->buildSecurityStrategy();
        $this->useCaseProxy->setUseCase(new OnlyRoleSecurityUseCaseStub());
        $this->executeAndAssert();
    }

    /**
     * @test
     */
    public function WithCacheAnnotationWithCacheConfiguration_ReturnResponse()
    {
        $this->buildCacheStrategy();
        $this->useCaseProxy->setUseCase(new OnlyCacheUseCaseStub());
        $this->executeAndAssert();
    }

    /**
     * @test
     */
    public function WithTransactionAnnotationWithTransactionConfiguration_ReturnResponse()
    {
        $this->buildTransactionStrategy();
        $this->useCaseProxy->setUseCase(new OnlyTransactionUseCaseStub());
        $this->executeAndAssert();
    }

    /**
     * @test
     */
    public function WithEventAnnotationWithCacheConfiguration_ReturnResponse()
    {
        $this->buildEventStrategy();
        $this->useCaseProxy->setUseCase(new OnlyEventNameEventUseCaseStub());
        $this->executeAndAssert();
    }

    protected function setUp()
    {
        $this->initUseCaseProxy();
    }

}