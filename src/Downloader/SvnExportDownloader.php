<?php

namespace Moxio\Composer\SvnExportPlugin\Downloader;

use Composer\Config;
use Composer\Downloader\DownloaderInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\VcsRepository;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;
use Composer\Util\Svn as SvnUtil;

class SvnExportDownloader implements DownloaderInterface {
	/** @var IOInterface */
	private $io;
	/** @var Config */
	private $config;
	/** @var ProcessExecutor */
	private $process;
	/** @var Filesystem */
	private $filesystem;
	/** @var bool */
	private $cacheCredentials;

	public function __construct(IOInterface $io, Config $config, ProcessExecutor $process = null, Filesystem $filesystem = null) {
		$this->io = $io;
		$this->config = $config;
		$this->process = $process ?: new ProcessExecutor($io);
		$this->filesystem = $filesystem ?? new Filesystem($this->process);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInstallationSource() {
		return 'source';
	}

	/**
	 * {@inheritDoc}
	 */
	public function download(PackageInterface $package, $path, $output = true) {
		if (!$package->getSourceReference()) {
			throw new \InvalidArgumentException('Package '.$package->getPrettyName().' is missing reference information');
		}

		if ($output) {
			$this->io->writeError("  - Installing <info>" . $package->getName() . "</info> (<comment>" . $package->getFullPrettyVersion() . "</comment>): ", false);
		}
		$this->filesystem->emptyDirectory($path);

		$urls = $package->getSourceUrls();
		while ($url = array_shift($urls)) {
			try {
				if (Filesystem::isLocalPath($url)) {
					// realpath() below will not understand
					// url that starts with "file://"
					$needle = 'file://';
					$isFileProtocol = false;
					if (0 === strpos($url, $needle)) {
						$url = substr($url, strlen($needle));
						$isFileProtocol = true;
					}

					// realpath() below will not understand %20 spaces etc.
					if (false !== strpos($url, '%')) {
						$url = rawurldecode($url);
					}

					$url = realpath($url);

					if ($isFileProtocol) {
						$url = $needle . $url;
					}
				}

				$this->doDownload($package, $path, $url);
				break;
			} catch (\Exception $e) {
				// rethrow phpunit exceptions to avoid hard to debug bug failures
				if ($e instanceof \PHPUnit_Framework_Exception) {
					throw $e;
				}
				if ($this->io->isDebug()) {
					$this->io->writeError('Failed: ['.get_class($e).'] '.$e->getMessage());
				} elseif (count($urls)) {
					$this->io->writeError('    Failed, trying the next URL');
				}
				if (!count($urls)) {
					throw $e;
				}
			}
		}
	}

	/**
	 * Downloads specific package into specific folder.
	 *
	 * @param PackageInterface $package package instance
	 * @param string           $path    download path
	 * @param string           $url     package url
	 */
	public function doDownload(PackageInterface $package, $path, $url)
	{
		SvnUtil::cleanEnv();
		$ref = $package->getSourceReference();

		$repo = $package->getRepository();
		if ($repo instanceof VcsRepository) {
			$repoConfig = $repo->getRepoConfig();
			if (array_key_exists('svn-cache-credentials', $repoConfig)) {
				$this->cacheCredentials = (bool) $repoConfig['svn-cache-credentials'];
			}
		}

		$this->io->writeError(" Exporting ".$package->getSourceReference());
		$this->execute($url, "svn export --force", sprintf("%s/%s", $url, $ref), null, $path);
	}

	/**
	 * {@inheritDoc}
	 */
	public function update(PackageInterface $initial, PackageInterface $target, $path, $output = true) {
		$name = $target->getName();
		$from = $initial->getPrettyVersion();
		$to = $target->getPrettyVersion();

		$this->io->writeError("  - Updating <info>" . $name . "</info> (<comment>" . $from . "</comment> => <comment>" . $to . "</comment>): ", false);

		$this->remove($initial, $path, false);
		$this->download($target, $path, false);

		$this->io->writeError('');
	}

	/**
	 * Execute an SVN command and try to fix up the process with credentials
	 * if necessary.
	 *
	 * @param  string            $baseUrl Base URL of the repository
	 * @param  string            $command SVN command to run
	 * @param  string            $url     SVN url
	 * @param  string            $cwd     Working directory
	 * @param  string            $path    Target for a checkout
	 * @throws \RuntimeException
	 * @return string
	 */
	protected function execute($baseUrl, $command, $url, $cwd = null, $path = null) {
		$util = new SvnUtil($baseUrl, $this->io, $this->config, $this->process);
		$util->setCacheCredentials($this->cacheCredentials);
//		try {
			return $util->execute($command, $url, $cwd, $path, $this->io->isVerbose());
//		} catch (\RuntimeException $e) {
//			throw new \RuntimeException(
//				'Package could not be downloaded, '.$e->getMessage()
//			);
//		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove(PackageInterface $package, $path, $output = true) {
		if ($output) {
			$this->io->writeError("  - Removing <info>" . $package->getName() . "</info> (<comment>" . $package->getPrettyVersion() . "</comment>)");
		}
		if (!$this->filesystem->removeDirectory($path)) {
			throw new \RuntimeException('Could not completely delete '.$path.', aborting.');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function setOutputProgress($outputProgress) {
		return $this;
	}
}