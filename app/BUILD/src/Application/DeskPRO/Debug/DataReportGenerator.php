<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Debug;

use Application\DeskPRO\Debug\Data\DataInterface;

class DataReportGenerator
{
    /**
     * @var \Application\DeskPRO\Debug\Data\DataInterface[]
     */
    public $datas = [];

    /**
     * @var bool
     */
    public $enable_gzip = true;

    public function addData(DataInterface $data)
    {
        $this->datas[] = $data;
    }

    /**
     * @return string
     */
    public function generateReport()
    {
        $data = [];

        foreach ($this->datas as $d) {
            $name        = get_class($d);
            $data[$name] = $d->getData();
        }

        $type = 'json';
        if (defined('JSON_PRETTY_PRINT')) {
            $data = json_encode($data, constant('JSON_PRETTY_PRINT'));
        } else {
            $data = json_encode($data);
        }

        $encode = 'plain';
        if ($this->enable_gzip && function_exists('gzencode')) {
            $encode = 'gzip';
            $data   = gzencode($data);
        }

        return [
            'data'        => $data,
            'data_encode' => $type,
            'file_encode' => $encode,
        ];
    }
}
