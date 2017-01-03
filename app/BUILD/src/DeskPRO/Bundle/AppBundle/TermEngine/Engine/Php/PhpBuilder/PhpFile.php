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
