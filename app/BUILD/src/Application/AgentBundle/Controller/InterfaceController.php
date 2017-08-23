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

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\Entity\ApiToken;

class InterfaceController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected function requireRequestToken($action, $arguments = null)
    {
        return false;
    }

    public function interfaceAction($interface)
    {
        switch ($interface) {
            case 'reports':
                $token               = new ApiToken();
                $token->scope        = ApiToken::SCOPE_SESSION;
                $token->person       = $this->person;
                $token->date_expires = new \DateTime('+1 hour');

                $this->em->persist($token);
                $this->em->flush();

                return $this->render('AgentBundle:ReportsInterface:interface.html.twig', [
                    'api_token' => $token,
                ]);
            default:
                throw $this->createNotFoundException();
        }
    }

    //###################################################################################################################
    // multi-load-view
    //###################################################################################################################

    public function loadViewsAction()
    {
        $views = [];

        foreach ($this->in->getCleanValueArray('views', 'string', 'discard') as $view_name) {
            $id = $view_name;

            $rendered = null;
            if ($this->tpl->exists($view_name.'.twig')) {
                $rendered = $this->renderView($view_name.'.twig');
            } else {
                $rendered = 'View does not exist: '.$view_name.'.twig';
            }

            $views[] = [
                'id'       => $id,
                'template' => $view_name,
                'source'   => $rendered,
            ];
        }

        if ($this->in->getBool('intercepted')) {
            $view = array_pop($views);
            if ($view) {
                return $this->createResponse($view['source']);
            } else {
                return $this->createResponse('');
            }
        }

        return $this->createJsonResponse($views);
    }
}
