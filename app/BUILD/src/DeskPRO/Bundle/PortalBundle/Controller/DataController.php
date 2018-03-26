<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\CustomFieldDefinition;
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
        if (!$newTitle = $request->request->get('new_field')) {
            throw new InvalidArgumentException('please provide "new_field"');
        }

        // get the definition
        $def = $this->getEm()->getRepository(CustomFieldDefinition::class)->find($id);

        // add a new option
        $new_choice             = $def->spawnChild($newTitle);
        $new_choice->context_id = $this->getUser()->getId();
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
