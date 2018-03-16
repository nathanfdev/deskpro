<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\Attachments;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AttachmentCollectionType.
 */
class AttachmentCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'], 100);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit'], -1);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'], 100);

        if ($options['blob_auth_prototype']) {
            $prototype = $builder->create($options['prototype_name'], BlobAuthPrototypeType::class, []);
            $builder->setAttribute('prototype', $prototype->getForm());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'allow_add'           => true,
                'allow_delete'        => true,
                'label'               => false,
                'error_bubbling'      => false,
                'blob_auth_prototype' => false,
                'constraints'         => [
                    new AppAssert\UniqueCollection(['property' => 'blob']),
                ],
            ])
            ->setRequired(['person', 'entry_type', 'entry_options'])
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * Ensure there is a collection of attachments on the message (even if empty).
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data instanceof Collection) {
            $event->setData(new ArrayCollection());
        }
    }

    /**
     * Remove empty data.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var ArrayCollection $data */
        $data = $event->getData();
        foreach ($data as $key => $attachment) {
            if (!$attachment) {
                $data->remove($key);
            }
        }

        $event->setData(new ArrayCollection($data->getValues()));
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data     = $event->getData();
        $filtered = [];

        if (is_array($data)) {
            foreach ($data as $value) {
                if (is_array($value)) {
                    if (array_key_exists('delete', $value) && $value['delete']) {
                        continue;
                    }
                    if (isset($value['blob']) && $value['blob']) {
                        if (array_key_exists('upload', $value['blob']) && !$value['blob']['upload']) {
                            continue;
                        }
                    }
                }

                $filtered[] = $value;
            }

            $event->setData($filtered);
        }
    }
}
