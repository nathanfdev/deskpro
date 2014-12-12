<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Themes\Base\Controller;

use Application\DeskPRO\Entity\Person;
use Application\PersonBundle\Person\Context\CreatePersonContext;
use Application\PortalBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ProfileController extends AbstractController
{
    public function registerAction(Request $request)
    {
        $person = $this->getPersonFactory()->createNewPerson();

        $form = $this->createForm('person_registration', $person, array(
            'settings' => $this->getBrandContainer()->getSettings()
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $context = new CreatePersonContext('gateway.person');
            $this->getPersonFactory()->saveNewPerson($person, $context);
            $this->addFlash('success', 'thank.you.for.registering');

            return $this->redirectToRoute('portal_login');
        }

        return $this->render('Theme:Profile:register.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Security("is_granted('EDIT_PROFILE', user)")
     */
    public function editAction(Request $request)
    {
        // PROFILE
        $profile_form = $this->createForm('person_profile', $this->getUser(), array(
            'settings' => $this->getBrandContainer()->getSettings()
        ));
        $profile_form->handleRequest($request);
        if ($profile_form->isValid()) {
            $this->getEm()->flush();
            $this->addFlash('success', 'success.updated.profile.phrase');

            return $this->redirectToRoute('portal_user_profile');
        }

        // PASSWORD
        $password_form = $this->createForm('person_change_password', $this->getUser(), array(
            'settings' => $this->getBrandContainer()->getSettings()
        ));
        $password_form->handleRequest($request);
        if ($password_form->isValid()) {
            $this->getEm()->flush();
            $this->addFlash('success', 'success.changed.password.phrase');

            return $this->redirectToRoute('portal_user_profile');
        }

        return $this->render('Theme:Profile:edit.html.twig', array(
            'profile_form' => $profile_form->createView(),
            'password_form' => $password_form->createView()
        ));
    }

    /**
     * @return \Application\PersonBundle\Person\PersonFactory
     */
    public function getPersonFactory()
    {
        return $this->get('person_factory');
    }
}
