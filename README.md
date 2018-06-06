# ATTENTION:

github has been bought by Microsoft. This repository is orphaned and has been moved to:

https://gitlab.com/thyseus/yii2-files

Thanks a lot for your understanding and blame Microsoft.

# Yii2-files

A General File Upload Manager for the Yii 2 framework.

Files can be uploaded and linked to any ActiveRecord Model. Since version 0.3.0 the user
can upload files without the need to link to an Model.
 
It uses the kartik-v/yii2-widget-fileinput widget (https://github.com/kartik-v/yii2-widget-fileinput).

It contains only one database table 'files' where everything is stored.

A simple Access Control is provided. All Files are uploaded into an protected folder by default and can
be set to public for everyone or for specific users by the uploader.

The upload endpoint has been designed with this document in mind:
http://webtips.krajee.com/ajax-based-file-uploads-using-fileinput-plugin/

In case images are uploaded, a simple image cropper is provided.

Translations are available to german and english currently.

## Installation

```bash
$ cd /your/project/root
$ composer require thyseus/yii2-files
$ php yii migrate/up --migrationPath=@vendor/thyseus/yii2-files/migrations
$ mkdir uploads/
$ echo '.uploads/' >> .gitignore
```

## Security

Note that by default all users that apply to Yii::$app->user->can('admin') are able to see, download 
and remove all files that are available in the database. Every other user can only access his own 
uploaded files and files that he has shared with other users. Guests can only see public files.

Since 0.3.0 users can share his files with users he chooses to. In order to make these feature to
work, you need to provide an app\models\User Active Record model with at least the attributes 
'id' and 'username'.

## Important Update Notice:

Since 0.4.0 (2017-12-18) yii2-files does automatically save a md5 checksum of each file that is being uploaded.
This checksum is being checked when downloading the file.
Ensure that your migrations have been executed so that the column 'checksum' exists.
In order to update all already uploaded files with the correct checksum, please run the following command in your
yii2-shell:

```php
foreach(thyseus\files\models\File::find()->all() as $file) {
    if (!$file->checksum) {
        $file->updateAttributes(['checksum' => md5(@file_get_contents($file->filename_path))]);
    }
}
```

## Configuration

Add following lines to your main configuration file:

```php
'modules' => [
    'files' => [
        'class' => 'thyseus\files\FileWebModule',
    ],
],
```

## Integration in your application:

Attach the hasFilesBehavior onto every ActiveRecord model that you want to attach files to:

```php
use \thyseus\files\behaviors\HasFilesBehavior;

    public function behaviors()
    {
        return [
                HasFilesBehavior::class,
                ];
    }
```

You can access every uploaded file that is attached to the Active Record model depending on the current
logged in user via:

```php
$model->files;
```

You can render an upload widget that attaches every uploaded file automatically to the model by using:

```php
use yii\helpers\Url;

echo $this->render('@vendor/thyseus/yii2-files/views/file/_upload', [
    'model' => $model,
    'options' => ['multiple' => true], // optional
    'target_url' => Url::to(['agency/view', 'id' => $model->slug]), // optional
    'uploadExtraData' => ['public' => true] // uploaded files are automatically public (default is: protected). optional.
]);
```

Use
```php
use thyseus\files\models\File;

$model = File::findOne(57);
echo File::downloadLink();
```

to display a "Download file" button in the view file.

## Events

You can attach events to most of the actions, for example to send a notification once a user gets
access to a shared file, like this:

```php
use yii\base\Event;
use thyseus\files\controllers\FileController;

Event::on(FileController::class, FileController::EVENT_AFTER_SHARE_WITH_USER, function ($event) {
    $username = $event->sender->actionParams['username'];
    
    Message::sendNotification($username); // <-- you need to implement this
});
```

## Routes

You can use the following routes to access the files module:

* list all files of the current logged in user: https://your-domain/files/files/index
* view: https://your-domain/files/files/view?id=<id>
* update: 'files/update/<id>' => 'files/files/update',
* delete: 'files/delete/<id>' => 'files/files/delete',
* view: 'files/<id>' => 'files/files/view',

## License

Yii2-files is released under the GPLv3 License.
