<?php
/**
 * 
 *
 * @package admin
 * @version 0.6
 * @license http://download.smfproject.net/license.php New-BSD
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 *
 */
class ProjectTools_Maintenance_Repair extends ProjectTools_Maintenance_Action
{
	/**
	 *
	 */
	protected $actions = array(
		array('ProjectTools_Maintenance_Repair', 'MaintenanceGeneral'),
		array('ProjectTools_Maintenance_Repair', 'MaintenanceEvents1'),
		array('ProjectTools_Maintenance_Repair', 'MaintenanceEvents2'),
		array('ProjectTools_Maintenance_Repair', 'MaintenanceEvents3'),
		array('ProjectTools_Maintenance_Repair', 'MaintenanceIssues1'),
		array('ProjectTools_Maintenance_Repair', 'MaintenanceIssues2'),
		array('ProjectTools_Maintenance_Repair', 'MaintenanceIssueCounts'),
	);
	
	/**
	 * Maintenance function for generic maintenance
	 */
	function MaintenanceGeneral($check = false)
	{
		global $smcFunc;
	
		// Is this step required to run?
		if ($check)
			return true;
	
		// Set maxEventID
		$request = $smcFunc['db_query']('', '
			SELECT MAX(id_event)
			FROM {db_prefix}project_timeline');
	
		list ($maxEventID) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
	
		updateSettings(array('project_maxEventID' => $maxEventID));
	
		return true;
	}
	
	/**
	 * Maintenance function for comments not linked to events
	 */
	function MaintenanceEvents1($check = false)
	{
		global $smcFunc;
	
		// Is this step required to run?
		if ($check)
		{
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(*)
				FROM {db_prefix}issue_comments
				WHERE id_event = 0');
	
			list ($numErrors) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
	
			return $numErrors > 0;
		}
	
		$request = $smcFunc['db_query']('', '
			SELECT id_comment
			FROM {db_prefix}issue_comments
			WHERE id_event = 0');
	
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$event_req = $smcFunc['db_query']('', '
				SELECT id_event
				FROM {db_prefix}project_timeline AS tl
				WHERE tl.event = {string:new_comment}
					AND INSTR(tl.event_data , {string:comment})',
				array(
					'new_comment' => 'new_comment',
					'comment' => 's:7:"comment";i:' . $row['id_comment'] . ''
				)
			);
	
			list ($id_event) = $smcFunc['db_fetch_row']($event_req);
			$smcFunc['db_free_result']($event_req);
	
			if (!$id_event)
			{
				$event_req = $smcFunc['db_query']('', '
					SELECT id_event
					FROM {db_prefix}issues AS i
						LEFT JOIN {db_prefix}project_timeline AS tl ON (tl.id_issue = i.id_issue)
					WHERE i.id_comment_first = {int:comment}
						AND tl.event = {string:new_issue}',
					array(
						'new_issue' => 'new_issue',
						'comment' => $row['id_comment'],
					)
				);
				list ($id_event) = $smcFunc['db_fetch_row']($event_req);
				$smcFunc['db_free_result']($event_req);
			}
	
			if ($id_event)
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}issue_comments
					SET id_event = {int:event}
					WHERE id_comment = {int:comment}',
					array(
						'event' => $id_event,
						'comment' => $row['id_comment'],
					)
				);
		}
		$smcFunc['db_free_result']($request);
	
		return true;
	}
	
	/**
	 * Maintenance function for events without poster info
	 */
	function MaintenanceEvents2($check = false)
	{
		global $smcFunc;
	
		// Is this step required to run?
		if ($check)
		{
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(*)
				FROM {db_prefix}project_timeline
				WHERE poster_name = {string:empty} OR poster_email = {string:empty} OR poster_ip = {string:empty}',
				array(
					'empty' => '',
				)
			);
	
			list ($numErrors) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
	
			return $numErrors > 0;
		}
	
		$request = $smcFunc['db_query']('', '
			SELECT tl.id_event, com.poster_name, com.poster_email, com.poster_ip
			FROM {db_prefix}project_timeline AS tl
				INNER JOIN {db_prefix}issue_comments AS com ON (com.id_event = tl.id_event)
			WHERE tl.poster_name = {string:empty} OR tl.poster_email = {string:empty} OR tl.poster_ip = {string:empty}',
			array(
				'empty' => '',
			)
		);
	
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}project_timeline
				SET poster_name = {string:poster_name}, poster_email = {string:poster_email}, poster_ip = {string:poster_ip}
				WHERE id_event = {int:event}', array(
					'event' => $row['id_event'],
					'poster_name' => $row['poster_name'],
					'poster_email' => $row['poster_email'],
					'poster_ip' => $row['poster_ip'],
				)
			);
	
		return true;
	}
	
	/**
	 * Maintenance function for unnecassary events
	 */
	function MaintenanceEvents3($check = false)
	{
		global $smcFunc, $txt;
	
		// Is this step required to run?
		if ($check)
		{
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(*)
				FROM {db_prefix}project_timeline
				WHERE event = {string:edit_comment} OR event = {string:delete_comment}',
				array(
					'edit_comment' => 'edit_comment',
					'delete_comment' => 'delete_comment',
				)
			);
	
			list ($numErrors) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
	
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(*)
				FROM {db_prefix}project_timeline AS tl
					LEFT JOIN {db_prefix}issues AS i ON (i.id_issue = tl.id_issue)
				WHERE ISNULL(i.id_issue)');
	
			list ($numErrors2) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
	
			return (int) $numErrors + (int) $numErrors2;
		}
	
		$request = $smcFunc['db_query']('', '
			DELETE FROM {db_prefix}project_timeline
			WHERE event = {string:edit_comment} OR event = {string:delete_comment}',
			array(
				'edit_comment' => 'edit_comment',
				'delete_comment' => 'delete_comment',
			)
		);
	
		return true;
	}
	
	/**
	 * Maintenance function for updating issue counts
	 *
	 * @todo Write code for this function
	 */
	function MaintenanceIssueCounts($check = false)
	{
		global $smcFunc, $txt;
		
		if ($check)
		{
			// TODO: Write actual code
			return true;
		}
		
		// TODO: Write code to recount issues
		
		return true;
	}
	
	/**
	 * Maintenance function for deleting invalid issues
	 *
	 * @todo Check function needs to be written
	 */
	function MaintenanceIssues1($check = false)
	{
		global $smcFunc, $txt;
	
		if ($check)
		{
			// TODO: Write actual code
			return true;
		}
	
		$request = $smcFunc['db_query']('', '
			SELECT i.id_issue
			FROM {db_prefix}issues AS i
				LEFT JOIN {db_prefix}issue_comments AS com ON (com.id_comment = i.id_comment_first)
			WHERE ISNULL(com.id_comment)');
	
		while ($row = $smcFunc['db_fetch_assoc']($request))
			deleteIssue($row['id_issue'], false);
	
		$smcFunc['db_free_result']($request);
		
		return true;
	}
	
	/**
	 * Maintenance function for setting deleted posters as guests
	 *
	 * @todo Write check code
	 */
	function MaintenanceIssues2($check = false)
	{
		global $smcFunc, $txt;
	
		if ($check)
		{
			// TODO: Write actual code
			return true;
		}
		
		$deletedMembers = array();
	
		// Reporters
		$request = $smcFunc['db_query']('', '
			SELECT DISTINCT i.id_reporter
			FROM {db_prefix}issues AS i
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = i.id_reporter)
			WHERE ISNULL(mem.id_member)');
	
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$deletedMembers[$row['id_reporter']] = $row['id_reporter'];
		$smcFunc['db_free_result']($request);
		
		// Updaters
		$request = $smcFunc['db_query']('', '
			SELECT DISTINCT i.id_updater
			FROM {db_prefix}issues AS i
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = i.id_updater)
			WHERE ISNULL(mem.id_member)');
	
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$deletedMembers[$row['id_updater']] = $row['id_updater'];
		$smcFunc['db_free_result']($request);
		
		/*// Commenters
		$request = $smcFunc['db_query']('', '
			SELECT DISTINCT com.id_member
			FROM {db_prefix}issue_comments AS com
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = com.id_member)
			WHERE ISNULL(mem.id_member)');
	
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$deletedMembers[$row['id_member']] = $row['id_member'];
		$smcFunc['db_free_result']($request);*/
		
		// Timeline
		$request = $smcFunc['db_query']('', '
			SELECT DISTINCT tl.id_member
			FROM {db_prefix}project_timeline AS tl
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = tl.id_member)
			WHERE ISNULL(mem.id_member)');
		
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$deletedMembers[$row['id_member']] = $row['id_member'];
		$smcFunc['db_free_result']($request);
		
		if (empty($deletedMembers))
			return true;
		
		/*// Make Project Tools comments guest posts
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}issue_comments
			SET id_member = {int:guest_id}
			WHERE id_member IN ({array_int:users})',
			array(
				'guest_id' => 0,
				'blank_email' => '',
				'users' => $deletedMembers,
			)
		);*/
		// Make Project Tools issues guest
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}issues
			SET id_reporter = {int:guest_id}
			WHERE id_reporter IN ({array_int:users})',
			array(
				'guest_id' => 0,
				'blank_email' => '',
				'users' => $deletedMembers,
			)
		);	
		// Make Project Tools issues updated by guest
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}issues
			SET id_updater = {int:guest_id}
			WHERE id_updater IN ({array_int:users})',
			array(
				'guest_id' => 0,
				'blank_email' => '',
				'users' => $deletedMembers,
			)
		);
		// Make Project Tools events guests
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}project_timeline
			SET id_member = {int:guest_id}
			WHERE id_member IN ({array_int:users})',
			array(
				'guest_id' => 0,
				'blank_email' => '',
				'users' => $deletedMembers,
			)
		);
		// Delete the members notifications and read logs
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_notify_projects
			WHERE id_member IN ({array_int:users})',
			array(
				'users' => $deletedMembers,
			)
		);
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_projects
			WHERE id_member IN ({array_int:users})',
			array(
				'users' => $deletedMembers,
			)
		);	
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_project_mark_read
			WHERE id_member IN ({array_int:users})',
			array(
				'users' => $deletedMembers,
			)
		);
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_issues
			WHERE id_member IN ({array_int:users})',
			array(
				'users' => $deletedMembers,
			)
		);
		// Delete developer status
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}project_developer
			WHERE id_member IN ({array_int:users})',
			array(
				'users' => $deletedMembers,
			)
		);
		// Delete possible settings
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}project_settings
			WHERE id_member IN ({array_int:users})',
			array(
				'users' => $deletedMembers,
			)
		);
		
		return true;
	}
}

?>