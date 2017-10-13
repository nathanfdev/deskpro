<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Component\Pdf;

/**
 * Class mPDF.
 */
class mPDF extends \Mpdf\Mpdf
{
    /**
     * {@inheritdoc}
     */
    public function ConvertSize($size = 5, $maxsize = 0, $fontsize = false, $usefontsize = true)
    {
        $size    = trim(strtolower($size));
        $numsize = floatval($size);

        if ($size == 'thin') {
            $numsize = 1 * (25.4 / $this->dpi);
        } //1 pixel width for table borders
        elseif (stristr($size, 'px')) {
            $numsize *= (25.4 / $this->dpi);
        } //pixels
        elseif (stristr($size, 'cm')) {
            $numsize *= 10;
        } //centimeters
        elseif (stristr($size, 'mm')) {
            $numsize += 0;
        } //millimeters
        elseif (stristr($size, 'pt')) {
            $numsize *= 25.4 / 72;
        } //72 pts/inch
        elseif (stristr($size, 'rem')) {
            $numsize += 0; //make "0.83rem" become simply "0.83"
            $numsize *= ($this->default_font_size / _MPDFK);
        } elseif (stristr($size, 'em')) {
            $numsize += 0; //make "0.83em" become simply "0.83"
            if ($fontsize) {
                $numsize *= $fontsize;
            } else {
                $numsize *= $maxsize;
            }
        } elseif (stristr($size, '%')) {
            $numsize += 0; //make "90%" become simply "90"
            if ($fontsize && $usefontsize) {
                $numsize *= $fontsize / 100;
            } else {
                $numsize *= $maxsize / 100;
            }
        } elseif (stristr($size, 'in')) {
            $numsize = (int) $size * 25.4;
        } //inches
        elseif (stristr($size, 'pc')) {
            $numsize *= 38.1 / 9;
        } //PostScript picas
        elseif (stristr($size, 'ex')) { // Approximates "ex" as half of font height
            $numsize += 0; //make "3.5ex" become simply "3.5"
            if ($fontsize) {
                $numsize *= $fontsize / 2;
            } else {
                $numsize *= $maxsize / 2;
            }
        } elseif ($size == 'medium') {
            $numsize = 3 * (25.4 / $this->dpi);
        } //3 pixel width for table borders
        elseif ($size == 'thick') {
            $numsize = 5 * (25.4 / $this->dpi);
        } //5 pixel width for table borders
        elseif ($size == 'xx-small') {
            if ($fontsize) {
                $numsize *= $fontsize * 0.7;
            } else {
                $numsize *= $maxsize * 0.7;
            }
        } elseif ($size == 'x-small') {
            if ($fontsize) {
                $numsize *= $fontsize * 0.77;
            } else {
                $numsize *= $maxsize * 0.77;
            }
        } elseif ($size == 'small') {
            if ($fontsize) {
                $numsize *= $fontsize * 0.86;
            } else {
                $numsize *= $maxsize * 0.86;
            }
        } elseif ($size == 'medium') {
            if ($fontsize) {
                $numsize *= $fontsize;
            } else {
                $numsize *= $maxsize;
            }
        } elseif ($size == 'large') {
            if ($fontsize) {
                $numsize *= $fontsize * 1.2;
            } else {
                $numsize *= $maxsize * 1.2;
            }
        } elseif ($size == 'x-large') {
            if ($fontsize) {
                $numsize *= $fontsize * 1.5;
            } else {
                $numsize *= $maxsize * 1.5;
            }
        } elseif ($size == 'xx-large') {
            if ($fontsize) {
                $numsize *= $fontsize * 2;
            } else {
                $numsize *= $maxsize * 2;
            }
        } else {
            $numsize *= (25.4 / $this->dpi);
        } //nothing == px

        return $numsize;
    }

    public function GetCharWidth($c, $addSubset = true)
    {
        $width = parent::GetCharWidth($c, $addSubset);
        if (!$width) {
            if (isset($this->CurrentFont['desc']['MissingWidth'])) {
                $width = $this->CurrentFont['desc']['MissingWidth'];
            } elseif (isset($this->CurrentFont['MissingWidth'])) {
                $width = $this->CurrentFont['MissingWidth'];
            } else {
                $width = 600; // just like in DejaVuSans
            }
            $width *= ($this->FontSize ?: $this->default_font_size / 1000);
        }

        return $width;
    }

    /**
     * {@inheritdoc}
     */
    public function _lightenColor($c)
    {
        if (!$c) {
            return '';
        }

        return parent::_lightenColor($c);
    }

    /**
     * {@inheritdoc}
     */
    public function _darkenColor($c)
    {
        if (!$c) {
            return '';
        }

        return parent::_darkenColor($c);
    }
}
