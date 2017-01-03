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

namespace DeskPRO\Bundle\ApiBundle\Controller\Authentication;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonProfileType;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProfileController.
 *
 * @ApiModes("all")
 * @Rest\Route("/me/profile")
 * @SerializerView(mapping={
 *     "Application\DeskPRO\Entity\Person": "DeskPRO\Bundle\AppBundle\Serializer\Model\Person\PersonProfile"
 * })
 */
class ProfileController extends BaseController
{
    /**
     * Get my profile.
     *
     * @ApiDoc(
     *     section="Auth",
     *     description="get my profile action",
     *     statusCodes={
     *         200="Success"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\Person\PersonProfile"
     * )
     *
     * @Rest\Get("")
     */
    public function getAction()
    {
        return $this->wrap($this->getUser());
    }

    /**
     * @ApiDoc(
     *     section="Auth",
     *     description="update my profile action",
     *     statusCodes={
     *         200="Success",
     *         400="Bad request"
     *     }
     * )
     *
     * @Rest\Put("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function putAction(Request $request)
    {
        $person = $this->getUser();
        $form   = $this->createForm(PersonProfileType::class, $person);

        $form->submit($request->request->all(), false);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($person);
        $em->flush();

        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}
