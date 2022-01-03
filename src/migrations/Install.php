<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\googlecloud\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use craft\googlecloud\Fs;
use craft\googlecloud\Volume;
use craft\helpers\Json;
use craft\services\ProjectConfig;
use craft\services\Volumes;

/**
 * Installation Migration
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 */
class Install extends Migration
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Convert any built-in Google Cloud volumes to ours
        $this->_convertVolumes();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }

    // Private Methods
    // =========================================================================

    /**
     * Converts any old school Google Cloud volumes to this one
     *
     * @return void
     */
    private function _convertVolumes()
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $projectConfig->muteEvents = true;

        $volumes = $projectConfig->get(ProjectConfig::PATH_FILESYSTEMS) ?? [];

        foreach ($volumes as $uid => &$volume) {
            if ($volume['type'] === Fs::class && isset($volume['settings']) && is_array($volume['settings'])) {
                $settings = $volume['settings'];

                $hasUrls = !empty($volume['hasUrls']);
                $url = ($hasUrls && !empty($settings['urlPrefix'])) ? $settings['urlPrefix'] : null;
                unset($settings['urlPrefix'], $settings['keyId'], $settings['secret']);


                $volume['url'] = $url;
                $volume['settings'] = $settings;

                $this->update(Table::FILESYSTEMS, [
                    'settings' => Json::encode($settings),
                    'url' => $url,
                ], ['uid' => $uid]);

                $projectConfig->set(ProjectConfig::PATH_FILESYSTEMS . '.' . $uid, $volume);
            }
        }

        $projectConfig->muteEvents = false;
    }
}
