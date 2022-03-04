<?php
declare(strict_types=1);
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\googlecloud;

use Craft;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\errors\FsException;
use craft\flysystem\base\FlysystemFs;
use craft\helpers\ArrayHelper;
use craft\helpers\Assets;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use DateTime;
use Google\Auth\HttpHandler\Guzzle6HttpHandler;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use League\Flysystem\GoogleCloudStorage\PortableVisibilityHandler;

/**
 * Class Fs
 *
 * @property mixed $settingsHtml
 * @property string $rootUrl
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 */
class Fs extends FlysystemFs
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Google Cloud Storage';
    }

    /**
     * @var string Subfolder to use
     */
    public string $subfolder = '';

    /**
     * @var string Google Cloud project id.
     */
    public string $projectId = '';

    /**
     * @var string Contents of the connection key file
     */
    public string $keyFileContents = '';

    /**
     * @var string Bucket to use
     */
    public string $bucket = '';

    /**
     * @var string Cache expiration period.
     */
    public string $expires = '';

    /**
     * @var string Bucket selection mode ('choose' or 'manual')
     */
    public string $bucketSelectionMode = 'choose';

    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
        if (isset($config['manualBucket'])) {
            if (isset($config['bucketSelectionMode']) && $config['bucketSelectionMode'] === 'manual') {
                $config['bucket'] = ArrayHelper::remove($config, 'manualBucket');
            } else {
                unset($config['manualBucket']);
            }
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['parser'] = [
            'class' => EnvAttributeParserBehavior::class,
            'attributes' => [
                'subfolder',
                'projectId',
                'bucket',
            ],
        ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['bucket', 'projectId'], 'required'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('google-cloud/fsSettings', [
            'fs' => $this,
            'periods' => array_merge(['' => ''], Assets::periodList()),
        ]);
    }

    /**
     * Get the bucket list using the specified key file contents and project id.
     *
     * @param string $projectId
     * @param string $keyFileContents
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function loadBucketList(string $projectId, string $keyFileContents): array
    {
        // Any region will do.
        $config = self::_buildConfigArray($projectId, $keyFileContents);

        $client = static::client($config);

        /** @var Bucket[] $buckets */
        $buckets = $client->buckets(['projection' => 'noAcl']);

        $bucketList = [];

        foreach ($buckets as $bucket) {
            $bucketList[] = [
                'bucket' => $bucket->name(),
                'urlPrefix' => 'http://storage.googleapis.com/' . $bucket->name() . '/',
            ];
        }

        return $bucketList;
    }

    /**
     * @inheritdoc
     */
    public function getRootUrl(): ?string
    {
        $rootUrl = parent::getRootUrl();

        if ($rootUrl) {
            $rootUrl .= $this->_subfolder();
        }

        return $rootUrl;
    }

    /**
     * @inheritdoc
     */
    public function deleteDirectory(string $path): void
    {
        $fileList = $this->getFileList($path, true);

        foreach ($fileList as $object) {
            try {
                if ($object['type'] === 'dir') {
                    $this->filesystem()->deleteDirectory($object['path']);
                } else {
                    $this->filesystem()->delete($object['path']);
                }
            } catch (\Throwable $exception) {
                // Even though we just listed this, the folders may or may not exist
                // Depending on whether the folder was created or a file like "folder/file.ext" was uploaded
                continue;
            }
        }

        try {
            $this->filesystem()->deleteDirectory($path);
        } catch (\Throwable $exception) {
            //Ignore if this was a phantom folder, too.
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteFile(string $path): void
    {
        try {
            parent::deleteFile($path);
        } catch (\Throwable $exception) {
            /** @phpstan-ignore-next-line */
            Craft::$app->getErrorHandler()->logException($exception);
            throw new FsException(Craft::t('google-cloud', 'Could not delete file due to bucket’s retention policy'), 0, $exception);
        }
    }

    /**
     * @inheritdoc
     * @return GoogleCloudStorageAdapter
     */
    protected function createAdapter(): FilesystemAdapter
    {
        $config = $this->_getConfigArray();

        $client = static::client($config);
        $bucket = $client->bucket(Craft::parseEnv($this->bucket));

        return new GoogleCloudStorageAdapter($bucket, $this->_subfolder(), new PortableVisibilityHandler('allUsers'));
    }

    /**
     * Get the Google Cloud Storage client.
     *
     * @param array $config
     * @return StorageClient
     */
    protected static function client(array $config = []): StorageClient
    {
        return new StorageClient($config);
    }

    /**
     * @inheritdoc
     */
    protected function addFileMetadataToConfig(array $config): array
    {
        if (!empty($this->expires) && DateTimeHelper::isValidIntervalString($this->expires)) {
            $expires = new DateTime();
            $now = new DateTime();
            $expires->modify('+' . $this->expires);
            $diff = (int)$expires->format('U') - (int)$now->format('U');

            if (!isset($config['metadata'])) {
                $config['metadata'] = [];
            }
            $config['metadata']['cacheControl'] = 'public,max-age=' . $diff . ', must-revalidate';
        }

        return parent::addFileMetadataToConfig($config);
    }

    /**
     * Returns the parsed subfolder path
     *
     * @return string
     */
    private function _subfolder(): string
    {
        if ($this->subfolder && ($subfolder = rtrim(Craft::parseEnv($this->subfolder), '/')) !== '') {
            return $subfolder . '/';
        }
        return '';
    }

    /**
     * Get the config array for Google Cloud Storage clients.
     *
     * @return array
     */
    private function _getConfigArray(): array
    {
        $projectId = Craft::parseEnv($this->projectId);
        $keyFileContents = Craft::parseEnv($this->keyFileContents);

        return self::_buildConfigArray($projectId, $keyFileContents);
    }

    /**
     * Build the config array based on a project id and key file contents.
     *
     * @param string $projectId
     * @param string $keyFileContents
     * @return array
     */
    private static function _buildConfigArray(string $projectId, string $keyFileContents): array
    {
        $config = [
            'projectId' => $projectId,
        ];

        if (!empty($keyFileContents)) {
            $config['keyFile'] = Json::decode($keyFileContents);
        }

        $client = Craft::createGuzzleClient();
        $handler = new Guzzle6HttpHandler($client);

        $config['httpHandler'] = $handler;
        $config['authHttpHandler'] = $handler;

        return $config;
    }
}
