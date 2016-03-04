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

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\ObjectLang;
use Application\DeskPRO\Entity\TextSnippet;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\TextSnippet\TextSnippetType;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class TextSnippetsController.
 *
 * @ApiModes("all")
 * @Route("/{context}_snippets", requirements={"context"="(ticket|chat)"})
 */
class TextSnippetsController extends CrudController
{
    use ContextTypeTrait;

    public static $entity    = TextSnippet::class;
    public static $type      = TextSnippetType::class;
    public static $listOrder = 'asc';

    /**
     * @param HttpKernelInterface $kernel
     * @param Request             $masterRequest
     * @param array               $params
     *
     * @return Response
     */
    public static function subRequestSearch(HttpKernelInterface $kernel, Request $masterRequest, array $params)
    {
        $request = $masterRequest->duplicate(array_merge($params, $masterRequest->query->all()), null, [
            '_controller' => 'ApiBundle:TextSnippets\TextSnippets:list',
            'context'     => $masterRequest->attributes->get('context'),
        ]);
        $request->query->add($params);

        return $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb
            ->join('e.category', 'c')
            ->andWhere('c.typename = :category_type')
            ->setParameter('category_type', $this->getSnippetTypeName($request))
        ;

        $query = $request->query;

        // filter by person
        if ($query->get('my')) {
            $qb
                ->andWhere('e.person = :user_id')
                ->setParameter('user_id', $this->getUser()->getId())
            ;
        } elseif ($query->get('global')) {
            $qb->andWhere('e.person is null');
        } else {
            $qb
                ->andWhere('e.person = :user_id OR e.person is null')
                ->setParameter('user_id', $this->getUser()->getId())
            ;
        }

        // filter by category
        if ($query->get('category')) {
            $qb
                ->andWhere('e.category = :category_id')
                ->setParameter('category_id', $request->get('category'))
            ;
        }

        // filter by draft
        if ($query->has('draft')) {
            $qb
                ->andWhere('e.is_draft = :is_draft')
                ->setParameter('is_draft', $query->getInt('draft'))
            ;
        }

        // filter by language
        if ($query->get('language')) {
            if (is_numeric($query->get('language'))) {
                $language_id = (int) $query->get('language');
            } else {
                // look by lang code or locale
                $language = $this
                    ->getRepository(Language::class)
                    ->createQueryBuilder('l')
                    ->where('l.locale = :language OR l.lang_code = :language')
                    ->setParameter('language', $query->get('language'))
                    ->getQuery()
                    ->getOneOrNullResult()
                ;

                $language_id = $language ? $language->getId() : 0;
            }

            $qb
                ->join(ObjectLang::class, 'o', Join::WITH, 'o.ref_id = e.id')
                ->andWhere(
                    'o.language = :language',
                    'o.ref_type = :ref_type'
                )
                ->setParameter('language', $language_id)
                ->setParameter('ref_type', 'text_snippets')
            ;
        }
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
        /** @var TextSnippet $entity */
        $entity   = parent::findEntity($id, $request);
        $category = $entity->getCategory();

        if ($category && $category->getTypename() !== $this->getSnippetTypeName($request)) {
            throw $this->createNotFoundException();
        }
        if ($entity->getPerson() && $entity->getPerson() !== $this->getUser()) {
            throw $this->createNotFoundException();
        }

        return $entity;
    }
}
