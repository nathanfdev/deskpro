<?php

namespace DeskPRO\Bundle\AppBundle\CountBadge;

use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MapUtils;

class CountBuilder
{
    /**
     * @var CountTitleResolver
     */
    private $titleResolver;

    /**
     * CountBuilder constructor.
     *
     * @param CountTitleResolver $titleResolver
     */
    public function __construct(CountTitleResolver $titleResolver)
    {
        $this->titleResolver = $titleResolver;
    }

    /**
     * Given an array of counts (e.g. from a DB query), sorts them into a Count structure.
     *
     * The numeric count is expected to exist in 'count'.
     *
     * $groupFields should be values within each count. The purpose of $groupFields is to
     * define the order.
     *
     * Examples:
     *
     * <code>
     * // e.g. grouped by agent
     * [
     *     count => 123,
     *     agent_id => 5
     * ]
     *
     * // e.g. grouped by agent and then by department
     * [
     *     count => 123,
     *     agent_id => 5,
     *     department_id => 5
     * ]
     *
     * // If a value is null, then we assume it's a roll-up.
     * // e.g. department_id of null means this would be considered a rollout count
     * // of count for ALL agent_id 5
     * [
     *     count => 123,
     *     agent_id => 5,
     *     department_id => null
     * ]
     * </code>
     *
     * @param array  $counts
     * @param string $groupFields
     */
    public function buildFromArray(array $rawCounts, array $groupFields)
    {
        $hierarchyInfo = ListUtils::map(
            $this->collectRawIds($rawCounts, $groupFields),
            function ($ids, $idx) use ($groupFields) {
                return $this->titleResolver->getHierarchyMap($groupFields[$idx], $ids);
            }
        );

        $counts = ListUtils::map($rawCounts, function ($v) use ($groupFields, $hierarchyInfo) {
            $idParts = [];
            $groupField = null;
            foreach ($groupFields as $hDepth => $fieldId) {
                if (isset($v[$fieldId])) {
                    $i = $v[$fieldId];

                    // collapse field hierarchy to the root
                    if (!empty($hierarchyInfo[$hDepth][$i])) {
                        $i = $hierarchyInfo[$hDepth][$i][0];
                    }

                    $idParts[] = $i;
                    $groupField = $fieldId;
                } else {
                    break;
                }
            }

            $depth = count($idParts) - 1;

            return [
                'count'      => $v['count'],
                'path'       => $depth >= 0 ? implode('.', $idParts) : 'TOP',
                'ids'        => $idParts,
                'depth'      => $depth,
                'groupField' => $groupField,
                'groupBy'    => isset($idParts[$depth]) ? $idParts[$depth] : null,
            ];
        });

        /** @var Count[] $countMap */
        $countMap = [];
        foreach ($counts as $countInfo) {
            if (isset($countMap[$countInfo['path']])) {
                $countMap[$countInfo['path']]->setCount(
                    // may already exist if we collapsed hierarchy
                    $countInfo['count'] + $countMap[$countInfo['path']]->getCount()
                );
            } else {
                $count = Count::create(
                    $countInfo['count'],
                    $countInfo['groupBy'],
                    $countInfo['groupField'],
                    'TITLE'
                );

                $count->setMeta('pathIds', $countInfo['ids']);

                $countMap[$countInfo['path']] = $count;
            }
        }

        // initialise missing values
        // so this is when >1 groupFields exist, but no rollup counts are
        // provided (i.e. A > B = 10, A > C = 10 -- but we need to calc that A = 20)
        if (!isset($countMap['TOP'])) {
            $countMap['TOP'] = Count::create(0, 'TOP', null, 'TOP');
            $countMap['TOP']->setMeta('isCalculated', true);
            $countMap['TOP']->setMeta('pathIds', []);
        }

        foreach ($counts as $countInfo) {
            if ($countInfo['depth'] < 1) {
                continue;
            }

            $idParts = $countInfo['ids'];
            while (array_pop($idParts)) {
                $key = implode('.', $idParts) ?: 'TOP';
                if (!isset($countMap[$key])) {
                    $countMap[$key] = Count::create(
                        0,
                        ListUtils::last($idParts),
                        $groupFields[count($idParts) - 1],
                        'TITLE'
                    );
                    $countMap[$key]->setMeta('isCalculated', true);
                    $countMap[$key]->setMeta('pathIds', $idParts);
                }
            }
        }

        $titleIds = array_fill_keys($groupFields, []);
        foreach ($countMap as $c) {
            if ($c->getType() && $c->getId()) {
                $titleIds[$c->getType()][] = $c->getId();
            }
        }
        $titles = MapUtils::map($titleIds, function ($fieldId, $ids) {
            return [$fieldId, $this->titleResolver->getTitles($fieldId, array_unique($ids))];
        });

        // Fill in titles, and connect children to their parent
        MapUtils::forEach($countMap, function ($i, Count $count) use ($titles, $countMap) {
            if ($count->getType() && $count->getId()) {
                if (isset($titles[$count->getType()][$count->getId()])) {
                    $title = $titles[$count->getType()][$count->getId()];
                } else {
                    $titleParts = [];
                    $parentIds = $count->getMeta('pathIds', []);
                    while (array_pop($parentIds) !== null && $parentIds) {
                        $titleParts[] =
                            $countMap[implode('.', $parentIds)]->getType()
                            .'.'
                            .$countMap[implode('.', $parentIds)]->getId();
                    }

                    $titleParts[] = "{$count->getType()}.{$count->getId()}";
                    $title = implode('/', $titleParts);
                }
            } else {
                $title = ''; // the top level count
            }

            $count->setTitle($title);

            if ($i !== 'TOP') {
                $parentIds = $count->getMeta('pathIds', []);
                array_pop($parentIds);

                if (empty($parentIds)) {
                    $parentCount = $countMap['TOP'];
                } else {
                    $parentCount = $countMap[implode('.', $parentIds)];
                }

                $parentCount->addNestedInstance($count, $parentCount->getMeta('isCalculated', false));
            }
        });

        return $countMap['TOP'];
    }

    private function collectRawIds(array $rawCount, array $groupFields)
    {
        $collectIds = array_fill(0, count($groupFields), []);
        foreach ($rawCount as $v) {
            $idParts = [];
            foreach ($groupFields as $fieldId) {
                if (isset($v[$fieldId])) {
                    $idParts[] = $v[$fieldId];
                } else {
                    break;
                }
            }

            if (!empty($idParts)) {
                $depth = count($idParts) - 1;

                if (isset($collectIds[$depth])) {
                    $collectIds[$depth][] = $idParts[$depth];
                }
            }
        }

        $collectIds = ListUtils::map($collectIds, function ($v) {
            return array_unique($v);
        });

        return $collectIds;
    }
}
