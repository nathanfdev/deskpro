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
