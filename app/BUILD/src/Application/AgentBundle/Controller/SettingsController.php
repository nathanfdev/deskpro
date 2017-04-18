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

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Form\Model\SettingsProfile as SettingsProfileModel;
use Application\AgentBundle\Form\Type\SettingsProfile;
use Application\AgentBundle\Validator\AgentProfileValidator;
use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\People\AgentNotifPrefs\PrefsLoader as AgentNotifPrefsLoader;
use Application\DeskPRO\People\PersonEditManager;
use Application\DeskPRO\Tickets\Filters\TicketFilterCollection;
use Application\DeskPRO\UI\RuleBuilder;
use DateTimeZone;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SettingsController extends AbstractController
{
    //###########################################################################
    // Profile
    //###########################################################################

    public function profileAction()
    {
        $defaultCountryCode = $this->getContainer()->get('deskpro.core.settings')->get('core.default_country_code');
        $edit_profile       = new SettingsProfileModel($this->person, $defaultCountryCode);
        $edit_form          = new SettingsProfile();
        $form               = $this->get('form.factory')->create($edit_form, $edit_profile);

        /** @var \Application\DeskPRO\People\PasswordPolicyValidator $password_validator */
        $password_validator = App::$container->getSystemService('password_policy_validator');

        return $this->render('AgentBundle:Settings:profile.html.twig', [
            'form'             => $form->createView(),
            'edit_profile'     => $edit_profile,
            'password_expired' => $password_validator->isPasswordExpired($this->person),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profileSaveAction(Request $request)
    {
        $edit_profile = new SettingsProfileModel($this->person);
        $edit_form    = new SettingsProfile();
        $form         = $this->get('form.factory')->create($edit_form, $edit_profile);

        $form->handleRequest($request);
        $edit_profile->new_emails    = $this->in->getCleanValueArray('new_emails', 'string', 'discard');
        $edit_profile->remove_emails = $this->in->getCleanValueArray('remove_emails', 'uint', 'discard');

        if ($edit_profile->requiresAuth()) {
            $code = $this->in->getString('authcode');
            if (!$this->session->getEntity()->checkSecurityToken('password_confirm'.$this->person->secret_string, $code)) {
                return $this->createJsonResponse(['error' => true, 'error_code' => 'invalid_auth']);
            }
        }

        // Check for dupe emails where a user already exists, we'll send a merge request
        // -> Only do this when the other user is a plain user and not an agent
        // FEATURE: user merge
        if (false) {
            $check_exists = $this->em->getRepository('DeskPRO:Person')->findByEmail($edit_profile->email);
            if ($check_exists && $check_exists->getId() != $this->person->getId() && !$check_exists->is_agent) {
                // Insert the merge code
                $tmpdata = Entity\TmpData::create('validated_merge_user', [
                    'agent_id'      => $this->person->getId(),
                    'other_user_id' => $check_exists->getId(),
                    'email_address' => $edit_profile->email,
                    '_type'         => 'agent_profile_email',
                ], '+2 days');

                $this->em->persist($tmpdata);
                $this->em->flush();

                $vars = [
                    'person'       => $this->person,
                    'other_person' => $check_exists,
                    'authcode'     => $tmpdata->getCode(),
                    'old_email'    => $this->person->getEmailAddress(),
                    'new_email'    => $edit_profile->email,
                ];

                if ($this->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                    $viewModel = $this->get('email.agent_viewmodel_factory')
                        ->createAgentChangeEmailMergeUserModel($this->person->getEmailAddress(), $edit_profile->email);
                    $this->get('email.email_sender')
                        ->send($viewModel, ['to' => $edit_profile->email]);
                } else {
                    // Send validation email
                    $message = $this->container->getMailer()->createMessage();
                    $message->setTemplate('DeskPRO:emails_agent:agent-changeemail-mergeuser.html.twig', $vars);
                    $message->setTo($edit_profile->email, $this->person->getDisplayName());
                    $this->container->getMailer()->send($message);
                }

                // Pop the old email address back so it passes the dupe check validation,
                // we're not actually updating the address yet
                $edit_profile->email = $this->person->getEmailAddress();
            }
        }

        $validator = new AgentProfileValidator();
        if (!$validator->isValid($edit_profile)) {
            return $this->createJsonResponse([
                'error'       => true,
                'error_code'  => 'form_errors',
                'form_errors' => $validator->getErrors(),
            ]);
        }

        $edit_profile->save();

        if ($edit_profile->password) {
            $this->db->delete('sessions', ['person_id' => $this->person->id]);

            return $this->createJsonResponse(['success' => true, 'login' => true]);
        }

        return $this->createJsonResponse(['success' => true]);
    }

    public function profileSaveWelcomeAction()
    {
        if ($this->in->getString('name')) {
            $this->person->setName($this->in->getString('name'));
        }

        if ($blob_id = $this->in->getString('new_blob_id')) {
            $blob = $this->em->getRepository(Entity\Blob::class)->getByAuthId($blob_id);
            if ($blob) {
                $this->person->picture_blob = $blob;
            }
        }

        if (($tz = $this->in->getString('timezone')) && in_array($tz, DateTimeZone::listIdentifiers())) {
            $this->person->timezone = $tz;
        }

        $this->em->persist($this->person);
        $this->em->flush();

        $this->db->delete('people_prefs', [
            'person_id' => $this->person->getId(),
            'name'      => 'agent.first_login',
        ]);
        $this->db->delete('people_prefs', [
            'person_id' => $this->person->getId(),
            'name'      => 'agent.first_login_name',
        ]);

        return $this->createJsonResponse([
            'success' => true,
        ]);
    }

    public function signatureAction()
    {
        if (!$this->person->PermissionsManager->GeneralChecker->canSetSignature()) {
            throw new NotFoundHttpException();
        }

        return $this->render('AgentBundle:Settings:signature.html.twig', [
            'signature'       => $this->person->getSignature(),
            'signature_html'  => $this->person->getSignatureHtml(),
            'tweet_signature' => $this->person->getTweetSignature(),
        ]);
    }

    public function signatureSaveAction()
    {
        if (!$this->person->PermissionsManager->GeneralChecker->canSetSignature()) {
            throw new NotFoundHttpException();
        }

        if ($this->in->getBool('is_html_signature')) {
            $signature_html = $this->in->getHtmlCore('ticket_signature');
            $signature_html = Strings::trimHtml($signature_html);

            foreach ($this->in->getCleanValueArray('blob_inline_ids', 'uint', 'discard') as $blob_id) {
                $blob = App::getEntityRepository(Entity\Blob::class)->find($blob_id);
                if ($blob) {
                    $regex          = '#(<img[^>]+src=")'.preg_quote($blob->getDownloadUrl(true), '#').'("[^>]*>)#i';
                    $replace        = $blob->getEmbedCode(true, 'signature_image');
                    $signature_html = preg_replace($regex, $replace, $signature_html);
                }
            }

            $regex          = '#<img[^>]+class="dp-signature-image" alt="([^"]+)"[^>]*>#i';
            $signature_html = preg_replace($regex, '$1', $signature_html);

            $signature_html = str_replace(['<div', '</div>'], ['<p', '</p>'], $signature_html);
            $signature_html = preg_replace('/^<p>/', '<p class="dp-signature-start">', trim($signature_html));

            $signature = strip_tags($signature_html);
        } else {
            $signature      = $this->in->getString('ticket_signature');
            $signature_html = nl2br(htmlspecialchars($signature));
            if ($signature_html) {
                $signature_html = '<p class="dp-signature-start">'.$signature.'</p>';
            }
        }

        $this->db->delete('drafts', [
            'person_id'    => $this->person->id,
            'content_type' => 'ticket',
        ]);

        $this->person->setPreference('agent.ticket_signature', $signature);
        $this->person->setPreference('agent.ticket_signature_html', $signature_html);

        $this->person->setPreference('agent.tweet_signature', $this->in->getString('tweet_signature'));

        $this->em->persist($this->person);
        $this->em->flush();

        return $this->createJsonResponse(['success' => true]);
    }

    public function updateTimezoneAction()
    {
        $tz = $this->in->getString('timezone');

        if (!in_array($tz, DateTimeZone::listIdentifiers())) {
            return $this->createJsonResponse(['error' => true, 'error_code' => 'invalid_timezone']);
        }

        $this->person->timezone = $tz;

        $this->db->beginTransaction();
        try {
            $this->em->persist($this->person);
            $this->em->flush();
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createJsonResponse(['success' => true]);
    }

    //###########################################################################
    // Ticket Notifications
    //###########################################################################

    public function ticketNotificationsAction()
    {
        $loader = new AgentNotifPrefsLoader($this->person, $this->em);
        $prefs  = $loader->getPrefs();

        $filters = new TicketFilterCollection($this->em->getRepository(Entity\LegacyTicketFilter::class)->getFiltersForPerson($this->person));

        $all_filters      = $filters->getAllFilters();
        $sys_filters      = $filters->getSystemFilters();
        $sys_filters_hold = $filters->getSystemHoldFilters();
        $custom_filters   = $filters->getCustomFilters();

        $my_subs = $prefs->getFilterSubs();

        $sys_ids = [];
        foreach ($sys_filters as $f) {
            $sys_ids[$f->sys_name] = $f->id;
        }

        return $this->render('AgentBundle:Settings:ticket-notifications.html.twig', [
            'all_filters'      => $all_filters,
            'sys_filters'      => $sys_filters,
            'sys_ids'          => $sys_ids,
            'sys_filters_hold' => $sys_filters_hold,
            'custom_filters'   => $custom_filters,
            'my_subs'          => $my_subs,
        ]);
    }

    public function ticketNotificationsSaveAction()
    {
        $subs = $this->in->getCleanValueArray('filter_sub', 'array', 'uint');

        /** @var PersonEditManager $person_editor */
        $person_editor = $this->container->getSystemService('person_edit_manager');
        $person_editor->saveFilterSubscriptions($this->person, $subs);

        $this->em->getRepository(Entity\PersonPref::class)->savePref(
            $this->person,
            'agent_notif.ticket_mention',
            $this->in->getString('ticket_mention') == 'smart_send' ? 'smart_send' : 'always_send'
        );
        $this->em->getRepository(Entity\PersonPref::class)->savePref(
            $this->person,
            'agent_notify_override.all.email',
            $this->in->getBool('agent_notify_override_all_email') ? 1 : 0
        );
        $this->em->getRepository(Entity\PersonPref::class)->savePref(
            $this->person,
            'agent_notify_override.forward.email',
            $this->in->getBool('agent_notify_override_forward_email') ? 1 : 0
        );
        $this->em->getRepository(Entity\PersonPref::class)->savePref(
            $this->person,
            'agent_notify_override.all.alert',
            $this->in->getBool('agent_notify_override_all_alert') ? 1 : 0
        );
        $this->em->getRepository(Entity\PersonPref::class)->savePref(
            $this->person,
            'agent_notify_override.forward.alert',
            $this->in->getBool('agent_notify_override_forward_alert') ? 1 : 0
        );

        return $this->createJsonResponse(['success' => true]);
    }

    //###########################################################################
    // General Notifications
    //###########################################################################

    public function otherNotificationsAction()
    {
        $my_prefs = $this->em->getRepository(Entity\PersonPref::class)->getPrefgroupForPersonId('agent_notif', $this->person->id, true);

        return $this->render('AgentBundle:Settings:other-notifications.html.twig', [
            'my_prefs' => $my_prefs,
        ]);
    }

    public function otherNotificationsSaveAction()
    {
        $prefs = $this->in->getCleanValueArray('my_prefs', 'bool', 'string');

        $person_editor = $this->container->getSystemService('person_edit_manager');
        $person_editor->saveNotificationPreferences($this->person, $prefs);

        return $this->createJsonResponse(['success' => true]);
    }

    //###########################################################################
    // Ticket Filters
    //###########################################################################

    /**
     * Just a list of filters.
     */
    public function ticketFiltersAction()
    {
        $filters        = $this->em->getRepository(Entity\LegacyTicketFilter::class)->getPersonalFilters($this->person);
        $filters_shared = $this->em->getRepository(Entity\LegacyTicketFilter::class)->getSharedFilters($this->person);

        //agent.ui.filter
        $filter_show_options = $this->db->fetchAllKeyValue("
            SELECT name, value_str
            FROM people_prefs
            WHERE person_id = ? AND (name LIKE 'agent.ui.filter-visibility.%')
        ", [$this->person->id]);

        return $this->render('AgentBundle:Settings:ticket-filters.html.twig', [
            'filters'             => $filters,
            'filters_shared'      => $filters_shared,
            'filter_show_options' => $filter_show_options,
        ]);
    }

    /**
     * Edit a filter.
     */
    public function ticketFilterEditAction($filter_id)
    {
        if ($filter_id) {
            $filter = $this->em->find(Entity\LegacyTicketFilter::class, $filter_id);
            if ($filter and $filter['sys_name']) {
                $filter = null;
            }

            if (!$filter) {
                throw $this->createNotFoundException("There is no filter with ID $filter_id");
            }
        } else {
            $filter = new Entity\LegacyTicketFilter();
        }

        $term_options = App::getApi('tickets')->getTicketOptions($this->person);

        $ticket_field_defs                    = App::getApi('custom_fields.tickets')->getEnabledFields();
        $custom_fields                        = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);
        $term_options['custom_ticket_fields'] = $custom_fields;
        $brands                               = $this->getAgentBrands();

        return $this->render('AgentBundle:Settings:ticket-filter-edit.html.twig', [
            'term_options' => $term_options,
            'filter'       => $filter,
            'brands'       => $brands,
        ]);
    }

    public function ticketFilterEditSaveAction($filter_id)
    {
        if ($filter_id) {
            $is_new = false;
            $filter = $this->em->find(Entity\LegacyTicketFilter::class, $filter_id);
            if ($filter and $filter['sys_name']) {
                $filter = null;
            }

            if (!$filter) {
                throw $this->createNotFoundException("There is no filter with ID $filter_id");
            }
        } else {
            $is_new = true;
            $filter = new Entity\LegacyTicketFilter();
        }

        $filter['title']    = $this->in->getString('filter.title');
        $filter['person']   = $this->person;
        $filter['order_by'] = $this->in->getString('filter.order_by');

        $term_rules      = RuleBuilder::newTermsBuilder();
        $filter['terms'] = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw', 'discard'));

        $filter['is_global']  = false;
        $filter['is_enabled'] = true;

        $this->em->persist($filter);
        $this->em->flush();

        return $this->createJsonResponse(['success' => true, 'filter_id' => $filter->id, 'filter_title' => $filter->title, 'is_new' => $is_new]);
    }

    public function ticketFilterDeleteAction($filter_id)
    {
        $filter = $this->em->find(Entity\LegacyTicketFilter::class, $filter_id);
        if (!$filter) {
            throw $this->createNotFoundException('Could not find filter');
        }

        $this->em->remove($filter);
        $this->em->flush();

        return $this->createJsonResponse(['success' => true]);
    }

    //###########################################################################
    // Ticket Macros
    //###########################################################################

    public function ticketMacrosAction()
    {
        $all_macros = $this->person->getHelper('Agent')->getMacros();

        if (!count($all_macros)) {
            $all_macros = false;
        }

        return $this->render('AgentBundle:Settings:ticket-macros.html.twig', [
            'show_saved_flash' => $this->in->getBool('saved'),
            'all_macros'       => $all_macros,
        ]);
    }

    public function ticketMacroEditAction($macro_id)
    {
        $ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

        if ($macro_id) {
            $is_new = false;
            $macro  = $this->em->getRepository(Entity\TicketMacro::class)->find($macro_id);
            if (!$macro || (!$macro->is_global && $macro->person->id != $this->person->id)) {
                throw $this->createNotFoundException('Could not find macro');
            }
        } else {
            $macro           = new Entity\TicketMacro();
            $macro['person'] = $this->person;
            $is_new          = true;
        }

        $ticket_field_defs                      = App::getApi('custom_fields.tickets')->getEnabledFields();
        $custom_fields                          = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);
        $ticket_options['custom_ticket_fields'] = $custom_fields;

        // People stuff
        $ticket_options['people_organizations'] = $this->em->getRepository(Entity\Organization::class)->getOrganizationNames();
        $people_field_defs                      = App::getApi('custom_fields.people')->getEnabledFields();
        $ticket_options['custom_people_fields'] = $custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($people_field_defs);

        return $this->render('AgentBundle:Settings:ticket-macro-edit.html.twig', [
            'ticket_options' => $ticket_options,
            'macro'          => $macro,
            'is_new'         => $is_new,
        ]);
    }

    public function ticketMacroEditSaveAction($macro_id)
    {
        if ($macro_id) {
            $is_new = false;
            $macro  = $this->em->getRepository(Entity\TicketMacro::class)->find($macro_id);
            if (!$macro || (!$macro->is_global && $macro->person->id != $this->person->id)) {
                throw $this->createNotFoundException('Could not find macro');
            }
        } else {
            $macro           = new Entity\TicketMacro();
            $macro['person'] = $this->person;
            $is_new          = true;
        }

        $macro['title']     = $this->in->getString('macro.title');
        $macro['is_global'] = $this->in->getBool('macro.is_global');

        $action_rules = RuleBuilder::newActionsBuilder();
        $actions      = $action_rules->readForm($this->in->getCleanValueArray('actions', 'raw', 'str_simple'));

        $macro['actions'] = $actions;

        if (!$macro->getTitle()) {
            return $this->createJsonResponse(['error' => true, 'form_errors' => ['title_required']]);
        }

        $this->em->persist($macro);
        $this->em->flush();

        return $this->createJsonResponse([
            'success'  => true,
            'is_new'   => $is_new,
            'macro_id' => $macro->getId(),
            'title'    => $macro->getTitle(),
        ]);
    }

    public function ticketMacroDeleteAction($macro_id)
    {
        $macro = $this->em->getRepository(Entity\TicketMacro::class)->find($macro_id);
        if (!$macro || (!$macro->is_global && $macro->person->id != $this->person->id)) {
            throw $this->createNotFoundException('Could not find macro');
        }

        $this->em->remove($macro);
        $this->em->flush();

        return $this->createJsonResponse(['success' => true]);
    }

    //###########################################################################
    // Ticket Slas
    //###########################################################################

    /**
     * Just a list of filters.
     */
    public function ticketSlasAction()
    {
        $sla_filter = $this->person->getPref('agent.ui.sla.ticket-filter');
        $slas       = $this->em->getRepository(Entity\Sla::class)->getAllSlas();

        //agent.ui.filter
        $filter_show_options = $this->db->fetchAllKeyValue("
            SELECT name, value_str
            FROM people_prefs
            WHERE person_id = ? AND (name LIKE 'agent.ui.sla.filter-visibility.%')
        ", [$this->person->id]);

        return $this->render('AgentBundle:Settings:ticket-slas.html.twig', [
            'slas'                => $slas,
            'sla_filter'          => $sla_filter,
            'filter_show_options' => $filter_show_options,
        ]);
    }

    /**
     * @return Entity\Brand[]
     */
    protected function getAgentBrands()
    {
        /** @var Entity\Department[] $departments */
        $departments = $this->container->getDataService('Department')
            ->getPersonDepartments($this->person, 'tickets', [], 'assign');
        /** @var Entity\Brand[] $brands */
        $brands = $this->em->getRepository(Entity\Brand::class)->findAll();
        foreach ($brands as $key => $brand) {
            $department_found = false;
            foreach ($departments as $department) {
                if ($department->hasBrand($brand)) {
                    $department_found = true;
                    break;
                }
            }
            if (!$department_found) {
                unset($brands[$key]);
            }
        }

        return $brands;
    }
}
