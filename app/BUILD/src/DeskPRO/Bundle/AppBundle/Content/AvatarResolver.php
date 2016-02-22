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

use Application\DeskPRO\Entity\Avatar\AvatarOwner;
use Application\DeskPRO\Entity\Person;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * find the avatar image url for any user.
 */
class AvatarResolver
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var bool
     */
    private $use_gravatar;

    /**
     * @param RouterInterface $router
     * @param bool            $use_gravatar
     */
    public function __construct(RouterInterface $router, $use_gravatar = true)
    {
        $this->router       = $router;
        $this->use_gravatar = $use_gravatar;
    }

    /**
     * @param bool $use_gravatar
     */
    public function setUseGravatar($use_gravatar)
    {
        $this->use_gravatar = $use_gravatar;
    }

    /**
     * @param mixed $obj
     * @param int   $size
     * @param bool  $returnedDefault Bool var passed by reference, will be true if returned image is a default fallback
     *
     * @return null|string
     */
    public function getAvatar($obj, $size = 80, &$returnedDefault = null)
    {
        if ($obj instanceof Person) {
            if ($custom = $this->getPersonAvatar($obj, $size)) {
                $returnedDefault = false;

                return $custom;
            } else {
                $returnedDefault = true;

                return $this->getDefaultPersonAvatar($size);
            }
        } elseif ($obj instanceof AvatarOwner) {
            if ($custom = $this->getCommonAvatar($obj, $size)) {
                $returnedDefault = false;

                return $this->getCommonAvatar($obj, $size);
            } else {
                $returnedDefault = true;

                return $this->getDefaultCommonAvatar($size);
            }
        } else {
            return;
        }
    }

    /**
     * @param mixed $obj
     *
     * @return Avatar
     */
    public function getAvatarModel($obj)
    {
        $safeSizePlaceholder = '_____SAFE_PLACEHOLDER_____';

        $url_pattern     = null;
        $default_pattern = null;
        $gravatar        = null;

        if ($obj instanceof Person) {
            if ($obj->picture_blob) {
                $url_pattern = $this->getPersonAvatar($obj, $safeSizePlaceholder);
            }
            if ($this->use_gravatar) {
                $gravatar = $obj->getRawGravatarUrl();
            }
            $default_pattern = $this->getDefaultPersonAvatar($safeSizePlaceholder);
        } elseif ($obj instanceof AvatarOwner) {
            if ($obj->getAvatarBlob()) {
                $url_pattern = $this->getCommonAvatar($obj, $safeSizePlaceholder);
            }
            $default_pattern = $this->getDefaultCommonAvatar($safeSizePlaceholder);
        }

        if ($url_pattern) {
            $url_pattern = str_replace($safeSizePlaceholder, '{{IMG_SIZE}}', $url_pattern);
        }
        if ($default_pattern) {
            $default_pattern = str_replace($safeSizePlaceholder, '{{IMG_SIZE}}', $default_pattern);
        }

        return new Avatar($url_pattern, $default_pattern, $gravatar);
    }

    /**
     * @param mixed  $obj
     * @param string $placeholder
     *
     * @return null|string
     */
    public function getAvatarPattern($obj, $placeholder)
    {
        // Using an intermediate value instead of the passed $placeholder to prevent
        // router from encoding special URL character of the original $placeholder
        $safeSizePlaceholder = '_____SAFE_PLACEHOLDER_____';

        $pattern = $this->getAvatar($obj, $safeSizePlaceholder);
        $pattern = str_replace($safeSizePlaceholder, $placeholder, $pattern);

        return $pattern;
    }

    /**
     * @param Person $person
     * @param int    $size
     *
     * @return string|null
     */
    public function getPersonAvatar(Person $person, $size = 80)
    {
        $blob = $person->picture_blob;
        $url  = null;

        if ($blob && $blob->isImage()) {
            $url = $this->router->generate('serve_blob_sizefit', array(
                'blob_auth_id' => $blob->getAuthId(),
                'filename'     => $blob->getFilenameSafe(),
                's'            => $size,
            ), UrlGeneratorInterface::ABSOLUTE_URL);
        } elseif ($this->use_gravatar && $person->primary_email) {
            $url = $person->primary_email->getGravatarUrl(true).'&s='.$size;

            if ($person->organization) {
                $org_url = $this->getCommonAvatar($person->organization, $size);
                if ($org_url) {
                    $url .= '&d='.urlencode($org_url);
                }
            }
        } elseif ($person->organization) {
            $url = $this->getCommonAvatar($person->organization, $size);
        }

        return $url;
    }

    /**
     * @param int $size
     *
     * @return string
     */
    public function getDefaultPersonAvatar($size = 80)
    {
        $url = $this->router->generate('serve_default_picture', array(
            's'        => $size,
            'size-fit' => 1,
        ), UrlGeneratorInterface::ABSOLUTE_URL);

        return $url;
    }

    /**
     * @param AvatarOwner $org
     * @param int         $size
     *
     * @return string|null
     */
    public function getCommonAvatar(AvatarOwner $org, $size = 80)
    {
        $blob = $org->getAvatarBlob();
        $url  = null;

        if ($blob && $blob->isImage()) {
            $url = $this->router->generate('serve_blob_sizefit', array(
                'blob_auth_id' => $blob->getAuthId(),
                'filename'     => $blob->getFilenameSafe(),
                's'            => $size,
            ), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $url;
    }

    /**
     * @param int $size
     *
     * @return string
     */
    public function getDefaultCommonAvatar($size = 80)
    {
        $url = $this->router->generate('serve_org_picture_default', array(
            's'        => $size,
            'size-fit' => 1,
        ), UrlGeneratorInterface::ABSOLUTE_URL);

        return $url;
    }
}
