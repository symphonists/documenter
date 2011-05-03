# Documenter

- Version: 1.0RC2
- Author: craig zheng
- Build Date: 3rd May 2011
- Requirements: Symphony 2.2

A Symphony extension enabling addition of documentation to any page in the back end, including user-defined section indexes and entry editors.

## Installation

1. Place the 'documenter' folder in your Symphony 'extensions' directory.
2. Go to System > Extensions, select "Documenter", choose "Enable" from the with-selected menu, then click Apply.
3. _optional_ Go to System > Preferences and select the text formatter to use for your documentation items.

## Usage

### Managing Documentation

Documentation can be managed at `System > Documentation`. For each documentation item, specify:

1. The item's **Title**, used as a heading for the documentation box.
2. The item's **Content**.
3. The back-end **Pages** on which you'd like this item to appear.

## Changelog

- **1.0RC2** Interface improvements from Nils and Johanna Hörrmann
- **1.0RC1** Update for Symphony 2.2, some CSS additions from froschdesign
- **0.9.9** UI overhaul
- **0.9.8** Johanna's style improvements
- **0.9.7** Fix conflict with Symphony's duplicator.js
- **0.9.6** Fix page height issue; fix text validation; remove duplicate checking
- **0.9.5** Fix bugs introduced by 0.9.3 and 0.9.4
- **0.9.4** Fixed field error issues.
- **0.9.3** Brendo's updates, eKoeS's style fix.
- **0.9.2** Made button text configurable, along with a few minor tweaks.
- **0.9.1** Updated for compatibility with version of Symphony earlier than 2.0.7
- **0.9** Initial release

## Credits

Documenter has benefitted from contributions by [Nils Hörrmann](http://nilshoerrmann.de/), [Johanna Hörrmann](http://johannahoerrmann.de/), [froschdesign](https://github.com/froschdesign), and uses Ben Alman's [jQuery resize event](http://benalman.com/projects/jquery-resize-plugin/).
