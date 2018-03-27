<?php

namespace thyseus\files\services;

use thyseus\files\models\File;
use Yii;
use yii\httpclient\Client;
use yii\httpclient\FormatterInterface;
use yii\httpclient\ParserInterface;
use yii\httpclient\Response;


/**
 * Class FileDownloadService
 *
 * Functionality to download a file from a given url and save it into your files.
 * @package thyseus\files\services
 */
class FileDownloadService
{
    /**
     * @var null target class to link the downloaded file to.
     */
    public $target_class = null;

    /**
     * @var null target id to link the downloaded file to.
     */
    public $target_id = null;

    /**
     * @var null target url to link the downloaded file to.
     */
    public $target_url = null;

    /**
     * @var null tags to save the downloaded files into. Can be an array or a comma separated list.
     */
    public $tags = null;

    /**
     * @var string http method to use, defaults to GET
     */
    public $http_method = 'GET';

    /**
     * @var bool Whether the file should be public or not. Defaults to false (0) of course.
     */
    public $public = 0;

    /**
     * @var string filename to use. Leave to null to let it be determined automatically.
     */
    public $filename = null;

    /**
     * @var Client HTTP Client to use, defaults to yii\httpclient\Client.
     */
    public $client;

    /**
     * @var user id to download, leave empty to use Yii::$app->user->id
     */
    public $user_id = null;

    public function __construct()
    {
        $this->client = new Client();
    }


    public function download($url)
    {
        if (is_array($this->tags)) {
            $this->tags = explode(', ', $this->tags);
        }

        $response = $this->request($url);

        if (!$this->filename) {
            $this->filename = basename($url);
        }

        $filename_parts = explode('.', $this->filename);

        $target = Yii::$app->getModule('files')->uploadPath . '/' . md5(uniqid()) . "." . array_pop($filename_parts);

        file_put_contents($target, $response->content);

        $file = Yii::createObject([
            'class' => File::class,
            'attributes' => [
                'model' => $this->target_class,
                'target_id' => (string) $this->target_id,
                'target_url' => $this->target_url,
                'content' => $response->content,
                'filename_path' => $target,
                'filename_user' => $this->filename,
                'created_by' => $this->user_id ? $this->user_id : Yii::$app->user->id,
                'updated_by' => $this->user_id ? $this->user_id : Yii::$app->user->id,
                'mimetype' => mime_content_type($target),
                'public' => $this->public,
                'tags' => $this->tags,
            ],
        ]);

        // We detach the behavior because this is done manually.
        // This also avoids problems with console app configurations.
        $file->detachBehavior('blameable');

        $file->save();

        return $file;
    }

    protected function request($url)
    {
        try {
            return $this->client->createRequest()
                ->setMethod($this->http_method)
                ->setUrl($url)
                ->send();
        } catch (\Exception $e) {

        }
    }
}
