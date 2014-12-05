<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Form\Type;

use Application\FormBundle\Form\DataTransformer\BlobTypeModelTransformer;
use Application\FormBundle\Form\DataTransformer\BlobTypeViewTransformer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BlobType extends AbstractType
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onChange'));
        $builder->addEventListener(FormEvents::SUBMIT, array($this, 'onChange'));

        $builder->addModelTransformer(new BlobTypeModelTransformer($this->em->getRepository('DeskPRO:Blob')));
//        $builder->addViewTransformer(new BlobTypeViewTransformer($this->em->getRepository('DeskPRO:Blob')));
    }

    public function onChange(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Blob $blob */
        $blob = $event->getData();
        $form = $event->getForm();

        if (!$blob) {
            $form->add('upload', 'file', array(
                'mapped' => false
            ));

            if ($form->has('delete_blob')) {
                $form->remove('delete_blob');
            }

            if ($form->has('blob_auth')) {
                $form->remove('blob_auth');
            }

            return;
        }

        $form->add('delete_blob', 'checkbox');
        $form->add('blob_auth', 'hidden');

        if ($form->has('upload')) {
            $form->remove('upload');
        }
    }

    public function getName()
    {
        return 'deskpro_blob';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Application\\DeskPRO\\Entity\\Blob'
            )
        );
    }
}
 