<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Request;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagRequest extends SymfonyRequest
{
    /**
     * @var \Symfony\Component\OptionsResolver\OptionsResolver
     */
    protected $options_resolver;

    /**
     * @return \Symfony\Component\OptionsResolver\OptionsResolver
     */
    public function getOptionsResolver()
    {
        return $this->options_resolver;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $options_resolver
     */
    public function setOptionsResolver(OptionsResolver $options_resolver)
    {
        $options_resolver->setDefined('_tag_name');
        $this->options_resolver = $options_resolver;
    }

    /**
     * @return array
     */
    public function getTagOptions()
    {
        return $this->getOptionsResolver()->resolve($this->query->get('tag_options', array()));
    }
}
