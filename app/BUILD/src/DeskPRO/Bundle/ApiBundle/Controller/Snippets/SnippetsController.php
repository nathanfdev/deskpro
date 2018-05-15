<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Snippets;

use Application\DeskPRO\DependencyInjection\SystemServices\LanguageDataService;
use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Entity\SnippetLabel;
use DeskPRO\Bundle\AppBundle\Entity\SnippetTranslation;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Snippets\SnippetMassActionType;
use DeskPRO\Bundle\AppBundle\Form\Type\Snippets\SnippetType;
use DeskPRO\Bundle\AppBundle\Notification\Event\Snippet\SnippetsUpdatedEvent;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class SnippetsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/snippets")
 * @Feature("new_snippets")
 * @ApiDoc(
 *     target="all",
 *     section="Snippets",
 *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\Snippets\Snippet"
 * )
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Snippets\SnippetType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\Snippet",
 *          "person"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {"name"="inline_sideloads", "pattern"="true|false", "description"="sideload entities"},
 *          {"name"="include", "pattern"="\w[,\w]", "description"="entities to sideload (ex: snippet_translation)"},
 *          {"name"="type", "pattern"="\w[,\w]", "description"="type to limit result"},
 *          {"name"="page", "pattern"="\d", "description"="Which page to display", "dataType"="integer"},
 *          {"name"="count", "pattern"="\d", "description"="Resource per page count", "dataType"="integer"},
 *          {"name"="limit", "pattern"="\d", "description"="Max number of resources to return", "dataType"="integer"},
 *          {"name"="ids", "pattern"="[\d,]+", "description"="Comma separated list of IDs", "dataType"="string"},
 *     }
 * )
 */
class SnippetsController extends CrudController
{
    public static $entity    = Snippet::class;
    public static $type      = SnippetType::class;
    public static $listOrder = 'asc';

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $agent = $this->getUser();
        $agent->loadHelper('AgentTeam');

        $qb
            ->andWhere("($alias.person = :person")
            ->orWhere("$alias.isOwnershipGlobal = true)")
            ->setParameter('person', $agent)
        ;

        if ($agent->getAgentTeamIds()) {
            $qb
                ->leftJoin("$alias.ownershipTeams", 't')
                ->orWhere('t.id IN (:teams)')
                ->setParameter('teams', $agent->getAgentTeamIds())
            ;
        }
        $type = $request->get('type');
        if ($type) {
            $qb->andWhere("$alias.types LIKE :type")
                ->setParameter('type', '%'.$type.'%');
        }
    }

    /**
     * @ApiDoc(
     *      description="Create a new resource",
     *      tags={"CRUD"="#ffa500"},
     *      statusCodes={
     *          201="Returned in case of successful resource creation",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        return $this->handleForm($this->instantiateEntity($request), $request);
    }

    /**
     * {@inheritdoc}
     */
    protected function additionalValidation($model, Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::CREATE, new PermissionGroupContext($model));
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'person' => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    protected function persistModel($model, FormInterface $form = null)
    {
        /** @var Snippet $model */
        $model = parent::persistModel($model);
        $this->container
            ->get('event_dispatcher')
            ->dispatch(SnippetsUpdatedEvent::EVENT_NAME, new SnippetsUpdatedEvent(
                $model,
                'update'
            ));

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request, $type = '')
    {
        return $this->findOr404(static::$entity, $id, null, $type);
    }

    /**
     * {@inheritdoc}
     */
    protected function findOr404($class, $id, $message = null, $type = '')
    {
        $agent = $this->getUser();

        if (!$entity = $this->getManager()->getRepository($class)->findSnippetForAgent($agent, $id, $type)) {
            throw $this->createNotFoundException($message ?: "#{$id} Not Found");
        }

        return $entity;
    }

    /**
     * @ApiDoc(
     *      description="Render a snippet for an object",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the snippet",
     *              "dataType"="integer"
     *          },
     *          {
     *              "name"="type",
     *              "requirement"="(ticket|chat)",
     *              "description"="The type of object to render the snippet for",
     *              "dataType"="string"
     *          },
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the object",
     *              "dataType"="integer"
     *          }
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Serializer\Model\Snippets\Snippet",
     *      statusCodes={
     *          200="We will return such status in case we found your entity",
     *          404="Not Found error will returned in case we can't find entity with specified ID"
     *      }
     * )
     * @Rest\Get("/render/{id}/{type}/{objectId}",  requirements={"id"="\d+", "type"="(ticket|chat)", "objectId"="\d+"})
     *
     * @param Request $request
     * @param int     $id
     * @param string  $type
     *
     * @return View
     */
    public function renderSnippetAction(Request $request, $id, $type)
    {
        $snippet = $this->findEntity($id, $request, $type);

        return View::create($this->wrap($snippet));
    }

    /**
     * @ApiDoc(
     *      description="Apply mass actions function",
     *      tags={"CRUD"="#ffa500"},
     *      statusCodes={
     *          201="Returned in case of successful resource creation",
     *          400="We will return this in case your request was malformed",
     *      },
     *     input={
     *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Snippets\SnippetMassActionType"
     *     },
     *     output="array"
     * )
     * @Rest\Post("/mass_actions")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function postMassActionsAction(Request $request)
    {
        $form = $this->createForm(SnippetMassActionType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $action   = $form->get('action')->getData();
        $value    = $form->get('value')->getData();
        $selected = $form->get('selected')->getData();

        /** @var Snippet[] $snippets */
        $snippets  = $this->getRepository(Snippet::class)->findSnippetsForAgent($this->getUser(), $selected);
        $processed = [];

        switch ($action) {
            case 'labels':
                foreach ($snippets as $snippet) {
                    $selectedLabels = [];
                    foreach ($value as $label => $labelValue) {
                        $labelObject = new SnippetLabel($label);
                        if ($labelValue) {
                            $selectedLabels[] = $label;
                            if (!$snippet->hasLabel($labelObject)) {
                                $snippet->addLabel($labelObject);
                                $processed[] = $snippet->getId();
                            }
                        }
                    }
                    foreach ($snippet->getLabels() as $label) {
                        if (!in_array($label->getLabel(), $selectedLabels)) {
                            $snippet->removeLabel($label);
                            $processed[] = $snippet->getId();
                        }
                    }
                    // we need to call this, otherwise labels will not be saved/removed
                    $this->getManager()->persist($snippet);
                }
                break;
            case 'visibility':
                foreach ($snippets as $snippet) {
                    if (!$this->getUser()->hasPerm('agent_snippets.edit_by_others') && $snippet->getPerson() !== $this->getUser()) {
                        continue;
                    }
                    if ($value['isVisibleGlobal']) {
                        if (!$snippet->isVisibleGlobal() || $snippet->hasDepartments()) {
                            $snippet->setIsVisibleGlobal(true);
                            $snippet->clearDepartments();
                            $processed[] = $snippet->getId();
                        }
                    } elseif ($value['selectedDepartments']) {
                        if ($snippet->isVisibleGlobal()) {
                            $snippet->setIsVisibleGlobal(false);
                            $processed[] = $snippet->getId();
                        }
                        $selectedDepartmentIds = [];
                        foreach ($value['selectedDepartments'] as $departmentId => $departmentValue) {
                            $department = $this->getManager()->getRepository(Department::class)->find($departmentId);
                            if (!$department) {
                                throw $this->createNotFoundException(sprintf('Unkown department %s', $department));
                            }
                            if ($departmentValue) {
                                $selectedDepartmentIds[] = $departmentId;
                                if (!$snippet->hasDepartment($department)) {
                                    $snippet->addDepartment($department);
                                    $processed[] = $snippet->getId();
                                }
                            }
                        }
                        foreach ($snippet->getVisibleDepartments() as $department) {
                            if (!in_array($department->getId(), $selectedDepartmentIds)) {
                                $snippet->removeDepartment($department);
                                $processed[] = $snippet->getId();
                            }
                        }
                    } else {
                        if ($snippet->isVisibleGlobal() || $snippet->hasDepartments()) {
                            $snippet->setIsVisibleGlobal(false);
                            $snippet->clearDepartments();
                            $processed[] = $snippet->getId();
                        }
                    }
                }
                break;
            case 'ownership':
                foreach ($snippets as $snippet) {
                    if ($value['isOwnershipGlobal']) {
                        if (!$snippet->isOwnershipGlobal() || $snippet->hasTeams()) {
                            $snippet->setIsOwnershipGlobal(true);
                            $snippet->clearTeams();
                            $processed[] = $snippet->getId();
                        }
                    } elseif ($value['selectedTeams']) {
                        if ($snippet->isOwnershipGlobal()) {
                            $snippet->setIsOwnershipGlobal(false);
                            $processed[] = $snippet->getId();
                        }
                        $selectedTeamIds = [];
                        foreach ($value['selectedTeams'] as $teamId => $teamValue) {
                            $team = $this->getManager()->getRepository(AgentTeam::class)->find($teamId);
                            if (!$team) {
                                throw $this->createNotFoundException(sprintf('Unkown team %s', $teamId));
                            }
                            if ($teamValue) {
                                $selectedTeamIds[] = $teamId;
                                if (!$snippet->hasTeam($team)) {
                                    $snippet->addTeam($team);
                                    $processed[] = $snippet->getId();
                                }
                            } else {
                                if ($snippet->hasTeam($team)) {
                                    $snippet->removeTeam($team);
                                    $processed[] = $snippet->getId();
                                }
                            }
                        }
                    } else {
                        if ($snippet->isOwnershipGlobal() || $snippet->hasTeams()) {
                            $snippet->setIsOwnershipGlobal(false);
                            $snippet->clearTeams();
                            $processed[] = $snippet->getId();
                        }
                    }
                }
                break;
            case 'type':
                if (!$value) {
                    throw $this->createBadRequestException('At least one type required');
                }

                foreach ($snippets as $snippet) {
                    foreach ($value as $type => $typeValue) {
                        if (!in_array($type, [Snippet::TYPE_TICKET, Snippet::TYPE_CHAT])) {
                            throw $this->createNotFoundException("#Unkown type {$type} for type action");
                        }
                        if ($typeValue) {
                            if (!$snippet->hasType($type)) {
                                $snippet->addType($type);
                                $processed[] = $snippet->getId();
                            }
                        } else {
                            if ($snippet->hasType($type)) {
                                $snippet->removeType($type);
                                $processed[] = $snippet->getId();
                            }
                        }
                    }
                }
                break;
            case 'draft':
                foreach ($snippets as $snippet) {
                    if (!isset($value['publish_status'])) {
                        $value['publish_status'] = '';
                    }
                    if ($value['publish_status'] === 'draft') {
                        if (!$snippet->isDraft()) {
                            $snippet->setIsDraft(true);
                            $processed[] = $snippet->getId();
                        }
                    } elseif ($value['publish_status'] === 'published') {
                        if ($snippet->isDraft()) {
                            $snippet->setIsDraft(false);
                            $processed[] = $snippet->getId();
                        }
                    } else {
                        throw $this->createNotFoundException("#Unkown value {$value['publish_status']} for draft action");
                    }
                }
                break;
            default:
                throw $this->createNotFoundException("#Unkown action {$action}");
        }
        $this->getManager()->flush();

        return View::create($this->wrap(['processed' => array_values(array_unique($processed))]));
    }

    /**
     * @ApiDoc(
     *      description="Get collection of snippet labels",
     *      tags={"CRUD"="#ffa500"},
     *      filters={
     *          {"name"="page", "pattern"="\d", "description"="Which page to display", "dataType"="integer"},
     *          {"name"="count", "pattern"="\d", "description"="Resource per page count", "dataType"="integer"},
     *          {"name"="limit", "pattern"="\d", "description"="Max number of resources to return", "dataType"="integer"},
     *          {"name"="ids", "pattern"="[\d,]+", "description"="Comma separated list of IDs", "dataType"="string"},
     *      },
     *      output="array",
     *      statusCodes={
     *          200="Returned if your request was successful",
     *          400="An error will occur if you provide wrong filters set",
     *      }
     * )
     * @Rest\Get("/labels")
     *
     * @return View
     */
    public function listLabelsAction()
    {
        return View::create($this->wrap($this->getRepository(Snippet::class)->getSnippetsLabelsForAgent($this->getUser())));
    }

    /**
     * @ApiDoc(
     *      description="Get an export of snippets",
     *      tags={"CRUD"="#ffa500"},
     *      filters={
     *          {"name"="page", "pattern"="\d", "description"="Which page to display", "dataType"="integer"},
     *          {"name"="count", "pattern"="\d", "description"="Resource per page count", "dataType"="integer"},
     *          {"name"="limit", "pattern"="\d", "description"="Max number of resources to return", "dataType"="integer"},
     *          {"name"="ids", "pattern"="[\d,]+", "description"="Comma separated list of IDs", "dataType"="string"},
     *      },
     *      statusCodes={
     *          200="Returned if your request was successful",
     *          400="An error will occur if you provide wrong filters set",
     *      },
     *      output="file"
     * )
     * @Rest\Get("/csv")
     *
     * @param Request $request
     *
     * @return \FOS\RestBundle\View\View
     */
    public function csvAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW_LIST, $this->getPermissionGroupContext($request));

        $qb = $this->getManager()->createQueryBuilder();
        $qb->select('e');
        $qb->from(static::$entity, 'e');

        $this->applyListFilters($qb, 'e', $request);
        $this->applySorting($qb, 'e', $request);

        $ids = $request->get('ids');
        if ($ids) {
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }

            $ids = array_map(function ($id) {
                return (int) $id;
            }, $ids);

            $qb->andWhere('e.id IN (:ids)');
            $qb->setParameter('ids', $ids);
        }

        $result = $qb->getQuery()->getResult();

        $delimiter = ';';

        $headers = [
            'id',
            'title',
            'person_id',
            'shortcut_code',
            'types',
            'labels',
            'is_draft',
            'is_ownership_global',
            'teams_ids',
            'is_visible_global',
            'departments_ids',
            'is_split',
        ];

        /** @var LanguageDataService $languageDataService */
        $languageDataService = $this->container->getDataService('Language');
        if ($languageDataService->isMultiLang()) {
            $languages = $languageDataService->getAll();
            foreach ($languages as $language) {
                $headers[] = $language->getLocale().'_content';
                $headers[] = $language->getLocale().'_blobs';
                $headers[] = $language->getLocale().'_ticket_content';
                $headers[] = $language->getLocale().'_ticket_blobs';
                $headers[] = $language->getLocale().'_chat_content';
                $headers[] = $language->getLocale().'_chat_blobs';
            }
        } else {
            $headers[] = 'content';
            $headers[] = 'blobs';
            $headers[] = 'ticket_content';
            $headers[] = 'ticket_blobs';
            $headers[] = 'chat_content';
            $headers[] = 'chat_blobs';
        }

        $response = new StreamedResponse();
        $response->setCallback(function () use ($delimiter, $headers, $result, $languageDataService) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            flush();
            $i = 0;
            /** @var Snippet $snippet */
            foreach ($result as $snippet) {
                $labels = [];
                foreach ($snippet->getLabels() as $label) {
                    $labels[] = $label->getLabel();
                }
                $teams = [];
                foreach ($snippet->getOwnershipTeams() as $team) {
                    $teams[] = $team->getId();
                }
                $departments = [];
                foreach ($snippet->getVisibleDepartments() as $department) {
                    $departments[] = $department->getId();
                }
                $data = [
                    $snippet->getId(),
                    $snippet->getTitle(),
                    $snippet->getPerson() ? $snippet->getPerson()->getId() : '',
                    $snippet->getShortcutCode(),
                    implode(',', $snippet->getTypes()),
                    implode(',', $labels),
                    $snippet->isDraft() ?: 0,
                    $snippet->isOwnershipGlobal() ?: 0,
                    implode(',', $teams),
                    $snippet->isVisibleGlobal() ?: 0,
                    implode(',', $departments),
                    $snippet->isSplit() ?: 0,
                ];
                $translations = $snippet->getTranslations();
                if ($languageDataService->isMultiLang()) {
                    $languages = $languageDataService->getAll();
                    foreach ($languages as $language) {
                        $this->fillTranslations($language, $translations, $snippet, $data);
                    }
                } else {
                    $language = $languageDataService->getDefault();
                    $this->fillTranslations($language, $translations, $snippet, $data);
                }
                fputcsv($out, $data);
                ++$i;
                if ($i > 50) {
                    flush();
                    $i = 0;
                }
            }
        });
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'export.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->send();
    }

    /**
     * @param Language             $language
     * @param SnippetTranslation[] $translations
     * @param string               $type
     *
     * @return SnippetTranslation|bool
     */
    protected function findTranslation($language, $translations, $type = '')
    {
        $foundTranslation = false;
        foreach ($translations as $translation) {
            if ($translation->getLanguage()->getId() === $language->getId()) {
                if (!$type || $translation->getType() === $type) {
                    $foundTranslation = $translation;
                    break;
                }
            }
        }

        return $foundTranslation;
    }

    protected function fillTranslations($language, $translations, $snippet, &$data)
    {
        if ($snippet->isSplit()) {
            $data[] = '';
            $data[] = '';
            foreach (['ticket', 'chat'] as $type) {
                $translation = $this->findTranslation($language, $translations, $type);
                if ($translation) {
                    $data[] = $translation->getContent();
                    $blobs  = [];
                    foreach ($translation->getBlobs() as $blob) {
                        $blobs[] = $blob->getDownloadUrl(true);
                    }
                    $data[] = implode(',', $blobs);
                } else {
                    $data[] = '';
                    $data[] = '';
                }
            }
        } else {
            $translation = $this->findTranslation($language, $translations);
            if ($translation) {
                $data[] = $translation->getContent();
                $blobs  = [];
                foreach ($translation->getBlobs() as $blob) {
                    $blobs[] = $blob->getDownloadUrl(true);
                }
                $data[] = implode(',', $blobs);
                $data[] = '';
                $data[] = '';
                $data[] = '';
                $data[] = '';
            } else {
                $data[] = '';
                $data[] = '';
                $data[] = '';
                $data[] = '';
                $data[] = '';
                $data[] = '';
            }
        }
    }
}
