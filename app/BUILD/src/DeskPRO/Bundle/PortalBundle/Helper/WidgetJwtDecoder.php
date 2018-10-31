<?php

namespace DeskPRO\Bundle\PortalBundle\Helper;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use DeskPRO\Bundle\AppBundle\Settings\WidgetSettingsResolver;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Doctrine\ORM\EntityManager;
use Firebase\JWT\JWT;
use Orb\Util\Arrays;

/**
 * Class WidgetJwtDecoder.
 */
class WidgetJwtDecoder
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var WidgetSettingsResolver
     */
    private $settingsResolver;

    /**
     * @var \DeskPRO\Bundle\BrandBundle\Brand\BrandStack
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param EntityManager          $em
     * @param WidgetSettingsResolver $settingsResolver
     * @param BrandStack             $brandStack
     */
    public function __construct(EntityManager $em, WidgetSettingsResolver $settingsResolver, BrandStack $brandStack)
    {
        $this->em               = $em;
        $this->settingsResolver = $settingsResolver;
        $this->brandStack       = $brandStack;
    }

    /**
     * @param string $payload
     *
     * @return array|null
     */
    public function decodeJwtPayload($payload)
    {
        if (!$payload) {
            return;
        }

        $brand     = $this->brandStack->getActive()->getBrand();
        $jwtSecret = $this->settingsResolver->getJwtSecret($brand);

        try {
            $payload = JWT::decode($payload, $jwtSecret, array_keys(JWT::$supported_algs));
            $payload = Arrays::fromStdClass($payload);

            return $payload;
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @param string $payload
     *
     * @return Person|null
     */
    public function getPersonFromJwtPayload($payload)
    {
        $decoded = $this->decodeJwtPayload($payload);
        if (!$decoded) {
            return;
        }

        $id = isset($decoded['person_id']) ? $decoded['person_id'] : null;
        if ($id) {
            return $this->em->getRepository(Person::class)->find($id);
        }

        $email = null;
        foreach (['email', 'user_email'] as $option) {
            if (isset($decoded[$option])) {
                $email = $decoded[$option];
            }
        }

        if ($email) {
            $personEmail = $this->em->getRepository(PersonEmail::class)->findOneBy([
                'email' => $email,
            ]);

            if ($personEmail) {
                return $personEmail->getPerson();
            }
        }

        return;
    }
}
