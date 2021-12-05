<?php

declare(strict_types=1);

namespace ZfcDatagridTest\DataSource;

use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column;
use ZfcDatagrid\Column\AbstractColumn;

class DataSourceTestCase extends TestCase
{
    /** @var array */
    protected $data;

    /** @var AbstractColumn */
    protected $colVolumne;

    /** @var AbstractColumn */
    protected $colEdition;

    /** @var AbstractColumn */
    protected $colUserDisplayName;

    public function setUp(): void
    {
        $data   = [];
        $data[] = [
            'volume'      => 67,
            'edition'     => 2,
            'unneededCol' => 'something',
        ];
        $data[] = [
            'volume'  => 86,
            'edition' => 1,
            'unneded' => 'blubb',
        ];
        $data[] = [
            'volume'  => 85,
            'edition' => 6,
        ];
        $data[] = [
            'volume'  => 98,
            'edition' => 2,
        ];
        $data[] = [
            'volume'  => 86,
            'edition' => 6,
        ];
        $data[] = [
            'volume'  => 67,
            'edition' => 7,
            'user'    => [
                'displayName' => 'Martin',
            ],
        ];

        $this->data = $data;

        $col1             = new Column\Select('volume');
        $this->colVolumne = $col1;

        $col1             = new Column\Select('edition');
        $this->colEdition = $col1;

        $col3                     = new Column\Select('displayName', 'user');
        $this->colUserDisplayName = $col3;
    }
}
