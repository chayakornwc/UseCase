<?php

namespace OpenClassrooms\Tests\CleanArchitecture\Application\Services\Proxy\UseCases;

use OpenClassrooms\Tests\CleanArchitecture\BusinessRules\Requestors\UseCaseRequestStub;
use
    OpenClassrooms\Tests\CleanArchitecture\BusinessRules\UseCases\Security\FieldRoleSecurityUseCaseStub;
use
    OpenClassrooms\Tests\CleanArchitecture\BusinessRules\UseCases\Security\ManyRolesSecurityUseCaseStub;
use
    OpenClassrooms\Tests\CleanArchitecture\BusinessRules\UseCases\Security\OnlyRoleNotAuthorizedSecurityUseCaseStub;
use
    OpenClassrooms\Tests\CleanArchitecture\BusinessRules\UseCases\Security\OnlyRoleSecurityUseCaseStub;
use
    OpenClassrooms\Tests\CleanArchitecture\BusinessRules\UseCases\Security\RequestRoleSecurityUseCaseStub;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class SecurityUseCaseProxyTest extends AbstractUseCaseProxyTest
{
    /**
     * @test
     * @expectedException \OpenClassrooms\Tests\CleanArchitecture\Application\Services\Security\Exceptions\AccessDeniedException
     */
    public function OnlyRoleNotAuthorized_ThrowException()
    {
        $this->useCaseProxy->setUseCase(new OnlyRoleNotAuthorizedSecurityUseCaseStub());
        $this->useCaseProxy->execute(new UseCaseRequestStub());
    }

    /**
     * @test
     */
    public function OnlyAuthorizedRole_DonTThrowException()
    {
        $this->useCaseProxy->setUseCase(new OnlyRoleSecurityUseCaseStub());
        $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertNull($this->security->object);
    }

    /**
     * @test
     */
    public function ManyRoles_DonTThrowException()
    {
        $this->useCaseProxy->setUseCase(new ManyRolesSecurityUseCaseStub());
        $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertEquals(array('ROLE_1', 'ROLE_2'), $this->security->attributes);
        $this->assertNull($this->security->object);
    }

    /**
     * @test
     */
    public function Request_CheckAccessOnRequest()
    {
        $this->useCaseProxy->setUseCase(new RequestRoleSecurityUseCaseStub());
        $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertEquals(new UseCaseRequestStub(), $this->security->object);
    }

    /**
     * @test
     */
    public function Field_CheckAccessOnField()
    {
        $this->useCaseProxy->setUseCase(new FieldRoleSecurityUseCaseStub());
        $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertEquals(UseCaseRequestStub::FIELD_VALUE, $this->security->object);
    }
}
