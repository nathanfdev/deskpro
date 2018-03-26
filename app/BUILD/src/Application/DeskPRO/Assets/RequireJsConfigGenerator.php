<?php

/**
 * DeskPRO.
 *
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
    private $shims = [];

    /**
     * @var array
     */
    private $paths = [];

    /**
     * @param string $url
     */
    public function setBaseUrl($url)
    {
        if ($url === null) {
            $this->base_url = null;

            return;
        }

        $this->setBaseUrlExpr('"'.addslashes($url).'"');
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

        $this->setUrlArgsExpr('"'.addslashes($url_args).'"');
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
     * @param array  $config
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
        $this->addPathExpr($key, '"'.addslashes($path).'"');
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

            $parts = [];
            foreach ($this->shims as $k => $v) {
                $parts[] = "\t\t\"$k\": $v";
            }

            $js .= "\t\t".trim(implode(",\n", $parts));
            $js .= "\n\t},\n";
        }

        if ($this->paths) {
            $js .= "\t\"paths\": {\n";

            $parts = [];
            foreach ($this->paths as $k => $v) {
                $parts[] = "\t\t\"$k\": $v";
            }

            $js .= "\t\t".trim(implode(",\n", $parts));
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
        return 'requirejs.config('.$this->generateConfigObject().');';
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
