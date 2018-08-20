<?php

namespace Application\DeskPRO\Form\Type\CustomFields;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\From;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType as BaseTextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContextualChoiceType.
 */
class ContextualChoiceType extends ChoiceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('custom_choice', BaseTextType::class, [
            'required' => false,
            'label'    => false,
            'mapped'   => false,
            'attr'     => [
                'placeholder' => 'Custom choice',
                'style'       => 'display:none;',
            ],
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetCustomChoice'], 200);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(['context']);
    }

    /**
     * @param EntityRepository $er
     * @param array            $options
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getChoicesQueryBuilder(EntityRepository $er, array $options)
    {
        /** @var DomainObject $ctx */
        $ctx = $options['context'];
        $def = $this->definition;

        return $er
            ->createQueryBuilder('d')
            ->add('from', new From(CustomFieldDefinition::class, 'd', 'd.id'), false)
            ->where('d.parent = :parent')
            ->andWhere('d.owner_class = :owner_class and d.context_class = :cc and d.context_id = :cid')
            ->orderBy('d.display_order', 'ASC')
            ->setParameter('parent', $def['id'])
            ->setParameter('owner_class', $def['owner_class'])
            ->setParameter('cc', ClassUtils::getClass($ctx))
            ->setParameter('cid', $ctx['id'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cf_contextual_choice';
    }

    /**
     * @param FormEvent $event
     */
    public function onSetCustomChoice(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!empty($data['custom_choice'])) {
            $choices = $form->get('value')->getConfig()->getOption('choice_list')->getChoices();
            $this->handleCustomChoice($form, $choices, $data);
            $form->remove('value');
            $form->add('value', EntityType::class, array_merge($this->getValueOptions(), [
                'class'   => CustomFieldDefinition::class,
                'choices' => $choices,
            ]));

            $event->setData($data);
        }
    }

    /**
     * select choice or add new if not exist.
     *
     * @param FormInterface $form
     * @param array         $choices
     * @param               $data
     */
    protected function handleCustomChoice(FormInterface $form, array &$choices, &$data)
    {
        if (empty($data['custom_choice'])) {
            return;
        }

        $check  = strtolower($data['custom_choice']);
        $newVal = isset($choices[$data['custom_choice']]) ? $choices[$data['custom_choice']] : null;

        // first, string comparison
        if (!$newVal) {
            foreach ($choices as $choice) {
                /* @var CustomFieldDefinition $choice */
                if (strtolower($choice['title']) === $check) {
                    $newVal = $choice['id'];
                }
            }
        }

        // then, add new choice to list
        if (!$newVal) {
            $newDef            = clone $this->definition;
            $newDef['id']      = null;
            $newDef->parent    = $this->definition;
            $newDef->children  = new ArrayCollection();
            $newDef['title']   = $data['custom_choice'];
            $newDef['options'] = [];

            if ($context = $form->getConfig()->getOption('context')) {
                $newDef['context_id'] = $context['id'];
            }

            $form->get('value')->getConfig()->getOption('em')->persist($newDef);
            $form->get('value')->getConfig()->getOption('em')->flush();
            $newVal           = $newDef['id'];
            $choices[$newVal] = $newDef;
        }

        if ($newVal && $form->get('value')->getConfig()->getOption('multiple')) {
            $newVal = (array) $newVal;
        }

        $data['value'] = $newVal;
        unset($data['custom_choice']);
    }
}
