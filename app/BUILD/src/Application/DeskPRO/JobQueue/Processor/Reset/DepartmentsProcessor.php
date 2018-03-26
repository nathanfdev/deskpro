<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;

class DepartmentsProcessor extends Base
{
    const JOB_TYPE = 'reset.departments';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $this->connection->executeUpdate('DELETE FROM departments');
        $this->connection->executeUpdate('DELETE FROM department_permissions');

        $brands = $this->em->getRepository(Brand::class)->findAll();
        foreach (['Support', 'Sales'] as $title) {
            $td          = Department::createTicketDepartment();
            $td->brands  = $brands;
            $cd          = Department::createChatDepartment();
            $cd->brands  = $brands;
            $td['title'] = $cd['title'] = $title;
            $this->em->persist($td);
            $this->em->persist($cd);
        }
        $this->em->flush();
    }
}
