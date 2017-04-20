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

namespace DeskPRO\Bundle\AppBundle\Twig;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;

/**
 * Class TwigTemplateRenderer.
 */
class TwigTemplateRenderer
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var BrandAwareSettingsResolver
     */
    protected $settingsResolver;

    /**
     * Constructor.
     *
     * @param \Twig_Environment          $twig
     * @param BrandAwareSettingsResolver $settingsResolver
     */
    public function __construct(\Twig_Environment $twig, BrandAwareSettingsResolver $settingsResolver)
    {
        $this->twig             = $twig;
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * @param $template_code
     * @param array $vars
     *
     * @throws \Exception|null
     *
     * @return null|string
     */
    public function renderStringTemplate($template_code, array $vars = [])
    {
        $old_loader = $this->twig->getLoader();
        $old_cache  = $this->twig->getCache();

        $arr_loader = new \Twig_Loader_Array([
            'template' => $template_code,
        ]);

        $this->twig->setLoader($arr_loader);
        $this->twig->setCache(false);

        $result    = null;
        $exception = null;
        try {
            $result = $this->twig->render('template', $vars);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->twig->setLoader($old_loader);
        $this->twig->setCache($old_cache);

        if ($exception) {
            throw $exception;
        }

        return $result;
    }

    /**
     * @param string                   $string
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param array                    $extraVars
     *
     * @return string
     */
    public function renderTicketTemplate($string, Ticket $ticket, ExecutorContextInterface $context, array $extraVars = [])
    {
        // Simple string, cant be a template so dont waste time evaluating it
        if (strpos($string, '{{') === false && strpos($string, '{%') === false) {
            return $string;
        }

        $vars = [
            'performer'     => $context->getPersonContext(),
            'helpdesk_name' => $this->settingsResolver->getSetting('core.deskpro_name'),
            'site_name'     => $this->settingsResolver->getSetting('core.site_name'),
            'user_vars'     => $context->getUserVars(),
        ];

        if ($extraVars) {
            $vars = array_merge($vars, $extraVars);
        }

        if (!isset($vars['ticket'])) {
            $vars['ticket'] = $ticket->toApiData();
        }

        try {
            $rendered = $this->renderStringTemplate($string, $vars);
        } catch (\Exception $e) {
            return $string;
        }

        return $rendered;
    }
}
