<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Twig;

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
     * Constructor.
     *
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
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
}
