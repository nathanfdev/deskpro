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
 * @subpackage
 */

namespace Application\PortalBundle\DataCollector;


use Application\DeskPRO\Brand\BrandStack;
use Application\LanguageBundle\Language\LanguageStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class PortalCollector extends DataCollector
{
    /**
     * @var \Application\DeskPRO\Brand\BrandStack
     */
    private $brand_stack;
    /**
     * @var \Application\LanguageBundle\Language\LanguageStack
     */
    private $language_stack;


    public function __construct(BrandStack $brand_stack, LanguageStack $language_stack)
    {
        $this->brand_stack = $brand_stack;
        $this->language_stack = $language_stack;
    }


    /**
     * Collects data for the given Request and Response.
     *
     * @param Request    $request   A Request instance
     * @param Response   $response  A Response instance
     * @param \Exception $exception An Exception instance
     * @api
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $brandContainer = $this->brand_stack->getActive();
        $language = $this->language_stack->getActive();
        $this->data     = array(
            'route_name'          => $request->attributes->get('_route'),
            'executed_controller' => $request->attributes->get('_controller'),
            'brand_id'            => $brandContainer->getBrand()->id,
            'brand_name'          => $brandContainer->getBrand()->name,
            'theme_id'            => $brandContainer->getTheme()->getId(),
            'theme_name'          => $brandContainer->getTheme()->getName(),
            'language_code'       => $language ? $language->getTwoLetterLanguageCode() : 'N/A',
            'language_id'         => $language ? $language->getId() : 'N/A',
            'language_img'        => $language ? $language->flag_image : null,
            'settings'            => $brandContainer->getSettings()->toArray()
        );
    }


    public function getLanguageCode()
    {
        return $this->data['language_code'];
    }

    public function getLanguageImage()
    {
        return $this->data['language_img'];
    }

    public function getLanguageId()
    {
        return $this->data['language_id'];
    }

    public function getRoute()
    {
        return $this->data['route_name'];
    }


    public function getController()
    {
        return $this->data['executed_controller'];
    }


    public function getSettings()
    {
        return $this->data['settings'];
    }


    public function getThemeId()
    {
        return $this->data['theme_id'];
    }


    public function getThemeName()
    {
        return $this->data['theme_name'];
    }


    public function getBrandId()
    {
        return $this->data['brand_id'];
    }


    public function getBrandName()
    {
        return $this->data['brand_name'];
    }


    public function getName()
    {
        return 'portal';
    }
}
