<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Tickets;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use JMS\Serializer\Annotation as JMS;

/**
 * Class DefaultDepartmentSettings.
 */
class DefaultDepartmentSettings
{
    const DEFAULT_DEPARTMENT_AGENT_TYPE = 'agent';
    const DEFAULT_DEPARTMENT_USER_TYPE  = 'user';

    /**
     * @JMS\Exclude()
     *
     * @var string
     */
    private static $pattern = 'default_department.%s';

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $type;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\Brand>")
     *
     * @var Brand
     */
    private $brand;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     *
     * @var Department
     */
    private $department;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        if (!$this->type) {
            throw new \LogicException('The $type should be set before setting name resolve');
        }

        return sprintf(self::$pattern, $this->type);
    }

    /**
     * @param $type
     *
     * @return mixed
     */
    public static function constructName($type)
    {
        return sprintf(self::$pattern, $type);
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     *
     * @return $this
     */
    public function setBrand(Brand $brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function setDepartment(Department $department = null)
    {
        $this->department = $department;

        return $this;
    }
}
