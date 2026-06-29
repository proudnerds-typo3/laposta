# Laposta

TYPO3 extension to subscribe to and unsubscribe from one or more
[Laposta](https://laposta.nl/) newsletters from the TYPO3 frontend. Laposta is a
Dutch newsletter service.

## Features

- Subscribe and unsubscribe plugins for the frontend
- Multiple newsletter lists, with optional double opt-in per list
- Custom fields per newsletter (configured in Laposta)
- Accessible forms and notifications (WCAG 2.2 AA): grouped messages with a clear
  structure, `fieldset`/`legend`, programmatic labels and focus handling
- Fully translatable; English and Dutch are included

## Requirements

- TYPO3 12.4 LTS
- PHP 8.1 or higher
- A Laposta account and API key

## Installation

```bash
composer require proudnerds/laposta
```

Then include the static TypoScript template ("Laposta") on your site.

## Configuration

1. Set your Laposta API key in the constants
   (`plugin.tx_laposta.settings.apiKey`).
2. Create "Laposta newsletter list" records — one per newsletter, each holding
   the Laposta list id.
3. Add the *Laposta subscribe* and/or *Laposta unsubscribe* plugin to a page.
4. Select the newsletter list records on the plugin and set the plugin's record
   storage page to where those records live.

Custom fields can be added in the Laposta administration and exposed in the
template by prefixing the field name with `customField.`.

## Documentation

Full documentation is available in the [`Documentation/`](Documentation/) folder
and rendered on [docs.typo3.org](https://docs.typo3.org/p/proudnerds/laposta/12.4/en-us/).

## License

GPL-2.0-or-later. Development sponsored by Gemeente Gooise Meren.
