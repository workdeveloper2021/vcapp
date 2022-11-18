<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

//error code
define('STATUS_AUTHORIZATION_CODE', 401);
define('ERROR_AUTHORIZATION_CODE', 401);
define('EMAILNOTVERIFED', 468);
define('MOBILENOTVERIFIED', 469);
define('ERROR_FAILED_CODE', 105);
define('HTTP_OK', 200);
define('HTTP_NOT_FOUND', 404);
define('HTTP_NOT_MODIFIED', 304);


// Email Functions 

define('SITE_NAME', 'Virtual Catalogue');
define('SITE_EMAIL', 'no-reply@consagous.co');
define('PROTOCOL', 'smtp');       // mail, sendmail, smtp
define('SMTP_HOST','smtp.gmail.com');   // your smtp host e.g. smtp.gmail.com
define('SMTP_PORT','465');          // your smtp port e.g. 25, 587
define('SMTP_CRYPTO','ssl');          // your smtp port e.g. 25, 587
define('SMTP_USER','no-reply@consagous.co');		// your smtp user 
define('SMTP_PASS','@Consagous@123@');  // your smtp password
define('MAIL_PATH','/usr/sbin/sendmail');
define('AdminEmail','prathak.godawat@consagous.com');
define('FROM_EMAIL','no-reply@consagous.com'); // your smtp user



// Table Name Constant

define('TABLE_USERS','user');
define('TABLE_MANAGE_ROLES','manage_roles');
define('TABLE_USER_ROLES','user_role');
define('TABLE_OPTIONS','options');

define('TABLE_COMPANIES','manage_company_list');




// Media Constant

define('MEDIA_VIDEO','MP4|AVI|3GP|3GPP|mp4|avi|3gp|3gpp');
define('MEDIA_PICTURE','JPG|JPEG|PNG|jpg|jpeg|png');
define('MEDIA_AUDIO','MP3|WAV|mp3|wav|avi');
define('MEDIA_PDF','PDF|pdf');
define('MEDIA_PODCAST','MP3|WAV|mp3|wav');
define('MEDIA_URL','url');



// Pagination

define('COMPANY_PER_PAGE', 50);
define('SHOWROOM_PER_PAGE', 50);
define('PRODUCT_CATEGORIES_PER_PAGE', 50);
define('PRODUCT_PER_PAGE', 50);






// Permission 
define('ADD', '0');
define('EDIT', '1');
define('VIEW', '2');
define('DELETE', '3');
define('STATUS', '4');

$base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
$base_url .= "://". @$_SERVER['HTTP_HOST'];
$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);

define('FRONT_URL',$base_url);
define('IMAGE_URL',$base_url.'/uploads/');
define('SITE_TITLE','VirJual:Digital Showrooms');
define('CURRENCY','C$');



// VIMEO SECRET KEYS 'LOCAL' (keys generated by email - kirtisagar.prajapat@consagous.com)
define('VIMEO_CLIENT_ID','6ac70c833f758b40321091774253dbc6f31eaf2c');

define('VIMEO_CLIENT_SECRET','2AfcryXCThMyOgIFVg3gwE/30sRG6MfM8y+1Sp//iEY1/bReClgGnxXlWfr6AEExJJ+VmP7wZnsI+dnjYQ2vxGofj9WGJ1xTs5XFLiscZwFmUqGnQyIo69Pbpq/2LUKh');

define('VIMEO_ACCESS_TOKEN','22dd2f596572b78cfc3f0a415ae287ae');



//FOR CLOVER PAYMENT For USA
define('CLOVER_KEY_USA','af2bbe3c4b4dd3682793cc09155a9a7a');
define('MERCHANT_ID_USA','RKMDWMMA611F1');
define('ACCESS_TOKEN_USA','24a0bbef-8ef3-657b-9449-4b01c158d928');
define('CURRENCY_CODE_USA','USD');

define('PAGE_LIMIT', 10);

//FOR CLOVER PAYMENT For CAD
define('CLOVER_KEY_CAD','af2bbe3c4b4dd3682793cc09155a9a7a');
define('MERCHANT_ID_CAD','RKMDWMMA611F1');
define('ACCESS_TOKEN_CAD','24a0bbef-8ef3-657b-9449-4b01c158d928');
define('CURRENCY_CODE_CAD','CAD');


define('CLOVER_BASE_URL','https://apisandbox.dev.clover.com/');

define('CLOVER_CARD_BASE_URL','https://sandbox.dev.clover.com/v3');
define('CLOVER_BASE_URL_NEW','https://scl-sandbox.dev.clover.com/v1');
define('CLOVER_TOKEN_URL','https://token-sandbox.dev.clover.com/v1/tokens');
