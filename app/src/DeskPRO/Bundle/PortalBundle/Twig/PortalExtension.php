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
namespace DeskPRO\Bundle\PortalBundle\Twig;

use Application\DeskPRO\Entity;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Helper\TicketPublicIdResolver;
use DeskPRO\Bundle\AppBundle\Model\TicketView;
use DeskPRO\Bundle\PortalBundle\View\File\FileViewHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class PortalExtension extends \Twig_Extension
{
    /**
     * @var \DeskPRO\Bundle\PortalBundle\Brand\BrandStack
     */
    private $brand_stack;

    /**
     * @var \Application\DeskPRO\NewSettings\SettingsResolver
     */
    private $settings_resolver;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Content\AvatarResolver
     */
    private $avatar_resolver;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TicketPublicIdResolver
     */
    private $ticket_public_id_resolver;

    /**
     * @var TokenStorage
     */
    private $token_storage;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager
     */
    private $permission_manager;

    /**
     * @var \DeskPRO\Bundle\PortalBundle\Helper\PortalRatingsHelper
     */
    private $ratings_helper;

    /**
     * @var \DeskPRO\Bundle\PortalBundle\Visitor\VisitorIdentificationProvider
     */
    private $visitor_identification_provider;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Language\LanguageManager
     */
    private $language_manager;

    /**
     * @param ContainerInterface $continer
     */
    public function __construct(ContainerInterface $continer)
    {
        $this->container                       = $continer;
        $this->brand_stack                     = $continer->get('brand_stack');
        $this->settings_resolver               = $continer->get('settings_resolver');
        $this->avatar_resolver                 = $continer->get('avatar_resolver');
        $this->ticket_public_id_resolver       = $continer->get('ticket.public_id_resolver');
        $this->permission_manager              = $continer->get('portal_permissions_manager');
        $this->token_storage                   = $continer->get('security.token_storage');
        $this->ratings_helper                  = $continer->get('ratings_helper');
        $this->visitor_identification_provider = $continer->get('visitor_identification_provider');
        $this->language_manager                = $continer->get('language_manager');
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('ticket_status', array($this, 'getTicketStatusString')),
            new \Twig_SimpleFunction('ticket_public_id', array($this, 'getPublicTicketId')),
            new \Twig_SimpleFunction('brand_setting', array($this, 'getBrandSetting'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('brand', array($this, 'getBrand')),
            new \Twig_SimpleFunction('avatar_url', array($this, 'getAvatarUrl')),
            new \Twig_SimpleFunction('render_message', array($this, 'getRenderedObject'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('render_news', array($this, 'getRenderedObject'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('get_secure_content_cats', array($this, 'getSecureCats')),
            new \Twig_SimpleFunction('user_up_voted', array($this, 'didUserUpVote')),
            new \Twig_SimpleFunction('user_down_voted', array($this, 'didUserDownVote')),
            new \Twig_SimpleFunction('file_css_class', array($this, 'getFileCssClass')),
            new \Twig_SimpleFunction('ticket_view', array($this, 'getTicketView')),
            new \Twig_SimpleFunction('phrase_form_error', array($this, 'makeFormError')),
        );
    }

    public function makeFormError(FormError $form_error)
    {
        $params = $this->parseErrorParams($form_error->getMessageParameters());

        return $this->language_manager->phrase($form_error->getMessageTemplate(), $params);
    }

    protected function parseErrorParams(array $params)
    {
        $cleaned_params = [];

        foreach ($params as $raw_name => $param) {
            // strip symfony's curly braces
            if (substr($raw_name, 0, 2) === '{{') {
                $raw_name = substr($raw_name, 2);
                $raw_name = substr($raw_name, 0, -2);
            }
            $clean_name                  = trim($raw_name);
            $cleaned_params[$clean_name] = $param;
        }

        return $cleaned_params;
    }

    public function getFileCssClass(Entity\Download $download)
    {
        if ($blob = $download->getBlob()) {
            return implode(' ', FileViewHelper::getCssClassForFileExtension($blob->getExtension()));
        }

        return '';
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function didUserUpVote($object)
    {
        if ($rating = $this->getRating($object)) {
            return $rating->isPositive();
        }

        return false;
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function didUserDownVote($object)
    {
        if ($rating = $this->getRating($object)) {
            return $rating->isNegative();
        }

        return false;
    }

    /**
     * @param $object
     *
     * @return Entity\Rating|null
     */
    public function getRating($object)
    {
        $person = $this->getPerson();

        if ($person instanceof Entity\Person && !$person instanceof PersonGuest) {
            if ($rating = $this->ratings_helper->findPersonRating($object, $person)) {
                return $rating;
            }
        } else {
            $visitor_id = $this->visitor_identification_provider->getVisitorIdentifier();

            if ($rating = $this->ratings_helper->findVisitorRating($object, $visitor_id)) {
                return $rating;
            }
        }

        return;
    }

    public function getTicketView(Entity\Ticket $ticket)
    {
        return $this->container->get('tickets.view')->getUserTicketView($ticket);
    }

    public function getPublicTicketId($ticket)
    {
        if ($ticket instanceof TicketView) {
            $ticket = $ticket->getTicket();
        }

        if (!$ticket instanceof Entity\Ticket) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Twig function "ticket_public_id" requires a Ticket or TicketView, but "" given',
                    is_object($ticket) ? get_class($ticket) : 'scalar'
                )
            );
        }

        return $this->ticket_public_id_resolver->findId($ticket);
    }

    public function getSecureCats(Entity\ContentAbstract $content)
    {
        $permission_bag = $this->getPermissionBagForCurrentUser();

        if ($content instanceof Entity\Article) {
            $cat = $content->getPrimaryCategory();
            if (!$permission_bag->hasContentCategoryAccess($cat)) {
                foreach ($content->getCategories() as $cat) {
                    if ($permission_bag->hasContentCategoryAccess($cat)) {
                        break;
                    }
                }
            }
        } elseif ($content instanceof Entity\News) {
            $cat = $content->getCategory();
        } elseif ($content instanceof Entity\Download) {
            $cat = $content->getCategory();
        } elseif ($content instanceof Entity\Feedback) {
            $cat = $content->getCategory();
        } else {
            throw new \InvalidArgumentException('the get_secure_cats twig function requires one of: Article, Download, News, Feedback, but did not get one');
        }

        $category_tree = array();

        $permission_bag = $this->getPermissionBagForCurrentUser();
        foreach ($cat->getTreeParents() as $c) {
            if ($permission_bag->hasContentCategoryAccess($c)) {
                $category_tree[] = $c;
            }
        }

        // check here, too
        if ($permission_bag->hasContentCategoryAccess($cat)) {
            $category_tree[] = $cat;
        }

        return $category_tree;
    }

    /**
     * @param string $setting
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getBrandSetting($setting, $default = null)
    {
        return $this->brand_stack->getActive()->getSetting($setting, $default);
    }

    /**
     * @param string $prop
     *
     * @return string
     */
    public function getBrand($prop)
    {
        return $this->brand_stack->getActive()->getBrand()->get($prop);
    }

    /**
     * @param Entity\Ticket $ticket
     *
     * @return string
     */
    public function getTicketStatusString($ticket)
    {
        switch ($ticket->status_code) {
            case Entity\Ticket::STATUS_RESOLVED:
                return 'Resolved';
            case Entity\Ticket::STATUS_AWAITING_AGENT:
                return 'Awaiting Agent';
            case Entity\Ticket::STATUS_AWAITING_USER:
                return 'Awaiting You';
            case Entity\Ticket::STATUS_HIDDEN:
                return 'Hidden';
            case Entity\Ticket::STATUS_ARCHIVED:
                return 'Archived';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get URL to a profile picture/avatar.
     *
     * @param mixed $obj
     * @param int   $size
     *
     * @return string the url
     */
    public function getAvatarUrl($obj = null, $size = 80)
    {
        return $this->avatar_resolver->getAvatar($obj, $size);
    }

    /**
     * @param mixed $obj
     * @param mixed $opt
     *
     * @return string
     */
    public function getRenderedObject($obj, $opt = null)
    {
        if ($obj instanceof Entity\TicketMessage) {
            return $this->container->get('ticket_message.renderer')->render($obj);
        } elseif ($obj instanceof Entity\News) {
            if ($opt == 'exceprt') {
                return $this->container->get('news.renderer')->renderExceprt($obj);
            } else {
                return $this->container->get('news.renderer')->render($obj);
            }
        } else {
            return '';
        }
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        return array('global_settings' => $this->settings_resolver->getGlobalSettings());
    }

    /**
     * @return PersonGuest|Entity\Person
     */
    protected function getPerson()
    {
        $person = null;
        if ($token = $this->token_storage->getToken()) {
            $person = $token->getUser();
        }

        if (!$person instanceof Entity\Person) {
            $person = new PersonGuest();
        };

        return $person;
    }

    protected function getPermissionBagForCurrentUser()
    {
        $person = $this->getPerson();

        if ($person) {
            return $this->permission_manager->getPermissionsBagForPerson($person);
        }

        return $this->permission_manager->getPermissionsBagForGuest();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'portal_extension';
    }
}
