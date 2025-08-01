#! /bin/bash

# This script downloads phpunit.xml.dist for specified MediaWiki branches.
# It should be run from the 'mediawiki' directory.

MW_BRANCH=$1

if [ -z "$MW_BRANCH" ]; then
  echo "Error: MediaWiki branch not provided."
  exit 1
fi

echo "Attempting to download phpunit.xml.dist for MW branch ${MW_BRANCH}"

wget https://raw.githubusercontent.com/wikimedia/mediawiki/${MW_BRANCH}/phpunit.xml.dist -O phpunit.xml.dist

if [ ! -f phpunit.xml.dist ]; then
  echo "Error: phpunit.xml.dist was not downloaded!"
  exit 1
fi

if [ ! -s phpunit.xml.dist ]; then
  echo "Error: phpunit.xml.dist is empty!"
  exit 1
fi

echo "phpunit.xml.dist downloaded successfully."
