<?php

namespace craft\googlecloud;

use craft\events\RegisterComponentTypesEvent;
use craft\services\Filesystems;
use craft\services\Volumes;
use yii\base\Event;


/**
 * Plugin represents the Amazon S3 volume plugin.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0
 */
class Plugin extends \craft\base\Plugin
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public string $schemaVersion = '2.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Event::on(Filesystems::class, Filesystems::EVENT_REGISTER_FILESYSTEM_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Fs::class;
        });
    }
}
