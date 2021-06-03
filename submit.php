<?php
error_reporting(E_ERROR | E_PARSE);
require __DIR__ . '/google-drive.php';

require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

$service = new Google_Service_Drive($GLOBALS['client']);


if(!isset($_POST['folder']) || empty($_POST['folder'])){
    echo "Please enter the folder name";
    exit;
}


if(!isset($_POST['filename']) || empty($_POST['filename'])){
    echo "Please enter the filename";
    exit;
}


if(!isset($_POST['s3bucket']) || empty($_POST['s3bucket'])){
    echo "Please enter the s3bucket";
    exit;
}

$folderName = $_POST['folder'];
$parameters['q'] = "name = '" . $folderName . "' and  trashed=false";
$files = $service->files->listFiles($parameters);


$url = getcwd() . '/images/' . $_POST['filename'];

try {

    $bucketName = $_POST['s3bucket'];
    $IAM_KEY = '';
    $IAM_SECRET = '';


    $s3 = S3Client::factory(
        array(
            'credentials' => array(
                'key' => $IAM_KEY,
                'secret' => $IAM_SECRET
            ),
            'version' => 'latest',
            'region'  => 'ap-south-1'
        )
    );
} catch (Exception $e) {

    die("Error: " . $e->getMessage());
}

$keyName = 'test_example/' . $_POST['filename'];
$pathInS3 = 'https://s3.ap-south-1.amazonaws.com/' . $bucketName . '/' . $keyName;
$result = $s3->putObject(
    array(
        'Bucket' => $bucketName,
        'Key' =>  $keyName,
        'SourceFile' => $url,
        'ACL'        => 'public-read', //for making the public url
        'StorageClass' => 'REDUCED_REDUNDANCY'
    )
);

$url = $result->get('ObjectURL');
echo "<br>Image uploaded successfully. Image path is: " . $result->get('ObjectURL');
$response = ['success'=>$_POST['filename']];
echo json_encode($response);

