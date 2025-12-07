# Docker Image Management and Access Control

This document provides comprehensive guidance on managing Docker images centrally in GitHub Container Registry (GHCR) and controlling who can view and access these images.

## Table of Contents

1. [Overview](#overview)
2. [GitHub Container Registry (GHCR)](#github-container-registry-ghcr)
3. [Publishing Docker Images](#publishing-docker-images)
4. [Access Control](#access-control)
5. [Managing Package Visibility](#managing-package-visibility)
6. [Authentication and Authorization](#authentication-and-authorization)
7. [Team and Organization Access](#team-and-organization-access)
8. [Best Practices](#best-practices)
9. [Troubleshooting](#troubleshooting)

---

## Overview

ChiBank uses GitHub Container Registry (GHCR) to centrally manage Docker images. GHCR provides:

- **Centralized Storage**: All Docker images are stored in one location
- **Access Control**: Fine-grained permissions to control who can view and pull images
- **Version Management**: Easy tracking and management of image versions
- **Integration**: Seamless integration with GitHub Actions for CI/CD
- **Security**: Built-in security scanning and vulnerability detection

### Current Setup

The ChiBank Docker images are published to:
```
ghcr.io/hhongli1979-coder/chibank999:main
ghcr.io/hhongli1979-coder/chibank999:v*
ghcr.io/hhongli1979-coder/chibank999:sha-*
```

---

## GitHub Container Registry (GHCR)

### What is GHCR?

GitHub Container Registry is GitHub's container image hosting service that allows you to store and manage Docker and OCI images directly in your GitHub account or organization.

### Key Features

1. **Free for Public Repositories**: Unlimited storage and bandwidth for public images
2. **Private Images**: Support for private images with usage limits based on your GitHub plan
3. **Fine-grained Access**: Control access at the organization, team, and individual user level
4. **GitHub Actions Integration**: Automatic authentication in workflows
5. **Security Scanning**: Automated vulnerability scanning for images

### Storage Locations

- **Personal Account**: `ghcr.io/username/image-name`
- **Organization**: `ghcr.io/organization/image-name`

---

## Publishing Docker Images

### Automated Publishing with GitHub Actions

ChiBank uses GitHub Actions to automatically build and publish Docker images. The workflow is defined in `.github/workflows/docker-image.yml`.

#### Workflow Triggers

Images are automatically built and published when:

1. **Push to main/master branch**: Creates `main` or `master` tag
2. **Creating version tags**: Push tags like `v1.0.0` to create versioned images
3. **Pull Requests**: Builds images for testing (not published)
4. **Manual Dispatch**: Can be triggered manually from GitHub Actions UI

#### Image Tags

The workflow automatically creates multiple tags:

- `main` - Latest version from main branch
- `v1.0.0` - Semantic version tags
- `sha-abc123` - Git commit SHA tags

### Manual Publishing

To manually build and push an image:

```bash
# Login to GHCR
echo $GITHUB_TOKEN | docker login ghcr.io -u USERNAME --password-stdin

# Build the image
docker build -t ghcr.io/hhongli1979-coder/chibank999:latest .

# Push the image
docker push ghcr.io/hhongli1979-coder/chibank999:latest
```

### Using Personal Access Token (PAT)

For manual operations, create a Personal Access Token with appropriate permissions:

1. Go to GitHub Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Click "Generate new token (classic)"
3. Select scopes:
   - `write:packages` - Upload packages to GitHub Package Registry
   - `read:packages` - Download packages from GitHub Package Registry
   - `delete:packages` - Delete packages from GitHub Package Registry
4. Click "Generate token" and save it securely

```bash
# Login using PAT
echo $PAT | docker login ghcr.io -u USERNAME --password-stdin
```

---

## Access Control

### Understanding Permissions

GHCR uses a hierarchical permission model:

1. **Repository Permissions**: Inherited from the linked GitHub repository
2. **Package Permissions**: Can be configured independently of the repository
3. **Organization Permissions**: Apply to all members of an organization

### Permission Levels

#### For Public Packages

- **Public**: Anyone can pull the image (no authentication required)
- **Internal**: Only organization members can pull (requires authentication)
- **Private**: Only specified users/teams can pull

#### For Private Packages

- **Read**: Can pull and view the image
- **Write**: Can pull and push new versions
- **Admin**: Full control including deleting versions and managing access

### Default Access

By default, packages inherit permissions from the source repository:

- If repository is public → package is public
- If repository is private → package is private
- Repository collaborators get corresponding package access

---

## Managing Package Visibility

### Changing Package Visibility

#### Via GitHub Web Interface

1. Navigate to the repository: `https://github.com/hhongli1979-coder/chibank999`
2. Click on "Packages" in the right sidebar
3. Select the package (e.g., `chibank999`)
4. Click "Package settings" at the bottom right
5. Under "Danger Zone", select visibility:
   - **Public**: Anyone can pull without authentication
   - **Private**: Only authorized users can pull
   - **Internal**: Only organization members can access

#### Making a Package Public

To make the ChiBank Docker image publicly accessible:

1. Go to `https://github.com/users/hhongli1979-coder/packages/container/chibank999/settings`
2. Scroll to "Danger Zone"
3. Click "Change visibility"
4. Select "Public"
5. Type the package name to confirm
6. Click "I understand, change package visibility"

**Note**: Public packages allow anyone to pull images without authentication:

```bash
# No login required for public images
docker pull ghcr.io/hhongli1979-coder/chibank999:main
```

#### Keeping a Package Private

Private packages require authentication:

```bash
# Login required
echo $GITHUB_TOKEN | docker login ghcr.io -u USERNAME --password-stdin

# Then pull
docker pull ghcr.io/hhongli1979-coder/chibank999:main
```

---

## Authentication and Authorization

### Authenticating to GHCR

#### Method 1: Using GitHub Token (Recommended for CI/CD)

```bash
echo $GITHUB_TOKEN | docker login ghcr.io -u USERNAME --password-stdin
```

#### Method 2: Using Personal Access Token

```bash
echo $PAT | docker login ghcr.io -u USERNAME --password-stdin
```

#### Method 3: Using GitHub CLI

```bash
gh auth token | docker login ghcr.io -u USERNAME --password-stdin
```

### Granting Access to Users

#### Adding Individual Users

1. Go to package settings
2. Click "Manage Actions access" or "Manage package access"
3. Click "Add people or teams"
4. Search for the user
5. Select permission level:
   - Read
   - Write
   - Admin
6. Click "Add"

#### For Organization Packages

Organization owners and members with appropriate permissions can:

1. Navigate to organization packages
2. Select the package
3. Click "Package settings"
4. Under "Manage Actions access", add teams or members
5. Set appropriate permission levels

### Revoking Access

1. Go to package settings
2. Find the user/team in the access list
3. Click the "X" or "Remove" button next to their name
4. Confirm the removal

---

## Team and Organization Access

### Organization-level Controls

#### Setting Default Permissions

For organizations, you can set default package permissions:

1. Go to Organization Settings
2. Navigate to "Packages" under "Code, planning, and automation"
3. Set default permissions for packages:
   - Public by default
   - Private by default
   - Inherit from repository

#### Managing Team Access

Grant access to entire teams:

1. Go to package settings
2. Click "Add people or teams"
3. Select a team
4. Choose permission level
5. All team members will receive the specified access

### Best Practices for Organizations

1. **Use Teams**: Grant access to teams rather than individual users for easier management
2. **Least Privilege**: Give users minimum required permissions
3. **Regular Audits**: Periodically review who has access to packages
4. **Separate Environments**: Use different packages or tags for dev/staging/production
5. **Document Access**: Maintain documentation of who should have access and why

### Repository-Package Linking

Link packages to repositories for better organization:

1. Go to package settings
2. Under "Danger Zone", click "Connect repository"
3. Select the repository
4. Package will now appear in repository's sidebar

Benefits of linking:
- Package inherits repository permissions by default
- Shows up in repository UI
- Easier for collaborators to discover

---

## Best Practices

### Security Best Practices

1. **Use PAT with Minimal Scopes**: Only grant necessary permissions
2. **Rotate Tokens Regularly**: Update PATs and secrets periodically
3. **Enable 2FA**: Require two-factor authentication for organization members
4. **Scan Images**: Regularly scan images for vulnerabilities
5. **Sign Images**: Use Docker Content Trust or sigstore for image signing
6. **Use Private Images for Sensitive Data**: Don't make images public if they contain sensitive information

### Version Management

1. **Use Semantic Versioning**: Tag images with clear version numbers (v1.0.0, v1.0.1)
2. **Tag Latest Carefully**: Keep `latest` tag for stable releases
3. **Maintain Multiple Tags**: Use branch tags (main), version tags (v1.0.0), and SHA tags
4. **Immutable Tags**: Don't overwrite existing version tags
5. **Clean Up Old Images**: Regularly remove unused images to save storage

### Documentation

1. **Document Pull Commands**: Clearly show how to pull images
2. **List Available Tags**: Keep a registry of available image tags
3. **Access Requirements**: Document who needs access and how to request it
4. **Update Instructions**: Keep this documentation current

### Monitoring and Maintenance

1. **Monitor Downloads**: Track package download metrics
2. **Review Access Logs**: Regularly audit access patterns
3. **Update Dependencies**: Keep base images and dependencies updated
4. **Automate Cleanup**: Set up workflows to remove old images
5. **Monitor Storage**: Track storage usage (important for private packages)

---

## Troubleshooting

### Common Issues

#### 1. "unauthorized: authentication required"

**Problem**: Cannot pull image without authentication

**Solution**:
```bash
# Login first
echo $GITHUB_TOKEN | docker login ghcr.io -u USERNAME --password-stdin

# Then pull
docker pull ghcr.io/hhongli1979-coder/chibank999:main
```

#### 2. "denied: permission_denied"

**Problem**: User has no permission to access the package

**Solutions**:
- Check if package is private (make it public or request access)
- Verify user has been granted appropriate permissions
- Confirm PAT has `read:packages` scope
- Check if organization SSO is enabled (may need to authorize PAT)

#### 3. Image Not Found

**Problem**: `Error: manifest unknown: manifest unknown`

**Solutions**:
- Verify image name and tag are correct
- Check if package exists in registry
- Ensure image was successfully published
- Review GitHub Actions workflow logs for build failures

#### 4. GitHub Actions Build Failures

**Problem**: Workflow fails to push image

**Solutions**:
- Check if `GITHUB_TOKEN` has `packages: write` permission (should be automatic)
- Verify Dockerfile has no errors
- Review workflow logs for specific error messages
- Ensure repository settings allow GitHub Actions to push packages

#### 5. Rate Limiting

**Problem**: Too many requests to GHCR

**Solutions**:
- Implement caching in CI/CD pipelines
- Use authenticated requests (higher rate limits)
- Consider using a mirror or proxy for frequently accessed images

### Getting Help

If you encounter issues:

1. **Check GitHub Status**: Visit [githubstatus.com](https://www.githubstatus.com)
2. **Review Documentation**: GitHub's official GHCR documentation
3. **Check Workflow Logs**: Review GitHub Actions logs for errors
4. **Contact Repository Maintainers**: Open an issue in the repository
5. **GitHub Support**: Contact GitHub Support for platform issues

---

## Additional Resources

### Official Documentation

- [GitHub Container Registry Documentation](https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry)
- [Authenticating to GitHub Packages](https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry#authenticating-to-the-container-registry)
- [Managing Package Access](https://docs.github.com/en/packages/learn-github-packages/configuring-a-packages-access-control-and-visibility)

### Related Documentation

- [Docker Deployment Guide](DEPLOYMENT-GUIDE.md)
- [GitHub Actions Workflow](.github/workflows/docker-image.yml)
- [Main README](../../README.md)

---

## Summary

This document covered:

- ✅ Setting up and using GitHub Container Registry
- ✅ Publishing Docker images automatically and manually
- ✅ Controlling access with fine-grained permissions
- ✅ Managing package visibility (public/private)
- ✅ Authenticating and authorizing users
- ✅ Managing team and organization-level access
- ✅ Best practices for security and management
- ✅ Troubleshooting common issues

For questions or improvements to this documentation, please open an issue in the repository.
