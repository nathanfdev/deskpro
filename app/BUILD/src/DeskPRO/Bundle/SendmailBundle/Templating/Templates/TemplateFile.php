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

namespace DeskPRO\Bundle\SendmailBundle\Templating\Templates;

use Application\DeskPRO\Templating\Templates\Template;
use DeskPRO\Component\Filesystem\SafeFile;

class TemplateFile extends Template
{
    /**
     * @var string
     */
    private $filePath;

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

        if ($bundle === 'DeskPRO') {
            $path = DP_ROOT.'/src/Application/DeskPRO/Resources/views/';
        } else {
            $path = DP_ROOT."/src/DeskPRO/Bundle/$bundle/Resources/views/";
        }
        if ($dir) {
            $path .= "$dir/";
        }
        $path .= $file;

        $this->filePath = $path;
    }

    /**
     * Check if the template file exists.
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->filePath);
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
        return $this->filePath;
    }

    /**
     * @return string|null
     */
    public function getContent()
    {
        if (!$this->exists()) {
            return null;
        }

        if ($this->content === null) {
            $this->content = SafeFile::fileGetContents($this->filePath, DP_ROOT.'/src');
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
