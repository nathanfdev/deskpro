<?php

namespace DeskPRO\Component\Pdf;

/**
 * Class mPDF.
 */
class mPDF extends \Mpdf\Mpdf
{
    /**
     * {@inheritdoc}
     */
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
}
