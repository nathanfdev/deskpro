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
namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api;

use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\TextStringTransformer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CreateChatType.
 */
class CreateChatType extends AbstractType
{
    /**
     * @var EmailAccountManager
     */
    private $email_account_manager;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EmailAccountManager $email_account_manager
     * @param EntityManager       $em
     */
    public function __construct(EmailAccountManager $email_account_manager, EntityManager $em)
    {
        $this->email_account_manager = $email_account_manager;
        $this->em                    = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'api_chat_create';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'property_path' => 'person_name',
                'required'      => false,
            ])
            ->add('email', 'email', [
                'property_path' => 'person_email',
                'required'      => false,
                'constraints'   => [
                    new Assert\Email(),
                ],
            ])
        ;

        $builder->get('name')->addModelTransformer(new TextStringTransformer());
        $builder->get('email')->addModelTransformer(new TextStringTransformer());
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection'               => false,
            'csrf_double_submit_protection' => false,
        ]);
    }

    /**
     * Check for existing person and assign to chat.
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form  = $event->getForm();
        $data  = $event->getData();
        $email = !empty($data['email']) ? $data['email'] : null;

        /** @var \Application\DeskPRO\EntityRepository\Person $repository */
        $repository = $this->em->getRepository('DeskPRO:Person');
        $person     = $repository->findOneByEmail($email);

        if (!$this->email_account_manager->findAccountForEmailAddress($email) && $person) {
            $form->add('person', 'entity', [
                'class' => 'DeskPRO:Person',
            ]);
            $event->setData(array_merge($event->getData(), [
                'person' => $person->getId(),
            ]));
        }
    }
}
