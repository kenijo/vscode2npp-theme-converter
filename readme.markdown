# VSCode2NPP Theme Converter

Generates Notepad++ Theme from VSCode Theme (XML Theme, Markdown Theme, and Dark Mode)

## How To

Open VSCode and select your theme of choice.

Run the following command to generate a color theme: CTRL+P >> Developer: Generate Color Theme From Current Settings

Paste the color theme generated in the text box VSCode Theme (JSON).

Click convert.

Copy Notepad++ Theme (XML) and save it into the Notepad++ themes folder.

You can use the matching dark mode tones to further improve the theme integration.

# Color Converter

Convert color to and from the following format:

* Decimal (DEC)
* Hexadecimal (HEX)
* Hexadecimal with Alpha channel (HEXA)
* RGB
* RGBA (RGB with Alpha channel)

Notes: Alpha channel is computed by providing a background color to blend the foreground color onto. (The default background color used is white)

## Requirements

* PHP 7.2+

## Credits - This project relies on the following dependancies

* [PicoCSS](https://picocss.com/): Minimal CSS Framework for Semantic HTML [(GitHub)](https://github.com/picocss/pico)
