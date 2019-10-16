<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Helper;

use DeskPRO\Bundle\PortalBundle\Model\CommunityFilter;

class CommunityFilterUriHelper
{
    /**
     * @param $filter_uri
     *
     * @return CommunityFilter
     */
    public function extractCommunityFilter($filter_uri)
    {
        $filter = new CommunityFilter();

        $segments = explode('/', $filter_uri);

        foreach ($segments as $segment) {
            if (!strlen($segment)) {
                continue;
            }

            if ($this->isStatus($segment)) {
                $parts = explode('-', $segment);

                $filter->setStatus($parts[0]);

                if (isset($parts[1])) {
                    $categories = $this->getIntArrayFromCsv($parts[1]);

                    $filter->setStatusCategories($categories);
                }
            } elseif ($this->isTypes($segment)) {
                $parts = explode('-', $segment);

                if (isset($parts[1])) {
                    $types = $this->getIntArrayFromCsv($parts[1]);

                    $filter->setTypes($types);

                    if (count($types) === 1) {
                        $filter->setCurrentType(current($types));
                    }
                }
            } elseif ($this->isSort($segment)) {
                $parts = $this->getSortParts($segment);

                $filter->setSort($parts[0]);

                if (isset($parts[1])) {
                    $filter->setSortDirection($parts[1]);
                }
            } elseif ($this->isView($segment)) {
                $filter->setView(ltrim($segment, 'view-'));
            } elseif ($this->isViewMode($segment)) {
                $filter->setViewMode(str_replace('viewmode-', '', $segment));
            } elseif ($this->hasActivities($segment)) {
                $filter->setActivities(explode(',', str_replace('activity-', '', $segment)));
            } elseif ($categories = $this->getIntArrayFromCsv($segment)) {
                $filter->setStatus(CommunityFilter::STATUS_ACTIVE);
                $filter->setStatusCategories($categories);
            } else {
                throw new \InvalidArgumentException('could not parse community uri segment "'.$segment.'"');
            }
        }

        return $filter;
    }

    private function isStatus($segment)
    {
        $segment = $this->filterSegment($segment);

        if (strlen($segment)) {
            foreach (CommunityFilter::$statuses as $status) {
                if ($status === substr($segment, 0, strlen($status))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $segment
     *
     * @return string
     */
    private function filterSegment($segment)
    {
        if (strlen($segment) && $segment[0] === '/') {
            $segment = substr($segment, 1);
        }

        return $segment;
    }

    /**
     * @param $parts
     *
     * @return array
     */
    private function getIntArrayFromCsv($parts)
    {
        $categories = explode(',', $parts);
        $categories = array_filter($categories, function ($val) {
            return (int) $val;
        });

        return $categories;
    }

    private function isTypes($segment)
    {
        $segment = $this->filterSegment($segment);

        if (substr($segment, 0, 4) === 'type') {
            return true;
        }

        return false;
    }

    private function isView($segment)
    {
        $segment = $this->filterSegment($segment);

        return in_array(ltrim($segment, 'view-'), CommunityFilter::$views);
    }

    private function isViewMode($segment)
    {
        $segment = $this->filterSegment($segment);

        return in_array(str_replace('viewmode-', '', $segment), CommunityFilter::$viewModes);
    }

    private function isSort($segment)
    {
        $segment = $this->filterSegment($segment);

        $parts = $this->getSortParts($segment);

        if (count($parts) && strlen($parts[0])) {
            foreach (CommunityFilter::$sorts as $sort) {
                if ($sort === substr($parts[0], 0, strlen($sort))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $segment
     * @return false|int
     */
    private function hasActivities($segment)
    {
        return preg_match('/activity\-.*/', $segment);
    }

    /**
     * @param $segment
     *
     * @return array
     */
    private function getSortParts($segment)
    {
        $parts = explode('-', $segment);

        if (count($parts) === 2) {
            if (!in_array($parts[1], [
                CommunityFilter::SORT_DIRECTION_DESC,
                CommunityFilter::SORT_DIRECTION_ASC,
            ])) {
                return [$parts[0].'-'.$parts[1]];
            }
        }

        if (count($parts) > 2) {
            $parts = [
                $parts[0].'-'.$parts[1],
                $parts[2],
            ];

            return $parts;
        }

        return $parts;
    }

    public function generateUriSegment(CommunityFilter $filter)
    {
        $defaults = CommunityFilter::getDefaultValues();

        // default requires no url
        if ($filter->toArray() == $defaults) {
            return '';
        }

        // we need to return something now
        $uri = '';

        if ($filter->getStatus() != $defaults['status']) {
            $uri .= '/'.$filter->getStatus();
        }

        if ($filter->getStatusCategories() != $defaults['status_categories']) {
            $uri .= sprintf(
                '%s%s',
                $filter->getStatus() !== $defaults['status'] ? '-' : '',
                implode(',', $filter->getStatusCategories()));
        }

        if ($filter->getTypes() != $defaults['types']) {
            $uri .= sprintf('/type-%s', implode(',', $filter->getTypes()));
        }

        if (
            $filter->getSort() != $defaults['sort']
            || $filter->getSortDirection() != $defaults['sort_direction']
        ) {
            if ($filter->getSortDirection() != $defaults['sort_direction']) {
                $uri .= sprintf('/%s-%s', $filter->getSort(), $filter->getSortDirection());
            } else {
                $uri .= sprintf('/%s', $filter->getSort());
            }
        }

        if ($filter->getView()) {
            $uri .= sprintf('/view-%s', $filter->getView());
        }

        if ($filter->getViewMode()) {
            $uri .= sprintf('/viewmode-%s', $filter->getViewMode());
        }

        if ($filter->getActivities() != $defaults['activities']) {
            $uri .= sprintf('/activity-%s', implode(',', $filter->getActivities()));
        }

        return ltrim($uri, '/');
    }
}
