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
 */

namespace DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformer;

use DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformer\AbstractDataSerializerTransformer;
use DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformerRequest;
use DeskPRO\Bundle\ApiBundle\DataSerializer\PropertyTransformer\Callback\CallbackDeferredProperty;
use Doctrine\DBAL\Connection;

class TaskTransformer extends AbstractDataSerializerTransformer
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var int[]
     */
    private $count_ids;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->count_ids = [];
    }

    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return [
            'id',
            'title',
            'is_done',
            'percent_complete',
            'date_created',
            'task_type',
            'date_due',
            'date_event_start',
            'date_event_end',
            'creator',
            'visibility',
            'project',
            'list',
            'urgency',
            'assigned',
            'linked_items',
        ];
    }

    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\Entity\Task $data */
        $data = $transformation_request->getDataToBeTransformed();

        $labels = [];

        if (!empty($data->getLabels())) {
            foreach ($data->getLabels() as $label) {
                $labels[] = $label->getLabel();
            }
        }

        $id = $data->getId();
        $this->count_ids[] = $id;

        return [
            'labels' => $labels,
            'some_count' => new CallbackDeferredProperty(
                [$this, 'getCount'],
                [$id]
            )
        ];
    }

    public function getCount($id)
    {
        // the callbacks won't be called until after all of thee "CallbackDeferredProperty" are set
        // which means we now have an array of all of the IDs we will want in $ths->count_ids

        return 5;
    }
}
