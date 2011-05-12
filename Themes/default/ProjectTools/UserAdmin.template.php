<?php
/**
 * Template for UserAdmin.php
 *
 * @package UserAdmin
 * @version 0.6
 * @license http://download.smfproject.net/license.php New-BSD
 * @since 0.6
 * @see UserAdmin.php
 */

/**
 *
 */
function template_select_project()
{
	global $context, $settings, $options, $txt, $modSettings;
	
		echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['project'], '
		</h3>
	</div>
	<div id="projects_table">
		<table class="table_list">
			<thead>
				<tr><th colspan="4"></th></tr>
			</thead>
			<tfoot>
				<tr><td colspan="4"></td></tr>
			</tfoot>
			<tbody class="content">';

		foreach ($context['admin_projects'] as $i => $project)
		{
			echo '
				<tr>
					<td class="windowbg2 info">
						<h4><a href="', $project['href'], '">', $project['name'], '</a></h4>
					</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>
	</div>';
}

/**
 *
 */
function template_admin_frontpage()
{
	global $context, $settings, $options, $txt, $modSettings;
	
	$context['project_form']->render();
}

/**
 *
 */
function template_members_list()
{
	global $context, $settings, $options, $txt, $modSettings;

	template_show_list('members_list');
	
	echo '
	<br />
	<form action="', ProjectTools::get_admin_url(array('project' => ProjectTools_Project::getCurrent()->id, 'area' => 'members', 'sa' => 'add')), '" method="post">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['pt_add_project_member'], '</h3>
		</div>
		<div class="windowbg2">
			<span class="top_slice"><span></span></span>
			<div class="content">
				<input type="text" name="member" id="member" size="25" tabindex="', $context['tabindex']++, '" />
				<div id="member_container"></div>
				<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/suggest.js?rc5"></script>
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					var oDeveloperSuggest = new smc_AutoSuggest({
						sSelf: \'oDeveloperSuggest\',
						sSessionVar: \'', $context['session_var'], '\',
						sSessionId: \'', $context['session_id'], '\',
						sSuggestId: \'member\',
						sControlId: \'member\',
						sSearchType: \'member\',
						bItemList: true,
						sPostName: \'member_container\',
						sURLMask: \'action=profile;u=%item_id%\',
						sItemListContainerId: \'member_container\',
						aListItems: []
					});
					// ]]></script>
				<br />
				<input class="button_submit" type="submit" value="', $txt['pt_add_members'], '" />
			</div>
			<span class="bot_slice"><span></span></span>
		</div>
	</form>';
}

/**
 *
 */
function template_versions_list()
{
	global $context, $settings, $options, $txt, $modSettings;

	template_show_list('versions_list');
}

/**
 *
 */
function template_categories_list()
{
	global $context, $settings, $options, $txt, $modSettings;

	template_show_list('categories_list');
}

/**
 *
 */
function template_modules_form()
{
	global $context, $settings, $options, $txt, $modSettings;

	$context['modules_form']->render();
}

/**
 *
 */
function template_edit_version()
{
	global $context, $settings, $options, $scripturl, $txt, $project, $modSettings;

	$context['version_form']->render();
}

/**
 *
 */
function template_edit_category()
{
	global $context, $settings, $options, $txt, $modSettings, $project;

	$context['category_form']->render();
}

?>