<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DirectMessageNewThreadType.
 */
class DirectMessageNewThreadType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, TokenStorage $tokenStorage)
    {
        $this->em           = $em;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label'       => 'Email',
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(['strict' => true]),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label'       => 'Message',
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
            ])
        ;

        $builder->get('message')
            ->addModelTransformer(new CallbackTransformer(
                function ($value) {
                    return $value;
                },
                function ($value) {
                    return nl2br($value);
                }
            ))
        ;

        $builder->get('email')->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'], 100);
    }

    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        /** @var \Application\DeskPRO\EntityRepository\Person $personRepository */
        $personRepository = $this->em->getRepository(Person::class);

        if (!empty($data)) {
            $person = $personRepository->findOneByEmail($data);
            if (!$person) {
                $form->addError(new FormError(ErrorsCodes::NO_PERSON, null, ['value' => $data]));
            } elseif ($person->isAgent()) {
                $form->addError(new FormError(ErrorsCodes::NOT_USER, null, ['value' => $data]));
            }

            if ($person) {
                $currentUser = $this->tokenStorage->getToken()->getUser();
                if ($currentUser instanceof Person && $currentUser->getId() == $person->getId()) {
                    $form->addError(new FormError('dm_message_to_self'));
                }
            }
        }
    }
}
