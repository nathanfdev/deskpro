<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\MassActions;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\MassActionCollectionType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractMassActionsController.
 */
abstract class AbstractMassActionsController extends BaseController
{
    /**
     * @var string
     */
    protected static $type;

    /**
     * @ApiDoc(
     *     description="create new mass action for real-time apply",
     *     statusCodes={
     *         200="Your request was successful",
     *         400="Malformed request, refer to manual",
     *     },
     *     noOutput=true
     * )
     *
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function massAction(Request $request)
    {
        // handle request
        $form = $this->createForm(static::$type, null, [
            'person' => $this->getUser(),
        ]);

        $form->submit($request->request->all(), false);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        // modify entities
        $entities = $form->get('ids')->getData();
        if (!$entities) {
            $entities = [];
        }

        $massForm = $this->createForm(MassActionCollectionType::class, ['ids' => $entities], [
            'person'       => $this->getUser(),
            'params_class' => $form->getConfig()->getOption('params_class'),
        ]);

        $massForm->submit(['ids' => array_fill(0, count($entities), $request->request->get('params'))], false);
        if (!$massForm->isValid()) {
            throw new InvalidFormException($massForm);
        }

        // save changes
        foreach ($entities as $entity) {
            $this->saveObject($entity);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param mixed $entity
     */
    protected function saveObject($entity)
    {
        $this->getManager()->persist($entity);
        $this->getManager()->flush();
    }
}
