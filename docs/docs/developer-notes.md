# Developer Notes

This plugin makes use of Composer to autoload classes. Because this is a WordPress plugin and it would be awkward for users to have to run Composer, especially on shared servers, this plugin has all of the dependencies included with it. This prevents end-users from having to run `composer install` or `composer update` to get them.

Some extra classes are autoloaded using the classmap autoloader. Please check in the `composer.json` file to see while classes are loaded in that way.
