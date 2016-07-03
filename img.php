<?php
include 'simple_html_dom.php';

$nopointsforums = array(
        'Introduction' => true,
		'Test/Junk' => true,
);
/**
 * Original Script created by Transfusion http://github.com/Transfusion/myBBpostcounterfor and rebuild and fixed bug by Dynamo/HMR for http://post4vps.com
 * Source is available on https://github.com/hmrserver/MyBBPostCounter
 * See http://php.net/manual/en/function.imagettftext.php to know about text on images using TTF/OTF Fonts.
 */
function imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px) {

    for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
        for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
            $bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);

   return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
}

if(!$_GET)die("Please use a id with parameter ?userid=youruserid");
if(!isset($_GET['userid'])){ die("user ID required.");}
if(!is_numeric($_GET['userid']))die("Enter a numeric ID.");
//end

$userpage = file_get_html('https://post4vps.com/user-'.$_GET["userid"].'.html');
$username = $userpage->find('span[class="largetext"]', 0)->plaintext; // find the username from the user profile page.
$username = rtrim($username, " ");
if($username == "")die("There is no such account with this ID");
//refer to http://simplehtmldom.sourceforge.net/manual.htm

$url="https://post4vps.com/search.php?action=finduser&uid=".$_GET["userid"];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$a = curl_exec($ch); // $a will contain all headers

$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // This is what you need, it will return you the last effective URL

// Uncomment to see all headers
/*
echo "<pre>";
print_r($a);echo"<br>";
echo "</pre>";
*/

//echo $url; // Voila
/* the above code was taken from http://stackoverflow.com/questions/20233115/how-to-get-destination-url-from-redirection-url-in-php
when clicking on "Find more posts" it appears the resulting search page that only returns the user's posts is dynamically generated*/

$numberofpoststhismonth = 0;
$uncountedposts = 0;
$numberofpostsnotmadeinthismonth = 0;
$pagenumber = 1;
$firstrun = True;
while (($numberofpostsnotmadeinthismonth === 0 and $numberofpoststhismonth != 0) or $firstrun === True) {
        if ($pagenumber === 1) {
                $firstrun = False;
        }
        $html = file_get_html($url.'&sortby=dateline&order=desc&uid=&page='.$pagenumber);
        $rows = 0;
        foreach($html->find('table[class="tborder"] tr[class="inline_row"]') as $element)
        {
                
                $post = $element->find('*[style="white-space: nowrap; text-align: center;"]',0);
                $forum = $element->find('a', 3)->innertext;
				if (isset($nopointsforums[$forum])) {
                                        $uncountedposts++;
					continue;
				}
				if (!is_string($post->plaintext)) {
					continue;
				}
                
				if (substr($post->plaintext, 0, 3) === 'Yes' or substr($post->plaintext, 0, 3) === 'Tod' or substr($post->plaintext, 0, 3) === 'Les' or substr($post->plaintext, 3, 3) === 'min' or substr($post->plaintext, 2, 3) === 'min' or substr($post->plaintext, 2, 3) === 'hou' or substr($post->plaintext, 3, 3) === 'hou' or substr($post->plaintext, 0, 3) === date("m-") && substr($post->plaintext, 5, 5) === date("-Y")) {
					$numberofpoststhismonth++;
         }
				else {
					$numberofpostsnotmadeinthismonth++;
				}
	}
	$totalposts = $uncountedposts+$numberofpoststhismonth;
	if (($totalposts %= 20) != 0) {
                $numberofpostsnotmadeinthismonth++;
        }
        $pagenumber++;
}

header('Pragma: public');
header('Cache-Control: max-age=240');
header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 240));
if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){ 
  // if the browser has a cached version of this image, send 304 
  header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'],true,304); 
  exit; 
} 
// Force the browser to cache the image for 4 minutes. Crawling webpages like that is not exactly good etiquette.
header("Content-Type: image/png");
$img = imagecreatefrompng("./post4vpsbg.png"); // the background image.
$font_color = imagecolorallocate($img, 255, 255, 255);
$stroke_color = imagecolorallocate($img, 0, 0, 0);
if ($numberofpoststhismonth == 0) {
	$posts_color = imagecolorallocate($img, 225, 0, 0);} else {$posts_color = imagecolorallocate($img, 221, 255, 0);}
if ($numberofpoststhismonth >= 40) {
        $message="posessed poster!";
        $message_color = imagecolorallocate($img, 111, 255, 0);
		$posts_color = imagecolorallocate($img, 0, 255, 0);
} elseif ($numberofpoststhismonth >= 30) {
        $message="Sociable Seal!";
        $message_color = imagecolorallocate($img, 0, 255, 222);
		$posts_color = imagecolorallocate($img, 0, 255, 0);
} elseif ($numberofpoststhismonth >= 20) {
        $message="Completed! Atlast ^_^";
        $message_color = imagecolorallocate($img, 0, 200, 0);
		$posts_color = imagecolorallocate($img, 0, 255, 0);
} elseif ($numberofpoststhismonth >= 17) {
        $message="OH! LIVIN ON A PRAYER";
        $message_color = imagecolorallocate($img, 255, 100, 0);
} elseif ($numberofpoststhismonth >=15) {
        $message="WOAH, HALFWAY THERE";
        $message_color = imagecolorallocate($img, 255, 255, 0);
} elseif ($numberofpoststhismonth >= 0) {
        $message="Dry your tears n Post";
        $message_color = imagecolorallocate($img, 255, 0, 0);
}

imagettfstroketext($img, 9, 0, 15, 13, $font_color, $stroke_color, "./visitor1.ttf", $username."'s posts: ", 1);
if ($numberofpoststhismonth <= 9) {
	imagettfstroketext($img, 9, 0, 175, 13, $posts_color, $stroke_color, "./visitor1.ttf", $numberofpoststhismonth, 1);
} elseif ($numberofpoststhismonth <= 99) {imagettfstroketext($img, 9, 0, 168, 13, $posts_color, $stroke_color, "./visitor1.ttf", $numberofpoststhismonth, 1);}
elseif ($numberofpoststhismonth <= 999) {imagettfstroketext($img, 9, 0, 161, 13, $posts_color, $stroke_color, "./visitor1.ttf", $numberofpoststhismonth, 1);}
imagettfstroketext($img, 7, 0, 183, 13, $font_color, $stroke_color, "./visitor1.ttf", "/20", 1);
imagettfstroketext($img, 10, 0, 15, 26, $message_color, $stroke_color, "./visitor1.ttf", $message, 1);
// http://www.johnciacia.com/2010/01/04/using-php-and-gd-to-add-border-to-text/
imagepng($img);
imagedestroy($img);
// free memory associated with this image - delete it from the PHP cache
//echo $numberofpoststhismonth;
//echo date("m-d");
?>
