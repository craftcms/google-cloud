# Google Cloud Storage for Craft CMS

This plugin provides a [Google Cloud Storage](https://cloud.google.com/storage/) integration for [Craft CMS](https://craftcms.com/).

## Requirements

This plugin requires Craft CMS 4.0.0+ or 5.0.0+ or 6.0.0-alpha.1+ with the `craftcms/yii2-adapter` package.

### Craft 6

Craft 6 replaces filesystems with [Laravel disks](https://laravel.com/docs/filesystem), and has no settings screens for filesystems. With the `craftcms/yii2-adapter` package installed, Google Cloud Storage filesystems defined in your project config keep working.

When you upgrade, Craft asks you to define a disk in `config/filesystems.php` for each filesystem your volumes use, and then removes all filesystem definitions from your project config, including Google Cloud Storage ones. Laravel has no built-in Google Cloud Storage driver. To keep using this plugin:

1. Before upgrading, add a disk that points to each Google Cloud Storage filesystem, using the filesystem’s handle for both the disk name and `fsHandle`:

   ```php
   'disks' => [
       'myGoogleFs' => [
           'driver' => 'craft-fs-bridge',
           'fsHandle' => 'myGoogleFs',
       ],
   ],
   ```

2. Upgrade Craft.
3. Restore the filesystems’ `fs` entries in your project config from version control, and run `php artisan craft:project-config:apply`.

To move to native Laravel disks instead, install [spatie/laravel-google-cloud-storage](https://github.com/spatie/laravel-google-cloud-storage) 2.4.2 or later (earlier versions ignore volume subpaths) and define a `gcs` disk for each filesystem:

```php
'disks' => [
    'myGoogleFs' => [
        'driver' => 'gcs',
        'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
        'key_file' => json_decode((string) env('GOOGLE_CLOUD_KEY_FILE'), true),
        'bucket' => env('GOOGLE_CLOUD_BUCKET'),
        'path_prefix' => 'path/to/subfolder',
        'storage_api_uri' => 'https://my-bucket.example.com',
        'visibility' => 'public',
    ],
],
```

Use `path_prefix` for the filesystem’s Subfolder setting, and `storage_api_uri` for its base URL. Native disks don’t apply the Cache Duration setting.

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

Your Google Cloud bucket must use Google's `Fine grained` (per object) permissions, not `Uniform` (per bucket).

On the permissions table for the new bucket, grant `Storage Object Viewer` to `allUsers`. The `Storage Admin` role should be granted to the service account the credentials are tied to.

To create a new asset volume for your Google Cloud Storage bucket, go to Settings → Assets, create a new volume, and set the Volume Type setting to “Google Cloud Storage”.

> **Tip:** The Project ID, Bucket, and Subfolder settings can be set to environment variables. See [Environmental Configuration](https://docs.craftcms.com/v3/config/environments.html) in the Craft docs to learn more about that.
