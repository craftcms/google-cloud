<?php

namespace craft\googlecloud\controllers;

use Craft;
use craft\googlecloud\Volume;
use craft\web\Controller as BaseController;
use yii\web\Response;

/**
 * This controller provides functionality to load data from AWS.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0
 */
class DefaultController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->defaultAction = 'load-bucket-data';
    }

    /**
     * Load bucket data for specified credentials.
     *
     * @return Response
     */
    public function actionLoadBucketData()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $projectId = Craft::parseEnv($request->getRequiredBodyParam('projectId'));
        $keyFileContents = $request->getRequiredBodyParam('keyFileContents');

        try {
            return $this->asJson(Volume::loadBucketList($projectId, $keyFileContents));
        } catch (\Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }
    }
}
