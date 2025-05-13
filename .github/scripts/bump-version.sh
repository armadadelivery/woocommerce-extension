#!/bin/bash

# This script updates the version number in all relevant files
# Usage: ./bump-version.sh X.Y.Z

if [ $# -ne 1 ]; then
  echo "Usage: $0 <version>"
  echo "Example: $0 1.0.0"
  exit 1
fi

VERSION=$1

# Check if version follows semantic versioning format
if ! [[ $VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  echo "Error: Version must follow semantic versioning format (X.Y.Z)"
  exit 1
fi

echo "Updating version to $VERSION"

# Update package.json
if [ -f "package.json" ]; then
  echo "Updating package.json..."
  sed -i "s/\"version\": \"[0-9]*\.[0-9]*\.[0-9]*\"/\"version\": \"$VERSION\"/" package.json
fi

# Update main plugin file
if [ -f "armada-plugin.php" ]; then
  echo "Updating armada-plugin.php..."
  sed -i "s/Version: [0-9]*\.[0-9]*\.[0-9]*/Version: $VERSION/" armada-plugin.php
fi

# Update readme.txt if it exists and has a stable tag
if [ -f "readme.txt" ]; then
  echo "Updating readme.txt..."
  if grep -q "Stable tag:" readme.txt; then
    sed -i "s/Stable tag: [0-9]*\.[0-9]*\.[0-9]*/Stable tag: $VERSION/" readme.txt
  fi
fi

echo "Version updated to $VERSION in all files"
echo ""
echo "Next steps:"
echo "1. Review the changes: git diff"
echo "2. Commit the changes: git add . && git commit -m \"Bump version to $VERSION\""
echo "3. Create a tag: git tag v$VERSION"
echo "4. Push changes and tag: git push origin main && git push origin v$VERSION"
