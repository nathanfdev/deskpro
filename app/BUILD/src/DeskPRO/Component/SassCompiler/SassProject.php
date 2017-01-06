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

namespace DeskPRO\Component\SassCompiler;

class SassProject
{
    /**
     * @var string[]
     */
    private $file_sources = [];

    /**
     * @var string[]
     */
    private $include_paths = [];

    /**
     * @param string $p
     */
    public function addIncludePath($p)
    {
        $this->include_paths[] = rtrim($p, '\\/');
    }

    /**
     * Sets the main SCSS entry point file.
     *
     * @param string $source
     */
    public function setSource($source)
    {
        $this->addFileSource('__main__.scss', $source);
    }

    /**
     * Sets the filename of the SCSS entry point.
     *
     * Note: This is a shortcut for just @import'ing the file, so the file must be importable
     * from the compiler for this to work properly. If you don't want to do that,
     * you might want to use `setSource` and pass the entire file contents in directly.
     *
     * @param string $file_name
     */
    public function setSourceFile($file_name)
    {
        $this->setSource('@import "'.addslashes($file_name).'"');
    }

    /**
     * Adds a source file to the project (i.e, can be @import'd). This should take precedence over any include pats.
     * E.g., if a file exists on the path but you set it manually here, then the compiler should take this version
     * over the one on the filesystem.
     *
     * @param string $name   File name
     * @param string $source The source for the file
     */
    public function addFileSource($name, $source)
    {
        if (!preg_match('#[a-zA-Z0-9_\-][a-zA-Z0-9\._\- \\/]#', $name)) {
            throw new \InvalidArgumentException('The file name must only contain normal characters');
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
