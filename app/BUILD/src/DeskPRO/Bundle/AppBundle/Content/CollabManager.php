<?php

namespace DeskPRO\Bundle\AppBundle\Content;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\HttpFoundation\Session;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use Firebase\JWT\JWT;

/**
 * Collaborative editing manager.
 */
class CollabManager
{
    /**
     * @var BrandAwareSettingsResolver
     */
    protected $settingsResolver;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param BrandAwareSettingsResolver $settingsResolver
     * @param Session                    $session
     */
    public function __construct(BrandAwareSettingsResolver $settingsResolver, Session $session)
    {
        $this->settingsResolver = $settingsResolver;
        $this->session          = $session;
    }

    /**
     * @return string
     */
    public function getWebsocketUrl()
    {
        return $this->settingsResolver->getSetting('collab_websocket_url');
    }

    /**
     * @return string
     */
    public function getConnectionToken()
    {
        $user = $this->session->getPerson();
        if (!$user instanceof Person) {
            return false;
        }

        $payload = [
            'sub'  => $this->getUserUrn($user),
            'iss'  => $this->settingsResolver->getSetting('collab_deskpro_client_id'),
            'name' => $user->getDisplayName(),
        ];

        return JWT::encode($payload, $this->settingsResolver->getSetting('collab_token_secret'));
    }

    /**
     * @param string $resourceType
     * @param string $resourceIdentity
     *
     * @return string
     */
    public function getResourceJoinToken($resourceType, $resourceIdentity)
    {
        $user = $this->session->getPerson();
        if (!$user instanceof Person) {
            return false;
        }

        $payload = [
            'sub'  => $this->getUserUrn($user),
            'iss'  => $this->settingsResolver->getSetting('collab_deskpro_client_id'),
            'name' => $user->getDisplayName(),
            'aud'  => $this->getResourceUrn($resourceType, $resourceIdentity),
        ];

        return JWT::encode($payload, $this->settingsResolver->getSetting('collab_token_secret'));
    }

    /**
     * Used to pass as editor options.
     *
     * @param string $resourceType
     * @param string $resourceIdentity
     *
     * @return []
     */
    public function getEditorCollabOptions($resourceType, $resourceIdentity)
    {
        $user = App::getCurrentPerson();

        return [
            'documentUrn' => $this->getResourceUrn($resourceType, $resourceIdentity),
            'userUrn'     => $this->getUserUrn($user),
            'token'       => $this->getResourceJoinToken($resourceType, $resourceIdentity),
        ];
    }

    /**
     * @param \DeskPRO\Bundle\AppBundle\Content\Person $user
     *
     * @return string
     */
    protected function getUserUrn(Person $user)
    {
        return "person:{$user->getId()}";
    }

    /**
     * @param string $resourceType
     * @param string $resourceIdentity
     *
     * @return string
     */
    protected function getResourceUrn($resourceType, $resourceIdentity)
    {
        $dpClientId = $this->settingsResolver->getSetting('collab_deskpro_client_id');

        return "urn:deskpro:client:{$dpClientId}:instance:resource:{$resourceType}/$resourceIdentity";
    }
}
