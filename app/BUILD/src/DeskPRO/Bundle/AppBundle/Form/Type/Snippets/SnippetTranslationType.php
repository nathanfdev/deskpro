<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Snippets;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Entity\SnippetTranslation;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SnippetTranslationType extends AbstractType
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('language', EntityType::class, [
                'class' => Language::class,
            ])
            ->add('content', TextType::class, [
                'filter_clean' => false,
            ])
            ->add('blobs', EntityType::class, [
                'class'    => Blob::class,
                'multiple' => true,
            ])
            ->add('snippet', EntityType::class, [
                'class' => Snippet::class,
            ])
            ->add('type', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => [Snippet::TYPE_CHAT, Snippet::TYPE_TICKET],
            ])
            ->add('id', TextType::class, [
                'mapped'   => false,
                'required' => false,
            ])
        ;
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('snippet')
            ->setDefaults([
                'data_class' => SnippetTranslation::class,
            ])
            ->setAllowedTypes('snippet', Snippet::class)
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $form->getData();

        if ($data instanceof SnippetTranslation) {
            $data->setSnippet($form->getConfig()->getOption('snippet'));
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        /** @var SnippetTranslation $snippetTranslation */
        $snippetTranslation = $event->getData();

        if (!is_object($snippetTranslation)) {
            return;
        }

        $imagesAuth = [];
        if (preg_match_all('|file\.php/([A-Z0-9]+)/|', $snippetTranslation->getContent(), $imagesAuth)) {
            $blobRepository = $this->em->getRepository(Blob::class);
            foreach ($imagesAuth[1] as $imageAuth) {
                $blob = $blobRepository->getByAuthCode($imageAuth);
                if ($blob instanceof Blob && $blob->getId()) {
                    $blob->setIsTemp(false);
                }
            }
        }

        $blobs = $snippetTranslation->getBlobs();
        foreach ($blobs as $blob) {
            if ($blob instanceof Blob && $blob->getId()) {
                $blob->setIsTemp(false);
            }
        }
    }
}
