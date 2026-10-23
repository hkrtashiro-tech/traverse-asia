#!/bin/sh
# Publishes the theme folder to the theme-release branch, where Git Updater on
# the live site looks for updates. Bump "Version" in style.css first.
set -e
cd "$(dirname "$0")/.."
git subtree split --prefix=wp-content/themes/traverse-asia -b theme-release
git push -u origin theme-release
