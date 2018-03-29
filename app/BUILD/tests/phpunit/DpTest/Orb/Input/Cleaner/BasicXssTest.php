<?php

/**
 * DeskPRO.
 */

namespace DpTest\Orb\Input\Cleaner;

use DpTest\DeskProTestCase;

class BasicXssTest extends DeskProTestCase
{
    /**
     * @var \Orb\Input\Cleaner\Cleaner
     */
    private $cleaner;

    /**
     * {@inheritdoc}
     */
    public function setup()
    {
        $this->cleaner = new \Orb\Input\Cleaner\Cleaner();
        $this->cleaner->addCleaner(new \Orb\Input\Cleaner\CleanerPlugin\BasicXss());
    }

    /**
     * @return array
     */
    public function fileProvider()
    {
        return array_map(
            function ($f) {
                return [$f];
            },
            $this->_loadFileList()
        );
    }

    /**
     * @dataProvider fileProvider
     *
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

    //###################################################################################################################

    private function _normalizeResult($r)
    {
        return trim($r);
    }

    /**
     * @return array
     */
    private function _loadFileList()
    {
        $dir   = dir(__DIR__.'/xss_data');
        $files = [];

        while (false !== ($f = $dir->read())) {
            if (preg_match('#\.txt$#', $f)) {
                $files[] = $dir->path.'/'.$f;
            }
        }

        return $files;
    }

    /**
     * @param string $f
     *
     * @throws \Exception
     *
     * @return array
     */
    private function _readFile($f)
    {
        $f = file_get_contents($f);

        $parts = preg_split("#\n\\-{20,180}\n#", $f, 2);
        if (!$parts || count($parts) != 2) {
            throw new \Exception("$f is not properly formatted");
        }

        return [
            'source' => trim($parts[0]),
            'result' => trim($parts[1]),
        ];
    }
}
