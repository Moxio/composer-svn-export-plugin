<?php

namespace Moxio\Composer\SvnExportPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Moxio\Composer\SvnExportPlugin\Downloader\SvnExportDownloader;

class SvnExportPlugin implements PluginInterface {
	public function activate(Composer $composer, IOInterface $io) {
		$composer->getDownloadManager()->setDownloader('svn', new SvnExportDownloader($io, $composer->getConfig()));
	}
}