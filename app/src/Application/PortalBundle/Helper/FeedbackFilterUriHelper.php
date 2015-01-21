<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Helper;


use Application\PortalBundle\Model\FeedbackFilter;

class FeedbackFilterUriHelper
{
    /**
     * @param $filter_uri
     * @return FeedbackFilter
     */
    public function extractFeedbackFilter($filter_uri)
    {
        $filter = new FeedbackFilter();

        $segments = explode('/', $filter_uri);

        foreach ($segments as $segment) {
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
                }
            } elseif ($this->isSort($segment)) {
                $parts = $this->getSortParts($segment);

                $filter->setSort($parts[0]);

                if (isset($parts[1])) {
                    $filter->setSortDirection($parts[1]);
                }
            }
        }


        return $filter;
    }

    private function isStatus($segment)
    {
        $segment = $this->filterSegment($segment);

        if (strlen($segment)) {
            foreach (FeedbackFilter::$statuses as $status) {
                if ($status === substr($segment, 0, strlen($status))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $segment
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
     * @return array
     */
    private function getIntArrayFromCsv($parts)
    {
        $categories = explode(',', $parts);
        $categories = array_filter($categories, function ($val) {
            return (int)$val;
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

    private function isSort($segment)
    {
        $segment = $this->filterSegment($segment);

        $parts = $this->getSortParts($segment);


        if (count($parts) && strlen($parts[0])) {
            foreach (FeedbackFilter::$sorts as $sort) {
                if ($sort === substr($parts[0], 0, strlen($sort))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $segment
     * @return array
     */
    private function getSortParts($segment)
    {
        $parts = explode('-', $segment);

        if (count($parts) === 2) {
            if (!in_array($parts[1], array(
                FeedbackFilter::SORT_DIRECTION_DESC,
                FeedbackFilter::SORT_DIRECTION_ASC
            ))) {
                return array($parts[0] . '-' . $parts[1]);
            }
        }

        if (count($parts) > 2) {
            $parts = array(
                $parts[0] . '-' . $parts[1],
                $parts[2]
            );
            return $parts;
        }

        return $parts;
    }

    public function generateUriSegment(FeedbackFilter $filter)
    {
        $defaults = FeedbackFilter::getDefaultValues();

        // default requires no url
        if ($filter->toArray() == $defaults) {
            return '';
        }

        // we need to return something now
        $uri = '';

        if ($filter->getStatus() != $defaults['status']) {
            $uri .= '/' . $filter->getStatus();
        }

        if ($filter->getStatusCategories() != $defaults['status_categories']) {
            $uri .= sprintf('-%s', implode(',', $filter->getStatusCategories()));
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

        return strlen($uri) ? substr($uri, 1) : ''; // remove the leading "/"
    }
}
