<?php

declare(strict_types=1);

/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace ZfcDatagridTest\DataSource\Doctrine2\Mocks;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\UnitOfWork;

use function is_null;

/**
 * Special EntityManager mock used for testing purposes.
 */
class EntityManagerMock extends EntityManager
{
    /** @var UnitOfWork|null */
    private $uowMock;

    /** @var ProxyFactory|null */
    private $proxyFactoryMock;

    /**
     * {@inheritdoc}
     */
    public function getUnitOfWork()
    {
        return $this->uowMock ?? parent::getUnitOfWork();
    }

    /* Mock API */

    /**
     * Sets a (mock) UnitOfWork that will be returned when getUnitOfWork() is called.
     *
     * @param UnitOfWork $uow
     * @return void
     */
    public function setUnitOfWork($uow)
    {
        $this->uowMock = $uow;
    }

    /**
     * @param ProxyFactory $proxyFactory
     * @return void
     */
    public function setProxyFactory($proxyFactory)
    {
        $this->proxyFactoryMock = $proxyFactory;
    }

    /**
     * @return ProxyFactory
     */
    public function getProxyFactory()
    {
        return $this->proxyFactoryMock ?? parent::getProxyFactory();
    }

    /**
     * Mock factory method to create an EntityManager.
     *
     * {@inheritdoc}
     */
    public static function create(
        $conn,
        ?Configuration $config = null,
        ?EventManager $eventManager = null
    ) {
        if (is_null($config)) {
            $config = new Configuration();
            $config->setProxyDir(__DIR__ . '/../Proxies');
            $config->setProxyNamespace('Doctrine\Tests\Proxies');
            $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver([], true));
        }
        if (is_null($eventManager)) {
            $eventManager = new EventManager();
        }

        return new EntityManagerMock($conn, $config, $eventManager);
    }
}
