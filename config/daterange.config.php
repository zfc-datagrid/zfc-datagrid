<?php

declare(strict_types=1);

use Laminas\Json\Expr;

return [
    'ZfcDatagrid' => [
        'renderer' => [
            'bootstrapTable' => [
                // Daterange bootstrapTable filter configuration example
                'daterange' => [
                    'enabled' => false,
                    'options' => [
                        'ranges' => [
                            'Today'        => new Expr(
                                "[moment().startOf('day'), moment().endOf('day')]"
                            ),
                            'Yesterday'    => new Expr(
                                "[moment().subtract('days', 1), moment().subtract('days', 1)]"
                            ),
                            'Last 7 Days'  => new Expr(
                                "[moment().subtract('days', 6), moment()]"
                            ),
                            'Last 30 Days' => new Expr(
                                "[moment().subtract('days', 29), moment()]"
                            ),
                            'This Month'   => new Expr(
                                "[moment().startOf('month'), moment().endOf('month')]"
                            ),
                            // @codingStandardsIgnoreStart
                            'Last Month'   => new \Laminas\Json\Expr(
                                "[moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]"
                            ),
                            // @codingStandardsIgnoreEnd
                        ],
                        'locale' => Locale::getDefault(),
                        'format' => 'DD/MM/YY HH:mm:ss',
                    ],
                ],
            ],
        ],
    ],
];
