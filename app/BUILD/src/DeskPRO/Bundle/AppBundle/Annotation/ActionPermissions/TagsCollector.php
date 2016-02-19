<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions;

use Application\DeskPRO\Entity\ApiKey;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\ActionPermissionsMetadataFactory;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\MethodMetadata;
use DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyAction;
use DeskPRO\Bundle\AppBundle\Security\Authorization\ActionPermissionsHelper;
use Doctrine\ORM\EntityManager;
use Gnugat\NomoSpaco\File\FileRepository;
use Gnugat\NomoSpaco\FqcnRepository;
use Gnugat\NomoSpaco\Token\ParserFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TagsCollector.
 */
class TagsCollector
{
    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var ActionPermissionsHelper
     */
    protected $helper;

    /** @var ActionPermissionsMetadataFactory */
    protected $factory;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param ActionPermissionsHelper          $helper
     * @param ActionPermissionsMetadataFactory $factory
     * @param Entitymanager                    $em
     */
    public function __construct(
        ActionPermissionsHelper $helper,
        ActionPermissionsMetadataFactory $factory,
        EntityManager $em
    ) {
        $this->helper  = $helper;
        $this->factory = $factory;
        $this->em      = $em;
    }

    /**
     * @return array
     */
    public function getClasses()
    {
        $fqcn_repo = new FqcnRepository(new FileRepository(), new ParserFactory());

        return array_merge(
            @$fqcn_repo->findIn(DP_ROOT.'/src/DeskPRO/Bundle/ApiBundle/Controller'),
            @$fqcn_repo->findIn(DP_ROOT.'/src/Application/LegacyApiBundle/Controller')
        );
    }

    /**
     * @param $tags
     */
    public function addTags($tags)
    {
        $this->tags = array_merge($this->tags, $tags);
    }

    /**
     *
     */
    public function unique()
    {
        $this->tags = array_unique($this->tags);
    }

    /**
     * @return array
     */
    public function getTagsHierarchy()
    {
        $this->unique();
        $tags_hierarhy = call_user_func_array('array_merge_recursive', array_map([$this->helper, 'createActionTagsHierarchy'], $this->tags));

        return $tags_hierarhy;
    }

    /**
     * @param bool|false $force_reload
     */
    public function collectTags($force_reload = false)
    {
        foreach ($this->getClasses() as $class) {
            try {
                $classMetadata = $this->factory->getMetadataForClass($class, $force_reload);
                $metadata[]    = $classMetadata;
                /** @var \Metadata\ClassMetadata $metadatum */
                foreach ($classMetadata->methodMetadata as $metadatum) {
                    /* @var MethodMetadata $metadatum */
                    $this->addTags($metadatum->getTags());
                }
            } catch (AbstractClassException $e) {
                // There is nothing to do. Or just output it
            } catch (\ReflectionException $e) {
                // TODO: we should dive into FQCN to know why it return directories as FQCN.
            }
        }
    }

    /**
     * @param int $key
     *
     * @return array
     */
    public function getTagsHierarchyForApi($key)
    {
        $tags          = $this->getTagsHierarchy();
        $gathered_tags = [];
        foreach ($this->getKey($key)->getActions() as $action) {
            /* @var ApiKeyAction $action */
            $gathered_tags[] = $action->getAction();
        }

        return $this->array_values_recursive($this->populateByGathered($gathered_tags, $this->mutate($tags)));
    }

    /**
     * @param $tags
     *
     * @return array
     */
    protected function mutate($tags)
    {
        static $id = 0;
        $mutated   = [];
        foreach ($tags as $key => $value) {
            ++$id;
            if (is_array($value)) {
                $mutated[$key] = ['id' => $id, 'title' => str_replace('-', '', $key), 'value' => 0, 'nodes' => $this->mutate($value)];
            } else {
                $mutated[$key] = ['id' => $id, 'title' => str_replace('-', '', $key), 'value' => 0];
            }
        }

        return $mutated;
    }

    /**
     * @param $tags
     * @param $hierarchy
     *
     * @return mixed
     */
    protected function populateByGathered($tags, $hierarchy)
    {
        foreach ($tags as $tag) {
            $allowed = strpos($tag, '-') === false;
            if ($tag === '*') {
                $arr = ['nodes' => &$hierarchy];
                $this->processRecursive($arr, $allowed);

                return $hierarchy;
            }
            $parts = explode('.', str_replace('-', '', $tag));
            $tmp   = &$hierarchy;
            while ($part = array_shift($parts)) {
                if ($part === '*') {
                    $arr = ['nodes' => &$tmp];
                    $this->processRecursive($arr, $allowed);
                } else {
                    $tmp          = &$tmp[$part];
                    $tmp['value'] = $allowed && $tmp['value'] >= 0 ? 1 : -1;
                    $tmp          = &$tmp['nodes'];
                }
            }
        }

        return $hierarchy;
    }

    /**
     * @param $hierarchy
     *
     * @return array
     */
    protected function array_values_recursive($hierarchy)
    {
        $new_hierarchy = [];
        foreach ($hierarchy as $node) {
            if (isset($node['nodes'])) {
                $node['nodes'] = $this->array_values_recursive($node['nodes']);
            }
            $new_hierarchy[] = $node;
        }

        return $new_hierarchy;
    }

    /**
     * @param $tmp
     * @param $allowed
     */
    protected function processRecursive(&$tmp, $allowed)
    {
        $tmp['value'] = $allowed && $tmp['value'] >= 0 ? 1 : -1;
        if ($tmp['nodes']) {
            foreach ($tmp['nodes'] as &$node) {
                $this->processRecursive($node, $allowed);
            }
        }
    }

    /**
     * @param int    $key
     * @param string $action
     * @param int    $value
     */
    public function updateTags($key, $action, $value)
    {
        $key = $this->getKey($key);
        $this->collectTags();
        $tags_hierarchy = $this->getTagsHierarchy();

        $original_action = $action;

        $parts = explode('.', $original_action);
        $tag   = $tags_hierarchy;
        while ($part = array_shift($parts)) {
            if (array_key_exists($part, $tag)) {
                $tag = $tag[$part];
            }
        }

        if (is_array($tag)) {
            $deletePattern = "$original_action%";
            $action .= '.*';
        } else {
            $deletePattern = $original_action;
        }
        $this->deleteOld($key, $deletePattern);

        $prefix = '';
        if ($value === 0) {
            return;
        } elseif ($value < 0) {
            $prefix = '-';
        }

        $action     = $prefix.$action;
        $api_action = new ApiKeyAction();
        $api_action->setAction($action)->setKey($key);
        $this->em->persist($api_action);
        $this->em->flush($api_action);
    }

    /**
     * @param $key
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @return ApiKey|null|object
     *
     */
    public function getKey($key)
    {
        $key = $this->em->find('\Application\DeskPRO\Entity\ApiKey', $key);
        if (!$key) {
            throw new NotFoundHttpException(sprintf('ApiKey with id [ %d ] was not found', (int) $key));
        }

        return $key;
    }

    /**
     * @param ApiKey $key
     * @param        $pattern
     */
    protected function deleteOld(ApiKey $key, $pattern)
    {
        $qb = $this->em->getRepository('DeskPRO\Bundle\AppBundle\Entity\ApiKeyAction')->createQueryBuilder('aka');
        $qb->delete()
           ->where($qb->expr()->like('aka.action', $qb->expr()->literal($pattern)))
           ->orWhere($qb->expr()->like('aka.action', $qb->expr()->literal('-'.$pattern)))
           ->andWhere('aka.key = :key')
           ->setParameters(['key' => $key]);
        $qb->getQuery()->execute();
    }
}
