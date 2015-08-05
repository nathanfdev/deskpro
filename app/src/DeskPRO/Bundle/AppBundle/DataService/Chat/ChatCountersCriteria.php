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
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService\Chat;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChatCountersCriteria
 */
class ChatCountersCriteria
{
    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var string
     */
    private $group_by;

    /**
     * ChatCountersCriteria constructor.
     *
     * @param array $filters
     * @param string $group_by
     */
    private function __construct(array $filters, $group_by)
    {
        $this->filters = $filters;
        $this->group_by = $group_by;
    }


    /**
     * @param Request $request
     * @return ChatCountersCriteria
     */
    public static function fromRequest(Request $request, OptionsResolver $resolver)
    {
        $params = $request->query->all();

        $group_by = null;
        if (array_key_exists('group_by', $params)) {
            $group_by = $params['group_by'];
            unset($params['group_by']);
        }

        $resolver->setDefined(['agent_id', 'department_id', 'date_created']);
        $resolver->setAllowedValues('agent_id', function($value) {
            return ctype_digit($value);
        });
        $resolver->setAllowedValues('department_id', function($value) {
            return ctype_digit($value);
        });
        $resolver->setAllowedValues('date_created', function($value) {
            return (bool) preg_match('/\d{4}\-\d{2}\-\d{2}\:\d{4}\-\d{2}\-\d{2}/', $value);
        });

        $filters = $resolver->resolve($params);

        return new self($filters, $group_by);
    }


}