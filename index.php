<?php
/* bjzhush@gmail.com  2012/09/01 
 * *读取某文件夹下的歌曲，随机播放，并推荐若干首可以供点播
 * 若总歌曲数少于随机歌曲数则推荐总歌曲数个，保证了推荐的歌曲不重复
 * 对于文件是否存在做了判断，如歌曲文件不存在则会有提示
 * 随便写了下，没有支持子目录,用了is_file排除了子目录
 *
 */
$fpath = 'music';
$rand_num = 9;
//检查refer，禁止连续播放相同都歌曲
if (
        isset($_SERVER['QUERY_STRING'])  && 
        strlen($_SERVER['QUERY_STRING']) &&
        isset($_SERVER['HTTP_REFERER'])  && 
        !strpos($_SERVER['HTTP_REFERER'],$_SERVER['QUERY_STRING'])===FALSE
   ) {
    unset($_GET['song']);
   }


if ($handle = opendir($fpath)) {
	
	$all = array();
	while (false !== ($file = readdir($handle))) {
		if(!($file==='.'||$file==='..')){
		array_push($all,$file);
		}
		    }
	closedir($handle);
}

if(isset($_GET['song'])&&strlen($_GET['song'])){
$thissong = urldecode($_GET['song']);
}
else{
$thissong = $all[rand(0,count($all)-1)];

}


if(!file_exists(rtrim($fpath).'/'.$thissong)){
	$alertmsg = "File ".$thissong." Seems doesnot exist";
}
else{
	$alertmsg = '';
}

$arr_may = array();
$real_randnum = $rand_num>count($all) ? count($all) : $rand_num;
while(count($arr_may)<$real_randnum){
	$tmp_one = $all[rand(0,count($all)-1)];
	in_array($tmp_one,$arr_may)? NULL : array_push($arr_may,$tmp_one);
}


echo "<html>
	<head>
	<title>".$thissong."--我的八音盒</title>

    <script src='./jquery.js'></script>

	<script type='text/javascript'>
    $(document).keyup(function(e){
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
        }
	});
    </script>

	</head>
	<body>
	<br> <br> <br> <br> <br> <br>
	";
echo '<div align="center">现在播放的是：'.$thissong;
echo "<br><br>";

echo $alertmsg;

echo ' <audio onended="document.location.reload()" id="media" controls="controls" autoplay="autoplay">
	<source src="music/'.$thissong.'" type="audio/mpeg" />
	Your browser does not support the audio element.
	</audio>
	';
echo '<a href="'.$_SERVER['SCRIPT_NAME'].'"><img src=./next.jpg></a><br><br><br><br>';
echo "或许也可以听听:<br><br><br>";

foreach($arr_may as $k =>$song){
	echo '<a href="'.$_SERVER['SCRIPT_NAME'].'?song='.urlencode($song).'">'.$song.'</a><br><br>';

}


echo "	@2012 <a href='http://www.shuaizhu.com' target='_blank'>Shuaizhu.com</a> </div>

	</body>
	</html>
	";
