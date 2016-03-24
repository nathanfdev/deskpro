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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks\Projects;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\OutputEntity;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProjectDepartmentController.
 *
 * @Annotations\Route("/project_members/{parentId}/agents")
 * @ApiDocSection("TaskProjects")
 * @OutputEntity("Application\DeskPRO\Entity\Person")
 * @ApiModes("all")
 */
class ProjectAgentsController extends AbstractProjectCrudSubController
{
    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return 'person';
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        $parent = $this->findParentOr404();

        if (!$entity = $this->getDoctrine()->getRepository(self::$entity)->findOneBy(['person' => $id, 'project' => $parent])) {
            throw $this->createNotFoundException('Not found');
        }

        $reflectionProperty = new \ReflectionProperty(static::$entity, static::$parentProperty);
        $reflectionProperty->setAccessible(true);
        $entityParent = $reflectionProperty->getValue($entity);

        if ($parent !== $entityParent) {
            throw $this->createNotFoundException('Requested resources does not belong to the specified parent');
        }

        return $entity;
    }

    /**
     * Get agents - members of the project with specified id.
     *
     * @ApiDoc(
     *     section="TaskProjects",
     *     tags={"CRUD"="#ffa500"},
     *     resourceDescription="Operations about task projects",
     *     description="get agents for the project with specified id",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "description"="the id of the project", "dataType"="integer"}
     *     },
     *     statusCodes={
     *         200="Returned if everything is OK"
     *     },
     *     output="array<Application\DeskPRO\Entity\Person>"
     * )
     *
     * @Annotations\Get("")
     *
     * @param Request $request
     *
     * @return \FOS\RestBundle\View\View
     */
    public function listAction(Request $request)
    {
        return $this->wrap($this->getProjectMembers($request->get('parentId'), Person::class));
    }
}
