<?php



namespace Application\DeskPRO\EntityRepository;

use Orb\Util\Arrays;

class ArticlePendingCreate extends AbstractEntityRepository
{
    public function getPendingArticles($limit = [], $order = 'ASC')
    {
        $offset = (int) $limit['offset'];
        $limit  =  $limit['max'];

        return $this->createQueryBuilder('a')
            ->leftjoin('a.ticket', 't')
            ->leftjoin('a.person', 'p')
            ->orderBy('a.date_created', $order)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getPendingArticlesCount()
    {
        return $this->createQueryBuilder('a')
            ->select('count(distinct a.id)')
            ->leftjoin('a.ticket', 't')
            ->leftjoin('a.person', 'p')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getByIds(array $ids, $keep_order = false)
    {
        $ids = Arrays::castToType($ids, 'int');
        $ids = Arrays::removeFalsey($ids);

        if (!$ids) {
            return [];
        }
        $ids = implode(',', $ids);

        return $this->getEntityManager()->createQuery("
            SELECT a
            FROM DeskPRO:ArticlePendingCreate a INDEX BY a.id
            WHERE a.id IN ($ids)
        ")->execute();
    }
}
