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

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Form\Type\Snippets\SnippetType;
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
}
