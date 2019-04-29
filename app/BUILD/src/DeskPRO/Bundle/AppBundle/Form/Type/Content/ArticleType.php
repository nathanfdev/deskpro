<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Content;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleAttachment;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\Attachments\BaseAttachmentType;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\DateTimeType;
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
     * @var CustomFieldManager
     */
    private $fieldManager;

    /**
     * Constructor.
     *
     * @param CustomFieldManager $fieldManager
     */
    public function __construct(CustomFieldManager $fieldManager)
    {
        $this->fieldManager = $fieldManager;
    }

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
            ->add('fields', CombinedType::class, [
                'required'       => false,
                'forms'          => $this->getCustomDataFields($options),
                'error_bubbling' => false,
            ]);

        if ($options['with_review_date']) {
            $builder
                ->add('date_next_review', DateTimeType::class, [
                    'property_path' => 'date_next_review',
                    'widget'        => 'single_text',
                    'required'      => false,
                ]);
        }

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
                'data_class'       => Article::class,
                'agent_interface'  => false,
                'with_review_date' => false,
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

    /**
     * @param array $options
     *
     * @return array
     */
    private function getCustomDataFields(array $options)
    {
        $defs   = $this->fieldManager->getAvailableArticleDefs();
        $fields = [];

        foreach ($defs as $def) {
            $fields[] = [
                'name'    => $def->getId(),
                'type'    => CustomDataType::class,
                'options' => [
                    'custom_def'      => $def,
                    'property_path'   => 'custom_data',
                    'agent_interface' => $options['agent_interface'],
                    'inline'          => true,
                ],
            ];
        }

        return $fields;
    }
}
