# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

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

## [0.3.1] - 2025-11-22
### Changed
- Updated README.md to reflect new module capabilities (inline mode, multi-form support).
- Minor improvements and refinements in documentation.

### Fixed
- Small fixes and cleanup related to the previous release.

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
