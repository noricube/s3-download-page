<?php
require 'config.php';
require 'auth.php';
require 'aws.phar';

use Aws\Common\Aws;
use Aws\S3\Exception\S3Exception;

// Instantiate an S3 client
$aws = Aws::factory(array(
     'key'    => $s3key,
    'secret' => $s3secret,
    'region' => $s3region,
));
$s3 = $aws->get('s3');

$it = $s3->getIterator('ListObjects',array('Bucket'=>$bucket));

header("Location: " . $s3->getObjectUrl($bucket, base64_decode($_GET['Key']), '+1 days'));
