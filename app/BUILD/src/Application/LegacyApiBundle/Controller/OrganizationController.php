<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Attachments\RestrictionSet;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationContactData;
use Application\DeskPRO\Entity\OrganizationEmailDomain;
use Application\DeskPRO\Entity\OrganizationNote;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonActivity;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketCharge;
use Application\DeskPRO\EntityRepository\ChatConversation as ChatConversationRepository;
use Application\DeskPRO\EntityRepository\Organization as OrganizationRepository;
use Application\DeskPRO\EntityRepository\OrganizationEmailDomain as OrganizationEmailDomainRepository;
use Application\DeskPRO\EntityRepository\OrganizationNote as OrganizationNoteRepository;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\EntityRepository\PersonActivity as PersonActivityRepository;
use Application\DeskPRO\EntityRepository\PersonEmail as PersonEmailRepository;
use Application\DeskPRO\EntityRepository\Ticket as TicketRepository;
use Application\DeskPRO\EntityRepository\TicketCharge as TicketChargeRepository;
use Application\DeskPRO\Searcher\ChatConversationSearch;
use Application\DeskPRO\Searcher\OrganizationSearch;
use Application\DeskPRO\Searcher\PersonSearch;
use Application\DeskPRO\Searcher\TicketSearch;
use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @ApiModes("all")
 */
class OrganizationController extends AbstractController
{
    /**
     * @return Response
     */
    public function searchAction()
    {
        $searchMap = [
            'address'   => OrganizationSearch::TERM_CONTACT_ADDRESS,
            'im'        => OrganizationSearch::TERM_CONTACT_IM,
            'label'     => OrganizationSearch::TERM_LABEL,
            'name'      => OrganizationSearch::TERM_NAME,
            'phone'     => OrganizationSearch::TERM_CONTACT_PHONE,
            'parent_id' => OrganizationSearch::TERM_PARENT_ID,
        ];

        $terms = [];

        foreach ($searchMap as $input => $searchKey) {
            $value = $this->in->getCleanValueArray($input, 'raw', 'discard');
            if ($value) {
                $terms[] = ['type' => $searchKey, 'op' => 'contains', 'options' => $value];
            }
        }

        foreach ($this->container->getSystemService('org_fields_manager')->getFields() as $field) {
            if ($this->in->checkIsset('field.'.$field->getId())) {
                $inVal = $this->in->getString('field.'.$field->getId());
                if ($inVal) {
                    $terms[] = [
                        'type'    => 'org_field['.$field->getId().']',
                        'op'      => 'is',
                        'options' => ['value' => $inVal],
                    ];
                }
            }
        }

        if ($this->in->checkIsset('order')) {
            $orderBy = $this->in->getString('order');
        } else {
            $orderBy = $this->person->getPref('agent.ui.org-filter-order-by.0');
            if (!$orderBy) {
                $orderBy = 'organization.name:asc';
            }
        }

        $extra = [];
        if ($orderBy !== null) {
            $extra['order_by'] = $orderBy;
        }

        $resultCache = $this->getApiSearchResult('organization', $terms, $extra, $this->in->getUInt('cache_id'), new OrganizationSearch());

        $page = $this->in->getUInt('page');
        if (!$page) {
            $page = 1;
        }

        $perPage = Numbers::bound($this->in->getUInt('per_page') ?: 25, 1, 250);

        $personIds = $resultCache->getResults();

        $pageIds = Arrays::getPageChunk($personIds, $page, $perPage);
        $orgs    = App::getEntityRepository('DeskPRO:Organization')->getByIds($pageIds, true);

        return $this->createApiResponse([
            'page'          => $page,
            'per_page'      => $perPage,
            'total'         => count($personIds),
            'cache_id'      => $resultCache->getId(),
            'organizations' => $this->getApiData($orgs),
        ]);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return Response
     */
    public function newOrganizationAction()
    {
        if (!$this->person->hasPerm('agent_org.create')) {
            throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
        }

        $org    = new Organization();
        $errors = [];

        $name = $this->in->getString('name');
        if (!$name) {
            $errors['name'] = ['required_field.name', 'name is empty or missing'];
        }

        $org->setName($name);

        $bulkSet = [
            'summary' => 'String',
        ];
        foreach ($bulkSet as $input => $type) {
            if ($this->in->checkIsset($input)) {
                $org->$input = $this->in->{'get'.$type}($input);
            }
        }

        if ($errors) {
            return $this->createApiMultipleErrorResponse($errors);
        }

        foreach ($this->in->getArrayValue('contact_data') as $contact) {
            $contactType = isset($contact['type']) ? $contact['type'] : false;
            $data        = (isset($contact['data']) && is_array($contact['data'])) ? $contact['data'] : false;

            if (!$contactType || !$data) {
                continue;
            }

            $data['comment'] = isset($contact['comment']) ? $contact['comment'] : '';

            $conctactData = new OrganizationContactData();
            $conctactData->setContactType($contactType);
            try {
                $conctactData->applyFormData($data);
            } catch (\InvalidArgumentException $e) {
                // invalid type
                continue;
            }

            $allEmpty = true;
            for ($i = 1; $i <= 10; ++$i) {
                if ($conctactData->{'field_'.$i}) {
                    $allEmpty = false;
                    break;
                }
            }

            if (!$allEmpty) {
                $org->addContactData($conctactData);
            }
        }

        $this->db->beginTransaction();

        try {
            foreach ($this->in->getCleanValueArray('group_id', 'int') as $ugId) {
                $ug = $this->em->find('DeskPRO:Usergroup', $ugId);
                if ($ug && !$ug->is_agent_group && !$ug->sys_name) {
                    $org->getUsergroups()->add($ug);
                }
            }

            $this->em->persist($org);

            $fieldManager     = $this->container->getSystemService('org_fields_manager');
            $postCustomFields = $this->getCustomFieldInput();
            if (!empty($postCustomFields)) {
                $fieldManager->saveFormToObject($postCustomFields, $org, true);
            }
            $this->em->flush();

            $labels = $this->in->getCleanValueArray('label', 'string', 'discard');
            if ($labels) {
                $org->getLabelManager()->setLabelsArray($labels);
                $this->em->flush();
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        if ($parent = $this->em->find(Organization::class, $this->in->getInt('parent_id') ?: 0)) {
            $org->setParent($parent);
            $this->em->flush();
        }

        return $this->createApiCreateResponse(
            ['id' => $org->getId()],
            $this->generateUrl(
                'api_organizations_organization',
                ['organization_id' => $org->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        return $this->createApiResponse(['organization' => $org->toApiData()]);
    }

    /**
     * @param $organization_id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return Response
     */
    public function postOrganizationAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        $name = $this->in->getString('name');
        if ($name) {
            $org->name = $name;
        }

        $bulkSet = [
            'summary' => 'String',
        ];
        foreach ($bulkSet as $input => $type) {
            if ($this->in->checkIsset($input)) {
                $org->$input = $this->in->{'get'.$type}($input);
            }
        }

        $this->db->beginTransaction();

        try {
            $this->em->persist($org);

            $fieldManager     = $this->container->getSystemService('org_fields_manager');
            $postCustomFields = $this->getCustomFieldInput();
            if (!empty($postCustomFields)) {
                $fieldManager->saveFormToObject($postCustomFields, $org, true);
            }
            $this->em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        if ($parent = $this->em->find('DeskPRO:Organization', $this->in->getInt('parent_id') ?: 0)) {
            $test = $parent;
            while ($test) {
                if ($test === $org) {
                    throw new \Exception(sprintf('You can\'t set organization "%s" as a parent of "%s"', $parent->name, $org->name));
                }
                $test = $test->parent;
            }
            $org->parent = $parent;
        } else {
            $org->parent = null;
        }
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deleteOrganizationAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'delete');

        $editManager = $this->container->getSystemService('org_edit_manager');
        $editManager->deleteOrganization($org);

        return $this->createSuccessResponse();
    }

    /**
     * @param $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationPictureAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $size = $this->in->getUInt('size');
        if (!$size) {
            $size = 80;
        }

        return $this->createApiResponse([
            'has_picture' => $org->hasPicture(),
            'picture_url' => $org->getPictureUrl($size),
            'size'        => $size,
        ]);
    }

    /**
     * @param int $organization_id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return Response
     */
    public function postOrganizationPictureAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        $file   = $this->request->files->get('file');
        $accept = $this->container->getAttachmentAccepter();

        if ($file) {
            $error = $accept->getError($file, 'agent');
            if (!$error) {
                $set = new RestrictionSet();
                $set->setAllowedExts(['gif', 'png', 'jpg', 'jpeg']);
                $accept->addRestrictionSet('only_images', $set);
                $error = $accept->getError($file, 'only_images');
            }
            if ($error) {
                $message = $this->container->getTranslator()->phrase('agent.general.attach_error_'.$error['error_code'], $error);

                return $this->createApiErrorResponse($error['error_code'], $message);
            }

            $blob = $accept->accept($file);
        } else {
            $blobId = $this->in->getUInt('blob_id');
            $blob   = $this->em->find('DeskPRO:Blob', $blobId);
            if (!$blob) {
                return $this->createApiErrorResponse('invalid_argument.blob_id', 'blob_id not found');
            }
        }

        $org->picture_blob = $blob;
        $this->em->persist($org);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deleteOrganizationPictureAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        $org->picture_blob = null;
        $this->em->persist($org);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationActivityStreamAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $page = $this->in->getUInt('page');
        if (!$page) {
            $page = 1;
        }

        $perPage = Numbers::bound($this->in->getUInt('per_page') ?: 25, 1, 250);
        $offset  = $perPage * ($page - 1);

        /** @var PersonActivityRepository $personActivityRepo */
        $personActivityRepo = $this->em->getRepository(PersonActivity::class);
        $activity           = $personActivityRepo->getForOrganization($org, $perPage, $offset);
        $total              = $personActivityRepo->countForOrganization($org);

        return $this->createApiResponse([
            'page'     => $page,
            'per_page' => $perPage,
            'total'    => $total,
            'activity' => $this->getApiData($activity),
        ]);
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationMembersAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $terms = [
            [
                'type'    => PersonSearch::TERM_ORGANIZATION,
                'op'      => 'contains',
                'options' => [$org->id],
            ],
        ];

        if ($this->in->checkIsset('order')) {
            $orderBy = $this->in->getString('order');
        } else {
            $orderBy = 'person.name:asc';
        }

        $extra = [];
        if ($orderBy !== null) {
            $extra['order_by'] = $orderBy;
        }

        $resultCache = $this->getApiSearchResult('person', $terms, $extra, $this->in->getUInt('cache_id'), new PersonSearch());

        $page = $this->in->getUInt('page');
        if (!$page) {
            $page = 1;
        }

        $perPage = Numbers::bound($this->in->getUInt('per_page') ?: 25, 1, 250);

        $personIds = $resultCache->getResults();

        $pageIds = Arrays::getPageChunk($personIds, $page, $perPage);
        /** @var PersonRepository $personRepository */
        $personRepository = App::getEntityRepository('DeskPRO:Person');
        $people           = $personRepository->getByIds($pageIds, true);

        return $this->createApiResponse([
            'page'     => $page,
            'per_page' => $perPage,
            'total'    => count($personIds),
            'cache_id' => $resultCache->getId(),
            'people'   => $this->getApiData($people),
        ]);
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationTicketsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $terms = [
            [
                'type'    => TicketSearch::TERM_ORGANIZATION,
                'op'      => 'contains',
                'options' => [$org->id],
            ],
        ];

        if ($this->in->checkIsset('order')) {
            $orderBy = $this->in->getString('order');
        } else {
            $orderBy = 'ticket.date_created:desc';
        }

        $extra = [];
        if ($orderBy !== null) {
            $extra['order_by'] = $orderBy;
        }

        $resultCache = $this->getApiSearchResult('ticket', $terms, $extra, $this->in->getUInt('cache_id'), new TicketSearch());

        $page = $this->in->getUInt('page');
        if (!$page) {
            $page = 1;
        }

        $perPage = Numbers::bound($this->in->getUInt('per_page') ?: 25, 1, 250);

        $personIds = $resultCache->getResults();

        $pageIds = Arrays::getPageChunk($personIds, $page, $perPage);

        /** @var TicketRepository $ticketRepository */
        $ticketRepository = App::getEntityRepository(Ticket::class);
        $tickets          = $ticketRepository->getByIds($pageIds, true);

        return $this->createApiResponse([
            'page'     => $page,
            'per_page' => $perPage,
            'total'    => count($personIds),
            'cache_id' => $resultCache->getId(),
            'tickets'  => $this->getApiData($tickets),
        ]);
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationChatsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        /** @var PersonRepository $personRepository */
        $personRepository = App::getEntityRepository(Person::class);
        $memberIds        = $personRepository->getOrganizationMemberIds($org);
        if ($memberIds) {
            $terms = [
                [
                    'type'    => ChatConversationSearch::TERM_PERSON_ID,
                    'op'      => 'contains',
                    'options' => $memberIds,
                ],
            ];

            $orderBy = 'chat_conversations.id:desc';

            $extra = [];
            if ($orderBy !== null) {
                $extra['order_by'] = $orderBy;
            }

            $resultCache = $this->getApiSearchResult('chat', $terms, $extra, $this->in->getUInt('cache_id'), new ChatConversationSearch());

            $ids     = $resultCache->getResults();
            $cacheId = $resultCache->getId();
        } else {
            $ids     = [];
            $cacheId = 0;
        }

        $page = $this->in->getUInt('page');
        if (!$page) {
            $page = 1;
        }

        $perPage = Numbers::bound($this->in->getUInt('per_page') ?: 25, 1, 250);

        $pageIds = Arrays::getPageChunk($ids, $page, $perPage);
        /** @var ChatConversationRepository $chatConverstaionRepository */
        $chatConverstaionRepository = App::getEntityRepository(ChatConversation::class);
        $chats                      = $chatConverstaionRepository->getByIds($pageIds, true);

        return $this->createApiResponse([
            'page'     => $page,
            'per_page' => $perPage,
            'total'    => count($ids),
            'cache_id' => $cacheId,
            'chats'    => $this->getApiData($chats),
        ]);
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationNotesAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        /** @var OrganizationNoteRepository $organiztionNoteRepository */
        $organiztionNoteRepository = $this->em->getRepository(OrganizationNote::class);
        $notes                     = $organiztionNoteRepository->getNotesForOrganization($org);

        return $this->createApiResponse(['notes' => $this->getApiData($notes)]);
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postOrganizationNotesAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'note');

        $noteText = $this->in->getString('note');
        if (!$noteText) {
            return $this->createApiErrorResponse('required_field', 'note field is empty or missing');
        }

        $note                 = new OrganizationNote();
        $note['agent']        = $this->person;
        $note['organization'] = $org;
        $note['note']         = $noteText;

        $this->em->persist($note);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['id' => $note->getId()],
            $this->generateUrl(
                'api_organizations_organization_notes_note',
                ['organization_id' => $org->id, 'note_id' => $note->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $organization_id
     * @param int $note_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationNoteAction($organization_id, $note_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $note = $this->em->getRepository(OrganizationNote::class)->find($note_id);
        if (!$note || $note->getOrganization()->getId() != $org->id) {
            throw new NotFoundHttpException();
        }

        return $this->createApiResponse(['note' => $note->toApiData()]);
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationBillingChargesAction($organization_id)
    {
        $organization = $this->_getOrganizationOr404($organization_id);

        $perPage = Numbers::bound($this->in->getUInt('per_page') ?: 25, 1, 250);

        $page = $this->in->getUInt('page');
        if (!$page) {
            $page = 1;
        }

        $offset = ($page - 1) * $perPage;

        /** @var TicketChargeRepository $ticketChargeRepository */
        $ticketChargeRepository = $this->em->getRepository(TicketCharge::class);
        $charges                = $ticketChargeRepository->getChargesForOrganization($organization, $perPage, $offset);
        $chargeTotals           = $ticketChargeRepository->getTotalChargesForOrganization($organization);

        return $this->createApiResponse([
            'total_charge_time'   => $chargeTotals['charge_time'],
            'total_charge_amount' => $chargeTotals['charge'],
            'total'               => $chargeTotals['count'],
            'per_page'            => $perPage,
            'page'                => $page,
            'charges'             => $this->getApiData($charges),
        ]);
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationEmailDomainsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        /** @var OrganizationEmailDomainRepository $organizationEmailDomainRepo */
        $organizationEmailDomainRepo = $this->em->getRepository(OrganizationEmailDomain::class);
        $organizationEmailDomains    = $organizationEmailDomainRepo->getDomainsForOrganization($org);

        /** @var PersonEmailRepository $personEmailRepo */
        $personEmailRepo = $this->em->getRepository(PersonEmail::class);

        $orgCountDomainNonMembers   = $personEmailRepo->countDomainsWithNoCompany($organizationEmailDomains);
        $orgCountDomainTakenMembers = $personEmailRepo->countDomainsWithOtherCompany($organizationEmailDomains, $org);
        $orgCountDomainMembers      = $organizationEmailDomainRepo->countMembersAtDomains($org, $organizationEmailDomains);

        $domains = [];
        foreach ($organizationEmailDomains as $domain) {
            $domains[] = [
                'domain'        => $domain,
                'members'       => isset($orgCountDomainMembers[$domain]) ? $orgCountDomainMembers[$domain] : 0,
                'nonmembers'    => isset($orgCountDomainNonMembers[$domain]) ? $orgCountDomainNonMembers[$domain] : 0,
                'taken_members' => isset($orgCountDomainTakenMembers[$domain]) ? $orgCountDomainTakenMembers[$domain] : 0,
            ];
        }

        return $this->createApiResponse(['domains' => $domains]);
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postOrganizationEmailDomainsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        $domain = $this->in->getString('domain');
        if (!$domain) {
            return $this->createApiErrorResponse('required_field.domain', 'domain is missing');
        }

        $orgDomainManager = $this->container->getSystemService('org_email_domain_manager');

        if ($orgDomainManager->isInUse($domain)) {
            return $this->createApiErrorResponse('invalid_argument.domain', 'domain is in use');
        }

        $domainRec = $orgDomainManager->assignDomain($domain, $org);

        return $this->createApiCreateResponse(
            ['domain' => $domainRec->domain],
            $this->generateUrl(
                'api_organizations_organization_email_domain',
                ['organization_id' => $org->id, 'domain' => $domainRec->domain],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int    $organization_id
     * @param string $domain
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationEmailDomainAction($organization_id, $domain)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        $exists = false;
        foreach ($org->email_domains as $emailDomain) {
            if ($emailDomain->domain == $domain) {
                $exists = true;
                break;
            }
        }

        return $this->createApiResponse(['exists' => $exists]);
    }

    /**
     * @param int    $organization_id
     * @param string $domain
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postOrganizationEmailDomainMoveUsersAction($organization_id, $domain)
    {
        $org       = $this->_getOrganizationOr404($organization_id, 'edit');
        $orgDomain = $this->em->getRepository(OrganizationEmailDomain::class)->find(['organization' => $org, 'domain' => $domain]);

        if ($orgDomain) {
            $orgDomainManager = $this->container->getSystemService('org_email_domain_manager');
            $orgDomainManager->moveNonCompanyUsers($orgDomain);
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int    $organization_id
     * @param string $domain
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postOrganizationEmailDomainMoveTakenUsersAction($organization_id, $domain)
    {
        $org       = $this->_getOrganizationOr404($organization_id, 'edit');
        $orgDomain = $this->em->getRepository(OrganizationEmailDomain::class)->find(['organization' => $org, 'domain' => $domain]);

        if ($orgDomain) {
            $orgDomainManager = $this->container->getSystemService('org_email_domain_manager');
            $orgDomainManager->moveOtherCompanyUsers($orgDomain);
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int    $organization_id
     * @param string $domain
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deleteOrganizationEmailDomainAction($organization_id, $domain)
    {
        $org       = $this->_getOrganizationOr404($organization_id, 'edit');
        $orgDomain = $this->em->getRepository(OrganizationEmailDomain::class)->find(['organization' => $org, 'domain' => $domain]);

        if ($orgDomain) {
            $orgDomainManager = $this->container->getSystemService('org_email_domain_manager');
            $orgDomainManager->unassignDomain($orgDomain, $this->in->getBool('remove_users'));
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationContactDetailsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        return $this->createApiResponse(['details' => $this->getApiData($org->contact_data)]);
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postOrganizationContactDetailsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        $type    = $this->in->getString('type');
        $data    = $this->in->getArrayValue('data');
        $comment = $this->in->getString('comment');

        if (!$type) {
            return $this->createApiErrorResponse('required_field.type', 'type is empty or missing');
        }
        if (!$data) {
            return $this->createApiErrorResponse('required_field.data', 'data is empty or missing');
        }

        $data['comment'] = $comment;

        $conctactData = new OrganizationContactData();
        $conctactData->setContactType($type);
        try {
            $conctactData->applyFormData($data);
        } catch (\InvalidArgumentException $e) {
            return $this->createApiErrorResponse('invalid_argument.type', 'type is invalid');
        }

        $allEmpty = true;
        for ($i = 1; $i <= 10; ++$i) {
            if ($conctactData->{'field_'.$i}) {
                $allEmpty = false;
                break;
            }
        }

        if ($allEmpty) {
            return $this->createApiErrorResponse('invalid_argument.data', 'data contains invalid data');
        }

        $conctactData->setOrganization($org);

        $this->em->persist($conctactData);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['id' => $conctactData->getId()],
            $this->generateUrl(
                'api_organizations_organization_contact_detail',
                ['organization_id' => $org->id, 'contact_id' => $conctactData->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $organization_id
     * @param int $contact_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationContactDetailAction($organization_id, $contact_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        foreach ($org->contact_data as $contact) {
            if ($contact->id == $contact_id) {
                return $this->createApiResponse(['exists' => true]);
            }
        }

        return $this->createApiResponse(['exists' => false]);
    }

    /**
     * @param int $organization_id
     * @param int $contact_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deleteOrganizationContactDetailAction($organization_id, $contact_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        foreach ($org->contact_data as $key => $contact) {
            if ($contact->id == $contact_id) {
                unset($org->contact_data[$key]);
                $this->em->persist($org);
                $this->em->flush();
                break;
            }
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationGroupsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        return $this->createApiResponse(['groups' => $this->getApiData($org->usergroups)]);
    }

    /**
     * @param int $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postOrganizationGroupsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        $groupId = $this->in->getUInt('id');

        $match = $this->db->fetchColumn('
            SELECT id
            FROM usergroups
            WHERE id = ?
                AND sys_name IS NULL
                AND is_agent_group = 0
        ', [$groupId]);
        if (!$match) {
            return $this->createApiErrorResponse('required_field', 'id must be specified as a non-system group');
        }

        $exists = false;
        foreach ($org->usergroups as $group) {
            if ($group->id == $groupId) {
                $exists = true;
            }
        }

        if (!$exists) {
            $this->db->insert('organization2usergroups', [
                'organization_id' => $org->id,
                'usergroup_id'    => $groupId,
            ]);
        }

        return $this->createApiCreateResponse(
            ['id' => $groupId],
            $this->generateUrl(
                'api_organizations_organization_group',
                ['organization_id' => $org->id, 'usergroup_id' => $groupId],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $organization_id
     * @param int $usergroup_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationGroupAction($organization_id, $usergroup_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        foreach ($org->usergroups as $group) {
            if ($group->id == $usergroup_id) {
                return $this->createApiResponse(['exists' => true]);
            }
        }

        return $this->createApiResponse(['exists' => false]);
    }

    /**
     * @param int $organization_id
     * @param int $usergroup_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deleteOrganizationGroupAction($organization_id, $usergroup_id)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        foreach ($org->usergroups as $key => $group) {
            if ($group->id == $usergroup_id) {
                if ($group->is_agent_group) {
                    return $this->createApiErrorResponse('invalid_group', 'Group is an agent group');
                }
                unset($org->usergroups[$key]);
                $this->em->persist($org);
                $this->em->flush();
                break;
            }
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationLabelsAction($organization_id)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        return $this->createApiResponse(['labels' => $this->getApiData($org->labels)]);
    }

    /**
     * @param $organization_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function postOrganizationLabelsAction($organization_id)
    {
        $org   = $this->_getOrganizationOr404($organization_id, 'edit');
        $label = $this->in->getString('label');

        if ($label === '') {
            return $this->createApiErrorResponse('required_field.label', "Field 'label' missing or empty");
        }

        $org->getLabelManager()->addLabel($label);
        $this->em->persist($org);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['label' => $label],
            $this->generateUrl(
                'api_organizations_organization_label',
                ['organization_id' => $org->id, 'label' => $label],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param $organization_id
     * @param $label
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function getOrganizationLabelAction($organization_id, $label)
    {
        $org = $this->_getOrganizationOr404($organization_id);

        if ($org->getLabelManager()->hasLabel($label)) {
            return $this->createApiResponse(['exists' => true]);
        } else {
            return $this->createApiResponse(['exists' => false]);
        }
    }

    /**
     * @param $organization_id
     * @param $label
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deleteOrganizationLabelAction($organization_id, $label)
    {
        $org = $this->_getOrganizationOr404($organization_id, 'edit');

        $org->getLabelManager()->removeLabel($label);
        $this->em->persist($org);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @return Response
     */
    public function getFieldsAction()
    {
        $fieldManager = $this->container->getSystemService('org_fields_manager');
        $fields       = $fieldManager->getFields();

        return $this->createApiResponse(['fields' => $this->getApiData($fields)]);
    }

    /**
     * @return Response
     */
    public function getGroupsAction()
    {
        $groups = $this->em->createQuery('
            SELECT g
            FROM DeskPRO:Usergroup g INDEX BY g.id
            WHERE g.is_agent_group = false AND g.sys_name IS NULL
            ORDER BY g.id
        ')->execute();

        return $this->createApiResponse(['groups' => $this->getApiData($groups)]);
    }

    /**
     * @param int    $id
     * @param string $checkPerm
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     * @throws \Exception
     *
     * @return mixed
     */
    protected function _getOrganizationOr404($id, $checkPerm = '')
    {
        /** @var OrganizationRepository $rep */
        $rep = $this->em->getRepository(Organization::class);
        $org = $rep->findOneById($id);

        if (!$org) {
            throw new NotFoundHttpException("There is no organization with ID $id");
        }

        if ($checkPerm) {
            switch ($checkPerm) {
                case 'edit':
                case 'delete':
                case 'create':
                case 'note':
                    if (!$this->person->hasPerm('agent_org.'.$checkPerm)) {
                        throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
                    }
                    break;

                default:
                    throw new \Exception("Unknown perm type $checkPerm");
            }
        }

        return $org;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function quickSearchAction(Request $request)
    {
        /** @var OrganizationRepository $rep */
        $rep = $this->em->getRepository(Organization::class);
        $res = $rep->search($request->get('query'), $request->get('limit'), false);

        return $this->createApiResponse($res);
    }
}
