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

namespace DeskPRO\Bundle\PortalBundle\Designer;

use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class UploadFileContext.
 */
class UploadFileContext
{
    /**
     * @var UploadedFile
     */
    private $file;

    private $filename;

    /**
     * @var
     */
    private $tag;

    /**
     * @var ThemeSetAsset
     */
    private $asset;

    /**
     * @var null|string
     */
    private $name;

    /**
     * @var string
     */
    private $extension;

    /**
     * @var null|string
     */
    private $mimeType;

    /**
     * UploadFileContext constructor.
     *
     * @param UploadedFile $file
     * @param ThemeSet     $themeSet
     * @param              $tag
     */
    public function __construct(UploadedFile $file, ThemeSet $themeSet, $tag)
    {
        $this->file      = $file;
        $this->filename  = $file->getRealPath();
        $this->extension = $file->getClientOriginalExtension();
        $this->mimeType  = $file->getClientMimeType();
        $this->name      = $file->getClientOriginalName();
        $this->tag       = $tag;

        $this->asset = new ThemeSetAsset();
        $this->asset->setThemeSet($themeSet);
        $this->asset->setName($this->name);
        $this->asset->setTags([$tag]);
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return mixed
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return ThemeSetAsset
     */
    public function getAsset()
    {
        return $this->asset;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return null|string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $extension
     *
     * @return $this
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * @param null|string $mimeType
     *
     * @return $this
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @param null|string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->asset->setName($name);

        return $this;
    }

    /**
     * @param string $filename
     *
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @param mixed $tag
     *
     * @return $this
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        $this->asset->setTags([$tag]);

        return $this;
    }
}
