<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace spec\Application\DeskPRO\Entity;

use PhpSpec\ObjectBehavior;

/**
 * @mixin \Application\DeskPRO\Entity\Blob
 */
class BlobSpec extends ObjectBehavior
{
    public function it_checks_text_content_type()
    {
        $this->setFilename('myfile.txt');
        $this->getContentType()->shouldReturn('text/plain');
    }

    public function it_checks_image_content_type()
    {
        $this->setFilename('myfile.png');
        $this->getContentType()->shouldReturn('image/png');
    }

    public function it_checks_basic_name()
    {
        $this->setFilename('myfile.txt');
        $this->getFilename()->shouldReturn('myfile.txt');
    }

    public function it_checks_trim_down_name()
    {
        $this->setFilename(str_repeat('a', 500).'.jpg');
        $this->getFilename()->shouldReturn(str_repeat('a', 251).'.jpg');
        $this->getContentType()->shouldReturn('image/jpeg');
    }

    public function it_checks_empty_filename()
    {
        $this->setFilename('myfile');
        $this->getFilename()->shouldReturn('myfile');
    }

    public function it_checks_empty_extension()
    {
        $this->setFilename('.jpg');
        $this->getFilename()->shouldReturn('_.jpg');
        $this->getContentType()->shouldReturn('image/jpeg');
    }
}
