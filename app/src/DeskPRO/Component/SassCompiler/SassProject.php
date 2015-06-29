<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\SassCompiler;

class SassProject
{
    /**
     * @var string[]
     */
    private $file_sources = array();

    /**
     * @var string[]
     */
    private $include_paths = array();

    /**
     * @param string $p
     */
    public function addIncludePath($p)
    {
        $this->include_paths[] = rtrim($p, '\\/');
    }

    /**
     * Sets the main SCSS entry point file
     *
     * @param string $source
     */
    public function setSource($source)
    {
        $this->addFileSource('__main__.scss', $source);
    }

    /**
     * Adds a source file to the project (i.e, can be @import'd)
     *
     * @param string $name    File name
     * @param string $source  The source for the file
     */
    public function addFileSource($name, $source)
    {
        $name = trim($name, '\\/');

        if (!preg_match('#[a-zA-Z0-9_\-][a-zA-Z0-9\._\- \\/]#', $name)) {
            throw new \InvalidArgumentException("The file name must only contain normal characters");
        }

        $this->file_sources[$name] = $source;
    }

    /**
     * @return \string[]
     */
    public function getIncludePaths()
    {
        return $this->include_paths;
    }

    /**
     * @return \string[]
     */
    public function getFileSources()
    {
        return $this->file_sources;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return isset($this->file_sources['__main__.scss']) ? $this->file_sources['__main__.scss'] : '';
    }
}