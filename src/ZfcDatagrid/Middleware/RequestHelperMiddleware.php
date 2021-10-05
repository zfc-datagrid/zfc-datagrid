<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZfcDatagrid\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouteResult;

/**
 * Pipeline middleware for injecting a RequestHelper with a RouteResult.
 */
class RequestHelperMiddleware implements MiddlewareInterface
{
    /**
     * @var RequestHelper
     */
    private $helper;

    public function __construct(RequestHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Inject the RequestHelper instance with a Request, if present as a request attribute.
     * Injects the helper, and then dispatches the next middleware.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $this->helper->setRequest($request);

        return $handler->handle($request);
    }
}
