<?php

namespace Moxio\Composer\SvnExportPlugin\Repository;

use Composer\Config;
use Composer\EventDispatcher\EventDispatcher;
use Composer\IO\IOInterface;
use Moxio\Composer\SvnExportPlugin\Repository\Vcs\SvnDriver;

class VcsRepository extends \Composer\Repository\VcsRepository {
	public function __construct(array $repoConfig, IOInterface $io, Config $config, EventDispatcher $dispatcher = null) {
		parent::__construct($repoConfig, $io, $config, $dispatcher, [
			'github' => 'Composer\Repository\Vcs\GitHubDriver',
			'gitlab' => 'Composer\Repository\Vcs\GitLabDriver',
			'git-bitbucket' => 'Composer\Repository\Vcs\GitBitbucketDriver',
			'git' => 'Composer\Repository\Vcs\GitDriver',
			'hg-bitbucket' => 'Composer\Repository\Vcs\HgBitbucketDriver',
			'hg' => 'Composer\Repository\Vcs\HgDriver',
			'perforce' => 'Composer\Repository\Vcs\PerforceDriver',
			'fossil' => 'Composer\Repository\Vcs\FossilDriver',
			// svn must be last because identifying a subversion server for sure is practically impossible
			'svn' => SvnDriver::class,
		]);
	}
}