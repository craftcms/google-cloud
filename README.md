# Google Cloud Storage for Craft CMS

This plugin provides a [Google Cloud Storage](https://cloud.google.com/storage/) integration for [Craft CMS](https://craftcms.com/).

## Requirements

This plugin requires Craft CMS 4.0.0+ or 5.0.0+.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Google Cloud Storage”. Then click on the “Install” button in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require craftcms/google-cloud

# tell Craft to install the plugin
./craft plugin/install google-cloud
```

## Setup

To create a new asset volume for your Google Cloud Storage bucket, go to Settings → Assets, create a new volume, and set the Volume Type setting to “Google Cloud Storage”.

> **Tip:** The Project ID, Bucket, and Subfolder settings can be set to environment variables. See [Environmental Configuration](https://docs.craftcms.com/v3/config/environments.html) in the Craft docs to learn more about that.
