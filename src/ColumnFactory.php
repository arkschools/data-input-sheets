<?php

namespace Arkschools\DataInputSheets;

use Arkschools\DataInputSheets\ColumnType\AbstractColumn;
use Arkschools\DataInputSheets\ColumnType\DateColumn;
use Arkschools\DataInputSheets\ColumnType\FloatColumn;
use Arkschools\DataInputSheets\ColumnType\GenderColumn;
use Arkschools\DataInputSheets\ColumnType\IntegerColumn;
use Arkschools\DataInputSheets\ColumnType\ObjectValueColumn;
use Arkschools\DataInputSheets\ColumnType\ServiceListColumn;
use Arkschools\DataInputSheets\ColumnType\StringColumn;
use Arkschools\DataInputSheets\ColumnType\TextColumn;
use Arkschools\DataInputSheets\ColumnType\YesNoColumn;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ColumnFactory
{
    /**
     * @var AbstractColumn[]
     */
    private $types;

    public function __construct(array $extraTypes = [], ContainerInterface $container)
    {
        $this->types = [
            'integer'     => new IntegerColumn(),
            'float'       => new FloatColumn(),
            'string'      => new StringColumn(),
            'text'        => new TextColumn(),
            'date'        => new DateColumn(),
            'yes/no'      => new YesNoColumn(),
            'serviceList' => new ServiceListColumn($container),
        ];

        foreach ($extraTypes as $type => $class) {
            if (class_exists($class)) {
                $this->types[$type] = new $class;
            }
        }
    }

    public function addColumnType(AbstractColumn $columnType, string $type)
    {
        $this->types[$type] = $columnType;
    }

    public function create(array $config, string $title)
    {
        $field    = $config['field'] ?? null;
        $option   = $config['option'] ?? null;
        $readOnly = $config['read_only'] ?? false;

        if (isset($this->types[$config['type']])) {
            return new Column($this->types[$config['type']], $title, $field, $option, $readOnly);
        }

        if ('->' === substr($config['type'], 0, 2)) {
            return new Column(new ObjectValueColumn(substr($config['type'], 2), $option), $title, $field, null, $readOnly);
        }

        return new Column($this->types['string'], $title, $field, $option, $readOnly);
    }
}
