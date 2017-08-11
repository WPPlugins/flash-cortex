<?php
/*
Plugin Name: Flash Cortex
Plugin URI: http://www.hirnrinde.de/2006/05/27/flash-cortex/
Description: A filter for easily inserting Flash applets (for videos from youtube.com, video.google.com, video.google.de, sevenload.com, www.metacafe.com, www.myvideo.de, www.vimeo.com, www.guba.com, video.yahoo.com, www.revver.com, www.divshare.com and www.clipfish.de) and a MP3-Player (AsySound) into posts, based on the "<a href="http://www.asymptomatic.net/wp-hacks">Flash Filter Plus</a>"-Plugin by Owen Winkler.
Version: 1.0.8
Author: Stefan Evertz
Author URI: http://www.hirnrinde.de
*/

function flashcortex_callback1($stuff)
{
	$movie = $stuff[3];
	$measure = $stuff[1];
    $measure_backup = trim($measure);

	preg_match_all('|([a-z]+)\s*=\s*"([^"]+)"|ims', $stuff[1], $params, PREG_SET_ORDER);
	foreach($params as $param)
	{
		$$param[1]= $param[2];
	}

$movie_url=$movie;

// Identify MP3-files
if (preg_match("/http:\/\/(.*)(AsySound.swf\?)(.*)/i",$movie,$test))
{
	$movie_domain="MP3";
	$movie_direct=$test[3];
}

// Identify MP3-files, called by '<flash>'-tag
elseif (preg_match("/http:\/\/(.*)(\.mp3)/i",$movie,$test))
{
	$plugindir = dirname(flashcortex_basename(__FILE__));
	$plugindir = get_settings('siteurl') . '/wp-content/plugins/' . (($plugindir == '.') ? '' : '/' . $plugindir);
	$measure=' width="128" height="15"';
	$movie_url=$plugindir.'/AsySound.swf?'.$movie;
	$movie_domain="MP3";
	$movie_direct=$movie;
}

// Identify videos from youtube.com
elseif (preg_match("/http:\/\/(www.youtube.com|youtube.com|.{2}\.youtube.com)\/(v\/|watch\?v=)(.{11})/i",$movie,$test))
{
	$measure=' width="425" height="350"';
	$movie_url="http://www.youtube.com/v/".$test[3];
    if ($test[1]=="youtube.com")
    {
	    $movie_domain="www.youtube.com";
    }
    else
    {
        $movie_domain=$test[1];
    }
	$movie_direct="http://".$movie_domain."/watch?v=".$test[3];
}
// Identify videos from video.google.com and video.google.de
elseif (preg_match("/http:\/\/(video.google.com|video.google.de)\/(videoplay\?docid=)(-?[0-9]+)/i",$movie,$test))
{
	if ($test[1]=='video.google.de')
	{
		$lang_add='&amp;hl=de';
	}
	$measure=' width="400" height="326"';
	$movie_url="http://video.google.com/googleplayer.swf?docId=".$test[3].$lang_add;
	$movie_domain=$test[1];
	$movie_direct="http://".$test[1]."/videoplay?docid=".$test[3];
}
// Identify videos from www.divshare.com
elseif (preg_match("/http:\/\/(www.divshare.com|divshare.com)\/(download)\/([0-9]+)-(.{3})/i",$movie,$test))
{
	$measure=' width="400" height="300"';
	$movie_url="http://www.divshare.com/flash/video2?myId=".$test[3]."-".$test[4];
	$movie_domain="www.divshare.com";
	$movie_direct="http://www.divshare.com/download/".$test[3]."-".$test[4];
}
// Identify videos from www.vimeo.com
elseif (preg_match("/http:\/\/(www.vimeo.com|vimeo.com)\/(clip:|[0-9]+)([0-9]*)/i",$movie,$test))
{
    $vimeo_id=$test[2];
    if ($vimeo_id=="clip:")
    {
        $vimeo_part="clip:";
        $vimeo_id=$test[3];
    }
	$measure=' width="400" height="300"';
	$movie_url="http://www.vimeo.com/moogaloop.swf?clip_id=".$vimeo_id."&amp;server=vimeo.com";
	$movie_domain="www.vimeo.com";
	$movie_direct="http://www.vimeo.com/".$vimeo_part.$vimeo_id;
}
// Identify videos from www.guba.com
elseif (preg_match("/http:\/\/(www.guba.com)\/(watch)\/(.{10})/i",$movie,$test))
{
	$measure=' width="375" height="360"';
	$movie_url="http://www.guba.com/f/root.swf?video_url=http://free.guba.com/uploaditem/".$test[3]."/flash.flv";
	$movie_domain="www.guba.com";
	$movie_direct="http://www.guba.com/watch/".$test[3];
}
// Identify videos from www.myvideo.de
elseif (preg_match("/http:\/\/(www.myvideo.de)\/(watch)\/([0-9]+)/i",$movie,$test))
{
	$measure=' width="470" height="406"';
	$movie_url="http://www.myvideo.de/movie/".$test[3];
	$movie_domain="www.myvideo.de";
	$movie_direct="http://www.myvideo.de/watch/".$test[3];
}
// Identify videos from www.metacafe.com
elseif (preg_match("/http:\/\/(www.metacafe.com)\/(watch)\/([0-9]+)\/(.*)\//i",$movie,$test))
{
	preg_match('/([a-zA-Z_]*)/i',$test[4],$mc_special);
	$measure=' width="400" height="345"';
	$movie_url="http://www.metacafe.com/fplayer/".$test[3]."/".$mc_special[1].".swf";
	$movie_domain="www.metacafe.com";
	$movie_direct="http://www.metacafe.com/watch/".$test[3]."/".$mc_special[1]."/";
}
// Identify videos from www.clipfish.de
elseif (preg_match("/http:\/\/(www.clipfish.de)\/(player\.php\?videoid=)(.{20})/i",$movie,$test))
{
	$measure=' width="464" height="380"';
	$movie_url="http://www.clipfish.de/videoplayer.swf?videoid=".$test[3]."&amp;r=1&amp;as=0";
	$movie_domain="www.clipfish.de";
	$movie_direct="http://www.clipfish.de/player.php?videoid=".$test[3];
}
// Identify videos from video.yahoo.com (new syntax)
elseif (preg_match("/http:\/\/(video.yahoo.com)\/(watch)\/(.{7})\/(.{7})/i",$movie,$test))
{
	$measure=' width="512" height="323"';
	$movie_url="http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?id=".$test[4]."&amp;vid=".$test[3];
	$movie_domain="video.yahoo.com";
	$movie_direct="http://video.yahoo.com/watch/".$test[3]."/".$test[4];
}
// Identify videos from video.yahoo.com (old syntax)
elseif (preg_match("/http:\/\/(video.yahoo.com)\/(video)\/(play\?vid=)(.{32})\.([0-9]+)/i",$movie,$test))
{
	$measure=' width="425" height="350"';
	$movie_url="http://us.i1.yimg.com/cosmos.bcst.yahoo.com/player/media/swf/FLVVideoSolo.swf?id=".$test[5];
	$movie_domain="video.yahoo.com";
	$movie_direct="http://video.yahoo.com/video/play?vid=".$test[4].".".$test[5];
}
// Identify videos from de.video.yahoo.com (old syntax)
elseif (preg_match("/http:\/\/(de.video.yahoo.com)\/(video)\/(play\?vid=)(.{32})\.([0-9]+)/i",$movie,$test))
{
	$measure=' width="425" height="350"';
	$movie_url="http://us.i1.yimg.com/cosmos.bcst.yahoo.com/player/media/swf/FLVVideoSolo.swf?id=".$test[5];
	$movie_domain="de.video.yahoo.com";
	$movie_direct="http://de.video.yahoo.com/video/play?vid=".$test[4].".".$test[5];
}
// Identify videos from www.revver.com / one.revver.com
elseif (preg_match("/http:\/\/(one.revver.com|revver.com|www.revver.com)\/(watch|video)\/(.{6})/i",$movie,$test))
{
	$measure=' width="480" height="392"';
	$movie_url="http://flash.revver.com/player/1.0/player.swf?mediaId=".$test[3]."&amp;affiliateId=0";
	$movie_domain="www.revver.com";
	$movie_direct="http://www.revver.com/video/".$test[3]."/flv";
}
// Identify videos from sevenload.de (old syntax)
elseif (preg_match("/http:\/\/(sevenload.de)\/(pl|videos)\/(.{7})(.*)/i",$movie,$test))
{
	if (preg_match("/ width=\"(\d{1,3})\" height=\"(\d{1,3})\"*/i",$measure,$measure_prepath))
	{
		$measure_url="/".$measure_prepath[1]."x".$measure_prepath[2];

	}
	elseif (preg_match("/(\d{3})x(\d{3})*/i",$test[4],$measure_path))
	{
		$measure=' width="'.$measure_path[1].'" height="'.$measure_path[2].'"';
		$measure_url="/".$measure_path[0];
	}
	elseif (empty($measure))
	{
		$measure=' width="380" height="313"';
		$measure_url="/380x313";
	}
	$movie_url="http://sevenload.de/pl/".$test[3].$measure_url."/swf";
	$movie_domain="sevenload.de";
	$movie_direct="http://sevenload.de/videos/".$test[3];
}
// Identify videos from sevenload.de (new syntax)
elseif (preg_match("/http:\/\/(.{2})\.(sevenload.com)\/(pl|videos)\/(.{7})(.*)/i",$movie,$test))
{
	if (preg_match("/ width=\"(\d{1,3})\" height=\"(\d{1,3})\"*/i",$measure,$measure_prepath))
	{
		$measure_url="/".$measure_prepath[1]."x".$measure_prepath[2];

	}
	elseif (preg_match("/(\d{3})x(\d{3})*/i",$test[4],$measure_path))
	{
		$measure=' width="'.$measure_path[1].'" height="'.$measure_path[2].'"';
		$measure_url="/".$measure_path[0];
	}
	elseif (empty($measure))
	{
		$measure=' width="380" height="313"';
		$measure_url="/380x313";
	}
	$movie_url="http://".$test[1].".sevenload.com/pl/".$test[4].$measure_url."/swf";
	$movie_domain=$test[1].".sevenload.com";
	$movie_direct="http://".$test[1].".sevenload.com/videos/".$test[4];
}

if ($measure_backup<>"")
{
    $measure=$measure_backup;
}

$out = '<object type="application/x-shockwave-flash"'.$measure.' data="'.$movie_url.'"><param name="movie" value="' . $movie_url . '" /><param name="quality" value="high" />Medium: '.$movie_domain.'</object>'."\n".'<br />';
if (!empty($movie_direct))
{
	$out.='Link: <a href="'.$movie_direct.'">'.$movie_domain.'</a>';
}

	return $out;
}

function flashcortex_callback2($stuff)
{
	$url = $stuff[1];
	$plugindir = dirname(flashcortex_basename(__FILE__));

	$plugindir = get_settings('siteurl') . '/wp-content/plugins/' . (($plugindir == '.') ? '' : '/' . $plugindir);
	$params = array('', ' width="128" height="15"', '', $plugindir . '/AsySound.swf?' . $url);
	return flashcortex_callback1($params);
}

function flashcortex_basename($file) {
	return preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', $file);
}

function flashcortex_encode($content)
{
	$content = preg_replace_callback('|[<\[]flash(( ?[a-z]+="[^"]+")*)[>\]](.*)[<\[]/flash[>\]]|imsU', 'flashcortex_callback1', $content);
	return preg_replace_callback('/\\[((https?):\/\/[\\s-A-Z0-9+&@#\/%?=~_|!:,.;]*.mp3)\\]/i', 'flashcortex_callback2', $content);
}
add_filter('the_content', 'flashcortex_encode', '11');

// Adds "Flash Cortex"-quicktag to the "Post"-interface
if((strpos($_SERVER['REQUEST_URI'], 'post.php'))OR(strpos($_SERVER['REQUEST_URI'], 'post-new.php'))OR(strpos($_SERVER['REQUEST_URI'], 'page.php'))OR(strpos($_SERVER['REQUEST_URI'], 'page-new.php')))
{
	add_action('admin_footer', 'flashcortexAddQuicktag');

	function flashcortexAddQuickTag()
	{
			echo <<<EOT
			<script type="text/javascript">
				<!--
					var flashcortexToolbar = document.getElementById("ed_toolbar");
					if(flashcortexToolbar){
						var flashcortexNr = edButtons.length;
						edButtons[edButtons.length] = new edButton('ed_flashcortex','','','','');
						var flashcortexBut = flashcortexToolbar.lastChild;
						while (flashcortexBut.nodeType != 1){
							flashcortexBut = flashcortexBut.previousSibling;
						}
						flashcortexBut = flashcortexBut.cloneNode(true);
						flashcortexToolbar.appendChild(flashcortexBut);
						flashcortexBut.value = 'Flash Cortex';
						flashcortexBut.onclick = edInsertflashcortex;
						flashcortexBut.title = "Video einbetten";
						flashcortexBut.id = "ed_flashcortex";
					}

					function edInsertflashcortex() {
						if(!edCheckOpenTags(flashcortexNr)){
							var U = prompt('Bitte den URL des Videos eingeben' , 'http://');
							var theTag = '[flash]' + U + '[/flash]';
							edButtons[flashcortexNr].tagStart  = theTag;
							edInsertTag(edCanvas, flashcortexNr);
						} else {
							edInsertTag(edCanvas, flashcortexNr);
						}
					}
					//-->
			</script>
EOT;
	}
}

?>