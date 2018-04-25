<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Content;

use Application\DeskPRO\Entity\CategoryAbstract;
use Application\DeskPRO\Entity\Guide;
use DeskPRO\Component\Util\TypeUtils;
use Orb\Util\Strings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Use this CategorySlugManager service to get category by slug string.
 */
class CategorySlugManager
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function getEm()
    {
        return $this->container->get('doctrine.orm.default_entity_manager');
    }

    /**
     * When you give this a category object, it assumes the "title" is correct.
     *
     * It then ensures that the slug that is set on the object is correct,
     * if it is not correct, it will find something valid AND SET IT on the content.
     *
     * @param CategoryAbstract|Guide $category
     *
     * @throws \Exception
     *
     * @return null|void
     */
    public function ensureValidSlug($category)
    {
        if (!$category instanceof CategoryAbstract && !$category instanceof Guide) {
            throw new \Exception('Category must be CategoryAbstract or Guide');
        }
        $expectedSlug = $this->slugifyTitle($category->getTitle());

        if ($expectedSlug === '') {
            $expectedSlug = strtolower(str_replace('Category', '', TypeUtils::getBaseTypeName($category)));
        }

        // check if the expected slug is a valid one, in both content repo and in slug history repo
        $newSlug = $expectedSlug;
        $i       = 1;
        while (!$this->isValidSlug($newSlug, $category)) {
            // if expected slug is not valid, keep incrementing a value at the end until we get something valid
            $newSlug = sprintf('%s-%d', $this->slugifyTitle($category->getTitle()) ?: strtolower(str_replace('Category', '', TypeUtils::getBaseTypeName($category))), ++$i);
        }

        return $category->setSlug($newSlug);
    }

    /**
     * @param $title
     *
     * @return string
     */
    private function slugifyTitle($title)
    {
        return substr(Strings::slugifyTitle($title), 0, 200) ?: '';
    }

    /**
     * Given a slug string and a class name of the entity, return the content object if it can be found.
     *
     * This method is aware of slug history.
     *
     * @param $slug
     * @param $categoryClassName
     *
     * @return null|object
     */
    public function findCategoryObjectBySlug($slug, $categoryClassName)
    {
        $contentRepo = $this->getEm()->getRepository($categoryClassName);
        if ($content = $contentRepo->findOneBy(['slug' => $slug])) {
            return $content;
        }

        return;
    }

    protected function isValidSlug($newSlug, $category)
    {
        if ($contentObject = $this->getContentBySlug($newSlug, $category)) {
            // valid if it is the current slug (should be covered already in ensureValidSlug, here for sanity)
            return $contentObject->getId() === $category->getId();
        }

        return true;
    }

    protected function getContentBySlug($newSlug, $category)
    {
        return $this->getRepoForContent($category)->findOneBy(['slug' => $newSlug]);
    }

    /**
     * @param CategoryAbstract|Guide $category
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepoForContent($category)
    {
        $class = get_class($category);

        return $this->getEm()->getRepository($class);
    }
}
