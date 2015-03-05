<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace DpUnitTests\Orb\Input\Cleaner;

use Orb\Serializer\Serializer\ArraySerializer;
use Orb\Serializer\SerializerRegistry;

class BasicXssTest extends \DpUnitTestCase
{
    /**
     * @var \Orb\Input\Cleaner\Cleaner
     */
    private $cleaner;

    /**
     * {@inheritDoc}
     */
    public function runBefore()
    {
        $this->cleaner = new \Orb\Input\Cleaner\Cleaner();
        $this->cleaner->addCleaner(new \Orb\Input\Cleaner\CleanerPlugin\BasicXss());
    }

    /**
     * @return array
     */
    public function fileProvider()
    {
        return array_map(function($f) { return array($f); }, $this->_loadFileList());
    }

    /**
     * @dataProvider fileProvider
     * @param string $f
     */
    public function testXss($f)
    {
        $parts = $this->_readFile($f);

        $result = $this->cleaner->clean($parts['source'], 'string');
        $this->assertEquals(
            $this->_normalizeResult($parts['result']),
            $this->_normalizeResult($result),
            basename($f)
        );
    }

    ####################################################################################################################

    private function _normalizeResult($r)
    {
        return trim($r);
    }

    /**
     * @return array
     */
    private function _loadFileList()
    {
        $dir = dir(__DIR__.'/xss_data');
        $files = array();

        while (false !== ($f = $dir->read())) {
            if (preg_match('#\.txt$#', $f)) {
                $files[] = $dir->path . '/' .$f;
            }
        }

        return $files;
    }

    /**
     * @param string $f
     * @return array
     * @throws \Exception
     */
    private function _readFile($f)
    {
        $f = file_get_contents($f);

        $parts = preg_split("#\n\\-{20,180}\n#", $f, 2);
        if (!$parts || count($parts) != 2) {
            throw new \Exception("$f is not properly formatted");
        }

        return array(
            'source' => trim($parts[0]),
            'result' => trim($parts[1])
        );
    }
}
