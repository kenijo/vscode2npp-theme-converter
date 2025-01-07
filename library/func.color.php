<?php

/**
 * Tests
 */
// $foregroundColor = '8327648';
// echo "DEC: $foregroundColor => HEX: "  . dec2hex($foregroundColor) . "<br />";
// echo "DEC: $foregroundColor => HEXA: " . dec2hexa($foregroundColor) . "<br />";
// echo "DEC: $foregroundColor => RGB: "  . dec2rgb($foregroundColor) . "<br />";
// echo "DEC: $foregroundColor => RGBA: " . dec2rgba($foregroundColor) . "<br /><br />";

// $foregroundColor = '#7F11E0';
// echo "HEX: $foregroundColor => DEC: "  . hex2dec($foregroundColor) . "<br />";
// echo "HEX: $foregroundColor => HEXA: " . hex2hexa($foregroundColor) . "<br />";
// echo "HEX: $foregroundColor => RGB: "  . hex2rgb($foregroundColor) . "<br />";
// echo "HEX: $foregroundColor => RGBA: " . hex2rgba($foregroundColor) . "<br /><br />";

// $foregroundColor = '#7F11E055';
// $backgroundColor = '#2D2A2E';
// echo "HEXA: $foregroundColor => DEC: "  . hexa2dec($foregroundColor, $backgroundColor) . "<br />";
// echo "HEXA: $foregroundColor => HEX: "  . hexa2hex($foregroundColor, $backgroundColor) . "<br />";
// echo "HEXA: $foregroundColor => RGB: "  . hexa2rgb($foregroundColor, $backgroundColor) . "<br />";
// echo "HEXA: $foregroundColor => RGBA: " . hexa2rgba($foregroundColor) . "<br /><br />";

// $foregroundColor = 'RGB(127, 17, 224)';
// echo "RGB: $foregroundColor => DEC: "  . rgb2dec($foregroundColor) . "<br />";
// echo "RGB: $foregroundColor => HEX: "  . rgb2hex($foregroundColor) . "<br />";
// echo "RGB: $foregroundColor => HEXA: " . rgb2hexa($foregroundColor) . "<br />";
// echo "RGB: $foregroundColor => RGBA: " . rgb2rgba($foregroundColor) . "<br /><br />";

// $foregroundColor = 'RGBA(127,17,224,0.333)';
// $backgroundColor = 'RGB(45, 42, 46)';
// echo "RGBA :$foregroundColor => DEC: "  . rgba2dec($foregroundColor, $backgroundColor) . "<br />";
// echo "RGBA :$foregroundColor => HEX: "  . rgba2hex($foregroundColor, $backgroundColor) . "<br />";
// echo "RGBA :$foregroundColor => HEXA: " . rgba2hexa($foregroundColor) . "<br />";
// echo "RGBA :$foregroundColor => RGB: "  . rgba2rgb($foregroundColor, $backgroundColor) . "<br /><br />";

function is_dec($color)
{
    $color = trim($color);

    if (
        is_numeric($color)
        && is_int((int) $color)
        && $color >= 0
        && $color <= 16777215
        && strlen($color) <= 8
    ) {
        return true;
    } else {
        return false;
    }
}

function is_hex($color)
{
    $color = trim($color);

    if (preg_match('/^#?(([a-f0-9]{3}){1,2})$/i', $color)) {
        return true;
    } else {
        return false;
    }
}

function is_hexa($color)
{
    $color = trim($color);

    if (preg_match('/^#?(([a-f0-9]{8}))$/i', $color)) {
        return true;
    } else {
        return false;
    }
}

function is_rgb($color)
{
    $color = str_replace(array_map('strtolower', ['rgba', 'rgb', '(', ')', ' ']), '', strtolower($color));
    $colorExploded = explode(',', $color);
    $colorImploded = implode($colorExploded);

    if (
        is_numeric($colorImploded)
        && is_int((int) $colorImploded)
        && count($colorExploded) === 3
        && min($colorExploded) >= 0
        && max($colorExploded) <= 255
    ) {
        return true;
    } else {
        return false;
    }
}

function is_rgba($color)
{
    $color = str_replace(array_map('strtolower', ['rgba', 'rgb', '(', ')', ' ']), '', strtolower($color));
    $colorExploded = explode(',', $color);
    $colorImploded = implode($colorExploded);

    if (
        is_numeric($colorImploded)
        && count($colorExploded) === 4
        && min($colorExploded) >= 0
        && max($colorExploded) <= 255
    ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Normalize color format
 *
 * @param   string  $color  Color
 * @return  string          Normalized color format
 */
function normalizeColor($color)
{
    if (is_hex($color) || is_hexa($color)) {
        // Cleanup the color string
        $color = ltrim($color, '#');

        // Convert 3-digit HEX color into 6-digit HEX color
        if (strlen($color) === 3) {
            $r = substr($color, 0, 1);
            $g = substr($color, 1, 1);
            $b = substr($color, 2, 1);
            $color = "$r$r$g$g$b$b";
        }

        return strtoupper("#$color");
    } elseif (is_rgb($color) || is_rgba($color)) {
        // Cleanup the color string
        $color = strtolower($color);
        $arr = array_map('strtolower', ['rgba', 'rgb', '(', ')', ' ']);
        $color = str_replace($arr, '', $color);

        if (substr_count($color, ',') === 2) {
            return "RGB($color)";
        } else if (substr_count($color, ',') === 3) {
            return "RGBA($color)";
        }
    }

    return $color;
}

/**
 * Convert DEC color to HEX color
 *
 * @param   string  $color  DEC color
 * @return  string          HEX color
 */
function dec2hex($color)
{
    $color = dechex($color);
    $color = str_pad($color, 6, '0');

    return normalizeColor($color);
}

/**
 * Convert DEC color to HEXA color (HEX + Alpha)
 *
 * @param   string  $color  DEC color
 * @return  string          HEXA color (HEX + Alpha)
 */
function dec2hexa($color)
{
    $color = dechex($color);
    $color = str_pad($color, 6, '0');
    $color = hex2hexa($color);

    return normalizeColor($color);
}

/**
 * Convert DEC color to RGB color
 *
 * @param   string  $color  DEC color
 * @return  string          RGB color
 */
function dec2rgb($color)
{
    $color = dechex($color);
    $color = str_pad($color, 6, '0');
    $color = hex2rgb($color);

    return normalizeColor($color);
}

/**
 * Convert DEC color to RGBA color (RGB + Alpha)
 *
 * @param   string  $color  DEC color
 * @return  string          RGBA color (RGB + Alpha)
 */
function dec2rgba($color)
{
    $color = dechex($color);
    $color = str_pad($color, 6, '0');
    $color = hex2rgba($color);

    return normalizeColor($color);
}

/**
 * Convert HEX color to DEC color
 *
 * @param   string  $color  HEX color
 * @return  string          DEC color
 */
function hex2dec($color)
{
    if (is_hex($color)) {
        $color = normalizeColor($color);
        $color = ltrim($color, '#');
        $color = hexdec($color);

        return $color;
    } else {
        return false;
    }
}

/**
 * Convert HEX color to HEXA color (HEX + Alpha)
 *
 * @param   string  $color  HEX color
 * @return  string          HEXA color (HEX + Alpha)
 */
function hex2hexa($color)
{
    if (is_hex($color)) {
        $color = normalizeColor($color);
        return normalizeColor("{$color}FF");
    } else {
        return false;
    }
}

/**
 * Convert HEX color into RGB color
 *
 * @param   string  $color  HEX color
 * @return  string          RGB color
 */
function hex2rgb($color)
{
    if (is_hex($color)) {
        $color = normalizeColor($color);

        // Extract RGB components
        $color = ltrim($color, '#');
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));

        return normalizeColor("$r,$g,$b");
    } else {
        return false;
    }
}

/**
 * Convert HEX color into RGBA color (RGB + Alpha)
 *
 * @param   string  $color  HEX color
 * @return  string          RGBA color (RGB + Alpha)
 */
function hex2rgba($color)
{
    if (is_hex($color)) {
        $color = normalizeColor($color);

        // Extract RGBA components
        $color = ltrim($color, '#');
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
        $a = 1;

        return normalizeColor("$r,$g,$b,$a");
    } else {
        return false;
    }
}

/**
 * Convert HEXA color (HEX + Alpha) to DEC color
 *
 * @param   string  $foregroundColor    Foreground HEXA color (HEX + Alpha)
 * @return  string                      DEC color
 */
function hexa2dec($foregroundColor, $backgroundColor = '#FFFFFF')
{
    if (is_hexa($foregroundColor) && is_hex($backgroundColor)) {
        $color = hexa2hex($foregroundColor, $backgroundColor);

        $color = ltrim($color, '#');
        $color = hexdec($color);

        return $color;
    } else {
        return false;
    }
}

/**
 * Convert HEXA color (HEX + Alpha) into HEX color
 *
 * @param   string  $foregroundColor    Foreground HEXA color (HEX + Alpha)
 * @param   string  $backgroundColor    Background HEX color (without alpha channel) the foreground color will blend onto
 * @return  string                      HEX color
 */
function hexa2hex($foregroundColor, $backgroundColor = '#FFFFFF')
{
    if (is_hexa($foregroundColor) && is_hex($backgroundColor)) {
        $color = hexa2rgb($foregroundColor, $backgroundColor);
        $color = rgb2hex($color);

        return normalizeColor($color);
    } else {
        return false;
    }
}

/**
 * Convert HEXA color (HEX + Alpha) into RGB color
 *
 * @param   string  $foregroundColor    Foreground HEXA color (HEX + Alpha)
 * @param   string  $backgroundColor    Background HEX color (without alpha channel) the foreground color will blend onto
 * @return  string                      RGB color
 */
function hexa2rgb($foregroundColor, $backgroundColor = '#FFFFFF')
{
    if (is_hexa($foregroundColor) && is_hex($backgroundColor)) {
        $foregroundColor = hexa2rgba($foregroundColor);
        $backgroundColor = hex2rgb($backgroundColor);

        $color = rgba2rgb($foregroundColor, $backgroundColor);

        return normalizeColor($color);
    } else {
        return false;
    }
}

/**
 * Convert HEXA color (HEX + Alpha) into RGBA color (RGB + Alpha)
 *
 * @param   string  $hexa               HEXA color (HEX + Alpha)
 * @param   int     $alphaPrecision     Precision of the RGBA alpha channel
 * @return  string                      RGBA color (RGB + Alpha)
 */
function hexa2rgba($color, $alphaPrecision = 3)
{
    if (is_hexa($color)) {
        $color = normalizeColor($color);

        // Extract the red, green, blue, and alpha components
        $color = ltrim($color, '#');
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
        $a = hexdec(substr($color, 6, 2)) / 255; // Normalize alpha to a value between 0 and 1
        $a = round($a, $alphaPrecision);

        return normalizeColor("$r,$g,$b,$a");
    } else {
        return false;
    }
}

/**
 * Convert RGB color to DEC color
 *
 * @param   string  $color  RGB color
 * @return  string          DEC color
 */
function rgb2dec($color)
{
    if (is_rgb($color)) {
        $color = normalizeColor($color);
        $color = rgb2hex($color);
        $color = ltrim($color, '#');
        $color = hexdec($color);

        return $color;
    } else {
        return false;
    }
}

/**
 * Convert RGB color into HEX color
 *
 * @param   string  $color  RGB color
 * @return  string          HEX color
 */
function rgb2hex($color)
{
    if (is_rgb($color)) {
        $color = normalizeColor($color);

        // Extract the RGB components
        $color = str_replace(array_map('strtolower', ['rgba', 'rgb', '(', ')', ' ']), '', strtolower($color));
        [$r, $g, $b] = explode(',', $color);

        // Convert each RGB component into a two-character HEX component
        $r = sprintf('%02x', $r);
        $g = sprintf('%02x', $g);
        $b = sprintf('%02x', $b);

        return normalizeColor("$r$g$b");
    } else {
        return false;
    }
}

/**
 * Convert RGB color into HEXA color (HEX + Alpha)
 *
 * @param   string  $color  RGB color
 * @return  string          HEXA color (HEX + Alpha)
 */
function rgb2hexa($color)
{
    if (is_rgb($color)) {
        $color = rgb2hex($color);
        $color = hex2hexa($color);

        return normalizeColor($color);
    } else {
        return false;
    }
}

/**
 * Convert RGB color into RGBA color (RGB + Alpha)
 *
 * @param   string  $color  RGB color
 * @return  string          RGBA color (RGB + Alpha)
 */
function rgb2rgba($color)
{
    if (is_rgb($color)) {
        $color = rgb2hexa($color);
        $color = hexa2rgba($color);

        return normalizeColor($color);
    } else {
        return false;
    }
}

/**
 * Convert RGBA color (RGB + Alpha) to DEC color
 *
 * @param   string  $foregroundColor    Foreground RGBA color (RGB + Alpha)
 * @param   string  $backgroundColor    Background RGB color (without alpha channel) the foreground color will blend onto
 * @return  string                      DEC color
 */
function rgba2dec($foregroundColor, $backgroundColor = 'RGB(255,255,255)')
{
    if (is_rgba($foregroundColor) && is_rgb($backgroundColor)) {
        $color = rgba2hex($foregroundColor, $backgroundColor);
        $color = ltrim($color, '#');
        $color = hexdec($color);

        return $color;
    } else {
        return false;
    }
}

/**
 * Convert RGBA color (RGB + Alpha) into HEX color
 *
 * @param   string  $foregroundColor    Foreground RGBA color (RGB + Alpha)
 * @param   string  $backgroundColor    Background RGB color (without alpha channel) the foreground color will blend onto
 * @return  string                      HEX color
 */
function rgba2hex($foregroundColor, $backgroundColor = 'RGB(255,255,255)')
{
    if (is_rgba($foregroundColor) && is_rgb($backgroundColor)) {
        $color = rgba2rgb($foregroundColor, $backgroundColor);
        $color = rgb2hex($color);

        return normalizeColor($color);
    } else {
        return false;
    }
}

/**
 * Convert RGBA color (RGB + Alpha) into HEXA color (HEX + Alpha)
 *
 * @param   string  $color  RGBA color (RGB + Alpha)
 * @return  string          HEXA color (HEX + Alpha)
 */
function rgba2hexa($color)
{
    if (is_rgba($color)) {
        $color = normalizeColor($color);

        // Extract the RGBA components
        $color = str_replace(array_map('strtolower', ['rgba', 'rgb', '(', ')', ' ']), '', strtolower($color));
        [$r, $g, $b, $a] = explode(',', $color);

        // Convert each RGBA component to a two-character HEXA component
        $r = dechex($r);
        $g = dechex($g);
        $b = dechex($b);
        $a = dechex($a * 255);

        return normalizeColor("$r$g$b$a");
    } else {
        return false;
    }
}

/**
 * Convert RGBA color (RGB + Alpha) into RGB color
 * Alpha channel both attenuates the background color and the foreground color
 * @link https://marcodiiga.github.io/rgba-to-rgb-conversion
 *
 * @param   string  $foregroundColor    Foreground RGBA color (RGB + Alpha)
 * @param   string  $backgroundColor    Background RGB color (without alpha channel) the foreground color will blend onto
 * @return  string                      RGB color
 */
function rgba2rgb($foregroundColor, $backgroundColor = 'RGB(255,255,255)')
{
    if (is_rgba($foregroundColor) && is_rgb($backgroundColor)) {

        $foregroundColor = normalizeColor($foregroundColor);
        $backgroundColor = normalizeColor($backgroundColor);

        // Extract channels using sscanf
        sscanf($foregroundColor, "RGBA(%d,%d,%d,%f)", $foregroundR, $foregroundG, $foregroundB, $foregroundA);
        sscanf($backgroundColor, "RGB(%d,%d,%d)", $backgroundR, $backgroundG, $backgroundB);

        // Calculate the resulting RGB components
        $r = round((1 - $foregroundA) * $backgroundR + $foregroundA * $foregroundR);
        $g = round((1 - $foregroundA) * $backgroundG + $foregroundA * $foregroundG);
        $b = round((1 - $foregroundA) * $backgroundB + $foregroundA * $foregroundB);

        return normalizeColor("$r,$g,$b");
    } else {
        return false;
    }
}
