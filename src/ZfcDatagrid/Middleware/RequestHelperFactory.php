<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZfcDatagrid\Middleware;

use Psr\Container\ContainerInterface;

class RequestHelperFactory
{
    /**
     * Create a RequestHelper instance.
     */
    public function __invoke(ContainerInterface $container) : RequestHelper
    {
        $request = $container->has('application')
            ? $container->get('application')->getMvcEvent()->getRequest()
            : null;

        return new RequestHelper($request);
    }
}
