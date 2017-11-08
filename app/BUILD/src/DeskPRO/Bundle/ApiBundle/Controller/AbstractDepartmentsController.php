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

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Form\Type\DepartmentType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DepartmentsController.
 */
abstract class AbstractDepartmentsController extends CrudController
{
    public static $entity    = Department::class;
    public static $type      = DepartmentType::class;
    public static $listOrder = 'asc';

    protected static $property;
    protected static $departmentType;

    /**
     * @ApiDoc(
     *     section="Departments",
     *     description="Get agents belongs to department",
     *     requirements={
     *         {
     *             "name"="id",
     *             "requirement"="\d+",
     *             "description"="the id of the department",
     *             "dataType"="integer"
     *         }
     *     },
     *     statusCodes={
     *         200="Returned if everything is OK",
     *         404="Returned if department wasn't found"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person>"
     * )
     * @Rest\Get("/{id}/agents")
     *
     * @param int     $id
     * @param Request $request
     *
     * @return View
     */
    public function getAgentsAction($id, Request $request)
    {
        $department = $this->findEntity($id, $request);
        $agents     = $this->get('data.departments')->getDepartmentAgents($department);

        return View::create($this->wrap($agents));
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb->andWhere(sprintf("$alias.%s = true", static::$property));

        if ($request->query->getBoolean('my', false)) {
            $qb->andWhere('e.id IN (:allowed_department_ids)');
            $qb->setParameter('allowed_department_ids', $this->getAllowedDepartments());
        }
        if ($request->query->getBoolean('selectable')) {
            $subQb = $qb->getEntityManager()->createQueryBuilder();
            $subQb
                ->select('count(childDepartment.id)')
                ->from(Department::class, 'childDepartment')
                ->where("childDepartment.parent = $alias.id")
            ;

            $qb->andWhere("($subQb) = 0");
        }
        if ($request->query->get('brands')) {
            $qb->join("$alias.brands", 'brands');
            $qb->andWhere('brands.id IN (:brand_ids)');
            $qb->setParameter('brand_ids', $request->query->get('brands'));
        }
    }

    /**
     * @return Department[]
     */
    abstract protected function getAllowedDepartments();

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'type' => static::$departmentType,
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        /** @var Department $entity */
        $entity = parent::findEntity($id, $request);
        if (!$entity->{static::$property}) {
            throw $this->createNotFoundException();
        }

        return $entity;
    }
}
