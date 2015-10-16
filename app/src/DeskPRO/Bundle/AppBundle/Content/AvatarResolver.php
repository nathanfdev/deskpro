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

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Symfony\Component\Routing\RouterInterface;

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
     * @param mixed $obj
     * @param int   $size
     *
     * @return null|string
     */
    public function getAvatar($obj, $size = 80)
    {
        if ($obj instanceof Person) {
            return $this->getPersonAvatar($obj, $size) ?: $this->getDefaultPersonAvatar($size);
        } elseif ($obj instanceof Organization) {
            return $this->getOrganizationAvatar($obj, $size) ?: $this->getDefaultOrganizationAvatar($size);
        } else {
            return;
        }
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
            ), true);
        } elseif ($this->use_gravatar && $person->primary_email) {
            $url = $person->primary_email->getGravatarUrl(true).'&s='.$size;

            if ($person->organization) {
                $org_url = $this->getOrganizationAvatar($person->organization, $size);
                if ($org_url) {
                    $url .= '&d='.urlencode($org_url);
                }
            }
        } elseif ($person->organization) {
            $url = $this->getOrganizationAvatar($person->organization, $size);
        }

        return $url;
    }

    /**
     * @param int $size
     */
    public function getDefaultPersonAvatar($size = 80)
    {
        $url = $this->router->generate('serve_default_picture', array(
            's'        => $size,
            'size-fit' => 1,
        ), true);

        return $url;
    }

    /**
     * @param Organization $org
     * @param int          $size
     *
     * @return string|null
     */
    public function getOrganizationAvatar(Organization $org, $size = 80)
    {
        $blob = $org->picture_blob;
        $url  = null;

        if ($blob && $blob->isImage()) {
            $url = $this->router->generate('serve_blob_sizefit', array(
                'blob_auth_id' => $blob->getAuthId(),
                'filename'     => $blob->getFilenameSafe(),
                's'            => $size,
            ), true);
        }

        return $url;
    }

    /**
     * @param int $size
     */
    public function getDefaultOrganizationAvatar($size = 80)
    {
        $url = $this->router->generate('serve_org_picture_default', array(
            's'        => $size,
            'size-fit' => 1,
        ), true);

        return $url;
    }
}
