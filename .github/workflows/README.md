# GitHub Workflows

This directory contains GitHub Actions workflows for automating various tasks in the Armada Delivery For WooCommerce plugin repository.

## Helper Scripts

The repository includes helper scripts to make the release process easier:

- **Version Bumping Script**: Located at `.github/scripts/bump-version.sh`, this script automatically updates version numbers across all relevant files.

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

## Release Workflow

The `release.yml` workflow automates the process of building and releasing the plugin.

### Triggers

The workflow can be triggered in two ways:

1. **Automatically** when pushing a tag that starts with `v` (e.g., `v1.0.0`, `v2.3.1`)
2. **Manually** from the GitHub Actions tab, where you can specify the version number

### What the workflow does

1. Sets up PHP 7.4 and Node.js 22
2. Installs PHP dependencies using Composer (without dev dependencies)
3. Installs JavaScript dependencies using npm
4. Builds the plugin assets
5. Creates a plugin zip file
6. Creates a GitHub release with the plugin zip attached

### How to use

#### Automatic release (recommended)

1. Update the version number in:
   - `package.json`
   - `armada-plugin.php`
   - Any other files that contain version information

2. Commit these changes:
   ```bash
   git add .
   git commit -m "Bump version to X.Y.Z"
   ```

3. Create and push a tag:
   ```bash
   git tag vX.Y.Z
   git push origin vX.Y.Z
   ```

   Replace `X.Y.Z` with your version number (e.g., `1.0.0`, `2.3.1`).

4. The workflow will automatically run and create a release with the plugin zip file.

#### Manual release

1. Go to the "Actions" tab in your GitHub repository
2. Select the "Release Plugin" workflow
3. Click "Run workflow"
4. Enter the version number (without the 'v' prefix) and click "Run workflow"
5. The workflow will run and create a release with the plugin zip file

### Notes

- The workflow uses the `GITHUB_TOKEN` secret which is automatically provided by GitHub
- The release will include a zip file of the plugin ready for installation in WordPress
- The release notes will include basic installation instructions
