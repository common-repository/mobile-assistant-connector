=== Mobile Assistant Connector ===
Contributors: emagicone
Tags: woocommerce, android, mobile, shop, sales, assistant, business, control, statistics, reports, tracker, assistant
Requires at least: 4.1.1
Tested up to: 6.0
Stable tag: 2.2.4
Requires PHP: 5.2.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin allows you to keep your online business under control wherever you are.

== Description ==

The plugin helps you to connect your WooCommerce store to your Android device and track your sales wherever you are. When installing free mobile assistant module from [Google Play](https://play.google.com/store/apps/details?id=com.WooCommerce.MobileAssistant) and connecting to the store you can easily view your recent orders or customers data, available products and some other useful information from your store. Filters and widgets informs you about new orders with push notifications or shows you information for a certain period of time (the last week, day, month, etc.). Also you can see cancelled, delivered or processing order statuses to see how orders are processed.

== Minimum Requirements ==

* WordPress version 4.1.1 or higher
* WooCommerce version 2.3.4 or higher
* PHP version 5.2.4 or higher
* MySQL version 5.0 or higher

== Installation ==

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of Mobile Assistant Connector, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “Mobile Assistant Connector” and click Search Plugins. Once you’ve found our plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.
After that Install Android app from Google Play and scan QR Code on Connector settings page using Mobile device to apply all settings.

== Manual installation ==

The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application. Visit our Documentation to find [instructions on how to do this here](https://mobile-store-assistant-help.emagicone.com/woocommerce-mobile-assistant-installation-instructions).

== Updating ==

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

If on the off-chance you do encounter issues with the shop/category pages after an update you simply need to flush the permalinks by going to WordPress > Settings > Permalinks and hitting 'save'. That should return things to normal.

== Frequently asked questions ==

= Something goes wrong, how can I contact support? =

Free support is provided via e-mail - mobile@emagicone.com

= Where can I find public documentation? =

Online documentation can be found at
[https://mobile-store-assistant-help.emagicone.com/](https://mobile-store-assistant-help.emagicone.com/)

= Where can I request a feature? =

You can request any feature via our [Google Form](https://docs.google.com/forms/d/e/1FAIpQLSfygFHYp5SrEroErnk5poGAHho0N1kGqDYON0aQFw4yn8tvFw/viewform).

== Screenshots ==

1. About WooCommerce Mobile Assistant
2. Mobile Assistant Dashboard
3. Mobile Assistant Connection Settings
4. Mobile Assistant Customers General
5. Mobile Assistant Menu
6. Mobile Assistant Order
7. Mobile Assistant Orders General
8. Mobile Assistant Orders Status Update
9. Mobile Assistant Product Details
10. Mobile Assistant Products General
11. Mobile Assistant Push Notifications
12. Mobile Assistant Side Menu
13. Mobile Assistant Widget
14. Widget
15. Plugin

== Changelog ==
= 2.2.4 =
* **Improvement:**
* Implemented recommended fixes from the Wordpress validator.

= 2.2.3 =
* **Improvement:**
* Updating Bootstrap library to 4.6.2
* Implemented recommended fixes from the Wordpress validator.

= 2.2.2 =
* **Improvement:**
* Implemented recommended fixes from the Wordpress validator.

= 2.2.1 =
* **Improvement:**
* Updating author details
* Implemented recommended fixes from the WordPress validator.

= 2.2.0 =
* **Fixed:**
* Issue with time conversion has been fixed. Now, Mobile Assistant displays order creation date according to the time zone set on the mobile device.
* Fixed “An unknown network error occurred” issue that occurred upon editing some downloadable products.

* **Improvements:**
* PHP 8 support has been added.

= 2.1.10 =
* **Fixed:**
* Fatal error on plugin activation

= 2.1.9 =
* **Fixed:**
* Deprecations of code for php7.4 has been fixed.

= 2.1.8 =
* **Fixed:**
* Switch user issue has been fixed.

= 2.1.7 =
* **Fixed:**
* Compatibility issue with WordPress 5.5 has been eliminated.

= 2.1.6 =
* **Fixed:**
* Add new product issue has been fixes.

= 2.1.5 =
* **Fixed:**
* Minor bug fixes.

= 2.1.4 =
* **Fixed:**
* Table "mobileassistant_devices" usage has been removed.
* Minor bug fixes.

= 2.1.3 =
* **Fixed:**
* Crashing on order details if products were removed has been fixed.

= 2.1.2 =
* **Fixed:**
* PHP notices have been fixed.
* Compatibility with WooCommerce version less then 3.0.0 has been fixed.
* Price with the "&nbsp" sign issue has been fixed.

= 2.1.1 =
* **Fixed:**
* PHP notices has been fixed.

= 2.1.0 =
* **NEW:**
* Product editing and creation feature has been implemented.
* More Product details have been added.

= 2.0.1 =
* **Fixed:**
* Push Notifications are not received

= 2.0.0 =
* **NEW:**
* Support for the new version of Mobile Assistant for WooCommerce has been added.

= 1.4.13 =
* **Fixed:** Notification currency symbol issues on some devices

= 1.4.12 =
* **Fixed:** Order list customer names are showed correspondingly to admin.
* **Improved:** Upgrade from GCM to FCM (Notifications Fixes).

= 1.4.11 =
* **Fixes:**
* Dashboard issues with specific server settings have been corrected.
* Product parent image in variable item is now displayed in ordered items list.
* Detailed order information display issue has been eliminated.
* Other minor fixes.

= 1.4.10 =
* **Fixes:**
* Variable items meta data is now displayed in the order detailed view.
* Variable item thumbnail in the order wasn't displayed earlier but now it is;)
* **Improvements:**
* Display of order items with corrupted content has been added.
* Ordered item "Price" field has been renamed to "Total".

= 1.4.9 =
* **NEW:**
* Multisite compatibility has been implemented.
* Compatibility with WooCommerce repositories installed from git has been added.
* **Fixes:**
* Module activation has been improved in the admin side.
* Product meta data issues are now amended.
* Some order information details have been reformatted.
* Other minor fixes.
* **Improved:** Compatibility with php version up to 7.1+ has been improved.

= 1.4.8 =
* **Fixed:** Performance optimizations.

= 1.4.7 =
* **Fixed:** From now on, product price is correctly displayed on front end.

= 1.4.6 =
* **NEW:**
* The display of returning customers have been added to dashboard.
* New filters for customers have been implemented.
* **Fixed:** Product sorting by quantity has been fixed.

= 1.4.5 =
* **NEW:** Draft products have been included to product grid.

= 1.4.4 =
* **Fixed:** An issue related to product images has been fixed.

= 1.4.3 =
* **Fixed:** Warnings about created order on WooCommerce 3.0 and higher have been eliminated.

= 1.4.2 =
* **Fixed:** Minor bug fixes.

= 1.4.1 =
* **Fixed:** An image on product details page is now properly displayed.

= 1.4.0 =
* **NEW:** Product editing and creation feature has been implemented.

= 1.3.7 =
* **NEW:** Order search by user email has been added.

= 1.3.6 =
* **Fixed:** An issue notifications on order status change has been eliminated.

= 1.3.5 =
* **NEW:** Order custom fields have been added in the application.
* **Fixed:** Now, correct product quantity is displayed in the order.


= 1.3.4 =
* **NEW:** WooCommerce PDF Invoices & Packing Slips compatibility has been implemented. Now you can generate and download PDF invoices.
* **Fixed:** Shipping tax issue has been amended.


= 1.3.3 =
* **Improved:** Ukrainian language has been added in the application.

= 1.3.2 =
* **Improved:** The possibility to add new translations has been added.

= 1.3.1 =
* **Fixes:**
* Ajax methods have been fixed.
* User permissions checking has been corrected.

= 1.3.0 =
* **Fixes:**
* Work with ajax in plugin on the admin page has been reorganized.
* Curl library check has been added.
* Device status saving issue has been amended.

= 1.2.9 =
* **Fixed:** Compatibility issues with PHP 7 has been fixed.

= 1.2.8 =
* **Fixed:** Ordered products list scrolling has been corrected.

= 1.2.7 =
* **Fixed:** Compatibility issues with "IG Page Builder" extension have been fixed.

= 1.2.6 =
* **Fixed:** Bug fix release.

= 1.2.5 =
* **Improved:** Files structure (internal) has been changed.

= 1.2.4 =
* **Fixed:** Compatibility issues with "WC Status Actions" extension have been amended.

= 1.2.3 =
* **Fixed:** An issue related to admin panel has been eliminated.

= 1.2.2 =
* **NEW:** Code optimization and some workaround with WooCommerce deprecated function has been added.

= 1.2.1 =
* **Fixed:** PHP notice has been fixed.

= 1.2.0 =
* **NEW:**
* Now, multiple user accounts can be created.
* User permission control is now at disposal.
* Device activity control is now possible: you can check all the devices connected to your store, activate/deactivate devices, remove devices.
* Display of all images list has been added.
* Grouping products by Product ID, Order ID has been added.
* **Improvements:**
* Additional parameters to sort by have been added.
* Ability to prevent loading images for faster work with application has been added.
* Additional data range for chart has been implemented.
* Formatting of dashboard average sums is now available in the application.
* **Fixed:** Code optimization has been implemented.

= 1.1.1 =
* **Improved:** Plugin v.1.1.0.1 or higher works only with beta release of the application 1.2.0.1 and higher.
* **Fixed:** Bug with empty product attributes value has been corrected.

= 1.1.0.1 =
* **Improvements:**
* Authorization has been improved and now it is more secure.
* Push notification devices list was added on Mobile Connector plugin settings page.

= 1.0.8 =
* **Fixed:** Wrong order discount display has been fixed.

= 1.0.7 =
* **Improved:** Added "yesterday" and "last quarter" period on the dashboard.

= 1.0.6 =
* **Fixed:** Other minor fixes.

= 1.0.5 =
* **Fixes:**
* Image thumbnails are now displayed in product list.
* Bug with incorrect date has been fixed.

= 1.0.4 =
* **Fixes:**
* Admin panel module design has been changed.
* Script loading has been fixed.

= 1.0.3 =

* **Fixes:**
* Compatibility issue with WordPress has been eliminated.

= 1.0.2 =
* **Fixed:** Other minor fixes.

= 1.0.1 =
* **Fixed:** Other minor fixes.

= 1.0.0 =
* First release

== Upgrade notice ==

= 1.0.5 =
* **NEW:** Image thumbnails now are displayed in product list.
* **Fixed:** Bug with incorrect date has been fixed.

= 1.0.4 =
* **Fixes:**
* Admin panel module design has been changed.
* Script loading has been fixed.

= 1.0.3 =
* **Fixes:**
* Compatibility issues with WordPress have been corrected.
* Other minor fixes.

= 1.0.2 =
* **Fixed:** Minor fixes.

= 1.0.1 =
* **Fixed:** Minor fixes.

= 1.0.0 =
* **First release.**