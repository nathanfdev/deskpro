<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\DpCaptchaType;
use DeskPRO\Bundle\AppBundle\Form\Type\Comments\CommentAttachmentCollectionType;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Form\Captcha\CaptchaDecider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class CommentType.
 */
class CommentType extends AbstractType
{
    /**
     * @var CaptchaDecider
     */
    private $captchaDecider;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * Constructor.
     *
     * @param CaptchaDecider  $captchaDecider
     * @param LanguageManager $languageManager
     */
    public function __construct(CaptchaDecider $captchaDecider, LanguageManager $languageManager)
    {
        $this->captchaDecider  = $captchaDecider;
        $this->languageManager = $languageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content_real', TextareaType::class, [
            'label'       => $this->languageManager->phrase('portal.forms.label_comment'),
            'constraints' => [
                new NotBlank(),
            ],
        ])
            ->add('parent_id', HiddenType::class)

            ->add('attachments', CommentAttachmentCollectionType::class, [
                'person'  => $options['person'],
                'comment' => $builder->getData(),
            ])
            ->add('more_attachments', SubmitType::class, [
                'validation_groups' => false,
                'label'             => $this->languageManager->phrase('portal.forms.label_add_attachment'),
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $comment = $event->getData();
            $form    = $event->getForm();

            // if this is a guest, ask for more information
            if ($comment->getPerson() instanceof PersonGuest) {
                $form->add('name', TextType::class, [
                    'label'       => $this->languageManager->phrase('portal.forms.label_full_name'),
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]);
                $form->add('email', EmailType::class, [
                    'label'       => $this->languageManager->phrase('portal.forms.label_email'),
                    'constraints' => [
                        new NotBlank(),
                        new Email(),
                    ],
                ]);
            }

            if ($this->captchaDecider->shouldRequireCommentCaptchaForCurrentPerson()) {
                $form->add('captcha', DpCaptchaType::class);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => CommentAbstract::class,
            ])
            ->setRequired('person')
            ->setAllowedTypes('person', Person::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'comment';
    }
}
