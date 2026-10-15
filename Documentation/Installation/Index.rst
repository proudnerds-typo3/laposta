..  _installation:

============
Installation
============

Target group: **Administrators / Integrators**

#.  Install the extension: ``composer require proudnerds/laposta``.
#.  Coming from 12.x: run the upgrade wizard *Migrate "Proudnerds Laposta" plugins to
    content elements* in the Install Tool. It converts the existing content elements
    from ``CType=list`` to their own content types and updates the backend user group
    permissions.
#.  Include the TypoScript template.
#.  Add the extension to the relevant backend user groups.
#.  Add the Laposta ``apiKey`` in the constants settings.
#.  Create "Laposta newsletter list" records.
#.  Insert the subscribe and unsubscribe plugins on your pages.
#.  Select the "Laposta newsletter list" records on each plugin.
#.  Set the record storage page to the page that holds the list records.
#.  On laposta.nl you can add custom fields per newsletter. The template ships with
    email (required), voornaam and achternaam.
#.  Add or change custom fields in the template by prefixing the field name with
    ``customField.``.
#.  Coming from 14.3: run the database compare to create the cache table
    ``cache_laposta_altcha``.
#.  Optional: enable the ALTCHA spam check and the rate limit per IP address, see
    :ref:`spam-protection`.
#.  A log is kept in :file:`var/log/Laposta.log`.

..  figure:: ../Images/Laposta.png
    :class: with-shadow
    :alt: The Laposta newsletter subscription form in the TYPO3 frontend

    The Laposta newsletter subscription form in the TYPO3 frontend.
