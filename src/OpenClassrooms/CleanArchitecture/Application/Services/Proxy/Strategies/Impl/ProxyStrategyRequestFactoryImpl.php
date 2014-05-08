<?php

namespace OpenClassrooms\CleanArchitecture\Application\Services\Proxy\Strategies\Impl;

use OpenClassrooms\CleanArchitecture\Application\Annotations\Cache;
use OpenClassrooms\CleanArchitecture\Application\Annotations\Event;
use OpenClassrooms\CleanArchitecture\Application\Annotations\Security;
use OpenClassrooms\CleanArchitecture\Application\Annotations\Transaction;
use
    OpenClassrooms\CleanArchitecture\Application\Services\Proxy\Strategies\Exceptions\UnSupportedAnnotationException;
use
    OpenClassrooms\CleanArchitecture\Application\Services\Proxy\Strategies\Impl\DTO\ProxyStrategyRequestDTO;
use
    OpenClassrooms\CleanArchitecture\Application\Services\Proxy\Strategies\Requestors\Cache\CacheProxyStrategyRequestBuilder;
use
    OpenClassrooms\CleanArchitecture\Application\Services\Proxy\Strategies\Requestors\Event\EventProxyStrategyRequestBuilder;
use
    OpenClassrooms\CleanArchitecture\Application\Services\Proxy\Strategies\Requestors\ProxyStrategyRequest;
use
    OpenClassrooms\CleanArchitecture\Application\Services\Proxy\Strategies\Requestors\ProxyStrategyRequestFactory;
use
    OpenClassrooms\CleanArchitecture\Application\Services\Proxy\Strategies\Requestors\Security\SecurityProxyStrategyRequestBuilder;
use OpenClassrooms\CleanArchitecture\BusinessRules\Requestors\UseCaseRequest;
use OpenClassrooms\CleanArchitecture\BusinessRules\Responders\UseCaseResponse;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ProxyStrategyRequestFactoryImpl implements ProxyStrategyRequestFactory
{
    /**
     * @var CacheProxyStrategyRequestBuilder
     */
    private $cacheProxyStrategyRequestBuilder;

    /**
     * @var SecurityProxyStrategyRequestBuilder
     */
    private $securityProxyStrategyRequestBuilder;

    /**
     * @var EventProxyStrategyRequestBuilder
     */
    private $eventProxyStrategyRequestBuilder;

    /**
     * @return ProxyStrategyRequest
     */
    public function createPreExecuteRequest($annotation, UseCaseRequest $useCaseRequest)
    {
        switch ($annotation) {
            case $annotation instanceof Security:

                /** @var Security $annotation */
                if ($annotation->checkRequest()) {
                    $object = $useCaseRequest;
                } elseif (null !== $fieldName = $annotation->getCheckField()) {
                    $reflectionObject = new \ReflectionObject($useCaseRequest);
                    $property = $reflectionObject->getProperty($fieldName);
                    $property->setAccessible(true);
                    $object = $property->getValue($useCaseRequest);
                } else {
                    $object = null;
                }

                $request = $this->securityProxyStrategyRequestBuilder
                    ->create()
                    ->withAttributes($annotation->getRoles())
                    ->withObject($object)
                    ->build();
                break;
            case $annotation instanceof Cache:
                /** @var Cache $annotation */
                $request = $this->cacheProxyStrategyRequestBuilder
                    ->create()
                    ->withNamespaceId($this->getNamespaceId($annotation, $useCaseRequest))
                    ->withId(md5(serialize($useCaseRequest)))
                    ->withLifeTime($annotation->getLifetime())
                    ->build();
                break;
            case $annotation instanceof Transaction:
                $request = new ProxyStrategyRequestDTO();
                break;
            case $annotation instanceof Event:
                /** @var Event $annotation */
                $request = $this->eventProxyStrategyRequestBuilder
                    ->create()
                    ->withEventName($annotation->getName())
                    ->withUseCaseRequest($useCaseRequest)
                    ->build();
                break;
            default:
                throw new UnSupportedAnnotationException();
                break;
        }

        return $request;
    }

    /**
     * @return string|null
     */
    private function getNamespaceId(Cache $annotation, UseCaseRequest $useCaseRequest)
    {
        $namespaceId = null;
        if (null !== $annotation->getNamespacePrefix()) {
            $namespaceId = $annotation->getNamespacePrefix();
        }
        if (null !== $namespaceAttribute = $annotation->getNamespaceAttribute()) {
            $reflectionClass = new \ReflectionClass($useCaseRequest);
            $property = $reflectionClass->getProperty($namespaceAttribute);
            $property->setAccessible(true);
            null === $namespaceId ? $namespaceId = $property->getValue($useCaseRequest)
                : $namespaceId .= $property->getValue($useCaseRequest);
        }

        return $namespaceId;
    }

    /**
     * @return ProxyStrategyRequest
     */
    public function createPostExecuteRequest(
        $annotation,
        UseCaseRequest $useCaseRequest,
        UseCaseResponse $useCaseResponse
    )
    {
        $request = new ProxyStrategyRequestDTO();
        switch ($annotation) {
            case $annotation instanceof Cache:
                /** @var Cache $annotation */
                $request = $this->cacheProxyStrategyRequestBuilder
                    ->create()
                    ->withNamespaceId($this->getNamespaceId($annotation, $useCaseRequest))
                    ->withId(md5(serialize($useCaseRequest)))
                    ->withLifeTime($annotation->getLifetime())
                    ->withData($useCaseResponse)
                    ->build();
                break;
            case $annotation instanceof Transaction:
                break;
            case $annotation instanceof Event:
                /** @var Event $annotation */
                if (in_array('post', $annotation->getMethods())) {
                    $request = $this->eventProxyStrategyRequestBuilder
                        ->create()
                        ->withEventName($annotation->getName())
                        ->withUseCaseRequest($useCaseRequest)
                        ->withUseCaseResponse($useCaseResponse)
                        ->build();
                }
                break;
            default:
                throw new UnSupportedAnnotationException();
        }

        return $request;
    }

    /**
     * @return ProxyStrategyRequest
     */
    public function createOnExceptionRequest(
        $annotation,
        UseCaseRequest $useCaseRequest,
        \Exception $exception
    )
    {
        switch ($annotation) {
            case $annotation instanceof Transaction:
                $request = new ProxyStrategyRequestDTO();
                break;
            case $annotation instanceof Event:
                /** @var Event $annotation */
                $request = $this->eventProxyStrategyRequestBuilder
                    ->create()
                    ->withEventName($annotation->getName())
                    ->withUseCaseRequest($useCaseRequest)
                    ->withException($exception)
                    ->build();
                break;
            default:
                throw new UnSupportedAnnotationException();
        }

        return $request;
    }

    public function setCacheProxyStrategyRequestBuilder(
        CacheProxyStrategyRequestBuilder $cacheProxyStrategyRequestBuilder
    )
    {
        $this->cacheProxyStrategyRequestBuilder = $cacheProxyStrategyRequestBuilder;
    }

    public function setSecurityProxyStrategyRequestBuilder(
        SecurityProxyStrategyRequestBuilder $securityProxyStrategyRequestBuilder
    )
    {
        $this->securityProxyStrategyRequestBuilder = $securityProxyStrategyRequestBuilder;
    }

    public function setEventProxyStrategyRequestBuilder(
        EventProxyStrategyRequestBuilder $eventProxyStrategyRequestBuilder
    )
    {
        $this->eventProxyStrategyRequestBuilder = $eventProxyStrategyRequestBuilder;
    }

}
