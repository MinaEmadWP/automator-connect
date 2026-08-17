# Automator Connect

**Contributors:** Mina Emad (@MinaEmadWP) \
**Tags:** uncanny automator, automation, cloudways, wp ulike, kinsta, integration, workflow, actions, triggers \
**Requires at least:** 5.8 \
**Tested up to:** 7.0 \
**Requires PHP:** 7.4 \
**Stable tag:** 1.1.0 \
**License:** GPLv2 or later \
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Extends Uncanny Automator with third-party plugin and app integrations.

## Description

Automator Connect adds new integrations to [Uncanny Automator](https://automatorplugin.com/), letting you build no-code recipes that manage third-party plugins and services directly from WordPress.

### Supported Integrations

* [Cloudways](https://www.cloudways.com/), a managed cloud hosting platform.
* [Kinsta](https://kinsta.com/), a managed WordPress hosting platform.
* [WP ULike](https://wordpress.org/plugins/wp-ulike/), a WordPress plugin for adding like and engagement functionality to posts and comments.

The Automator Connect plugin is under active development; more integrations are planned.

## Automation Examples

Here are examples of automations you can create:

### Cloudways Automation

Once [Cloudways](https://www.cloudways.com/) is connected, your Automator recipes can:

* **Start adding an application** to a Cloudways server
* **Remove an application** from a Cloudways server
* **Start a backup** of a Cloudways application
* **Check the status** of a running Cloudways operation

Cloudways operations such as adding an application or starting a backup run asynchronously on Cloudways' side. Automator Connect's actions return an operation ID as a token, which you can pass into the "Get the status of a Cloudways operation" action later in the same recipe (or a follow-up recipe) to check whether the operation has finished.

### WP ULike Automation

Once [WP ULike](https://wordpress.org/plugins/wp-ulike/) is active, your Automator recipes can respond to:

* **A user likes a post type**
* **A user unlikes a post type**
* **A user likes a comment**
* **A user unlikes a comment**

The WP ULike triggers can provide information about the user and the liked post or comment as tokens, allowing you to use that data in subsequent actions.

### Kinsta Automation

Once [Kinsta](https://kinsta.com/) is connected, your Automator recipes can:

* **Start creating a site** on Kinsta
* **Start deleting a site** on Kinsta
* **Check the status** of a running Kinsta operation

Kinsta operations such as creating or deleting an application run asynchronously on Kinsta's side. Automator Connect's actions return an operation ID as a token, which you can pass into the "Get the status of a Kinsta operation" action later in the same recipe (or a follow-up recipe) to check whether the operation has finished.

## Requirements

* [Uncanny Automator](https://wordpress.org/plugins/uncanny-automator/) must be installed and active.
* The [WP ULike](https://wordpress.org/plugins/wp-ulike/) plugin must be installed and active to use the WP ULike integration.
* A [Cloudways account](https://unified.cloudways.com/) with API access for the Cloudways integration.
* A [Kinsta account](https://kinsta.com/) with a Kinsta API key for the Kinsta integration.

## Installation

1. Upload the `automator-connect` folder to the `/wp-content/plugins/` directory, or install the plugin zip through the WordPress admin (Plugins > Add New > Upload Plugin).
2. Activate Automator Connect through the 'Plugins' menu in WordPress.
3. Go to Automator > App Integrations, find the app you want to connect (for example, Cloudways or Kinsta), and connect your account with the required credentials.
4. Make sure WP ULike is installed and active if you want to use its integration.
5. Build a recipe in Uncanny Automator and add any of the available triggers or actions.

## Support

This is an independently maintained plugin, not officially affiliated with WordPress, Uncanny Automator, Cloudways, WP ULike, or Kinsta. Please open an issue on [GitHub](https://github.com/MinaEmadWP/automator-connect) for bugs or feature requests.

## Frequently Asked Questions

### Do I need an Uncanny Automator Pro license?

No. Automator Connect works with the free version of Uncanny Automator. That said, if you're building out advanced recipes and want to take automation to the next level, [Uncanny Automator's paid plans](https://automatorplugin.com/pricing/) unlock more advanced features and additional integrations with hundreds of triggers and actions.

### How can I create my Cloudways API access token?

Log in to your [Cloudways account](https://unified.cloudways.com/), open **API Integration**, and click **Create Access Token**. Give the token a name, choose its expiration period and required access scope, then create and copy the token.

See the [Cloudways documentation on creating and managing API access tokens](https://support.cloudways.com/en/articles/5136065-how-to-create-and-manage-cloudways-api-access-tokens).

### Where do I get my Kinsta API key?

In [MyKinsta](https://my.kinsta.com/), go to **your username > Company settings > API Keys** and click **Create API Key**. Choose an expiration period, give the key a name, and click **Generate**. Copy and securely store the key when it is generated.

See the [Kinsta API documentation](https://kinsta.com/docs/kinsta-api/).

### Do I have to install WP ULike?

No. You only need to install and activate the WP ULike plugin if you are planning to use its triggers in recipes.

### What happens if I disconnect an app integration?

Disconnecting clears the saved credentials for that integration. Its actions will be skipped in any recipes using them until the integration is connected again.

### Are more integrations planned?

Yes. Automator Connect is under active development, and more third-party plugin and app integrations are planned.

## Changelog

### 1.1.0

Added:

* WP ULike integration with four triggers for post and comment likes and unlikes.
* Kinsta integration: connect an account, create/delete a site, and check an operation status.

Updated:

* Cloudways authentication to support the new access-token-based API flow.
* Cloudways credential handling and app settings page.

### 1.0.0

* Initial release.

Added:

* Cloudways integration: connect an account, add/remove an application, start an application backup, and check an operation status.
