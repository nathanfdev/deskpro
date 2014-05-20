<?php
namespace DpUnitTests\DeskPRO\Hierarchy;

use Application\DeskPRO\Hierarchy\PreloadedHierarchy;

class PreloadedHierarchyTest extends \DpUnitTestCase
{
	/**
	 * @var TestCategory[]
	 */
	private $cats;

	/**
	 * @var \Application\DeskPRO\Hierarchy\PreloadedHierarchy
	 */
	private $hierarchy;

	public function runBefore()
	{
		$cats = array();
		$cats[1] = new TestCategory(1, null, 'CatA');
		$cats[2] = new TestCategory(2, null, 'CatB');
		$cats[3] = new TestCategory(3, $cats[2], 'CatB - Sub1');
		$cats[4] = new TestCategory(4, $cats[2], 'CatB - Sub2');
		$cats[5] = new TestCategory(5, $cats[2], 'CatB - Sub3');
		$cats[6] = new TestCategory(6, $cats[5], 'CatB - Sub3A');
		$cats[7] = new TestCategory(7, $cats[5], 'CatB - Sub3B');
		$cats[8] = new TestCategory(8, null, 'CatC');
		$cats[9] = new TestCategory(9, null, 'CatD');

		$this->cats = $cats;
		$this->hierarchy = new PreloadedHierarchy($cats);
	}

	public function testCounts()
	{
		$this->assertEquals(9, $this->hierarchy->count());
		$this->assertEquals(4, $this->hierarchy->countRoots());
		$this->assertEquals(0, $this->hierarchy->countChildren(1));
		$this->assertEquals(3, $this->hierarchy->countChildren(2));
		$this->assertEquals(2, $this->hierarchy->countChildren(5));
	}

	public function testChildage()
	{
		$child_ids = $this->hierarchy->getChildrenIds(2);
		$expect_child_ids = array(3,4,5);
		$this->assertEquals($expect_child_ids, $child_ids);

		$child_ids = $this->hierarchy->getChildrenIds(1);
		$expect_child_ids = array();
		$this->assertEquals($expect_child_ids, $child_ids);

		$this->assertTrue($this->hierarchy->hasChildren(2));
		$this->assertTrue($this->hierarchy->hasChildren(5));
		$this->assertFalse($this->hierarchy->hasChildren(1));
		$this->assertFalse($this->hierarchy->hasChildren(7));

		$this->assertFalse($this->hierarchy->isChild(2));
		$this->assertTrue($this->hierarchy->isChild(5));
		$this->assertFalse($this->hierarchy->isChild(1));
		$this->assertTrue($this->hierarchy->isChild(7));
	}

	public function testParentage()
	{
		$parent = $this->hierarchy->getParent(7);
		$this->assertEquals($this->cats[5], $parent);

		$path_ids = $this->hierarchy->getParentPathIds(7);
		$expect_path_ids = array(5, 2);

		$this->assertEquals($expect_path_ids, $path_ids);

		$path = $this->hierarchy->getParentPath(7);
		$expect_path= array($this->cats[5], $this->cats[2]);
		$this->assertEquals($expect_path, $path);

		$this->assertFalse($this->hierarchy->isRoot(7));
		$this->assertFalse($this->hierarchy->isRoot(5));
		$this->assertTrue($this->hierarchy->isRoot(1));
	}

	public function testGetRoots()
	{
		$roots = $this->hierarchy->getRoots();

		$expect_roots = array();
		foreach (array(1,2,8,9) as $id) {
			$expect_roots[] = $this->cats[$id];
		}

		$this->assertEquals($expect_roots, $roots);

		$root_ids = $this->hierarchy->getRootIds();
		$expect_root_ids = array(1,2,8,9);
		$this->assertEquals($expect_root_ids, $root_ids);
	}

	public function testGetAll()
	{
		$all = $this->hierarchy->getAll();
		$expect_all = array_values($this->cats);
		$this->assertEquals($expect_all, $all);

		$all_ids = $this->hierarchy->getAllIds();
		$expect_all_ids = array_keys($this->cats);
		$this->assertEquals($all_ids, $expect_all_ids);
	}

	public function testFlatArray()
	{
		$flat_array = $this->hierarchy->getFlatArray();

		$expect_flat_array = array();
		foreach ($this->cats as $cat) {
			$depth = 0;
			if (in_array($cat->id, array(3,4,5))) {
				$depth = 1;
			} else if (in_array($cat->id, array(6,7))) {
				$depth = 2;
			}

			$expect_flat_array[] = array(
				'object' => $cat,
				'depth'  => $depth
			);
		}

		$this->assertEquals($expect_flat_array, $flat_array);
	}
}

class TestCategory
{
	public $id;
	public $parent;
	public $title;

	public function __construct($id, $parent, $title)
	{
		$this->id = $id;
		$this->parent = $parent;
		$this->title = $title;
	}
}