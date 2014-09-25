<?php

require_once(__DIR__.'/src/mpdf.php');

/**
 * DeskPRO wrapper for mPDF
 */
class mPDF_mPDF extends mPDF
{
	function WriteHTML($html,$sub=0,$init=true,$close=true)
	{
		$e = error_reporting(E_ERROR | E_WARNING | E_PARSE);
		$res = parent::WriteHTML($html, $sub, $init, $close);
		error_reporting($e);

		return $res;
	}
}