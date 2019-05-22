<?php

namespace DeskPRO\Bundle\VoiceBundle\Form\Type;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\EntityToIdTransformer;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceGlobalAgentSettingsType.
 */
class VoiceSettingsType extends AbstractType
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
            ->add('agent_voicemail_timeout', IntegerType::class, [
                'required'    => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThanOrEqual(10),
                ],
            ])
            ->add('agent_default_department', EntityType::class, [
                'required'      => false,
                'class'         => Department::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')->where('u.is_tickets_enabled = 1');
                },
            ])
            ->add('agent_default_brand', EntityType::class, [
                'class'    => Brand::class,
                'required' => false,
            ])
            ->add('group_missed_call_tickets', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('group_missed_call_tickets_timeout', IntegerType::class, [
                'required' => false,
            ])
        ;

        $builder->get('agent_default_department')->addModelTransformer(
            new ReversedTransformer(new EntityToIdTransformer($this->em->getRepository(Department::class)))
        );
        $builder->get('agent_default_brand')->addModelTransformer(
            new ReversedTransformer(new EntityToIdTransformer($this->em->getRepository(Brand::class)))
        );
    }
}
