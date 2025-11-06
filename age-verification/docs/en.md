# Alexwaha.com - Age Verification

**Module for OpenCart v2.3 - 3.x**

---

## Description

This module provides age verification (18+) popup that appears on first visit to restrict access for underage visitors.

The age verification popup displays when users first visit your store, requiring them to confirm they are of legal age before accessing the site content.

---

## Installation Instructions

### Requirements and Installation Steps

1. **Install Core Module**

   Make sure you have installed the module **[aw_core_oc2.3-3.x.ocmod.zip](https://github.com/AlexWaha/opencart-bundle/blob/master/Core/dist/aw_core_oc2.3_3.x.ocmod.zip)**

   > This is an auxiliary function for all my modules. **Critically required**, the module will not work without it

2. **FTP Upload Settings (OpenCart 2.x only)**

   Make sure you have a module installed that disables FTP uploads for extensions in OpenCart 2.x, or that your FTP settings in the store config are correct

   > [Link to module disabling FTP upload for OpenCart v2.3 only](https://www.opencart.com/index.php?route=extension/extension/info&extension_id=18892) **(Not required for OpenCart 3.x)**

3. **Install module files aw_age_verification_oc2.3-3.x.ocmod.zip**
4. **Refresh the modification cache in your OpenCart admin panel**
5. **Enable the Module**

   Go to: **Extensions → Modules** → Enable the module **alexwaha.com - Age Verification**

   > When enabled, the module will automatically grant view/edit permissions

### Self-Check After Installation

- Check for the module in the list of extensions
- Refresh the modification cache
- Test the age verification popup on the frontend (clear cookies first)

---

## Configuration

### Module Settings

1. Go to **Extensions → Modules → alexwaha.com - Age Verification**

2. Configure the module settings:

   - **Status:** Enable/Disable the age verification popup
   - **Block Title:** Set the popup title for each language (e.g., "Are you 18 years old?")
   - **Description:** Add popup description content for each language (supports rich text editor)
   - **Cookie Storage Days:** Set how long user response is remembered (1-365 days, default: 30)
   - **Redirect URL:** URL to redirect users who decline verification

3. Save the configuration

4. The popup will automatically appear on every page until user accepts it

> **Important:** The popup automatically integrates into all pages via OCMOD system modification

> **Note:** User response is stored in cookies and remembered for the configured period

---

### User Interaction Flow

1. **First Visit:** Popup appears centered on screen with dark overlay
2. **User Choice:** Visitor clicks "Yes" (I'm 18+) or "No" to confirm their age
3. **Accept:** If "Yes", popup disappears and cookie is set for configured period
4. **Decline:** If "No", user is redirected to specified URL (if configured)
5. **Return Visits:** Popup won't show again until cookie expires

### Features

- Responsive design that works on all devices
- Smooth animations and professional styling
- Multilingual support
- Configurable cookie duration
- Optional redirect for declined verification

> **Important:** This is a basic age verification system and should not be considered a foolproof age restriction method

---

## For Developers

### Technical Overview

The module uses the Twig templating engine, and does not require declaring language variables inside the controller, just add the variable to the language file.

### File Locations

- **Template file:** `catalog/view/theme/default/template/extension/module/aw_age_verification.twig`
- **CSS styles:** `catalog/view/javascript/aw_age_verification.css`

### Key Components

- **OCMOD Integration** - Automatic footer injection without template modifications
- **Cookie Management** - Stores user response with configurable expiration
- **AJAX Handling** - Smooth user interaction without page reload
- **Responsive Design** - Mobile-friendly popup with flexbox centering
- **Rich Text Editor** - Summernote integration for description fields

> **Critical Requirement:** Use of the module is only possible together with the system module **alexwaha.com - Core**

---

## License

This project is licensed under the [GNU General Public License version 3 (GPLv3)](https://github.com/alexwaha/opencart-bundle/blob/master/LICENSE).

The rights and authorship of this software belong to the developer Oleksandr Vakhovskyi, also known as: Alexwaha, Ocdev.pro, website [https://alexwaha.com](https://alexwaha.com)

---

## Support and Contacts

> **Attention!** Technical support for this module is only available on a paid basis. Bug fixes in the main repository are done without a schedule as long as the developer has free time. If you can offer a solution to any problem, a [pull-request on GitHub](https://github.com/AlexWaha/opencart-bundle/pulls) is welcome.

**Paid Services:** Setting up the module by the author, customization, resolving conflicts with other modules, integration with a template, integration with another module in your project - only on a **paid basis**, please contact us below.

**Tested on:** Clean OpenCart installation and on the default theme (Default).

### Contact Information

- **Telegram:** [@alexwaha_dev](https://t.me/alexwaha_dev)
- **Email:** [support@alexwaha.com](mailto:support@alexwaha.com)
- **Bug report:** [https://alexwaha.com/bug-report](https://alexwaha.com/bug-report)

---

Made with ☕ by [Alexwaha.com](https://alexwaha.com)
