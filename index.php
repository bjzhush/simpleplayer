<?php
/* bjzhush@gmail.com  2012/09/01 
 * *读取某文件夹下的歌曲，随机播放，并推荐若干首可以供点播
 * 若总歌曲数少于随机歌曲数则推荐总歌曲数个，保证了推荐的歌曲不重复
 * 对于文件是否存在做了判断，如歌曲文件不存在则会有提示
 * 随便写了下，没有支持子目录,用了is_file排除了子目录
 *
 */
$fpath = 'music';
$rand_num = 17;
$rand_del_cache = rand(1,10);
if ($rand_del_cache = 8) {
    unlink('cache.bin');
}
//检查refer，禁止连续播放相同都歌曲
if (
        isset($_SERVER['QUERY_STRING'])  && 
        strlen($_SERVER['QUERY_STRING']) &&
        isset($_SERVER['HTTP_REFERER'])  && 
        !strpos($_SERVER['HTTP_REFERER'],$_SERVER['QUERY_STRING'])===FALSE
   ) {
    unset($_GET['song']);
   }


$cacheFile =  'cache.bin';
if (file_exists($cacheFile)) {
    $fp = file_get_contents($cacheFile);
    $all = json_decode($fp, TRUE);
} else {
    if ($handle = opendir($fpath)) {
        
        $all = array();
        while (false !== ($file = readdir($handle))) {
            if(!($file==='.'||$file==='..')){
            array_push($all,$file);
            }
                }
        closedir($handle);
    } else {
        eixt('Unable to open dir');
    }
    file_put_contents($cacheFile, json_encode($all),  LOCK_EX);
}

if(isset($_GET['song'])&&strlen($_GET['song'])){
$thissong = urldecode($_GET['song']);
}
else{
$thissong = $all[rand(0,count($all)-1)];

}


if(!file_exists(rtrim($fpath).'/'.$thissong)){
	$errorMessage = "File ".$thissong." Seems doesnot exist";
    exit($errorMessage);
}

$arr_may = array();
$real_randnum = $rand_num>count($all) ? count($all) : $rand_num;
while(count($arr_may)<$real_randnum){
	$tmp_one = $all[rand(0,count($all)-1)];
	in_array($tmp_one,$arr_may)? NULL : array_push($arr_may,$tmp_one);
}


echo "<html>
	<head>
	<title>".$thissong."--我的八音盒</title>";

?>
    <script src='./jquery.js'></script>
    <link type='text/css' rel='stylesheet' href='./style.css'>


	<script type='text/javascript'>
    $(document).ready(function(){
        // show play time count
        var playCount = parseInt(localStorage.getItem('playCount'));
        if (playCount == undefined ) {
            playCount = 1;
        }
        playCount += 1;
        localStorage.setItem('playCount', playCount);
        $('#playCount').html(playCount);
    });

    $(document).keydown(function(e){
        var key =  e.which;
        if(key == 32){
            var song = $('#media').get(0);
            if(song.paused)
            {
                song.play();
            }
            else
            {
                song.pause();
            }
        } else if (key == 78) {
            window.location = './index.php';
        } else if (key == 38) {
            document.getElementById('media').volume = document.getElementById('media').volume+0.1;
        } else if (key == 40) {
            document.getElementById('media').volume = document.getElementById('media').volume-0.1;
        } else if (key == 37) {
            document.getElementById('media').currentTime = document.getElementById('media').currentTime-5;
        } else if (key == 39) {
            document.getElementById('media').currentTime = document.getElementById('media').currentTime+5;
        } else if (key == 39) {
        }
	});

    function repeatorreload()
    {
        if ($('#myonoffswitch').is(':checked') == true) {
            document.getElementById('media').currentTime = 0;
            document.getElementById('media').play();
        } else {
            document.location.reload();
        }
    }
    </script>

	</head>
	<body>
    <center>
<?php


echo "<br><br><br><br><br><br>";
echo ' <audio onended="repeatorreload()" id="media" controls="controls" autoplay="autoplay">
	<source src="music/'.$thissong.'" type="audio/mpeg" />
	Your browser does not support the audio element.
	</audio>
<a href="'.$_SERVER['SCRIPT_NAME'].'"><img src=./next.jpg height="30" width="30"></a><br><br><br><br><br><br><br><br><br>
<div width="30" class="onoffswitch"> <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="myonoffswitch" unchecked> <label class="onoffswitch-label" for="myonoffswitch"> <div class="onoffswitch-inner"></div> <div class="onoffswitch-switch"></div> </label> </div> ';
echo '<div align="center"><h1>现在播放的是：<font color="#ff0000">'.$thissong.'</font></h1>';
echo "或许也可以听听:<br>";

foreach($arr_may as $k =>$song){
	echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?song='.urlencode($song).'">'.$song.'</a><br><br>';

}

echo " 本播放器共播放了<span id='playCount'></span>首歌曲 @2012 <a href='http://www.shuaizhu.com' target='_blank'>Shuaizhu.com</a> </div>

	</body>
	</html>
	";
