<?php
namespace ZfcDatagrid;

use Zend\I18n\Translator\TranslatorInterface;
use Zend\Router\RouteStackInterface;
use function is_array;
use function array_walk_recursive;
use function is_object;
use function trim;
use function implode;

class PrepareData
{
    /** @var array */
    private $columns = [];

    /** @var array */
    private $data = [];

    /** @var array|null */
    private $dataPrepared;

    /** @var null|string */
    private $rendererName;

    /** @var TranslatorInterface|null */
    private $translator;

    /** @var \Zend\Router\RouteStackInterface */
    private $router;

    /**
     * @param array $data
     * @param array $columns
     */
    public function __construct(array $data, array $columns)
    {
        $this->setData($data);
        $this->setColumns($columns);
    }

    /**
     * @param array $columns
     *
     * @return $this
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param bool $raw
     *
     * @return array
     */
    public function getData(bool $raw = false): array
    {
        if (true === $raw) {
            return $this->data;
        }

        $this->prepare();

        return $this->dataPrepared;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setRendererName(?string $name = null): self
    {
        $this->rendererName = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getRendererName(): ?string
    {
        return $this->rendererName;
    }

    /**
     * @param TranslatorInterface $translator
     *
     * @return $this
     */
    public function setTranslator(TranslatorInterface $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @param RouteStackInterface $router
     *
     * @return $this
     */
    public function setRouter(RouteStackInterface $router): self
    {
        $this->router = $router;

        return $this;
    }

    /**
     * @return RouteStackInterface
     */
    public function getRouter(): ?RouteStackInterface
    {
        return $this->router;
    }

    /**
     * Return true if preparing executed, false if already done!
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function prepare(): bool
    {
        if (null !== $this->dataPrepared) {
            return false;
        }

        $data = $this->data;

        foreach ($data as $key => &$row) {
            $row = (array) $row;

            $ids = [];

            foreach ($this->getColumns() as $col) {
                /* @var $col \ZfcDatagrid\Column\AbstractColumn */

                if (isset($row[$col->getUniqueId()]) && $col->isIdentity() === true) {
                    $ids[] = $row[$col->getUniqueId()];
                }

                /*
                 * Maybe the data come not from another DataSource?
                 */
                if ($col instanceof Column\ExternalData) {
                    /* @var $col \ZfcDatagrid\Column\ExternalData */
                    // @todo improve the interface...
                    $dataPopulation = $col->getDataPopulation();

                    foreach ($dataPopulation->getObjectParametersColumn() as $parameter) {
                        $dataPopulation->setObjectParameter(
                            $parameter['objectParameterName'],
                            $row[$parameter['column']->getUniqueId()]
                        );
                    }
                    $row[$col->getUniqueId()] = $dataPopulation->toString();
                }

                if (! isset($row[$col->getUniqueId()])) {
                    $row[$col->getUniqueId()] = '';
                }

                /*
                 * Replace
                 */
                if ($col->hasReplaceValues() === true) {
                    $replaceValues = $col->getReplaceValues();

                    if (is_array($row[$col->getUniqueId()])) {
                        foreach ($row[$col->getUniqueId()] as &$value) {
                            if (isset($replaceValues[$value])) {
                                $value = $replaceValues[$value];
                            } elseif ($col->notReplacedGetEmpty() === true) {
                                $value = '';
                            }
                        }
                    } else {
                        if (isset($replaceValues[$row[$col->getUniqueId()]])) {
                            $row[$col->getUniqueId()] = $replaceValues[$row[$col->getUniqueId()]];
                        } elseif ($col->notReplacedGetEmpty() === true) {
                            $row[$col->getUniqueId()] = '';
                        }
                    }
                }

                /*
                 * Type converting
                 */
                if ($this->getRendererName() != 'PHPExcel') {
                    $row[$col->getUniqueId()] = $col->getType()->getUserValue($row[$col->getUniqueId()]);
                }

                /*
                 * Translate (nach typ convertierung -> PhpArray...)
                 */
                if ($col->isTranslationEnabled() === true) {
                    if (is_array($row[$col->getUniqueId()])) {
                        foreach ($row[$col->getUniqueId()] as &$value) {
                            if (is_array($value)) {
                                continue;
                            }
                            $value = $this->getTranslator()->translate($value);
                        }
                    } else {
                        $row[$col->getUniqueId()] = $this->getTranslator()->translate($row[$col->getUniqueId()]);
                    }
                }

                /*
                 * Trim the values
                 */
                if (is_array($row[$col->getUniqueId()])) {
                    array_walk_recursive($row[$col->getUniqueId()], function (&$value) {
                        if (! is_object($value)) {
                            $value = trim($value);
                        }
                    });
                } elseif (! is_object($row[$col->getUniqueId()])) {
                    $row[$col->getUniqueId()] = trim($row[$col->getUniqueId()]);
                }

                /*
                 * Custom formatter
                 */
                if ($col->hasFormatters() === true) {
                    foreach ($col->getFormatters() as $formatter) {
                        if ($formatter instanceof Column\Formatter\RouterInterface
                            && $this->getRouter() instanceof RouteStackInterface
                        ) {
                            /** @var Column\Formatter\RouterInterface */
                            $formatter->setRouter($this->getRouter());
                        }
                        $formatter->setRowData($row);
                        $formatter->setRendererName($this->getRendererName());

                        $row[$col->getUniqueId()] = $formatter->format($col);
                    }
                }
            }

            // Concat all identity columns
            if (!empty($ids)) {
                $data[$key]['idConcated'] = implode('~', $ids);
            }
        }

        $this->dataPrepared = $data;

        return true;
    }
}
