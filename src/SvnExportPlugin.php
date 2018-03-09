<?php

namespace Moxio\Composer\SvnExportPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Moxio\Composer\SvnExportPlugin\Downloader\SvnExportDownloader;
use Moxio\Composer\SvnExportPlugin\Repository\VcsRepository;

class SvnExportPlugin implements PluginInterface {
	public function activate(Composer $composer, IOInterface $io) {
		$composer->getDownloadManager()->setDownloader('svn', new SvnExportDownloader($io, $composer->getConfig()));
	}
}