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
        if ($this->options->get('ignore')) {
            return;
        }

        $save_dir = realpath($asset->getSourceRoot().'/../').'/images/gradients';
        if (!is_dir($save_dir)) {
            mkdir($save_dir, 0755, true);
        }

        $lines = explode("\n", $asset->getContent());
        foreach ($lines as &$l) {
            $m = null;
            if (!preg_match('#/\*gradient_(h|v):([0-9]+)(?:px)?:(.*?):(.*?)\*/#', $l, $m)) {
                continue;
            }

            $direction       = $m[1] == 'v' ? 'vertical' : 'horizontal';
            $size            = $m[2];
            $start_color     = $m[3];
            $end_color       = $m[4];
            $start_color_rgb = self::normalizeColorToRgbString($start_color);
            $end_color_rgb   = self::normalizeColorToRgbString($end_color);

            $desc = implode('-', $start_color_rgb).'_'.implode('-', $end_color_rgb).'_'.$direction.'_'.$size.'.png';
            $path = $save_dir.'/'.$desc;

            if (!is_file($path)) {
                $im = ImageUtil::getGradientImage($size, $start_color_rgb, $end_color_rgb, $direction);
                imagepng($im, $path);
            }

            $l = preg_replace(
                '#url\("?(.*?)"?\)#',
                'url(../images/gradients/'.$desc.')',
                $l
            );
        }

        $lines = implode("\n", $lines);
        $asset->setContent($lines);
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
