<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZfcDatagrid\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Psr7Bridge\Psr7ServerRequest;
use Zend\Stdlib\RequestInterface;

class RequestHelper
{
    /** @var null|RequestInterface */
    protected $request;

    /**
     * RequestHelper constructor.
     * @param null|RequestInterface $request
     */
    public function __construct(?RequestInterface $request = null)
    {
        $this->request = $request;
    }

    /**
     * @param RequestInterface|null $request
     */
    public function setRequest(?RequestInterface $request) : void
    {
        if ($request instanceof ServerRequestInterface) {
            $request = Psr7ServerRequest::toZend($request);
        }

        $this->request = $request;
    }

    /**
     * @return RequestInterface|null
     */
    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }
}
