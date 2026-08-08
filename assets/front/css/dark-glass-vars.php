<?php
header("Content-Type:text/css");
$color = $_GET['color']; // Change your Color Here
$color2 = isset($_GET['color2']) ? $_GET['color2'] : null;

function checkhexcolor($color)
{
  return preg_match('/^#[a-f0-9]{6}$/i', $color);
}

function hex2rgb($color)
{
  $hex = ltrim($color, '#');
  return implode(',', [
    hexdec(substr($hex, 0, 2)),
    hexdec(substr($hex, 2, 2)),
    hexdec(substr($hex, 4, 2)),
  ]);
}

if (isset($_GET['color']) and $_GET['color'] != '') {
  $color = "#" . $_GET['color'];
}

if (isset($_GET['color2']) and $_GET['color2'] != '') {
  $color2 = "#" . $_GET['color2'];
}

if (!$color or !checkhexcolor($color)) {
  $color = "#25D06F";
}

if (!$color2 or !checkhexcolor($color2)) {
  $color2 = "#2D8CFF";
}

$colorRgb = hex2rgb($color);
$color2Rgb = hex2rgb($color2);
?>

:root {
--accent: <?php echo $color; ?>;
--accent-2: <?php echo $color2; ?>;
--accent-rgb: <?php echo $colorRgb; ?>;
--accent-2-rgb: <?php echo $color2Rgb; ?>;
--glass-bg: rgba(255,255,255,0.06);
--glass-bg-strong: rgba(255,255,255,0.1);
--glass-border: rgba(255,255,255,0.14);
--glass-blur: 18px;
}
