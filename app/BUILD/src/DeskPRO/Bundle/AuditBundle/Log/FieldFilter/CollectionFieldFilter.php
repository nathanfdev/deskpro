<?php

namespace DeskPRO\Bundle\AuditBundle\Log\FieldFilter;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\CustomDataAbstract;
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
                if ($item instanceof CustomDataAbstract) {
                    $ids[] = $this->language->evaluate(new Expression('item.__toString()'), ['item' => $item]);
                } elseif ($item instanceof EntityInterface || $item instanceof DomainObject) {
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
