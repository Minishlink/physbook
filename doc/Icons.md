# Generating icons #
Phy'sbook uses [Gulpicon](https://github.com/filamentgroup/gulpicon) for generating SVG icons with PNG fallback.

## Creating SVGs ##
SVG 1.1 with or without artworks. 

## Installation ##
Copy from Google Drive the SVGs to the directory `app/Resources/public/icons/svg`.
Then `gulp icons`.

## Usage ##
Let `iconName` be the name of the icon (eg. "brand" for "brand.svg").

### Basic ###
Append the class `phys-iconName` to a `span` or `div`. The element will have its CSS property `display` set to `inline-block` by default.

### Colors ###
Let `colorName` be the name of a color. It can either be a classic HTML color name ("white", "red") or a config defined name in gulpfile.js ("rouge", "gris").

Monochromic icons can be displayed with multiple colors. To do so, rename `iconName.svg` with `iconName.colors-colorName1-colorName2.svg`.

Then instead of the class `phys-iconName`, append `phys-iconName-colorName` to your element.

### Advanced icons ###
You can customize the icon with CSS if you append the attribute `data-grunticon-embed` to the icon element.
Thus, you can create animations or have a multicolor icon.
