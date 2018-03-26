<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Organizations;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Organizations\OrganizationMemberType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrganizationMembersController.
 *
 * @ApiModes("all")
 * @Rest\Route("/organizations/{parentId}/members")
 * @ApiDoc(target="all", section="Organizations", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
 * @ApiDoc(
 *     target="postAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Organizations\OrganizationMemberType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Person",
 *          "organization"="Application\DeskPRO\Entity\Organization"
 *      }
 *     }
 * )
 */
class OrganizationMembersController extends CrudSubController
{
    public static $exposeOnly     = ['list', 'post'];
    public static $entity         = Person::class;
    public static $parentProperty = 'organization';
    public static $type           = OrganizationMemberType::class;

    /**
     * {@inheritdoc}
     */
    protected function instantiateEntity(Request $request)
    {
        return $this->getRepository(Person::class)->find($request->request->getInt('person'));
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $personId = $request->request->getInt('person');
        if ($personId) {
            $model = $this->getRepository(Person::class)->find($personId);
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
