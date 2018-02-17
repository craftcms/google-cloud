<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\googlecloud;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Asset bundle for the Dashboard
 */
class GoogleCloudBundle extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@craft/googlecloud/resources';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/editVolume.js',
        ];

        parent::init();
    }
}
