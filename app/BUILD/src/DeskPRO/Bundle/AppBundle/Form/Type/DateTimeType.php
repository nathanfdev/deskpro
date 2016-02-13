<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Validator\Constraints\DpDate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class DateTimeType.
 */
class DateTimeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'deskpro_datetime';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'datetime';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $current_date = new \DateTime();
        $current_year = (int) $current_date->format('Y');

        $resolver->setDefaults([
            'years'       => range(($current_year - 100), ($current_year + 100)),
            'placeholder' => '',
            'help'        => '',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['weekdays'] = null;
        $view->vars['min_date'] = null;
        $view->vars['max_date'] = null;

        if (array_key_exists('constraints', $options)) {
            foreach ($options['constraints'] as $constraint) {
                if ($constraint instanceof DpDate) {
                    $view->vars['weekdays'] = implode(',', $constraint->days_of_week);
                    $view->vars['min_date'] = $this->formatDate($constraint->min_date);
                    $view->vars['max_date'] = $this->formatDate($constraint->max_date);
                }
            }
        }
    }

    /**
     * @param mixed $date
     *
     * @return string
     */
    private function formatDate($date)
    {
        if ($date instanceof \DateTime) {
            return $date->format('Y m d');
        }

        return '';
    }
}
