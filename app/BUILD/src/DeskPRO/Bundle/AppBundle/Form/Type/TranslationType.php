<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\EntityRepository\Language as LanguageRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TranslationType.
 */
class TranslationType extends AbstractType
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
        /** @var LanguageRepository $languageRepository */
        $languageRepository = $this->em->getRepository(Language::class);
        $translations       = $builder->create('translations', FormType::class);
        /** @var Language $language */
        foreach ($languageRepository->findAll() as $language) {
            $translations->add($language->getLocale());
        }
        $builder->add($translations);
    }
}
