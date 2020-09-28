<?php
set_time_limit(0);
mb_internal_encoding('UTF-8');

$key='GOOGLE_CLOUD_VISION_KEY';

$folder='unsplash';
$error_img='error.png';
$divide=';';

$mysqli = new mysqli("localhost", "DATABASE_USER", "DATABASE_USER_PASSWORD", "DATABASE");

$mxob=0;
$mxlb=0;
$goq=0;
$args=array(0,0,0,0,0,0,0,0);
$path='';

function rsearch($dir) {
	// https://stackoverflow.com/questions/2524151/php-get-all-subdirectories-of-a-given-directory
	return array_filter(glob($dir.'/*'), 'is_dir');
}

function getcolors($name,$lid) {
global $mysqli;
	// http://www.expertphp.in/article/tips-tricks-for-text-detection-from-images-using-google-vision-api-in-php
	$url = "https://vision.googleapis.com/v1/images:annotate?key=AIzaSyAtcbCIz-yN7Ejc1B4vV9SLAcCgkmZZRoE";
	$detection_type = "IMAGE_PROPERTIES";
	$image_base64 = base64_encode(file_get_contents($name));
	$json_request ='{
			"requests": [
				{
				"image": {
					"content":"' . $image_base64. '"
				  },
				  "features": [
					  {
						"type": "' .$detection_type. '",
						"maxResults": 50
					  }
				  ]
				}
			]
		}';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $json_request);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	$json = json_decode($json_response, true);
	$colors=$json['responses'][0]['imagePropertiesAnnotation']['dominantColors']['colors'];
	for ($f=0;$f<sizeOf($colors);$f++) {
		$sql = "INSERT INTO `cols` (`pid`,`r`,`g`,`b`,`score`) VALUES (?, ?, ?, ?, ?);";
		$stmt = $mysqli->prepare($sql);
		$a=$colors[$f];
		$sc=1-$a['score'];
		$stmt->bind_param('iiiid',$lid,$a['color']['red'],$a['color']['green'],$a['color']['blue'],$sc);
		$stmt->execute();
	}
}

function getobjects($name,$lid) {
global $mysqli;
	// http://www.expertphp.in/article/tips-tricks-for-text-detection-from-images-using-google-vision-api-in-php
	$url = "https://vision.googleapis.com/v1/images:annotate?key=AIzaSyAtcbCIz-yN7Ejc1B4vV9SLAcCgkmZZRoE";
	$detection_type = "OBJECT_LOCALIZATION";
	$image_base64 = base64_encode(file_get_contents($name));
	$json_request ='{
			"requests": [
				{
				"image": {
					"content":"' . $image_base64. '"
				  },
				  "features": [
					  {
						"type": "' .$detection_type. '",
						"maxResults": 50
					  }
				  ]
				}
			]
		}';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $json_request);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	$json = json_decode($json_response, true);
	$objs=array();
	if (isset($json['responses'][0]['localizedObjectAnnotations'])) {
		$colors=$json['responses'][0]['localizedObjectAnnotations'];
		for ($f=0;$f<sizeOf($colors);$f++) {
			$objs[] = $colors[$f]['name'];
		}
		$objs=array_unique($objs, SORT_REGULAR);
		for ($f=0;$f<sizeOf($objs);$f++) {
			if (isset($objs[$f])) {
				$sql = "INSERT INTO `objs` (`pid`,`oid`,`obj`) VALUES (?, ?, ?);";
				$stmt = $mysqli->prepare($sql);
				$oid=getOBID($objs[$f],$lid);
				$ob=mb_strtolower($objs[$f]);
				$stmt->bind_param('iis',$lid,$oid,$ob);
				$stmt->execute();
			}
		}
	}
}

function getlabels($name,$lid) {
global $mysqli;
	// http://www.expertphp.in/article/tips-tricks-for-text-detection-from-images-using-google-vision-api-in-php
	$url = "https://vision.googleapis.com/v1/images:annotate?key=AIzaSyAtcbCIz-yN7Ejc1B4vV9SLAcCgkmZZRoE";
	$detection_type = "LABEL_DETECTION";
	$image_base64 = base64_encode(file_get_contents($name));
	$json_request ='{
			"requests": [
				{
				"image": {
					"content":"' . $image_base64. '"
				  },
				  "features": [
					  {
						"type": "' .$detection_type. '",
						"maxResults": 50
					  }
				  ]
				}
			]
		}';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $json_request);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	$json = json_decode($json_response, true);
	$objs=array();
	if (isset($json['responses'][0]['labelAnnotations'])) {
		$colors=$json['responses'][0]['labelAnnotations'];
		for ($f=0;$f<sizeOf($colors);$f++) {
			$objs[] = $colors[$f]['description'];
		}
		$objs=array_unique($objs, SORT_REGULAR);
		for ($f=0;$f<sizeOf($objs);$f++) {
			if (isset($objs[$f])) {
				$sql = "INSERT INTO `lbls` (`pid`,`lid`,`lbl`) VALUES (?, ?, ?);";
				$stmt = $mysqli->prepare($sql);
				$lbid=getLBID($objs[$f],$lid);
				$ob=mb_strtolower($objs[$f]);
				$stmt->bind_param('iis',$lid,$lbid,$ob);
				$stmt->execute();
			}
		}
	}
}

function getCAT($cat) {
global $folder;
	$dirs=rsearch($folder);
	usort($dirs, function($a, $b) {
	    return filectime($a) < filectime($b);
	});
	for ($f=0;$f<sizeOf($dirs);$f++) {
		if ($cat==basename($dirs[$f])) {
			return $f+1;
		}
	}
	return 0;
}

function getMXOB() {
global $mysqli,$mxob;
	if ($mxob==0) {
		$sql = "select MAX(oid) as max from `objs`;";
		$stmt = $mysqli->prepare($sql);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($row = mysqli_fetch_assoc($result)) {
			$mxob=$row['max'];
		}
		else {
			$mxob = 0;
		}
	}
	return $mxob;
}

function getMXLB() {
global $mysqli,$mxlb;
	if ($mxlb==0) {
		$sql = "select MAX(lid) as max from `lbls`;";
		$stmt = $mysqli->prepare($sql);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($row = mysqli_fetch_assoc($result)) {
			$mxlb=$row['max'];
		}
		else {
			$mxlb=0;
		}
	}
	return $mxlb;
}

function getIMID($name,$wr=0) {
global $mysqli;
	$sql = "SELECT * FROM `imgs` WHERE imgurl LIKE ?";
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('s',$name);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->num_rows<1){
		if ($wr) {
			$sql = "INSERT INTO `imgs` (`imgurl`,`cat`,`cname`) VALUES (?, ?, ?);";
			$stmt = $mysqli->prepare($sql);
			$cn=getCTID($name);
			$ci=getCAT($cn);
			echo ($stmt?1:0).' - '.$name.' - '.$ci.' - '.$cn.'<br>';
			$stmt->bind_param('sis',$name,$ci,$cn);
			$stmt->execute();
			$lid=$mysqli->insert_id;
			getcolors($name,$lid);
			getobjects($name,$lid);
			getlabels($name,$lid);
		}
		else {
			$lid=0;
		}
	}
	else {
		$row = mysqli_fetch_assoc($result);
		$lid=$row['id'];
	}
	return $lid;
}

function getCTID($p) {
    $p=str_replace('\\','/',trim($p));
    if (substr($p,-1)=='/') $p=substr($p,0,-1);
    $a=explode('/', $p);
    array_pop($a);
    return array_pop($a);
}

function getOBID($obj,$lid=false) {
global $mysqli,$mxob;
	getMXOB();
	$sql = "SELECT * FROM `objs` WHERE obj LIKE ?";
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('s',$obj);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->num_rows<1){
		if ($lid) {
			$mxob++;
			$oid=$mxob;
		}
		else {
			$oid=0;
		}
	}
	else {
		$row = mysqli_fetch_assoc($result);
		$oid=$row['oid'];
	}
	return $oid;
}

function getLBID($lbl,$lid=false) {
global $mysqli,$mxlb;
	getMXLB();
	$sql = "SELECT * FROM `lbls` WHERE lbl LIKE ?";
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('s',$lbl);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->num_rows<1){
		if ($lid) {
			$mxlb++;
			$lbid=$mxlb;
		}
		else {
			$lbid=0;
		}
	}
	else {
		$row = mysqli_fetch_assoc($result);
		$lbid=$row['lid'];
	}
	return $lbid;
}

function scan($dir,$limit) {
	$files=rsearch($dir);
	$files[]=$dir;
	foreach($files as $file) {
   		$fls=glob($file."/*.jpg");
		foreach($fls as $fl) {
			getIMID($fl,1);
		}
	}
}

function blow() {
global $mysqli;
	$sql = "DELETE FROM `cache` WHERE 1";
	$stmt = $mysqli->prepare($sql);
	$stmt->execute();
}

function goquery($cat,$obj,$lbl,$rgbHx,$rnk,$rnd,$nocache,$dump,$ln) {
global $mysqli,$error_img,$divide,$path;
	if (strpos($ln, 'nocache') === false) {
		$sql = "SELECT * FROM `cache` WHERE words LIKE ?";
		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('s',$ln);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($row = mysqli_fetch_assoc($result)) {
			return $row['imgurl'];
		}
	}
	$where=false;
	$types='';
	$params=array();
	if ($cat) {
		$cat=getCAT($cat);
	}
	if ($obj) {
		$obj=getOBID($obj);
	}
	if ($lbl) {
		$lbl=getOBID($lbl);
	}
	if ($rgbHx) {
		$rgbHx = array(hexdec(substr($rgbHx,0,2)),hexdec(substr($rgbHx,2,2)),hexdec(substr($rgbHx,4,2)));
	}
	$ex='';
	if ($rgbHx) {
		$ex=',c.r,c.g,c.b,c.score ';
	}
	$sql='SELECT DISTINCT i.id'.$ex.', i.imgurl from imgs i ';
	if ($obj) {
		$sql.='JOIN objs o ON o.pid = i.id ';
	}
	if ($lbl) {
		$sql.='JOIN lbls l ON l.pid = i.id ';
	}
	if ($rgbHx) {
		$sql.='JOIN cols c ON c.pid = i.id ';
	}
	if ($cat||$obj||$lbl) {
		if ($cat) {
			$where='WHERE i.cat=? ';
			$types.='i';
			$params[]=$cat;
		}
		if ($obj&&$where) {
			$where.='AND o.oid=? ';
			$types.='i';
			$params[]=$obj;
		}
		else if ($obj) {
			$where='WHERE o.oid=? ';
			$types.='i';
			$params[]=$obj;
		}
		if ($lbl&&$where) {
			$where.='AND l.lid=? ';
			$types.='i';
			$params[]=$lbl;
		}
		else if ($lbl) {
			$where='WHERE l.lid=? ';
			$types.='i';
			$params[]=$lbl;
		}
		$sql.=$where.' ';
	}
	if ($rgbHx) {
		// https://stackoverflow.com/questions/1847092/given-an-rgb-value-what-would-be-the-best-way-to-find-the-closest-match-in-the-d
		$sql.='ORDER BY (POWER((c.r-?)*0.3,2)+POWER((c.g-?)*0.59,2)+POWER((c.b-?)*0.11,2))*c.score ASC ';
			$types.='iii';
			$params[]=$rgbHx[0];
			$params[]=$rgbHx[1];
			$params[]=$rgbHx[2];
	}
	else if ($rnd) {
		$sql.='ORDER BY RAND() ';
	}
	else {
		$sql.='ORDER BY i.id ASC ';
	}
	if ($rnk) {
		$sql.='LIMIT ?,1';
		$types.='i';
		$params[]=$rnk;
	}
	else if ($dump==0) {
		$sql.='LIMIT 1';
	}
$stmt = false;
if(($stmt = $mysqli->prepare(trim($sql))) === false)
  throw new Exception('Error in statement: ' . $mysqli->error);

	if (strlen($types)>0) {
		$stmt->bind_param($types, ...$params);
	}
	$stmt->execute();
	$result = $stmt->get_result();
	$out='';
	if ($dump==-1) {
		while ($row = mysqli_fetch_assoc($result)) {
			$out.=$path.$row['imgurl'].$divide;
		}
	}
	else if ($dump) {
		$n=$dump;
		$fnd=array();
		while (($row = mysqli_fetch_assoc($result))&&($n>0)) {
			if (!in_array($row['imgurl'],$fnd)) {
				$fnd[]=$row['imgurl'];
				$out.=$path.$row['imgurl'].$divide;
				$n--;				
			}
		}
		while ($n>0) {
			$out.=$error_img.$divide;
			$n--;
		}
	}
	else if ($row = mysqli_fetch_assoc($result)) {
		$out.=$path.$row['imgurl'];
	}
	else {
		$out=$error_img;
	}
	if ($nocache!=1) {
		$sql = "INSERT INTO `cache` (`words`,`imgurl`) VALUES (?, ?);";
		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('ss',$ln,$out);
		$stmt->execute();
	}
	return $out;
}

function query($o) {
global $mysqli,$folder,$divide;
	switch ($o) {
	case 'cat':		$dirs=rsearch($folder);
					$n=sizeOf($dirs);
					echo $n.' Category'.(($n==1)?'':'s').$divide;
					break;
	case 'img':		$sql = "SELECT COUNT(*) AS num FROM `imgs`";
					$stmt = $mysqli->prepare($sql);
					$stmt->execute();
					$result = $stmt->get_result();
					$row = mysqli_fetch_assoc($result);
					$n=$row['num'];
					echo $n.' Image'.(($n==1)?'':'s').$divide;
					break;
	case 'obj':		$sql = "SELECT DISTINCT obj FROM `objs` ORDER BY obj";
					$stmt = $mysqli->prepare($sql);
					$stmt->execute();
					$result = $stmt->get_result();
					$n=$result->num_rows;
					echo $n.' Object'.(($n==1)?'':'s').$divide;
					break;
	case 'lbl':		$sql = "SELECT DISTINCT lbl FROM `lbls` ORDER BY lbl";
					$stmt = $mysqli->prepare($sql);
					$stmt->execute();
					$result = $stmt->get_result();
					$n=$result->num_rows;
					echo $n.' Label'.(($n==1)?'':'s').$divide;
					break;
	}
}

function lister($o) {
global $mysqli,$folder,$divide,$path;
	switch ($o) {
	case 'cat':		$dirs=rsearch($folder);
					usort($dirs, function($a, $b) {
					    return filectime($a) < filectime($b);
					});
					for ($f=0;$f<sizeOf($dirs);$f++) {
						echo basename($dirs[$f]).$divide;
					}
					break;
	case 'img':		$sql = "SELECT imgurl FROM `imgs`";
					$stmt = $mysqli->prepare($sql);
					$stmt->execute();
					$result = $stmt->get_result();
					while ($row = mysqli_fetch_assoc($result)) {
						echo $path.$row['imgurl'].$divide;
					}
					break;
	case 'obj':		$sql = "SELECT DISTINCT obj FROM `objs` ORDER BY obj";
					$stmt = $mysqli->prepare($sql);
					$stmt->execute();
					$result = $stmt->get_result();
					while ($row = mysqli_fetch_assoc($result)) {
						echo $row['obj'].$divide;
					}
					break;
	case 'lbl':		$sql = "SELECT DISTINCT lbl FROM `lbls` ORDER BY lbl";
					$stmt = $mysqli->prepare($sql);
					$stmt->execute();
					$result = $stmt->get_result();
					while ($row = mysqli_fetch_assoc($result)) {
						echo $row['lbl'].$divide;
					}
					break;
	}
}

function spit($v,$o) {
global $goq,$args,$folder;
	switch ($v) {
	case 'query':	query($o);
					break;
	case 'list':	lister($o);
					break;
	case 'scan':	scan($folder,$o);
					break;
	case 'blow':	blow();
					break;
	case 'cat':		$args[0]=$o;
					$goq=1;
					break;
	case 'obj':		$args[1]=$o;
					$goq=1;
					break;
	case 'lbl':		$args[2]=$o;
					$goq=1;
					break;
	case 'clr':		$args[3]=$o;
					$goq=1;
					break;
	case 'rnk':		$args[4]=intVal($o);
					$goq=1;
					break;
	case 'random':	$args[5]=1;
					$goq=1;
					break;
	case 'nocache':	$args[6]=1;
					$goq=1;
					break;
	case 'show':	$args[7]=intVal($o)>0?intVal($o):-1;
					$goq=1;
					break;
	}
}

function parse($ln) {
global $goq,$args;
	$prts=explode('][',substr($ln,1,-1));
	$goq=0;
	for ($f=0;$f<sizeOf($prts);$f++) {
		if (trim($prts[$f])!='') {
			$p=explode('|',$prts[$f].'|');
			spit($p[0],$p[1]);
		}
	}
	if ($goq) {
		echo goquery($args[0],$args[1],$args[2],$args[3],$args[4],$args[5],$args[6],$args[7],$ln);
	}
}

if (isset($_REQUEST['sarsaparilla'])) {
	$path=isset($_REQUEST['path'])?$_REQUEST['path']:'';
	$error_img=$path.'error.png';
	parse(strtolower($_REQUEST['sarsaparilla']));
}
?>