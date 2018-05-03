<?php

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
