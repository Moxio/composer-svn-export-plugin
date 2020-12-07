<?php

namespace Moxio\Composer\SvnExportPlugin\Test\Downloader;

use Composer\Config;
use Composer\IO\BufferIO;
use Composer\Package\Package;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;
use Moxio\Composer\SvnExportPlugin\Downloader\SvnExportDownloader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SvnExportDownloaderTest extends TestCase {
	/** @var BufferIO */
	private $io;
	/** @var Config */
	private $config;
	/** @var MockObject|ProcessExecutor */
	private $process;
	/** @var MockObject|Filesystem */
	private $filesystem;
	/** @var SvnExportDownloader */
	private $downloader;

	protected function setUp() {
		$this->io = new BufferIO();
		$this->config = new Config(false);
		$this->process = $this->createMock(ProcessExecutor::class);
		$this->filesystem = $this->createMock(Filesystem::class);
		$this->downloader = new SvnExportDownloader($this->io, $this->config, $this->process, $this->filesystem);
	}

	public function testDownload() {
		$package = new Package('foo/bar', '1.0.0', 'baz');
		$package->setSourceReference('trunk');
		$package->setSourceUrl('https://foo.bar/baz');
		$path = '/foo/bar/baz';
		$this->process->expects($this->once())
			->method('execute')
			->with($this->stringStartsWith('svn export --force'))
			->willReturn(0);
		$this->downloader->download($package, $path);
	}

	public function testUpdate() {
		$initial_package = new Package('foo/bar', '1.0.0', 'baz');
		$initial_package->setSourceReference('trunk');
		$initial_package->setSourceUrl('https://foo.bar/baz');
		$target_package = new Package('foo/bar', '1.0.1', 'baz qux');
		$target_package->setSourceReference('trunk');
		$target_package->setSourceUrl('https://foo.bar/baz');
		$path = '/foo/bar/baz';

		$this->filesystem->expects($this->once())
			->method('removeDirectory')
			->willReturn(true);
		$this->process->expects($this->once())
			->method('execute')
			->with($this->stringStartsWith('svn export --force'))
			->willReturn(0);
		$this->downloader->update($initial_package, $target_package, $path);
	}

	public function testRemove() {
		$package = new Package('foo/bar', '1.0.0', 'baz');
		$package->setSourceReference('trunk');
		$package->setSourceUrl('https://foo.bar/baz');
		$path = '/foo/bar/baz';
		$this->filesystem->expects($this->once())
			->method('removeDirectory')
			->willReturn(true);
		$this->downloader->remove($package, $path);
	}
}