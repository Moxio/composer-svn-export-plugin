# composer-svn-export-plugin
Configures composer to use `svn export` when retrieving packages from svn repositories (which is much faster than a full checkout).

## How it works
The SvnExportDownloader replaces the normal composer SvnDownloader. This means that you do not have to change your composer.json to use it. The use of `svn export` is, unfortunately, not configurable at runtime (composer makes this very hard to do right), so you should probably only install the plugin in an environment where you regularly do clean installs (i.e. on a build server).
