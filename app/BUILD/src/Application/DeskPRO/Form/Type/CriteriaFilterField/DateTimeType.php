<?php

namespace Application\DeskPRO\Form\Type\CriteriaFilterField;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class DateTimeType extends AbstractType
{
    public function getName()
    {
        return 'criteria_filter_datetime';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['rule_handler'] = ' data-rule-handler="DeskPRO.Agent.RuleBuilder.DateTimeTerm"';
    }
}
