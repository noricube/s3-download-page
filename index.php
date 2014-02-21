<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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

$prefix = '';
if ( isset($_GET['prefix']))
{
	$prefix = base64_decode($_GET['prefix']);
}
$it = $s3->getIterator('ListObjects',array('Bucket'=>$bucket, 'Prefix' => $prefix));

$folders = array();
$files = array();
foreach ($it as $object) {

	if (substr($object['Key'],-1) === "/") // 폴더 자체 객체는 그냥 무시한다.
	{
		continue;
	}

	$real_filename = substr($object['Key'], strlen($prefix));
	
	$split = explode("/", $real_filename);
	if ( count($split) > 1 )
	{
		$folders[$split[0]] = TRUE;
	}
	else
	{
		$object['RealFileName'] = $real_filename;
		$files[] = $object;
	}
}

// 뒤로가기 링크 만들기
if ( strlen($prefix) > 0 )
{
	$split = explode("/", $prefix);
	array_pop($split);
	array_pop($split);

	if (count($split) > 0 )
	{
		$prev_prefix = implode("/", $split) . "/";
		$back_link = $_SERVER['PHP_SELF'] . "?prefix=" . urlencode(base64_encode($prev_prefix));
	}
	else
	{
		$back_link = $_SERVER['PHP_SELF'];
	}
}
?><!doctype html>
<html>
<head>
	<meta charset="utf-8"/>
	<title>도서관</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

	<!-- Latest compiled and minified JavaScript -->
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
	
</head>
<body>
<div class="container">
	<h2>도서관</h2>
	<blockquote class="bg-danger">PC에서는 다운로드 버튼 우클릭 후 <strong>다른 이름으로 저장</strong> 으로 받으세요</blockquote>
	<div class="table-responsive">
		<table class="table table-striped">
			<thead>
				<tr>
					<th>
						파일명
					</th>
					<th>
						크기
					</th>
					<th>
						마지막 수정
					</th>
					<th>
						-
					</th>
				</tr>
			</thead>
			<tbody>
<?php if ( $prefix !== "" ) { // 폴더 안인경우 상위폴더 출력?>
	<tr class="warning">
		<td colspan="4"><a href="<?=$back_link?>"><span class="glyphicon glyphicon-circle-arrow-left"></span> 상위 폴더로 이동</a></td>
	</tr>
<?php } ?>
<?php foreach ($folders as $key=>$value) { 
			// 폴더명에는 마지막에 /가 빠져있어서 추가해줌
			$link = $_SERVER['PHP_SELF'] . '?prefix='. urlencode(base64_encode($prefix . $key . "/"));

?>
				<tr class="warning">
					<td colspan="4"><a href="<?=$link?>"><span class="glyphicon glyphicon-folder-open"></span> <?=$key?></a></td>
				</tr>
<?php } ?>
<?php foreach ($files as $object) { ?>		
				<tr>
					<td><?=$object['RealFileName']?></td>
					<td><?=round($object['Size']/1024/1024,2)?>MB</td>
					<td><?=$object['LastModified']?></td>
					<td><a class="btn btn-success btn-xs" href="download.php?Key=<?=urlencode(base64_encode($object['Key']))?>" target="_blank">다운로드</a></td>
				</tr>
<?php } ?>
			</tbody>
		</table>
	</div>
</div>
</body>
</html>
