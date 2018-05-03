<?php

namespace DeskPRO\Bundle\ApiBundle\Request\ParamConverter;

use Application\DeskPRO\Entity\Department;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TicketLayoutDepartmentConverter.
 */
class TicketLayoutDepartmentConverter implements ParamConverterInterface
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

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $department   = null;
        $departmentId = $request->attributes->get($configuration->getName());

        if ($departmentId !== 'default') {
            $department = $this->em->getRepository(Department::class)->find((int) $departmentId);
            if (!$department) {
                throw new NotFoundHttpException('Department object not found.');
            }
        }

        $request->attributes->set($configuration->getName(), $department);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'ticket_layout_department';
    }
}
