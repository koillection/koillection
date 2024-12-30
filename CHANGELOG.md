# Changelog
All notable changes to this project will be documented in this file.

## [1.6.1] / 2024-12-30
### Fixes
- Fix error on price counters (benjaminjonard)

## [1.6.0] / 2024-12-29
:warning: PHP 8.4 is now required

### Features
- Add error panel in Administration page (benjaminjonard)

### Fixes
- Fix error when using html file scrapper (benjaminjonard)

### Miscellaneous
- Display item names on public pages based on owner settings (benjaminjonard)
- Update to PHP 8.4 (benjaminjonard)
- Update to Symfony 7.2 (benjaminjonard)
- Upgrade PHP and JS dependencies (benjaminjonard)
- Update translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)

## [1.5.15] / 2024-11-22
### Features
- Currency can now be changed on price data (benjaminjonard)

### Fixes
- Fix ordering on columns containing numbers (benjaminjonard)

### Miscellaneous
- Update Docker images to Ubuntu 24 (benjaminjonard)
- Upgrade PHP and JS dependencies (benjaminjonard)

## [1.5.14] / 2024-10-28
:warning: If you are using the API, the "hydra" prefix has been removed from api responses. Removing every occurrence of `hydra:` should work.

### Features
- Add Chinese (simplified) language (Gannnonnn)

### Miscellaneous
- Upgrade PHP and JS dependencies (benjaminjonard)
- Update translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)


## [1.5.13] / 2024-09-12
### Features
- Add an option to display search results as list (benjaminjonard)

### Fixes
- Fix duplicated list entries when changing position (benjaminjonard)
- Disable keyboard navigation between pages when gallery is open  (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies, fix 7 JS CVEs (benjaminjonard)

## [1.5.12] / 2024-06-12
### Features
- Add Portuguese translation (dmartins)

### Fixes
- Fix tags underlining for lists data fields (benjaminjonard)
- Properly check for label uniqueness and value formats for post Datum with API (benjaminjonard)
- Fix missing datum price type when using API (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies, fix CVE-2024-4068 (benjaminjonard)

## [1.5.11] / 2024-06-06
# Fixes
- Fix item links on shared search page (benjaminjonard)
- Prevent cropper refresh when clicking on preview image (benjaminjonard)
- Fix data field values being deleted whe using the 'Load common fields' feature (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies (benjaminjonard)

## [1.5.10] / 2024-05-28
### Fixes
- Fix scrapped image URL when protocol is missing (benjaminjonard)
- Fix wrong collection total prices when the collection has private items (benjaminjonard)
- Fix nested counters on shared pages (benjaminjonard)

### Miscellaneous
- Rework caches (benjaminjonard)
- Upgrade PHP and JS dependencies (benjaminjonard)
- Update translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)

## [1.5.9] / 2024-05-08
### Features
- Additional images can now be added in scrapers (benjaminjonard)
- Add metrics endpoint, see [Metrics wiki](https://github.com/benjaminjonard/koillection/wiki/Metrics) (benjaminjonard)

### Fixes
- Fix search in country select list (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies (benjaminjonard)
- Update translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)

## [1.5.8] / 2024-04-08
### Features
- Add visibility on item and collection data fields (benjaminjonard)

### Fixes
-  Fix error when choice lists has duplicated entries (benjaminjonard)
-  Fix data not being properly displayed when choice from a choice list is updated/deleted (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies (benjaminjonard)
- Update translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)

## [1.5.7] / 2024-03-15
### Fixes
- Properly use quantity when display collection's total price (benjaminjonard)
- Fix inventory form always using all collections (benjaminjonard)
- Allow search in choice lists metadata (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies, fix CVE-2023-42282 and CVE-2024-28849 (benjaminjonard)
- Update translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)

## [1.5.6] / 2024-02-13
### Fixes
- Fix quotes in doctrine json datatype migrations (benjaminjonard)

## [1.5.5] / 2024-02-13
### Fixes
- Fix doctrine json datatype migrations (benjaminjonard)

## [1.5.4] / 2024-02-10
### Features
- Add an option to display item quantities on collection page (benjaminjonard)

### Fixes
- Fix name filters (benjaminjonard)
- Temp fix for composer (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies (benjaminjonard)
- Update translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)

## [1.5.3] / 2024-01-30
### Features
- Add video metadata type (benjaminjonard)

### Fixes
- Fix filtering when displaying items names (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies (benjaminjonard)
- Update translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)

## [1.5.2] / 2024-01-11
### Fixes
- Fix star rating misalignment on some screen resolutions and zoom levels (benjaminjonard)
- Fix input misalignment on smartphone when editing item's metadata (benjaminjonard)

## [1.5.1] / 2024-01-08
### Features
- Add title display on hover on collections names (benjaminjonard)
- Add new setting allowing the display of item names in grid view (benjaminjonard)

### Fixes
- Fix star ratings CSS misalignment (benjaminjonard)
- Fix wrong tag usage percentage (benjaminjonard)

### Miscellaneous
- Rework the display of related items on the item page (benjaminjonard)
- Fix Doctrine and API Platform deprecations (benjaminjonard)
- Update coding style (benjaminjonard)
- Upgrade PHP and JS dependencies, fix CVE-2023-26159 (benjaminjonard)
- Update translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)

## [1.5.0] / 2023-12-13
### Features
- Add wish scraper. It won't work for now on big ecommerce websites as they actively block scrapers (benjaminjonard)

### Fixes
- Make whole dropdown menu items clickable (bartoszLesniewski)
- Minor global CSS adjustments (benjaminjonard) 
    
### Miscellaneous
- Add name as hover text for items in grid view (Matthieu-LAURENT39)
- Better handling of error message when reaching upload limit (benjaminjonard)
- Split item and collection scrapers (benjaminjonard)
- Update to PHP 8.3 (benjaminjonard)
- Upgrade PHP (Symfony 7.0) and JS dependencies (benjaminjonard)
- Add a docker image using FrankenPHP, experimental (benjaminjonard)
- Update translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)

## [1.4.13] / 2023-11-08
### Features
- Add Russian translation (max2k.ru)

### Fixes
- Fix image data being deleted when reloading a template (benjaminjonard)
- Fix input max length for scrappers (benjaminjonard)

### Miscellaneous
- Auto resize text areas (benjaminjonard)
- Automatically select user's preferred timezone on first connection page (Matthieu-LAURENT39)
- Upgrade PHP and JS dependencies, fix CVE-2023-45133 and CVE-2023-44270 (benjaminjonard)
- Upgrade yarn to v4 and materializecss, small CSS issues are expected (benjaminjonard)
- Update translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)

## [1.4.12] / 2023-09-20
### Fixes
- Fix error when a Country data is empty (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies (benjaminjonard)
- Compatibility with Postgres 16 (benjaminjonard)
- Update translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)

## [1.4.11] / 2023-08-29
### Features
- Allow metadata search for text areas, lists and choice lists (benjaminjonard)
- Add basic sorting on list view (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies (benjaminjonard)
- Update translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)

## [1.4.10] / 2023-08-18
### Features
- Add Brazilian Portuguese translation (brunoccr)

### Fixes
- Fix overlapping remove button on lists (benjaminjonard)
- Fix scrappers with empty data (benjaminjonard)

### Miscellaneous
- Try to guess image url host if missing when scraping (benjaminjonard)
- Fill existing empty fields when scraping (benjaminjonard)
- Upgrade PHP and JS dependencies (benjaminjonard)
- Update spanish translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)

## [1.4.9] / 2023-08-05
### Features
- Add list field type (benjaminjonard)
- New scraping feature, see here : [Scraping wiki](https://github.com/benjaminjonard/koillection/wiki/Scraping) (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies (benjaminjonard)

## [1.4.8] / 2023-07-24
### Features
- Add Polish translation (bartoszLesniewski and Lenetis)

### Miscellaneous
- Upgrade PHP and JS dependencies, fix CVE-2022-25883 (benjaminjonard)

## [1.4.7] / 2023-07-07
### Features
- Add Italian translation (AlexKalopsia)

### Fixes
- Avoid crash with invalid date-time strings (leezer3)
- Fix broken choicelist support in API (bendotli)
- Fix endless loop when assigning a child element as its parent (benjaminjonard)

### Miscellaneous
- Update dark theme (benjaminjonard)
- Improve metadata edition on smartphones, add an icon to open datepickers (benjaminjonard)
- Upgrade PHP and JS dependencies (benjaminjonard)
- Update spanish and german translations, thanks to all contributors on [Crowdin](https://crowdin.com/project/koillection)

## [1.4.6] / 2023-06-14
### Features
- Add keyboard navigation (left and right arrows) on item pages (benjaminjonard)

### Fixes
- Fix access rights to prod logs with docker (benjaminjonard)
- Fix error when trying to delete a template linked to a collection (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies (benjaminjonard)
- Increase date picker year range + small UI fixes (benjaminjonard)
- Rely on HTML lazy loading for images instead of JS (benjaminjonard)
- Fix docker compose file for dev environment (punfil)

## [1.4.5] / 2023-06-01
### Fixes
- Fix broken related items on item edit page (benjaminjonard)

### Miscellaneous
- Update to Symfony 6.3 (benjaminjonard)

## [1.4.4] / 2023-05-30
### Features
- Add "Remove" and "Cancel" buttons on thumbnail preview (benjaminjonard) 
- Add option for admin to customize Light theme and Dark theme CSS (benjaminjonard)
- Add option for admin to change the thumbnails format, requires to regenerate existing thumbnails by executing `php bin/console app:regenerate-thumbnails` in Koillection root directory. Backing up your uploads directory before regeneration is advised. (benjaminjonard)
- Add support for AVIF image format (benjaminjonard)

### Fixes
- Fix broken special characters in autocompletes when pressing enter (benjaminjonard)

### Miscellaneous
- Update materialize library (follows Material Design 3), visual updates for forms (benjaminjonard)
- Add more context to commands (benjaminjonard)
- Move Docker base image from Debian to Ubuntu (benjaminjonard) 
- Add a link to API documentation in right menu (benjaminjonard)
- Rework translations files, should make the translating process easier (benjaminjonard)
- Upgrade PHP and JS dependencies (benjaminjonard)

## [1.4.3] / 2023-04-26
### Features
- Add a setting to enable search in data by default (benjaminjonard)

### Fixes
- Fix collection's custom labels for items and sub-collections (benjaminjonard)
- Fix autocomplete errors when using special characters (benjaminjonard)

### Miscellaneous
- Display related items using the thumbnail (benjaminjonard)
- Upgrade PHP and JS dependencies (benjaminjonard)

## [1.4.2] / 2023-03-29
### Features
- Add checkbox field type (benjaminjonard)
- Add UPLOAD_MAX_FILESIZE and PHP_MEMORY_LIMIT env variables (benjaminjonard)

### Miscellaneous
- Display datum file size (benjaminjonard)
- Rework dark theme (benjaminjonard)
- Use default browser theme when necessary (benjaminjonard)
- Upgrade PHP and JS dependencies (benjaminjonard)
- Move Docker files from koillection/koillection-docker (benjaminjonard)

## [1.4.1] / 2023-02-04
### Fixes
- Fix item name suggestion when using special characters (benjaminjonard)
- Fix error when removing a Price field (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies, fix CVE-2022-24894 and CVE-2022-24895 (benjaminjonard)

## [1.4.0] / 2023-01-23
### Fixes
- Add missing table koi_choice_list to SQL export (benjaminjonard)
- Fix error when refreshing cached values if parent is null (benjaminjonard)

### Miscellaneous
- Update to PHP 8.2 (benjaminjonard)
- Remove support for multipart/form-data in API POST endpoints, please use dedicated upload endpoints instead (benjaminjonard)

## [1.3.18] / 2023-01-13
### Features
- Add option search in metadata for items and collections (benjaminjonard)
- Add German translation (derfuttich)

### Fixes
- Fix error on list view if no columns are selected (benjaminjonard)
- Restore 'Add user' button (benjaminjonard)
- Properly display user role in admin page (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies, update to Symfony 6.2, fix CVE-2022-46175 (benjaminjonard)

## [1.3.17] / 2022-11-18
### Features
- Search now available on shared pages (benjaminjonard)
- Rework search page, now results are split into tabs (benjaminjonard)

### Miscellaneous
- Finish catching up with writing functionnal/unit tests (benjaminjonard)
- Upgrade PHP and JS dependencies, fix CVE-2022-37601 (benjaminjonard)

## [1.3.16] / 2022-10-14
### Fixes
- Fix bug in counters when deleting an item from a collection (benjaminjonard)
- Fix new tags being created without display mode settings (benjaminjonard)

### Miscellaneous
- Rewrite all functional tests to make them easier to maintain (benjaminjonard)

## [1.3.15] / 2022-10-07
### Features
- Support for MariaDB (benjaminjonard)

## [1.3.14] / 2022-10-01
:warning: If after this update you have incoherent collections/items counters, head to the Administration page and click on "Refresh caches" 

### Features
- Add children (sub-collections) list display mode (benjaminjonard)
- Add list display mode for collections, albums and wishlists index pages (benjaminjonard)
- Add option to display or hide actions, visibility, number of items and number of children columns in list display mode (benjaminjonard)
- Add sorting on number of items and number of children for list display mode (benjaminjonard)
- Add Price field type. Price fields with the same label will be added up and displayed on collection page (benjaminjonard)

### Miscellaneous
- Upgrade PHP and JS dependencies, fix CVE-2022-39261 (benjaminjonard)
- Upgrade to API Platform 3 (benjaminjonard)
- Rework cached counters (benjaminjonard)
- Externalize translation process on https://crowdin.com/project/koillection (benjaminjonard)
    
## [1.3.13] / 2022-09-12
### Features
- Add possibility to order columns in list display mode (benjaminjonard)
- Add possibility to hide actions and visibility in list display mode (benjaminjonard)

### Fixes
- Fix sql export in admin panel (benjaminjonard)
- Fix error on collection edit (benjaminjonard)

### Miscellaneous
- Make table headers sticky (benjaminjonard)

## [1.3.12] / 2022-08-29
### Fixes
- Fix empty logs in production environment (benjaminjonard)
- Add missing migration for new locale values (benjaminjonard)

## [1.3.11] / 2022-08-28
### Features
- Add Spanish translation (crishnakh)
- Add new API endpoints for uploads (benjaminjonard)

### Fixes
- Make link properly clickable in list display mode (benjaminjonard)
- Properly display empty date metadata (benjaminjonard)

### Miscellaneous
- Small improvements for search (benjaminjonard)
- Remove phpunit and composer binaries (benjaminjonard)
- Clean up translation files (benjaminjonard)

## [1.3.10] / 2022-08-03
### Features
- Search will now use metadata (benjaminjonard)

### Fixes
- Fix decoding of lists metadata (benjaminjonard)
- Fix error when adding a new tag to an item (benjaminjonard)

## [1.3.9] / 2022-07-29
### Features
- Add possibility to order choices in choice list form (benjaminjonard)

### Fixes
- Fix choice list not loading when using default item template from a collection (benjaminjonard)
- Fix choice list not displaying choices containing special characters (benjaminjonard)
- Fix errors on gif upload (benjaminjonard)

## [1.3.8] / 2022-07-28
### Fixes
- Fix error when submitting forms with "date" metadata (benjaminjonard)

## [1.3.7] / 2022-07-27
### Features
- Add possibility to choose which columns to display in collection list view based on items metadata (benjaminjonard)
- Add new choice list type (benjaminjonard)
- Add possibility to order s collection's items on text, list and country metadata types (benjaminjonard)

### Miscellaneous
- Update JS dependencies, fixes CVE-2022-25858 (benjaminjonard)
- Remove update logs, add create/delete logs for more entities (benjaminjonard)

## [1.3.6] / 2022-07-20
### Features
- Add sorting feature on collections; sorting is available on item name and data of type Date, Number and Rating (benjaminjonard)
- Add textarea (long text) data type (benjaminjonard)
- Add an items default template field in collection form (benjaminjonard)

### Fixes
- Fix missing DateFormat in enum (benjaminjonard)
- Fix wrong column label on wishes list (benjaminjonard)
- Properly handle currency display based on user locale (benjaminjonard)
- Fix broken path to shared items in a tag (benjaminjonard)
- Improve thumbnails generation, now properly takes image EXIF orientation into account (benjaminjonard)

### Miscellaneous
- Api - add possibility to directly link a Datum to an item or a collection (benjaminjonard)
- Improve english translation (benjaminjonard)
- Update JS and PHP dependencies (benjaminjonard)

## [1.3.5] / 2022-07-07
### Features
- Add a list view for items in collections, tags and albums (benjaminjonard)
 
### Fixes
- Prevent unnecessary image re-upload when submitting forms containing base64 images (benjaminjonard)
- Fix swipe gesture on item page (benjaminjonard)

### Miscellaneous
- Update JS and PHP dependencies (benjaminjonard)

## [1.3.4] / 2022-06-27
### Fixes
- Fix zip exports if user uploads directory doesn't exist (benjaminjonard)
- Fix error when displaying item with empty file or country fields (benjaminjonard)
- Remove Administration entry in menu on mobile for non admin users (benjaminjonard)
- Fix completion percent on inventories (benjaminjonard)
- Handle division by zero in inventories when a collection has no items (benjaminjonard)

### Miscellaneous
- Update JS and PHP dependencies (benjaminjonard)

## [1.3.3] / 2022-05-04
### Features
- Add new Link type (benjaminjonard)

### Fixes
- Fix CleanUp and RegenerateThumbnails commands (benjaminjonard)

### Miscellaneous
- Update JS and PHP dependencies, fixing all current CVEs (benjaminjonard)

## [1.3.2] / 2022-03-21
### Fixes
- Fix image upload in additional data (benjaminjonard)

### Miscellaneous
- Add display password icon on login page (benjaminjonard)
- Multi-arch image for docker, now supports ARM (benjaminjonard)

## [1.3.1] / 2022-02-20
### Fixes
- Fix date pickers not displaying (benjaminjonard)
- Fix bug where last common field was incorrectly generated (benjaminjonard)
- Allow empty dates when loading common fields (benjaminjonard)

### Miscellaneous
- Update JS dependencies (benjaminjonard)
- Add pgadmin container to example configuration (Zwordi)

## [1.3.0] / 2022-02-13
### Features
- Add a basic REST API, documentation is accessible on /api (benjaminjonard)
- Enhance visibility mechanism: takes parent visibility into account without erasing own visibility (benjaminjonard)
- Display visibility level icon on each page (benjaminjonard)

### Fixes
- Fix first connection auto-login error (benjaminjonard)
- Fix exception loop (benjaminjonard)

### Miscellaneous
- Display flags as emoji instead of images (benjaminjonard)
- Rework error pages (benjaminjonard)
- Update PHP and JS dependencies, fix current CVEs (benjaminjonard)
- Restore asset preloading (benjaminjonard)

## [1.2.5] / 2021-12-20
### Fixes
- Fix duplicates from tags list, order alphabetically (benjaminjonard)
- Fix tag edition (benjaminjonard)
- Fix sql dump (benjaminjonard)

### Miscellaneous
- Update JS dependencies (benjaminjonard)
- Update PHP dependencies (benjaminjonard)
- Remove built-in assets (benjaminjonard)
- Admin: split backup function into two separated buttons (benjaminjonard)

## [1.2.4] / 2021-09-16
### Features
- Allow user to change username and email in their profile (benjaminjonard)

### Fixes
- Fix divisions by zero when inventory is empty (benjaminjonard)
- Fix delete forms (benjaminjonard)

### Miscellaneous
- Preload assets (benjaminjonard)
- Update JS dependencies, fix CVE-2021-33587 (benjaminjonard)
- Update PHP dependencies (benjaminjonard)

## [1.2.3] / 2021-05-31
### Fixes
- Fix date format (benjaminjonard)

### Miscellaneous
- Update JS, fix CVE-2021-32640 and CVE-2021-23386 (benjaminjonard)
- Update to Symfony 5.3 (benjaminjonard)

## [1.2.2] / 2021-05-22
### Fixes
- Fix select lists on first connection page (benjaminjonard)

## [1.2.1] / 2021-05-17
### Features
- Add description to visibilities (benjaminjonard)

### Fixes
- Fix update date on all pages (benjaminjonard)

### Miscellaneous
- Update JS and PHP dependencies, fix CVE-2021-21424 (benjaminjonard)

## [1.2.0] / 2021-05-03
### Features
- Add "rating" field type (benjaminjonard)
- Add "number" field type (benjaminjonard)
- Add new "Only to authenticated users" visibility (benjaminjonard)
- Add some activity counters on admin dashboard (benjaminjonard)
- Display a link to item's collection when viewing it from a tag page (benjaminjonard)
- Add return to collection button when viewing another user collection (benjaminjonard)

### Fixes
- Fix release checking (benjaminjonard)
- Fix breadcrumb on wish edit (benjaminjonard)
- Fix item name guesser (benjaminjonard)
- Prioritize thumbnails when available (benjaminjonard)

### Miscellaneous
- Add production ready docker setup example with Traefik and SSL (Zwordi)
- Move all Javascript to Stimulus, remove jQuery (benjaminjonard)
- Update PHP version requirement to 8.0 (benjaminjonard)
- Update JS and PHP dependencies (benjaminjonard)

## [1.1.7] / 2021-01-27
### Features
- Add a message in admin interface if a new release of Koillection is available  (benjaminjonard)
- Add a new field type for dates on item page (benjaminjonard)
- Add .gif support for uploads (benjaminjonard)
- Add the possibility to add related items to an item (benjaminjonard)

### Miscellaneous
- Update JS and PHP dependencies (benjaminjonard)
- Trim white spaces on tag autocomplete (benjaminjonard)
- Change text color and font weight on autocompletes (benjaminjonard)
- Multiple small dark mode improvements (benjaminjonard)
- Add a CONTRIBUTING.md file (benjaminjonard)
- Add some new counters in admin dashboard (benjaminjonard)
- Remove preload support as it was dropped by Chrome (benjaminjonard)

### Fixes
- Fix social media metas (benjaminjonard)

## [1.1.6] / 2020-10-17
### Features
- Functionalities can now be activated/deactivated in settings page (benjaminjonard)
- Dark mode can be automatically activated between two hours (benjaminjonard)
- On mobile devices, user can swipe left or right to navigate between items (benjaminjonard)
- Add a "remember me" option on login page (benjaminjonard)
- Keep tag context when navigating tag's items instead of switching to the item's collection (benjaminjonard)

### Miscellaneous
- Rework profile and settings pages (benjaminjonard)
- Move search bar at the top of the left menu (benjaminjonard)
- Remove themes for now (benjaminjonard)
- Improve dark mode (benjaminjonard)
- Update JS and PHP dependencies (benjaminjonard)

### Fixes
- Fix album breadcrumb (benjaminjonard)
- Fix preload for assets (benjaminjonard)

## [1.1.5] / 2020-09-21
### Fixes
- Update migration configuration to comply with the new syntax introduced in doctrine/doctrine-migrations-bundle v3.0 (benjaminjonard)

### Miscellaneous
- Update JS and PHP dependencies (benjaminjonard)

## [1.1.4] / 2020-09-11
### Fixes
- Fix clean up function (benjaminjonard)
- Fix all deprecations (benjaminjonard)

### Miscellaneous
- Update PHP dependencies to fix high severity alert on symfony/http-kernel (benjaminjonard)
- Update JS dependencies (benjaminjonard)

## [1.1.3] / 2020-08-14
### Features
- New file field for items and collections (benjaminjonard)

### Fixes
- Fix multiple data display on item page (benjaminjonard)
- Fix links in underlined tags (benjaminjonard)
- Properly handle context in tag links (benjaminjonard)

### Miscellaneous
- Update PHP and JS dependencies (benjaminjonard)

## [1.1.2] / 2020-07-30
### Features
- Collections can have additional fields like items (benjaminjonard)
- Add an autocomplete on search in the header (benjaminjonard)
- Rework additional fields creation (benjaminjonard)

### Fixes
- Fix upload on item data (benjaminjonard)
- Fix detection of common fields on item data (benjaminjonard)
- Fix tag search (benjaminjonard)
- Fix locale dropdown on login page (benjaminjonard)
- Prevent default selection on country fields (benjaminjonard) 

### Miscellaneous
- Handle echarts properly through yarn (benjaminjonard)
- Update JS dependencies (benjaminjonard)
- Update PHP dependencies (benjaminjonard)

## [1.1.1] / 2020-06-25
### Features
- Add a button in admin panel to regenerate thumbnails (benjaminjonard)

### Fixes
- Fix locale updates (benjaminjonard)
- Fix wish transfer to collection form (benjaminjonard)

### Miscellaneous
- Upgrade to Symfony 5.1  (benjaminjonard)
- Improve dark mode (benjaminjonard)
- Fix vulnerabilities in JS dependencies (benjaminjonard)

## [1.1.0] / 2020-04-30
### Important
- Update PHP minimum version to 7.4 (benjaminjonard)
- Update to Symfony 5 (benjaminjonard)
- Update to Twig 3 (benjaminjonard)
- Add support of Mysql for version 8 and superior (benjaminjonard)

### Features
- Dark mode (benjaminjonard)
- Suggest new item name based on other items in the collection (benjaminjonard)
- Webp format support for images (benjaminjonard)
- Support for all currencies (benjaminjonard)
- Add a field `Country` for items (benjaminjonard)
- Users can choose how dates should be displayed (benjaminjonard)
- Add Inventory (benjaminjonard)
- Tags can now be assigned to a category (benjaminjonard)
- Admins can do a full backup : database + images (benjaminjonard)
- Admins can clean obsoletes images (benjaminjonard)
- Tag autocompletion will now priotarize words starting the same way as the word searched (benjaminjonard)
- Add metadata for content sharing on social networks (benjaminjonard)
- Use select2 for all dropdown lists (benjaminjonard)
- Add 'See more' button on related tags (benjaminjonard)
- Add history for Albums and Wishlists (benjaminjonard)
- Add possibility to create child albums (benjaminjonard)
- Search now search in Albums and Wishlists (benjaminjonard)
- Urls now depends on user's locale (benjaminjonard)
- Merge Profile and Settings menus (benjaminjonard)

### Miscellaneous
- Rework responsive on forms (benjaminjonard)
- Rework modals (benjaminjonard)
- Rework counters (benjaminjonard)
- Rework file upload system (benjaminjonard)
- Add indexes on `visibility` property (benjaminjonard)
- Lazy load Doctrine listeners (benjaminjonard)

### Fixes
- Avatars are now correctly deleted (benjaminjonard)
- Fix floating label on croppers when using Firefox (benjaminjonard)
- Fix streamed responses when downloading backups (benjaminjonard)
- Fix infinite loading bar (benjaminjonard)

## [1.0.4] / 2019-03-06
### Fixes
 - Improve header responsiveness (benjaminjonard)
 
### Features
 - Add a scrollbar on navbar for mobile devices (benjaminjonard)
 - Add hidden menu for mobile devices (benjaminjonard)
 
### Miscellaneous
- Update dependencies and config files (benjaminjonard)
- Switch to the new .env system (with .env.local) (benjaminjonard)
- Update phpUnit version (benjaminjonard)

## [1.0.3] / 2019-02-26
### Fixes
- Fix recompute disk usage action (benjaminjonard)
- Fix collection placeholder style (benjaminjonard)
- Fix item images margins (benjaminjonard)
- Fix signs linked to a private item (benjaminjonard)
- Fix date search (benjaminjonard)
- Fix deprecations on transchoices (benjaminjonard)
- Fix twig deprecations in blocks (benjaminjonard)

### Features
- Add action filter on history page (benjaminjonard)
- Use referer for redirection after user login (benjaminjonard)

### Miscellaneous
- Upgrade Encore version (benjaminjonard)
- Upgrade to Symfony 4.2 (benjaminjonard)
- Update some english translations for proper plurals handling (benjaminjonard)
- Update general style for the header (benjaminjonard)
- Rework search input style (benjaminjonard)
- Replace all generateUrl with redirectToRoute (benjaminjonard)
- Replace deprecated TranslatorInterface (benjaminjonard)
- Add debug env for local docker (benjaminjonard)

 
## [1.0.2] / 2018-09-28
### Fixes
- Fix error pages style (benjaminjonard)
- Fix sortable fields on item edition page (benjaminjonard)
- Fix custom field selection on item edition page (benjaminjonard)

## [1.0.1] / 2018-09-18
### Features
- Add SQL export (benjaminjonard)
- Add images export (benjaminjonard)
- Add tooltips on some images (benjaminjonard)

### Miscellaneous
- Update php dependencies (benjaminjonard)
- Fix deprecations (benjaminjonard)
- Switch from Gulp to Symfony Encore for managing assets (benjaminjonard)
- Update materializecss to v1.0.0 (benjaminjonard)
- Remove FOSJsRoutingBundle (benjaminjonard)
- Remove unused FontAwesome icons and inline the others (benjaminjonard)

### Refactoring
- Move javascript from Twig template to a JS file on statistics page (benjaminjonard)

## [1.0.0] / 2018-07-31
- Initial release (benjaminjonard)
