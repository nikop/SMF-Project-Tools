<?php
/**
 * 
 *
 * @package IssueTracker
 * @version 0.6
 * @license http://download.smfproject.net/license.php New-BSD
 * @since 0.6
 */

/**
 *
 */
class ProjectTools_IssueTracker_Module extends ProjectTools_ModuleBase
{
	/**
	 *
	 */
	public function Main()
	{
		$subActions = array(
			'main' => array('ProjectTools_IssueTracker_List', 'Main'),
			'view' => array('ProjectTools_IssueTracker_View', 'Main'),
			'tags' => array('ProjectTools_IssueTracker_Tags', 'Main'),
			'update' => array('ProjectTools_IssueTracker_Edit', 'Update'),
			'upload' => array('ProjectTools_IssueTracker_Edit', 'Upload'),
			'move' => array('ProjectTools_IssueTracker_Edit', 'Move'),
			'delete' => array('ProjectTools_IssueTracker_Edit', 'Delete'),
			// Edit
			'edit' => array('ProjectTools_IssueTracker_Edit', 'Main'),
			'edit2' => array('ProjectTools_IssueTracker_Edit', 'Main2'),
			// Comment
			'reply' => array('ProjectTools_IssueTracker_Comment', 'Add'),
			'reply2' => array('ProjectTools_IssueTracker_Comment', 'Add2'),
			'removeComment' => array('ProjectTools_IssueTracker_Comment', 'Delete'),
			// Report
			'report' => array('ProjectTools_IssueTracker_Report', 'Report'),
			'report2' => array('ProjectTools_IssueTracker_Report', 'Report2'),
		);
		
		if (!isset($_REQUEST['sa']) || !isset($subActions[$_REQUEST['sa']]))
			$_REQUEST['sa'] = 'main';
			
		call_user_func($subActions[$_REQUEST['sa']]);
	}
	
	/**
	 *
	 */
	public function RegisterArea()
	{
		global $txt;
		
		return array(
			'id' => 'issues',
			'title' => $txt['issues'],
			'callback' => array(get_class(), 'Main'),
			'hide_linktree' => true,
			'order' => 10,
		);
	}
	
	/**
	 *
	 */
	function RegisterProjectFrontpageBlocks(&$frontpage_blocks)
	{
		global $context, $project, $user_info;
		
		$issues_num = 5;

		$issue_list = array(
			'recent_issues' => array(
				'title' => 'recent_issues',
				'href' => project_get_url(array('project' => $project, 'area' => 'issues')),
				'order' => 'i.updated DESC',
				'where' => '1 = 1',
				'show' => projectAllowedTo('issue_view'),
			),
			'my_reports' => array(
				'title' => 'reported_by_me',
				'href' => project_get_url(array('project' => $project, 'area' => 'issues', 'reporter' => $user_info['id'])),
				'order' => 'i.updated DESC',
				'where' => 'i.id_reporter = {int:current_member}',
				'show' => projectAllowedTo('issue_report'),
			),
			'assigned' => array(
				'title' => 'assigned_to_me',
				'href' => project_get_url(array('project' => $project, 'area' => 'issues', 'assignee' => $user_info['id'])),
				'order' => 'i.updated DESC',
				'where' => 'i.id_assigned = {int:current_member} AND NOT (i.status IN ({array_int:closed_status}))',
				'show' => projectAllowedTo('issue_resolve'),
			),
			'new_issues' => array(
				'title' => 'new_issues',
				'href' => project_get_url(array('project' => $project, 'area' => 'issues', 'status' => 1,)),
				'order' => 'i.created DESC',
				'where' => 'i.status = 1',
				'show' => projectAllowedTo('issue_view'),
			),
		);
	
		foreach ($issue_list as $block_id => $information)
			$frontpage_blocks[$block_id] = array(
				'title' => $information['title'],
				'href' => $information['href'],
				'data_function' => 'getIssueList',
				'data_parameters' => array(0, $issues_num, $information['order'], $information['where']),
				'template' => 'issue_list_block',
				'show' => $information['show'],
			);
	}
}

?>