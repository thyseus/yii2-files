<?php

namespace thyseus\files\events;

use app\models\User;
use yii\base\Event;

/** When an file has been shared with another user, we can deliver the author and the recipient */
class ShareWithUserEvent extends Event {
    /**
     * @var User who shared the file ?
     */
    public $sharedFrom;

    /**
     * @var User who received the file share ?
     */
    public $sharedWith;

    /**
     * @var File which file has been shared ?
     */
    public $sharedFile;

    /**
     * @var integer has the share been given (1) or revoked (0)
     */
    public $add;
}
