<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\Task;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class LinkedItemsType extends CollectionType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['allow_add'] && $options['prototype']) {
            $prototypeOptions = array_replace(array(
                'required' => $options['required'],
                'label' => $options['prototype_name'].'label__',
            ), $options['entry_options']);

            if (null !== $options['prototype_data']) {
                $prototypeOptions['data'] = $options['prototype_data'];
            }

            $prototype = $builder->create($options['prototype_name'], $options['entry_type'], $prototypeOptions);
            $builder->setAttribute('prototype', $prototype->getForm());
        }

        $resizeListener = new LinkedItemsFormListener(
            $options['entry_type'],
            $options['entry_options']
        );

        $builder->addEventSubscriber($resizeListener);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'linked_items';
    }
}
