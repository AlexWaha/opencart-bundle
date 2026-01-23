# Alexwaha.com - Global Layout

**Module for OpenCart v2.3 - 3.x**

---

## Description

This module creates one global layout that allows you to place modules in this layout, and these modules will be displayed on all pages of your OpenCart store.

The Global Layout module allows you to place modules in a single layout that will be automatically displayed on every page of your online store.

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

3. **Install module files aw_global_layout_oc2.3-3x.ocmod.zip**
4. **Refresh the modification cache**
5. **Enable the Module**

   Go to: **Extensions → Modules** → Enable the module **alexwaha.com - Global layout**

   > When enabled, the module will automatically grant view/edit permissions and create necessary database tables

### Self-Check After Installation

- Check for the module in the list of extensions
- Refresh the modification cache
- Verify that the global layout interface is accessible
- Test module positioning on the frontend

---

## Configuration

### Module Settings

1. Go to **Extensions → Modules → alexwaha.com - Global layout**

2. Configure the module settings:

   - **Layout Name:** Set a name for your global layout (default: "All pages")
   - **Status:** Enable/Disable the global layout
   - **Add Modules:** Add modules to the global layout from dropdown lists
   - **Sort Order:** Drag modules by the handle to change their order within the same position

3. Save the configuration

4. When enabled, all modules in the global layout will appear on every page of your store

> **Important:** The global layout displays its modules on all pages when the layout is active

> **Note:** The module supports drag-and-drop reordering of modules within each position using visual sorting handles

---

### Using the Module

The Global Layout module creates a single global layout where you can add modules that will appear on all pages of your store.

1. **Access the Module:** Go to Extensions → Modules → Global Layout
2. **Choose Modules:** Select any installed module from the dropdown lists
3. **Add to Layout:** Add modules to the global layout positions (Content Top/Bottom, Left/Right columns, Full Width Top/Bottom)
4. **Reorder Modules:** Use drag-and-drop handle to reorder modules within the same position only
5. **Remove Modules:** Click the remove button to delete modules from positions
6. **Save Configuration:** Click "Save" to make modules appear on all pages

### Available Layout Positions

- **Full Width Top** - spans entire page width
- **Content Top** - above main content area
- **Column Left** - left sidebar
- **Column Right** - right sidebar
- **Content Bottom** - below main content area
- **Full Width Bottom** - spans entire page width

> **Important:** Modules added to the global layout will appear on all pages of your store. Remove modules from the layout if you don't want them on all pages.

---

## For Developers

### Technical Overview

The module uses the Twig templating engine, and does not require declaring language variables inside the controller, just add the variable to the language file.

The module's language files are always located in the `en-gb`, `ru-ru` folders regardless of the OpenCart version.

### File Locations

- **Admin template:** `admin/view/template/extension/module/aw_global_layout.twig`
- **Position templates:** `catalog/view/theme/default/template/extension/aw_position/aw_content_top.twig`, `aw_content_bottom.twig`

### Key Functions

- `getLayout($route)` - Retrieves layout configuration for a specific route
- `getModules($id)` - Gets all modules assigned to a layout position
- `editLayout($id, $data)` - Updates layout configuration and module assignments
- `install($name, $route)` - Creates database tables and initial layout

---

## License

This project is licensed under the [GNU General Public License version 3 (GPLv3)](https://github.com/alexwaha/opencart-bundle/blob/master/LICENSE).

The rights and authorship of this software belong to the developer Oleksandr Vakhovskyi (Alexwaha), website [https://alexwaha.com](https://alexwaha.com)

---

## Support and Contacts

> **Attention!** Technical support for this module is only available on a paid basis. Bug fixes in the main repository are done without a schedule as long as the developer has free time. If you can offer a solution to any problem, a [pull-request on GitHub](https://github.com/AlexWaha/opencart-bundle/pulls) is welcome.

**Paid Services:** Setting up the module by the author, customization, resolving conflicts with other modules, integration with a template, integration with another module in your project - only on a **paid basis**, please contact us below.

**Tested on:** Clean OpenCart installation and on the default theme (Default).

### Contact Information

- **Telegram:** [@alexwaha_dev](https://t.me/alexwaha_dev)
- **Email:** [support@alexwaha.com](mailto:support@alexwaha.com)
- **Bug Report:** [GitHub Issues](https://github.com/AlexWaha/opencart-bundle/issues)
- **Contact Form:** [https://alexwaha.com/contact](https://alexwaha.com/contact)

---

Made with ☕ by [Alexwaha.com](https://alexwaha.com)
