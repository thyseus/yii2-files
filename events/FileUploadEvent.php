<?php

namespace thyseus\files\events;

use thyseus\files\models\File;
use yii\base\Event;

class FileUploadEvent extends Event {
    /**
     * @var array File/files that has/have been uploaded. Contains the $_FILES array as it is.
     */
    public $filesData;

    /**
     * @var array Extra post data that has been given with the request. Contains the Yii::$app->request->post() array as
     * it is.
     * @see http://demos.krajee.com/widget-details/fileinput#advanced-usage
     */
    public $postData;

    /**
     * @var boolean has the file upload been successfully?
     */
    public $success;

    /**
     * @var array when successful, the ActiveRecord Models of the files that have been uploaded.
     */
    public $files;
}
