<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\OutputEntity;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\LabelTask;
use DeskPRO\Bundle\AppBundle\Form\Type\TaskLabelType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TaskLabelsController.
 *
 * @Annotations\Route("/task_labels")
 * @ApiModes("all")
 * @ApiDocSection("Tasks")
 * @OutputEntity("DeskPRO\Bundle\AppBundle\Entity\LabelTask")
 */
class TaskLabelsController extends CrudController
{
    public static $entity    = LabelTask::class;
    public static $type      = TaskLabelType::class;
    public static $listSort  = 'label';
    public static $listOrder = 'asc';

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $group = $request->query->get('group', false);

        if (!empty($group)) {
            $qb->groupBy("$alias.label");
        }
        parent::applyListFilters($qb, $alias, $request);
    }

    /**
     * {@inheritdoc}
     */
    public function instantiateEntity(Request $request)
    {
        $label = new LabelTask($this->getUser());

        return $label;
    }
}
