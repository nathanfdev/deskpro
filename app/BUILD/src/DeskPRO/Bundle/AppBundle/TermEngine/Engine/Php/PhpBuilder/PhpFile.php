<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder;

class PhpFile
{
    /**
     * @var string
     */
    protected $file_name;

    /**
     * @var string
     */
    protected $dir;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var PhpClass
     */
    protected $class;

    public function __toString()
    {
        $string = '<?php';

        if ($this->namespace) {
            $string .= "\n\nnamespace ".$this->namespace.';';
        }

        if ($this->class) {
            $string .= "\n\n".$this->class;
        }

        return "$string\n";
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * @param string $file_name
     */
    public function setFileName($file_name)
    {
        $this->file_name = $file_name;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @param string $dir
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return PhpClass
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param PhpClass $class
     */
    public function setClass(PhpClass $class = null)
    {
        $this->class = $class;
    }
}
