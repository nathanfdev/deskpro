<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\Authentication;

use Application\DeskPRO\Entity\ApiToken;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Error\ApiErrors;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\ApiBundle\Security\Authentication\ApiAuthenticator;
use DeskPRO\Bundle\ApiBundle\Security\Token\AbstractApiSecurityToken;
use Symfony\Component\Validator\Constraints\NotNull;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

use FOS\RestBundle\Controller\Annotations\Post;

class ApiTokensController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="create a new api token",
     *      input="email,password",
     *      output="token",
     *      statusCodes={
     *          201="Created token",
     *          401="Invalid credentials",
     *          400="Bad request"
     *      }
     * )
     *
     * @Post("/api_tokens", name="post_api_tokens")
     */
    public function newTokenAction(Request $request)
    {
        $form = $this->createFormBuilder(
            array('email' => null, 'password' => null),
            array('csrf_protection' => false)
        )
            ->add('email', 'email', array('constraints' => new NotNull()))
            ->add('password', 'password', array('constraints' => new NotNull()))
            ->getForm();

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $data = $form->getData();
        $email = $data['email'];
        $password = $data['password'];

        $auth_result = $this->get('dp_authentication_manager.agent')->authenticateFormLogin($email, $password);

        if (!$auth_result->isValid()) {
            // failed on agent usersources, revert to user
            $auth_result = $this->get('dp_authentication_manager.user')->authenticateFormLogin($email, $password);
            if (!$auth_result->isValid()) {
                $this->throwUnauthorized();
            }
        }

        $identity = $auth_result->getIdentity();

        if (!$person_id = $identity->getIdentity()) {
            $this->throwUnauthorized();
        }

        $em = $this->get('doctrine.orm.default_entity_manager');
        if (!$person = $em->getRepository('DeskPRO:Person')->find($person_id)) {
            $this->throwUnauthorized();
        }

        $api_token = new ApiToken();
        $api_token->person = $person;
        $api_token->scope = ApiToken::SCOPE_CLIENT;

        $em->persist($api_token);
        $em->flush($api_token);

        return View::create(
            $this->createRepresentation(
                array(
                    'token' => $api_token->id . ':' . $api_token->token
                )
            ),
            201
        );
    }

    protected function makeAuthMethodString(AbstractApiSecurityToken $token)
    {
        return $token->getName();
    }

    private function throwUnauthorized()
    {
        throw new UnauthorizedHttpException(ApiAuthenticator::HTTP_REALM, ApiErrors::BAD_CREDENTIALS);
    }
}
