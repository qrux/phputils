# phputils
PHP Utils (Ajax, Time, Logging)

Drop the contents of the 'api' and 'inc' and 'js' dirs into their respective dirs in the
web project.

The PHP portion of the project should include the inc/utils.php file:

	require_once("inc/utils.php");

The JS portion should include--AFTER jquery--the js/utils.js file:

	&lt;script type="text/javascript" src="js/utils.js"&gt;&lt;/script&gt;

API calls should be defined in api/&lt;version&gt;/index.php, as a static function in the
class 'AjaxCalls'.

    $(document).ready(function () {
    }
