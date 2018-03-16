<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Helper;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use DeskPRO\Bundle\AppBundle\Settings\WidgetSettingsResolver;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
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
     * @var BrandStack
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
