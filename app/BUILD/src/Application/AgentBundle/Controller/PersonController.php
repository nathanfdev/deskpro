<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Form\Model\NewPerson as NewPersonModel;
use Application\AgentBundle\Form\Type\NewPerson as NewPersonType;
use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\Handler\HandlerAbstract;
use Application\DeskPRO\CustomFields\PersonFieldManager;
use Application\DeskPRO\DependencyInjection\SystemServices\LanguageDataService;
use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\BanEmail;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\LogEvent;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person as Person;
use Application\DeskPRO\Entity\PersonActivity;
use Application\DeskPRO\Entity\PersonContactData;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonFile;
use Application\DeskPRO\Entity\PersonNote;
use Application\DeskPRO\Entity\PersonPref;
use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketCharge;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\EntityRepository\ChatConversation as ChatConversationRepository;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\Form\Type\PhoneNumberType;
use Application\DeskPRO\HttpFoundation\Cookie;
use Application\DeskPRO\Log\Event\UserMerged;
use Application\DeskPRO\People\PersonEditManager;
use Application\DeskPRO\People\PersonMerge\PersonMerge;
use Application\DeskPRO\Reader\VCard;
use Application\EmailBundle\SwiftMailer\Mailer;
use DeskPRO\Bundle\AppBundle\Notification\Event\People\PersonCreatedEvent;
use Orb\Util\Arrays;
use Orb\Util\DpStrings;
use Orb\Validator\StringEmail;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Handles viewing and editing a person.
 */
class PersonController extends AbstractController
{
    //###########################################################################
    // /agent/people/:person_id                                   agent_people_view
    //###########################################################################

    public function viewAction($person_id, $with_warn_for_email = false)
    {
        $person = $this->getPersonOr404($person_id);

        if (!$person['first_name'] && !$person['last_name'] && $person['name']) {
            $parts = explode(' ', $person['name'], 2);
            $parts = Arrays::removeFalsey($parts);

            if ($parts) {
                $person['first_name'] = $parts[0];
                if (isset($parts[1])) {
                    $person['last_name'] = $parts[1];
                }
            }
        }

        if ($person->is_agent) {
            $person->loadHelper('Agent');
        }

        //------------------------------
        // Custom fields
        //------------------------------

        $manager = $this->container->getCustomFieldManager();

        // todo replace with:
//		 $form = $manager->createFormForOwner($person, $this->person);
//		 $custom_fields = $form->createView();
        $field_manager = $this->container->getPersonFieldManager();
        $custom_fields = $field_manager->getDisplayArrayForObject($person);

        $form                      = $manager->createDefinitionsFormForContext($person);
        $custom_fields_definitions = $form->createView();

        //------------------------------
        // Misc info needed
        //------------------------------

        $notes = $this->em->getRepository(PersonNote::class)->getNotesForPerson($person);
        /** @var Ticket $rep */
        $rep = $this->em->getRepository(Ticket::class);

        $permissionsHelper          = $this->getPerson()->getHelper('AgentPermissions');
        $allowedTicketDepartmentIds = $permissionsHelper->getAllowedDepartments('tickets', false, 'full');

        $person_tickets       = $rep->getPersonTickets($person, $this->getPerson(), 251, 'status', 'DESC', $allowedTicketDepartmentIds);
        $person_tickets_count = $rep->countTicketsForPerson(
            $person,
            $this->getPerson(),
            ['awaiting_agent', 'awaiting_user', 'resolved', 'archived', 'hidden'],
            $allowedTicketDepartmentIds

        );

        $person_files       = $this->em->getRepository(PersonFile::class)->getFilesForPerson($person);
        $person_files_count = count($person_files);

        $max                    = 5;
        $person_tickets_initial = [];
        foreach ($person_tickets as $t) {
            if ($t->status == 'open') {
                $person_tickets_initial[$t->id] = $t;
                unset($person_tickets[$t->id]);
                if (count($person_tickets_initial) >= $max) {
                    break;
                }
            }
        }
        if (count($person_tickets_initial) < $max) {
            foreach ($person_tickets as $t) {
                if ($t->status == 'pending') {
                    $person_tickets_initial[$t->id] = $t;
                    unset($person_tickets[$t->id]);
                    if (count($person_tickets_initial) >= $max) {
                        break;
                    }
                }
            }
            if (count($person_tickets_initial) < $max) {
                foreach ($person_tickets as $t) {
                    $person_tickets_initial[$t->id] = $t;
                    unset($person_tickets[$t->id]);
                    if (count($person_tickets_initial) >= $max) {
                        break;
                    }
                }
            }
        }

        $person_charges       = $this->em->getRepository(TicketCharge::class)->getChargesForPerson($person, 20);
        $person_charge_totals = $this->em->getRepository(TicketCharge::class)->getTotalChargesForPerson($person);

        $activity_stream = $this->em->getRepository(PersonActivity::class)->getForPerson($person, 50);

        $contact_data = [];
        foreach ($person->contact_data as $cd) {
            if (!isset($contact_data[$cd->contact_type])) {
                $contact_data[$cd->contact_type] = [];
            }
            $contact_data[$cd->contact_type][] = $cd->getTemplateVars();
        }

        $contact_data['phone_numbers'] = $this->createForm('collection', $person->phone_numbers, [
            'type'         => new PhoneNumberType(),
            'allow_add'    => true,
            'allow_delete' => true,
            'options'      => [
                'label'            => false,
                'show_phone_label' => true,
            ],
        ])->createView();

        $session = $this->em->getRepository(Session::class)->getSessionForPerson($person);

        $timezone_options = \DateTimeZone::listIdentifiers();
        $usergroup_names  = $this->em->getRepository(Usergroup::class)->getUsergroupNames();
        $reg_group        = $this->container->getUserGroups()->getRegisteredGroup();

        $person->loadHelper('PermissionsManager');
        $person_usergroups_ids     = $person->getPermissionsManager()->getUsergroupIds();
        $person_org_usergroups_ids = $person->getPermissionsManager()->getOrganizationUsergroupIds();

        // Org stuff
        $org_members_count = null;
        $org_contact_data  = null;
        if ($person->organization) {
            $org_members_count = $this->em->getRepository(Organization::class)->countMembersFor($person->organization);

            $org_contact_data = [];
            foreach ($person->organization->contact_data as $cd) {
                if (!isset($org_contact_data[$cd->contact_type])) {
                    $org_contact_data[$cd->contact_type] = [];
                }
                $org_contact_data[$cd->contact_type][] = $cd->getTemplateVars();
            }
        }

        $allowedChatDepartmentsIds = $permissionsHelper->getAllowedDepartments('chat');

        /** @var ChatConversationRepository $chatConversationRepository */
        $chatConversationRepository = $this->em->getRepository(ChatConversation::class);
        $person_chats               = $chatConversationRepository->getPastChatsForPerson($person, $this->getPerson(), 'date_created', 'DESC', $allowedChatDepartmentsIds);
        $person_chats_count         = count($person_chats);

        $is_editable = $this->isPersonEditable($person);
        $perms       = [
            'edit'           => $is_editable && $this->person->hasPerm('agent_people.edit'),
            'delete'         => $is_editable && $this->person->hasPerm('agent_people.delete'),
            'merge'          => $is_editable && $this->person->hasPerm('agent_people.merge'),
            'disable'        => $is_editable && !$person->is_agent && $this->person->hasPerm('agent_people.disable'),
            'manage_emails'  => $is_editable && $this->person->hasPerm('agent_people.manage_emails'),
            'reset_password' => $is_editable && !$person->is_agent && $this->person->hasPerm('agent_people.reset_password'),
            'notes'          => $is_editable && $this->person->hasPerm('agent_people.notes'),
            'org_create'     => $is_editable && $this->person->hasPerm('agent_org.create'),
            'login_as'       => !$person->is_agent && $this->person->hasPerm('agent_people.login_as'),
        ];

        $person_api = $person->getDataForWidget();

        $is_vcf = $this->in->getBool('vcf');

        if ($is_vcf) {
            $response = new \Symfony\Component\HttpFoundation\Response();
            $response->headers->set('Content-Type', 'text/vcf');

            if ($person->getName()) {
                $filename = $person->getName();
            } else {
                $filename = $person->getEmailAddress();
            }

            $filename = str_replace(' ', '_', $filename);
            $filename = preg_replace('[^a-zA-Z0-9_.@-]', '', $filename);

            if (strlen($filename) == 0) {
                $filename = 'Unknown_'.$person->id;
            }

            if (strlen($filename) > 128) {
                $filename = substr($filename, 0, 128);
            }

            $response->headers->set('Content-Disposition', 'attachment; filename='.$filename.'.vcf');
            $vcard = \File_IMC::build('vCard');

            $vcard->setFormattedName($person->name);
            $vcard->setName($person->last_name, $person->first_name, '', '', '');
            //$vcard->setPhoto($person->gravatar_url);

            if ($person->organization) {
                $vcard->addOrganization($person->organization->name);
            }

            if (!empty($person['organization_position'])) {
                $vcard->setTitle($person['organization_position']);
            }

            foreach ($person->emails as $email) {
                $vcard->addEmail($email->email);
            }

            foreach ($contact_data as $c_data) {
                foreach ($c_data as $data) {
                    if (!isset($data['contact_type'])) {
                        continue;
                    }
                    switch ($data['contact_type']) {
                        case 'website':
                            $vcard->setURL($data['url']);
                            break;
                        case 'address':
                            $vcard->addAddress(
                                '',
                                '',
                                $data['address'],
                                $data['city'],
                                $data['state'],
                                $data['zip'],
                                $data['country']
                            );
                            break;
                    }
                }
            }

            foreach ($person->phone_numbers as $phone) {
                $vcard->addTelephone($phone->getFormattedForVCard());
            }

            $response->setContent($vcard->fetch());

            return $response;
        }

        $has_email_validating = false;
        foreach ($person->emails as $e) {
            if (!$e->is_validated) {
                $has_email_validating = true;
                break;
            }
        }

        $banned_emails = [];
        foreach ($person->getEmailAddresses() as $eml) {
            $match = null;
            if (App::getOrm()->getRepository(BanEmail::class)->isEmailBanned($eml, $match)) {
                $banned_emails[$eml] = $eml;
            }
        }

        $changelog = $this->em->getRepository(LogEvent::class)->findBy(
            ['subject' => 'Person', 'subject_id' => $person['id'], 'parent' => null],
            ['id' => 'DESC']
        );

        $brands         = $this->em->getRepository(Brand::class)->findAll();
        $defaultBrandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
        if (!$defaultBrandId && count($brands)) {
            $defaultBrandId = $brands[0]->getId();
        }

        return $this->render('AgentBundle:Person:view.html.twig', [
            'with_warn_for_email'       => $with_warn_for_email,
            'person'                    => $person,
            'banned_emails'             => $banned_emails,
            'has_email_validating'      => $has_email_validating,
            'person_api'                => $person_api,
            'person_usergroups_ids'     => $person_usergroups_ids,
            'person_org_usergroups_ids' => $person_org_usergroups_ids,
            'session'                   => $session,
            'timezone_options'          => $timezone_options,
            'usergroup_names'           => $usergroup_names,
            'contact_data'              => $contact_data,
            'activity_stream'           => $activity_stream,
            'custom_fields'             => $custom_fields,
            'notes'                     => $notes,
            'person_files'              => $person_files,
            'person_files_count'        => $person_files_count,
            'person_tickets'            => $person_tickets,
            'person_chats'              => $person_chats,
            'person_chats_count'        => $person_chats_count,
            'person_tickets_initial'    => $person_tickets_initial,
            'person_tickets_count'      => $person_tickets_count,
            'person_charges'            => $person_charges,
            'person_charge_totals'      => $person_charge_totals,
            'org_members_count'         => $org_members_count,
            'org_contact_data'          => $org_contact_data,
            'perms'                     => $perms,
            'is_person_editable'        => $is_editable,
            'reg_group'                 => $reg_group,
            'person_object_counts'      => $this->em->getRepository(Person::class)->getPersonObjectCounts($person),
            'changelog'                 => $changelog,
            'brands'                    => $brands,
            'default_brand_id'          => $defaultBrandId,

            'custom_fields_definitions' => $custom_fields_definitions,
        ]);
    }

    public function getBasicInfoAction($person_id)
    {
        $person = $this->getPersonOr404($person_id);

        return $this->createJsonResponse([
            'person_id'    => $person,
            'name'         => $person->getDisplayName(),
            'email'        => $person->getPrimaryEmailAddress(),
            'contact_name' => $person->getDisplayContact(),
            'url'          => $this->generateUrl('agent_people_view', ['person_id' => $person->id]),
        ]);
    }

    //###########################################################################
    // viewSession
    //###########################################################################

    public function viewSessionAction($session_id)
    {
        $session = $this->em->find(Session::class, $session_id);

        if ($session->is_person) {
            return $this->viewAction($session->person->id);
        }

        // removed visitor assocations
        $related_person     = null;
        $person_chats       = [];
        $person_chats_count = 0;

        return $this->render('AgentBundle:Person:view-session.html.twig', [
            'person_chats'       => $person_chats,
            'person_chats_count' => $person_chats_count,
            'session'            => $session,
        ]);
    }

    //###########################################################################
    // /agent/people/:person_id/ajax-save                     agent_people_ajaxsave
    //###########################################################################

    public function ajaxSaveAction($person_id)
    {
        $person = $this->getPersonOr404($person_id);

        $data = [
            'success' => true,
        ];

        $action = $this->in->getString('action');

        if (!$this->person->hasPerm('agent_people.edit') || !$this->isPersonEditable($person)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        switch ($action) {
            case 'name':
                if ($this->in->getString('name')) {
                    $person->name = $this->in->getString('name');
                    $this->em->persist($person);
                }
                break;

            case 'quick-edit-name':
                $person->name         = $this->in->getString('name');
                $person->title_prefix = $this->in->getString('title_prefix');
                if ($person->organization) {
                    $person->organization_position = $this->in->getString('organization_position');
                }
                $this->em->persist($person);
                break;

            case 'timezone':
                if (in_array($this->in->getString('timezone'), \DateTimeZone::listIdentifiers())) {
                    $person->timezone = $this->in->getString('timezone');
                    $this->em->persist($person);
                }

                $data['bit_html'] = $this->renderView('AgentBundle:Person:view-bit-timezoneinfo.html.twig', ['person' => $person]);

                break;

            case 'set-is-disabled':
                if (!$this->person->hasPerm('agent_people.disable') || !$this->isPersonEditable($person)) {
                    throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
                }
                $person->is_disabled = $this->in->getBool('is_disabled');
                $this->em->persist($person);
                break;

            case 'disable_autoresponses':
                $person->setDisableAutoresponses(
                    $this->in->getBool('disable_autoresponses'),
                    'Disabled by '.$this->person->getDisplayContact()
                );

                $this->em->persist($person);
                break;

            case 'toggle_confirmed':
                $person->is_confirmed = !$person->is_confirmed;
                $this->em->persist($person);
                break;

            case 'set-primary-email':
                if (!$this->person->hasPerm('agent_people.manage_emails')) {
                    throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
                }

                $email_id  = $this->in->getUint('email_id');
                $set_email = $person->getEmailId($email_id);
                if ($set_email) {
                    $person->primary_email = $set_email;
                    $this->em->persist($person);
                }

                $data['primary_email_address'] = $person->primary_email->email;
                break;

            case 'delete-picture':
                $person->setDisablePicture(false);
                if ($person->getPictureBlob()) {
                    $this->container->getBlobStorage()->deleteBlobRecord($person->getPictureBlob());
                }
                $person->setPictureBlob(null);

                if ($this->in->getBool('disable_picture')) {
                    $person->setDisablePicture(true);
                }

                $this->em->persist($person);
                break;

            case 'set-picture':
                $person->setDisablePicture(false);
                $blob = $this->em->find(Blob::class, $this->in->getUint('blob_id'));
                if ($blob) {
                    $blobCurr = $person->getPictureBlob();
                    if ($blobCurr && $blobCurr->getId() !== $blob->getId()) {
                        $this->container->getBlobStorage()->deleteBlobRecord($blobCurr);
                    }
                    $person->setPictureBlob($blob);
                    $this->em->persist($person);
                }
                break;

            case 'set-summary':
                $person->summary = $this->in->getString('summary');
                $this->em->persist($person);
                break;

            case 'set-organization':

                $name = $this->in->getString('name');
                $id   = $this->in->getUint('id');
                $org  = null;

                if ($id) {
                    $org = $this->em->getRepository(Organization::class)->find($id);
                } elseif ($name) {
                    $org = $this->em->getRepository(Organization::class)->getByName($name);

                    if (!$org) {
                        $org       = new Organization();
                        $org->name = $name;

                        $this->em->persist($org);
                        $this->em->flush();
                    }
                }

                $old_org = $person->organization;

                if ($org) {
                    $add = 0;
                    if ($org && $person->organization && $person->organization->getId() != $org->getId()) {
                        $add = 1;
                    }

                    $person->setOrganization($org, $this->in->getString('position'), $this->in->getBool('manager'));

                    // Org stuff
                    $org_members_count = null;
                    $org_contact_data  = null;
                    if ($person->organization) {
                        $org_members_count = $this->em->getRepository(Organization::class)->countMembersFor($person->organization) + $add;

                        $org_contact_data = [];
                        foreach ($person->organization->contact_data as $cd) {
                            if (!isset($contact_data[$cd->contact_type])) {
                                $contact_data[$cd->contact_type] = [];
                            }
                            $org_contact_data[$cd->contact_type][] = $cd->getTemplateVars();
                        }
                    }

                    // Regenerate the HTML block
                    $html = $this->renderView('AgentBundle:Person:view-org-info.html.twig', [
                        'org'               => $org,
                        'person'            => $person,
                        'org_members_count' => $org_members_count,
                        'org_contact_data'  => $org_contact_data,
                    ]);

                    $data['organization_id'] = $org->id;
                    $data['html']            = $html;
                } else {
                    $person->setOrganization(null);

                    $data['organization_id'] = 0;
                    $data['html']            = '';
                }

                $conn = $this->em->getConnection();
                foreach (['tickets', 'tickets_search_active'] as $table) {
                    $conn->executeQuery(
                        sprintf(
                            'update %s set organization_id = %s where person_id = %d and organization_id %s',
                            $table,
                            $person->organization ? $person->organization['id'] : 'null',
                            $person['id'],
                            $old_org ? ' = '.$old_org['id'] : 'is null'
                        )
                    );
                }

                break;

            case 'set-usergroups':
                $usergroup_ids = $this->in->getCleanValueArray('usergroup_ids', 'uint', 'discard');
                $usergroup_ids = Arrays::removeFalsey($usergroup_ids);

                $usergroups = $usergroup_ids
                    ? $this->em->getRepository(Usergroup::class)->findBy(['id' => $usergroup_ids])
                    : [];

                foreach ($person->usergroups as $personGroup) {
                    if ($personGroup->is_agent_group) {
                        continue;
                    } // dont touch agent groups
                    if (false === in_array($personGroup, $usergroups, true)) {
                        $person->removeUsergroup($personGroup);
                    }
                }

                foreach ($usergroups as $personGroup) {
                    if ($personGroup->is_agent_group) {
                        continue;
                    } // dont touch agent groups
                    $person->addUsergroup($personGroup);
                }

                $this->em->flush();
                break;

            case 'set-brands':
                $brand_ids = $this->in->getCleanValueArray('brand_ids', 'uint', 'discard');
                $brand_ids = Arrays::removeFalsey($brand_ids);

                $brands = $brand_ids
                    ? $this->em->getRepository(Brand::class)->findBy(['id' => $brand_ids])
                    : [];

                foreach ($person->brands as $personBrand) {
                    if (false === in_array($personBrand, $brands, true)) {
                        $person->removeBrand($personBrand);
                    }
                }

                foreach ($brands as $brand) {
                    $person->addBrand($brand);
                }

                $this->em->flush();
                break;

            case 'remove-usersource':

                $userSourceId = $this->in->getUint('usersource_id');
                foreach ($person->getUsersourceAssoc() as $assoc) {
                    if ($assoc->getUsersource()->getId() === $userSourceId) {
                        $this->em->remove($assoc);
                        $person->getUsersourceAssoc()->removeElement($assoc);
                    }
                }

                $this->em->flush();
                break;

            case 'remove-file':
                $file = $this->em->find(PersonFile::class, $this->in->getUint('file_id'));
                if ($file && $file->person && $file->person->id == $person->id) {
                    $this->em->remove($file);
                    $data['removed_file_id'] = $file['id'];
                }
                break;

            case 'password':
                if (!$this->person->hasPerm('agent_people.reset_password')) {
                    throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
                }
                if ($this->in->getString('password')) {
                    $person->setPassword($this->in->getString('password'));
                    $this->em->persist($person);

                    $this->db->delete('sessions', ['person_id' => $person->id]);

                    $email = $person->getPrimaryEmailAddress();

                    if ($email) {
                        if ($this->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                            $viewModel = $this->get('email.user_viewmodel_factory')
                                ->createAgentChangedPasswordModel($person->getPlaintextPassword());
                            $this->get('email.email_sender')
                                ->send($viewModel, ['to' => $person]);
                        } else {
                            $message = $this->container->getMailer()->createMessage();
                            $message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
                            $message->setTemplate('DeskPRO:emails_user:agent-changed-password.html.twig', [
                                'person' => $person,
                            ]);

                            $this->container->getTranslator()->setTemporaryLanguage($person->getLanguage(), function () use ($message) {
                                $message->prepare();
                            });

                            $this->container->getMailer()->send($message);
                        }
                    }
                }
                break;
                        case 'upload-vcard':
                                $blobId = $this->in->getUint('blob_id');

                                $blob = $this->em->getRepository(Blob::class)->find($blobId);

                                $content = $this->container->getBlobStorage()->copyBlobRecordToString($blob);

                                $vCardReader = new \Application\DeskPRO\Reader\VCard($this->em);

                                $vCardReader->applyToPerson($content, $person);

                                break;

            default:
                return $this->createJsonResponse(['error' => true, 'message' => 'Unknown action']);
                break;
        }

        $this->em->persist($person);
        $this->em->flush();

        $this->db->executeUpdate('
            UPDATE people
            SET
                organization_id = ?, organization_position = ?, organization_manager = ?
            WHERE id = ?
        ', [
            $person->getOrganizationId() ?: null,
            $person->organization_position ?: '',
            $person->organization_manager ?: 0,
            $person->getId(),
        ]);

        return $this->createJsonResponse($data);
    }

    public function ajaxSaveCustomFieldsAction(Request $request, $person_id)
    {
        $person = $this->getPersonOr404($person_id);

        if (!$this->person->hasPerm('agent_people.edit') || !$this->isPersonEditable($person)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $timezone_options = \DateTimeZone::listIdentifiers();

        $timezone = $this->in->getString('timezone');
        if (!$timezone || !in_array($timezone, $timezone_options)) {
            $timezone = null;
        }

        $language = $this->in->getUint('language');
        if ($language) {
            $language = $this->container->getDataService('Language')->get($language);
        } else {
            $language = null;
        }

        /** @var \Application\DeskPRO\CustomFields\PersonFieldManager $field_manager */
        $field_manager = $this->container->getPersonFieldManager();
        $custom_fields = !empty($_POST['custom_fields']) ? $_POST['custom_fields'] : [];

        $invalid_custom_fields = [];
        $is_valid              = true;
        foreach ($field_manager->getDefinedFields() as $field) {
            $errors = $field->getHandler()->validateFormData($custom_fields, HandlerAbstract::CONTEXT_AGENT);
            foreach ($errors as $code) {
                $invalid_custom_fields['field_'.$field->getId()] = preg_replace('#^(.*?)\.#', '', $code);
                $is_valid                                        = false;
            }
        }
        if (!$is_valid) {
            return $this->createJsonResponse([
                'error'                 => true,
                'invalid_custom_fields' => $invalid_custom_fields,
            ]);
        }

        // specific user custom fields definitions
        $manager = $this->container->getCustomFieldManager();
        $form    = $manager->createDefinitionsFormForContext($person);
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

        $field_manager->saveFormToObject($custom_fields, $person);

        if ($timezone) {
            $person->timezone = $timezone;
        }

        $person->language = $language;

        $this->em->flush();

        $custom_fields = $field_manager->getDisplayArrayForObject($person);

        return $this->createJsonResponse([
            'success' => true,
            'tpl'     => $this->renderView('AgentBundle:Person:view-customfields-rendered-rows.html.twig', [
                'timezone_options'          => $timezone_options,
                'person'                    => $person,
                'custom_fields'             => $custom_fields,
                'custom_fields_definitions' => $form->createView(),
            ]),
        ]);
    }

    public function changePictureOverlayAction($person_id)
    {
        $person = $this->getPersonOr404($person_id);

        return $this->render('AgentBundle:Person:change-person-picture.html.twig', [
            'person' => $person,
        ]);
    }

    public function uploadVcardOverlayAction($person_id)
    {
        $person = $this->getPersonOr404($person_id);

        return $this->render('AgentBundle:Person:upload-vcard-overlay.html.twig', [
            'person' => $person,
        ]);
    }

    //###########################################################################
    // unban-email
    //###########################################################################

    public function unbanEmailAction($person_id, $email_id)
    {
        $person = $this->getPersonOr404($person_id);

        if (!$this->person->hasPerm('agent_people.edit') || !$this->isPersonEditable($person)) {
            throw new NotFoundHttpException();
        }

        $email = $person->getEmailId($email_id);

        if (!$email) {
            throw $this->createNotFoundException();
        }

        $banned_pattern = null;
        if (App::getOrm()->getRepository(BanEmail::class)->isEmailBanned($email->email, $banned_pattern)) {
            App::getDb()->delete('ban_emails', ['banned_email' => $banned_pattern]);
        }

        return $this->createJsonResponse([
            'success' => true,
        ]);
    }

    //###########################################################################
    // save-contact-data
    //###########################################################################

    public function saveContactDataAction(Request $request, $person_id)
    {
        $person = $this->getPersonOr404($person_id);

        if (!$this->person->hasPerm('agent_people.edit') || !$this->isPersonEditable($person)) {
            throw new NotFoundHttpException();
        }

        $this->em->beginTransaction();

        $errors = [];

        $changed_primary_email = false;

        $contact_data_array = [];
        foreach ($person->contact_data as $cd) {
            if (!isset($contact_data_array[$cd->contact_type])) {
                $contact_data_array[$cd->contact_type] = [];
            }
            $contact_data_array[$cd->contact_type][$cd->getId()] = $cd->getTemplateVars();
        }
        $added = [];

        $phones_form = $this->createForm('collection', $person->phone_numbers, [
            'type'         => new PhoneNumberType(),
            'allow_add'    => true,
            'allow_delete' => true,
            'options'      => [
                'label'            => false,
                'show_phone_label' => true,
            ],
        ]);

        try {
            if ($this->person->hasPerm('agent_people.manage_emails')) {
                // Editing emails
                $email_comments = $this->in->getCleanValueArray('emails_comment', 'string', 'uint');

                // Setting comment
                foreach ($email_comments as $email_id => $comment) {
                    if ($email = $person->getEmailId($email_id)) {
                        $email->comment = $comment;
                        $this->em->persist($email);
                    }
                }

                // Adding emails
                $email_comments = $this->in->getCleanValueArray('new_emails_comment', 'string', 'uint');
                foreach ($this->in->getCleanValueArray('new_emails', 'string', 'discard') as $k => $email) {
                    if (!\Orb\Validator\StringEmail::isValueValid($email)) {
                        $errors[] = "\"$email\" was not saved because it is an invalid email address";
                        continue;
                    }

                    $account_manager = App::$container->getEmailAccountManager();
                    if ($account_manager->findAccountForEmailAddress($email)) {
                        $errors[] = "\"$email\" was not saved because it belongs to a ticket account";
                        continue;
                    }

                    $check = $this->em->getRepository(PersonEmail::class)->getEmail($email);
                    if ($check) {
                        if ($check->person->id == $person->id) {
                            // silent discard
                        } else {
                            $errors[] = "\"$email\" was not saved because it is already added to another user";
                        }
                        continue;
                    }

                    $email_rec          = $person->addEmailAddressString($email);
                    $email_rec->comment = isset($email_comments[$k]) ? $email_comments[$k] : '';
                    $this->em->persist($email_rec);
                }

                // Removing emails
                foreach ($this->in->getCleanValueArray('remove_emails', 'uint') as $email_id) {
                    $email_rec = $person->getEmailId($email_id);
                    if ($email_rec) {
                        if (count($person->emails) == 1) {
                            $errors[] = "You cannot remove the users only email address ({$email_rec->email})";
                            continue;
                        }

                        if ($person->primary_email && $person->primary_email->id == $email_id) {
                            $changed_primary_email = true;
                            $person->primary_email = null;
                        }

                        $this->em->remove($email_rec);
                        $person->removeEmailAddressId($email_id);
                    }
                }

                if ($changed_primary_email && count($person->emails)) {
                    foreach ($person->emails as $e) {
                        $person->primary_email = $e;
                        break;
                    }
                }
            } // email perm

            // Adding contact data
            foreach ($this->in->getCleanValueArray('new_contact_data') as $type => $inputs) {
                foreach ($inputs as $input) {
                    $contact_data               = new PersonContactData();
                    $contact_data->contact_type = $type;
                    $contact_data->applyFormData($input);
                    $person->addContactData($contact_data);

                    $added[] = $contact_data;
                }
            }

            // Editing values
            foreach ($this->in->getCleanValueArray('contact_data') as $id => $input) {
                if (!isset($person->contact_data[$id])) {
                    continue;
                }

                $person->contact_data[$id]->applyFormData($input);
            }

            // Removing values
            foreach ($this->in->getCleanValueArray('remove_contact_data', 'uint') as $id) {
                if ($cd = $person->contact_data->get($id)) {
                    $person->removeContactData($cd);
                    // todo: should be removed implicitly
                    $this->em->remove($cd);

                    if (isset($contact_data_array[$cd->contact_type][$cd->id])) {
                        unset($contact_data_array[$cd->contact_type][$cd->id]);
                    }
                }
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        // to handle empty form submission
        if (!$request->get('collection')) {
            $request->request->set('collection', []);
        }
        $phones_form->handleRequest($request);
        if ($phones_form->isValid()) {
            foreach ($phones_form->getData() as $phone) {
                if ($phone->person) {
                    continue;
                }
                $phone->person = $person;
                $this->em->persist($phone);
            }
            $this->em->flush();
        } else {
            foreach ($phones_form->getErrors(true, true) as $error) {
                /* @var $error FormError */
                $errors[] = $error->getMessage();
            }
        }

        // Reset display array
        $contact_data_array = [
            'phone_numbers' => $phones_form->createView(),
        ];
        foreach ($person->contact_data as $cd) {
            if (!isset($contact_data_array[$cd->contact_type])) {
                $contact_data_array[$cd->contact_type] = [];
            }
            $contact_data_array[$cd->contact_type][$cd->getId()] = $cd->getTemplateVars();
        }

        foreach ($added as $cd) {
            if (!isset($contact_data_array[$cd->contact_type])) {
                $contact_data_array[$cd->contact_type] = [];
            }
            $contact_data_array[$cd->contact_type][$cd->getId()] = $cd->getTemplateVars();
        }

        $is_editable = $this->isPersonEditable($person);
        $perms       = [
            'edit'           => $is_editable && $this->person->hasPerm('agent_people.edit'),
            'delete'         => $is_editable && $this->person->hasPerm('agent_people.delete'),
            'manage_emails'  => $is_editable && $this->person->hasPerm('agent_people.manage_emails'),
            'reset_password' => $is_editable && $this->person->hasPerm('agent_people.reset_password'),
            'notes'          => $is_editable && $this->person->hasPerm('agent_people.notes'),
            'org_create'     => $is_editable && $this->person->hasPerm('agent_org.create'),
        ];

        $display_html = $this->renderView('AgentBundle:Person:view-contact-display.html.twig', [
            'person'       => $person,
            'contact_data' => $contact_data_array,
            'perms'        => $perms,
        ]);
        $editor_overlay_html = $this->renderView('AgentBundle:Person:contact-overlay.html.twig', [
            'person'       => $person,
            'contact_data' => $contact_data_array,
            'perms'        => $perms,
        ]);

        return $this->createJsonResponse([
            'success'               => 1,
            'display_html'          => $display_html,
            'editor_overlay_html'   => $editor_overlay_html,
            'errors'                => $errors ? $errors : false,
            'primary_email_address' => $person->getPrimaryEmailAddress(),
            'changed_primary_email' => $changed_primary_email,
        ]);
    }

    //###########################################################################
    // /agent/people/:person_id/ajax-save-organization        agent_people_ajaxsave_organization
    //###########################################################################

    public function ajaxSaveOrganizationAction($person_id)
    {
        if (!$this->person->hasPerm('agent_people.manage_emails')) {
            throw new NotFoundHttpException();
        }

        $person = $this->getPersonOr404($person_id);

        $org_id = $this->in->getUint('organization_id');
        if (!$org_id) {
            $person['organization_id']       = 0;
            $person['organization']          = null;
            $person['organization_position'] = '';

            $em = App::getOrm();
            $em->persist($person);
            $em->flush();

            return $this->createJsonResponse([
                'success'               => true,
                'person_id'             => $person['id'],
                'organization_name'     => '',
                'organization_position' => '',
            ]);
        }

        $org = Organization::getRepository()->find($org_id);

        $person['organization']          = $org;
        $person['organization_position'] = $this->in->getString('organization_position');

        $em = App::getOrm();
        $em->persist($person);
        $em->flush();

        return $this->createJsonResponse([
            'success'               => true,
            'person_id'             => $person['id'],
            'organization_name'     => $org['name'],
            'organization_position' => $person['organization_position'],
        ]);
    }

    //###########################################################################
    // /agent/people/:person_id/ajax-save-note           agent_people_ajaxsave_note
    //###########################################################################

    public function ajaxSaveNoteAction($person_id)
    {
        if (!$this->person->hasPerm('agent_people.notes')) {
            throw new NotFoundHttpException();
        }

        $person = $this->getPersonOr404($person_id);

        $note_txt = $this->in->getString('note');

        if (!$note_txt) {
            return $this->createJsonResponse([
                'error'      => true,
                'error_code' => 'no_message',
                'person_id'  => $person->id,
            ]);
        }

        $em = App::getOrm();
        $em->beginTransaction();

        $note           = new PersonNote();
        $note['agent']  = $this->person;
        $note['person'] = $person;
        $note['note']   = $note_txt;
        $person->addNote($note);
        $em->persist($note);

        $em->flush();
        $em->commit();

        return $this->createJsonResponse([
            'success'      => true,
            'person_id'    => $person['id'],
            'note_li_html' => $this->renderView('AgentBundle:Person:note-li.html.twig', ['note' => $note]),
        ]);
    }

    /**
     * @param $note_id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteNoteAction($note_id)
    {
        if (!$this->person->hasPerm('agent_people.notes')) {
            throw new AccessDeniedException();
        }

        if (!$note = $this->em->find(PersonNote::class, $note_id)) {
            throw new NotFoundHttpException();
        }

        $this->em->remove($note);
        $this->em->flush();

        return $this->createJsonResponse(['success' => true]);
    }

    //###########################################################################
    // /agent/people/:person_id/ajax-save-note           agent_people_ajaxsave_note
    //###########################################################################

    public function ajaxSaveFileAction($person_id)
    {
        if (!$this->person->hasPerm('agent_people.notes')) {
            throw new NotFoundHttpException();
        }

        $person = $this->getPersonOr404($person_id);

        $note_txt = $this->in->getString('note');

        if ($this->in->getUint('file_id')) {
            $file = $this->em->find(PersonFile::class, $this->in->getUint('file_id'));
        } else {
            $blob = $this->em->find(Blob::class, $this->in->getUint('blob_id'));

            if (!$blob) {
                return $this->createJsonResponse([
                    'error'      => true,
                    'error_code' => 'invalid_blob',
                    'person_id'  => $person->id,
                ]);
            }

            $file = new PersonFile();

            $file['agent']  = $this->person;
            $file['person'] = $person;
            $file['blob']   = $blob;
        }

        $file['note'] = $note_txt;

        $em = $this->em;

        $em->beginTransaction();

        $em->persist($file);

        $em->flush();
        $em->commit();

        return $this->createJsonResponse([
            'success'   => true,
            'person_id' => $person['id'],
            'html'      => $this->renderView('AgentBundle:Person:file-row.html.twig', ['file' => $file]),
        ]);
    }

    //###########################################################################
    // ajax-save-labels
    //###########################################################################

    public function ajaxSaveLabelsAction($person_id)
    {
        $person = $this->getPersonOr404($person_id);

        if (!$this->person->hasPerm('agent_people.edit') || !$this->isPersonEditable($person)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

        $person->getLabelManager()->setLabelsArray($labels);

        $this->em->persist($person);
        $this->em->flush();

        return $this->createJsonResponse(['success' => 1]);
    }

    //###########################################################################
    // merge
    //###########################################################################

    public function mergeOverlayAction($person_id, $other_person_id = 0)
    {
        $person = $this->getPersonOr404($person_id);

        $field_manager        = $this->container->getSystemService('person_fields_manager');
        $person_custom_fields = $field_manager->getDisplayArrayForObject($person);

        if ($other_person_id && $other_person_id != $person_id) {
            $other_person        = $this->getPersonOr404($other_person_id);
            $other_custom_fields = $field_manager->getDisplayArrayForObject($other_person);

            if (!$person->isAgent() && $other_person->isAgent()) {
                $tmp                  = $person;
                $tmpFields            = $person_custom_fields;
                $person               = $other_person;
                $person_custom_fields = $other_custom_fields;
                $other_person         = $tmp;
                $other_custom_fields  = $tmpFields;
            }
        } else {
            $other_person        = false;
            $other_custom_fields = false;
        }

        return $this->render('AgentBundle:Person:merge-overlay.html.twig', [
            'person'               => $person,
            'person_custom_fields' => $person_custom_fields,
            'other_person'         => $other_person,
            'other_custom_fields'  => $other_custom_fields,
        ]);
    }

    public function mergeAction($person_id, $other_person_id)
    {
        $person      = $this->getPersonOr404($person_id);
        $otherPerson = $this->getPersonOr404($other_person_id);

        if (!$person || !$otherPerson) {
            throw new NotFoundHttpException();
        }

        if (!$this->person->hasPerm('agent_people.merge') || !$this->isPersonEditable($person)) {
            return $this->createJsonResponse(['success' => false]);
        }

        if (!$this->person->hasPerm('agent_people.merge') || !$this->isPersonEditable($otherPerson)) {
            return $this->createJsonResponse(['success' => false]);
        }

        $oldPersonId = $otherPerson['id'];

        $logEvent = new LogEvent(new UserMerged($person, $otherPerson), $this->person);
        $merge    = new PersonMerge($this->person, $person, $otherPerson);
        $merge->merge();
        $this->container->get('deskpro.logger.changelog')->info($logEvent);

        return $this->createJsonResponse([
            'success' => true,
            'id'      => $person['id'],
            'old_id'  => $oldPersonId,
        ]);
    }

    //###########################################################################
    // delete
    //###########################################################################

    public function deletePersonAction($person_id, $security_token)
    {
        $person = $this->getPersonOr404($person_id);

        if ($person->is_agent || !$this->person->hasPerm('agent_people.delete') || !$this->isPersonEditable($person)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        if (!$this->session->getEntity()->checkSecurityToken('delete_person', $security_token)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $this->em->beginTransaction();
        try {
            $this->em->getConnection()->executeQuery(
                    'REPLACE INTO persons_deleted (person_id, by_person_id, reason, date_created)
                     VALUES (:person, :by_person, :reason, :date)
                ', [
                    'person'    => $person_id,
                    'by_person' => $this->person->id,
                    'reason'    => $this->in->getString('reason'),
                    'date'      => date('Y-m-d H:i:s'),
                ]
            );

            if ($this->in->getBool('ban')) {
                foreach ($person->emails as $email) {
                    $email_addy = strtolower($email->email);
                    App::getDb()->replace('ban_emails', [
                        'banned_email' => $email_addy,
                        'is_pattern'   => 0,
                    ]);
                }
            }

            /** @var PersonEditManager $edit_manager */
            $edit_manager = $this->container->getSystemService('person_edit_manager');
            $edit_manager->setPersonContext($this->person);
            $edit_manager->deleteUser($person);

            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        return $this->createJsonResponse(['success' => true]);
    }

    //###########################################################################
    // login-as
    //###########################################################################

    public function loginAsAction($person_id)
    {
        $person = $this->getPersonOr404($person_id);

        if (!$this->person->hasPerm('agent_people.login_as') || !$person || $person->is_agent) {
            throw $this->createNotFoundException();
        }

        foreach (['dpsid'] as $cookie_name) {
            if (!empty($_COOKIE[$cookie_name])) {
                $sess2 = $this->em->getRepository(Session::class)->getSessionFromCode($_COOKIE[$cookie_name]);
                if ($sess2) {
                    $this->em->remove($sess2);
                    $this->em->flush();
                }
            }

            $cookie = Cookie::makeDeleteCookie($cookie_name);
            $cookie->send();
        }

        $tmp = TmpData::create('agent_user_login', [
            'agent_id'  => $this->person->getId(),
            'person_id' => $person->id,
        ], '+5 minutes');
        $this->em->persist($tmp);
        $this->em->flush();

        return $this->redirectRoute('portal_agent_login', ['code' => $tmp->getCode()]);
    }

    //###########################################################################
    // New person
    //###########################################################################

    public function newPersonAction()
    {
        if (!$this->person->hasPerm('agent_people.create')) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $state = $this->em->getRepository(PersonPref::class)->getPrefForPersonId('agent.ui.state.newperson', $this->person->id);

        //------------------------------
        // Custom fields
        //------------------------------

        // We use this fieldgroup so the form names are part of custom_fields array: custom_fields[field_1] etc
        // So dont remove it even though it looks like it's not used! :-)
        $custom_fields_form = $this->get('form.factory')->createNamedBuilder('newperson_custom_fields', 'form');
        $field_manager      = $this->container->getPersonFieldManager();
        $custom_fields      = $field_manager->getDisplayArrayForObject(new Person(), $custom_fields_form);

        $manager                   = $this->container->getCustomFieldManager();
        $custom_fields_definitions = $manager->createDefinitionsFormForContext(new Person());

        $timezone_options = \DateTimeZone::listIdentifiers();
        $usergroup_names  = $this->em->getRepository(Usergroup::class)->getUsergroupNames();
        $brands           = $this->em->getRepository(Brand::class)->findAll();
        $defaultBrandId   = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
        if (!$defaultBrandId && count($brands)) {
            $defaultBrandId = $brands[0]->getId();
        }

        return $this->render('AgentBundle:Person:newperson.html.twig', [
            'state'            => $state,
            'custom_fields'    => $custom_fields,
            'timezone_options' => $timezone_options,
            'usergroup_names'  => $usergroup_names,
            'brands'           => $brands,
            'default_brand_id' => $defaultBrandId,

            'custom_fields_definitions' => $custom_fields_definitions->createView(),
        ]);
    }

    public function newPersonSaveAction(Request $request)
    {
        if (!$this->person->hasPerm('agent_people.create')) {
            throw new NotFoundHttpException();
        }

        $newperson = new NewPersonModel($this->person, $this->get('doctrine.orm.default_entity_manager'));

        $isVCard = $this->in->getBool('isVCard');

        if ($this->in->getString('newperson.set_password')) {
            $password = 'generate' === $this->in->getString('newperson.set_password_radio') || !$this->in->getString('newperson.new_password')
                ? DpStrings::random(8)
                : $this->in->getString('newperson.new_password');
            $newperson->password = $password;
        }

        if ($isVCard) {
            $blobId = $this->in->getBool('blobId');
            if (!$blobId) {
                throw new \Exception('Invalid Blob ID');
            }

            $blob    = $this->em->getRepository(Blob::class)->find($blobId);
            $content = $this->container->getBlobStorage()->copyBlobRecordToString($blob);

            $vCardReader = new VCard($this->em);

            $fields = $vCardReader->parseVCard($content);

            if (!isset($fields['emails']) || !count($fields['emails'])) {
                return $this->createJsonResponse([
                    'success'        => false,
                    'error_messages' => ['No valid email was found in the vCard'],
                ]);
            }

            $new_email = $fields['emails'][0];
        } else {
            $new_email = $this->in->getString('newperson.email');
        }

        $account_manager = App::$container->getEmailAccountManager();

        // Check for dupe email address
        if (!$new_email || !StringEmail::isValueValid($new_email)) {
            return $this->createJsonResponse([
                'success'        => false,
                'error_messages' => ['Please enter a valid email address'],
            ]);
        } elseif ($account_manager->findAccountForEmailAddress($new_email)) {
            return $this->createJsonResponse([
                'success'        => false,
                'error_messages' => ['That email address is in use by a ticket account'],
            ]);
        } else {
            /** @var PersonRepository $personRepository */
            $personRepository = $this->em->getRepository(Person::class);
            $check_exists     = $personRepository->findOneByEmail($new_email);
            if ($check_exists) {
                return $this->createJsonResponse([
                    'success'        => false,
                    'error_messages' => ['The email address you entered already belongs to an existing user'],
                ]);
            }
        }

        if ($language = $this->in->getUInt('newperson.language')) {
            /** @var LanguageDataService $languageDataService */
            $languageDataService = $this->container->getDataService('Language');
            $newperson->language = $languageDataService->get($language);
        }

        if ($isVCard) {
            $newperson->save();

            $person = $newperson->getPerson();

            $vCardReader->applyToPerson($content, $person);

            $this->em->getRepository(PersonPref::class)->deletePrefForPersonId(
                'agent.ui.state.newperson',
                $this->person->id
            );

            $this->em->flush();

            if ($this->in->getString('newperson.send_welcome_email')) {
                if ($this->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                    $viewModel = $this->get('email.user_viewmodel_factory')
                        ->createRegisterWelcomeByAgentModel($person->getPlaintextPassword());
                    $this->get('email.email_sender')
                        ->send($viewModel, ['to' => $person]);
                } else {
                    /** @var Mailer $mailer */
                    $mailer  = $this->get('mailer');
                    $message = $mailer->createMessage();
                    $message->setToPerson($person);
                    $message->setTemplate(
                        'DeskPRO:emails_user:register-welcome-byagent.html.twig',
                        [
                            'person' => $person,
                        ]
                    );
                    $mailer->send($message);
                }
            }

            return $this->createJsonResponse(
                [
                    'success'   => true,
                    'person_id' => $person['id'],
                ]
            );
        }

        $formType = new NewPersonType();
        $form     = $this->get('form.factory')->create($formType, $newperson);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $form->isValid();

            $newperson->setCustomFieldForm($_POST);

            /** @var PersonFieldManager $fieldsManager */
            $fieldsManager = App::getSystemService('PersonFieldsManager');
            $personFields  = $fieldsManager->getDefinedFields();

            $fieldErrors = [];
            foreach ($personFields as $field) {
                $errors = $field->getHandler()->validateFormData(
                    $newperson->custom_fields ?: [],
                    HandlerAbstract::CONTEXT_AGENT
                );

                foreach ($errors as $code) {
                    $title = $field->getTitle();
                    $str   = "Please correct $title";
                    $code  = str_replace('field_'.$field->getId().'.', '', $code);
                    switch ($code) {
                        case 'required':
                            $str = "$title is required";
                            break;
                        case 'min_length':
                            $str = "$title is too short";
                            break;
                        case 'max_length':
                            $str = "$title is too long";
                            break;
                        case 'regex':
                            $str = "$title is invalid";
                            break;
                    }

                    $fieldErrors[] = $str;
                }
            }

            if (count($fieldErrors)) {
                return $this->createJsonResponse([
                    'success'        => false,
                    'error_messages' => $fieldErrors,
                ]);
            }

            $newperson->save();
            $person = $newperson->getPerson();

            $manager                   = $this->container->getCustomFieldManager();
            $custom_fields_definitions = $manager->createDefinitionsFormForContext($person);
            // fix: jquery removes empty arrays from post request
            if (!$request->request->has($custom_fields_definitions->getName())) {
                $request->request->set($custom_fields_definitions->getName(), []);
            }
            if ($custom_fields_definitions->handleRequest($request)->isValid()) {
                $manager->flush($custom_fields_definitions);
            }

            $this->em->getRepository(PersonPref::class)->deletePrefForPersonId('agent.ui.state.newperson', $this->person->id);

            $this->get('event_dispatcher')->dispatch(PersonCreatedEvent::EVENT_NAME, new PersonCreatedEvent($person));

            if ($this->in->getString('newperson.send_welcome_email')) {
                $trans = $this->container->getTranslator();
                $trans->setPersonContext($newperson->getPerson());

                if ($this->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                    $viewModel = $this->get('email.user_viewmodel_factory')
                        ->createRegisterWelcomeByAgentModel($person->getPlaintextPassword());
                    $this->get('email.email_sender')
                        ->send($viewModel, ['to' => $person]);
                } else {
                    /** @var Mailer $mailer */
                    $mailer  = $this->get('mailer');
                    $message = $mailer->createMessage();
                    $message->setToPerson($person);
                    $message->setTemplate(
                        'DeskPRO:emails_user:register-welcome-byagent.html.twig',
                        [
                            'person' => $person,
                        ]
                    );

                    $mailer->send($message);
                }

                $trans->setPersonContext($this->person);
            }

            return $this->createJsonResponse([
                'success'   => true,
                'person_id' => $person['id'],
            ]);
        } else {
            return $this->createJsonResponse([
                'success' => false,
            ]);
        }
    }

    public function getPersonTicketsAction($person_id)
    {
        $person = $this->getPersonOr404($person_id);

        $sort_by = $this->in->getString('sort_by');

        /** @var Ticket $rep */
        $rep = $this->em->getRepository(Ticket::class);

        $permissionsHelper          = $this->getPerson()->getHelper('AgentPermissions');
        $allowedTicketDepartmentIds = $permissionsHelper->getAllowedDepartments('tickets', false, 'full');

        $person_tickets = $rep->getPersonTickets($person, $this->getPerson(), 250, $sort_by, 'DESC', $allowedTicketDepartmentIds);

        return $this->render('AgentBundle:Person:view-tickets.html.twig', [
            'tickets' => $person_tickets,
        ]);
    }

    public function getPersonChatsAction($person_id)
    {
        $person = $this->getPersonOr404($person_id);

        /** @var ChatConversationRepository $chatConversationRepository */
        $chatConversationRepository = $this->em->getRepository(ChatConversation::class);

        $orderBy  = $this->in->getString('order_by');
        $orderDir = $this->in->getString('order_dir');
        $chats    = $chatConversationRepository->getPastChatsForPerson($person, $this->getPerson(), $orderBy, $orderDir);

        return $this->render('AgentBundle:Person:view-chats.html.twig', [
            'chats' => $chats,
        ]);
    }

    public function isPersonEditable($person)
    {
        if ($this->person->can_admin) {
            return true;
        }

        if ($person->is_agent && $person->getId() != $this->person->getId()) {
            return false;
        }

        return true;
    }

    /**
     * @param int $person_id
     *
     * @return Person
     */
    protected function getPersonOr404($person_id)
    {
        $person = $this->em->find(Person::class, $person_id);

        if (!$person) {
            throw new NotFoundHttpException("There is no person with ID $person_id");
        }

        return $person;
    }

    /**
     * todo: we use this only for agents now.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        /** @var PersonRepository $rep */
        $rep = $this->em->getRepository(Person::class);
        $ret = $rep->getAgentsRaw();

        return $this->createJsonResponse($ret);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listTeamsAction()
    {
        /** @var \Application\DeskPRO\EntityRepository\AgentTeam $rep */
        $rep = $this->em->getRepository(AgentTeam::class);
        $ret = $rep->getTeamsRaw();

        return $this->createJsonResponse($ret);
    }
}
