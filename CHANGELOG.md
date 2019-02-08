# Release Notes for Google Cloud Storage for Craft CMS

## 1.3.0 - 2019-02-08

### Added
- The Project ID and Subfolder settings can now be set to environment variables. ([#7](https://github.com/craftcms/aws-s3/issues/7))

## 1.2.1 - 2019-02-06

### Fixed
- Fixed a bug where installing this plugin via `project.yaml` would result in other changes being applied partially.

## 1.2.0 - 2019-02-01

### Fixed
- Fixed a bug where error message would not be displayed for the bucket dropdown when configuring a volume. ([#4](https://github.com/craftcms/google-cloud/issues/4))
- Fixed an error that occurred when updating from Craft 2 to Craft 3.1 when using this plugin.

## 1.1 - 2018-07-27

### Changed
- Bumped `superbalist/flysystem-google-storage` version requirement to `^7.0.0`

### Fixed
- Fixed a bug where folders moved or deleted in Assets would remain in Google Cloud. ([#2](https://github.com/craftcms/google-cloud/issues/2))

## 1.0.5 - 2018-01-15

### Fixed
- Fixed a bug where the Field Layout tab wasn’t showing up on volume settings when Google Cloud Storage was installed. ([#1](https://github.com/craftcms/google-cloud/issues/1))

## 1.0.4 - 2018-01-02

### Added
- Google Cloud volumes’ Base URL settings are now parsed for [aliases](http://www.yiiframework.com/doc-2.0/guide-concept-aliases.html) (e.g. `@web`).

## 1.0.3 - 2017-12-04

### Changed
- Loosened the Craft CMS version requirement to allow any 3.x version.

## 1.0.2 - 2017-08-15

### Changed
- Craft 3 Beta 24 compatibility.

### Fixed
- Fixed a bug where cache duration information was not being saved for Volumes.
- Fixed a bug where cache duration information was not being set when uploading a file.

## 1.0.1 - 2017-07-07

### Changed
- Craft 3 Beta 20 compatibility.

## 1.0.0 - 2017-05-02

Initial release.
