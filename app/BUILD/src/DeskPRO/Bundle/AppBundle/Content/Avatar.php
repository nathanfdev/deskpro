<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Content;

use JMS\Serializer\Annotation as JMS;

/**
 * Class Avatar.
 *
 * @JMS\ExclusionPolicy("none")
 */
class Avatar
{
    const GRAVATAR_DEFAULT_404     = '404';
    const GRAVATAR_DEFAULT_MM      = 'mm';
    const GRAVATAR_DEFAULT_IDENT   = 'identicon';
    const GRAVATAR_DEFAULT_MONSTER = 'monster';
    const GRAVATAR_DEFAULT_WAVA    = 'wavatar';
    const GRAVATAR_DEFAULT_RETRO   = 'retro';
    const GRAVATAR_DEFAULT_BLANK   = 'blank';

    /**
     * Default pattern used if there is no custom one.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $default_url_pattern;

    /**
     * Url pattern for avatar gathering.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $url_pattern;

    /**
     * Url to gravatar.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $base_gravatar_url;

    /**
     * Avatar constructor.
     *
     * @param string|null $url_pattern
     * @param string|null $default_url_pattern
     * @param string|null $base_gravatar_url
     */
    public function __construct($url_pattern, $default_url_pattern, $base_gravatar_url)
    {
        $this->url_pattern         = $url_pattern;
        $this->default_url_pattern = $default_url_pattern;
        $this->base_gravatar_url   = $base_gravatar_url;
    }

    /**
     * @param int $size
     *
     * @return mixed
     */
    public function getUrl($size = 80)
    {
        if (!$this->url_pattern) {
            return '';
        }

        return str_replace('{{IMG_SIZE}}', $size, $this->url_pattern);
    }

    /**
     * @return string
     */
    public function getUrlPattern()
    {
        if (!$this->url_pattern) {
            return '';
        }

        return $this->url_pattern;
    }

    /**
     * @param int $size
     *
     * @return mixed
     */
    public function getDefaultUrl($size = 80)
    {
        if (!$this->default_url_pattern) {
            return '';
        }

        return str_replace('{{IMG_SIZE}}', $size, $this->default_url_pattern);
    }

    /**
     * @return string
     */
    public function getDefaultUrlPattern()
    {
        if (!$this->default_url_pattern) {
            return '';
        }

        return $this->default_url_pattern;
    }

    /**
     * @param int    $size
     * @param string $default
     *
     * @return string
     */
    public function getGravatarUrl($size = 80, $default = self::GRAVATAR_DEFAULT_BLANK)
    {
        if (!$this->base_gravatar_url) {
            return '';
        }

        return $this->base_gravatar_url."?s={$size}&d=$default";
    }

    /**
     * @return string|null
     */
    public function getBaseGravatarUrl()
    {
        return $this->base_gravatar_url;
    }
}
