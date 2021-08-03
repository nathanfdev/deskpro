<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Application\DeskPRO\Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;
use Orb\Images\Util as ImageUtil;

class CssGradientImage implements FilterInterface
{
    /**
     * @var \Orb\Util\OptionsArray
     */
    public $options;

    public function __construct(array $options = [])
    {
        $this->options = new \Orb\Util\OptionsArray($options);
    }

    public function filterDump(AssetInterface $asset)
    {
    }

    public function filterLoad(AssetInterface $asset)
    {

    }

    public static function normalizeColorToRgbString($color)
    {
        // Not rgb(
        if (!strpos($color, '(') || !strpos($color, ')')) {
            $color = preg_replace('#[^a-fA-F0-9]#', '', $color);
            if (strlen($color) == 6 || strlen($color) == 3) {
                $color = \Orb\Util\Numbers::hex2rgb($color);
                if ($color) {
                    $color = 'rgb('.implode(',', $color).')';
                } else {
                    $color = 'rgb(0,0,0)';
                }
            } else {
                $color = 'rgb(0,0,0)';
            }
        }

        if (preg_match('#rgb\((.*?),(.*?),(.*?)\)#i', $color, $m)) {
            $rgb = [
                'red'   => (int) trim($m[1]),
                'green' => (int) trim($m[2]),
                'blue'  => (int) trim($m[3]),
            ];

            return $rgb;
        } else {
            return ['red' => 0, 'green' => 0, 'blue' => 0];
        }
    }
}
