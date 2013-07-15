# ownCloud theming

Themes make it possible to easily customize ownCloud without the need to edit the source code:

* names, titles, footers and other strings are customizable via the defaults.php
* images override existing images, so you can easily use your own logo
* CSS files are loaded additionally so you can override styles
* **NOT RECOMMENDED**: You can also override JS files and PHP templates with own versions. This will potentially break updates though so we do not recommend it.

Just place a theme in this folder with the name of the theme as folder name. You can then activate it by putting 
'theme' => 'themename', into the config.php file. The folder structure of a theme is exactly the same as the main ownCloud structure.
