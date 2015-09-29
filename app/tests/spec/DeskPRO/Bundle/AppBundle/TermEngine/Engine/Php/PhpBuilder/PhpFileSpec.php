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
namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder;

use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpFile
 */
class PhpFileSpec extends ObjectBehavior
{
    public function it_can_have_a_filename()
    {
        $this->getFileName()->shouldBe(null);

        $this->setFileName('file.php');

        $this->getFileName()->shouldBe('file.php');
    }

    public function it_can_have_a_filesystem_dir()
    {
        $this->getDir()->shouldBe(null);

        $this->setDir('/home/tickner');

        $this->getDir()->shouldBe('/home/tickner');
    }

    public function it_can_have_a_set_namespace()
    {
        $this->getNamespace()->shouldBe(null);

        $this->setNamespace('Just\A\Namespace');

        $this->getNamespace()->shouldBe('Just\A\Namespace');
    }

    public function it_can_hold_a_single_class(
        \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpClass $class
    ) {
        $this->getClass()->shouldBe(null);

        $this->setClass($class);

        $this->getClass()->shouldBe($class);
    }

    public function it_can_convert_to_string(
        \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpClass $class
    ) {
        $class->__toString()->willReturn(
            'class MyClass extends \SplFileInfo
{
    protected $id;

    public function __construct($id, $path)
    {
        parent::construct($path);
        $this->id = $id;
    }
}'
        );

        $this->setFilename('MyClass.php');
        $this->setDir('/home/tickner/Just/A/Namespace');
        $this->setNamespace('Just\A\Namespace');
        $this->setClass($class);

        $this->__toString()->shouldBeLike(
            '<?php

namespace Just\A\Namespace;

class MyClass extends \SplFileInfo
{
    protected $id;

    public function __construct($id, $path)
    {
        parent::construct($path);
        $this->id = $id;
    }
}
'
        );
    }

    public function it_will_work_without_a_namespace(
        \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpClass $class
    ) {
        $class->__toString()->willReturn(
            'class MyClass extends \SplFileInfo
{
    protected $id;

    public function __construct($id, $path)
    {
        parent::construct($path);
        $this->id = $id;
    }
}'
        );

        $this->setFilename('MyClass.php');
        $this->setDir('/home/tickner/Just/A/Namespace');
        $this->setClass($class);

        $this->__toString()->shouldBeLike(
            '<?php

class MyClass extends \SplFileInfo
{
    protected $id;

    public function __construct($id, $path)
    {
        parent::construct($path);
        $this->id = $id;
    }
}
'
        );
    }

    public function it_will_even_work_without_a_class()
    {
        $this->setFilename('MyClass.php');
        $this->setDir('/home/tickner/Just/A/Namespace');

        $this->__toString()->shouldBeLike(
            '<?php
'
        );
    }
}
