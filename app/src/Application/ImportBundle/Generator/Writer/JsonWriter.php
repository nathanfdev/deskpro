<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Writer;

/**
 * Class JsonWriter
 * @package Application\ImportBundle\Generator\Writer
 */
class JsonWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function writeData($destination, array $data)
    {
        $this->createOutputDirsIfNotExist();
        if ($this->config->isLive()) {
            file_put_contents($destination, json_encode($data));
        }

        return true;
    }

    /**
     * Make output directories if not exist
     *
     * @throws \Exception
     */
    private function createOutputDirsIfNotExist()
    {
        if (!$this->config) {
            throw new \Exception('Generator configuration is not set up');
        }

        if (!is_dir($this->config->getOutputPath())) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid configuration: data_path is invalid (got %s)',
                $this->config->getOutputPath())
            );
        }
        if ($this->config->isLive()) {
            foreach ($this->config->getRecordTypes() as $n) {
                if (!is_dir($this->config->getOutputPath() . $n)) {
                    mkdir($this->config->getOutputPath() . $n, 0777, true);
                }
            }
        }
    }
}
