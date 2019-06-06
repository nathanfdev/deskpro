<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MembersController extends AbstractController
{
    /**
     * @Route("/members", name="portal_members")
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request $request
     */
    public function indexAction(Request $request)
    {
        $this->isCommunityEnabledOrNotFoundException();

        $page  = $request->get('page', 1);
        $count = $this->getBrandSetting('portal.per_page_content');
        $pager = $this->getPersonDataService()->getPortalMembersPager($page, $count);

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildMembersList();

        return $this->renderThemeView(
            'Theme:Members:index.html.twig',
            [
                'breadcrumbs' => $breadcrumbs,
                'pager'       => $pager,
                'count'       => $count,
                'page'        => $page,
            ]
        );
    }

    /**
     * @Route("/members/u/{id}", name="portal_members_view")
     * @ParamConverter("person", class="DeskPRO:Person")
     * @Security("is_granted('ROLE_USER')")
     * @PageHttpCache(content="person")
     *
     * @param Request $request
     * @param Person  $person
     *
     * @return Response
     */
    public function viewAction(Request $request, Person $person)
    {
        $this->isCommunityEnabledOrNotFoundException();

        if (!$person->isUser() || $person->isAgent() || $person->isDeleted()) {
            throw $this->createNotFoundException();
        }

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildMembersList();
        $avatar      = $this->container->get('avatar_resolver')->getPersonAvatar($person);

        $customData = [];
        foreach ($this->getEm()->getRepository(CustomDefPerson::class)->getEnabledPublicUserFields() as $def) {
            if ($def->getParent()) {
                continue;
            }
            if ($data = $person->getCustomDataForField($def)) {
                if (is_array($data)) {
                    $val = [];
                    foreach ($data as $datum) {
                        $val[] = $this->get('data.custom_field_util')->getValueForCustomFormField($def, $datum);
                    }
                } else {
                    $val = $this->get('data.custom_field_util')->getValueForCustomFormField($def, $data);
                }
            } else {
                $val = '';
            }
            if (!$val) {
                continue;
            }
            $customData[] = [
                'type'  => $def->type,
                'label' => $def->getTitle(),
                'value' => $val,
            ];
        }

        return $this->renderThemeView(
            'Theme:Members:view.html.twig',
            [
                'breadcrumbs' => $breadcrumbs,
                'person'      => $person,
                'avatar'      => $avatar,
                'custom_data' => $customData,
            ]
        );
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function isCommunityEnabledOrNotFoundException()
    {
        $settings = $this->container->get('settings_resolver');
        if (!$settings->getGlobalSettings()->get('portal.members_community')) {
            throw $this->createNotFoundException();
        }
    }
}
