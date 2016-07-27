<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\CustomField\Context\CustomFieldContext;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A very simple controller that the portal depends on for AJAX requests.
 */
class DataController extends AbstractController
{
    /**
     * @Route("/portal-data/custom-per/{type}/{id}", requirements={"type":"per_user|per_org"})
     * @Security("is_granted('ROLE_USER')")
     * @PageHttpCache
     *
     * @param Request $request
     * @param string  $type
     * @param int     $id
     *
     * @return Response
     */
    public function saveFormCustomPerChoiceControllerAction(Request $request, $type, $id)
    {
        /**
         * NOTE: $type will always be "per_user" because we don't allow the ability
         *       for admins to let a user change organization related choices from portal.
         *       In the case that we do in the future: edit form_theme.html.twig to allow
         *       us to know here if its a user or org field in $type, then deal with accordingly
         *       here. No heavy JS needs to change.
         */
        if ($type !== 'per_user') {
            throw $this->createNotFoundException('this type is not allowed');
        }

        $typeContext = Person::class;
        if (!$newTitle = $request->request->get('new_field')) {
            throw new InvalidArgumentException('please provide "new_field"');
        }

        // get the definition
        $customPerManager = $this->get('tickets.custom_per_field_manager');

        $context = new CustomFieldContext(Ticket::class, $typeContext);
        $def     = $customPerManager->getCustomPerFieldDefinition($id, $context);

        // add a new option
        $new_choice = $customPerManager->createNewOption($def, $newTitle, $this->getUser()->getId());
        $this->persistAndFlushEntity($new_choice);

        return $this->makeJsonResponse([
            'new' => [
                'id'    => $new_choice->id,
                'title' => $new_choice->getTitle(),
            ],
        ]);
    }

    /**
     * @param Request $request
     */
    protected function ensureAjaxRequest(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException(
                'ApiController is an ajax-only controller. You tried to access it directly.'
            );
        }
    }
}
