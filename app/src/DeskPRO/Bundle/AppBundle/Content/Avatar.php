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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Content;

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
     * @var string
     */
    private $url_pattern;

    /**
     * @var bool
     */
    private $url_is_custom;

    /**
     * @var string
     */
    private $base_gravatar;

    /**
     * Create an avatar that represents a custom avatar.
     *
     * @param string $url_pattern
     * @param string $base_gravatar
     *
     * @return Avatar
     */
    public static function createCustomAvatar($url_pattern, $base_gravatar = null)
    {
        return new self($url_pattern, true, $base_gravatar);
    }

    /**
     * Create an avatar that represents a default avatar.
     *
     * @param string $default_url_pattern
     * @param string $base_gravatar
     *
     * @return Avatar
     */
    public static function createDefaultAvatar($default_url_pattern, $base_gravatar = null)
    {
        return new self($default_url_pattern, false, $base_gravatar);
    }

    /**
     * Avatar constructor.
     *
     * @param string $url_pattern   A pattern with {IMG_SIZE} placeholder
     * @param string $url_is_custom If $url_pattern is a custom image instead of a default image
     * @param string $base_gravatar Gravatar base URL (no options on it)
     */
    private function __construct($url_pattern, $url_is_custom, $base_gravatar = null)
    {
        $this->url_pattern   = $url_pattern;
        $this->url_is_custom = $url_is_custom;
        $this->base_gravatar = $base_gravatar;
    }

    /**
     * @param int $size
     *
     * @return mixed
     */
    public function getUrl($size = 80)
    {
        return str_replace('{{IMG_SIZE}}', $size, $this->url_pattern);
    }

    /**
     * @return string
     */
    public function getUrlPattern()
    {
        return $this->url_pattern;
    }

    /**
     * @return bool
     */
    public function isCustom()
    {
        return $this->url_is_custom;
    }

    /**
     * @param int    $size
     * @param string $default
     * @param string|null
     */
    public function getGravatarUrl($size = 80, $default = self::GRAVATAR_DEFAULT_BLANK)
    {
        if (!$this->base_gravatar) {
            return;
        }

        return $this->base_gravatar."?s={$size}&d=$default";
    }

    /**
     * @return string|null
     */
    public function getBaseGravatarUrl()
    {
        return $this->base_gravatar;
    }
}
