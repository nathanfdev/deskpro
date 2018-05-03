<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Webhooks;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TriggerActionsFormType extends AbstractType implements ContainerAwareInterface
{

    /**
     * @var \Application\DeskPRO\Tickets\Actions\ActionDef\TicketActionDefManager
     */
    private $ticketActionsManager;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit'], -1);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['allow_extra_fields' => true]);
    }

    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $form->getData();

        $extraData = $form->getExtraData();
        $actions = new TriggerActions();

        if (! array_key_exists('actions', $extraData)) {
            $event->setData($actions);
            return;
        }

        $actionList = $extraData['actions'];
        foreach ($actionList as $act) {
            if ($act) {
                $type = $act['type'];

                if (isset($act['DP_DISABLED'])) {
                    continue;
                }

                if ($this->ticketActionsManager->hasNamedDef($type)) {
                    $act['type_class'] = $this->ticketActionsManager
                        ->getNamedDef($type)
                        ->getDef()
                        ->getTriggerActionClass()
                    ;
                    if (!$act['type_class']) {
                        continue;
                    }
                }

                try {
                    $actions->addActionFromArray($act);
                } catch (\Exception $e) {}
            }
        }

        $event->setData($actions);
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer( ContainerInterface $container = null )
    {
        if (!is_null($container) && $container instanceof DeskproContainer) {
            $this->ticketActionsManager = $container->getTicketActionDefManager();
        }
    }
}
