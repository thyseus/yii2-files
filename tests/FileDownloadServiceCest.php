<?php

use Codeception\Util\Stub;
use \thyseus\files\services\FileDownloadService;
use yii\db\ActiveRecord;
use yii\httpclient\Client;
use yii\httpclient\Response;

class FileDownloadServiceCest
{
    public function _before(UnitTester $I)
    {
        $this->service = Stub::make(FileDownloadService::class, [
            'client' => Stub::make(Client::class, [
                'send' => Stub::make(Response::class, [
                    'getIsOK' => true,
                    'data' => 'Demonstration File Content',
                ])
            ]),
        ]);
    }

    public function _after(UnitTester $I)
    {
    }

    public function downloadAFile(UnitTester $I)
    {
        $this->service->tags = 'example-tag';
        $this->service->target_class = 'app\models\Example';
        $this->service->target_id = 666;

        $file = $this->service->download('https://stubbed-url-example.gov/example_file.txt');

        $I->assertEquals([], $file->getErrors());
        $I->assertFalse($file->hasErrors());

        $I->assertFalse($file->isNewRecord);

        $I->assertContains('example_file.txt', $file->filename_user);

        $I->assertArraySubset([
            'target_id' => '666',
            'target_url' => null,
            'model' => 'app\models\Example',
            'status' => 0,
            'mimetype' => 'text/plain',
            'public' => true,
            'position' => 1000,
            'download_count' => 0,
            'tags' => 'example-tag',
            'shared_with' => null,
            'slug' => 'examplefiletxt',
            'checksum' => 'd41d8cd98f00b204e9800998ecf8427e',
        ], $file->attributes);

        $I->assertFileExists($file->filename_path);

        $I->openFile($file->filename_path);
        $I->seeInThisFile('Demonstration File Content');

    }
}
