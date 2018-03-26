<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class DateType.
 */
class DpDateType extends AbstractType
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
        return DateType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['widget'] === 'single_text') {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onParseDateTime']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $currentDate = new \DateTime();
        $currentYear = (int) $currentDate->format('Y');

        /** @var \Application\DeskPRO\Entity\Person $user */
        $token = $this->tokenStorage->getToken();
        $user  = $token ? $token->getUser() : null;

        $resolver
            ->setDefaults([
                'years'         => range(($currentYear - 100), ($currentYear + 100)),
                'placeholder'   => '',
                'calendar'      => 'gregorian',
                'weekdays'      => [0, 1, 2, 3, 4, 5, 6],
                'min_date'      => null,
                'max_date'      => null,
                'view_timezone' => $user instanceof Person ? $user->getTimezone() : 'UTC',
            ])
            ->setAllowedTypes('weekdays', ['array', 'null'])
            ->setAllowedValues('calendar', ['gregorian', 'hijri', null])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['calendar'] = $options['calendar'];
        $view->vars['weekdays'] = $options['weekdays'] ? implode(',', $options['weekdays']) : null;
        $view->vars['min_date'] = $options['min_date'];
        $view->vars['max_date'] = $options['max_date'];
    }

    /**
     * Transform datetime string to date string.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onParseDateTime(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data) {
            return;
        }

        try {
            $data = new \DateTime($data);
            $data = $data->format('Y-m-d');

            $event->setData($data);
        } catch (\Exception $e) {
            // unable to parse, leave as is
        }
    }
}
