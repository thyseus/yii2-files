# Yii2-files

A General File Upload Manager for the Yii 2 framework.
Files can be uploaded and linked to an ActiveRecord Model.
Contains only one table 'files' where everything is stored.
The files are uploaded into an protected folder by default.

It uses the kartik-v/yii2-widget-fileinput widget (https://github.com/kartik-v/yii2-widget-fileinput).

The upload endpoint has been designed with this document in mind: http://webtips.krajee.com/ajax-based-file-uploads-using-fileinput-plugin/

## Installation

```bash
$ composer require thyseus/yii2-files
$ php yii migrate/up --migrationPath=@vendor/thyseus/yii2-files/migrations
$ mkdir uploads/
$ echo '.uploads/' >> .gitignore
```

## Security

Note that by default all users that apply to Yii::$app->user->can('admin') are able to see, download 
and remove all files that are available in the database. Every other user can only access his own 
uploaded files. Guests can do nothing.

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
                HasFilesBehavior::className(),
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
]);
```

Use
```php
use thyseus\files\models\File;

$model = File::findOne(57);
echo File::downloadLink();
```

to display a "Download file" button in the view file.

## Routes

You can use the following routes to access the files module:

* list all files of the current logged in user: https://your-domain/files/files/index
* view: https://your-domain/files/files/view?id=<id>
* update: 'files/update/<id>' => 'files/files/update',
* delete: 'files/delete/<id>' => 'files/files/delete',
* view: 'files/<id>' => 'files/files/view',

## License

Yii2-files is released under the GPLv3 License.
