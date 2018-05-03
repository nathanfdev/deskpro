<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\DpCaptchaType;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Form\Captcha\CaptchaDecider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TicketReplyType.
 */
class ShareArticleType extends AbstractType
{
    /**
     * @var CaptchaDecider
     */
    private $captcha_decider;

    /**
     * @var LanguageManager
     */
    private $language_manager;

    /**
     * Constructor.
     *
     * @param CaptchaDecider  $captcha_decider
     * @param LanguageManager $language_manager
     */
    public function __construct(CaptchaDecider $captcha_decider, LanguageManager $language_manager)
    {
        $this->captcha_decider  = $captcha_decider;
        $this->language_manager = $language_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label'       => $this->language_manager->phrase('portal.forms.label_email'),
                'constraints' => [
                    new Email(),
                ],
            ])
            ->add('name', TextType::class, [
                'label'       => $this->language_manager->phrase('portal.forms.label_name'),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label'       => $this->language_manager->phrase('portal.forms.label_message'),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('send_myself', CheckboxType::class, [
                'label' => $this->language_manager->phrase('portal.forms.label_send_to_myself'),
            ])
            ->add('submit', 'submit', [
                'label' => $this->language_manager->phrase('portal.general.btn-send-email'),
            ]);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();

                if ($this->captcha_decider->shouldRequireShareCaptchaForCurrentPerson()) {
                    $form->add('captcha', DpCaptchaType::class);
                }
            }
        );
    }

    public function getName()
    {
        return 'share_article';
    }
}
