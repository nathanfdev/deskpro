<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\Controller\Organizations;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationNote;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Organizations\OrganizationNoteType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrganizationNotesController.
 *
 * @ApiModes("all")
 */
class OrganizationNotesController extends CrudController
{
    public static $exposeOnly = ['list', 'post', 'put', 'delete'];
    public static $entity     = OrganizationNote::class;
    public static $type       = OrganizationNoteType::class;

    /**
     * @Get("/organizations/{id}/notes")
     */
    public function listAction(Request $request)
    {
        return parent::listAction($request);
    }

    /**
     * @Post("/organizations/{id}/notes")
     */
    public function postAction(Request $request)
    {
        return parent::postAction($request);
    }

    /**
     * @Put("/organizations/{organization_id}/notes/{id}")
     */
    public function putAction($id, Request $request)
    {
        $entity = $this->findEntity($id);
        if ($entity->getOrganization()->getId() !== (int) $request->get('organization_id')) {
            throw $this->createBadRequestException();
        }
        if ($entity->getAgent() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return parent::putAction($id, $request);
    }

    /**
     * @Delete("/organizations/{organization_id}/notes/{id}")
     */
    public function deleteAction($id, Request $request)
    {
        $entity = $this->findEntity($id);
        if ($entity->getOrganization()->getId() !== (int) $request->get('organization_id')) {
            throw $this->createBadRequestException();
        }
        if ($entity->getAgent() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return parent::deleteAction($id, $request);
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $alias
     * @param Request      $request
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $organization = $this->findOr404(Organization::class, $request->get('id'));
        $qb->andWhere("{$alias}.organization = :organization");
        $qb->setParameter('organization', $organization->getId());
    }

    /**
     * @param Request $request
     *
     * @return object
     */
    protected function instantiateEntity(Request $request)
    {
        $organization = $this->findOr404(Organization::class, $request->get('id'));

        /** @var OrganizationNote $entity */
        $entity               = new static::$entity();
        $entity->agent        = $this->getUser();
        $entity->organization = $organization;

        return $entity;
    }
}
