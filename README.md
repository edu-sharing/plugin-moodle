:warning: **Warning: This is an old repository!**

  Please use the following for the current version:
  - Main-Plugin: https://github.com/edu-sharing/plugin-moodle-edu-sharing
  - Filter-Plugin: https://github.com/edu-sharing/plugin-moodle-edu-sharing-filter
  - Atto-Editor-Plugin: https://github.com/edu-sharing/moodle-atto_edusharing
  - Wokspace-Plugin (optional): https://github.com/edu-sharing/plugin-moodle-edu-sharing-workspace 
  
  ----


edu-sharing moodle-Plugin
===========================

This is a set of plugins to connect Moodle to an edu-sharing e-learning repository and it’s rendering service(s). It enables teachers to provide documents and tools stored in an edu-sharing repository as linked resources or embedded objects in all WYSIWYG fields.


This package includes the following plugins:
- A block with a button taking you to the edu-sharing workspace and the edu-sharing search engine. The workspace provides the graphical user interface of the edu-sharing repository that allows users, departments or project groups to manage their content, arrange it in folders or share it with peers. It offers your users the following features:
  - content licenses (creative commons)
  - freely configurable folder structures
  - meta-data editing and inheritance
  - version management

- The edu-sharing activity plugin which adds a new option to the activities/resource menu. Using the edu-sharing resource allows you to either pick content from the repository or upload it to a folder of the repository. You may pick which version of the content you would like to provide in the course (always the latest vs. the version you just picked).
- The edu-sharing Atto editor plug in to add the option to embed all kinds of edu-sharing content to all WYSIWYG fields. The new button will open the edu-sharing search engine and let you pick an item embed. Audio and video-files will be embedded with a player. Documents will be represented by a link. You may pick which version of the content you would like to provide in the course (always the latest vs. the version you just picked).
- [deprecated] The edu-sharing TinyMCE editor plug in to add the option to embed all kinds of edu-sharing content to all WYSIWYG fields. The new button will open the edu-sharing search engine and let you pick an item embed. Audio and video-files will be embedded with a player. Documents will be represented by a link. You may pick which version of the content you would like to provide in the course (always the latest vs. the version you just picked).
- The edu-sharing filter for the editor ensuring the editor can do its job.


Dependencies
------------

The block, filter and editor plugins all depend on the activity module.

Installation
------------

For a full documentation with screenshots of the post installation steps visit the [documentation pages](http://docs.edu-sharing.com/confluence/edp/en  ).
After installing the Plugins in short the following steps are necessary:
- connect the activity module to an edu-sharing repository (plugin settings / repository settings)
- add the edu-sharing plugin to the Atto toolbar
- activate the edu-sharing filter, set it up for moodle and html texts and push it to the first position in the filter overview list

*You will need to run an CLI update script in order for your emedded objects to be displayed correctly.
You can download the script from our GitHub page.
This script migrates edu-sharing objects inserted with TinyMCE for use with Atto.
Migrated objects are NOT editable with TinyMCE anymore  - so there is no way back. If you are unsure please make a backup.
If the script is started without parameters, all objects from all course sections will be migrated. It is also possible to specify a list of courses for which the migration will take place.
You can get instructions how to use it with "php edusharing_migrate_tinymce_atto.php -h".
It is strongly recommended to set Moodle to Maintenace Mode before migration and to backup the database!*   

Documentation
-------------

More information can be found on the [homepage](http://edu-sharing.com).

Where can I get the latest release?
-----------------------------------
You find our latest releases on our [github repository](https://github.com/edu-sharing).

Contributing
------------

If you plan to contribute on a regular basis, please visit our [community site](http://edu-sharing-network.org/?lang=en).

License
-------
Code is under the [GNU GENERAL PUBLIC LICENSE v3](./LICENSE).
