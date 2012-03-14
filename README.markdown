This is a simple lightweight PHP MVC Framework

Even if it's currently implemented in a couple of mid-sized online projects, it's far from a PRO project, so any corrections/comments are welcome!

I also know that it's kinda poorly documented, so any help is welcome too!

To put it working:

* Make sure you have apache2's mod-rewrite enabled, and your Allow Override directives enabled for your site.
* Copy "system" directory, "index.php" and ".htaccess" (this last one can be replaced by copying it's contents to apache config) to your site root.
* Create a directory named "source" on the same level of your "system" directory.
* Copy the directory structure inside "example" to "source".
* Configure the contents of the config directory according to your site directives.
* Create a controller file (a good idea is to match your default controller configuration).
* Files should ALWAYS be lowercase (home.php) but class definitions should be camelcase standard (class Home extends Controller), and that's true for ANY class in the FW
* Open your controller file and define a class matching filename (class FileName extends Controller) with an "index" method (index methods are the ones invoked when no explicit method is called in url).
* Your urls will be like "http://siteDomain/controller/method/param1/param2/..." 
* You're set to start testing!! (You then can start trying Models, Views, etc, I should upload examples later).

* A good advice is that you can set the SYSTEM_DEBUG to "true" in system/core/definitions.php to get a (very rudimentary) system loading debug log.

TO-DOS:

- Improve FW documentation.
- [DONE] Fix an answer bug regarding the names for the $_SERVER["PATH_INFO"] variable being different in the various httpd implementations.
- I use a great framework for Mailing (Swift) but we have to externalize it in opposition to adding it to our system core.
