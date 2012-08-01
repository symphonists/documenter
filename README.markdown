# Documenter

A Symphony extension enabling addition of documentation to any page in the backend, including user-defined section indexes and entry editors.

## Usage

### Managing Documentation

Documentation can be managed at `System > Documentation`. For each documentation item, specify:

1. The item's **Title**, used as the heading for the documentation drawer.
2. The item's **Content**.
3. The back-end **Pages** on which you'd like this item to appear.

### Special styling

1. **Collapsible blocks:** Documenter will automatically wrap the content between two `h3` headlines into a collapsible block. The headline is used to toggle the state of the following block. All blocks are closed by default but Documenter stores the current opening state of all content blocks in the browser's `localStorage` and restores it on page refresh.
2. **Notes:** You can add notes that are highlighted and never collapsed by wrapping it in a `div.note`, e. g.:

        <div class="note"><strong>Note:</strong> I'm just here to look important.</div>

## Acknowledgement

This extension is not a work of a single person, a lot of people tested it and [contributed to it](https://github.com/symphonists/documenter/graphs/contributors).
