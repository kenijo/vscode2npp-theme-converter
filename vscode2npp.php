<?php

// TODO: generate matching Markdown color file
// TODO: update index.htm to handle Markdown
// TODO: Take external mapping file as an imput
// TODO: Add textbox to input theme name and add it to the XML comments
// TODO: generate config.xml line for colors
// TODO: colors are saved as decimal, RGB is inverted. Check https://www.checkyourmath.com/convert/color/decimal_rgb.php
// TODO: better handle markup.bold / makrup.italic / markup.underline
// TODO: download the stylers.model.xml directly form NotePad++ GitHub to always get the latest version
// TODO: finish mapping file for Notepad++ searchResult and GlobalStyles
// TODO: include the dark theme conversion values

// Include libraries
require_once __DIR__ . '/library/func.color.php';

// Check to see if the script is running in a web context
$is_web = http_response_code() !== FALSE;
if ($is_web) {
    if (isset($_POST['vscode_json'])) {
        $VSCODE_THEME_JSON = $_POST['vscode_json'];
    } else {
        echo 'This script is not meant to be called directly from the web.';
        exit;
    }
} else {
    if (isset($argv[1])) {
        $VSCODE_THEME_JSON = $argv[1];
    } else {
        echo 'Provide a VSCode Theme JSON file as an argument.' . PHP_EOL;
        echo 'In VS Code: CTRL+P > Developer: Generate Color Theme From Current Settings';
        exit;
    }
}

$MAPPING_FILE = 'mapping.json';
$NPP_STYLERS_MODEL_FILE = downloadStylers();

// Load the JSON and XML files
$mapping = loadJson($MAPPING_FILE, 'DELETE');
$vscodeTheme = loadJson($VSCODE_THEME_JSON, 'TOGGLE');
$nppTheme = loadXml($NPP_STYLERS_MODEL_FILE);

// Match keys and assign colors
try {
    // Preprocess tokenColors for faster lookups
    $vscodeColors = $vscodeTheme['colors'];
    $vscodeTokenColors = $vscodeTheme['tokenColors'] ?? [];

    $tokenMap = [];
    foreach ($vscodeTokenColors as $token) {
        $scopes = is_array($token['scope']) ? $token['scope'] : [$token['scope']];
        foreach ($scopes as $scope) {
            $tokenMap[$scope] = $token['settings'];
        }
    }

    // Retrieve settings from VSCode Theme and assign them to the mapping file
    foreach ($mapping as $section => $sectionContent) {
        // Loop through the mapping JSON
        foreach ($sectionContent as $nppKey => $nppSettings) {
            foreach ($nppSettings as $nppSetting => $vscodeKey) {
                // Initialize the setting value to an empty string
                $vscodeSetting = '';

                // Check if the VSCode key exists in the 'colors' section of the VSCode Theme JSON
                if (isset($vscodeColors[$vscodeKey])) {
                    $vscodeSetting = $vscodeColors[$vscodeKey];
                }
                // Check if the VSCode key exists in the tokenMap (preprocessed tokenColors)
                if (isset($tokenMap[$vscodeKey])) {
                    switch ($nppSetting) {
                        case 'bgColor':
                            $vscodeSetting = $tokenMap[$vscodeKey]['background'];
                            break;
                        case 'fgColor':
                            $vscodeSetting = $tokenMap[$vscodeKey]['foreground'];
                            break;
                        case 'fontStyle':
                            $vscodeSetting = mapFontStyle($tokenMap[$vscodeKey]['fontStyle']);
                            break;
                    }
                }
                // Set setting in mapping
                $mapping[$section][$nppKey][$nppSetting] = $vscodeSetting;
            }
        }
    }

    $defaultSetting = [
        'bgColor' => $mapping['WidgetStyle']['Default Style']['bgColor'],
        'fgColor' => $mapping['WidgetStyle']['Default Style']['fgColor'],
        'fontName' => 'Courier New',
        'fontStyle' => 0,
        'fontSize' => 11
    ];

    $comment = PHP_EOL;
    $comment .= "\t\tNotepad++ Theme converted from VSCode Theme using VSCODE2NPP\n";
    $comment .= "\t\tAvailable at <URL>\n";
    $comment .= "\n";
    $comment .= "\t\tTo complement the Notepad++ Theme, we suggest the following Dark Mode Tones:\n";

    // Loop through the Dark Mode Tones and add them as comment to the Notepad++ Theme for reference
    foreach ($mapping['DarkModeTones'] as $nppKey => $nppSettings) {
        foreach ($nppSettings as $nppSetting => $vscodeSetting) {
            $hex = strtoupper(hexa2hex($defaultSetting['bgColor'], $vscodeSetting));
            $rgb = strtoupper(hex2rgb($hex));
            $comment .= "\t\t\t\t$nppKey: $hex / $rgb\n";
            $darkMode[$nppKey] = [
                'hex' => $hex,
                'rgb' => $rgb
            ];
        }
    }

    // Create a XML comment
    $xmlCommment = $nppTheme->createComment($comment);

    // Insert the XML comment at the top of the document
    if ($nppTheme->firstChild) {
        $nppTheme->insertBefore($xmlCommment, $nppTheme->firstChild);
    } else {
        $nppTheme->appendChild($xmlCommment);
    }

    foreach (['WidgetStyle', 'WordsStyle'] as $section) {
        initializeElements($defaultSetting, $nppTheme, $section);

        foreach ($mapping[$section] as $nppKey => $nppSettings) {
            // Use XPath to find the specific element
            $xpath = new DOMXPath($nppTheme);
            $elements = $xpath->query("//" . $section . "[@name='$nppKey']");

            foreach ($elements as $element) {
                foreach ($nppSettings as $nppSetting => $vscodeSetting) {
                    // Set setting in XML
                    if (substr($vscodeSetting, 0, 1) === "#") {
                        $vscodeSetting = hexa2hex($defaultSetting['bgColor'], $vscodeSetting);
                        $vscodeSetting = ltrim($vscodeSetting, '#');
                    }
                    $element->setAttribute($nppSetting, $vscodeSetting);
                }
            }
        }
    }

    // Create a cache folder and build the filename string
    //$folder = 'cache';
    //if (!file_exists($folder)) {
    //    mkdir($folder, 0777, true);
    //}
    //$filename = 'npp_theme.xml';

    // Save the new Theme on the server
    //$nppTheme->save('cache' . DIRECTORY_SEPARATOR . $filename);

    // Output Notepad++ DarkMode, Theme, and Markdown
    $data = [
        'darkMode' => $darkMode,
        'nppTheme' => $nppTheme->saveXML(),
        'markdownTheme' => ''
    ];
    echo json_encode($data);
} catch (Exception $e) {
    echo $e->getMessage();
}

/**
 * Download stylers.model.xml from GitHub repository
 *
 * @return string The content of the downloaded stylers.model.xml or the path to a stylers.model.xml
 */
function downloadStylers()
{
    $filename = 'stylers.model.xml';
    $url = 'https://raw.githubusercontent.com/notepad-plus-plus/notepad-plus-plus/refs/heads/master/PowerEditor/src/' . $filename;

    // Check if the file exists and is less than 1 week old
    if (file_exists($filename) && filemtime($filename) > strtotime('-1 week')) {
        return $filename;
    }

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Execute the cURL session and retrieve the content
    $xmlContent = curl_exec($ch);

    // Check for cURL errors
    if ($xmlContent === false) {
        echo 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    // Close the cURL session
    curl_close($ch);

    // Save the content to the file
    if (file_put_contents($filename, $xmlContent) !== false) {
        return $xmlContent;
    } else {
        echo 'Error writing file.';
        return false;
    }
}

/**
 * Function to initialize the settings attributes with empty string
 *
 * @param array $defaultSetting The default settings
 * @param   DOMDocument $xml            The XML document
 * @param   string      $xmlElementName The name of the XML element
 */
function initializeElements($defaultSetting, $xml, $xmlElementName)
{
    $prioritizedTags = ['Global override', 'Default Style'];

    $defaultSetting['bgColor'] = ltrim($defaultSetting['bgColor'], '#');
    $defaultSetting['fgColor'] = ltrim($defaultSetting['fgColor'], '#');

    // Find all elements with name attribute equal to $nppKey
    $elements = $xml->getElementsByTagName($xmlElementName);
    foreach ($elements as $element) {
        foreach (['bgColor', 'fgColor', 'fontName', 'fontStyle', 'fontSize'] as $attribute) {
            if ($element->hasAttribute($attribute)) {
                switch ($attribute) {
                    case 'bgColor':
                        $element->setAttribute($attribute, $defaultSetting['bgColor']);
                        break;
                    case 'fgColor':
                        $element->setAttribute($attribute, $defaultSetting['fgColor']);
                        break;
                    case 'fontStyle':
                        $element->setAttribute($attribute, $defaultSetting['fontStyle']);
                        break;
                    default:
                        $element->setAttribute($attribute, '');
                        break;
                }
            }
        }

        if (in_array($element->getAttribute('name'), $prioritizedTags)) {
            $element->setAttribute('fontName', $defaultSetting['fontName']);
            $element->setAttribute('fontSize', $defaultSetting['fontSize']);
        }
    }
}

/**
 * Function to load a JSON
 *
 * @param   string  $json           JSON file path or JSON string
 * @param   string  $cleanComments  How to handle comments: 'DELETE' or 'TOGGLE'
 * @return  mixed                   Returns the value encoded in json as an appropriate PHP type
 */
function loadJson($json, $cleanComments = null)
{
    // Read the JSON from the file if a filename is provided, other read it from the string
    $jsonString = is_file($json) && file_exists($json) ? file_get_contents($json) : $json;

    if ($jsonString === false) {
        throw new Exception('Error: Failed to read JSON file');
    }

    // Clean comments based on the specified method
    switch ($cleanComments) {
        case 'DELETE':
            $jsonString = preg_replace('/\/\/.*$/', '', $jsonString);
            break;
        case 'TOGGLE':
            $jsonString = str_replace('//', '', $jsonString);
            break;
    }

    // Decode the JSON string
    $jsonData = json_decode($jsonString, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error: Failed to decode JSON');
    }
    return $jsonData;
}

/**
 * Function to load a sorted XML
 *
 * @param   string  $xml    XML file path
 * @return  mixed           Returns an object of class SimpleXMLElement
 */
function loadXml($xml)
{
    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;

    try {
        // Read the XML from the file if a filename is provided, other read it from the string
        if (is_file($xml) && file_exists($xml)) {
            $dom->load($xml);
        } else {
            $dom->loadXML($xml);
        }
        removeComments($dom);
        sortElements($dom);
        return $dom;
    } catch (Exception $e) {
        echo 'Error: Failed to load XML';
    }
}

/**
 * Function to map font styles from VSCode to Notepad++
 *
 * @param   string  $fontStyle  The font style from VSCode
 * @return  string              The corresponding font style for Notepad++
 */
function mapFontStyle($fontStyle)
{
    $styleMapping = [
        'bold' => '1',
        'italic' => '2',
        'underline' => '4',
        'bold italic' => '3',
        'italic bold' => '3',
        'bold underline' => '5',
        'underline bold' => '5',
        'italic underline' => '6',
        'underline italic' => '6',
        'bold italic underline' => '7',
        'bold underline italic' => '7',
        'italic bold underline' => '7',
        'italic underline bold' => '7',
        'underline bold italic' => '7',
        'underline italic bold' => '7'
    ];
    return $styleMapping[$fontStyle] ?? '';
}

/**
 * Function to remove comments from the XML
 *
 * @param   DOMDocument $dom    The XML object
 */
function removeComments(DOMDocument $dom)
{
    $xpath = new DOMXPath($dom);
    $comments = $xpath->query('//comment()');

    foreach ($comments as $comment) {
        $comment->parentNode->removeChild($comment);
    }
}

/**
 * Function that recursively sorts the XML content by tag names and by the 'name' attribute
 *
 * @param   DOMDocument $xml    XML file path
 */
function sortElements($xml)
{
    if ($xml->hasChildNodes()) {
        $children = [];
        foreach ($xml->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $children[] = $child;
            }
        }

        usort($children, function ($a, $b) {
            $prioritizedTags = ['Global override', 'Default Style'];

            $aName = $a->hasAttribute('name') ? $a->getAttribute('name') : '';
            $bName = $b->hasAttribute('name') ? $b->getAttribute('name') : '';

            // Check if both elements are in the prioritizedTags array
            if (in_array($aName, $prioritizedTags) && in_array($bName, $prioritizedTags)) {
                return array_search($aName, $prioritizedTags) - array_search($bName, $prioritizedTags);
            }

            // If only one is in the prioritizedTags array, prioritize it
            if (in_array($aName, $prioritizedTags)) {
                return -1;
            }
            if (in_array($bName, $prioritizedTags)) {
                return 1;
            }

            // If neither is in the prioritizedTags array, fall back to tag name comparison
            $tagCompare = strcmp($a->tagName, $b->tagName);
            if ($tagCompare !== 0) {
                return $tagCompare;
            }

            // If the tag names are the same, compare by name attribute
            if ($a->hasAttribute('name') && $b->hasAttribute('name')) {
                return strcmp($a->getAttribute('name'), $b->getAttribute('name'));
            }

            return 0;
        });

        foreach ($children as $child) {
            $xml->appendChild($child);
            sortElements($child);
        }
    }
}
