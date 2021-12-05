<?php

declare(strict_types=1);

namespace ZfcDatagridTest\DataSource\Doctrine2;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\IndexedReader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\EventManager;
use Doctrine\Common\Version;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use ZfcDatagridTest\DataSource\DataSourceTestCase;
use ZfcDatagridTest\DataSource\Doctrine2\Assets\Entity\Category;
use ZfcDatagridTest\DataSource\Doctrine2\Mocks\ConnectionMock;
use ZfcDatagridTest\DataSource\Doctrine2\Mocks\DriverMock;
use ZfcDatagridTest\DataSource\Doctrine2\Mocks\EntityManagerMock;

use function is_array;
use function version_compare;

/**
 *  @group DataSource
 *  @covers \ZfcDatagrid\DataSource\Doctrine2
 */
abstract class AbstractDoctrine2Test extends DataSourceTestCase
{
    /**
     * The metadata cache that is shared between all ORM tests (except functional tests).
     *
     * @var Cache null
     */
    private static $metadataCacheImpl;

    /**
     * The query cache that is shared between all ORM tests (except functional tests).
     *
     * @var Cache null
     */
    private static $queryCacheImpl;

    /** @var EntityManager */
    protected $em;

    /**
     * @param array $paths
     * @param mixed $alias
     * @return AnnotationDriver
     */
    protected function createAnnotationDriver($paths = [], $alias = null)
    {
        if (version_compare(Version::VERSION, '3.0.0', '>=')) {
            $reader = new CachedReader(
                new AnnotationReader(),
                new ArrayCache()
            );
        } elseif (version_compare(Version::VERSION, '2.2.0-DEV', '>=')) {
            // Register the ORM Annotations in the AnnotationRegistry
                $reader = new SimpleAnnotationReader();
            $reader->addNamespace('Doctrine\ORM\Mapping');
            $reader = new CachedReader($reader, new ArrayCache());
        } elseif (version_compare(Version::VERSION, '2.1.0-BETA3-DEV', '>=')) {
            $reader = new AnnotationReader();
            $reader->setIgnoreNotImportedAnnotations(true);
            $reader->setEnableParsePhpImports(false);
            if ($alias) {
                $reader->setAnnotationNamespaceAlias('Doctrine\ORM\Mapping\\', $alias);
            } else {
                $reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
            }
            $reader = new CachedReader(
                new IndexedReader($reader),
                new ArrayCache()
            );
        } else {
            $reader = new AnnotationReader();
            if ($alias) {
                $reader->setAnnotationNamespaceAlias('Doctrine\ORM\Mapping\\', $alias);
            } else {
                $reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
            }
        }
        AnnotationRegistry::registerFile(
            __DIR__ . "/../../../lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php"
        );

        return new AnnotationDriver($reader, (array) $paths);
    }

    /**
     * Creates an EntityManager for testing purposes.
     *
     * NOTE: The created EntityManager will have its dependant DBAL parts completely
     * mocked out using a DriverMock, ConnectionMock, etc. These mocks can then
     * be configured in the tests to simulate the DBAL behavior that is desired
     * for a particular test,
     *
     * @param Connection|array $conn
     * @param mixed                              $conf
     * @param EventManager|null $eventManager
     * @param bool                               $withSharedMetadata
     * @return EntityManager
     */
    protected function getTestEntityManager(
        $conn = null,
        $conf = null,
        $eventManager = null,
        $withSharedMetadata = true
    ) {
        $metadataCache = $withSharedMetadata ?
            self::getSharedMetadataCacheImpl() : new \Doctrine\Common\Cache\ArrayCache();

        $config = new Configuration();

        $config->setMetadataCacheImpl($metadataCache);
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver([], true));
        $config->setQueryCacheImpl(self::getSharedQueryCacheImpl());
        $config->setProxyDir(__DIR__ . '/Proxies');
        $config->setProxyNamespace('Doctrine\Tests\Proxies');

        $config->setEntityNamespaces([
            'ZfcDatagridTest\DataSource\Doctrine2\Assets\Entity',
            Category::class,
        ]);

        if (null === $conn) {
            $conn = [
                'driverClass'  => DriverMock::class,
                'wrapperClass' => ConnectionMock::class,
                'user'         => 'john',
                'password'     => 'wayne',
            ];
        }

        if (is_array($conn)) {
            $conn = DriverManager::getConnection($conn, $config, $eventManager);
        }

        return EntityManagerMock::create($conn, $config, $eventManager);
    }

    /**
     * @return Cache
     */
    private static function getSharedMetadataCacheImpl()
    {
        if (null === self::$metadataCacheImpl) {
            self::$metadataCacheImpl = new \Doctrine\Common\Cache\ArrayCache();
        }

        return self::$metadataCacheImpl;
    }

    /**
     * @return Cache
     */
    private static function getSharedQueryCacheImpl()
    {
        if (null === self::$queryCacheImpl) {
            self::$queryCacheImpl = new \Doctrine\Common\Cache\ArrayCache();
        }

        return self::$queryCacheImpl;
    }

    public function setUp(): void
    {
        $this->em = $this->getTestEntityManager();

        parent::setUp();
    }
}
