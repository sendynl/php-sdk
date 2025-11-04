#!/usr/bin/env bash
# Print the current version of the project and bump it to the given version.

set -e

current_version=$(echo "echo Connection::VERSION . PHP_EOL;" | cat src/Connection.php - | php)
echo "Current version: $current_version"

if [[ -z "$1" ]]
then
  echo "To bump the version, provide the new version number as an argument."
  exit 1
fi

# Remove the 'v' prefix if it exists
new_version=${1#v}

echo "New version: $new_version"

if ! [[ "$new_version" =~ ^[0-9]+\.[0-9]+\.[0-9](-[a-z]+\.[0-9]+)?$ ]]
then
  echo "Invalid version format. Please use semantic versioning (https://semver.org/)."
  exit 1
fi

echo "Bumping version to: $new_version"

perl -pi -e "s/^    public const VERSION = .*/    public const VERSION = '$new_version';/" src/Connection.php

echo
echo "To release the new version, first, commit the changes:"
echo "  git add --all"
echo "  git commit -m "$new_version""
echo "  git push"
echo
echo "Once the commit is pushed to the master branch, create a release on GitHub to distribute the new version:"
echo "  https://github.com/sendynl/php-sdk/releases/new?tag=v$new_version"
