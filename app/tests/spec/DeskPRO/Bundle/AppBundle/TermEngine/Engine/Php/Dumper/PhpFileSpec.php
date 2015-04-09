<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
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
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Dumper;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Dumper\PhpClass;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Dumper\PhpFile;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Dumper\PhpFile
 */
class PhpFileSpec extends ObjectBehavior
{
    function it_can_have_a_filename()
    {
        $this->getFileName()->shouldBe(null);

        $this->setFileName('file.php');

        $this->getFileName()->shouldBe('file.php');
    }

    function it_can_have_a_filesystem_dir()
    {
        $this->getDir()->shouldBe(null);

        $this->setDir('/home/tickner');

        $this->getDir()->shouldBe('/home/tickner');
    }

    function it_can_have_a_set_namespace()
    {
        $this->getNamespace()->shouldBe(null);

        $this->setNamespace('Just\A\Namespace');

        $this->getNamespace()->shouldBe('Just\A\Namespace');
    }

    function it_can_hold_a_single_class(
        PhpClass $class
    )
    {
        $this->getClass()->shouldBe(null);

        $this->setClass($class);

        $this->getClass()->shouldBe($class);
    }

    function it_can_convert_to_string(
        PhpClass $class
    )
    {
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

    function it_will_work_without_a_namespace(
        PhpClass $class
    )
    {
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

    function it_will_even_work_without_a_class()
    {
        $this->setFilename('MyClass.php');
        $this->setDir('/home/tickner/Just/A/Namespace');

        $this->__toString()->shouldBeLike(
            '<?php
'
        );
    }
}
