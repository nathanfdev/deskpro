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

namespace DeskPRO\Bundle\ApiBundle\Controller\TextSnippets;

use Application\DeskPRO\Entity\TextSnippetCategory;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Traits\TextSnippets\ContextTypeTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TextSnippetCategoriesController.
 *
 * @ApiModes("all")
 * @Route("/{context}_snippet_categories", requirements={"context"="(ticket|chat)"})
 */
class TextSnippetCategoriesController extends CrudController
{
    use ContextTypeTrait;

    public static $listOrder = 'asc';

    /**
     * @ApiDoc(
     *      description="Get the snippets within a category",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the category",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="Application\DeskPRO\Entity\TextSnippet"
     * )
     * @Get("/{id}/snippets")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getSnippetsAction(Request $request, $id)
    {
        $category = $this->findEntity($id, $request);
        if (!$category) {
            throw $this->createNotFoundException();
        }

        return TextSnippetsController::subRequestSearch($this->getKernel(), $request, [
            'category' => $category->getId(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb
            ->andWhere('e.typename = :typename')
            ->setParameter('typename', $this->getSnippetTypeName($request))
        ;

        if ($request->get('my')) {
            $qb
                ->andWhere('e.person = :user_id')
                ->setParameter('user_id', $this->getUser()->getId())
            ;
        } elseif ($request->get('global')) {
            $qb->andWhere('e.is_global = true');
        } else {
            $qb
                ->andWhere('e.person = :user_id OR e.is_global = true')
                ->setParameter('user_id', $this->getUser()->getId())
            ;
        }

        $this->applyFilterByLanguage($request, $qb, 'text_snippet_categories');
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'type'   => $this->getSnippetTypeName($request),
            'person' => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        /** @var TextSnippetCategory $entity */
        $entity = parent::findEntity($id, $request);
        if ($entity->getTypename() !== $this->getSnippetTypeName($request)) {
            throw $this->createNotFoundException();
        }
        if (!$entity->getIsGlobal() && $entity->getPerson() !== $this->getUser()) {
            throw $this->createNotFoundException();
        }

        return $entity;
    }
}
