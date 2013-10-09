<?php

include('core/init.inc.php');

$width = (isset($_GET['width'])) ? min(1220, max(305, intval($_GET['width']))) : 610;
$height = (isset($_GET['height'])) ? min(240, max(60, intval($_GET['height']))) : 120;

$bg_color = (isset($_GET['bg_color']) && preg_match('#^[a-f0-9]{6}$#i', $_GET['bg_color'])) ? array(hexdec(substr($_GET['bg_color'], 0, 2)), hexdec(substr($_GET['bg_color'], 2, 2)), hexdec(substr($_GET['bg_color'], 4, 2))) : array(255, 255, 255);
$title_color = (isset($_GET['title_color']) && preg_match('#^[a-f0-9]{6}$#i', $_GET['title_color'])) ? array(hexdec(substr($_GET['title_color'], 0, 2)), hexdec(substr($_GET['title_color'], 2, 2)), hexdec(substr($_GET['title_color'], 4, 2))) : array(40, 40, 40);
$axis_label_color = (isset($_GET['axis_label_color']) && preg_match('#^[a-f0-9]{6}$#i', $_GET['axis_label_color'])) ? array(hexdec(substr($_GET['axis_label_color'], 0, 2)), hexdec(substr($_GET['axis_label_color'], 2, 2)), hexdec(substr($_GET['axis_label_color'], 4, 2))) : array(40, 40, 40);
$axis_value_color = (isset($_GET['axis_value_color']) && preg_match('#^[a-f0-9]{6}$#i', $_GET['axis_value_color'])) ? array(hexdec(substr($_GET['axis_value_color'], 0, 2)), hexdec(substr($_GET['axis_value_color'], 2, 2)), hexdec(substr($_GET['axis_value_color'], 4, 2))) : array(40, 40, 40);
$fill_color = (isset($_GET['fill_color']) && preg_match('#^[a-f0-9]{6}$#i', $_GET['fill_color'])) ? array(hexdec(substr($_GET['fill_color'], 0, 2)), hexdec(substr($_GET['fill_color'], 2, 2)), hexdec(substr($_GET['fill_color'], 4, 2))) : array(80, 180, 210);
$line_color = (isset($_GET['line_color']) && preg_match('#^[a-f0-9]{6}$#i', $_GET['line_color'])) ? array(hexdec(substr($_GET['line_color'], 0, 2)), hexdec(substr($_GET['line_color'], 2, 2)), hexdec(substr($_GET['line_color'], 4, 2))) : array(20, 120, 160);
$axis_color = (isset($_GET['axis_color']) && preg_match('#^[a-f0-9]{6}$#i', $_GET['axis_color'])) ? array(hexdec(substr($_GET['axis_color'], 0, 2)), hexdec(substr($_GET['axis_color'], 2, 2)), hexdec(substr($_GET['axis_color'], 4, 2))) : array(0, 0, 0);

$image = imagecreatetruecolor($width, $height);
imageantialias($image, true);

$bg_color = imagecolorallocate($image, $bg_color[0], $bg_color[1], $bg_color[2]);
$error_color = imagecolorallocate($image, 255, 40, 40);
$title_color = imagecolorallocate($image, $title_color[0], $title_color[1], $title_color[2]);
$axis_label_color = imagecolorallocate($image, $axis_label_color[0], $axis_label_color[1], $axis_label_color[2]);
$axis_value_color = imagecolorallocate($image, $axis_value_color[0], $axis_value_color[1], $axis_value_color[2]);
$fill_color = imagecolorallocate($image, $fill_color[0], $fill_color[1], $fill_color[2]);
$line_color = imagecolorallocate($image, $line_color[0], $line_color[1], $line_color[2]);
$axis_color = imagecolorallocate($image, $axis_color[0], $axis_color[1], $axis_color[2]);

imagefill($image, 0, 0, $bg_color);

if (!isset($_GET['id']) || ($project = project::get_by_id($_GET['id'])) === false){
	imagestring($image, 5, 5, 5, 'Unknown project', $error_color);
}else{
	$file_stats = $project->get_file_stats(14);
	
	$ttfbox = imagettfbbox(12, 0.0, './ext/fonts/Vera-webfont.ttf', $project->get_title());
	imagettftext($image, 12, 0.0, 3, 16, $title_color, './ext/fonts/Vera-webfont.ttf', $project->get_title());
	
	foreach ($file_stats as $file_number => &$info){
		if (isset($info['stats'])){
			foreach ($info['stats'] as $key => $stats){
				$index = round($stats['start_time'] / 86400) * 86400;
				
				if (!isset($download_stats[$index])){
					$download_stats[$index] = 0;
				}
				
				$download_stats[$index] += $stats['downloads'];
			}
		}
	}
	
	if (empty($download_stats) || count($download_stats) < 2){
		imagestring($image, 5, 5, 20, 'Awaiting data', $error_color);
	}else{
		$values = array_values($download_stats);
		$labels = array_keys($download_stats);
		
		$points = count($download_stats);
		$ymax = max($download_stats);
		$ymax = (substr($ymax, 0, 1) + 1) * pow(10, strlen($ymax) - 1);
		
		$ttfbox = imagettfbbox(10, 90.0, './ext/fonts/Vera-webfont.ttf', 'Downloads');
		$label_width = $ttfbox[1] - $ttfbox[3];
		imagettftext($image, 10, 90.0, 12, 25 + (($height - 30) / 2) + ($label_width / 2), $axis_label_color, './ext/fonts/Vera-webfont.ttf', 'Downloads');
		
		$ttfbox = imagettfbbox(6, 0.0, './ext/fonts/Vera-webfont.ttf', $ymax);
		$value_width = $ttfbox[2] - $ttfbox[0];
		imagettftext($image, 6, 0.0, 16, 34, $axis_value_color, './ext/fonts/Vera-webfont.ttf', $ymax);
		
		for ($i = 1; $i < $points; ++$i){
			$x1 = 22 + $value_width + ($i - 1) * (($width - 22 - $value_width - 5) / ($points - 1));
			$y1 = $height - 5 - (($values[$i - 1] / $ymax) * ($height - 30));
			
			$x2 = 22 + $value_width + ($i) * (($width - 22 - $value_width - 5) / ($points - 1));
			$y2 = $height - 5 - (($values[$i] / $ymax) * ($height - 30));
			
			imagefilledpolygon($image, array($x1, $height - 5, $x1, $y1, $x2, $y2, $x2, $height - 5), 4, $fill_color);
			imageline($image, $x1, $y1, $x2, $y2, $line_color);
		}
		
		imageline($image, 19 + $value_width, 30, 22 + $value_width, 30, $axis_color);
		imageline($image, 22 + $value_width, 25, 22 + $value_width, $height - 5, $axis_color);
		imageline($image, 22 + $value_width, $height - 5, $width - 5, $height - 5, $axis_color);
	}
}

header('Content-Type: image/png');
header('Expires: ' . gmdate('D, d M Y H:i:s', mktime(0, 0, 0, date('n'), date('j') + 1)) . ' GMT');
header("Last-Modified: " . gmdate('D, d M Y H:i:s', mktime(0, 0, 0)) . " GMT");

imagepng($image);

?>
