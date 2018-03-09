<?php

namespace Moxio\Composer\SvnExportPlugin\Repository\Vcs;

class SvnDriver extends \Composer\Repository\Vcs\SvnDriver {
	public function getDist($identifier) {
		return array('type' => 'svn-export', 'url' => $this->baseUrl, 'reference' => $identifier);
	}
}