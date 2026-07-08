# Automator Connect

**Contributors:** minaemad \
**Tags:** uncanny automator, automation, cloudways, integration, workflow \
**Requires at least:** 5.8 \
**Tested up to:** 7.0 \
**Requires PHP:** 7.4 \
**Stable tag:** 1.0.0 \
**License:** GPLv2 or later \
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Extends Uncanny Automator with third-party plugin and app integrations.

## Description

Automator Connect adds new integrations to [Uncanny Automator](https://automatorplugin.com/), letting you build no-code recipes that manage third-party plugins and  services directly from WordPress.

### Supported apps
* [Cloudways](https://www.cloudways.com/), a managed cloud hosting platform.

The Automator Connect plugin is under active development; more integrations are planned.

## Automation Examples

Here are examples of automations you can create:

### Cloudways Automation

Once [Cloudways](https://www.cloudways.com/) is connected, your Automator recipes can:

* **Add an application** to a Cloudways server
* **Remove an application** from a Cloudways server
* **Start a backup** of a Cloudways application
* **Check the status** of a running Cloudways operation

Cloudways operations (like adding an app or starting a backup) run asynchronously on Cloudways' side. Automator Connect's actions return an operation ID as a token, which you can pass into the "Get the status of a Cloudways operation" action later in the same recipe (or a follow-up recipe) to check whether it finished.

## Requirements

* [Uncanny Automator](https://wordpress.org/plugins/uncanny-automator/) must be installed and active.
* A [Cloudways account](https://unified.cloudways.com/) with API access (email + API key from your Cloudways account settings) for the Cloudways integration.

## Support

This is an independently maintained plugin, not officially affiliated with Uncanny Automator or Cloudways. Please open an issue on [GitHub](https://github.com/MinaEmadWP/automator-connect) for bugs or feature requests.

## Installation

1. Upload the `automator-connect` folder to the `/wp-content/plugins/` directory, or install the plugin zip through the WordPress admin (Plugins > Add New > Upload Plugin).
2. Activate Automator Connect through the 'Plugins' menu in WordPress.
3. Go to Automator > App Integrations, find the app you want to connect (for example, Cloudways), and connect your account with the required credentials.
4. Build a recipe in Uncanny Automator and add any of the triggers/actions.

## Frequently Asked Questions

### Do I need an Uncanny Automator Pro license?

No. Automator Connect works with the free version of Uncanny Automator.

### Where do I find my Cloudways API key?

In your [Cloudways account](https://unified.cloudways.com/), go to Account > API Access to generate an API key. You'll need this along with your Cloudways account email to connect the integration. For more information, follow the [step-by-Step getting started guide](https://support.cloudways.com/en/articles/5136065-how-to-use-the-cloudways-api).

### What happens if I disconnect the integration?

Disconnecting clears your saved app credentials. Any recipes using the integration triggers/actions will fail until you reconnect.

### Are more integrations planned?

Yes. Cloudways is the first of several planned integrations.

## Changelog

### 1.0.0
* Initial release.
* Cloudways integration: connect an account, add/remove applications, start application backups, and check operation status.
