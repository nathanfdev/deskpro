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

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\PersonOnboarding;
use DeskPRO\Bundle\AppBundle\Form\Type\People\PersonOnboardingType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class PeopleOnboardingController.
 *
 * @ApiModes("all")
 * @Rest\Route("/people/onboarding")
 * @ApiDoc(target="all", section="People", output="DeskPRO\Bundle\AppBundle\Entity\PersonOnboarding")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\People\PersonOnboardingType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\PersonOnboarding"
 *      }
 *     }
 * )
 */
class PeopleOnboardingController extends CrudController
{
    public static $entity     = PersonOnboarding::class;
    public static $type       = PersonOnboardingType::class;
    public static $exposeOnly = ['put', 'post'];

    /**
     * @ApiDoc(
     *     section="People",
     *     description="Get current user available onboardings",
     *     statusCodes={
     *         200="Success",
     *         404="Not Found error will returned in case there is no available onboardings"
     *     }
     * )
     *
     * @Rest\Get("/new")
     *
     * @return View
     */
    public function getNewAction()
    {
        $onboardings = $this->getRepository(PersonOnboarding::class)->findOneBy([
            'person' => $this->getUser(),
            'status' => PersonOnboarding::STATUS_NEW,
        ]);

        return new View($this->wrap($onboardings));
    }

    /**
     * @ApiDoc(
     *     section="People",
     *     description="Get current user pending onboardings",
     *     statusCodes={
     *         200="Success",
     *         404="Not Found error will returned in case there is no available onboardings"
     *     }
     * )
     *
     * @Rest\Get("/pending")
     *
     * @return View
     */
    public function getPendingAction()
    {
        $onboardings = $this->getRepository(PersonOnboarding::class)->findOneBy([
            'person' => $this->getUser(),
            'status' => [
                PersonOnboarding::STATUS_NEW,
                PersonOnboarding::STATUS_IN_PROGRESS,
            ],
        ]);

        return new View($this->wrap($onboardings));
    }
}
