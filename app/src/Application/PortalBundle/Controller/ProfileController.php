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

namespace Application\PortalBundle\Controller;

use Application\DeskPRO\Entity\Person;
use Application\PersonBundle\Person\Context\CreatePersonContext;
use Application\PortalBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/register", name="portal_user_registration")
     */
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
            $request->getSession()->set('last_username', $person->getPrimaryEmail());

            return $this->redirectToRoute('portal_login');
        }

        return $this->renderThemeView(
            'Theme:Profile:register.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }

    /**
     * @Route("/profile", name="portal_user_profile")
     * @Security("is_granted('EDIT_PROFILE', user)")
     */
    public function editAction(Request $request)
    {
        if ($email_id = $request->query->get('new_primary')) {
            $proposed_new_primary_email = $this->getRepo('DeskPRO:PersonEmail')->find($email_id);
            if ($proposed_new_primary_email->getPerson()->getId() == $this->getUser()->getId()) {
                $this->getUser()->setPrimaryEmail($proposed_new_primary_email);
                $this->getEm()->flush();
                $this->addFlash('success', 'updated.primary.email');
                return $this->redirectToRoute('portal_user_profile');
            }
        }

        if ($email_id = $request->query->get('remove_email')) {
            $proposed_email_removal = $this->getRepo('DeskPRO:PersonEmail')->find($email_id);
            if ($proposed_email_removal->getPerson()->getId() == $this->getUser()->getId()) {
                if (!$proposed_email_removal->isPrimary()) { // cannot remove primary email
                    $this->getUser()->removeEmail($proposed_email_removal);
                    $this->getEm()->remove($proposed_email_removal);
                    $this->getEm()->flush();
                    $this->addFlash('success', 'removed.email');
                    return $this->redirectToRoute('portal_user_profile');
                }
            }
        }


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

        // EMAILS
        $emails_form = $this->createForm('person_manage_emails', $this->getUser(), array(
            'settings' => $this->getBrandContainer()->getSettings()
        ));
        $emails_form->handleRequest($request);
        if ($emails_form->isValid()) {
            $this->getEm()->flush();
            $this->addFlash('success', 'success.updated.emails.phrase');

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

        return $this->renderThemeView(
            'Theme:Profile:edit.html.twig', array(
                'profile_form' => $profile_form->createView(),
                'password_form' => $password_form->createView(),
                'emails_form' => $emails_form->createView()
            )
        );
    }

    /**
     * @return \Application\PersonBundle\Person\PersonFactory
     */
    public function getPersonFactory()
    {
        return $this->get('person_factory');
    }
}
