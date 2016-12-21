<?php
/*
 * MyBB: Christmas Lights
 *
 * File: christmas_lights.php
 * 
 * Authors: Jeremiah Johnson & Vintagedaddyo
 *
 * MyBB Version: 1.8
 *
 * Plugin Version: 1.2
 *
 * Based on http://www.schillmania.com/projects/snowstorm/
 * 
 */

// Disallow direct access to this file for security reasons

if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook('admin_config_settings_change_commit', 'christmas_lights_admin_config_settings_change_commit');

function christmas_lights_info()
{
   global $lang;

    $lang->load("christmas_lights");
    
    $lang->christmas_lights_Desc = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float:right;">' .
        '<input type="hidden" name="cmd" value="_s-xclick">' . 
        '<input type="hidden" name="hosted_button_id" value="AZE6ZNZPBPVUL">' .
        '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">' .
        '<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">' .
        '</form>' . $lang->christmas_lights_Desc;

    return Array(
        'name' => $lang->christmas_lights_Name,
        'description' => $lang->christmas_lights_Desc,
        'website' => $lang->christmas_lights_Web,
        'author' => $lang->christmas_lights_Auth,
        'authorsite' => $lang->christmas_lights_AuthSite,
        'version' => $lang->christmas_lights_Ver,
        'compatibility' => $lang->christmas_lights_Compat
    );
}

function christmas_lights_is_installed()
{

   global $db;

   $query = $db->simple_select("settinggroups", "name", "name='christmas_lights'");  
   $result = $db->fetch_array($query);

   if($result) {

	return 1;

   } else {

	return 0;

   }
	
}

function christmas_lights_install()
{
	
   global $db, $lang;

   $lang->load("christmas_lights");

   $setting_group = array(
		'gid'			=> 'NULL',
		'name' => $lang->christmas_lights_name_1,
		'title' => $lang->christmas_lights_title_1,
		'description' => $lang->christmas_lights_description_1,
		'disporder'		=> "1",
		'isdefault'		=> 'no',
	);

   $db->insert_query('settinggroups', $setting_group);

   $gid = $db->insert_id();

   $myplugin_setting = array(
		'name' => $lang->christmas_lights_name_2,
		'title' => $lang->christmas_lights_title_2,
		'description' => $lang->christmas_lights_description_2,
		'optionscode'	=> 'yesno',
		'value'			=> '1',
		'disporder'		=> 1,
		'gid'			=> intval($gid),
	);

   $db->insert_query('settings', $myplugin_setting);

   rebuild_settings();

}

function christmas_lights_activate() 
{

	//setup templates

	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';

	find_replace_templatesets(

		"header",

		'#'.preg_quote('<div id="container">').'#',

		'<div id="container">
<link rel="stylesheet" media="screen" href="{$mybb->asset_url}/inc/lights/christmaslights.css" />
<script type="text/javascript" src="{$mybb->asset_url}/inc/lights/soundmanager2-nodebug-jsmin.js"></script>
<script type="text/javascript" src="{$mybb->asset_url}/inc/lights/combo.js"></script>
<script type="text/javascript" src="{$mybb->asset_url}/inc/lights/christmaslights.js.php?bburl={$mybb->settings[\'bburl\']}"></script>
<script type="text/javascript">
var urlBase = \'./\';
soundManager.url = \'./inc/lights/\';
</script>
<div id="lights" class="lightsActive"  onclick="makeInact()"></div>'

	);
}

function christmas_lights_deactivate() 
{

	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';

	//repair templates

	find_replace_templatesets(
		"header",

		'#'.preg_quote('
<link rel="stylesheet" media="screen" href="{$mybb->asset_url}/inc/lights/christmaslights.css" />
<script type="text/javascript" src="{$mybb->asset_url}/inc/lights/soundmanager2-nodebug-jsmin.js"></script>
<script type="text/javascript" src="{$mybb->asset_url}/inc/lights/combo.js"></script>
<script type="text/javascript" src="{$mybb->asset_url}/inc/lights/christmaslights.js.php?bburl={$mybb->settings[\'bburl\']}"></script>
<script type="text/javascript">
var urlBase = \'./\';
soundManager.url = \'./inc/lights/\';
</script>
<div id="lights" class="lightsActive"  onclick="makeInact()"></div>').'#',
		''
	);

	find_replace_templatesets(
		"header",
		'#'.preg_quote('
<link rel="stylesheet" media="screen" href="{$mybb->asset_url}/inc/lights/christmaslights.css" />
<script type="text/javascript" src="{$mybb->asset_url}/inc/lights/soundmanager2-nodebug-jsmin.js"></script>
<script type="text/javascript" src="{$mybb->asset_url}/inc/lights/combo.js"></script>
<script type="text/javascript" src="{$mybb->asset_url}/inc/lights/christmaslights.js.php?bburl={$mybb->settings[\'bburl\']}"></script>
<script type="text/javascript">
var urlBase = \'./\';
soundManager.url = \'./inc/lights/\';
</script>
<div id="lights" class="lightsInactive"></div>').'#',
		''
	);
}

function christmas_lights_uninstall()
{
	global $db;

	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN ('christmas_lights_smashable')");

	$db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='christmas_lights'");

	rebuild_settings(); 

}

function christmas_lights_admin_config_settings_change_commit()
{
   global $mybb;

   require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';

   if(!$mybb->settings['christmas_lights_smashable']) { //if they want smashable off

		find_replace_templatesets(
			"header",

			'#'.preg_quote('<div id="lights" class="lightsActive"  onclick="makeInact()">').'#',
			'<div id="lights" class="lightsInactive">');
   }

   if($mybb->settings['christmas_lights_smashable']) { //if they want smashable on

		find_replace_templatesets(

			"header",

			'#'.preg_quote('<div id="lights" class="lightsInactive">').'#',
			'<div id="lights" class="lightsActive"  onclick="makeInact()">');
   }
}  

?>