<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Templating\Templates;

class TemplateFile extends Template
{
    /**
     * @var string
     */
    private $file_path;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $parts = explode(':', $this->getName());

        if (count($parts) != 3) {
            throw new \InvalidArgumentException("Invalid template name: {$this->getName()}");
        }

        list($bundle, $dir, $file) = $parts;

        $path = DP_ROOT."/src/Application/$bundle/Resources/views/";
        if ($dir) {
            $path .= "$dir/";
        }
        $path .= $file;

        $this->file_path = $path;
    }

    /**
     * Check if the template file exists.
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->file_path);
    }

    /**
     * @return bool
     */
    public function isCustom()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->file_path;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        if (!$this->exists()) {
            return;
        }

        if ($this->content === null) {
            $this->content = file_get_contents($this->file_path);
        }

        return $this->content;
    }

    public function getOriginalName()
    {
        return;
    }

    public function getOriginalContent()
    {
        return;
    }

    /**
     * @return string
     */
    public function getType()
    {
        if ($this->type !== null) {
            return $this->type;
        }

        if (strpos($this->getContent(), '<dp:subject') !== false) {
            $this->type = 'email';
        } else {
            $this->type = 'normal';
        }

        return $this->type;
    }
}
