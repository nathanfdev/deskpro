<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Application\DeskPRO\CustomFields;

/**
 * Class AbstractCustomFieldsController.
 */
abstract class AbstractCustomFieldsController extends CrudController
{
    public static $exposeOnly  = ['list', 'get', 'put', 'delete', 'post'];
    public static $type        = CustomFieldType::class;
    public static $listSort    = 'display_order';
    public static $listOrder   = 'asc';
    public static $sortOptions = [
        'display_order' => 'display_order',
    ];

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb->andWhere("$alias.parent is null");

        $isEnabled = $request->query->getInt('is_enabled', 1);
        if ($isEnabled !== -1) {
            $qb->andWhere("$alias.is_enabled = :is_enabled");
            $qb->setParameter('is_enabled', $isEnabled);
        }
    }

    /**
     * @param CustomDefAbstract  $model
     * @param Request $request
     * @param array   $options
     *
     * @throws InvalidFormException
     *
     * @return View
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $isModify = $model && $model->getId();
        $status   = $isModify ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        // empty put requests
        $formData = $request->request->all();
        if ($isModify && empty($formData)) {
            $view = View::create(null, $status);
            $view->setLocation($this->getLocationUrl($model, $request));
            return $view;
        }

        $container = $this->getContainer();
        $helper = new CustomFields\Form\FormHelper($container->getEm(), $container->getFormFactory());
        $helper->saveFormToField($model, $request->request->all());

        $view = View::create(!$isModify ? $this->wrap($model) : null, $status);
        $view->setLocation($this->getLocationUrl($model, $request));

        return $view;
    }
}
