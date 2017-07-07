Google Cloud Storage for Craft CMS
=======================

This plugin provides a [Google Cloud Storage](https://cloud.google.com/storage/) integration for [Craft CMS](https://craftcms.com/).

:warning: If you are updating from Craft 2, you will need to provide the contents of your Service Account Credentials JSON file for each Volume in their settings.

## Requirements

This plugin requires Craft CMS 3.0.0-beta.20 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require craftcms/google-cloud

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Google Cloud Storage.

## Setup

To create a new asset volume for your Google Cloud Storage bucket, go to Settings → Assets, create a new volume, and set the Volume Type setting to “Google Cloud Storage”.
