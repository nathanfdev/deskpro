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
namespace DeskPRO\Bundle\ApiBundle\Controller\Authentication;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\ApiBundle\Model\Me;
use DeskPRO\Bundle\ApiBundle\Model\PersonProfile;
use DeskPRO\Bundle\ApiBundle\Security\Token\AgentSessionSecurityToken;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MeController.
 */
class MeController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get information about the authenticated user",
     *      output="DeskPRO\Bundle\ApiBundle\Model\Me",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/me", name="me")
     */
    public function meAction()
    {
        /** @var \DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken $token */
        $token  = $this->get('security.token_storage')->getToken();
        $person = $token->getUser();

        $me              = new Me();
        $me->auth_method = $token->getName();
        $me->person_id   = $person->getId();
        $me->person      = $person->toApiData(); //TODO

        if ($token instanceof AgentSessionSecurityToken) {
            $me->app_id = $token->getAppId();
        }

        return View::create(
            $this->createRepresentation(
               $me
            ),
            200
        );
    }

    /**
     * @Get("/me/profile", name="get_my_profile")
     */
    public function getProfileAction()
    {
        return View::create(
            $this->dataSerialize(new PersonProfile($this->getUser())),
            Response::HTTP_OK
        );
    }

    /**
     * @Put("/me/profile", name="put_my_profile")
     *
     * @param Request $request
     *
     * @return View
     */
    public function putProfileAction(Request $request)
    {
        $person = $this->getUser();

        $form = $this->get('form.factory')->createNamedBuilder(null, 'person_profile', $person)->getForm();
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($person);
        $em->flush();

        return View::create(
            $this->dataSerialize(new PersonProfile($person)),
            Response::HTTP_CREATED
        );
    }
}
