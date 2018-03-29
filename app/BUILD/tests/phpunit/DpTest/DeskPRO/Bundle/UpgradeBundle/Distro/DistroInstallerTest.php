<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\UpdateBundle\Distro;

use Alchemy\Zippy\Zippy;
use DeskPRO\Bundle\UpdateBundle\Distro\DistroInstaller;
use DeskPRO\Bundle\UpdateBundle\Instance\InstanceReader;
use DeskPRO\Component\Filesystem\TmpDir;
use DpTest\DeskProTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\Filesystem\Filesystem;

class DistroInstallerTest extends DeskProTestCase
{
    /**
     * @var Zippy
     */
    private $zippy;

    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var string[]
     */
    private $cleanupTmps = [];

    /**
     * @return string
     */
    private function getDistroZipPath()
    {
        return __DIR__.'/../data/distro_15742.0.zip';
    }

    public function setUp()
    {
        $this->zippy = Zippy::load();

        $this->root = vfsStream::setup('install_test');
        vfsStream::create(require(__DIR__.'/../data/app_structure.php'), $this->root);
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        $this->cleanupTmps;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->cleanupTempDirs();
    }

    /**
     * Removes temp dirs we have created.
     */
    public function cleanupTempDirs()
    {
        $fs = new Filesystem();

        foreach ($this->cleanupTmps as $d) {
            try {
                $fs->remove($d);
            } catch (\Exception $e) {
            }
        }

        $this->cleanupTmps = [];
    }

    /**
     * @return string
     */
    private function tmpAppStructure()
    {
        $tmpDir = sys_get_temp_dir().'/'.uniqid('install_test');

        // Dumps virtual structure to real fs so we can test the real
        // way we'll install files

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->root->url()), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
            /** @var \SplFileInfo $item */
            if ($item->getFilename() === '.' || $item->getFilename() === '..') {
                continue;
            }

            $path     = str_replace($this->root->url().'/', '', $item->getPathname());
            $realPath = $tmpDir.'/'.$path;

            if ($item->isDir()) {
                mkdir($realPath, 0755, true);
            } else {
                file_put_contents($realPath, file_get_contents($item->getPathname()));
            }

            chmod($realPath, $item->getPerms());
        }

        $this->cleanupTmps[] = $tmpDir;

        return $tmpDir;
    }

    /**
     * @test
     */
    public function it_installs_dirs()
    {
        $tmpDir = $this->tmpAppStructure();

        $instance = new InstanceReader(
            '15740.0',
            $tmpDir.'/app',
            $tmpDir.'/www',
            $tmpDir.'/var/kernel_cache'
        );
        $inst = new DistroInstaller($this->zippy, $instance);
        $inst->installFromZip($this->getDistroZipPath());

        $this->assertTrue(is_file($tmpDir.'/app/15742.0/sys/config/build_num.txt'), 'Check that app dir was copied');
        $this->assertTrue(is_file($tmpDir.'/var/kernel_cache/15742.0/file.txt'), 'Check that kernel cache dir was copied');
        $this->assertTrue(is_dir($tmpDir.'/var/kernel_cache/15742.0/dp_run'), 'Check that dp_run was copied to kernel cache dir');
        $this->assertTrue(is_file($tmpDir.'/www/assets/15742.0/pub/file.txt'), 'Check that pub dir was copied');
        $this->assertTrue(is_file($tmpDir.'/www/assets/15742.0/web/file.txt'), 'Check that web dir was copied');
        $this->assertTrue(is_file($tmpDir.'/app/15742.0/sys/Resources/deskpro.zip'), 'Check that deskpro.zip was 
        copied');
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Filesystem\Exception\IOException
     */
    public function it_throws_exception_on_perm_error()
    {
        $tmpDir = $this->tmpAppStructure();

        chmod($tmpDir.'/app', 0555);

        $instance = new InstanceReader(
            '15740.0',
            $tmpDir.'/app',
            $tmpDir.'/www',
            $tmpDir.'/var/kernel_cache'
        );
        $inst = new DistroInstaller($this->zippy, $instance);
        $inst->installFromZip($this->getDistroZipPath());
    }

    /**
     * @test
     */
    public function it_detects_perm_errors()
    {
        $tmpDir = $this->tmpAppStructure();

        chmod($tmpDir, 0555);
        chmod($tmpDir.'/app', 0555);
        chmod($tmpDir.'/www/assets', 0555);
        chmod($tmpDir.'/var/kernel_cache', 0555);

        $instance = new InstanceReader(
            '15740.0',
            $tmpDir.'/app',
            $tmpDir.'/www',
            $tmpDir.'/var/kernel_cache'
        );
        $inst = new DistroInstaller($this->zippy, $instance);

        $probs    = $inst->detectProblems();
        $probKeys = array_keys($probs);

        $this->assertEquals([
            'app_dir_not_writable',
            'kernel_cache_dir_not_writable',
            'assets_dir_not_writable',
        ], $probKeys);
    }

    /**
     * @test
     */
    public function it_detects_no_perm_errors()
    {
        $tmpDir = $this->tmpAppStructure();

        $instance = new InstanceReader(
            '15740.0',
            $tmpDir.'/app',
            $tmpDir.'/www',
            $tmpDir.'/var/kernel_cache'
        );
        $inst = new DistroInstaller($this->zippy, $instance);

        $probs = $inst->detectProblems();
        $this->assertEmpty($probs);
    }

    /**
     * @test
     */
    public function it_detects_tmpdir_error()
    {
        $fakeTmpDir = TmpDir::makeTmpDir(sys_get_temp_dir());
        chmod($fakeTmpDir, 0555);

        $tmpDir = $this->tmpAppStructure();

        $instance = new InstanceReader(
            '15740.0',
            $tmpDir.'/app',
            $tmpDir.'/www',
            $tmpDir.'/var/kernel_cache'
        );
        $inst = new DistroInstaller($this->zippy, $instance, $fakeTmpDir);

        $probs    = $inst->detectProblems();
        $probKeys = array_keys($probs);

        $this->assertEquals([
            'tmp_dir_not_writable',
        ], $probKeys);
    }
}
