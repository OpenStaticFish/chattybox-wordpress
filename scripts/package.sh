#!/bin/sh
# Package committed runtime files only. Directory artwork belongs in SVN /assets.
set -eu
cd "$(git rev-parse --show-toplevel)"
mkdir -p dist
git archive --format=zip --prefix=chattybox/ -o dist/chattybox.zip HEAD -- \
	chattybox.php includes/class-chattybox-plugin.php uninstall.php readme.txt LICENSE
sha256sum dist/chattybox.zip
