<html>
<head>
<title>edu-sharing metadata import</title>
<style type="text/css" id="vbulletin_css">
body
{
    background: #e4f3f9;
    color: #000000;
    font: 11pt verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
    margin: 5px 10px 10px 10px;
    padding: 0px;
}
table
{
    background: #e4f3f9;
    color: #000000;
    font: 10pt verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
    margin: 5px 10px 10px 10px;
    padding: 0px;
}
p
{
    margin: 10px;
    padding: 20px;
    background: #AEF2AC;
    
}
fieldset{
    margin: 10px;
    border: 1px solid #ddd;
}
</style>
</head>
<body>
<?php

// customize 
define('import_metadata',true);



require_once(dirname(dirname(dirname(__FILE__))).'/config.php');


define ('CC_CONF_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define ('CC_CONF_APPFILE','ccapp-registry.properties.xml');

if (!import_metadata) die('metadata import disabled');

require_once ($CFG->dirroot.'/mod/edusharing/lib/ESApp.php');
require_once ($CFG->dirroot.'/mod/edusharing/lib/EsApplications.php');
require_once ($CFG->dirroot.'/mod/edusharing/lib/EsApplication.php');

function check_permission(){

    $path =  dirname(__FILE__);
    $app_file = $path.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'esmain'.DIRECTORY_SEPARATOR.'x.txt';
    $fh = fopen($app_file, 'w');// or die("Can't open file");
    if (!$fh){
      echo "please check write permissions on ".$path.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'esmain';
      die();
    };
    unlink($app_file);
    fclose($fh);

}

check_permission();


function getLoginDataForm($filename){
    $form = '
    <form action="import_metadata.php" method="post">
  <fieldset>
    <legend>login information</legend>
    <table>
      <tr>
        <td><label for="name">name:</label></td>
        <td><input type="text" size="30" id="name" name="name" value="">
      </tr>
      <tr>
        <td><label for="pwd">password:</label></td>
        <td><input type="text" size="30" id="pwd" name="pwd" value="">
      </tr>
      <tr>
        <td></td>
        <td><input type="hidden"  id="file" name="file" value="'.$filename.'">
        <input type="submit" value="send"></td>
      </tr>
    </table>
  </fieldset>
</form>';

  return $form;

    }


function getForm($url){

    $form = '
    <form action="import_metadata.php" method="post" name="mdform">
  <fieldset>
    <legend>import application metadata</legend>
    <table>
      <tr>
        <td colspan="2">
        example metadata endpoints:<br>
            <table>
              <tr>
                <td>repository: </td><td><a href="javascript:void();" onclick="document.forms[0].mdataurl.value=\'http://your-server-name/edu-sharing/metadata?format=lms\'">http://edu-sharing-server/edu-sharing/metadata?format=lms</a><br></td>
          </tr>
          <!-- tr>
          <td>renderservice:</td><td> <a href="javascript:void();" onclick="document.forms[0].mdataurl.value=\'http://your-server-name/esrender/application/esmain/metadata.php\'">http://edu-sharing-server/esrender/application/esmain/metadata.php</a><br></td>
          </tr -->
        </td>
      </tr>
      <tr>
        <td><label for="metadata">metadata endpoint:</label></td>
        <td><input type="text" size="80" id="metadata" name="mdataurl" value="'.$url.'">
        <input type="submit" value="import"></td>
      </tr>
    </table>
  </fieldset>
</form>';

  return $form;

    }

if (!empty($_POST['name']) && !empty($_POST['pwd']) && !empty($_POST['file']) )
{
    $change_name = false;
    $change_pwd = false;
        $path =  dirname(__FILE__);
        $app = new DOMDocument();
    $app_file = $path.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'esmain'.DIRECTORY_SEPARATOR.$_POST['file'];
        $app->load($app_file);
        $app->preserveWhiteSpace = false;

        $entrys = $app->getElementsByTagName('entry');
        foreach ($entrys as $entry)
        {
            if ($entry->getAttribute('key')=='password')
            {
            if ($entry->nodeValue=="") {
                $entry->nodeValue=$_POST['pwd'];
                $change_pwd = true;
                }
        };
            if ($entry->getAttribute('key')=='username')
            {
            if ($entry->nodeValue=="") {
                $entry->nodeValue=$_POST['name'];
            $change_name = true;
          }
        };

        };
if ($change_name && $change_pwd) {
    $app->save($app_file);
  echo ('<p>set userdata for repository <br></p>');
  }
  else {
  echo ('<p>name and password already set / make no changes <br></p>');
  };
};



$config_access = false;
$filename='';

if (!empty( $_POST['mdataurl'])){


        $xml = new DOMDocument();

$internal_errors = libxml_use_internal_errors(true);

      if ($xml->load($_POST['mdataurl']) == false){

            echo ('<p style="background: #FF8170">could not load '.$_POST['mdataurl'].' please check url')."<br></p>";
      echo getForm($_POST['mdataurl']);
        die();
        }else {

            };

libxml_use_internal_errors($internal_errors);

        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        $entrys = $xml->getElementsByTagName('entry');
        $repoInfo = array();
        foreach ($entrys as $entry){
            if ($entry->getAttribute('key')=='appid'){
            $filename = "app-".$entry->nodeValue.".properties.xml";
             }
            if ($entry->getAttribute('key')=='type'){
               // for moodle not necessary
                        //if ($entry->nodeValue=='REPOSITORY') $config_access = true;
             }
            if ($entry->getAttribute('key')=='password' && $config_access)
            {
            if ($entry->nodeValue=="") $config_access = true;
        };
            if ($entry->getAttribute('key')=='username' && $config_access)
            {
            if ($entry->nodeValue=="") $config_access = true;
        };
            
            $repoInfo[$entry->getAttribute('key')] = $entry->nodeValue;

            }

    $path =  dirname(__FILE__);

    $file = $path.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'esmain'.DIRECTORY_SEPARATOR.$filename;

    $app_reg = new DOMDocument();
    $app_reg_file = $path.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'esmain'.DIRECTORY_SEPARATOR.'ccapp-registry.properties.xml';
    $app_reg_file_dist = $path.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'esmain'.DIRECTORY_SEPARATOR.'ccapp-registry.properties.xml.dist';

    if ( file_exists($app_reg_file)){
    // ok
    } else
    { 
      if ( file_exists($app_reg_file_dist)){
        $app_reg_file_dist = $path.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'esmain'.DIRECTORY_SEPARATOR.'ccapp-registry.properties.xml.dist';
        
        @copy ( $app_reg_file_dist , $app_reg_file );
         }
    }

    $app_reg->load($app_reg_file);
        $app_reg->preserveWhiteSpace = false;

        $entrys = $app_reg->getElementsByTagName('entry');
        foreach ($entrys as $entry)
        {
            if ($entry->getAttribute('key')=='applicationfiles')
            {
            if (!strrpos($entry->nodeValue,$filename))
            {
              $app_reg->save($app_reg_file.'_'.time().'.bak');
            $entry->nodeValue = $entry->nodeValue.','.$filename;

              $props = $app_reg->getElementsByTagName('properties');
          $props->item(0);
          $comment = $app_reg->createElement('comment','added new trusted app '.$filename);
            $app_reg->save($app_reg_file);
              $xml->save($file);
          echo "<p>import successful: ".$filename.' added and add to registry (ccapp-registry.properties.xml)<br><br></p>';
        }else
        {
          echo "<p>application already registered: ".$filename.' check  (ccapp-registry.properties.xml)<br><br></p>';

            }
            };
    };
    
    
    
        $app_home = $path.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'esmain'.DIRECTORY_SEPARATOR.'homeApplication.properties.xml';
    $app_home_dist = $path.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'esmain'.DIRECTORY_SEPARATOR.'homeApplication.properties.xml.dist';

    if ( file_exists($app_home)){
    // ok
    } else
    { 
      if ( file_exists($app_home_dist)){
         @copy ( $app_home_dist , $app_home );
          
          
        $homeApp = new DOMDocument();
        $homeApp -> load($app_home);
        $homeApp -> preserveWhiteSpace = false;
        $entrys = $homeApp -> getElementsByTagName('entry');
    
        require_once(dirname(__FILE__) . '/AppPropertyHelper.php');
        $appPropertyHelper = new AppPropertyHelper();
        $sslKeypair = $appPropertyHelper -> getSslKeypair();
        
        foreach($entrys as $entry) {
            switch($entry -> getAttribute('key')) {
                case 'host':
                    $entry -> nodeValue = $_SERVER['SERVER_ADDR'];
                break;
                case 'appid':
                    $entry -> nodeValue = uniqid('moodle_');
                break;
                case 'homerepid':
                    $entry -> nodeValue = $repoInfo['appid'];
                break;
                case 'contenturl':
                    $entry -> nodeValue = $repoInfo['contenturl'];
                break;
                case 'cc_webservice_url':
                    $entry -> nodeValue = str_replace('/usage', '', $repoInfo['usagewebservice']);
                break;
                case 'alfresco_webservice_url':
                    $entry -> nodeValue = $repoInfo['alfresco_webservice_url'];
                break;
                case 'cc_gui_url':
                    $entry -> nodeValue = str_replace('/services/usage', '', $repoInfo['usagewebservice']);
                break;
                case 'private_key':
                    $entry -> nodeValue = $sslKeypair['privateKey'];
                break;
                case 'public_key':
                    $entry -> nodeValue = $sslKeypair['publicKey'];
                break;
                case 'signatureRedirector':
                    $entry -> nodeValue = $appPropertyHelper -> getSignatureRedirector();
                break;
            }
        }
        $homeApp -> save($app_home);
          
          
         }
    }

    $auth_srv = $path.DIRECTORY_SEPARATOR.'services'.DIRECTORY_SEPARATOR.'authentication.wsdl';
    $auth_srv_dist = $path.DIRECTORY_SEPARATOR.'services'.DIRECTORY_SEPARATOR.'authentication.wsdl.dist';
    if ( file_exists($auth_srv)){
    // ok
    } else { 
        if (file_exists($auth_srv_dist)) {
            @copy($auth_srv_dist, $auth_srv);
            $auth = new DOMDocument();
            $auth -> load($auth_srv);
            $auth -> preserveWhiteSpace = false;
            $address = $auth -> getElementsByTagName('address') -> item(0);
            $address -> setAttribute('location', $CFG->wwwroot.'/mod/edusharing/services/authentication.php');
            $auth -> save($auth_srv);
         }
    }
    
    $perm_srv = $path.DIRECTORY_SEPARATOR.'services'.DIRECTORY_SEPARATOR.'permission.wsdl';
    $perm_srv_dist = $path.DIRECTORY_SEPARATOR.'services'.DIRECTORY_SEPARATOR.'permission.wsdl.dist';
    if ( file_exists($perm_srv)){
    // ok
    } else { 
        if (file_exists($perm_srv_dist)) {
            @copy($perm_srv_dist, $perm_srv);
            $auth = new DOMDocument();
            $auth -> load($perm_srv);
            $auth -> preserveWhiteSpace = false;
            $address = $auth -> getElementsByTagName('address') -> item(0);
            $address -> setAttribute('location', $CFG->wwwroot.'/mod/edusharing/services/permission.php');
            $auth -> save($perm_srv);
         }
    }
    
    
    
};
    

if ($config_access){
  echo getLoginDataForm($filename);
    die();
    }

echo getForm('');
die();
