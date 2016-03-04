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
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ContextTypeTrait.
 *
 * @method EntityRepository getRepository($class)
 */
trait ContextTypeTrait
{
    /**
     * @param Request $request
     *
     * @return string
     */
    protected function getSnippetTypeName(Request $request)
    {
        $context = $request->attributes->get('context');

        switch ($context) {
            case 'ticket':
                return TextSnippetCategory::TYPE_TICKET;
            case 'chat':
                return TextSnippetCategory::TYPE_CHAT;
            default:
                throw new NotFoundHttpException();
        }
    }

    /**
     * @param Request      $request
     * @param QueryBuilder $qb
     * @param string       $ref_type
     */
    protected function applyFilterByLanguage(Request $request, QueryBuilder $qb, $ref_type)
    {
        $query = $request->query;
        if (!$query->get('language')) {
            return;
        }

        if (is_numeric($query->get('language'))) {
            $language_id = (int) $query->get('language');
        } else {
            // look by lang code or locale
            $language = $this
                ->getRepository('DeskPRO:Language')
                ->createQueryBuilder('l')
                ->where('l.locale = :language OR l.lang_code = :language')
                ->setParameter('language', $query->get('language'))
                ->getQuery()
                ->getOneOrNullResult()
            ;

            $language_id = $language ? $language->getId() : 0;
        }

        $qb
            ->join('DeskPRO:ObjectLang', 'o', Join::WITH, 'o.ref_id = e.id')
            ->andWhere(
                'o.language = :language',
                'o.ref_type = :ref_type'
            )
            ->setParameter('language', $language_id)
            ->setParameter('ref_type', $ref_type)
        ;
    }
}
