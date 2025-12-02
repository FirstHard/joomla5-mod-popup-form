# Popup Callback & Contact Form for Joomla 5

**mod_popup_form** is a flexible form module for Joomla 5 that can work both as:

- a **popup callback request form** (opened by clicking a link with a hash, e.g. `#callback`);
- a **static contact/feedback form** embedded directly into a module position.

You can configure arbitrary fields in the Joomla administrator panel and use the module for callback requests, contact forms, and other custom forms (it is **not intended** to replace Joomla's core registration/login forms).

The module is ideal for landing pages, corporate websites, and projects that need a lightweight, fast form without page reloads.

---

## ✨ Features

### 🖥 Display Modes

- **Popup mode**
  - Smooth appearance animation from the click position to the center of the screen.
  - Background dimming with click-to-close behavior.
  - Close button and click-outside-to-close support.

- **Inline (static) mode**
  - The form is displayed directly in the module position.
  - Fully supports client-side validation and AJAX submission.
  - Can be used alongside popup forms on the same page.

- **Multiple forms on one page**
  - You can publish several instances of the module (popup and/or inline).
  - Each instance has its own configuration and works independently.

### ⚙️ Fully Configurable Form Fields

- Add, remove, and reorder fields in the admin panel (Subform-based UI).
- Supported field types, for example:
  - `text`, `email`, `tel`, `textarea` (field types such as `select` and others will be added in future releases).
- Per-field configuration:
  - label text;
  - show/hide label;
  - placeholder;
  - required flag;
  - label position (top / left);
  - optional email validation for email-type fields.
- Flexible architecture that can be extended later (telephone format validation, input masks, custom validators, etc.).

### 🔒 Optional Captcha Support

The module supports Joomla’s built-in captcha plugins, including:

- reCAPTCHA v2 (checkbox & invisible)
- reCAPTCHA v3
- Any other Joomla-compatible captcha plugin

You can choose a captcha plugin **per module instance**.

Features:

- Captcha is validated server-side inside the module.
- Captcha works for both **popup** and **inline** modes.
- Fully AJAX-compatible — form does not reload page.
- reCAPTCHA v3 badge is automatically relocated to the **bottom-left** corner to avoid UI overlap.

If captcha validation fails, the form displays an appropriate error message above the form.

### 📬 AJAX Form Submission

- No page reload required.
- Client-side validation for required fields and optional email validation.
- Server-side validation for ALL submitted fields.
- Error messages displayed using Bootstrap 5 styling.
- Success message displayed after successful form submission.

### ✉️ Email Settings

Currently supported:

- **Receiver email** (required).

- a **contact selector** appears in module settings
- the **contact ID** is stored in the form (via hidden input)
- email is pulled from `com_contact` dynamically during submission
- fallback to manual email if contact has no valid email

This enables advanced usage — for example:

- One form module can be reused on **multiple pages**  
- JavaScript can dynamically assign different **contact IDs**  
- Perfect for **multi-office contacts pages** 
- Customizable **success message**.
- Customizable submit button texts.
- Optional intro text before the form, with configurable position (**above** or **left** of the form on desktop) and optional HTML output.
- Per-module custom email subject.
- **File upload field** with server-side validation and email attachments.

### Submit button CSS class

You can optionally specify an additional CSS class for the submit button:

- Parameter: **Submit button CSS class** (`submit_btn_class`)
- Example values:
  - `btn-lg`
  - `btn-outline-secondary`
  - `btn-lg btn-outline-secondary`
- The module automatically concatenates this value with the base classes (`btn btn-primary mpf-submit-btn`) without extra spaces.

Planned for future versions:

- CC/BCC.
- Custom email intro line.
- Custom sender name and email.

### 🌐 Multilingual Support

Includes language files for:

- `uk-UA`
- `en-GB`
- `ru-RU`
- `kk-KZ`

### 📁 JSON-Based Configuration Storage

All configuration (including form fields) is stored as structured JSON inside Joomla’s `params`.

---

## 📦 Installation

### 1. Install via Joomla Administrator Panel

1. Go to **Extensions → Install**.
2. Upload the module ZIP.
3. Publish the module via **Extensions → Modules**.
4. Assign it to menu items or positions as needed.

### 2. Use as Popup

Add a link with the configured hash:

```html
<a href="#callback">Request a Callback</a>
```

### 3. Use as Static Form

Switch **Display Mode** to **Inline / Static** in module settings.

---

### File upload field

The module supports a `file` field type:

- Uploaded files are attached to the email.
- Allowed extensions are based on Joomla Media Manager (`com_media` upload and image extensions), plus a built-in whitelist:
  - Office documents: **doc, docx, xls, xlsx, ppt, pptx**
  - **pdf**
  - Images: **jpg, jpeg, png, gif, webp, bmp** (excluding `svg`)
  - Archives: **zip**
- Two module parameters control limits:
  - **Maximum total attachments size (MB)** – default: `20`
  - **Maximum number of attachments** – default: `5`
- `0` in either limit means “no limit”.

---

## 🔧 Requirements

- Joomla 5.x  
- PHP 8.1+  
- Bootstrap 5.3.x  
- Working email configuration  
- *(Optional)* any Joomla-supported captcha plugin  

---

## 🤝 Feedback & Support

If you enjoy this module or want to support further development:

- Donatello: https://donatello.to/TekhnoKhobbIT  
- Buy Me a Coffee: https://buymeacoffee.com/tekhnokhobbit  

Report issues or contribute via GitHub.

---

## 📄 License

**GPL-2.0-or-later**
