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

use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Entity\ProjectMember;
use DeskPRO\Bundle\AppBundle\Form\Type\ProjectMemberType;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class AbstractProjectCrudSubController.
 */
abstract class AbstractProjectCrudSubController extends CrudSubController
{
    public static $entity         = ProjectMember::class;
    public static $type           = ProjectMemberType::class;
    public static $exposeOnly     = ['list', 'post', 'delete'];
    public static $parentProperty = 'project';

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getRequestContent(Request $request)
    {
        $submitted = $request->request->getDigits('id', null);

        return [
            'project'        => $this->findParentOr404()->getId(),
            $this->getType() => $submitted,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        try {
            return parent::handleForm($model, $request, $options);
        } catch (DBALException $e) {
            throw new BadRequestHttpException('Something goes wrong, perhaps member already exists');
        }
    }

    /**
     * {@inheritdoc}
     */
    abstract protected function getType();

    /**
     * Get the project members list.
     *
     * @param $projectId
     * @param $object
     *
     * @return \Doctrine\ORM\Query
     */
    protected function getProjectMembers($projectId, $object)
    {
        /* @var QueryBuilder $queryBuilder */
        $entityManager = $this->getDoctrine()->getManager();
        $queryBuilder  = $entityManager->createQueryBuilder()->select('d')->from($object, 'd')
                                       ->leftJoin('d.project_members', 'p')
                                       ->where('p.project = :project')
                                       ->setParameter('project', $projectId);

        return $queryBuilder->getQuery()->getResult();
    }
}
