<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Fractal;


use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Doctrine\NotifyPropertyChangeEntity;
use DeskPRO\Component\DoctrineAssociation\Deferred\DeferredIdentity;
use Doctrine\Common\Collections\Collection;
use League\Fractal\TransformerAbstract;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class FractalTransformer extends TransformerAbstract implements ContainerAwareInterface
{
    /**
     * @var PropertyAccessor
     */
    protected $property_accessor;

    /**
     * @var null|ContainerInterface
     */
    protected $container;

    /**
     * An array of object property names to include in the result.
     *
     * @var array
     */
    abstract public function getWhitelist();

    /**
     * All transformers call this function, which processes the white list to transform data.
     *
     * To customize data, implement the transformExtras() method.
     *
     * @param $data
     * @return array
     */
    public function transform($data)
    {
        $transformed = [];

        foreach ($this->getWhitelist() as $property_name) {
            $val = $this->getPropertyAccessor()->getValue($data, $property_name);

            $val = $this->autoProcessValue($val, $property_name, $data);

            $transformed[$property_name] = $val;
        }

        return array_merge($transformed, $this->transformExtras($data));
    }

    /**
     * Override this to add extra data to the output. Any key you add to this array will be seen in the JSON output with
     * the value you provide. This array will overwrite the default values.
     *
     * @param $data
     * @return array
     */
    protected function transformExtras($data)
    {
        return [];
    }

    /**
     * This is a place where we can auto-detect a value type and convert it as needed. For example, if a string
     * happens to be "1" and we want all is_numeric to be integers, we can do that here.
     *
     * @param mixed $val
     * @param mixed $property_name
     * @param mixed $data
     * @return mixed
     */
    protected function autoProcessValue($val, $property_name, $data)
    {
        $assoc_manager = $this->container->get('doctrine_association_manager');

        if ($val instanceof \DateTime) {
            return $val->format(\DateTime::ISO8601);
        }

        if ($val instanceof DomainObject || $val instanceof NotifyPropertyChangeEntity) {
            return $assoc_manager->deferAssociationIds($data, $property_name);
        }

        if ($val instanceof Collection || is_array($val) || $val instanceof \Traversable || $val === null) {
            if ($assoc_manager->isAssociation($data, $property_name)) {
                // this is an association, we don't want to worry about getting these IDs yet
                $val = $assoc_manager->deferAssociationIds($data, $property_name);
            } else {
                $val = null;
            }
        }

        if (!is_scalar($val) && !$val instanceof DeferredIdentity) {
            // this is a catch-all for now, and we will iron out any value that hits here and deal with it accordingly
            $val = null;
        }

        return $val;
    }

    /**
     * @return PropertyAccessor
     */
    protected function getPropertyAccessor()
    {
        if (!$this->property_accessor) {
            $this->property_accessor = PropertyAccess::createPropertyAccessor();
        }

        return $this->property_accessor;
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
