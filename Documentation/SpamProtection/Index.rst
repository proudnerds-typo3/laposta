..  _spam-protection:

===============
Spam protection
===============

Target group: **Integrators**

Both forms carry a honey pot field. Bots that load the form in a real browser and
leave that field empty get past it; they are typically used for *subscription
bombing*, where a victim's address is subscribed to as many newsletters as possible.
For those, the extension can add an `ALTCHA <https://altcha.org>`__ check and limit
the number of submissions per IP address.

ALTCHA
======

ALTCHA is a self-hosted proof-of-work check. When a visitor starts filling in the
form, the browser solves a small challenge in the background (about 0.1 seconds)
and "I'm not a robot" is ticked automatically. There is no puzzle, no cookie and no
external service: challenges are created and verified on your own server.

#.  Make sure the Composer package ``altcha-org/altcha`` is installed (it is a
    dependency of this extension; installations without Composer cannot use ALTCHA).
#.  Set the constant ``plugin.tx_laposta.settings.altcha.enable = 1``.
#.  Run ``vendor/bin/typo3 database:updateschema`` (or the database compare in the
    Install Tool) to create the cache table ``cache_laposta_altcha``.
#.  Check that ``/laposta/altcha-challenge`` returns JSON and is not cached by a
    reverse proxy (the response carries ``Cache-Control: no-store``).
#.  If your site package overrides :file:`Subscribe.html` or :file:`Unsubscribe.html`,
    add ``<f:render partial="Altcha" />`` inside the form, before the submit button,
    within ``<f:if condition="{settings.altcha.enable}">``. Without it the widget is
    missing and every submission is refused.

With ALTCHA enabled, a submission without a valid, unused solution is refused with
a notification, and logged when logging is enabled. Each solved challenge is
accepted once.

The used challenges are kept in the cache ``laposta_altcha``. With more than one
web server, all servers must share that cache. The default database backend does;
to use another shared backend such as Redis, define
``$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['laposta_altcha']``
in your own configuration.

The widget follows the language of the page (``<html lang>``) and of the browser.
English is built in and the Dutch translation is included; other languages fall back
to English.

Settings in the extension configuration:

``altchaCost``
    PBKDF2 iterations per attempt. Higher is harder for bots and slower for
    visitors. Default ``10000``.

``altchaLifetime``
    Seconds a challenge stays valid after the widget fetched it. Default ``1800``.

Content Security Policy
-----------------------

The widget script (``Resources/Public/JavaScript/Altcha/altcha.min.js``) adds its
styles inline and runs its calculations in ``blob:`` web workers. With a Content
Security Policy, allow ``style-src 'unsafe-inline'`` and ``worker-src blob:``.

Styling
-------

The extension ships no styles of its own. The ``altcha-widget`` element is inline
without margins, so give it ``display: block`` and a bottom margin to keep it clear
of the submit button.

The widget declares its defaults as CSS custom properties on ``:root``, for example
``--altcha-color-primary``, ``--altcha-border-color`` and ``--altcha-checkbox-size``.
Set your own values on the ``altcha-widget`` element. For other properties, prefix
the selector with the element name (``altcha-widget .altcha-footer``): the widget
injects its own styles after your stylesheet with single-class selectors.

Accessibility
-------------

There is no puzzle, so the check meets WCAG 2.2 success criterion 3.3.8 (Accessible
Authentication). The widget is operable with the keyboard and has a visible focus
indicator. Its checkbox is 22 by 22 pixels by default; set
``--altcha-checkbox-size: 24px`` to meet 2.5.8 (Target Size) on the checkbox itself.
Visitors without JavaScript see a notification that JavaScript is required.

Rate limit
==========

The extension can limit how often each form is sent from one IP address. Only
submissions that pass the honey pot and the ALTCHA check count; the subscribe and
unsubscribe forms have separate counters. A submission over the limit is refused
with a notification before the Laposta API is called, and logged when logging is
enabled.

The limit is off by default. Set it in the extension configuration:

``rateLimit``
    Maximum submissions per form from one IP address within the interval. ``0``
    disables the limit. Default ``0``.

``rateLimitInterval``
    A relative time such as ``1 hour`` or ``30 minutes``. Default ``1 hour``.

Many visitors can share one IP address, for example in an office, a school or on a
mobile network, so do not set the limit too low. The counters are kept in the TYPO3
core cache ``ratelimiter``. Behind a reverse proxy, configure
``$GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP']`` so the visitor's address is
used instead of the proxy's.
