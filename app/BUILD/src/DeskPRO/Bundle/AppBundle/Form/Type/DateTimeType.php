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

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as BaseDateTimeType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class DateTimeType.
 */
class DateTimeType extends AbstractType
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param TokenStorage $tokenStorage
     */
    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseDateTimeType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $current_date = new \DateTime();
        $current_year = (int) $current_date->format('Y');

        /** @var \Application\DeskPRO\Entity\Person $user */
        $token = $this->tokenStorage->getToken();
        $user  = $token ? $token->getUser() : null;

        $resolver
            ->setDefaults([
                'years'         => range(($current_year - 100), ($current_year + 100)),
                'placeholder'   => '',
                'help'          => '',
                'weekdays'      => [0, 1, 2, 3, 4, 5, 6],
                'min_date'      => null,
                'max_date'      => null,
                'view_timezone' => $user instanceof Person ? $user->getTimezone() : 'UTC',
            ])
            ->setAllowedTypes('weekdays', ['array', 'null'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['weekdays'] = $options['weekdays'] ? implode(',', $options['weekdays']) : null;
        $view->vars['min_date'] = $options['min_date'];
        $view->vars['max_date'] = $options['max_date'];
    }
}
