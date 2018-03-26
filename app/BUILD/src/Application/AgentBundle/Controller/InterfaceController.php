<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\Entity\ApiToken;
use Symfony\Component\HttpFoundation\Request;

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

    public function preActionHandler(Request $request, $action, $arguments = null)
    {
        // just pass loadViews, nothing criminal here
        if ($action === 'loadViewsAction') {
            return;
        }

        return parent::preActionHandler($request, $action, $arguments);
    }

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
