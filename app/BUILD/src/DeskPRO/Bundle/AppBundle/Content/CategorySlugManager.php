<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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

    protected function isValidSlug($newSlug, CategoryAbstract $category)
    {
        if ($contentObject = $this->getContentBySlug($newSlug, $category)) {
            // valid if it is the current slug (should be covered already in ensureValidSlug, here for sanity)
            return $contentObject->getId() === $category->getId();
        }

        return true;
    }

    protected function getContentBySlug($newSlug, CategoryAbstract $category)
    {
        return $this->getRepoForContent($category)->findOneBy(['slug' => $newSlug]);
    }

    /**
     * @param CategoryAbstract $category
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepoForContent(CategoryAbstract $category)
    {
        $class = get_class($category);

        return $this->getEm()->getRepository($class);
    }
}
