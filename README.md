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

### 📬 AJAX Form Submission

- No page reload required.
- Client-side validation for required fields and optional email validation.
- Server-side validation for all submitted fields.
- Error messages displayed using Bootstrap 5 styling.
- Success message displayed after successful form submission.

### ✉️ Email Settings

Currently supported:

- **Receiver email** (required).
- Customizable **success message** after submission.
- Customizable **button labels**:
  - normal state;
  - while sending.
- Optional intro text displayed **above the form** in the popup/inline block.

Planned for future versions (not implemented yet):

- CC and BCC fields.
- Custom email subject per module.
- Custom email body intro (separate from the visible intro text).
- Custom sender name and sender email.

### 🌐 Multilingual Support

Comes with language files for:

- `ru-RU`
- `en-GB`
- `kk-KZ`

Additional languages can be added easily by providing the corresponding language files.

### 📁 JSON-Based Configuration Storage

All module settings (including the form fields configuration) are stored in Joomla's database as structured JSON via the standard `params` field.

---

## 📦 Installation

### 1. Install via Joomla Administrator Panel

1. Go to **Extensions → Install**.
2. Upload the module ZIP package.
3. After installation, go to **Extensions → Modules** and find **Popup Callback Form** (mod_popup_form).
4. Publish the module and assign it to the desired menu items and template position.

### 2. Use as a Popup Form

Add a link which contains the configured hash (by default: `callback`):

```html
<a href="#callback">Request a Callback</a>
```

When the link is clicked:

- the popup form appears with a smooth animation;
- the background is dimmed;
- the user can close the popup by clicking the close button or anywhere outside the popup.

You can change the hash in the module settings.

### 3. Use as a Static (Inline) Form

In the module parameters, switch the display mode from **Popup** to **Inline / Static**.

In this mode:

- the form is rendered directly where the module is published;
- there is no overlay or popup animation;
- the form still sends data via AJAX and shows errors/success messages without page reload.

You can publish both popup and inline instances of the module on the same page.

---

## 🔧 Requirements

- Joomla **5.x**
- Bootstrap **5.3.x**
- PHP **8.1+**
- Working mail setup (PHP mail or SMTP configured in Joomla)

---

## 🤝 Feedback & Support

If you enjoy this module or want to support further development, you can help via:

- Donatello: https://donatello.to/TekhnoKhobbIT  
- Buy Me a Coffee: https://buymeacoffee.com/tekhnokhobbit  

If you find a bug or want to propose an enhancement, feel free to create an Issue or Pull Request in the project repository.

---

## 📄 License

**GPL-2.0-or-later**
