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

namespace Application\PortalBundle\Theme;


use Application\DeskPRO\Brand\BrandStack;

class ThemeView
{
    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var array
     */
    private $default_options;

    public function __construct(BrandStack $brand_stack, array $default_options)
    {
        $this->default_options = $default_options;
        $this->brand_stack = $brand_stack;
    }

    public function __call($tag_name, array $explicit_options)
    {
        $default_options = $this->calculateDefaultOptions($tag_name);

        // explicit options uses [0] becasue twig passes all arguments directly here
        // we are only interested in the first one, which must be a twig array
        // page.tag({ array: 'here'}) - that first arg is $explicition_options[0]
        if (count($explicit_options)) {
            $explicit_options = $explicit_options[0];
        }

        $options = array_merge($default_options, $explicit_options);

        $brand_container = $this->brand_stack->getActive();

        return $brand_container->renderTag($tag_name, $options);
    }

    protected function calculateDefaultOptions($tag_name)
    {
        $options = array();

        if (!$tag = $this->brand_stack->getActive()->getTheme()->resolveTag($tag_name)) {
            throw new \InvalidArgumentException(sprintf('cannot resolve tag "%s"', $tag_name));
        }

        foreach ($tag->getDefinedOptions() as $defined) {
            if (array_key_exists($defined, $this->default_options)) {
                $options[$defined] = $this->default_options[$defined];
            }
        }

        return $options;
    }
}
