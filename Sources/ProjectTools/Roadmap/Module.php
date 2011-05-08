<?php
/**
 * 
 *
 * @package SVNIntegration
 * @version 0.6
 * @license http://download.smfproject.net/license.php New-BSD
 * @since 0.6
 */

/**
 *
 */
class ProjectTools_Roadmap_Module extends ProjectTools_ModuleBase
{
	/**
	 *
	 */
	public function Main()
	{
		$subActions = array(
			'main' => array(
				array($this, '__projectRoadmapMain')
			),
			'version' => array(
				array($this, '__projectRoadmapVersion'),
			),
		);
		
		if (!isset($_REQUEST['sa']) || !isset($subActions[$_REQUEST['sa']]))
			$_REQUEST['sa'] = 'main';
			
		if (isset($_REQUEST['version']) && $_REQUEST['sa'] == 'main')
			$_REQUEST['sa'] = 'version';
			
		call_user_func($subActions[$_REQUEST['sa']][0], $this->project);
	}

	/**
	 *
	 */
	public function RegisterArea()
	{
		global $txt;
		
		return array(
			'id' => 'rodmap',
			'title' => $txt['roadmap'],
			'callback' => 'Main',
			'order' => 50,
			'project_permission' => 'admin',
		);
	}

	function __projectRoadmapMain()
	{
		global $context, $project, $user_info, $smcFunc, $txt;
	
		// Canonical url for search engines
		$context['canonical_url'] = ProjectTools::get_url(array('project' => $project, 'area' => 'roadmap'));
		
		$ids = array(0);
		$context['roadmap'] = array();
	
		$request = $smcFunc['db_query']('', '
			SELECT ver.id_version, ver.id_parent, ver.version_name, ver.status, ver.description, ver.release_date, ver.' . implode(', ver.', $context['tracker_columns']) . '
			FROM {db_prefix}project_versions AS ver
			WHERE {query_see_version}
				AND ver.id_project = {int:project}' . (!isset($_REQUEST['all']) ? '
				AND ver.status IN ({array_int:status})' : '') . '
			ORDER BY ver.version_name DESC',
			array(
				'project' => $project,
				'status' => array(0, 1),
			)
		);
	
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$ids[] = $row['id_version'];
	
			if (!empty($row['release_date']))
				$row['release_date'] = @unserialize($row['release_date']);
			else
				$row['release_date'] = array();
	
			$time = array();
	
			if (empty($row['release_date']['day']) && empty($row['release_date']['month']) && empty($row['release_date']['year']))
				$time = array('roadmap_no_release_date', array());
			elseif (empty($row['release_date']['day']) && empty($row['release_date']['month']))
				$time = array('roadmap_release_date_year', array($row['release_date']['year']));
			elseif (empty($row['release_date']['day']))
				$time = array('roadmap_release_date_year_month', array($txt['months'][(int) $row['release_date']['month']], $row['release_date']['year']));
			else
				$time = array('roadmap_release_date_year_month_day', array($row['release_date']['day'], $txt['months'][(int) $row['release_date']['month']], $row['release_date']['year']));
	
			$context['roadmap'][$row['id_version']] = array(
				'id' => $row['id_version'],
				'name' => $row['version_name'],
				'href' => ProjectTools::get_url(array('project' => $project, 'area' => 'roadmap', 'version' => $row['id_version'])),
				'description' => parse_bbc($row['description']),
				'release_date' => vsprintf($txt[$time[0]], $time[1]),
				'issues' => array(
					'open' => 0,
					'closed' => 0,
					'total' => 0,
				),
				'progress' => 0,
			);
			
			foreach (ProjectTools_Project::getCurrent()->trackers as $id => $tracker)
			{
				$context['roadmap'][$row['id_version']]['issues']['open'] += $row['open_' . $tracker['short']];
				$context['roadmap'][$row['id_version']]['issues']['closed'] += $row['closed_' . $tracker['short']];		
				$context['roadmap'][$row['id_version']]['issues']['total'] += $row['open_' . $tracker['short']] + $row['closed_' . $tracker['short']];
			}
			
			if ($context['roadmap'][$row['id_version']]['issues']['total'] > 0)
				$context['roadmap'][$row['id_version']]['progress'] = round($context['roadmap'][$row['id_version']]['issues']['closed'] / $context['roadmap'][$row['id_version']]['issues']['total'] * 100, 2);	
		}
		$smcFunc['db_free_result']($request);
	
		// N/A version
		$context['roadmap'][0] = array(
			'id' => 0,
			'name' => $txt['version_na'],
			'href' => ProjectTools::get_url(array('project' => $project, 'area' => 'roadmap', 'version' => 0)),
			'description' => $txt['version_na_desc'],
			'release_date' => $txt['roadmap_no_release_date'],
			'issues' => array(
				'open' => 0,
				'closed' => 0,
				'total' => 0,
			),
			'progress' => 0,
		);
	
		// Template
		$context['page_title'] = sprintf($txt['project_roadmap_title'], ProjectTools_Project::getCurrent()->name);
		$context['sub_template'] = 'project_roadmap';
		loadTemplate('ProjectRoadmap');
	}
	
	function __projectRoadmapVersion()
	{
		global $context, $project, $user_info, $smcFunc, $txt;
	
		if ($_REQUEST['version'] != '0')
		{
			$request = $smcFunc['db_query']('', '
				SELECT ver.id_version, ver.id_parent, ver.version_name, ver.status, ver.description, ver.release_date, ver.' . implode(', ver.', $context['tracker_columns']) . '
				FROM {db_prefix}project_versions AS ver
				WHERE ({query_see_version})
					AND ver.id_project = {int:project}
					AND ver.id_version = {int:version}',
				array(
					'project' => $project,
					'version' => (int) $_REQUEST['version'],
				)
			);
			$row = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);
		}
		else
		{
			$row = array(
				'id_version' => 0,
				'id_parent' => 0,
				'version_name' => $txt['version_na'],
				'status' => 0,
				'description' => $txt['version_na_desc'],
				'release_date' => '',
			);
		}
	
		if (!$row)
			fatal_lang_error('version_not_found', false);
	
		// Canonical url for search engines
		$context['canonical_url'] = ProjectTools::get_url(array('project' => $project, 'area' => 'roadmap', 'version' => $row['id_version']));
		
		// Make release date string
		if (!empty($row['release_date']))
			$row['release_date'] = unserialize($row['release_date']);
		else
			$row['release_date'] = array();
	
		$time = array();
	
		if (empty($row['release_date']['day']) && empty($row['release_date']['month']) && empty($row['release_date']['year']))
			$time = array('roadmap_no_release_date', array());
		elseif (empty($row['release_date']['day']) && empty($row['release_date']['month']))
			$time = array('roadmap_release_date_year', array($row['release_date']['year']));
		elseif (empty($row['release_date']['day']))
			$time = array('roadmap_release_date_year_month', array($txt['months'][$row['release_date']['month']], $row['release_date']['year']));
		else
			$time = array('roadmap_release_date_year_month_day', array($row['release_date']['day'], $txt['months'][$row['release_date']['month']], $row['release_date']['year']));
	
		$context['version'] = array(
			'id' => $row['id_version'],
			'name' => $row['version_name'],
			'href' => ProjectTools::get_url(array('project' => $project, 'area' => 'roadmap', 'version' => $row['id_version'])),
			'description' => parse_bbc($row['description']),
			'release_date' => vsprintf($txt[$time[0]], $time[1]),
			'versions' => array(),
			'progress' => 0,
			'issues' => array(
				'open' => 0,
				'closed' => 0,
				'total' => 0,
			),
		);
		
		foreach (ProjectTools_Project::getCurrent()->trackers as $tracker)
		{
			if (!isset($row['open_' . $tracker['short']]))
				continue;
			
			$context['version']['issues']['open'] += $row['open_' . $tracker['short']];
			$context['version']['issues']['closed'] += $row['closed_' . $tracker['short']];		
			$context['version']['issues']['total'] += $row['open_' . $tracker['short']] + $row['closed_' . $tracker['short']];
		}
	
		if (!empty($context['version']['issues']['total']))
			$context['version']['progress'] = round($context['version']['issues']['closed'] / $context['version']['issues']['total'] * 100, 2);
	
		// Load Issues
		$context['issues'] = getIssueList(0, 10, 'i.updated DESC', 'FIND_IN_SET(i.versions, {int:version})', array('version' => (int) $context['version']['id']));
		$context['issues_href'] = ProjectTools::get_url(array('project' => $project, 'area' => 'issues', 'version_fixed' => $context['version']['id']));
	
		// Template
		$context['sub_template'] = 'project_roadmap_version';
		loadTemplate('ProjectRoadmap');
	}
}

?>