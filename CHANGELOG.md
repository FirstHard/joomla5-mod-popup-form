# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

---

## [0.6.0] - 2025-11-30
### Added
- Custom email subject per module instance.
- Intro text position options (**above** or **left** of the form on desktop).
- Option to allow or escape HTML in the intro text.

### Changed
- Form fields configuration block moved to the bottom of module parameters for better UX.

### Fixed
- Updated and corrected language strings.
- Minor UI and configuration consistency fixes.

---

## [0.5.0] - 2025-11-29
### Added
- New **Email recipient mode: Joomla Contact Email**
  - Module can now dynamically send messages to email of selected Joomla contact
  - Fully compatible with AJAX and multilingual environments
- Admin UI option for selecting between:
  - Manual email
  - Email from contact
- Automatic creation of hidden field storing contact ID
- Support for dynamic contact ID replacement by page scripts

### Improved
- Better handling of multiple form modules on the same page
- Correct hiding/displaying of receiver-related fields based on mode
- More robust validation flow
- Safer parameter access and type handling
- Added error logging on contact email lookup failures (only when JDEBUG enabled)

### Fixed
- PHP warning: undefined variable `$module` when submitting AJAX form
- Incorrect module loading on AJAX request
- JSON output was broken due to warnings in response
- Captcha handling logic updated to avoid missing/invalid token errors

---

## [0.4.0] - 2025-11-23
### Added
- Optional integration with Joomla captcha plugins (e.g. reCAPTCHA v2, Invisible reCAPTCHA, reCAPTCHA v3) on a per-module basis.
- Server-side captcha validation inside the module’s AJAX handler.
- Support for captcha in both popup and inline display modes.
- Automatic relocation of the reCAPTCHA v3 badge to the bottom-left corner to avoid overlapping UI elements.

### Changed
- Unified field validation logic in the AJAX handler to work consistently with custom fields and captcha.
- Improved error messages for validation failures, including captcha-related errors.

### Fixed
- Fixed an issue where forms with enabled captcha always failed validation with a generic error.
- Fixed handling of the reCAPTCHA v3 token by explicitly passing the module-specific token value to the captcha plugin.

---

## [0.3.1] - 2025-11-22
### Changed
- Updated README.md to reflect new module capabilities (inline mode, multi-form support).
- Minor improvements and refinements in documentation.

### Fixed
- Small fixes and cleanup related to the previous release.

---

## [0.3.0] - 2025-11-21
### Added
- **Static form display mode (inline mode)** — the form can now be embedded directly on the page.
- Support for **multiple forms on a single page**, both popup and inline.
- Added automatic handling for form validation and AJAX submission for inline forms.
- Improved JS architecture ensuring isolated behavior per instance of the module.

### Changed
- Refactored JavaScript event binding to avoid conflicts between multiple form instances.
- Improved parsing of dynamic fields from module params.
- Updated default template to support both modes with shared logic.

### Fixed
- Fixed issues where forms could not validate required fields.
- Fixed AJAX submission for inline mode.
- Prevented page reload for inline mode forms.

---

## [0.2.1] - 2025-11-20
### Added
- Dynamic form fields stored as JSON in module params.
- Subforms for adding/removing/editing fields in admin UI.
- Field-level features: type, placeholder, required, label position, email validation.
- Multilingual support (ru-RU, en-GB, kk-KZ).

### Fixed
- Corrected language constants for admin interface.
- Fixed popup animation and overlay behavior.
- Fixed AJAX error reporting via com_ajax.

---

## [0.1.0] - 2025-11-19
### Added
- Initial release with popup callback form.
- AJAX submission via com_ajax.
- Bootstrap 5 styling and validation.
- Customizable intro text and button labels.
