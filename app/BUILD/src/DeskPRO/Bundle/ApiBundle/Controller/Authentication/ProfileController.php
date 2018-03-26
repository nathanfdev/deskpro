<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Authentication;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\People\PersonProfileType;
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
 * @ApiDoc(
 *     target="putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\PersonProfileType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
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
