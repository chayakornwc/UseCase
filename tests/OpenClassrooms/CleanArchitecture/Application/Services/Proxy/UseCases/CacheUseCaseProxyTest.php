<?php

namespace OpenClassrooms\Tests\CleanArchitecture\Application\Services\Proxy\UseCases;

use OpenClassrooms\Tests\CleanArchitecture\BusinessRules\Exceptions\UseCaseException;
use OpenClassrooms\Tests\CleanArchitecture\BusinessRules\Requestors\UseCaseRequestStub;
use OpenClassrooms\Tests\CleanArchitecture\BusinessRules\Responders\UseCaseResponseStub;
use OpenClassrooms\Tests\CleanArchitecture\BusinessRules\UseCases\Cache\ExceptionCacheUseCaseStub;
use OpenClassrooms\Tests\CleanArchitecture\BusinessRules\UseCases\Cache\LifeTimeCacheUseCaseStub;
use OpenClassrooms\Tests\CleanArchitecture\BusinessRules\UseCases\Cache\NamespaceCacheUseCaseStub;
use OpenClassrooms\Tests\CleanArchitecture\BusinessRules\UseCases\Cache\OnlyCacheUseCaseStub;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class CacheUseCaseProxyTest extends UseCaseProxyTest
{
    /**
     * @test
     */
    public function OnlyCache_ReturnResponse()
    {
        $this->useCaseProxy->setUseCase(new OnlyCacheUseCaseStub());
        $response = $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertEquals(new UseCaseResponseStub(), $response);
        $this->assertTrue($this->cache->saved);
    }

    /**
     * @test
     */
    public function Cached_OnlyCache_ReturnResponse()
    {
        $this->useCaseProxy->setUseCase(new OnlyCacheUseCaseStub());
        $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertTrue($this->cache->saved);
        $this->cache->saved = false;
        $response = $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertEquals(new UseCaseResponseStub(), $response);
        $this->assertTrue($this->cache->fetched);
        $this->assertFalse($this->cache->saved);
    }

    /**
     * @test
     */
    public function WithNamespace_Cache_ReturnResponse()
    {
        $this->useCaseProxy->setUseCase(new NamespaceCacheUseCaseStub());
        $response = $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertEquals(new UseCaseResponseStub(), $response);
        $this->assertTrue($this->cache->savedWithNamespace);
        $this->assertEquals(
            NamespaceCacheUseCaseStub::NAMESPACE_PREFIX . UseCaseRequestStub::FIELD_VALUE,
            $this->cache->namespaceId
        );
    }

    /**
     * @test
     */
    public function CachedWithNamespace_Cache_ReturnResponse()
    {
        $this->useCaseProxy->setUseCase(new NamespaceCacheUseCaseStub());
        $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertTrue($this->cache->savedWithNamespace);
        $this->cache->savedWithNamespace = false;
        $response = $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertEquals(new UseCaseResponseStub(), $response);
        $this->assertTrue($this->cache->fetched);
        $this->assertFalse($this->cache->savedWithNamespace);
    }

    /**
     * @test
     */
    public function WithLifeTime_Cache_ReturnResponse()
    {
        $this->useCaseProxy->setUseCase(new LifeTimeCacheUseCaseStub());
        $response = $this->useCaseProxy->execute(new UseCaseRequestStub());
        $this->assertEquals(new UseCaseResponseStub(), $response);
        $this->assertTrue($this->cache->saved);
        $this->assertEquals(LifeTimeCacheUseCaseStub::LIFETIME, $this->cache->lifeTime);
    }

    /**
     * @test
     */
    public function CacheOnException_DonTSave()
    {
        try {
            $this->useCaseProxy->setUseCase(new ExceptionCacheUseCaseStub());
            $this->useCaseProxy->execute(new UseCaseRequestStub());
            $this->fail('Exception should be thrown');
        } catch (UseCaseException $e) {
            $this->assertFalse($this->cache->saved);
        }
    }
}
