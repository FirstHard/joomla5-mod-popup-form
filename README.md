
# Popup Callback Form for Joomla 5

**mod_popup_form** is a customizable popup callback form module for Joomla 5.  
The form opens when clicking a link with a specific hash (e.g., `#callback`) and supports fully configurable fields directly from the Joomla administrator panel.

The module is ideal for landing pages, corporate websites, and any project requiring a lightweight, fast contact form without page reloads.

---

## ✨ Features

### 🖱 Popup Window
- Smooth appearance animation from the user click position.
- Centered popup with transition effects.
- Background dimming with click-to-close behavior.
- Close button and click-outside-to-close support.

### ⚙️ Fully Configurable Form Fields
- Add, remove, and reorder fields in the admin panel.
- Supported field types:
  - `text`, `email`, `tel`, `textarea`, `select`, and more.
- Per-field configuration:
  - label text,
  - show/hide label,
  - placeholder,
  - required flag,
  - label position (top / left),
  - optional email validation.
- Flexible architecture that can be extended further (telephone format validation, input masks, custom validators) - in future.

### 📬 AJAX Form Submission
- No page reload required.
- Server-side validation of each field.
- Error messages displayed using Bootstrap 5 styling.
- Success message displayed after form submission.

### ✉️ Email Settings
- Receiver email (required).
- CC and BCC fields (optional) - in future.
- Email subject - in future.
- Introductory text before submitted fields.
- Customizable success message.
- Optional sender name and email - in future.

### 🌐 Multilingual Support
Comes with language files for:
- `ru-RU`
- `en-GB`
- `kk-KZ`

Additional languages can be added easily.

### 📁 JSON-Based Configuration Storage
All module settings (including form fields) are stored in Joomla's database as structured JSON.

---

## 📦 Installation

### 1. Install via Joomla Administrator Panel
1. Go to **Extensions → Install**.
2. Upload the module ZIP package.
3. After installation, go to **Extensions → Modules** and find **Popup Callback Form**.
4. Publish the module and (optionally) assign it to a template position.

### 2. Add a Link to Trigger the Popup

Use any link containing the configured hash:

```html
<a href="#callback">Request a Callback</a>
```

You can change the hash value in the module settings.

---

## ⚙️ Main Configuration Options

### 🔗 Link Hash
This value must match the part after `#` in your link:

```
callback  →  <a href="#callback">
```

### 📝 Custom Form Fields
In the **Form Fields** section you can:
- add fields,
- rearrange fields,
- configure each field.

### 🔤 Button Texts
You may define:
- Button text
- Button text during submission

If left empty, language file defaults are used.

### 💬 Success Message
After successful AJAX submission, the form is hidden and a custom success message is shown.

---

## 📧 Email Delivery

The module uses Joomla’s built-in mailer.

Email includes:
- Introductory text (if provided) - in future,
- All form fields in readable form:

```
Your Name: John
Your Phone: +1 234 567 89
Comment: Please contact me today ( - in future)
```

In case mail sending fails, the module displays a readable error message.

---

## 🔧 Requirements
- Joomla 5.x
- PHP 8.1+
- Working mail function (PHP mail or SMTP)

---

## 🤝 Feedback & Support

If you enjoy this module or want to support further development, you can help via:

- Donatello: https://donatello.to/TekhnoKhobbIT  
- Buy Me a Coffee: https://buymeacoffee.com/tekhnokhobbit  

If you find a bug or want to propose an enhancement, feel free to create an Issue or Pull Request on the project repository.

---

## 📄 License
**GPL-2.0-or-later**
