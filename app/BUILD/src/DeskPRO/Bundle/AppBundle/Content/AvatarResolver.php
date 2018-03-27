<?php

namespace DeskPRO\Bundle\AppBundle\Content;

use Application\DeskPRO\Entity\Avatar\AvatarOwner;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Doctrine\ORM\EntityManager;
use Orb\Util\Strings;
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
     * @var EntityManager
     */
    private $em;

    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param RouterInterface  $router
     * @param EntityManager    $em
     * @param SettingsResolver $settingsResolver
     */
    public function __construct(RouterInterface $router, EntityManager $em, SettingsResolver $settingsResolver)
    {
        $this->router           = $router;
        $this->em               = $em;
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * @param array $personIds
     * @param int   $size
     *
     * @return array
     */
    public function getAvatars(array $personIds, $size = 80)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select(
                'p.id as person_id',
                'pe.email',
                'COALESCE(pb.filename, ob.filename) as filename',
                'COALESCE(pb.authcode, ob.authcode) as authcode',
                '(CASE WHEN pb.filename IS NOT NULL THEN 1 ELSE 0 END) has_person_picture'
            )
            ->from(Person::class, 'p')
            ->leftJoin('p.primary_email', 'pe')
            ->leftJoin('p.picture_blob', 'pb')
            ->leftJoin('p.organization', 'o')
            ->leftJoin('p.picture_blob', 'ob')
            ->where('p.id IN(:ids)')
            ->setParameter('ids', $personIds)
        ;

        $gravatarUrl = 'https://secure.gravatar.com/avatar/';
        $defaultUrl  = $this->router->generate(
            'serve_default_picture',
            [
                's'        => $size,
                'size-fit' => 1,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $blobs  = $qb->getQuery()->getResult();
        $result = [];
        foreach ($blobs as $blob) {
            $blobUrl = null;
            if ($blob['filename']) {
                $blobUrl = $this->router->generate(
                    'serve_blob_sizefit',
                    [
                        'blob_auth_id' => $blob['authcode'],
                        'filename'     => Strings::getFilenameSafe($blob['filename']),
                        's'            => $size,
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            }

            if ($blobUrl && $blob['has_person_picture']) {
                // person picture
                $url = $blobUrl;
            } elseif ($this->settingsResolver->getGlobalSettings()->get('core.use_gravatar') && $blob['email']) {
                // gravatar
                if (!$blobUrl) {
                    $blobUrl = 'mm';
                }

                $url = $gravatarUrl.strtolower(md5($blob['email'])).'?&s='.urlencode($size).'&d='.$blobUrl;
            } elseif ($blobUrl) {
                // org picture
                $url = $blobUrl;
            } else {
                $url = $defaultUrl;
            }

            $result[$blob['person_id']] = $url;
        }

        return $result;
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
        }

        return;
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
            if ($this->settingsResolver->getGlobalSettings()->get('core.use_gravatar')) {
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

        if ($blob && $blob->isImage() && $blob->getAuthId()) {
            $url = $this->router->generate(
                'serve_blob_sizefit',
                [
                    'blob_auth_id' => $blob->getAuthId(),
                    'filename'     => $blob->getFilenameSafe(),
                    's'            => $size,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        } elseif ($this->settingsResolver->getGlobalSettings()->get('core.use_gravatar') && $person->getPrimaryEmail()) {
            $url = $person->getPrimaryEmail()->getGravatarUrl(true).'&s='.$size;

            if ($person->getOrganization()) {
                $org_url = $this->getCommonAvatar($person->getOrganization(), $size);
                if ($org_url) {
                    $url .= '&d='.urlencode($org_url);
                }
            }
        } elseif ($person->getOrganization()) {
            $url = $this->getCommonAvatar($person->getOrganization(), $size);
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
        $url = $this->router->generate(
            'serve_default_picture',
            [
                's'        => $size,
                'size-fit' => 1,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

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

        if ($blob && $blob->isImage() && $blob->getAuthId()) {
            $url = $this->router->generate(
                'serve_blob_sizefit',
                [
                    'blob_auth_id' => $blob->getAuthId(),
                    'filename'     => $blob->getFilenameSafe(),
                    's'            => $size,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
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
        $url = $this->router->generate(
            'serve_org_picture_default',
            [
                's'        => $size,
                'size-fit' => 1,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $url;
    }
}
