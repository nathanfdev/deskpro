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
 * @category Entities
 */

namespace Application\DeskPRO\Assets;

class RequireJsConfigGenerator
{
	/**
	 * @var null|string
	 */
	private $base_url = null;

	/**
	 * @var null|string
	 */
	private $url_args = null;

	/**
	 * @var array
	 */
	private $shims = array();

	/**
	 * @var array
	 */
	private $paths = array();

	/**
	 * @param string $url
	 */
	public function setBaseUrl($url)
	{
		if ($url === null) {
			$this->base_url = null;
			return;
		}

		$this->setBaseUrlExpr('"' . addslashes($url) . '"');
	}

	/**
	 * @param string $url_expr
	 */
	public function setBaseUrlExpr($url_expr)
	{
		if ($url_expr === null) {
			$this->base_url = null;
			return;
		}

		$this->base_url = $url_expr;
	}


	/**
	 * @return string|null
	 */
	public function getBaseUrlExpr()
	{
		return $this->base_url;
	}


	/**
	 * @param string $url_args
	 */
	public function setUrlArgs($url_args)
	{
		if ($url_args === null) {
			$this->url_args = null;
			return;
		}

		$this->setUrlArgsExpr('"' . addslashes($url_args) . '"');
	}


	/**
	 * @param string $url_args_expr
	 */
	public function setUrlArgsExpr($url_args_expr)
	{
		if ($url_args_expr === null) {
			$this->url_args = null;
			return;
		}

		$this->url_args = $url_args_expr;
	}


	/**
	 * @return string|null
	 */
	public function getUrlArgsExpr()
	{
		return $this->url_args;
	}


	/**
	 * @param string $key
	 * @param array $config
	 */
	public function addShim($key, array $config)
	{
		$this->addShimExpr($key, json_encode($config));
	}


	/**
	 * @return array
	 */
	public function getShimExprs()
	{
		return $this->shims;
	}


	/**
	 * @param string $key
	 * @param string $expr
	 */
	public function addShimExpr($key, $expr)
	{
		$this->shims[$key] = $expr;
	}


	/**
	 * @param string $key
	 * @param string $path
	 */
	public function addPath($key, $path)
	{
		$this->addPathExpr($key, '"' . addslashes($path) . '"');
	}


	/**
	 * @param string $key
	 * @param string $path_expr
	 */
	public function addPathExpr($key, $path_expr)
	{
		$this->paths[$key] = $path_expr;
	}


	/**
	 * @return array
	 */
	public function getPathExprs()
	{
		return $this->paths;
	}


	/**
	 * @return string
	 */
	public function generateConfigObject()
	{
		$js = "{\n";

		if ($this->base_url !== null) {
			$js .= "\t\"baseUrl\": {$this->base_url},\n";
		}
		if ($this->url_args !== null) {
			$js .= "\t\"urlArgs\": {$this->url_args},\n";
		}

		if ($this->shims) {
			$js .= "\t\"shim\": {\n";

			$parts = array();
			foreach ($this->shims as $k => $v) {
				$parts[] = "\t\t\"$k\": $v";
			}

			$js .= "\t\t" . trim(implode(",\n", $parts));
			$js .= "\n\t},\n";
		}

		if ($this->paths) {
			$js .= "\t\"paths\": {\n";

			$parts = array();
			foreach ($this->paths as $k => $v) {
				$parts[] = "\t\t\"$k\": $v";
			}

			$js .= "\t\t" . trim(implode(",\n", $parts));
			$js .= "\n\t},\n";
		}

		$js = trim($js);
		$js = rtrim($js, ',');

		$js .= "\n}";

		return $js;
	}


	/**
	 * @return string
	 */
	public function generateRequireJsConfigCode()
	{
		return "requirejs.config(" . $this->generateConfigObject() . ");";
	}


	/**
	 * @param RequireJsConfigGenerator $gen
	 */
	public function addPathsFromGenerator(RequireJsConfigGenerator $gen)
	{
		foreach ($gen->getPathExprs() as $k => $v) {
			$this->addPathExpr($k, $v);
		}
	}
}