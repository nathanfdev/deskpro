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
namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonNote;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonNoteType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PersonNotesController.
 *
 * @ApiModes("all")
 */
class PersonNotesController extends CrudController
{
    public static $exposeOnly = ['list', 'post', 'put', 'delete'];
    public static $entity     = PersonNote::class;
    public static $type       = PersonNoteType::class;

    /**
     * {@inheritdoc}
     *
     * @Get("/people/{id}/notes")
     */
    public function listAction(Request $request)
    {
        return parent::listAction($request);
    }

    /**
     * {@inheritdoc}
     *
     * @Post("/people/{id}/notes")
     */
    public function postAction(Request $request)
    {
        return parent::postAction($request);
    }

    /**
     * {@inheritdoc}
     *
     * @Put("/people/{person_id}/notes/{id}")
     */
    public function putAction($id, Request $request)
    {
        $entity = $this->findEntity($id, $request);
        if ($entity->getPerson()->getId() !== (int) $request->get('person_id')) {
            throw $this->createBadRequestException();
        }
        if ($entity->getAgent() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return parent::putAction($id, $request);
    }

    /**
     * {@inheritdoc}
     *
     * @Delete("/people/{person_id}/notes/{id}")
     */
    public function deleteAction($id, Request $request)
    {
        $entity = $this->findEntity($id, $request);
        if ($entity->getPerson()->getId() !== (int) $request->get('person_id')) {
            throw $this->createBadRequestException();
        }
        if ($entity->getAgent() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return parent::deleteAction($id, $request);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $person = $this->findOr404(Person::class, $request->get('id'));
        $qb->andWhere("{$alias}.person = :person");
        $qb->setParameter('person', $person->getId());
    }

    /**
     * {@inheritdoc}
     */
    protected function instantiateEntity(Request $request)
    {
        $person = $this->findOr404(Person::class, $request->get('id'));

        /** @var PersonNote $entity */
        $entity         = new static::$entity();
        $entity->agent  = $this->getUser();
        $entity->person = $person;

        return $entity;
    }
}
