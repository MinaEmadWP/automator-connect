# Automator Connect

Automator Connect extends Uncanny Automator with third-party integrations.

## Description

Automator Connect is a WordPress plugin that adds custom third-party integrations to Uncanny Automator. It is currently built around the Cloudways API and provides actions for managing Cloudways applications and operations.

## Features

* Cloudways connection settings page
* Cloudways API authentication and token caching
* Start application creation processes
* Check Cloudways operation status
* Start application removal processes
* Start application backup processes
* Dynamic server and application dropdowns in action fields

## Requirements

* WordPress 5.8 or higher
* PHP 7.4 or higher
* Uncanny Automator installed and active

## Installation

1. Upload the plugin files to the `wp-content/plugins/automator-connect` directory.
2. Activate the plugin in WordPress.
3. Open the Automator Connect settings page.
4. Enter your Cloudways email and API key.
5. Save the settings and confirm the connection.

## Usage

After connecting Cloudways, the available actions can be used inside Uncanny Automator recipes.

### Cloudways actions

* Start a Cloudways application creation process
* Get the status of a Cloudways operation
* Start a Cloudways application removal process
* Start a Cloudways application backup process

## Development Notes

* The plugin follows a modular structure inside `src/integrations/`.
* Each integration has its own folder.
* Classes use a consistent naming pattern and are autoloaded from the main plugin file.
* Cloudways API credentials are validated before saving.
* Operation responses are returned to Automator tokens for use in later recipe steps.

## Folder Structure

```text
automator-connect/
├── automator-connect.php
├── README.txt
└── src/
    └── integrations/
        └── cloudways/
            ├── actions/
            ├── helpers/
            ├── settings/
            ├── cloudways-integration.php
            └── load.php
```

## Changelog

### 1.0.0

* Initial release.
* Cloudways integration with settings page and actions.