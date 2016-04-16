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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\Controller\Organizations;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrganizationMembersController.
 *
 * @ApiModes("all")
 * @Rest\Route("/organizations/{parentId}/members")
 * @ApiDoc(target="all", section="Organizations", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
 */
class OrganizationMembersController extends CrudSubController
{
    public static $exposeOnly     = ['list', 'post'];
    public static $entity         = Person::class;
    public static $parentProperty = 'organization';
    public static $type           = 'organization_member';

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $person_id = $request->request->getInt('person');
        if ($person_id) {
            $model = $this->getRepository('DeskPRO:Person')->find($person_id);
        }

        $options = array_merge($options, [
            'organization' => $this->findParentOr404(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * @ApiDoc(
     *     section="Organizations",
     *     description="delete an organization member",
     *     statusCodes={
     *         200="Returned in case of successful response"
     *     }
     * )
     *
     * @Rest\Delete("/{person}", requirements={"id"="\d+"})
     *
     * @param Person $person
     *
     * @return View
     */
    public function deleteMemberAction(Person $person)
    {
        $organization = $this->findParentOr404();
        if ($person->getOrganization() !== $organization) {
            throw $this->createBadRequestException('Person is not a member of this organization.');
        }

        $person->setOrganization(null);

        $this->getManager()->persist($person);
        $this->getManager()->flush();

        return new View([]);
    }
}
