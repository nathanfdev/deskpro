<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Form\Model\NewOrganization;
use Application\AgentBundle\Form\Type\NewOrganization as NewOrganizationType;
use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationContactData;
use Application\DeskPRO\Entity\OrganizationDeleted;
use Application\DeskPRO\Entity\OrganizationEmailDomain;
use Application\DeskPRO\Entity\OrganizationFile;
use Application\DeskPRO\Entity\OrganizationNote;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonActivity;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonPref;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketCharge;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\EntityRepository\ChatConversation as ChatConversationRepository;
use Application\DeskPRO\EntityRepository\Organization as OrganizationRepository;
use Application\DeskPRO\EntityRepository\OrganizationEmailDomain as OrganizationEmailDomainRepository;
use Application\DeskPRO\EntityRepository\OrganizationFile as OrganizationFileRepository;
use Application\DeskPRO\EntityRepository\OrganizationNote as OrganizationNoteRepository;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\EntityRepository\PersonActivity as PersonActivityRepository;
use Application\DeskPRO\EntityRepository\PersonEmail as PersonEmailRepository;
use Application\DeskPRO\EntityRepository\PersonPref as PersonPrefRepository;
use Application\DeskPRO\EntityRepository\Ticket as TicketRepository;
use Application\DeskPRO\EntityRepository\TicketCharge as TicketChargeRepository;
use Application\DeskPRO\EntityRepository\Usergroup as UsergroupRepository;
use Application\DeskPRO\Searcher\TicketSearch;
use DeskPRO\Bundle\AppBundle\Notification\Event\Organization\OrganizationCreatedEvent;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\DBAL\DBALException;
use Orb\Util\Arrays;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Handles viewing and editing an org.
 */
class OrganizationController extends AbstractController
{
    /**
     * @param Organization $entity
     * @return array mixed
     * @throws \Exception
     */
    protected function getAPIv2Data( $entity)
    {
        $context = new SideloadSerializationContext();

        // no related entities required for organization but there might be in the future
        // $context->setIncludes(['person']);
        // $context->setInlineSideloads(true);
        $serialized = $this->container->get('serializer')->toArray(new ApiWrapper($entity), $context);
        return $serialized;
    }

    //###########################################################################
    // view
    //###########################################################################

    /**
     * @param int $organization_id
     *
     * @throws DBALException
     *
     * @return Response
     * @throws \Exception
     */
    public function viewAction($organization_id)
    {
        $org = $this->getOrgOr404($organization_id);

        //------------------------------
        // Custom fields
        //------------------------------

        $fieldManager = $this->container->getSystemService('org_fields_manager');
        $customFields = $fieldManager->getDisplayArrayForObject($org);

        // org specific custom fields definitions
        $newFieldManager         = $this->container->getCustomFieldManager();
        $form                    = $newFieldManager->createDefinitionsFormForContext($org);
        $customFieldsDefinitions = $form->createView();

        //------------------------------
        // Misc info needed
        //------------------------------

        /** @var OrganizationNoteRepository $organizationNoteRepository */
        $organizationNoteRepository = $this->em->getRepository(OrganizationNote::class);
        /** @var OrganizationFileRepository $organizationFileRepository */
        $organizationFileRepository = $this->em->getRepository(OrganizationFile::class);
        /** @var TicketRepository $ticketRepository */
        $ticketRepository = $this->em->getRepository(Ticket::class);
        /** @var ChatConversationRepository $chatConversationRepository */
        $chatConversationRepository = $this->em->getRepository(ChatConversation::class);
        /** @var TicketChargeRepository $ticketChargeRepository */
        $ticketChargeRepository = $this->em->getRepository(TicketCharge::class);
        /** @var PersonActivityRepository $personActivityRepository */
        $personActivityRepository = $this->em->getRepository(PersonActivity::class);
        /** @var OrganizationRepository $organizationRepository */
        $organizationRepository = $this->em->getRepository(Organization::class);
        /** @var UsergroupRepository $usergroupRepository */
        $usergroupRepository = $this->em->getRepository(Usergroup::class);
        /** @var PersonRepository $personRepository */
        $personRepository = $this->em->getRepository(Person::class);

        $notes         = $organizationNoteRepository->getNotesForOrganization($org);
        $orgFiles      = $organizationFileRepository->getFilesForOrganization($org);
        $orgFilesCount = count($orgFiles);

        $permissionsHelper          = $this->getPerson()->getHelper('AgentPermissions');
        $allowedTicketDepartmentIds = $permissionsHelper->getAllowedDepartments('tickets', false, 'assign');

        $search = new TicketSearch();
        $search
            ->addTerm(TicketSearch::TERM_ORGANIZATION, TicketSearch::OP_IS, $org->getId())
            ->addTerm(TicketSearch::TERM_DEPARTMENT, TicketSearch::OP_IS, $allowedTicketDepartmentIds);
        if (!$this->getPerson()->hasPerm('agent_tickets.view_unassigned')) {
            $search->addRawWhere('(tickets.agent_id IS NOT NULL OR tickets.agent_team_id IS NOT NULL)');
        }
        if (!$this->getPerson()->hasPerm('agent_tickets.view_others')) {
            $search->addRawWhere('tickets.agent_id IS NULL');
            $search->addRawWhere('tickets.agent_team_id IS NULL');
        }
        $search->addRawWhere('1 OR (tickets.agent_id = '.$this->getPerson()->getId().' AND tickets.organization_id = '.$org->getId().')');
        $search->setOrderBy('ticket.status', 'ASC');

        $orgTckets       = $search->getMatches(['offset' => 0, 'limit' => 30]);
        $orgTckets       = $ticketRepository->getByIds($orgTckets, true);
        $orgTcketsCount  = $search->getCount();
        $orgChats        = $chatConversationRepository->getRecentForOrganization($org);
        $orgChatsCount   = $chatConversationRepository->getCountForOrganization($org);
        $orgCharges      = $ticketChargeRepository->getChargesForOrganization($org, 20);
        $orgChargeTotals = $ticketChargeRepository->getTotalChargesForOrganization($org);
        $activityStream  = $personActivityRepository->getForOrganization($org, 10);
        $membersCount    = $organizationRepository->countMembersFor($org);
        $usergroupNames  = $usergroupRepository->getUsergroupNames();
        $orgUsergroups   = $org->getUsergroups();

        $contactData = [];
        foreach ($org->getContactData() as $cd) {
            if (!isset($contactData[$cd->getContactType()])) {
                $contactData[$cd->getContactType()] = [];
            }
            $contactData[$cd->getContactType()][] = $cd->getTemplateVars();
        }

        $orgDomainData = $this->getOrgEmailDisplayData($org);
        $orgMembers    = $personRepository->getOrganizationMembers($org);
        $orgApi        = [];

        foreach (['id', 'name', 'summary'] as $key) {
            $orgApi[$key] = $org->$key;
        }
        $orgApi['date_created'] = $org->getDateCreated()->getTimestamp();

        foreach ($customFields as $field) {
            $orgApi['custom'][$field['id']] = [
                'id'    => $field['id'],
                'title' => $field['title'],
                'value' => isset($field['value']['value']) ? $field['value']['value'] : false,
            ];
        }

        $vars = [
            'org'                           => $org,
            'org_email_domains'             => $orgDomainData['org_email_domains'],
            'org_count_domain_nonmembers'   => $orgDomainData['org_count_domain_nonmembers'],
            'org_count_domain_takenmembers' => $orgDomainData['org_count_domain_takenmembers'],
            'org_count_domain_members'      => $orgDomainData['org_count_domain_members'],
            'org_members'                   => $orgMembers,
            'org_api'                       => $orgApi,
            'contact_data'                  => $contactData,
            'org_usergroups'                => $orgUsergroups,
            'usergroup_names'               => $usergroupNames,
            'notes'                         => $notes,
            'org_files'                     => $orgFiles,
            'org_files_count'               => $orgFilesCount,
            'activity_stream'               => $activityStream,
            'org_tickets'                   => $orgTckets,
            'org_tickets_count'             => $orgTcketsCount,
            'org_chats'                     => $orgChats,
            'org_chats_count'               => $orgChatsCount,
            'org_charges'                   => $orgCharges,
            'org_charge_totals'             => $orgChargeTotals,
            'members_count'                 => $membersCount,
            'custom_fields'                 => $customFields,
            'custom_fields_definitions'     => $customFieldsDefinitions,
        ];

        // include api_v2_data
        $vars['api_v2_data'] = $this->getAPIv2Data($org);

        return $this->render('AgentBundle:Organization:view.html.twig', $vars);
    }

    //###########################################################################
    // ajax-save
    //###########################################################################

    /**
     * @param int $organization_id
     *
     * @return Response
     */
    public function ajaxSaveAction($organization_id)
    {
        $org = $this->getOrgOr404($organization_id);

        $this->em->beginTransaction();
        $data = [
            'success' => true,
        ];

        $action = $this->in->getString('action');
        if (!$this->person->hasPerm('agent_org.edit') && ($action != 'add-person' && $action != 'remove-person' && $action != 'get-person-row')) {
            throw new NotFoundHttpException();
        }

        switch ($action) {
            case 'name':
                if ($this->in->getString('name')) {
                    $org->setName($this->in->getString('name'));
                    $this->em->persist($org);
                }
                break;
            case 'set-summary':
                $org->setSummary($this->in->getString('summary'));
                $this->em->persist($org);
                break;
            case 'delete-picture':
                $org->setPicture(null);
                $this->em->persist($org);
                break;
            case 'set-picture':
                $blob = $this->em->find(Blob::class, $this->in->getUInt('blob_id'));
                if ($blob) {
                    $org->setPicture($blob);
                    $this->em->persist($org);
                }
                break;
            case 'add-person':
                if (!$this->person->hasPerm('agent_people.edit')) {
                    throw new NotFoundHttpException();
                }
                $person = $this->em->find(Person::class, $this->in->getUInt('person_id'));
                if ($person->getOrganization()) {
                    $data['already_in_organization'] = true;
                } elseif ($person) {
                    $person->setOrganization($org);
                    $person->setOrganizationPosition($this->in->getString('position'));
                    $this->em->persist($person);
                    $data['add_person_id'] = $person['id'];
                    $data['row_html']      = $this->renderView('AgentBundle:Organization:view-members-row.html.twig', ['person' => $person, 'org' => $org]);
                }
                break;
            case 'get-person-row':
                $person = $this->em->find(Person::class, $this->in->getUInt('person_id'));
                if ($person->getOrganization()->getId() == $org->getId()) {
                    $data['row_html'] = $this->renderView(
                        'AgentBundle:Organization:view-members-row.html.twig',
                        ['person' => $person, 'org' => $org]
                    );
                }
                break;
            case 'remove-person':
                if (!$this->person->hasPerm('agent_people.edit')) {
                    throw new NotFoundHttpException();
                }
                $person = $this->em->find(Person::class, $this->in->getUInt('person_id'));
                if ($person && $person->getOrganization() && $person->getOrganization()->getId() == $org->getId()) {
                    $person->setOrganization(null);
                    $this->em->persist($person);
                    $data['remove_person_id'] = $person['id'];
                }
                break;
            case 'set-usergroups':
                $usergroupIds = $this->in->getCleanValueArray('usergroup_ids', 'uint', 'discard');
                $usergroupIds = Arrays::removeFalsey($usergroupIds);

                if ($usergroupIds) {
                    $usergroupIds = array_unique($usergroupIds);

                    // Make sure only valid ones are set
                    $usergroupIds = $this->container->getDb()->fetchAllCol('
                        SELECT id
                        FROM usergroups
                        WHERE id IN (?) AND (sys_name NOT IN (\'everyone\', \'registered\') OR `sys_name` IS NULL)
                    ', [$usergroupIds], [Connection::PARAM_INT_ARRAY]);
                }
                $this->container->getDb()->delete('organization2usergroups', ['organization_id' => $org->getId()]);

                if ($usergroupIds) {
                    $inserts = [];
                    foreach ($usergroupIds as $uid) {
                        $inserts[] = ['organization_id' => $org->getId(), 'usergroup_id' => $uid];
                    }

                    $this->container->getDb()->batchInsert('organization2usergroups', $inserts);
                }
                break;
            case 'remove-file':
                $file = $this->em->find(OrganizationFile::class, $this->in->getUInt('file_id'));
                if ($file && $file->getOrganization() && $file->getOrganization()->getId() == $org->getId()) {
                    $this->em->remove($file);
                    $data['removed_file_id'] = $file['id'];
                }
                break;
            default:
                return $this->createJsonResponse(['error' => true, 'message' => 'Unknown action']);
        }

        $this->em->flush();
        $this->em->commit();

        return $this->createJsonResponse($data);
    }

    /**
     * @param Request $request
     * @param int     $organization_id
     *
     * @return Response
     */
    public function ajaxSaveCustomFieldsAction(Request $request, $organization_id)
    {
        if (!$this->person->hasPerm('agent_org.edit')) {
            throw new NotFoundHttpException();
        }

        $org = $this->getOrgOr404($organization_id);

        $fieldManager     = $this->container->getSystemService('org_fields_manager');
        $postCustomFields = $request->request->get('custom_fields', []);
        if (!empty($postCustomFields)) {
            $fieldManager->saveFormToObject($postCustomFields, $org);
        }

        // specific org custom fields definitions
        $manager = $this->container->getCustomFieldManager();
        $form    = $manager->createDefinitionsFormForContext($org);

        // fix: jquery removes empty arrays from post request
        if (!$request->request->has($form->getName())) {
            $request->request->set($form->getName(), []);
        }
        if (!$form->handleRequest($request)->isValid()) {
            return $this->createJsonResponse([
                'error'                 => true,
                'invalid_custom_fields' => $form->getErrors(true, true)->current(),
            ]);
        }

        $manager->flush($form);
        $customFields = $fieldManager->getDisplayArrayForObject($org);

        return $this->render('AgentBundle:Organization:view-customfields-rendered-rows.html.twig', [
            'org'                       => $org,
            'custom_fields'             => $customFields,
            'custom_fields_definitions' => $form->createView(),
        ]);
    }

    /**
     * @param int $organization_id
     *
     * @return Response
     */
    public function changePictureOverlayAction($organization_id)
    {
        $org = $this->getOrgOr404($organization_id);

        return $this->render('AgentBundle:Organization:change-org-picture.html.twig', [
            'org' => $org,
        ]);
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function saveContactDataAction($organization_id)
    {
        if (!$this->person->hasPerm('agent_org.edit')) {
            throw new NotFoundHttpException();
        }

        $org = $this->getOrgOr404($organization_id);

        // Build contact_data before modifying collection,
        // adding to the collection causes dupe values to be added (doctrine bug w/ add() on collection indexed by id?)
        $contactDataArray         = [];
        $organizationContactDatum = $org->getContactData();
        foreach ($organizationContactDatum as $cd) {
            if (!isset($contactDataArray[$cd->getContactType()])) {
                $contactDataArray[$cd->getContactType()] = [];
            }
            $contactDataArray[$cd->getContactType()][$cd->getId()] = $cd->getTemplateVars();
        }

        // Adding contact data
        $added = [];
        foreach ($this->in->getCleanValueArray('new_contact_data') as $type => $inputs) {
            foreach ($inputs as $input) {
                try {
                    $contactData = new OrganizationContactData();
                    $contactData->setContactType($type);
                    $contactData->applyFormData($input);

                    $contactData->setOrganization($org);

                    $this->em->persist($contactData);

                    $added[] = $contactData;
                } catch (\Exception $e) {
                    throw $e;
                }
            }
        }

        // Adding org emails
        foreach ($this->in->getCleanValueArray('new_org_email_domain') as $domain) {
            $check = $this->em->find(OrganizationEmailDomain::class, $domain);
            if (!$check) {
                $domain         = ltrim($domain, '@');
                $orgEmailDomain = new OrganizationEmailDomain();
                $orgEmailDomain->setOrganization($org);
                $orgEmailDomain->setDomain($domain);
                $this->em->persist($orgEmailDomain);
            }
        }

        //remove_org_email
        foreach ($this->in->getCleanValueArray('remove_org_email') as $domain) {
            $check = $this->em->getRepository('DeskPRO:OrganizationEmailDomain')->find($domain);
            if ($check && $check->organization->id == $org->getId()) {
                $this->em->remove($check);
            }
        }

        // Editing values
        foreach ($this->in->getCleanValueArray('contact_data') as $id => $input) {
            if (!isset($organizationContactDatum[$id])) {
                continue;
            }
            $organizationContactDatum[$id]->applyFormData($input);
            $this->em->persist($organizationContactDatum[$id]);
        }

        // Removing values
        foreach ($this->in->getCleanValueArray('remove_contact_data', 'uint') as $id) {
            if (isset($organizationContactDatum[$id])) {
                $cd = $organizationContactDatum[$id];
                $this->em->remove($organizationContactDatum[$id]);
                $organizationContactDatum->remove($id);

                if (isset($contactDataArray[$cd->getContactType()][$cd->getId()])) {
                    unset($contactDataArray[$cd->getContactType()][$cd->getId()]);
                }
            }
        }

        $this->em->beginTransaction();
        $this->em->flush();
        $this->em->commit();

        // Reset display array
        $contactDataArray = [];
        foreach ($organizationContactDatum as $cd) {
            if (!isset($contactDataArray[$cd->getContactType()])) {
                $contactDataArray[$cd->getContactType()] = [];
            }
            $contactDataArray[$cd->getContactType()][$cd->getId()] = $cd->getTemplateVars();
        }

        foreach ($added as $cd) {
            /** @var OrganizationContactData $cd */
            if (!isset($contactDataArray[$cd->getContactType()])) {
                $contactDataArray[$cd->getContactType()] = [];
            }
            $contactDataArray[$cd->getContactType()][$cd->getId()] = $cd->getTemplateVars();
        }

        /** @var OrganizationEmailDomainRepository $organizationEmailDomainRepository */
        $organizationEmailDomainRepository = $this->em->getRepository(OrganizationEmailDomain::class);
        $orgEmailDomains                   = $organizationEmailDomainRepository->getDomainsForOrganization($org);

        $displayHtml = $this->renderView('AgentBundle:Organization:view-contact-display.html.twig', [
            'org_email_domains' => $orgEmailDomains,
            'org'               => $org,
            'contact_data'      => $contactDataArray,
        ]);
        $editorOverlayHtml = $this->renderView('AgentBundle:Organization:contact-overlay.html.twig', [
            'org_email_domains' => $orgEmailDomains,
            'org'               => $org,
            'contact_data'      => $contactDataArray,
        ]);

        return $this->createJsonResponse([
            'success'             => 1,
            'display_html'        => $displayHtml,
            'editor_overlay_html' => $editorOverlayHtml,
        ]);
    }

    /**
     * @param int $organization_id
     * @param int $person_id
     *
     * @return Response
     */
    public function savePositionAction($organization_id, $person_id)
    {
        if (!$this->person->hasPerm('agent_people.edit')) {
            throw new NotFoundHttpException();
        }

        $person = $this->em->find(Person::class, $person_id);
        if ($person) {
            $person->setOrganizationPosition($this->in->getString('organization_position'));

            $this->em->persist($person);
            $this->em->flush();
        }

        return $this->createJsonResponse(['success' => true]);
    }

    /**
     * @param int $organization_id
     * @param int $person_id
     *
     * @return Response
     */
    public function saveManagerAction($organization_id, $person_id)
    {
        if (!$this->person->hasPerm('agent_people.edit')) {
            throw new NotFoundHttpException();
        }

        $this->db->executeUpdate('
            UPDATE people SET organization_manager = ?
            WHERE id = ? AND organization_id = ?
        ', [(int) $this->in->getBool('organization_manager'), $person_id, $organization_id]);

        return $this->createJsonResponse(['success' => true]);
    }

    //###########################################################################
    // ajax-save-note
    //###########################################################################

    /**
     * @param int $organization_id
     *
     * @return Response
     */
    public function ajaxSaveNoteAction($organization_id)
    {
        if (!$this->person->hasPerm('agent_org.notes')) {
            throw new NotFoundHttpException();
        }

        $org = $this->getOrgOr404($organization_id);

        $noteTxt = $this->in->getString('note');

        $em = App::getOrm();
        $em->beginTransaction();

        $note                 = new OrganizationNote();
        $note['agent']        = $this->person;
        $note['organization'] = $org;
        $note['note']         = $noteTxt;
        $em->persist($note);

        $em->flush();
        $em->commit();

        return $this->createJsonResponse([
            'success'         => true,
            'organization_id' => $org['id'],
            'note_li_html'    => $this->renderView('AgentBundle:Organization:note-li.html.twig', ['note' => $note]),
        ]);
    }

    /**
     * @param $note_id
     *
     * @return Response
     */
    public function deleteNoteAction($note_id)
    {
        if (!$this->person->hasPerm('agent_org.notes')) {
            throw new AccessDeniedException();
        }

        if (!$note = $this->em->find(OrganizationNote::class, $note_id)) {
            throw new NotFoundHttpException();
        }

        $this->em->remove($note);
        $this->em->flush();

        return $this->createJsonResponse(['success' => true]);
    }

    //###########################################################################
    // ajax-save-file
    //###########################################################################

    /**
     * @param $organization_id
     *
     * @return Response
     */
    public function ajaxSaveFileAction($organization_id)
    {
        if (!$this->person->hasPerm('agent_org.notes')) {
            throw new NotFoundHttpException();
        }

        $org = $this->getOrgOr404($organization_id);

        $noteTxt = $this->in->getString('note');

        if ($this->in->getUInt('file_id')) {
            $file = $this->em->find(OrganizationFile::class, $this->in->getUInt('file_id'));
        } else {
            $blob = $this->em->find(Blob::class, $this->in->getUInt('blob_id'));

            $file                 = new OrganizationFile();
            $file['agent']        = $this->person;
            $file['organization'] = $org;
            $file['blob']         = $blob;
        }
        $file['note'] = $noteTxt;

        $em = $this->em;

        $em->beginTransaction();

        $em->persist($file);

        $em->flush();
        $em->commit();

        return $this->createJsonResponse([
            'success'         => true,
            'organization_id' => $org['id'],
            'html'            => $this->renderView('AgentBundle:Person:file-row.html.twig', ['file' => $file]),
        ]);
    }

    //###########################################################################
    // ajax-save-labels
    //###########################################################################

    /**
     * @param $organization_id
     *
     * @return Response
     */
    public function ajaxSaveLabelsAction($organization_id)
    {
        if (!$this->person->hasPerm('agent_org.edit')) {
            throw new NotFoundHttpException();
        }

        $org = $this->getOrgOr404($organization_id);

        $labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

        $org->getLabelManager()->setLabelsArray($labels);

        $this->em->persist($org);
        $this->em->flush();

        return $this->createJsonResponse(['success' => 1]);
    }

    //###########################################################################
    // org domains
    //###########################################################################

    /**
     * @param $org
     *
     * @return array
     */
    protected function getOrgEmailDisplayData($org)
    {
        /** @var OrganizationEmailDomainRepository $organizationEmailDomainRepository */
        $organizationEmailDomainRepository = $this->em->getRepository(OrganizationEmailDomain::class);
        /** @var PersonEmailRepository $personEmailRepository */
        $personEmailRepository = $this->em->getRepository(PersonEmail::class);

        $orgEmailDomains            = $organizationEmailDomainRepository->getDomainsForOrganization($org);
        $orgCountDomainNonMembers   = $personEmailRepository->countDomainsWithNoCompany($orgEmailDomains);
        $orgCountDomainTakenMembers = $personEmailRepository->countDomainsWithOtherCompany($orgEmailDomains, $org);
        $orgCountDomainMembers      = $organizationEmailDomainRepository->countMembersAtDomains($org, $orgEmailDomains);

        return [
            'org'                           => $org,
            'org_email_domains'             => $orgEmailDomains,
            'org_count_domain_nonmembers'   => $orgCountDomainNonMembers,
            'org_count_domain_takenmembers' => $orgCountDomainTakenMembers,
            'org_count_domain_members'      => $orgCountDomainMembers,
        ];
    }

    /**
     * @param $organization_id
     *
     * @return Response
     */
    public function assignDomainAction($organization_id)
    {
        if (!$this->person->hasPerm('agent_org.edit')) {
            throw new NotFoundHttpException();
        }

        $org = $this->getOrgOr404($organization_id);

        $orgDomainManager = $this->container->getSystemService('org_email_domain_manager');
        $domain           = $this->in->getString('domain');

        if ($inUse = $orgDomainManager->isInUse($domain)) {
            return $this->createResponse('<div class="error" data-error-code="in_use" data-org-id="'.$inUse->id.'" />');
        }

        $orgDomainManager->assignDomain($domain, $org);

        $data = $this->getOrgEmailDisplayData($org);

        return $this->render('AgentBundle:Organization:orgemail-display.html.twig', $data);
    }

    /**
     * @param $organization_id
     *
     * @return Response
     */
    public function unassignDomainAction($organization_id)
    {
        if (!$this->person->hasPerm('agent_org.edit')) {
            throw new NotFoundHttpException();
        }

        $org = $this->getOrgOr404($organization_id);

        $domain    = $this->in->getString('domain');
        $orgDomain = $this->em->getRepository('DeskPRO:OrganizationEmailDomain')->findOneBy(['organization' => $org, 'domain' => $domain]);

        if ($orgDomain) {
            $orgDomainManager = $this->container->getSystemService('org_email_domain_manager');
            $orgDomainManager->unassignDomain($orgDomain, $this->in->getBool('remove_users'));
        }

        $data = $this->getOrgEmailDisplayData($org);

        return $this->render('AgentBundle:Organization:orgemail-display.html.twig', $data);
    }

    /**
     * @param $organization_id
     *
     * @return Response
     */
    public function moveDomainUsersAction($organization_id)
    {
        if (!$this->person->hasPerm('agent_org.edit')) {
            throw new NotFoundHttpException();
        }

        $org = $this->getOrgOr404($organization_id);

        $orgDomainManager = $this->container->getSystemService('org_email_domain_manager');
        $domain           = $this->in->getString('domain');

        $orgDomain = $this->em
            ->getRepository(OrganizationEmailDomain::class)
            ->findOneBy(['organization' => $org, 'domain' => $domain]);

        if ($orgDomain) {
            $orgDomainManager->moveNonCompanyUsers($orgDomain);
        }

        $data = $this->getOrgEmailDisplayData($org);

        return $this->render('AgentBundle:Organization:orgemail-display.html.twig', $data);
    }

    /**
     * @param $organization_id
     *
     * @return Response
     */
    public function moveTakenDomainUsersAction($organization_id)
    {
        if (!$this->person->hasPerm('agent_org.edit')) {
            throw new NotFoundHttpException();
        }

        $org = $this->getOrgOr404($organization_id);

        $orgDomainManager = $this->container->getSystemService('org_email_domain_manager');

        $domain    = $this->in->getString('domain');
        $orgDomain = $this->em
            ->getRepository(OrganizationEmailDomain::class)
            ->findOneBy(['organization' => $org, 'domain' => $domain]);

        if (!$orgDomain) {
            throw new NotFoundHttpException();
        }

        $orgDomainManager->moveOtherCompanyUsers($orgDomain);

        $data = $this->getOrgEmailDisplayData($org);

        return $this->render('AgentBundle:Organization:orgemail-display.html.twig', $data);
    }

    //###########################################################################
    // delete
    //###########################################################################

    /**
     * @param $organization_id
     * @param $security_token
     *
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function deleteOrganizationAction($organization_id, $security_token)
    {
        if (!$this->person->hasPerm('agent_org.delete')) {
            throw new NotFoundHttpException();
        }

        if (!$this->session->getEntity()->checkSecurityToken('delete_org', $security_token)) {
            throw new NotFoundHttpException();
        }

        $org = $this->getOrgOr404($organization_id);

        $organizationDeleted = new OrganizationDeleted();

        $organizationDeleted['organization_id'] = $organization_id;
        $organizationDeleted['by_person']       = $this->getPerson();
        $organizationDeleted['reason']          = $this->in->getString('reason');

        $this->em->persist($organizationDeleted);
        $this->em->flush();

        $editManager = $this->container->getSystemService('org_edit_manager');
        $editManager->deleteOrganization($org);

        return $this->createJsonResponse(['success' => true]);
    }

    //###########################################################################
    // New person
    //###########################################################################

    /**
     * @return Response
     */
    public function newOrganizationAction()
    {
        if (!$this->person->hasPerm('agent_org.create')) {
            throw new NotFoundHttpException();
        }

        /** @var PersonPrefRepository $personPrefRepository */
        $personPrefRepository = $this->em->getRepository(PersonPref::class);
        $state                = $personPrefRepository->getPrefForPersonId('agent.ui.state.neworg', $this->person->id);

        //------------------------------
        // Custom fields
        //------------------------------

        $customFieldsForm = $this->get('form.factory')->createNamedBuilder('org_custom_fields', 'form');
        $fieldManager     = $this->container->getOrgFieldManager();
        $customFields     = $fieldManager->getDisplayArrayForObject(new Organization(), $customFieldsForm);

        return $this->render('AgentBundle:Organization:neworganization.html.twig', [
            'state'         => $state,
            'custom_fields' => $customFields,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function newOrganizationSaveAction(Request $request)
    {
        if (!$this->person->hasPerm('agent_org.create')) {
            throw new NotFoundHttpException();
        }

        $newOrg = new NewOrganization($this->person);

        $formType = new NewOrganizationType();
        $form     = $this->get('form.factory')->create($formType, $newOrg);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $form->isValid();

            if (!$newOrg->name) {
                return $this->createJsonResponse(['error' => true, 'error_code' => 'invalid_name']);
            }

            $newOrg->setCustomFieldForm($_POST);
            $newOrg->save();

            $org = $newOrg->getOrganization();

            /** @var PersonPrefRepository $personPrefRepository */
            $personPrefRepository = $this->em->getRepository(PersonPref::class);
            $personPrefRepository->deletePrefForPersonId('agent.ui.state.neworg', $this->person->getId());

            $this->container->get('event_dispatcher')->dispatch(
                OrganizationCreatedEvent::EVENT_NAME,
                new OrganizationCreatedEvent($org)
            );

            return $this->createJsonResponse([
                'success' => true,
                'org_id'  => $org['id'],
            ]);
        } else {
            return $this->createJsonResponse([
                'success' => false,
            ]);
        }
    }

    /**
     * @param int $organization_id
     *
     * @return Organization
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    protected function getOrgOr404($organization_id)
    {
        $org = $this->em->find(Organization::class, $organization_id);

        if (!$org) {
            throw new NotFoundHttpException("There is no organization with ID $organization_id");
        }

        return $org;
    }

    /**
     * @param int $id
     *
     * @return Response
     */
    public function addChildAction($id)
    {
        try {
            $cid = $this->in->getInt('child_id');
            if (!$org = $this->em->find(Organization::class, $id)) {
                throw new NotFoundHttpException();
            }

            if ($cid) {
                $root = $org;
                while ($root->getParent()) {
                    $root = $root->getParent();
                }
                $child = $this->em->find(Organization::class, $cid);
                if (!$child || $child->getParent() || $org === $child || $root === $child) {
                    throw new BadRequestHttpException('That organization cannot be added as a child of the current organization.');
                }
            } else {
                if (!$title = $this->in->getString('title')) {
                    throw new BadRequestHttpException('Please, enter a title.');
                }

                if (!$this->person->hasPerm('agent_org.create')) {
                    throw new AccessDeniedHttpException('You don\t have permission to create an organization.');
                }

                if ($this->em->getRepository(Organization::class)->findOneBy(['name' => $title])) {
                    throw new BadRequestHttpException(sprintf('Organization with the name "%s" already exists.', $title));
                }

                $child = new Organization();
                $child->setName($title);
                $this->em->persist($child);
            }

            $org->getChildren()->add($child);
            $child->setParent($org);
            $this->em->flush();

            return $this->createJsonResponse([
                'id'   => $child->getId(),
                'name' => $child->getName(),
            ]);
        } catch (HttpException $e) {
            return $this->createJsonResponse($e->getMessage(), 400);
        }
    }

    /**
     * @param int $id
     *
     * @return Response
     */
    public function removeChildAction($id)
    {
        try {
            $cid = (int) $this->in->getInt('child_id');
            if (!$org = $this->em->find(Organization::class, $id)) {
                throw new NotFoundHttpException('There is no such organization');
            }

            if (!$child = $this->em->find(Organization::class, $cid)) {
                throw new NotFoundHttpException('There is no such child organization');
            }

            if ($child->getParent() === $org) {
                $org->getChildren()->removeElement($child);
                $child->setParent(null);

                $this->em->persist($child);
                $this->em->flush();
            }

            return $this->createJsonResponse([
                'id'   => $child->getId(),
                'name' => $child->getName(),
            ]);
        } catch (HttpException $e) {
            return $this->createJsonResponse($e->getMessage(), 400);
        }
    }
}
