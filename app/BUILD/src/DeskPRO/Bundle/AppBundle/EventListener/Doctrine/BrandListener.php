<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\BrandBundle\Brand\DefaultBrandFinder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Orb\Util\Strings;

/**
 * Class BrandListener.
 */
class BrandListener
{
    /**
     * @var \DeskPRO\Bundle\BrandBundle\Brand\DefaultBrandFinder
     */
    private $defaultBrandFinder;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param \DeskPRO\Bundle\BrandBundle\Brand\DefaultBrandFinder $defaultBrandFinder
     * @param EntityManager                                        $em
     */
    public function __construct(DefaultBrandFinder $defaultBrandFinder, EntityManager $em)
    {
        $this->defaultBrandFinder = $defaultBrandFinder;
        $this->em                 = $em;
    }

    /**
     * @internal
     *
     * @param Brand $entity
     */
    public function prePersist(Brand $entity)
    {
        if (!$entity->getSlug()) {
            $entity->setSlug($this->slugifyName($entity));
        }

        $this->validateSlug($entity);
        $this->ensureUniqueSlug($entity);
        $this->unsetEmptyUrl($entity);
    }

    /**
     * @internal
     *
     * @param Brand              $entity
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(Brand $entity, PreUpdateEventArgs $args)
    {
        // we updated name but not slug
        // update slug as well based on the new brand name
        if (!$entity->getSlug()
            || (!$args->hasChangedField('slug') && $args->hasChangedField('name') && $entity->getName())
        ) {
            $entity->setSlug($this->slugifyName($entity));
        }

        $this->validateSlug($entity);
        $this->ensureUniqueSlug($entity);
        $this->unsetEmptyUrl($entity);
    }

    /**
     * @internal
     *
     * @param Brand $entity
     */
    public function preRemove(Brand $entity)
    {
        $defaultBrand = $this->defaultBrandFinder->getDefaultBrand();
        if (!$defaultBrand) {
            return;
        }

        $defaultTicketDepartments = $defaultBrand->getTicketDepartments();
        $defaultChatDepartments   = $defaultBrand->getChatDepartments();

        // re-assign related tickets to the default brand
        if (count($defaultTicketDepartments)) {
            // just change brand w/o changing department
            $qb = $this->em->createQueryBuilder();
            $qb
                ->update(Ticket::class, 't')
                ->set('t.brand', ':default_brand')
                ->where(
                    't.brand = :deleting_brand',
                    't.department IN (:default_departments)'
                )
                ->setParameter('default_brand', $defaultBrand)
                ->setParameter('deleting_brand', $entity)
                ->setParameter('default_departments', $defaultTicketDepartments)
                ->getQuery()
                ->execute()
            ;
        }

        // modify department to first default one as well
        $defaultDepartment = $defaultTicketDepartments->first();
        if ($defaultDepartment) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->update(Ticket::class, 't')
                ->set('t.brand', ':default_brand')
                ->set('t.department', ':default_department')
                ->where('t.brand = :deleting_brand')
                ->setParameter('default_brand', $defaultBrand)
                ->setParameter('deleting_brand', $entity)
                ->setParameter('default_department', $defaultTicketDepartments->first())
                ->getQuery()
                ->execute()
            ;
        }

        // re-assign related chat to the default brand
        if (count($defaultChatDepartments)) {
            // just change brand w/o changing department
            $qb = $this->em->createQueryBuilder();
            $qb
                ->update(ChatConversation::class, 'c')
                ->set('c.brand', ':default_brand')
                ->where(
                    'c.brand = :deleting_brand',
                    'c.department IN (:default_departments)'
                )
                ->setParameter('default_brand', $defaultBrand)
                ->setParameter('deleting_brand', $entity)
                ->setParameter('default_departments', $defaultChatDepartments)
                ->getQuery()
                ->execute()
            ;
        }

        // modify department to first default one as well
        $defaultDepartment = $defaultChatDepartments->first();
        if ($defaultDepartment) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->update(ChatConversation::class, 'c')
                ->set('c.brand', ':default_brand')
                ->set('c.department', ':default_department')
                ->where('c.brand = :deleting_brand')
                ->setParameter('default_brand', $defaultBrand)
                ->setParameter('deleting_brand', $entity)
                ->setParameter('default_department', $defaultDepartment)
                ->getQuery()
                ->execute()
            ;
        }
    }

    /**
     * @param Brand $entity
     */
    private function ensureUniqueSlug(Brand $entity)
    {
        $originalSlug = $entity->getSlug();
        if (!$originalSlug) {
            $originalSlug = $this->slugifyName($entity);
        }

        $i    = 1;
        $slug = $originalSlug;
        while (!$this->isSlugUnique($slug, $entity)) {
            // if expected slug is not valid, keep incrementing a value at the end until we get something valid
            $slug = sprintf('%s-%d', $originalSlug, ++$i);
        }

        $entity->setSlug($slug);
    }

    /**
     * @param Brand $entity
     *
     * @return string
     */
    private function slugifyName(Brand $entity)
    {
        $name = $entity->getName();
        if (!$name && $entity->getId()) {
            $name = 'Brand '.$entity->getId();
        }

        return substr(Strings::slugifyTitle($name), 0, 94) ?: '';
    }

    /**
     * @param string $newSlug
     * @param Brand  $entity
     *
     * @return bool
     */
    private function isSlugUnique($newSlug, Brand $entity)
    {
        $existingBrand = $this->em->getRepository(Brand::class)->findOneBy([
            'slug' => $newSlug,
        ]);

        if ($existingBrand && $entity !== $existingBrand) {
            return false;
        }

        return true;
    }

    /**
     * @param Brand $entity
     */
    private function validateSlug(Brand $entity)
    {
        $entity->setSlug(Strings::slugifyTitle($entity->getSlug()));
    }

    /**
     * @param Brand $entity
     */
    private function unsetEmptyUrl(Brand $entity)
    {
        // if no url then force set it to NULL to prevent unique constraint errors
        if (!$entity->getUrl()) {
            $entity->setUrl(null);
        }
    }
}
