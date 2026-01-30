<?php
/**
 * PHPMailer - PHP email creation and transport class.
 * PHP Version 5.5
 * @package PHPMailer
 * @see https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Brent R. Matzelle (original founder)
 * @copyright 2012 - 2017 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */
/**
 * Get an OAuth2 token from an OAuth2 provider.
 * * Install this script on your server so that it's accessible
 * as [https/http]://<yourdomain>/<folder>/get_oauth_token.php
 * e.g.: http://localhost/phpmailer/get_oauth_token.php
 * * Ensure dependencies are installed with 'composer install'
 * * Set up an app in your Google/Yahoo/Microsoft account
 * * Set the script address as the app's redirect URL
 * If no refresh token is obtained when running this file,
 * revoke access to your app and run the script again.
 */

namespace PHPMailer\PHPMailer;

/**
 * Aliases for League Provider Classes
 * Make sure you have added these to your composer.json and run `composer install`
 * Plenty to choose from here:
 * @see http://oauth2-client.thephpleague.com/providers/thirdparty/
 */
// @see https://github.com/thephpleague/oauth2-google
use League\OAuth2\Client\Provider\Google;
// @see https://packagist.org/packages/hayageek/oauth2-yahoo
use Hayageek\OAuth2\Client\Provider\Yahoo;
// @see https://github.com/stevenmaguire/oauth2-microsoft
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;

if (!isset($_GET['code']) && !isset($_GET['provider'])) {
?>
<html>
<body>
<center>
Cek login Bro, Gunakan email noreplay@smpn171jkt.sch.id :<br/>
<a href='../gmail/vendor/phpmailer/get_oauth_token.php?provider=Google'>Login</a><br/>
</center>
</body>
</html>
<?php
exit;
}

require '../autoload.php';

session_start();

$providerName = '';

if (array_key_exists('provider', $_GET)) {
    $providerName = $_GET['provider'];
    $_SESSION['provider'] = $providerName;
} elseif (array_key_exists('provider', $_SESSION)) {
    $providerName = $_SESSION['provider'];
}
if (!in_array($providerName, ['Google', 'Microsoft', 'Yahoo'])) {
    exit('Only Google, Microsoft and Yahoo OAuth2 providers are currently supported in this script.');
}

//These details are obtained by setting up an app in the Google developer console,
//or whichever provider you're using.
$clientId = '932098413787-mi88vb1nk3a5cbe6rqnvctbmvl46pitr.apps.googleusercontent.com';
$clientSecret = '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';

//If this automatic URL doesn't work, set it yourself manually to the URL of this script
//$redirectUri = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
//$redirectUri = 'http://localhost/PHPMailer/redirect';
//$redirectUri = 'http://localhost/p106/nonpd/gmail/vendor/phpmailer/get_oauth_token.php';
$redirectUri = 'https://dev.p171.net/gmail/vendor/phpmailer/get_oauth_token.php';
$params = [
    'clientId' => $clientId,
    'clientSecret' => $clientSecret,
    'redirectUri' => $redirectUri,
    'accessType' => 'offline'
];

$options = [];
$provider = null;

switch ($providerName) {
    case 'Google':
        $provider = new Google($params);
        $options = [
            'scope' => [
                'https://mail.google.com/'
            ]
        ];
        break;
    case 'Yahoo':
        $provider = new Yahoo($params);
        break;
    case 'Microsoft':
        $provider = new Microsoft($params);
        $options = [
            'scope' => [
                'wl.imap',
                'wl.offline_access'
            ]
        ];
        break;
}

if (null === $provider) {
    exit('Provider missing');
}

if (!isset($_GET['code'])) {
    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl($options);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;
// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    unset($_SESSION['provider']);
    exit('Invalid state');
} else {
    unset($_SESSION['provider']);
    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken(
        'authorization_code',
        [
            'code' => $_GET['code']
        ]
    );
    // Use this to interact with an API on the users behalf
    // Use this to get a new access token if the old one expires
     $ambiltoken = $token->getRefreshToken();
 //   echo 'Refresh Token: ', $token->getRefreshToken();
//include "../../../adm/config/secure.php";
require "../../../config/konek.php";

date_default_timezone_set("Asia/Jakarta"); 
$tgl = date("Y-m-d H:i:s");

    
    if($ambiltoken =="")
    {
    echo "<center>
    Token lama Masih Berlaku bro
    </center>";
    }
    else
    {
    $sqladmin = mysqli_num_rows(mysqli_query($sqlconn,"select * from email_token"));
		
		

		if($sqladmin>0){
		 $sql = mysqli_query($sqlconn,"update email_token set token = '$ambiltoken', tgl = '$tgl' where name='gmail'"); 
     echo '<center>Token Gmail sudah diupdate Bro..!</center>';
		 if ($sqlconn->error) {
    try {   
        throw new Exception("MySQL error $sqlconn->error <br> Query:<br> $sql", $sqlconn->errno);   
    } catch(Exception $e ) {
        echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
        echo nl2br($e->getTraceAsString());
    }
}
		
    }
    else
    {
    $name = "gmail";
    $sql = mysqli_query($sqlconn,"INSERT INTO email_token (id, token, tgl, name) VALUES ('1','$ambiltoken','$tgl','$name')");
		
		
		echo '<center>Token Sudah dapat Bro..!</center>';
		
		 if ($sqlconn->error) {
    try {   
        throw new Exception("MySQL error $sqlconn->error <br> Query:<br> $sql", $sqlconn->errno);   
    } catch(Exception $e ) {
        echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
        echo nl2br($e->getTraceAsString());
    }
}
    }
    }
}
?>
<br/>
<center>Auto Close in 5 second</center>
<script>
setTimeout(function(){
    self.close();
},5000);
</script>

