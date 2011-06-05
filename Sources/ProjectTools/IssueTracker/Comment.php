<?php
/**
 * 
 *
 * @package IssueTracker
 * @version 0.6
 * @license http://download.smfproject.net/license.php New-BSD
 * @since 0.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 *
 */
class ProjectTools_IssueTracker_Comment
{
	/**
	 *
	 */
	public static function AddComment()
	{
		global $context, $project, $issue, $txt, $smcFunc;
		
		if (!ProjectTools_IssueTracker_Issue::getCurrent() || !ProjectTools::allowedTo('issue_comment'))
			fatal_lang_error('issue_not_found', false);
			
		$context['comment_form'] = new ProjectTools_IssueTracker_Form_Comment($project, $issue);
		if ($context['comment_form']->is_post)
		{
			$id = $context['comment_form']->Save();
			
			if ($id_comment !== false)
				redirectexit(ProjectTools::get_url(array('issue' => $issue . '.com' . $id[1])) . '#com' . $id[0]);
		}
		
		// Template
		$context['sub_template'] = 'issue_reply';
		$context['page_title'] = sprintf($txt['project_comment_issue'], ProjectTools_Project::getCurrent()->name, ProjectTools_IssueTracker_Issue::getCurrent()->id, ProjectTools_IssueTracker_Issue::getCurrent()->name);
	
		loadTemplate('ProjectTools/IssueTracker_Report');
	}
	
	/**
	 * Display Reply or Edit Reply page
	 */
	public static function _Comment()
	{
		global $context, $smcFunc, $sourcedir, $user_info, $txt, $issue, $modSettings, $options, $project;
	
		if (!ProjectTools_IssueTracker_Issue::getCurrent() || !ProjectTools::allowedTo('issue_comment'))
			fatal_lang_error('issue_not_found', false);
	
		$type = ProjectTools_IssueTracker_Issue::getCurrent()->is_mine ? 'own' : 'any';
	
		$context['show_update'] = false;
		$context['can_comment'] = ProjectTools::allowedTo('issue_comment');
		$context['can_subscribe'] = !$user_info['is_guest'];
		$context['can_issue_moderate'] = ProjectTools::allowedTo('issue_moderate');
		$context['can_issue_update'] = ProjectTools::allowedTo('issue_update_' . $type) || ProjectTools::allowedTo('issue_moderate');
		$context['can_issue_attach'] = ProjectTools::allowedTo('issue_attach');
	
		$context['allowed_extensions'] = strtr($modSettings['attachmentExtensions'], array(',' => ', '));
	
		if ($context['can_issue_update'])
		{
			$context['can_edit'] = true;
			$context['show_update'] = true;
		}
	
		if (ProjectTools::allowedTo('issue_moderate'))
		{
			$context['can_assign'] = true;
			$context['assign_members'] = &ProjectTools_Project::getCurrent()->developers;
		}
	
		$context['destination'] = 'reply2';
		
		// Check if user has subscribed to issue
		if (!$user_info['is_guest'])
		{
			$request = $smcFunc['db_query']('', '
				SELECT sent
				FROM {db_prefix}log_notify_projects
				WHERE id_issue = {int:issue}
					AND id_member = {int:current_member}
				LIMIT 1',
				array(
					'issue' => $issue,
					'current_member' => $user_info['id'],
				)
			);
			$context['is_subscribed'] = $smcFunc['db_num_rows']($request) != 0;
			$smcFunc['db_free_result']($request);
		}
		else
			$context['is_subscribed'] = false;
		
		$context['notify'] = isset($_POST['issue_subscribe']) ? !empty($_POST['issue_subscribe']) : ($context['is_subscribed'] || !empty($options['auto_notify']));
	
		// Editor
		require_once($sourcedir . '/Subs-Editor.php');
		require_once($sourcedir . '/Subs-Post.php');
	
		$editing = false;
	
		$form_comment = '';
	
		// Editing
		if ($_REQUEST['sa'] == 'edit' || $_REQUEST['sa'] == 'edit2')
		{
			ProjectTools::isAllowedTo('edit_comment_own');
			require_once($sourcedir . '/Subs-Post.php');
	
			if (empty($_REQUEST['com']) || !is_numeric($_REQUEST['com']))
				fatal_lang_error('comment_not_found', false);
	
			$request = $smcFunc['db_query']('', '
				SELECT c.id_comment, c.post_time, c.edit_time, c.body,
					IFNULL(mem.real_name, c.poster_name) AS real_name, c.poster_email, c.poster_ip, c.id_member
				FROM {db_prefix}issue_comments AS c
					LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = c.id_member)
				WHERE id_comment = {int:comment}' . (!ProjectTools::allowedTo('edit_comment_any') ? '
					AND c.id_member = {int:current_user}' : '') . '
				ORDER BY id_comment',
				array(
					'current_user' => $user_info['id'],
					'issue' => $issue,
					'comment' => (int) $_REQUEST['com'],
				)
			);
	
			$context['destination'] = 'edit2;com=' . (int) $_REQUEST['com'];
	
			$row = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);
	
			if (!$row)
				fatal_lang_error('comment_not_found', false);
	
			$form_comment = un_preparsecode($row['body']);
	
			$editing = true;
		}
		elseif (isset($_REQUEST['quote']) && is_numeric($_REQUEST['quote']))
		{
				}
	
		if (isset($_REQUEST['comment']) || !empty($context['post_error']))
		{
			if (!isset($_REQUEST['details']))
				$_REQUEST['details'] = '';
	
			if (empty($context['post_error']))
			{
				// TODO CHECKS
	
				$previewing = true;
			}
			else
				$previewing = !empty($_POST['preview']);
	
			$form_comment = $smcFunc['htmlspecialchars']($_REQUEST['comment'], ENT_QUOTES);
	
			if ($previewing)
			{
				$context['preview_comment'] = $form_comment;
				preparsecode($form_comment, true);
				preparsecode($context['preview_comment']);
	
				$context['preview_comment'] = parse_bbc($context['preview_comment']);
				censorText($context['preview_comment']);
			}
	
			$context['comment'] = $_REQUEST['comment'];
		}
	
		$context['comment'] = str_replace(array('"', '<', '>', '&nbsp;'), array('&quot;', '&lt;', '&gt;', ' '), $form_comment);
	
		$editorOptions = array(
			'form' => 'reportissue',
			'id' => 'comment',
			'width' => '550px',
			'value' => $context['comment'],
			'labels' => array(
				'post_button' => $editing ? $txt['issue_save'] : $txt['issue_reply'],
			),
		);
		create_control_richedit($editorOptions);
	
		checkSubmitOnce('register');
	
		$context['post_box_name'] = 'comment';
	

	}
	
	/**
	 * Add new reply or save edit
	 */
	public static function _Comment2()
	{
		global $context, $smcFunc, $sourcedir, $user_info, $txt, $issue, $modSettings, $project;
	
		if (!ProjectTools_Project::getCurrent() || !ProjectTools::allowedTo('issue_comment'))
			fatal_lang_error('issue_not_found', false);
	
		$type = ProjectTools_IssueTracker_Issue::getCurrent()->is_mine ? 'own' : 'any';
	
		$context['show_update'] = false;
		$context['can_comment'] = ProjectTools::allowedTo('issue_comment');
		$context['can_subscribe'] = !$user_info['is_guest'];
		$context['can_issue_moderate'] = ProjectTools::allowedTo('issue_moderate');
		$context['can_issue_update'] = ProjectTools::allowedTo('issue_update_' . $type) || ProjectTools::allowedTo('issue_moderate');
		$context['can_issue_attach'] = ProjectTools::allowedTo('issue_attach');
	
		$context['allowed_extensions'] = strtr($modSettings['attachmentExtensions'], array(',' => ', '));
	
		if ($context['can_issue_update'])
		{
			$context['can_edit'] = true;
			$context['show_update'] = true;
		}
	
		if (ProjectTools::allowedTo('issue_moderate'))
		{
			$context['can_assign'] = true;
			$context['assign_members'] = &ProjectTools_Project::getCurrent()->developers;
		}
	
		if (!empty($_REQUEST['comment_mode']) && isset($_REQUEST['comment']))
		{
			require_once($sourcedir . '/Subs-Editor.php');
	
			$_REQUEST['comment'] = html_to_bbc($_REQUEST['comment']);
			$_REQUEST['comment'] = un_htmlspecialchars($_REQUEST['comment']);
			$_POST['comment'] = $_REQUEST['comment'];
		}
	
		if (isset($_REQUEST['preview']))
			return self::Reply();
	
		require_once($sourcedir . '/Subs-Post.php');
	
		checkSubmitOnce('check');
	
		$post_errors = array();
	
		if (htmltrim__recursive(htmlspecialchars__recursive($_POST['comment'])) == '')
			$post_errors[] = 'no_message';
		else
		{
			$_POST['comment'] = $smcFunc['htmlspecialchars']($_POST['comment'], ENT_QUOTES);
	
			preparsecode($_POST['comment']);
			if ($smcFunc['htmltrim'](strip_tags(parse_bbc($_POST['comment'], false), '<img>')) === '' && (!allowedTo('admin_forum') || strpos($_POST['message'], '[html]') === false))
				$post_errors[] = 'no_message';
		}
	
		if (!empty($post_errors))
		{
			loadLanguage('Errors');
			$_REQUEST['preview'] = true;
	
			$context['post_error'] = array('messages' => array());
			foreach ($post_errors as $post_error)
			{
				$context['post_error'][$post_error] = true;
				$context['post_error']['messages'][] = $txt['error_' . $post_error];
			}
	
			return self::Reply();
		}
		
		// Check if user has subscribed to issue
		if (!$user_info['is_guest'])
		{
			$request = $smcFunc['db_query']('', '
				SELECT sent
				FROM {db_prefix}log_notify_projects
				WHERE id_issue = {int:issue}
					AND id_member = {int:current_member}
				LIMIT 1',
				array(
					'issue' => $issue,
					'current_member' => $user_info['id'],
				)
			);
			$context['is_subscribed'] = $smcFunc['db_num_rows']($request) != 0;
			$smcFunc['db_free_result']($request);
		}
		else
			$context['is_subscribed'] = false;	
	
		$_POST['guestname'] = $user_info['username'];
		$_POST['email'] = $user_info['email'];
	
		$_POST['guestname'] = htmlspecialchars($_POST['guestname']);
		$_POST['email'] = htmlspecialchars($_POST['email']);
	
		$posterOptions = array(
			'id' => $user_info['id'],
			'ip' => $user_info['ip'],
			'name' => $user_info['is_guest'] ? $_POST['guestname'] : $user_info['name'],
			'username' => $user_info['username'],
			'email' => $_POST['email'],
		);
		$issueOptions = array(
			'mark_read' => true,
		);
	
		if (ProjectTools::allowedTo('issue_update_' . $type) || ProjectTools::allowedTo('issue_moderate'))
			ProjectTools_IssueTracker_Report::handleUpdate($posterOptions, $issueOptions);
	
		if (count($issueOptions) > 1)
			$event_data = ProjectTools_IssueTracker_Issue::getCurrent()->update($issueOptions, $posterOptions, empty($_REQUEST['com']));
		else
			$event_data = array();
	
		if (empty($_REQUEST['com']))
		{
			// Event data might be boolean in certain cases
			if (!is_array($event_data))
				$event_data = array();
				
			$commentOptions = array(
				'body' => $_POST['comment'],
			);
			list ($id_comment, $id_issue_event) = createComment(ProjectTools_Project::getCurrent()->id, $issue, $commentOptions, $posterOptions, $event_data);
	
			$commentOptions['id'] = $id_comment;
	
			sendIssueNotification(array('id' => $issue, 'project' => ProjectTools_Project::getCurrent()->id), $commentOptions, $event_data, 'new_comment', $user_info['id']);
		}
		else
		{
			ProjectTools::isAllowedTo('edit_comment_own');
			require_once($sourcedir . '/Subs-Post.php');
	
			if (empty($_REQUEST['com']) || !is_numeric($_REQUEST['com']))
				fatal_lang_error('comment_not_found', false);
	
			$request = $smcFunc['db_query']('', '
				SELECT c.id_comment, c.post_time, c.edit_time, c.body,
					IFNULL(mem.real_name, c.poster_name) AS real_name, c.poster_email, c.poster_ip, c.id_member
				FROM {db_prefix}issue_comments AS c
					LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = c.id_member)
				WHERE id_comment = {int:comment}' . (!ProjectTools::allowedTo('edit_comment_any') ? '
					AND c.id_member = {int:current_user}' : '') . '
				ORDER BY id_comment',
				array(
					'current_user' => $user_info['id'],
					'issue' => $issue,
					'comment' => (int) $_REQUEST['com'],
				)
			);
	
			$row = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);
	
			if (!$row)
				fatal_lang_error('comment_not_found', false);
	
			$commentOptions = array(
				'body' => $_POST['comment'],
			);
	
			modifyComment($_REQUEST['com'], $issue, $commentOptions, $posterOptions);
	
			$id_comment = $_REQUEST['com'];
		}
	
		if (!empty($_POST['issue_subscribe']) && $context['can_subscribe'] && !$context['is_subscribed'])
		{
			$smcFunc['db_insert']('ignore',
				'{db_prefix}log_notify_projects',
				array(
					'id_project' => 'int',
					'id_issue' => 'int',
					'id_member' => 'int',
					'sent' => 'int',
				),
				array(
					0,
					$issue,
					$user_info['id'],
					0,
				),
				array('id_project', 'id_issue', 'id_member')
			);
		}
		// Unsubscribe
		elseif (empty($_POST['issue_subscribe']) && $context['is_subscribed'])
		{
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}log_notify_projects
				WHERE id_issue = {int:issue}
					AND id_member = {int:current_member}',
				array(
					'issue' => $issue,
					'current_member' => $user_info['id'],
				)
			);
		}
	

	}
	
	/**
	 * Delete Comment
	 */
	public static function _Delete()
	{
		global $context, $smcFunc, $sourcedir, $user_info, $txt, $modSettings;
	
		if (!ProjectTools_IssueTracker_Issue::getCurrent() || empty($_REQUEST['com']))
			fatal_lang_error('issue_not_found', false);
	
		ProjectTools::isAllowedTo('edit_comment_own');
		require_once($sourcedir . '/Subs-Post.php');
	
		$request = $smcFunc['db_query']('', '
			SELECT iv.id_issue_event, iv.changes, iv.id_comment, c.id_event, iv.poster_name, iv.id_member
			FROM {db_prefix}issue_events AS iv
			WHERE iv.id_comment = {int:comment}' . (!ProjectTools::allowedTo('edit_comment_any') ? '
				AND iv.id_member = {int:current_user}' : '') . '
				AND iv.id_issue = {int:issue}
			ORDER BY id_comment',
			array(
				'current_user' => $user_info['id'],
				'comment' => (int) $_REQUEST['com'],
				'issue' => ProjectTools_IssueTracker_Issue::getCurrent()->id,
			)
		);
	
		$row = $smcFunc['db_fetch_assoc']($request);
		if (!$row)
			fatal_lang_error('comment_not_found', false);
		$smcFunc['db_free_result']($request);
	
		if ($row['id_comment'] == ProjectTools_IssueTracker_Issue::getCurrent()->details['id'])
			fatal_lang_error('comment_cant_remove_first', false);
	
		// Delete comment
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}issue_comments
			WHERE id_comment = {int:comment}',
			array(
				'comment' => $row['id_comment'],
			)
		);
	
		$event_data = unserialize($row['changes']);
		
		// By default remove event too
		$removeEvent = true;
	
		if (!empty($event_data))
			$removeEvent = false;
	
		if ($removeEvent)
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}issue_events
				WHERE id_issue_event = {int:issue_event}',
				array(
					'issue_event' => $row['id_issue_event'],
				)
			);
		else
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}issue_events
				SET id_comment = 0
				WHERE id_issue_event = {int:issue_event}',
				array(
					'issue_event' => $row['id_issue_event'],
				)
			);
	
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}issues
				SET replies = {int:replies}
				WHERE id_issue = {int:issue}',
				array(
					'issue' => ProjectTools_IssueTracker_Issue::getCurrent()->id,
					'replies' => $num_replies,
				)
			);
		}
		
		// CreateTimeline?/project Adminlog?
		logAction('project_remove_comment', array('comment' => $row['id_comment']));
	
		redirectexit(ProjectTools::get_url(array('issue' => ProjectTools_IssueTracker_Issue::getCurrent()->id . '.0')));
	}
}

?>
