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

namespace DeskPRO\Bundle\ApiBundle\Controller\Snippets;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Entity\SnippetLabel;
use DeskPRO\Bundle\AppBundle\Form\Type\Snippets\SnippetType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

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

    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $agent = $this->getUser();
        $agent->loadHelper('AgentTeam');

        $qb
            ->where("($alias.person = :person")
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

    /**
     * @param int     $id
     * @param Request $request
     * @param string  $type
     *
     * @return object
     */
    protected function findEntity($id, Request $request, $type = '')
    {
        return $this->findOr404(static::$entity, $id, null, $type);
    }

    /**
     * @param string $class
     * @param int    $id
     * @param string $message
     * @param string $type
     *
     * @return object
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
     *      }
     * )
     * @Rest\Post("/mass_actions")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postMassActionsAction(Request $request)
    {
        $action   = $request->request->get('action');
        $value    = $request->request->get('value');
        $selected = $request->request->get('selected');

        /** @var Snippet[] $snippets */
        $snippets  = $this->getRepository(Snippet::class)->findSnippetsForAgent($this->getUser(), $selected);
        $processed = [];

        switch ($action) {
            case 'labels':
                foreach ($snippets as $snippet) {
                    foreach ($value as $label => $labelValue) {
                        $labelObject = new SnippetLabel($label);
                        if ($labelValue) {
                            if (!$snippet->hasLabel($labelObject)) {
                                $snippet->addLabel($labelObject);
                                $processed[] = $snippet->getId();
                            }
                        } else {
                            if ($snippet->hasLabel($labelObject)) {
                                $snippet->removeLabel($labelObject);
                                $processed[] = $snippet->getId();
                            }
                        }
                    }
                }
                break;
            case 'visibility':
                $departmentsBuffer = [];
                foreach ($snippets as $snippet) {
                    if (!$this->getUser()->hasPerm('agent_snippets.edit_by_others') && $snippet->getPerson() !== $this->getUser()) {
                        continue;
                    }
                    if ($value['isVisibleGlobal']) {
                        if (!$snippet->isVisibleGlobal()) {
                            $snippet->setIsVisibleGlobal(true);
                            foreach ($snippet->getVisibleDepartments() as $department) {
                                $snippet->removeDepartment($department);
                                $processed[] = $snippet->getId();
                            }
                        }
                    } else {
                        $snippetsDepartments = $snippet->getVisibleDepartments();
                        foreach ($value['selectedDepartments'] as $departmentId => $departmentValue) {
                            if (!isset($departmentsBuffer[$departmentId])) {
                                $departmentsBuffer[$departmentId] = $this->getManager()->getRepository(Department::class)->find($departmentId);
                            }
                            $department = $departmentsBuffer[$departmentId];
                            if ($departmentValue) {
                                if (!$snippetsDepartments->contains($department)) {
                                    $snippet->addDepartment($department);
                                    if (!in_array($snippet->getId(), $processed)) {
                                        $processed[] = $snippet->getId();
                                    }
                                }
                            } else {
                                if ($snippetsDepartments->contains($department)) {
                                    $snippet->removeDepartment($department);
                                    if (!in_array($snippet->getId(), $processed)) {
                                        $processed[] = $snippet->getId();
                                    }
                                }
                            }
                        }
                    }
                }
                break;
            case 'ownership':
                $teamsBuffer = [];
                foreach ($snippets as $snippet) {
                    if ($value['isOwnershipGlobal']) {
                        if (!$snippet->isOwnershipGlobal()) {
                            $snippet->setIsOwnershipGlobal(true);
                            foreach ($snippet->getOwnershipTeams() as $team) {
                                $snippet->removeTeam($team);
                                $processed[] = $snippet->getId();
                            }
                        }
                    } else {
                        $snippetsTeams = $snippet->getOwnershipTeams();
                        foreach ($value['selectedTeams'] as $teamId => $teamValue) {
                            if (!isset($teamsBuffer[$teamId])) {
                                $teamsBuffer[$teamId] = $this->getManager()->getRepository(AgentTeam::class)->find($teamId);
                            }
                            $team = $teamsBuffer[$teamId];
                            if ($teamValue) {
                                if (!$snippetsTeams->contains($team)) {
                                    $snippet->addTeam($team);
                                    if (!in_array($snippet->getId(), $processed)) {
                                        $processed[] = $snippet->getId();
                                    }
                                }
                            } else {
                                if ($snippetsTeams->contains($team)) {
                                    $snippet->removeTeam($team);
                                    if (!in_array($snippet->getId(), $processed)) {
                                        $processed[] = $snippet->getId();
                                    }
                                }
                            }
                        }
                    }
                }
                break;
            case 'type':
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
                                $snippet->addType($type);
                                $processed[] = $snippet->getId();
                            }
                        }
                    }
                }
                break;
            case 'draft':
                foreach ($snippets as $snippet) {
                    if ($value === 'draft') {
                        if (!$snippet->isDraft()) {
                            $snippet->setIsDraft(true);
                            $processed[] = $snippet->getId();
                        }
                    } elseif ($value === 'published') {
                        if ($snippet->isDraft()) {
                            $snippet->setIsDraft(false);
                            $processed[] = $snippet->getId();
                        }
                    } else {
                        throw $this->createNotFoundException("#Unkown value {$value} for draft action");
                    }
                }
                break;
            default:
                throw $this->createNotFoundException("#Unkown action {$action}");
        }
        $this->getManager()->flush();

        return View::create($this->wrap(['processed' => $processed]));
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
}
