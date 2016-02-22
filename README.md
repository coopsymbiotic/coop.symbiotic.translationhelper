CiviCRM Translation Helper
==========================

TODO description.

To get the latest version of this module:  
https://github.com/coopsymbiotic/coop.symbiotic.translationhelper/issues

Distributed under the terms of the GNU Affero General public license (AGPL).
See LICENSE.txt for details.

The development of this extension was sponsored by:  
National Democratic Institute https://www.ndi.org

Features
--------

* Search for translatable user-configurations.
* Edit/update translations directly from the search results.
* Export as a spreadsheet for Excel/LibreOffice.
* Import from a spreadsheet using interactive upload wizard.
* Import from a spreadsheet using Drush (to help with automated installations).

Demo:

* https://www.youtube.com/watch?v=Lfs_AUEejCM

Known issues
------------

See: https://github.com/coopsymbiotic/coop.symbiotic.translationhelper/issues

Requirements
------------

- CiviCRM >= 4.6

Installation
------------

* Enable this extension in CiviCRM (Administer > System Settings > Manage Extensions)
* Two new menu items will be added in Administer > Localization.

To use Drush, since this extension is not a Drupal module, you will have to copy or symlink
the drush module to your ~/.drush/ directory.

Example: ln -s `pwd`/translationhelper.drush.inc ~/.drush/translationhelper.drush.inc

Example drush usages:

```
drush translationhelper-importfile /tmp/CiviCRM_Translations_20151209-1518.ods en_US,fr_CA --debug
```

Developer notes
---------------

This extension uses bower and composer to manage dependancies. You will have to run:

```
bower install
composer install
```

This extension uses a more recent version of jQuery DataTables
since the one included in CiviCRM 4.6 has known bugs when dealing with objects.

Brainstorm
----------

* Track strings that have not been updated yet, or need translation (would require mysql triggers on the db fields? or hook_civicrm_post?).
* Integrate with the Transifex API to search & update a string in CiviCRM (and temp override?).

Support
-------

Please post bug reports in the issue tracker of this project on github:  
https://github.com/coopsymbiotic/coop.symbiotic.translationhelper/issues

For general support questions, please use the CiviCRM Extensions forum:  
http://forum.civicrm.org/index.php/board,57.0.html

This is a community contributed extension written thanks to the financial
support of organisations using it, as well as the very helpful and collaborative
CiviCRM community.

Please consider contributing to the CiviCRM project:  
http://civicrm.org/participate/support-civicrm

While I do my best to provide volunteer support for this extension, please
consider financially contributing to support and further develop this extension.

Commercial support is available through Coop SymbioTIC:  
https://www.symbiotic.coop

Copyright
---------

License: AGPL 3

Copyright (C) 2015 Mathieu Lutfy (mathieu@symbiotic.coop)  
https://www.symbiotic.coop/en

Distributed under the terms of the GNU Affero General public license (AGPL).
See LICENSE.txt for details.
