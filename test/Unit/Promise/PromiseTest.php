<?php

namespace Kraken\Test\Unit\Promise;

use Kraken\Promise\DeferredInterface;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Test\Unit\Promise\_Bridge\DeferredBridge;
use Kraken\Test\Unit\Promise\_Partial\FullTestPartial;
use Kraken\Test\Unit\Promise\_Partial\FunctionTestPartial;
use Kraken\Test\Unit\TestCase;
use Exception;

class PromiseTest extends TestCase
{
    use FullTestPartial;
    use FunctionTestPartial;

    /**
     * @return DeferredInterface
     */
    public function createDeferred()
    {
        $resolveCallback = $rejectCallback = $cancelCallback = null;

        $promise = new Promise(function($resolve, $reject, $cancel) use(&$resolveCallback, &$rejectCallback, &$cancelCallback) {
            $resolveCallback = $resolve;
            $rejectCallback  = $reject;
            $cancelCallback  = $cancel;
        });

        return new DeferredBridge([
            'getPromise' => function() use($promise) {
                return $promise;
            },
            'resolve'    => $resolveCallback,
            'reject'     => $rejectCallback,
            'cancel'     => $cancelCallback
        ]);
    }

    /**
     * @param string[] $methods
     * @return PromiseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createPromiseMock($methods = [])
    {
        return $this->getMock(Promise::class, $methods);
    }

    /**
     *
     */
    public function testCasePromise_RejectsPromise_IfResolverThrowsException()
    {
        $exception = new Exception('foo');
        $promise = new Promise(function() use($exception) {
            throw $exception;
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $promise
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }
}
