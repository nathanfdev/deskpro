<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\CustomFields;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractCustomFieldsController.
 */
abstract class AbstractCustomFieldsController extends CrudController
{
    public static $exposeOnly   = ['list', 'get', 'put', 'delete', 'post'];
    public static $type         = CustomFieldType::class;
    public static $listPaginate = false;
    public static $listSort     = 'display_order';
    public static $listOrder    = 'asc';
    public static $sortOptions  = [
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
     * {@inheritdoc}
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
        $helper    = new CustomFields\Form\FormHelper($container->getEm(), $container->getFormFactory());

        $form = $helper->buildForm($model, $formData);
        $form->submit($formData);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $helper->saveFormToField($model, $formData);

        $view = View::create(!$isModify ? $this->wrap($model) : null, $status);
        $view->setLocation($this->getLocationUrl($model, $request));

        return $view;
    }
}
