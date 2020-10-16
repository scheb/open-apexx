<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

$plattforms = [
    'youtube' => [
        '(www\.|[a-z]+\.)?youtube\.com',
        'url',
        'v=(.+?)(&|$)',
        '<object width="{WIDTH}" height="{HEIGHT}"><param name="movie" value="http://www.youtube.com/v/{VIDEOID}&hl=en"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/{VIDEOID}&hl=en" type="application/x-shockwave-flash" wmode="transparent" width="{WIDTH}" height="{HEIGHT}"></embed></object>',
        'YouTube.com',
    ],
    'myvideo' => [
        '(www\.|[a-z]+\.)?myvideo\.de',
        'url',
        'watch/([0-9]+)/',
        '<object style="width:{WIDTH}px;height:{HEIGHT}px;" width="{WIDTH}" height="{HEIGHT}" type="application/x-shockwave-flash" data="http://www.myvideo.de/movie/{VIDEOID}"><param name="wmode" value="transparent"><param name="movie" value="http://www.myvideo.de/movie/{VIDEOID}"/><param name="AllowFullscreen" value="true" /><embed src="http://www.myvideo.de/movie/{VIDEOID}" width="{WIDTH}" height="{HEIGHT}" wmode="transparent"></embed></object>',
        'MyVideo',
    ],
    'sevenload' => [
        '(www\.|[a-z]+\.)?sevenload\.com',
        'url',
        '/([^/]+?)-[^/]+$',
        '<script type="text/javascript" src="http://de.sevenload.com/pl/{VIDEOID}/{WIDTH}x{HEIGHT}"></script>',
        'Sevenload',
    ],
    'clipfish' => [
        '(www\.|[a-z]+\.)?clipfish\.de',
        'url',
        '/video/([0-9]+)/',
        '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="{WIDTH}" height="{HEIGHT}" id="player" align="middle"><param name="wmode" value="transparent"><param name="allowScriptAccess" value="always" /><param name="movie" value="http://www.clipfish.de/videoplayer.swf?as=0&videoid={VIDEOID}&r=1&c=0067B3" /><param name="quality" value="high" /><param name="bgcolor" value="#0067B3" /><param name="allowFullScreen" value="true" /><embed src="http://www.clipfish.de/videoplayer.swf?as=0&videoid={VIDEOID}&r=1&c=0067B3" quality="high" bgcolor="#0067B3" width="{WIDTH}" height="{HEIGHT}" name="player" align="middle" allowFullScreen="true" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" wmode="transparent"></embed></object>',
        'Clipfish',
    ],
    'gametrailers' => [
        '(www\.|[a-z]+\.)?gametrailers\.com',
        'url',
        '/[^/]+/[^/]+/([0-9]+)',
        '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="gtembed" width="{WIDTH}" height="{HEIGHT}"><param name="allowScriptAccess" value="sameDomain" /><param name="allowFullScreen" value="true" /><param name="movie" value="http://www.gametrailers.com/remote_wrap.php?mid={VIDEOID}"/><param name="quality" value="high" /><param name="wmode" value="transparent" /><embed src="http://www.gametrailers.com/remote_wrap.php?mid={VIDEOID}" swLiveConnect="true" name="gtembed" align="middle" allowScriptAccess="sameDomain" allowFullScreen="true" quality="high" wmode="transparent" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="{WIDTH}" height="{HEIGHT}"></embed></object>',
        'GameTrailers.com',
    ],
    'machinima' => [
        '(www\.|[a-z]+\.)?machinima\.com',
        'url',
        '&id=([0-9]+)',
        '<script language="javascript">var VideoID="{VIDEOID}";var Width = "{WIDTH}";var Height = "{HEIGHT}";var Background = "#ffffff"; var Style="29"</script><script src="http://www.machinima.com/flv_player_master/view/type/embed?VideoID={VIDEOID}&Width={WIDTH}&Height={HEIGHT}" language="javascript"></script>',
        'Machinima.com',
    ],
    'dailymotion' => [
        '(www\.|[a-z]+\.)?dailymotion\.com',
        'url',
        '/([a-z0-9]+)_',
        '<object width="{WIDTH}" height="{HEIGHT}"><param name="movie" value="http://www.dailymotion.com/swf/{VIDEOID}"></param><param name="allowFullScreen" value="true"></param><param name="allowScriptAccess" value="always"></param><embed src="http://www.dailymotion.com/swf/{VIDEOID}" width="{WIDTH}" height="{HEIGHT}" allowfullscreen="true" allowscriptaccess="always"></embed></object>',
        'Dailymotion.com',
    ],
    'myspace' => [
        'vids\.myspace\.com',
        'url',
        'videoid=([0-9]+)',
        '<object width="{WIDTH}px" height="{HEIGHT}px" ><param name="allowFullScreen" value="true"/><param name="wmode" value="transparent"/><param name="movie" value="http://mediaservices.myspace.com/services/media/embed.aspx/m={VIDEOID},t=1,mt=video"/><embed src="http://mediaservices.myspace.com/services/media/embed.aspx/m={VIDEOID},t=1,mt=video" width="{WIDTH}" height="{HEIGHT}" allowFullScreen="true" type="application/x-shockwave-flash" wmode="transparent"></embed></object>',
        'MySpace.com',
    ],
    'vimeo' => [
        'vimeo\.com',
        'url',
        '/([0-9]+)',
        '<object width="{WIDTH}" height="{HEIGHT}"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id={VIDEOID}&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=ffffff&amp;fullscreen=1" /><embed src="http://vimeo.com/moogaloop.swf?clip_id={VIDEOID}&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=ffffff&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="{WIDTH}" height="{HEIGHT}"></embed></object>',
        'Vimeo',
    ],
];
