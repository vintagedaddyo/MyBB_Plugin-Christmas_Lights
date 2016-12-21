<?php
/*
 * MyBB: Christmas Lights
 *
 * File: christmas_lights.php
 * 
 * Authors: Jeremiah Johnson & Vintagedaddyo & juventiner
 *
 * MyBB Version: 1.8
 *
 * Plugin Version: 1.3
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
$plugins->add_hook('pre_output_page','christmas_lights_snowstorm');
$plugins->add_hook("usercp_options_end", "christmas_lights_snowstorm_usercp");
$plugins->add_hook("usercp_do_options_end", "christmas_lights_snowstorm_usercp");

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

   if($db->field_exists("christmas_lights_showSnowstorm", "users"))
    {
        return true;
    }
    else 
    {
        return false;
    }
	
}

function christmas_lights_install()
{
	
   global $db, $lang;

   $lang->load("christmas_lights");

    // Add field for user option
    $db->query("ALTER TABLE ".TABLE_PREFIX."users ADD christmas_lights_showSnowstorm int NOT NULL default '1'");



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

      if($db->field_exists("christmas_lights_showSnowstorm", "users"))
        $db->query("ALTER TABLE ".TABLE_PREFIX."users DROP COLUMN christmas_lights_showSnowstorm");

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


function christmas_lights_snowstorm_usercp() {
    global $db, $mybb, $templates, $user, $lang;
    $lang->load('christmas_lights');
    
    if($mybb->request_method == "post")
    {
        $update_array = array(
            "christmas_lights_showSnowstorm" => intval($mybb->input['christmas_lights_showSnowstorm'])
        );      
        $db->update_query("users", $update_array, "uid = '".$user['uid']."'");
    }
    
    $add_option = '</tr><tr>
<td valign="top" width="1"><input type="checkbox" class="checkbox" name="christmas_lights_showSnowstorm" id="christmas_lights_showSnowstorm" value="1" {$GLOBALS[\'$christmas_lights_showSnowstormChecked\']} /></td>
<td><span class="smalltext"><label for="christmas_lights_showSnowstorm">{$lang->christmas_lights_snowstorm_show_question}</label></span></td>';

    $find = '{$lang->show_codebuttons}</label></span></td>';
    $templates->cache['usercp_options'] = str_replace($find, $find.$add_option, $templates->cache['usercp_options']);
    
    $GLOBALS['$christmas_lights_showSnowstormChecked'] = '';
    if($user['christmas_lights_showSnowstorm'])
        $GLOBALS['$christmas_lights_showSnowstormChecked'] = "checked=\"checked\"";
}


function christmas_lights_snowstorm($page)
{
    global $mybb;
    
    if($mybb->user['christmas_lights_showSnowstorm']) {
        $page=str_replace('</head>','<script type="text/javascript" src="'.$mybb->settings['bburl'].'/inc/lights/snowstorm.js"></script></head>',$page);
    }
    
    return $page;
}

?>