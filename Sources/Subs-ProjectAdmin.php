<?php
/**
 * Generic functions for Project Tools admin
 *
 * @package admin
 * @version 0.5
 * @license http://download.smfproject.net/license.php New-BSD
 * @since 0.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * Inserts new project to database
 */
function createProject($projectOptions)
{
	global $context, $smcFunc, $sourcedir, $user_info, $txt, $modSettings;

	if (empty($projectOptions['name']) || !isset($projectOptions['description']) || !isset($projectOptions['member_groups']) || !isset($projectOptions['trackers']))
		trigger_error('createProject(): required parameters missing or invalid', E_USER_ERROR);

	$smcFunc['db_insert']('insert',
		'{db_prefix}projects',
		array(
			'name' => 'string',
			'description' => 'string',
			'long_description' => 'string',
			'trackers' => 'string',
			'member_groups' => 'string',
			'id_profile' => 'int',
		),
		array(
			$projectOptions['name'],
			$projectOptions['description'],
			isset($projectOptions['long_description']) ? $projectOptions['long_description'] : '',
			implode(',', $projectOptions['trackers']),
			implode(',', $projectOptions['member_groups']),
			empty($projectOptions['profile']) ? 1 : $projectOptions['profile'],
		),
		array('id_project')
	);

	$id_project = $smcFunc['db_insert_id']('{db_prefix}projects', 'id_project');

	unset($projectOptions['name'], $projectOptions['description'], $projectOptions['trackers'], $projectOptions['member_groups'], $projectOptions['profile']);

	// Anything left?
	if (!empty($projectOptions))
		updateProject($id_project, $projectOptions);

	return $id_project;
}

/**
 * Updats project in database
 */
function updateProject($id_project, $projectOptions)
{
	global $context, $smcFunc, $sourcedir, $user_info, $txt, $modSettings;

	$projectUpdates = array();

	if (isset($projectOptions['name']))
		$projectUpdates[] = 'name = {string:name}';
	if (isset($projectOptions['description']))
		$projectUpdates[] = 'description = {string:description}';

	if (isset($projectOptions['long_description']))
		$projectUpdates[] = 'long_description = {string:long_description}';

	if (isset($projectOptions['trackers']))
	{
		$projectUpdates[] = 'trackers = {string:trackers}';
		$projectOptions['trackers'] = implode(',', $projectOptions['trackers']);
	}

	if (isset($projectOptions['modules']))
	{
		$projectUpdates[] = 'modules = {string:modules}';
		$projectOptions['modules'] = implode(',', $projectOptions['modules']);
	}
	


	if (isset($projectOptions['theme']))
		$projectUpdates[] = 'project_theme = {int:theme}';
	if (isset($projectOptions['override_theme']))
	{
		$projectUpdates[] = 'override_theme = {int:override_theme}';
		$projectOptions['override_theme'] = $projectOptions['override_theme'] ? 1 : 0;
	}

	if (isset($projectOptions['profile']))
		$projectUpdates[] = 'id_profile = {int:profile}';

	if (isset($projectOptions['category']))
		$projectUpdates[] = 'id_category = {int:category}';
	if (isset($projectOptions['category_position']))
		$projectUpdates[] = 'cat_position = {string:category_position}';

	if (!empty($projectUpdates))
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}projects
			SET
				' . implode(',
				', $projectUpdates) . '
			WHERE id_project = {int:project}',
			array_merge($projectOptions, array(
				'project' => $id_project,
			))
		);

	if (isset($projectOptions['developers']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}project_developer
			WHERE id_project = {int:project}',
			array(
				'project' => $id_project,
			)
		);

		$developers = array();

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$developers[] = $row['id_member'];
		$smcFunc['db_free_result']($request);

		$toRemove = array_diff($developers, $projectOptions['developers']);
		$toAdd = array_diff($projectOptions['developers'], $developers);

		if (!empty($toRemove))
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}project_developer
				WHERE id_member IN({array_int:remove})
					AND id_project = {int:project}',
				array(
					'remove' => $toRemove,
					'project' => $id_project,
				)
			);

		if (!empty($toAdd))
		{
			$rows = array();

			foreach ($toAdd as $id_member)
				if (!empty($id_member))
					$rows[] = array($id_project, (int) $id_member);

			$smcFunc['db_insert']('insert',
				'{db_prefix}project_developer',
				array(
					'id_project' => 'int',
					'id_member' => 'int',
				),
				$rows,
				array('id_project', 'id_member')
			);
		}
	}

	cache_put_data('project-' . $id_project, null, 120);
	cache_put_data('project-version-' . $id_project, null, 120);

	return true;
}

/**
 * Inserts new version to project
 */
function createVersion($id_project, $versionOptions)
{
	global $context, $smcFunc, $sourcedir, $user_info, $txt, $modSettings;

	if (empty($versionOptions['name']))
		trigger_error('createVersion(): required parameters missing or invalid');

	if (empty($versionOptions['release_date']))
		$versionOptions['release_date'] = serialize(array('day' => 0, 'month' => 0, 'year' => 0));

	if (empty($versionOptions['description']))
		$versionOptions['description'] = '';

	if (empty($versionOptions['parent']))
	{
		$versionOptions['parent'] = 0;
		$versionOptions['status'] = 0;
	}
	else
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_version
			FROM {db_prefix}project_versions
			WHERE id_project = {int:project}
				AND id_version = {int:version}',
			array(
				'project' => $id_project,
				'version' => $versionOptions['parent'],
			)
		);

		if ($smcFunc['db_num_rows']($request) == 0)
			trigger_error('createVersion(): invalid parent');
		$smcFunc['db_free_result']($request);
	}

	$smcFunc['db_insert']('insert',
		'{db_prefix}project_versions',
		array(
			'id_project' => 'int',
			'id_parent' => 'int',
			'version_name' => 'string',
			'description' => 'string',
			'member_groups' => 'string',
		),
		array(
			$id_project,
			$versionOptions['parent'],
			$versionOptions['name'],
			$versionOptions['description'],
			implode(',', $versionOptions['member_groups']),
		),
		array('id_version')
	);

	$id_version = $smcFunc['db_insert_id']('{db_prefix}project_versions', 'id_version');

	unset($versionOptions['parent'], $versionOptions['name'], $versionOptions['description'], $versionOptions['member_groups']);

	updateVersion($id_project, $id_version, $versionOptions);

	return $id_version;
}

/**
 * Updates vesion
 */
function updateVersion($id_project, $id_version, $versionOptions)
{
	global $context, $smcFunc, $sourcedir, $user_info, $txt, $modSettings;
	
	$request = $smcFunc['db_query']('', '
		SELECT id_parent, permission_inherit
		FROM {db_prefix}project_versions
		WHERE id_project = {int:project}
			AND id_version = {int:version}',
		array(
			'project' => $id_project,
			'version' => $id_version,
		)
	);
	
	$versionRow = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);
	
	if (!$versionRow)
		return false;
	
	$inherited = !empty($versionRow['permission_inherit']);
	
	// Will it change?
	if (isset($versionOptions['permission_inherit']))
		$inherited = !empty($versionOptions['permission_inherit']);
	
	// Don't allow changing member_groups when inherited
	if (isset($versionOptions['member_groups']) && !$inherited)
		unset($versionOptions['member_groups']);
			
	$versionUpdates = array();

	if (isset($versionOptions['name']))
		$versionUpdates[] = 'version_name = {string:name}';

	if (isset($versionOptions['description']))
		$versionUpdates[] = 'description = {string:description}';

	if (isset($versionOptions['release_date']))
		$versionUpdates[] = 'release_date = {string:release_date}';
		
	if (isset($versionOptions['permission_inherit']))
	{
		// Make sure it's not overwritten
		if (isset($versionOptions['member_groups']) && !empty($versionOptions['permission_inherit']))
			unset($versionOptions['member_groups']);
			
		$versionUpdates[] = 'permission_inherit = {int:permission_inherit}';
		$versionOptions['permission_inherit'] = !empty($versionOptions['permission_inherit']) ? 1 : 0;
		$versionRow = $versionOptions['permission_inherit'];
		
		// Inherit from parent version
		if (!empty($versionRow['id_parent']))
			$request = $smcFunc['db_query']('', '
				SELECT member_groups
				FROM {db_prefix}project_versions
				WHERE id_project = {int:project}
					AND id_version = {int:version}',
				array(
					'project' => $id_project,
					'version' => $versionRow['id_parent'],
				)
			);
		// or from project
		else
			$request = $smcFunc['db_query']('', '
				SELECT member_groups
				FROM {db_prefix}projects
				WHERE id_project = {int:project}',
				array(
					'project' => $id_project,
				)
			);
			
		$versionUpdates[] = 'member_groups = {string:member_groups}';
		list ($versionOptions['member_groups']) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
	}

	if (isset($versionOptions['member_groups']))
	{
		// Update versions with permission inherited
		$request = $smcFunc['db_query']('', '
			SELECT id_version
			FROM {db_prefix}project_versions
			WHERE id_project = {int:project}
				AND permission_inherit = {int:inherit}
				AND id_parent = {int:parent}',
			array(
				'project' => $id_project,
				'inherit' => 1,
				'parent' => $id_version,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			updateVersion($id_project, $row['id_version'], array('member_groups' => $versionOptions['member_groups']));
		$smcFunc['db_free_result']($request);
		
		$versionUpdates[] = 'member_groups = {string:member_groups}';
		$versionOptions['member_groups'] = is_array($versionOptions['member_groups']) ? implode(',', $versionOptions['member_groups']) : $versionOptions['member_groups'];
	}

	if (isset($versionOptions['status']))
		$versionUpdates[] = 'status = {int:status}';

	if (!empty($versionUpdates))
		$request = $smcFunc['db_query']('', '
			UPDATE {db_prefix}project_versions
			SET
				' . implode(',
				', $versionUpdates) . '
			WHERE id_version = {int:version}',
			array_merge($versionOptions, array(
				'version' => $id_version,
			))
		);

	cache_put_data('project-' . $id_project, null, 120);
	cache_put_data('project-version-' . $id_project, null, 120);

	return true;
}

/**
 * Creates new category for project
 */
function createPTCategory($id_project, $categoryOptions)
{
	global $smcFunc, $sourcedir, $user_info, $txt, $modSettings;

	$smcFunc['db_insert']('insert',
		'{db_prefix}issue_category',
		array('id_project' => 'int', 'category_name' => 'string'),
		array($id_project, $categoryOptions['name']),
		array('id_category')
	);

	cache_put_data('project-' . $id_project, null, 120);
	cache_put_data('project-version-' . $id_project, null, 120);

	return true;
}

/**
 * Updates category
 */
function updatePTCategory($id_project, $id_category, $categoryOptions)
{
	global $smcFunc, $sourcedir, $user_info, $txt, $modSettings;

	$categoryUpdates = array();

	if (isset($categoryOptions['name']))
		$categoryUpdates[] = 'category_name = {string:name}';

	if (isset($categoryOptions['project']))
		$categoryUpdates[] = 'id_project = {int:project}';

	if (!empty($categoryOptions))
		$request = $smcFunc['db_query']('', '
			UPDATE {db_prefix}issue_category
			SET
				' . implode(',
				', $categoryUpdates) . '
			WHERE id_category = {int:category}',
			array_merge($categoryOptions, array(
				'category' => $id_category,
			))
		);

	cache_put_data('project-' . $id_project, null, 120);
	cache_put_data('project-version-' . $id_project, null, 120);

	return true;
}



/**
 * Returns list of all possible permissions
 */
function getAllPTPermissions()
{
	// List of all possible permissions
	// 'perm' => array(own/any, [guest = true])

	return array(
		'issue_view' => array(false),
		'issue_view_private' => array(false),
		'issue_report' => array(false),
		'issue_comment' => array(false),
		'issue_update' => array(true, false),
		'issue_attach' => array(false),
		'issue_moderate' => array(false, false),
		// Comments
		'edit_comment' => array(true, false),
		'delete_comment' => array(true, false),
	);
}

?>