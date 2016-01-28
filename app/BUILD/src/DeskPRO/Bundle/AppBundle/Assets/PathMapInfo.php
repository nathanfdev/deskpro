<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Assets;

class PathMapInfo
{
    const DESKPRO_PATH = 'deskpro_path';
    const ROOT_PATH    = 'root_path';
    const URL          = 'url';

    const BUILD_VERSION = 'build_version';
    const NO_VERSION    = 'no_version';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $version = 'no_version';

    /**
     * @var string
     */
    private $path;

    /**
     * For URL parts, 0 is the http url, 1 is the https link
     * Either may be null.
     *
     * @var array
     */
    private $url_parts = [];

    /**
     * @return PathMapInfo
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @param array $details
     *
     * @return PathMapInfo
     */
    public static function createFromArray(array $details)
    {
        $p = new self();

        $details = array_replace([
            'type'    => '',
            'value'   => '',
            'version' => self::NO_VERSION,
        ], $details);

        switch ($details['type']) {
            case self::DESKPRO_PATH:
                $p->setDeskproPath($details['value']);
                break;
            case self::ROOT_PATH:
                $p->setRootPath($details['value']);
                break;
            case self::URL:
                if (is_array($details['value'])) {
                    foreach ($details['value'] as $v) {
                        $p->addUri($details['value']);
                    }
                } else {
                    $p->addUri($details['value']);
                }
                break;
            default:
                throw new \InvalidArgumentException('Invalid PathMapInfo type');
        }

        if ($details['version']) {
            $p->setVersion($details['version']);
        }

        return $p;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setDeskproPath($path = '/')
    {
        $this->type = self::DESKPRO_PATH;
        $this->path = '/'.trim($path, '/');

        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setRootPath($path = '/')
    {
        $this->type = self::ROOT_PATH;
        $this->path = '/'.trim($path, '/');

        return $this;
    }

    /**
     * Sets the URL. May be prefixed with http://, https:// or //.
     *
     * @param string $url
     *
     * @return $this
     */
    public function addUri($url)
    {
        $url = rtrim($url, '/');

        $this->type = self::URL;

        if (substr($url, 0, 2) === '//') {
            $this->url_parts[] = ['http://'.$url, 'https://'.$url];
        } elseif (substr($url, 0, 8) === 'https://') {
            $this->url_parts[] = [null, $url];
        } else {
            $this->url_parts[] = [$url, null];
        }

        return $this;
    }

    /**
     * @param string $version
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version ?: self::NO_VERSION;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeskproPath()
    {
        return $this->type === self::DESKPRO_PATH;
    }

    /**
     * @return bool
     */
    public function isRootPath()
    {
        return $this->type === self::DESKPRO_PATH;
    }

    /**
     * @return bool
     */
    public function isUrl()
    {
        return $this->type === self::URL;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if ($this->type === self::DESKPRO_PATH || $this->type === self::ROOT_PATH) {
            return $this->path;
        }

        return;
    }

    /**
     * @return string
     */
    public function getAllUrls()
    {
        return array_merge($this->getInsecureUrls(), $this->getSecureUrls());
    }

    /**
     * @return string
     */
    public function getInsecureUrls()
    {
        if ($this->type === self::URL) {
            $urls = [];
            foreach ($this->url_parts as $url) {
                if ($url[0]) {
                    $urls[] = $url[0];
                }
            }

            return $urls;
        }

        return;
    }

    /**
     * @return string
     */
    public function getSecureUrls()
    {
        if ($this->type === self::URL) {
            $urls = [];
            foreach ($this->url_parts as $url) {
                if ($url[1]) {
                    $urls[] = $url[1];
                }
            }

            return $urls;
        }

        return;
    }

    /**
     * @return string|null
     */
    public function getVersion()
    {
        return $this->version ?: null;
    }
}
