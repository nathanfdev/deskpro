<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\UserChat;

use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\EntityToIdTransformer;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserChatQueueSettingsType.
 */
class UserChatQueueSettingsType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('agent_timeout', IntegerType::class, [
                'required'    => false,
                'constraints' => [
                    new Assert\Range(['min' => 10]),
                ],
            ])
            ->add('default_queue', EntityType::class, [
                'required' => false,
                'class'    => UserChatQueue::class,
            ])
            ->add('max_chats_count', IntegerType::class, [
                'required'    => false,
                'constraints' => [
                    new Assert\Range(['min' => 1]),
                ],
            ])
        ;

        $builder->get('default_queue')->addModelTransformer(
            new ReversedTransformer(new EntityToIdTransformer($this->em->getRepository(UserChatQueue::class)))
        );
    }
}
