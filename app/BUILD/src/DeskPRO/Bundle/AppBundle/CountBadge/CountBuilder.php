<?php

namespace DeskPRO\Bundle\AppBundle\CountBadge;

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
     * @param array  $counts
     * @param string $groupFields
     */
    public function buildFromArray(array $counts, array $groupFields)
    {
        $struct = [];

        foreach ($counts as $c) {
            $idParts = [];
            $depth   = 0;

            foreach ($groupFields as $f) {
                if ($c[$f] === null) {
                    break;
                }
                $idParts[] = $c[$f];
                ++$depth;
            }

            $id = implode('.', $idParts);
            if ($id === '') {
                $id = 'TOP';
            }
            $parentIdParts = $depth ? array_slice($idParts, 0, $depth - 1) : ['TOP'];
            $parentId      = implode('.', $parentIdParts) ?: 'TOP';

            $struct[$id] = [
                'parent'      => $parentId,
                'parentParts' => $parentIdParts,
                'data'        => $c,
                'hasCount'    => true,
                'depth'       => $depth,
                'count'       => Count::create(
                    $c['count'],
                    $depth ? $c[$groupFields[$depth - 1]] : null,
                    $depth ? $groupFields[$depth - 1] : null,
                    'TITLE'
                ),
            ];
        }

        // It's possible the full hierarchy isn't here
        // like if we got counts that did not come from a WITH ROLLUP query
        do {
            $didFix = false;

            foreach ($struct as $id => $info) {
                if (!isset($struct[$info['parent']])) {
                    $didFix = true;

                    $depth         = $info['depth'] - 1;
                    $idParts       = $info['parentParts'];
                    $id            = implode('.', $idParts);
                    $parentIdParts = $depth ? array_slice($idParts, 0, $depth - 1) : ['TOP'];
                    $parentId      = implode('.', $parentIdParts);

                    $struct[$id] = [
                        'parent'      => $parentId,
                        'parentParts' => $parentIdParts,
                        'data'        => null,
                        'hasCount'    => true,
                        'depth'       => $depth,
                        'count'       => Count::create(
                            0,
                            ($depth && $depth - 1) ? $info[$groupFields[$depth - 2]] : null,
                            $depth ? $groupFields[$depth - 1] : null,
                            'TITLE'
                        ),
                    ];
                }
            }
        } while ($didFix);

        // Gather title ids
        $titleIds = [];
        foreach ($struct as $info) {
            $type = $info['count']->getType();
            if (empty($titleIds[$type])) {
                $titleIds[$type] = [];
            }
            $titleIds[$type][] = $info['count']->getId();
        }
        $titleIds = MapUtils::mapValues($titleIds, function ($k, $vals) {
            return array_unique($vals);
        });

        $titleResolver = $this->titleResolver;
        $titles        = MapUtils::mapValues($titleIds, function ($type, $ids) use ($titleResolver) {
            return $titleResolver->getTitles($type, $ids);
        });

        foreach ($struct as $info) {
            $type = $info['count']->getType();
            $id   = $info['count']->getId();
            if (isset($titles[$type][$id])) {
                $info['count']->setTitle($titles[$type][$id]);
            } elseif ($id !== null) {
                $info['count']->setTitle("{$type}.{$id}");
            } else {
                $info['count']->setTitle(''); // the top level count
            }
        }

        // by now we should have a fully structured array with no gaps,
        // we just now need to organise it into a count hierarchy
        $depth = count($groupFields) + 1;
        while ($depth-- > 0) {
            foreach ($struct as $id => $info) {
                if ($info['depth'] != $depth) {
                    continue;
                }

                // add self to parent
                $struct[$info['parent']]['count']->addNestedInstance(
                    $info['count'],
                    !$struct[$info['parent']]['hasCount'] // this makes the intermediary count get calculated if it was provided
                );
            }
        }

        return $struct['TOP']['count'];
    }
}
