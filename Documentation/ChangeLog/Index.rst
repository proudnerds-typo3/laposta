..  _changelog:

=========
ChangeLog
=========

*   1.0.0 Initial release on GitHub
*   10.4.0 Release in TER
*   11.5.0 Release for TYPO3 11.5
*   11.5.1 Small TCA fixes for TYPO3 11
*   12.4.0 Release for TYPO3 12
*   12.4.1 Small bugfix, prevent log errors on spam
*   12.5.0 Accessibility (WCAG 2.2 AA) and notifications:
    notifications now use the flash message queue, grouped per outcome and rendered as
    paragraphs and lists without ``<br>``; added ``fieldset``/``legend`` and a
    required-field hint; focus moves to the message after submitting; code-quality
    baseline (``declare(strict_types=1)``, typed properties). Note: the frontend
    templates changed, so re-check any template overrides.
