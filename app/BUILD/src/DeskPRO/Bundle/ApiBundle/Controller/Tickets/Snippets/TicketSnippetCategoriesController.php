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
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\Snippets;

use Application\DeskPRO\Entity\TextSnippetCategory;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\TextSnippet\TextSnippetCategoryType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketSnippetCategoriesController.
 *
 * @ApiModes("all")
 * @Route("/ticket_snippet_categories")
 */
class TicketSnippetCategoriesController extends CrudController
{
    public static $entity = TextSnippetCategory::class;
    public static $type   = TextSnippetCategoryType::class;

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb
            ->andWhere('e.typename = :typename')
            ->setParameter('typename', TextSnippetCategory::TYPE_TICKET)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'type' => TextSnippetCategory::TYPE_TICKET,
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * @param int $id
     *
     * @return object
     */
    protected function findEntity($id)
    {
        /** @var TextSnippetCategory $entity */
        $entity = parent::findEntity($id);
        if (!$entity->getTypename() !== TextSnippetCategory::TYPE_TICKET) {
            throw $this->createNotFoundException();
        }

        return $entity;
    }
}
