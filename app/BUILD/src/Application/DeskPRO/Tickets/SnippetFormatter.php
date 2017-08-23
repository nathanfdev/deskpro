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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonContextInterface;
use DeskPRO\Bundle\AppBundle\Twig\TwigTemplateRenderer;

/**
 * Class SnippetFormatter.
 */
class SnippetFormatter implements PersonContextInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person_context;

    /**
     * @var array
     */
    protected $extra_vars;

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
     * @param Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;
    }

    /**
     * @param Ticket $ticket
     *
     * @return array
     */
    public function getVars(Ticket $ticket)
    {
        $data           = $this->extra_vars;
        $data['ticket'] = $ticket->toApiData();
        $data['entity'] = $ticket->toApiData();

        if (isset($data['ticket']['person'])) {
            $data['user'] = $data['ticket']['person'];
        }

        if (isset($data['ticket']['agent'])) {
            $data['agent'] = $data['ticket']['agent'];
        }

        if (isset($data['ticket']['agent_team'])) {
            $data['agent_team'] = $data['ticket']['agent_team'];
        }

        if ($this->person_context) {
            $data['me'] = $this->person_context->toApiData();
        }

        return $data;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function addVar($name, $value)
    {
        $this->extra_vars[$name] = $value;
    }

    /**
     * @param $snippet
     * @param Ticket $ticket
     *
     * @return null|string
     */
    public function formatSnippet($snippet, Ticket $ticket)
    {
        $data = $this->getVars($ticket);

        try {
            return $this->getTemplateRenderer()->renderStringTemplate($snippet->snippet, $data);
        } catch (\Exception $e) {
            return $snippet->snippet;
        }
    }

    /**
     * @param string $text
     * @param Ticket $ticket
     *
     * @return null|string
     */
    public function formatText($text, Ticket $ticket)
    {
        $data = $this->getVars($ticket);

        try {
            return $this->getTemplateRenderer()->renderStringTemplate($text, $data);
        } catch (\Exception $e) {
            return $text;
        }
    }

    /**
     * @return TwigTemplateRenderer
     */
    protected function getTemplateRenderer()
    {
        return new TwigTemplateRenderer($this->twig, App::$container->get('brand_aware_settings_resolver'));
    }
}
