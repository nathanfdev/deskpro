<?php

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
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\PersonOnboarding>"
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
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\PersonOnboarding>"
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
