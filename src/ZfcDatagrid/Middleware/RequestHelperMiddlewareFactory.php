<?php

namespace ZfcDatagrid\Middleware;

use Psr\Container\ContainerInterface;

class RequestHelperMiddlewareFactory
{

    public function __invoke(ContainerInterface $container) : RequestHelperMiddleware
    {
        return new RequestHelperMiddleware($container->get(RequestHelper::class));
    }
}