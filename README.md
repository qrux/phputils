# phputils
PHP Utils (Ajax, Time, Logging)

Drop the contents of the 'api' and 'inc' and 'js' dirs into their respective dirs in the
web project.

The PHP portion of the project should include the inc/utils.php file:

	require_once("inc/utils.php");

The JS portion should include *AFTER jquery* the js/utils.js file:

	<script type="text/javascript" src="js/utils.js"></script>

API calls should be defined in

	api/<version>/index.php

as a static function in the class 'AjaxCalls'.

Run JS body like this:

    $(document).ready(function () {
    }

(you already know this; I'm always forgetting).


## Dependencies

https://sourceforge.net/projects/phpqrcode/

https://github.com/phpseclib/phpseclib
