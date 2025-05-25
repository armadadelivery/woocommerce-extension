# Armada Delivery For WooCommerce

A WooCommerce extension that integrates with Armada Delivery service, allowing merchants to easily ship orders, track deliveries, and manage shipping information. Inspired by [Create Woo Extension](https://github.com/woocommerce/woocommerce/blob/trunk/packages/js/create-woo-extension/README.md).

## Getting Started

### Prerequisites

-   [NPM](https://www.npmjs.com/)
-   [Composer](https://getcomposer.org/download/)
-   [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)

### Installation and Build

```
npm install
npm run build
wp-env start
```

Visit the added page at http://localhost:8888/wp-admin/admin.php?page=wc-admin&path=%2Fexample.

## Releasing the Plugin

This plugin uses GitHub Actions to automate the release process.

### Automatic Version Bumping

We provide a script to help update version numbers across all relevant files:

```bash
# Make the script executable (first time only)
chmod +x .github/scripts/bump-version.sh

# Run the script with the new version
.github/scripts/bump-version.sh 1.0.0
```

This will update the version number in:
- package.json
- armada-plugin.php
- readme.txt (if it has a "Stable tag" field)

### Creating a Release

#### Option 1: Automatic Release (Recommended)

1. Update the version numbers using the script above
2. Commit the changes:
   ```bash
   git add .
   git commit -m "Bump version to X.Y.Z"
   ```
3. Create and push a tag:
   ```bash
   git tag vX.Y.Z
   git push origin vX.Y.Z
   ```
4. The GitHub Action will automatically build the plugin and create a release with the zip file attached

#### Option 2: Manual Release

1. Go to the "Actions" tab in the GitHub repository
2. Select the "Release Plugin" workflow
3. Click "Run workflow"
4. Enter the version number (without the 'v' prefix) and click "Run workflow"

For more details, see the [GitHub Workflows README](.github/workflows/README.md).

### Release Package Contents

The release package is optimized for production use and excludes development-only files and directories. The following are excluded from the release package:

- Development configuration files (`.editorconfig`, `.eslintrc.js`, etc.)
- Source files (`src/` directory - only built files are included)
- Development tools and dependencies (`node_modules/`, certain `vendor/` directories)
- Test files and directories (`tests/`, `phpunit/`, `*.test.js`)
- Version control files (`.git/`, `.github/`)
- Build process configurations (`.travis.yml`, etc.)

Important files like `composer.json` are kept in the release to maintain transparency and allow for code review and forking. The complete list of exclusions can be found in the `.distignore` file.
