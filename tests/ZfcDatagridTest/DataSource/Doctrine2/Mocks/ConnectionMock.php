<?php

declare(strict_types=1);

namespace ZfcDatagridTest\DataSource\Doctrine2\Mocks;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\AbstractPlatform;

use function is_string;

/**
 * Mock class for Connection.
 */
class ConnectionMock extends Connection
{
    /** @var mixed */
    private $fetchOneResult;

    /** @var DatabasePlatformMock */
    private $platformMock;

    /** @var int */
    private $lastInsertId = 0;

    /** @var array */
    private $inserts = [];

    /** @var array */
    private $executeUpdates = [];

    /**
     * @param array                              $params
     * @param Driver $driver
     * @param Configuration|null $config
     * @param EventManager|null $eventManager
     */
    public function __construct(array $params, $driver, $config = null, $eventManager = null)
    {
        $this->platformMock = new DatabasePlatformMock();

        parent::__construct($params, $driver, $config, $eventManager);

        // Override possible assignment of platform to database platform mock
        $this->_platform = $this->platformMock;
    }

    /**
     * @ERROR!!!
     */
    public function getDatabasePlatform()
    {
        return $this->platformMock;
    }

    /**
     * @ERROR!!!
     */
    public function insert($tableName, array $data, array $types = [])
    {
        $this->inserts[$tableName][] = $data;
    }

    /**
     * @ERROR!!!
     */
    public function executeUpdate(string $query, array $params = [], array $types = []): int
    {
        $this->executeUpdates[] = [
            'query'  => $query,
            'params' => $params,
            'types'  => $types,
        ];

        return 1;
    }

    /**
     * @ERROR!!!
     */
    public function lastInsertId($seqName = null)
    {
        return $this->lastInsertId;
    }

    /**
     * @ERROR!!!
     */
    public function fetchColumn($statement, array $params = [], $colnum = 0, array $types = [])
    {
        return $this->fetchOneResult;
    }

    /**
     * @ERROR!!!
     */
    public function quote($input, $type = null)
    {
        if (is_string($input)) {
            return "'" . $input . "'";
        }

        return $input;
    }

    /* Mock API */

    /**
     * @param mixed $fetchOneResult
     * @return void
     */
    public function setFetchOneResult($fetchOneResult)
    {
        $this->fetchOneResult = $fetchOneResult;
    }

    /**
     * @param AbstractPlatform $platform
     * @return void
     */
    public function setDatabasePlatform($platform)
    {
        $this->platformMock = $platform;
    }

    /**
     * @param int $id
     * @return void
     */
    public function setLastInsertId($id)
    {
        $this->lastInsertId = $id;
    }

    /**
     * @return array
     */
    public function getInserts()
    {
        return $this->inserts;
    }

    /**
     * @return array
     */
    public function getExecuteUpdates()
    {
        return $this->executeUpdates;
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->inserts      = [];
        $this->lastInsertId = 0;
    }
}
