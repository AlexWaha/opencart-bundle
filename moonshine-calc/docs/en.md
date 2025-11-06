# Alexwaha.com - Moonshine Calculator

**Module for OpenCart v2.3 - 3.x**

---

## Description

This module provides a moonshine dilution calculator that automatically calculates the amount of water needed to achieve desired alcohol strength.

The calculator helps determine the exact amount of water needed to dilute moonshine to the desired strength based on the initial volume and alcohol percentage.

---

## Installation Instructions

### Requirements and Installation Steps

1. **Install Core Module**

   Make sure you have installed the module **[aw_core_oc2.3-3.x.ocmod.zip](https://github.com/AlexWaha/opencart-bundle/blob/master/Core/dist/aw_core_oc2.3_3.x.ocmod.zip)**

   > This is an auxiliary function for all my modules.

   > **Critically required**, the module will not work without it

2. **FTP Upload Settings (OpenCart 2.x only)**

   Make sure you have a module installed that disables FTP uploads for extensions in OpenCart 2.x, or that your FTP settings in the store config are correct

   > [Link to module disabling FTP upload for OpenCart v2.3 only](https://www.opencart.com/index.php?route=extension/extension/info&extension_id=18892) **(Not required for OpenCart 3.x)**

3. **Install module files aw_moonshine_calculator_oc2.3-3.x.ocmod.zip**

4. **Refresh the modification cache**

5. **Enable the Module**

   Go to: **Extensions → Modules** → Enable the module **alexwaha.com - Moonshine Calculator**

   > When enabled, the module will automatically grant view/edit permissions

### Self-Check After Installation

- Check for the module in the list of extensions
- Refresh the modification cache
- Test the calculator functionality on the frontend

---

## Configuration

### Module Settings

1. Go to **Extensions → Modules → alexwaha.com - Moonshine Calculator**

2. Configure the module settings:

   - **Status:** Enable/Disable the calculator
   - **Page Title:** Set the page title for each language
   - **H1 Heading:** Set the main heading for each language
   - **Description:** Add page description content (supports HTML)
   - **Instructions:** Add usage instructions (supports HTML)
   - **SEO URL:** Set the friendly URL (default: moonshine-calculator)

3. Save the configuration

4. The calculator will be accessible at: `your-site.com/moonshine-calculator` (or your custom SEO URL)

> **Important:** The calculator page is automatically added to the sitemap **sitemap.xml**

> **Note:** The module supports AJAX calculations for instant results without page reload

---

### Using the Calculator

The calculator helps determine how much water to add when diluting moonshine to achieve desired alcohol strength.

1. **Initial Strength (%):** Enter the current alcohol strength (0-100%)
2. **Desired Final Strength (%):** Enter the target alcohol strength (must be lower than initial)
3. **Moonshine Volume (L):** Enter the volume of moonshine to be diluted
4. **Click "Calculate"** to get results

### Calculation Results

- Amount of water to add (L)
- Final total volume (L)

> **Important:** All calculations are accurate for component temperature of 20°C

---

## For Developers

### Technical Overview

The module uses the Twig templating engine, and does not require declaring language variables inside the controller, just add the variable to the language file.

The module's language files are always located in the `en-gb`, `ru-ru` folders regardless of the OpenCart version.

### File Locations

- **Catalog template:** `catalog/view/theme/default/template/extension/module/aw_moonshine_calculator.twig`
- **CSS styles:** `catalog/view/javascript/aw_moonshine_calculator.min.css`

### Key Functions

- `calculate($initial_strength, $final_strength, $moonshine_volume)` - Main calculation method
- `getTemperatureCorrection($temperature)` - Temperature correction (for future updates)
- `validateStrength($strength)` - Validates alcohol strength input
- `validateVolume($volume)` - Validates volume input

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
