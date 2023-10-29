# scripts/update-versions.sh

#!/bin/bash

# Remove the 'v' prefix from the tag name
VERSION=${1//v}

# Update the version in composer.json
jq '.version = "'$VERSION'"' composer.json > composer.tmp.json && mv composer.tmp.json composer.json

# Update the version in package.json
jq '.version = "'$VERSION'"' package.json > package.tmp.json && mv package.tmp.json package.json
