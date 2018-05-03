<?php

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Orb\Util\Strings;

class Guide extends AbstractEntityRepository
{
    public function getBySlug($slug)
    {
        $id = Strings::extractRegexMatch('#^([0-9]+)#', $slug, 1);
        if (!$id) {
            return;
        }

        return $this->find($id);
    }

    public function getAllCounts()
    {
        $counts = App::getDb()->fetchAllKeyed('
            SELECT g.id as guide_id, COUNT(t.id) as count
            FROM guides AS g
              LEFT JOIN topics AS t
                ON t.guide_id = g.id
            GROUP BY g.id
            ORDER BY g.id ASC
        ', [], 'guide_id');

        $result = [];
        foreach ($counts as $count) {
            $result[$count['guide_id']] = $count['count'];
        }

        return $result;
    }

    public function getPermissionTableName()
    {
        return 'guide2usergroup';
    }

    public function getCategoryField()
    {
        return 'guide_id';
    }

    public function getGuidesForUsergroups(array $usergroupIds)
    {
        // For guides, everyone is always on, even if its disabled,
        // because everyone still means everyone from agent ui perspective
        $usergroupIds[] = App::$container->getUserGroups()->getEveryoneGroup()->id;

        if (!$usergroupIds) {
            return [];
        }

        $conn = App::getDb();
        $qb   = $conn->createQueryBuilder();

        $tbl = $conn->quoteIdentifier('guide2usergroup');
        $qb->select('guide_id');
        $qb->from($tbl, 't');
        $qb->andWhere($qb->expr()->in('usergroup_id', $usergroupIds));
        $qb->groupBy('guide_id');

        /** @var BrandStack $brandStack */
        $brandStack = App::get('brand_stack');

        $currentBrand = $brandStack->getActive()->getBrand();

        $qb->innerJoin('t', 'guides', 'g', 'g.id = t.guide_id');
        $qb->andWhere($qb->expr()->eq('g.brand_id', $currentBrand->getId()));

        $guideIds = $conn->fetchAllCol($qb->getSQL());

        return $guideIds;
    }

    public function getIds()
    {
        return App::getDb()->fetchAllCol('
            SELECT m.id as guide_id
            FROM guides AS m
        ');
    }

    /**
     * Not used in guides be it is called by PublishController.
     */
    public function repair()
    {
    }
}
