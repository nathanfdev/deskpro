<?php

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
