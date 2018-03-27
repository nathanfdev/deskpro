<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\TextSnippets;

use Application\DeskPRO\Entity\TextSnippetCategory;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AbstractTextSnippetsController.
 */
abstract class AbstractTextSnippetsController extends CrudController
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
     * @param Request $request
     *
     * @return string
     */
    protected function getContextEntityClass(Request $request)
    {
        switch ($this->getSnippetTypeName($request)) {
            case TextSnippetCategory::TYPE_TICKET:
                return 'DeskPRO:Ticket';
            default:
                throw new NotFoundHttpException();
        }
    }

    /**
     * @param Request      $request
     * @param QueryBuilder $qb
     * @param string       $refType
     */
    protected function applyFilterByLanguageAndQueryString(Request $request, QueryBuilder $qb, $refType)
    {
        $query = $request->query;
        if (!$query->get('language') && !$query->get('q')) {
            return;
        }

        $qb
            ->join('DeskPRO:ObjectLang', 'o', Join::WITH, 'o.ref_id = e.id')
            ->andWhere('o.ref_type = :ref_type')
            ->setParameter('ref_type', $refType)
        ;

        if ($query->get('language')) {
            if (is_numeric($query->get('language'))) {
                $languageId = $query->getInt('language');
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

                $languageId = $language ? $language->getId() : 0;
            }

            $qb->andWhere('o.language = :language');
            $qb->setParameter('language', $languageId);
        }

        if ($query->get('q')) {
            if ($refType === 'text_snippets') {
                $qb->andWhere('o.value LIKE :query_string OR e.shortcut_code LIKE :query_string');
            } else {
                $qb->andWhere('o.value LIKE :query_string');
            }

            $qb->setParameter('query_string', '%'.$query->get('q').'%');
        }
    }
}
