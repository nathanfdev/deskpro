<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AuditBundle\Log\FieldFilter;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Component\Util\TypeUtils;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class CollectionFieldFilter.
 */
class CollectionFieldFilter implements FieldFilterInterface
{
    /**
     * @var array
     */
    private $procssed = [];

    /**
     * @var ExpressionLanguage
     */
    private $language;

    /**
     * CollectionFieldFilter constructor.
     */
    public function __construct()
    {
        $this->language = new ExpressionLanguage();
    }

    /**
     * {@inheritdoc}
     */
    public function filter($value, $expression = 'item.getId()')
    {
        $values = $value;
        if ($value instanceof PersistentCollection) {
            $oid = spl_object_hash($value);
            if (!array_key_exists($oid, $this->procssed)) {
                $values               = $value->getSnapshot();
                $this->procssed[$oid] = true;
            }
        }

        $ids = [];
        try {
            foreach ($values as $item) {
                if ($item instanceof EntityInterface || $item instanceof DomainObject) {
                    $ids[] = $this->language->evaluate(new Expression($expression), ['item' => $item]);
                } elseif ($item instanceof \Traversable || is_array($item)) {
                    $ids[] = $this->filter($item);
                } elseif (is_scalar($item)) {
                    $ids[] = $item;
                } elseif (is_object($item)) {
                    $ids[] = TypeUtils::getBaseTypeName($item);
                }
            }
        } catch (\Exception $e) {
            $exception = $e;
        }

        return sprintf('[ %s ]', implode(', ', $ids));
    }
}
