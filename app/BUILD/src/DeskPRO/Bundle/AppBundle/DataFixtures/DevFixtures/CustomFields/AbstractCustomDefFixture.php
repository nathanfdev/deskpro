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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures\CustomFields;

use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\DataFixtures\DeskProAbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

/**
 * Class AbstractCustomDefFixture.
 */
abstract class AbstractCustomDefFixture extends DeskProAbstractFixture implements OrderedFixtureInterface
{
    protected $cnt = 1;

    /**
     * @return CustomDefAbstract
     */
    abstract protected function initiateEntity();

    /**
     * @param string     $type
     * @param string     $title
     * @param array|null $choices
     *
     * @return CustomDefAbstract
     */
    protected function createField($type, $title, array $choices = null)
    {
        $handlers = 'Application\DeskPRO\CustomFields\Handler\\';
        $options  = [];
        switch ($type) {
            case 'text':
                $handler_class = $handlers.'Text';
                break;
            case 'textarea':
                $handler_class = $handlers.'Textarea';
                break;
            case 'date':
                $handler_class = $handlers.'Date';
                break;
            case 'datetime':
                $handler_class = $handlers.'DateTime';
                break;
            case 'select':
                $handler_class = $handlers.'Choice';
                break;
            case 'multiselect':
                $handler_class       = $handlers.'Choice';
                $options['multiple'] = true;
                break;
            case 'checkbox':
                $handler_class       = $handlers.'Choice';
                $options['multiple'] = true;
                $options['expanded'] = true;
                break;
            case 'radio':
                $handler_class       = $handlers.'Choice';
                $options['multiple'] = false;
                $options['expanded'] = true;
                break;
            default:
                throw new \InvalidArgumentException();
        }

        $f                  = $this->initiateEntity();
        $f->title           = $title;
        $f->description     = 'A custom '.$f->getWidgetType().' field';
        $f->handler_class   = $handler_class;
        $f->options         = $options;
        $f->is_user_enabled = true;
        $f->is_enabled      = true;
        $f->display_order   = $this->cnt++;

        $this->manager->persist($f);
        $this->manager->flush();

        if ($handler_class === $handlers.'Choice' && $choices) {
            foreach ($choices as $c) {
                $this->_createSubOptions($f, null, $c);
            }
        }

        return $f;
    }

    /**
     * @param CustomDefAbstract      $parent
     * @param CustomDefAbstract|null $parent_opt
     * @param array|string           $desc
     *
     * @return CustomDefAbstract
     */
    protected function _createSubOptions(CustomDefAbstract $parent, CustomDefAbstract $parent_opt = null, $desc)
    {
        if (is_array($desc)) {
            $title  = $desc[0];
            $others = $desc[1];
        } else {
            $title  = $desc;
            $others = [];
        }

        $opt_f                  = $this->initiateEntity();
        $opt_f->parent          = $parent;
        $opt_f->title           = $title;
        $opt_f->description     = '';
        $opt_f->is_user_enabled = true;
        $opt_f->is_enabled      = true;
        $opt_f->display_order   = $this->cnt++;

        if ($parent_opt) {
            $opt_f->setOption('parent_id', $parent_opt->getId());
        }

        $parent->addChild($opt_f);

        $this->manager->persist($opt_f);
        $this->manager->flush();

        if ($others) {
            foreach ($others as $sub_title) {
                $this->_createSubOptions($parent, $opt_f, $sub_title);
            }
        }

        return $opt_f;
    }
}
