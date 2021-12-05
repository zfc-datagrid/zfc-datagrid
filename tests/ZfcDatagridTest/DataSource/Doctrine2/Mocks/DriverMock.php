<?php

declare(strict_types=1);

namespace ZfcDatagridTest\DataSource\Doctrine2\Mocks;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\MySQLSchemaManager;

/**
 * Mock class for Driver.
 */
class DriverMock implements Driver
{
    /** @var AbstractPlatform null */
    private $platformMock;

    /** @var AbstractSchemaManager null */
    private $schemaManagerMock;

    /**
     * @ERROR!!!
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        return new DriverConnectionMock();
    }

    /**
     * @ERROR!!!
     */
    public function getDatabasePlatform()
    {
        if (! $this->platformMock) {
            $this->platformMock = new DatabasePlatformMock();
        }

        return $this->platformMock;
    }

    public function getSchemaManager(Connection $conn, AbstractPlatform $platform)
    {
        if (null == $this->schemaManagerMock) {
            return new MySQLSchemaManager($conn, $platform);
        } else {
            return $this->schemaManagerMock;
        }
    }

    public function getExceptionConverter(): ExceptionConverter
    {
        return new \Doctrine\DBAL\Driver\API\MySQL\ExceptionConverter();
    }

    /* MOCK API */

    /**
     * @return void
     */
    public function setDatabasePlatform(AbstractPlatform $platform)
    {
        $this->platformMock = $platform;
    }

    /**
     * @return void
     */
    public function setSchemaManager(AbstractSchemaManager $sm)
    {
        $this->schemaManagerMock = $sm;
    }

    /**
     * @ERROR!!!
     */
    public function getName()
    {
        return 'mock';
    }

    /**
     * @ERROR!!!
     */
    public function getDatabase(Connection $conn)
    {
        return;
    }
}
