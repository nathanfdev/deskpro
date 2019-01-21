<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Content;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleAttachment;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Type\Attachments\BaseAttachmentType;
use DeskPRO\Bundle\AppBundle\Form\Type\ObjectLang\ObjectLangCollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ArticleType.
 */
class ArticleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title_translations', ObjectLangCollectionType::class, [
                'mapped'    => false,
                'prop_name' => 'title',
                'owner'     => $builder->getData(),
                'required'  => false,
            ])
            ->add('content_translations', ObjectLangCollectionType::class, [
                'mapped'    => false,
                'prop_name' => 'content',
                'owner'     => $builder->getData(),
                'required'  => false,
            ])
            ->add('categories', EntityType::class, [
                'class'    => ArticleCategory::class,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('attachments', CollectionType::class, [
                'entry_type'   => BaseAttachmentType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required'     => false,
                'options'      => [
                    'data_class' => ArticleAttachment::class,
                    'person'     => $options['person'],
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'preSetData']);
    }

    public function preSetData(FormEvent $event)
    {
        $article = $event->getData();
        foreach ($article->getAttachments() as $attachment) {
            $attachment->setArticle($article);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Article::class,
            ])

            ->setRequired('person')
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ContentAbstractType::class;
    }
}
