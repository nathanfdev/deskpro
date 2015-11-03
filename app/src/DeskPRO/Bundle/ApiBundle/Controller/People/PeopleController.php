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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PeopleController extends BaseController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *      description="get a list of people",
     *      parameters={
     *          {
     *              "name"="page",
     *              "requirement"="\d+",
     *              "description"="the page you are requesting",
     *              "dataType"="integer",
     *              "required"=false
     *          },
     *          {
     *              "name"="count",
     *              "requirement"="\d+",
     *              "description"="results per page",
     *              "dataType"="integer",
     *              "required"=false
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/people", name="api_people")
     *
     * @param Request $request
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $query         = $request->query->all();

        $filter = $this->getFilter($request);
        if (!empty($query['ids'])) {
            $people = $this->selectPeople(explode(',', $query['ids']));
        } elseif (!empty($filter)) {
            $people = $this->getByFilter($entityManager, $filter);
        } else {
            $people = $entityManager->createQueryBuilder()
                ->select('p')->from('DeskPRO:Person', 'p')->getQuery();
        }

        // allow up to 200 when selecting by IDs
        $count = $request->query->get('count', empty($query['ids']) ? 10 : 200);
        $page  = $request->query->get('page', 1);

        $pager = new Pagerfanta(new DoctrineORMAdapter($people));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->dataSerialize($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get a list of all agents w/o pagination",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/agents", name="api_agents")
     *
     * @return View
     */
    public function getAllAgentsAction()
    {
        $agents = $this->getRepository(Person::class)->findBy(['is_agent' => true]);

        return View::create(
            $this->dataSerialize($agents),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get a person",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the person",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="Application\DeskPRO\Entity\Person"
     * )
     * @Get("/people/{id}", name="api_people_get", requirements={"id" = "\d+"})
     *
     * @param int $id
     *
     * @return View
     */
    public function getAction($id)
    {
        $person = $this->getPerson($id);

        if (empty($person)) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->dataSerialize($person),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="create a new person",
     *      input={"class"="person", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="Application\DeskPRO\Entity\Person"
     * )
     * @Post("/people", name="api_people_post")
     *
     * @param Request $request
     *
     * @throws WrappedApiErrorException
     * @throws InvalidFormException
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $person = new Person($this->getUser());

        return $this->handleFormSubmission($request, $person);
    }

    /**
     * @APIDoc(
     *      description="update a person",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the person",
     *              "dataType"="integer"
     *          }
     *      },
     *      input={"class"="person", "name"=""},
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     * @Put("/people/{id}", name="api_people_put", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param $id
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    public function putAction(Request $request, $id)
    {
        $person = $this->getPerson($id);

        return $this->handleFormSubmission($request, $person);
    }

    /**
     * @APIDoc(
     *      description="delete a person",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the person",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      }
     * )
     * @Delete("/people/{id}", name="api_people_delete", requirements={"id" = "\d+"})
     *
     * @param $id
     *
     * @return View
     */
    public function deleteAction($id)
    {
        $person = $this->getPerson($id);
        $this->getDoctrine()->getManager()->remove($person);
        $this->getDoctrine()->getManager()->flush();

        return View::create(
            array(),
            Response::HTTP_OK
        );
    }

    /**
     * @param int $id
     *
     * @return Person
     */
    protected function getPerson($id)
    {
        $id     = (int) $id;
        $person = $this->getDoctrine()->getManager()->getRepository('DeskPRO:Person')->find($id);

        if (!$person) {
            throw $this->createNotFoundException();
        }

        return $person;
    }

    /**
     * Will be abstracted for use by other controllers.
     *
     * @param Request $request
     * @param Person  $person
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    protected function handleFormSubmission(Request $request, Person $person)
    {
        $status = $person->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'person', $person)->getForm();

        $submitted = $request->request->all();

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($person);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_people_get', array('id' => $person->getId()));

            return View::create(
                $this->dataSerialize($person),
                $status,
                array(
                    'Location' => $location,
                )
            );
        }

        throw new InvalidFormException($form);
    }

    public function getFilter(Request $request)
    {
        $filter       = array();
        $validFilters = array(
            'not_me', 'is_agent',
        );

        $request = $request->query->all();

        foreach ($request as $param => $value) {
            if (in_array($param, $validFilters)) {
                $filter[$param] = $value;
            }
        }

        return $filter;
    }

    /**
     * @param $em
     * @param $filter
     *
     * @return mixed
     */
    protected function getByFilter($em, $filter)
    {
        // Get the entity manager for tasks, and join the assigned table
        /* @var EntityManager $em */
        $query = $em->createQueryBuilder()->select('p')
            ->from('DeskPRO:Person', 'p');

        if (isset($filter['is_agent'])) {
            $query = $query->andWhere('p.is_agent = :is_agent');
            $query = $query->setParameter('is_agent', (int) $filter['is_agent']);
        }

        if (!empty($filter['not_me'])) {
            $user  = $this->getUser();
            $query = $query->andWhere('p.id != :id');
            $query = $query->setParameter('id', $user->getId());
        }

        return $query->getQuery();
    }

    /**
     * Get specific teams.
     *
     * @param $peopleIds
     *
     * @return mixed
     */
    protected function selectPeople($peopleIds)
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Clean the IDs
        $peopleIds = array_map(function ($value) {
            return (int) $value;
        }, $peopleIds);

        $query = $entityManager->createQueryBuilder()->select('p')->from('DeskPRO:Person', 'p')
            ->where('p.id IN (:peopleIds)')
            ->setParameter('peopleIds', $peopleIds);

        return $query->getQuery();
    }
}
