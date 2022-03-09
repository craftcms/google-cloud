<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\googlecloud\migrations;

use Craft;
use craft\db\Migration;
use craft\googlecloud\Volume;
use craft\helpers\Json;
use craft\services\Volumes;

/**
 * Installation Migration
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.4
 */
class m200529_110000_cleanup_expires_config extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Cleanup failed conversions
        $projectConfig = Craft::$app->getProjectConfig();

        $schemaVersion = $projectConfig->get('plugins.google-cloud.schemaVersion', true);
        $projectConfig->muteEvents = true;

        $volumes = $projectConfig->get(Volumes::CONFIG_VOLUME_KEY, true) ?? [];

        foreach ($volumes as $uid => &$volume) {
            if ($volume['type'] === Volume::class && !empty($volume['settings']) && is_array($volume['settings']) && array_key_exists('expires', $volume['settings'])) {
                if (preg_match('/^([\d]+)([a-z]+)$/', $volume['settings']['expires'], $matches)) {
                    $volume['settings']['expires'] = $matches[1] . ' ' . $matches[2];

                    $this->update('{{%volumes}}', [
                        'settings' => Json::encode($volume['settings']),
                    ], ['uid' => $uid]);

                    // If project config schema up to date, don't update project config
                    if (!version_compare($schemaVersion, '1.1', '>=')) {
                        $projectConfig->set(Volumes::CONFIG_VOLUME_KEY . '.' . $uid, $volume);
                    }
                }
            }
        }

        $projectConfig->muteEvents = false;

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}
