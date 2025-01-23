<?php

// Include libraries
require_once __DIR__ . '/library/func.color.php';

// Initialize variables
$backgroundColorFormat = $_POST['backgroundColorFormat'] ?? 'HEX';
$backgroundColorInput = null;
$backgroundColorPalette = '#FFFFFF';
$backgroundError = null;

$foregroundColorFormat = $_POST['foregroundColorFormat'] ?? 'HEX';
$foregroundColorInput = null;
$foregroundColorInputPalette = null;
$foregroundError = null;

$colorDEC = '&nbsp;';
$colorHEX = '&nbsp;';
$colorHEXA = '&nbsp;';
$colorRGB = '&nbsp;';
$colorRGBA = '&nbsp;';

// Process foreground color input
if (isset($_POST['foregroundColorInput']) && $_POST['foregroundColorInput'] != '') {
    $foregroundColorInput = $_POST['foregroundColorInput'];

    if ($foregroundColorFormat === 'DEC' && is_dec($foregroundColorInput)) {
        $colorDEC = $foregroundColorInput;
        $colorHEX = dec2hex($foregroundColorInput);
        $colorHEXA = dec2hexa($foregroundColorInput);
        $colorRGB = dec2rgb($foregroundColorInput);
        $colorRGBA = dec2rgba($foregroundColorInput);
        $foregroundColorInputPalette = $colorHEX;
    } elseif ($foregroundColorFormat === 'HEX' && is_hex($foregroundColorInput)) {
        $colorDEC = hex2dec($foregroundColorInput);
        $colorHEX = normalizeColor($foregroundColorInput);
        $colorHEXA = hex2hexa($foregroundColorInput);
        $colorRGB = hex2rgb($foregroundColorInput);
        $colorRGBA = hex2rgba($foregroundColorInput);
        $foregroundColorInput = normalizeColor($foregroundColorInput);
        $foregroundColorInputPalette = normalizeColor($foregroundColorInput);
    } elseif ($foregroundColorFormat === 'RGB' && is_rgb($foregroundColorInput)) {
        $colorDEC = rgb2dec($foregroundColorInput);
        $colorHEX = rgb2hex($foregroundColorInput);
        $colorHEXA = rgb2hexa($foregroundColorInput);
        $colorRGB = normalizeColor($foregroundColorInput);
        $colorRGBA = rgb2rgba($foregroundColorInput);
        $foregroundColorInput = normalizeColor($foregroundColorInput);
        $foregroundColorInputPalette = rgb2hex($foregroundColorInput);
    } elseif (($foregroundColorFormat === 'HEXA' && is_hexa($foregroundColorInput)) || ($foregroundColorFormat === 'RGBA' && is_rgba($foregroundColorInput))) {
        // Process background color input
        if (isset($_POST['backgroundColorInput']) && $_POST['backgroundColorInput'] != '') {
            $backgroundColorInput = $_POST['backgroundColorInput'];

            if ($backgroundColorFormat === 'DEC') {
                if (is_dec($backgroundColorInput)) {
                    $backgroundColorPalette = dec2hex($backgroundColorInput);
                } else {
                    $backgroundError = "Invalid $backgroundColorFormat background color format (using white as default)";
                    $backgroundColorInput = hex2dec($backgroundColorPalette);
                }
            } elseif ($backgroundColorFormat === 'HEX') {
                if (is_hex($backgroundColorInput)) {
                    $backgroundColorInput = normalizeColor($backgroundColorInput);
                    $backgroundColorPalette = normalizeColor($backgroundColorInput);
                } else {
                    $backgroundError = "Invalid $backgroundColorFormat background color format (using white as default)";
                    $backgroundColorInput = $backgroundColorPalette;
                }
            } elseif ($backgroundColorFormat === 'RGB') {
                if (is_rgb($backgroundColorInput)) {
                    $backgroundColorInput = normalizeColor($backgroundColorInput);
                    $backgroundColorPalette = rgb2hex($backgroundColorInput);
                } else {
                    $backgroundError = "Invalid $backgroundColorFormat background color format (using white as default)";
                    $backgroundColorInput = hex2rgb($backgroundColorPalette);
                }
            }
        } else {
            $backgroundError = "No background color provided (using white as default)";

            if ($foregroundColorFormat === 'HEXA') {
                $backgroundColorFormat = 'HEX';
                $backgroundColorInput = $backgroundColorPalette;
            } elseif ($foregroundColorFormat === 'RGBA') {
                $backgroundColorFormat = 'RGB';
                $backgroundColorInput = hex2rgb($backgroundColorPalette);
            }
        }

        if ($foregroundColorFormat === 'HEXA') {
            $backgroundColorHEX = $backgroundColorPalette;
            $colorDEC = hexa2dec($foregroundColorInput, $backgroundColorHEX);
            $colorHEX = hexa2hex($foregroundColorInput, $backgroundColorHEX);
            $colorHEXA = normalizeColor($foregroundColorInput);
            $colorRGB = hexa2rgb($foregroundColorInput, $backgroundColorHEX);
            $colorRGBA = hexa2rgba($foregroundColorInput);
            $foregroundColorInput = normalizeColor($foregroundColorInput);
            $foregroundColorInputPalette = hexa2hex($foregroundColorInput);
        } elseif ($foregroundColorFormat === 'RGBA') {
            $backgroundColorHEX = hex2rgb($backgroundColorPalette);
            $colorDEC = rgba2dec($foregroundColorInput, $backgroundColorHEX);
            $colorHEX = rgba2hex($foregroundColorInput, $backgroundColorHEX);
            $colorHEXA = rgba2hexa($foregroundColorInput);
            $colorRGB = rgba2rgb($foregroundColorInput, $backgroundColorHEX);
            $colorRGBA = normalizeColor($foregroundColorInput);
            $foregroundColorInput = normalizeColor($foregroundColorInput);
            $foregroundColorInputPalette = rgba2hex($foregroundColorInput);
        }
    } else {
        $foregroundError = "Invalid $foregroundColorFormat foreground color format";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="color-scheme" content="light dark" />
        <title>Color Converter</title>
        <meta name="description" content="A VSCode to Notepad++ theme converter." />

        <!-- PicoCSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" />
    </head>

    <body>
        <!-- Header -->
        <header class="container">
            <div class="grid">
                <span class="grid">
                    <h2>Color Converter</h2>
                    <span style="text-align: right;">
                        <a class="AppHeader-logo ml-2" href="https://github.com/" data-hotkey="g d"
                            aria-label="Homepage " data-turbo="false" data-analytics-event="{&quot;category&quot;:&quot;Header&quot;,&quot;action&quot;:&quot;go to
                        dashboard&quot;,&quot;label&quot;:&quot;icon:logo&quot;}">
                            <svg height="32" aria-hidden="true" viewBox="0 0 24 24" version="1.1" width="32"
                                data-view-component="true"
                                class="octicon octicon-mark-github v-align-middle color-fg-default">
                                <path
                                    d="M12.5.75C6.146.75 1 5.896 1 12.25c0 5.089 3.292 9.387 7.863 10.91.575.101.79-.244.79-.546 0-.273-.014-1.178-.014-2.142-2.889.532-3.636-.704-3.866-1.35-.13-.331-.69-1.352-1.18-1.625-.402-.216-.977-.748-.014-.762.906-.014 1.553.834 1.769 1.179 1.035 1.74 2.688 1.25 3.349.948.1-.747.402-1.25.733-1.538-2.559-.287-5.232-1.279-5.232-5.678 0-1.25.445-2.285 1.178-3.09-.115-.288-.517-1.467.115-3.048 0 0 .963-.302 3.163 1.179.92-.259 1.897-.388 2.875-.388.977 0 1.955.13 2.875.388 2.2-1.495 3.162-1.179 3.162-1.179.633 1.581.23 2.76.115 3.048.733.805 1.179 1.825 1.179 3.09 0 4.413-2.688 5.39-5.247 5.678.417.36.776 1.05.776 2.128 0 1.538-.014 2.774-.014 3.162 0 .302.216.662.79.547C20.709 21.637 24 17.324 24 12.25 24 5.896 18.854.75 12.5.75Z">
                                </path>
                            </svg>
                        </a>
                    </span>
                </span>
            </div>
        </header>
        <!-- ./ Header -->

        <!-- Main -->
        <main class="container">
            <section id="convertColorsSection">
                <article id="convertColorsArticle">
                    <h5>Source Color (Decimal, HEX, HEX Alpha, RGB, RGB Alpha)</h5>
                    <?php
                    if ($foregroundError != null) {
                        echo "<mark>$foregroundError</mark><br /><br />";
                    }
                    if ($backgroundError != null) {
                        echo "<mark>$backgroundError</mark><br /><br />";
                    }
                    ?>
                    <form method="post">
                        <span class="grid">
                            <fieldset id="foregroundGroup" role="group">
                                <select id="foregroundColorFormat" name="foregroundColorFormat">
                                    <?php
                                    $foregroundColorFormatArray = [
                                        'DEC' => 'Decimal',
                                        'HEX' => 'HEX',
                                        'HEXA' => 'HEX Alpha',
                                        'RGB' => 'RGB',
                                        'RGBA' => 'RGB Alpha'
                                    ];
                                    foreach ($foregroundColorFormatArray as $key => $value) { ?>
                                        <option value="<?= $key ?>" <?= ($key == $foregroundColorFormat) ? ' selected="selected"' : ''; ?>>
                                            <?= $value ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <input id="foregroundColorInput" name="foregroundColorInput"
                                    placeholder="Foreground Color" value="<?= $foregroundColorInput ?>" />
                                <?php if ($foregroundColorInputPalette != null) { ?>
                                    <input type="text" id="foregroundColorInputPalette" disabled
                                        style="opacity: 100; background-color: <?= $foregroundColorInputPalette ?>;">
                                <?php } ?>
                            </fieldset>
                            <fieldset id="backgroundGroup" role="group" <?php
                            $foregroundColorFormatArray = [
                                'HEXA',
                                'RGBA'
                            ];
                            if (!in_array($foregroundColorFormat, $foregroundColorFormatArray)) {
                                ?> style="visibility: hidden;" <?php
                            }
                            ?>>
                                <select id="backgroundColorFormat" name="backgroundColorFormat">
                                    <?php
                                    $backgroundColorFormatArray = [
                                        'DEC' => 'Decimal',
                                        'HEX' => 'HEX',
                                        'RGB' => 'RGB'
                                    ];
                                    foreach ($backgroundColorFormatArray as $key => $value) { ?>
                                        <option value="<?= $key ?>" <?= ($key == $backgroundColorFormat) ? ' selected="selected"' : ''; ?>>
                                            <?= $value ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <input id="backgroundColorInput" name="backgroundColorInput"
                                    placeholder="Background Color" value="<?= $backgroundColorInput ?>" />
                                <?php if ($backgroundColorPalette != null) { ?>
                                    <input type="text" id="backgroundColorPalette" disabled
                                        style="opacity: 100; background-color: <?= $backgroundColorPalette ?>;">
                                <?php } ?>
                            </fieldset>
                        </span>
                        <input type="submit" value="Convert">
                    </form>
                </article>
            </section>

            <section id="convertedColorsSection">
                <article id="convertedColorsArticle">
                    <h5>Converted Color</h5>
                    <span class="grid" style="text-align: center;">
                        <span>
                            <input type="text" id="colorPalette" disabled
                                style="height: 250px; opacity: 100; background-color: <?= $colorHEX ?>;">
                        </span>
                        <span>
                            <label id="decimalColorLabel">Decimal</label>
                            <button id="decimalColorButton" class="outline secondary"
                                style="opacity: 100; width: 100%;"><?= $colorDEC ?></button>
                        </span>
                        <span>
                            <label id="hexadecimalColorLabel">HEX</label>
                            <button id="colorHEXButton" class="outline secondary"
                                style="opacity: 100; width: 100%;"><?= $colorHEX ?></button>
                            <label id="hexadecimalAlphaColorLabel"><br />HEX Alpha</label>
                            <button id="colorHEXAButton" class="outline secondary"
                                style="opacity: 100; width: 100%;"><?= $colorHEXA ?></button>
                        </span>
                        <span>
                            <label id="colorRGBLabel">RGB</label>
                            <button id="colorRGBButton" class="outline secondary"
                                style="opacity: 100; width: 100%;"><?= $colorRGB ?></button>
                            <label id="rgbAlphaColorLabel"><br />RGB Alpha</label>
                            <button id="colorRGBAButton" class="outline secondary"
                                style="opacity: 100; width: 100%;"><?= $colorRGBA ?></button>
                        </span>
                </article>
            </section>
        </main>
        <!-- ./ Main -->

        <!-- Footer -->
        <footer class="container">
            <small>Built with <a href="https://picocss.com">Pico</a> • 2024</small>
        </footer>
        <!-- ./ Footer -->

        <!-- jQuery -->
        <script src="https://cdn.jsdelivr.net/npm/jquery@3/dist/jquery.min.js"></script>

        <script>
            document.getElementById("decimalColorButton").addEventListener("click", copyToClipboard);
            document.getElementById("colorHEXButton").addEventListener("click", copyToClipboard);
            document.getElementById("colorHEXAButton").addEventListener("click", copyToClipboard);
            document.getElementById("colorRGBButton").addEventListener("click", copyToClipboard);
            document.getElementById("colorRGBAButton").addEventListener("click", copyToClipboard);

            document.getElementById("foregroundColorFormat").addEventListener("change", updateBackgroundGroupVisibility);

            function copyToClipboard() {
                navigator.clipboard.writeText(this.innerText);
            }

            function updateBackgroundGroupVisibility() {
                if (this.value == 'HEXA' || this.value == 'RGBA') {
                    document.getElementById("backgroundGroup").style.visibility = "visible";
                } else {
                    document.getElementById("backgroundGroup").style.visibility = "hidden";
                }
            }
        </script>

    </body>

</html>
