<?php
// Version: 0.2; IssueView

function template_issue_view_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings, $settings;

	$reply_button = create_button('quote.gif', 'reply_quote', 'quote', 'align="middle"');
	$modify_button = create_button('modify.gif', 'modify_msg', 'modify', 'align="middle"');
	$remove_button = create_button('delete.gif', 'remove_comment_alt', 'remove_comment', 'align="middle"');

	$buttons = array(
		'reply' => array(
			'text' => 'reply',
			'test' => 'can_comment',
			'image' => 'reply_issue.gif',
			'url' => project_get_url(array('issue' => $context['current_issue']['id'] . '.0' , 'sa' => 'reply')),
			'lang' => true
		),
	);

	$issueDetails = &$context['current_issue']['details'];
	$alternate = false;

	if ($issueDetails['first_new'])
		echo '
	<a name="new"></a>';

	// Issue Info table
	echo '
	<a name="com', $context['current_issue']['comment_first'], '"></a>
	<div id="issueinfo" class="floatright tborder">
		<h3 class="catbg3 headerpadding clearfix">', $txt['issue_details'], '</h3>
		<div id="issueinfot" class="clearfix topborder windowbg smalltext">
			<ul class="details">
				<li>
					<dl class="clearfix">
						<dt>', $txt['issue_reported'], '</dt>
						<dd>', $context['current_issue']['created'], '</dd>
					</dl>
				</li>
				<li>
					<dl class="clearfix">
						<dt>', $txt['issue_updated'], '</dt>
						<dd>', $context['current_issue']['updated'], '</dd>
					</dl>
				</li>
				<li id="issue_view_status" class="clearfix">
					<dl class="clearfix">
						<dt>', $txt['issue_view_status'], '</dt>
						<dd>', $context['current_issue']['private'] ? $txt['issue_view_status_private'] : $txt['issue_view_status_public'], '</dd>
					</dl>
				</li>
				<li id="issue_type" class="clearfix">
					<dl class="clearfix">
						<dt>', $txt['issue_type'], '</dt>
						<dd>', $context['current_issue']['type']['name'], '</dd>
					</dl>
				</li>
				<li id="issue_status" class="clearfix">
					<dl class="clearfix">
						<dt>', $txt['issue_status'], '</dt>
						<dd>', $context['current_issue']['status']['text'], '</dd>
					</dl>
				</li>
				<li id="issue_priority" class="clearfix">
					<dl class="clearfix">
						<dt>', $txt['issue_priority'], '</dt>
						<dd>', $txt[$context['current_issue']['priority']], '</dd>
					</dl>
				</li>
				<li id="issue_version" class="clearfix">
					<dl class="clearfix">
						<dt>', $txt['issue_version'], '</dt>
						<dd>', !empty($context['current_issue']['version']['id']) ? $context['current_issue']['version']['name'] : $txt['issue_none'], '</dd>
					</dl>
				</li>
				<li id="issue_verfix" class="clearfix">
					<dl class="clearfix">
						<dt>', $txt['issue_version_fixed'], '</dt>
						<dd>', !empty($context['current_issue']['version_fixed']['id']) ? $context['current_issue']['version_fixed']['name'] : $txt['issue_none'], '</dd>
					</dl>
				</li>
				<li id="issue_assign" class="clearfix">
					<dl class="clearfix">
						<dt>', $txt['issue_assigned_to'], '</dt>
						<dd>', !empty($context['current_issue']['assignee']['id']) ? $context['current_issue']['assignee']['link'] : $txt['issue_none'], '</dd>
					</dl>
				</li>
				<li id="issue_category" class="clearfix">
					<dl class="clearfix">
						<dt>', $txt['issue_category'], '</dt>
						<dd>', !empty($context['current_issue']['category']['id']) ? $context['current_issue']['category']['link'] : $txt['issue_none'], '</dd>
					</dl>
				</li>
			</ul>
		</div>
	</div>';

	// Issue Details
	echo '
	<div id="firstcomment" class="tborder">
		<h3 class="catbg3 headerpadding">
			<img src="', $settings['images_url'], '/', $context['current_issue']['type']['image'], '" align="bottom" alt="" width="20" />
			<span>', $txt['issue'], ': ', $context['current_issue']['name'], '</span>
		</h3>
		<div class="bordercolor">
			<div class="clearfix topborder windowbg largepadding">
				<div class="floatleft poster">
					<h4>', $context['current_issue']['reporter']['link'], '</h4>
					<ul class="smalltext">';

	// Show the member's custom title, if they have one.
	if (isset($context['current_issue']['reporter']['title']) && $context['current_issue']['reporter']['title'] != '')
		echo '
						<li>', $context['current_issue']['reporter']['title'], '</li>';

	// Show the member's primary group (like 'Administrator') if they have one.
	if (isset($context['current_issue']['reporter']['group']) && $context['current_issue']['reporter']['group'] != '')
		echo '
						<li>', $context['current_issue']['reporter']['group'], '</li>';

	// Don't show these things for guests.
	if (!$context['current_issue']['reporter']['is_guest'])
	{
		// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
		if ((empty($settings['hide_post_group']) || $context['current_issue']['reporter']['group'] == '') && $context['current_issue']['reporter']['post_group'] != '')
			echo '
						<li>', $context['current_issue']['reporter']['post_group'], '</li>';
		echo '
						<li>', $context['current_issue']['reporter']['group_stars'], '</li>';

		// Is karma display enabled?  Total or +/-?
		if ($modSettings['karmaMode'] == '1')
			echo '
						<li class="margintop">', $modSettings['karmaLabel'], ' ', $context['current_issue']['reporter']['karma']['good'] - $context['current_issue']['reporter']['karma']['bad'], '</li>';
		elseif ($modSettings['karmaMode'] == '2')
			echo '
						<li class="margintop">', $modSettings['karmaLabel'], ' +', $context['current_issue']['reporter']['karma']['good'], '/-', $context['current_issue']['reporter']['karma']['bad'], '</li>';

		// Is this user allowed to modify this member's karma?
		if ($context['current_issue']['reporter']['karma']['allow'])
			echo '
						<li>
								<a href="', $scripturl, '?action=modifykarma;sa=applaud;uid=', $context['current_issue']['reporter']['id'], ';issue=', $context['current_issue']['id'], '.' . $context['start'], ';com=', $issueDetails['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaApplaudLabel'], '</a>
								<a href="', $scripturl, '?action=modifykarma;sa=smite;uid=', $context['current_issue']['reporter']['id'], ';issue=', $context['current_issue']['id'], '.', $context['start'], ';com=', $issueDetails['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaSmiteLabel'], '</a>
						</li>';

		// Show online and offline buttons?
		if (!empty($modSettings['onlineEnable']))
			echo '
						<li>', $context['can_send_pm'] ? '<a href="' . $context['current_issue']['reporter']['online']['href'] . '" title="' . $context['current_issue']['reporter']['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $context['current_issue']['reporter']['online']['image_href'] . '" alt="' . $context['current_issue']['reporter']['online']['text'] . '" border="0" style="margin-top: 2px;" />' : $context['current_issue']['reporter']['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $context['current_issue']['reporter']['online']['text'] . '</span>' : '', '</li>';

		// Show the member's gender icon?
		if (!empty($settings['show_gender']) && $context['current_issue']['reporter']['gender']['image'] != '' && !isset($context['disabled_fields']['gender']))
			echo '
						<li>', $txt['gender'], ': ', $context['current_issue']['reporter']['gender']['image'], '</li>';

		// Show how many posts they have made.
		if (!isset($context['disabled_fields']['posts']))
			echo '
						<li>', $txt['member_postcount'], ': ', $context['current_issue']['reporter']['posts'], '</li>';

		// Any custom fields?
		if (!empty($context['current_issue']['reporter']['custom_fields']))
		{
			foreach ($context['current_issue']['reporter']['custom_fields'] as $custom)
				echo '
						<li>', $custom['title'], ': ', $custom['value'], '</li>';
		}

		// Show avatars, images, etc.?
		if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($context['current_issue']['reporter']['avatar']['image']))
			echo '
						<li class="margintop" style="overflow: auto;">', $context['current_issue']['reporter']['avatar']['image'], '</li>';

		// Show their personal text?
		if (!empty($settings['show_blurb']) && $context['current_issue']['reporter']['blurb'] != '')
			echo '
						<li>', $context['current_issue']['reporter']['blurb'], '</li>';

		// This shows the popular messaging icons.
		if ($context['current_issue']['reporter']['has_messenger'] && $context['current_issue']['reporter']['can_view_profile'])
			echo '
						<li>
							<ul class="nolist">
								', !isset($context['disabled_fields']['icq']) && !empty($context['current_issue']['reporter']['icq']['link']) ? '<li>' . $context['current_issue']['reporter']['icq']['link'] . '</li>' : '', '
								', !isset($context['disabled_fields']['msn']) && !empty($context['current_issue']['reporter']['msn']['link']) ? '<li>' . $context['current_issue']['reporter']['msn']['link'] . '</li>' : '', '
								', !isset($context['disabled_fields']['aim']) && !empty($context['current_issue']['reporter']['aim']['link']) ? '<li>' . $context['current_issue']['reporter']['aim']['link'] . '</li>' : '', '
								', !isset($context['disabled_fields']['yim']) && !empty($context['current_issue']['reporter']['yim']['link']) ? '<li>' . $context['current_issue']['reporter']['yim']['link'] . '</li>' : '', '
							</ul>
						</li>';

		// Show the profile, website, email address, and personal message buttons.
		if ($settings['show_profile_buttons'])
		{
			echo '
						<li>
							<ul class="nolist">';
			// Don't show the profile button if you're not allowed to view the profile.
			if ($context['current_issue']['reporter']['can_view_profile'])
				echo '
								<li><a href="', $context['current_issue']['reporter']['href'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/icons/profile_sm.gif" alt="' . $txt['view_profile'] . '" title="' . $txt['view_profile'] . '" border="0" />' : $txt['view_profile']), '</a></li>';

			// Don't show an icon if they haven't specified a website.
			if ($context['current_issue']['reporter']['website']['url'] != '' && !isset($context['disabled_fields']['website']))
				echo '
								<li><a href="', $context['current_issue']['reporter']['website']['url'], '" title="' . $context['current_issue']['reporter']['website']['title'] . '" target="_blank" class="new_win">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $txt['www'] . '" border="0" />' : $txt['www']), '</a></li>';

			// Don't show the email address if they want it hidden.
			if (in_array($context['current_issue']['reporter']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
				echo '
								<li><a href="', $scripturl, '?action=emailuser;sa=email;com=', $issueDetails['id'], '" rel="nofollow">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']), '</a></li>';

			// Since we know this person isn't a guest, you *can* message them.
			if ($context['can_send_pm'])
				echo '
								<li><a href="', $scripturl, '?action=pm;sa=send;u=', $context['current_issue']['reporter']['id'], '" title="', $context['current_issue']['reporter']['online']['label'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/im_' . ($context['current_issue']['reporter']['online']['is_online'] ? 'on' : 'off') . '.gif" alt="' . $context['current_issue']['reporter']['online']['label'] . '" border="0" />' : $context['current_issue']['reporter']['online']['label'], '</a></li>';

			echo '
							</ul>
						</li>';
		}

		// Are we showing the warning status?
		if (!isset($context['disabled_fields']['warning_status']) && $context['current_issue']['reporter']['warning_status'] && ($context['user']['can_mod'] || (!empty($modSettings['warning_show']) && ($modSettings['warning_show'] > 1 || $context['current_issue']['reporter']['id'] == $context['user']))))
			echo '
						<li>', $context['can_issue_warning'] ? '<a href="' . $scripturl . '?action=profile;u=' . $context['current_issue']['reporter']['id'] . ';sa=issueWarning">' : '', '<img src="', $settings['images_url'], '/warning_', $context['current_issue']['reporter']['warning_status'], '.gif" alt="', $txt['user_warn_' . $context['current_issue']['reporter']['warning_status']], '" />', $context['can_issue_warning'] ? '</a>' : '', '<span class="warn_', $context['current_issue']['reporter']['warning_status'], '">', $txt['warn_' . $context['current_issue']['reporter']['warning_status']], '</span></li>';
	}
	// Otherwise, show the guest's email.
	elseif (in_array($context['current_issue']['reporter']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
		echo '
						<li><a href="', $scripturl, '?action=emailuser;sa=email;com=', $issueDetails['id'], '" rel="nofollow">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" border="0" />' : $txt['email']), '</a></li>';

	echo '
					</ul>
				</div>
				<div class="postarea">
					<div class="keyinfo">
						<div class="messageicon floatleft">
							<img src="', $settings['images_url'], '/', $context['current_issue']['type']['image'], '" align="bottom" alt="" width="20" style="padding: 6px 3px" />
						</div>
						<h5><a href="', project_get_url(array('issue' => $context['current_issue']['id'] . '.0')), '#com', $issueDetails['id'], '" rel="nofollow">', $context['current_issue']['name'], '</a></h5>							<div class="smalltext">&#171; <strong>', !empty($issueDetails['counter']) ? $txt['reply'] . ' #' . $issueDetails['counter'] : '', ' ', $txt['on'], ':</strong> ', $issueDetails['time'], ' &#187;</div>
					</div>
					<ul class="smalltext postingbuttons">';

	if ($context['can_comment'])
		echo '
						<li><a href="', project_get_url(array('issue' => $context['current_issue']['id'] . '.0', 'sa' => 'reply', 'quote' => $issueDetails['id'], 'sesc' => $context['session_id'])), '">', $reply_button, '</a></li>';

	if ($issueDetails['can_edit'])
		echo '
						<li><a href="', project_get_url(array('issue' => $context['current_issue']['id'] . '.0', 'sa' => 'edit', 'com' => $issueDetails['id'], 'sesc' => $context['session_id'])), '">', $modify_button, '</a></li>';

	if ($issueDetails['can_remove'])
		echo '
						<li><a href="', project_get_url(array('issue' => $context['current_issue']['id'] . '.0', 'sa' => 'removeComment', 'com' => $issueDetails['id'], 'sesc' => $context['session_id'])), '" onclick="return confirm(\'', $txt['remove_comment_sure'], '?\');">', $remove_button, '</a></li>';

	echo '
					</ul>
					<div id="com_', $issueDetails['id'], '" class="post">
						', $issueDetails['body'], '
					</div>';

	// Show attachments
	if (!empty($context['attachments']))
	{
		echo '
					<hr width="100%" size="1" class="hrcolor" />
					<div style="overflow: auto; width: 100%;">';

		foreach ($context['attachments'] as $attachment)
		{
			if ($attachment['is_image'])
			{
				if ($attachment['thumbnail']['has_thumb'])
					echo '
						<a href="', $attachment['href'], ';image" id="link_', $attachment['id'], '" onclick="', $attachment['thumbnail']['javascript'], '"><img src="', $attachment['thumbnail']['href'], '" alt="" id="thumb_', $attachment['id'], '" border="0" /></a><br />';
				else
					echo '
						<img src="' . $attachment['href'] . ';image" alt="" width="' . $attachment['width'] . '" height="' . $attachment['height'] . '" border="0" /><br />';
			}
			echo '
						<a href="' . $attachment['href'] . '"><img src="' . $settings['images_url'] . '/icons/clip.gif" align="middle" alt="*" border="0" />&nbsp;' . $attachment['name'] . '</a> ';

			echo '
							(', $attachment['size'], ($attachment['is_image'] ? ', ' . $attachment['real_width'] . 'x' . $attachment['real_height'] . ' - ' . $txt['attach_viewed'] : ' - ' . $txt['attach_downloaded']) . ' ' . $attachment['downloads'] . ' ' . $txt['attach_times'] . '.)<br />';
		}

		echo '
					</div>';
	}

	echo '
				</div>
				<div class="moderatorbar">
					<div class="smalltext floatleft">';

	// Show "� Last Edit: Time by Person �" if this post was edited.
	if ($settings['show_modify'] && !empty($issueDetails['modified']['name']))
		echo '
						&#171; <em>', $txt['last_edit'], ': ', $issueDetails['modified']['time'], ' ', $txt['by'], ' ', $issueDetails['modified']['name'], '</em> &#187;';

	echo '
					</div>
					<div class="smalltext floatright">';
	echo '
						<img src="', $settings['images_url'], '/ip.gif" alt="" border="0" />';

	// Show the IP to this user for this post - because you can moderate?
	if (allowedTo('moderate_forum') && !empty($issueDetails['ip']))
		echo '
						<a href="', $scripturl, '?action=trackip;searchip=', $issueDetails['ip'], '">', $issueDetails['ip'], '</a> <a href="', $scripturl, '?action=helpadmin;help=see_admin_ip" onclick="return reqWin(this.href);" class="help">(?)</a>';
	// Or, should we show it because this is you?
	elseif ($issueDetails['can_see_ip'])
		echo '
						<a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqWin(this.href);" class="help">', $issueDetails['ip'], '</a>';
	// Okay, are you at least logged in?  Then we can show something about why IPs are logged...
	elseif (!$context['user']['is_guest'])
		echo '
						<a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqWin(this.href);" class="help">', $txt['logged'], '</a>';
	// Otherwise, you see NOTHING!
	else
		echo '
						', $txt['logged'];

	echo '
					</div>
				</div>
			</div>
		</div>
	</div><br />';

	// Javascript for Dropdowns
	if (!empty($context['can_issue_update']))
	{
		echo '
	<script language="JavaScript" type="text/javascript">
		var ddIssueType = new PTDropdown("issue_type", "type", "', $context['current_issue']['type']['id'], '", ', $context['current_issue']['id'], ', "', $context['session_id'], '");
		var ddIssueCate = new PTDropdown("issue_category", "category", ', (int) $context['current_issue']['category']['id'], ', ', $context['current_issue']['id'], ', "', $context['session_id'], '");
		var ddIssueVers = new PTDropdown("issue_version", "version", ', (int) $context['current_issue']['version']['id'], ', ', $context['current_issue']['id'], ', "', $context['session_id'], '");
		var ddIssueViewS = new PTDropdown("issue_view_status", "private", ', (int) $context['current_issue']['private'], ', ', $context['current_issue']['id'], ', "', $context['session_id'], '");
		ddIssueViewS.addOption(0, "', $txt['issue_view_status_public'], '");
		ddIssueViewS.addOption(1, "', $txt['issue_view_status_private'], '");
		ddIssueCate.addOption(0, "', $txt['issue_none'], '");
		ddIssueVers.addOption(0, "', $txt['issue_none'], '");';

		// Types
		foreach ($context['possible_types'] as $id => $type)
			echo '
		ddIssueType.addOption("', $id, '", "', $type['name'], '");';

		// Categories
		foreach ($context['project']['category'] as $c)
			echo '
		ddIssueCate.addOption(', $c['id'], ', "', $c['name'], '");';

		// Versions
		foreach ($context['versions'] as $v)
		{
			echo '
		ddIssueVers.addOption(', $v['id'], ', "', $v['name'], '", "font-weight: bold");';

			foreach ($v['sub_versions'] as $subv)
				echo '
		ddIssueVers.addOption(', $subv['id'], ', "', $subv['name'], '");';
		}

		if (!empty($context['can_issue_moderate']))
		{
			echo '
		var ddIssueStat = new PTDropdown("issue_status", "status", ', (int) $context['current_issue']['status']['id'], ', ', $context['current_issue']['id'], ', "', $context['session_id'], '");
		var ddIssueAssi = new PTDropdown("issue_assign", "assign", ', (int) $context['current_issue']['assignee']['id'], ', ', $context['current_issue']['id'], ', "', $context['session_id'], '");
		var ddIssueFixv = new PTDropdown("issue_verfix", "version_fixed", ', (int) $context['current_issue']['version_fixed']['id'], ', ', $context['current_issue']['id'], ', "', $context['session_id'], '")
		var ddIssuePrio = new PTDropdown("issue_priority", "priority", ', (int) $context['current_issue']['priority_num'], ', ', $context['current_issue']['id'], ', "', $context['session_id'], '");
		ddIssueFixv.addOption(0, "', $txt['issue_none'], '");
		ddIssueAssi.addOption(0, "', $txt['issue_none'], '");';

			// Status
			foreach ($context['issue_status'] as $status)
				echo '
		ddIssueStat.addOption(', $status['id'], ', "', $status['text'], '");';

			// Members
			foreach ($context['assign_members'] as $mem)
				echo '
		ddIssueAssi.addOption(', $mem['id'], ', "', $mem['name'], '");';

			// Versions
			foreach ($context['versions'] as $v)
			{
				echo '
		ddIssueFixv.addOption(', $v['id'], ', "', $v['name'], '", "font-weight: bold");';

				foreach ($v['sub_versions'] as $subv)
					echo '
		ddIssueFixv.addOption(', $subv['id'], ', "', $subv['name'], '");';
			}

			// Priorities
			foreach ($context['issue']['priority'] as $id => $text)
				echo '
		ddIssuePrio.addOption(', $id, ', "', $txt[$text], '");';

		}

		echo '
	</script>';
	}

	echo '
	<div style="text-align: right">
		<form action="', project_get_url(), '" method="get">
			<select name="issue">
				<option value="', $context['current_issue']['id'], '.0"', $context['current_view'] == 'comments' ? ' selected="selected"' : '', '>', $txt['issue_view_comments'], '</option>
				<option value="', $context['current_issue']['id'], '.log"', $context['current_view'] == 'log' ? ' selected="selected"' : '', '>', $txt['issue_view_changes'], '</option>
			</select>
			<input type="submit" value="', $txt['go'], '" />
		</form>
	</div><br />';
}

function template_issue_comments_new()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings, $settings;

	// Print out comments
	if ($context['num_events'] == 0)
		return;

	$alternate = true;

	$reply_button = create_button('quote.gif', 'reply_quote', 'quote', 'align="middle"');
	$modify_button = create_button('modify.gif', 'modify_msg', 'modify', 'align="middle"');
	$remove_button = create_button('delete.gif', 'remove_comment_alt', 'remove_comment', 'align="middle"');

	$buttons = array(
		'reply' => array(
			'text' => 'reply',
			'test' => 'can_comment',
			'image' => 'reply_issue.gif',
			'url' => $scripturl . '?issue=' . $context['current_issue']['id'] . '.0;sa=reply',
			'lang' => true
		),
	);

	echo '
	<div class="modbuttons clearfix margintop">
		<div class="floatleft middletext">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#top"><b>' . $txt['go_up'] . '</b></a>' : '', '</div>
		', template_button_strip($buttons, 'bottom'), '
	</div>
	<div class="tborder">
		<h3 class="catbg3 headerpadding">
			', $txt['issue_comments'], '
		</h3>
		<div class="bordercolor">';

	while ($comment = getEvent())
	{
		echo '
			<div class="clearfix topborder windowbg', $alternate ? '' : '2', ' largepadding" id="com' . $comment['id'] . '">
				<div class="floatleft poster">
					<h4>', $comment['member']['link'], '</h4>
					<ul class="smalltext">';

		// Show the member's custom title, if they have one.
		if (isset($comment['member']['title']) && $comment['member']['title'] != '')
			echo '
						<li>', $comment['member']['title'], '</li>';

		// Show the member's primary group (like 'Administrator') if they have one.
		if (isset($comment['member']['group']) && $comment['member']['group'] != '')
			echo '
						<li>', $comment['member']['group'], '</li>';

		// Don't show these things for guests.
		if (!$comment['member']['is_guest'])
		{
			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $comment['member']['group'] == '') && $comment['member']['post_group'] != '')
				echo '
						<li>', $comment['member']['post_group'], '</li>';
			echo '
						<li>', $comment['member']['group_stars'], '</li>';

			// Is karma display enabled?  Total or +/-?
			if ($modSettings['karmaMode'] == '1')
				echo '
						<li class="margintop">', $modSettings['karmaLabel'], ' ', $comment['member']['karma']['good'] - $comment['member']['karma']['bad'], '</li>';
			elseif ($modSettings['karmaMode'] == '2')
				echo '
						<li class="margintop">', $modSettings['karmaLabel'], ' +', $comment['member']['karma']['good'], '/-', $comment['member']['karma']['bad'], '</li>';

			// Is this user allowed to modify this member's karma?
			if ($comment['member']['karma']['allow'])
				echo '
						<li>
								<a href="', $scripturl, '?action=modifykarma;sa=applaud;uid=', $comment['member']['id'], ';issue=', $context['current_issue']['id'], '.' . $context['start'], ';com=', $comment['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaApplaudLabel'], '</a>
								<a href="', $scripturl, '?action=modifykarma;sa=smite;uid=', $comment['member']['id'], ';issue=', $context['current_issue']['id'], '.', $context['start'], ';com=', $comment['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaSmiteLabel'], '</a>
						</li>';

			// Show online and offline buttons?
			if (!empty($modSettings['onlineEnable']))
				echo '
						<li>', $context['can_send_pm'] ? '<a href="' . $comment['member']['online']['href'] . '" title="' . $comment['member']['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $comment['member']['online']['image_href'] . '" alt="' . $comment['member']['online']['text'] . '" border="0" style="margin-top: 2px;" />' : $comment['member']['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $comment['member']['online']['text'] . '</span>' : '', '</li>';

			// Show the member's gender icon?
			if (!empty($settings['show_gender']) && $comment['member']['gender']['image'] != '' && !isset($context['disabled_fields']['gender']))
				echo '
						<li>', $txt['gender'], ': ', $comment['member']['gender']['image'], '</li>';

			// Show how many posts they have made.
			if (!isset($context['disabled_fields']['posts']))
				echo '
						<li>', $txt['member_postcount'], ': ', $comment['member']['posts'], '</li>';

			// Any custom fields?
			if (!empty($comment['member']['custom_fields']))
			{
				foreach ($comment['member']['custom_fields'] as $custom)
					echo '
						<li>', $custom['title'], ': ', $custom['value'], '</li>';
			}

			// Show avatars, images, etc.?
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($comment['member']['avatar']['image']))
				echo '
						<li class="margintop" style="overflow: auto;">', $comment['member']['avatar']['image'], '</li>';

			// Show their personal text?
			if (!empty($settings['show_blurb']) && $comment['member']['blurb'] != '')
				echo '
						<li>', $comment['member']['blurb'], '</li>';

			// This shows the popular messaging icons.
			if ($comment['member']['has_messenger'] && $comment['member']['can_view_profile'])
				echo '
						<li>
							<ul class="nolist">
								', !isset($context['disabled_fields']['icq']) && !empty($comment['member']['icq']['link']) ? '<li>' . $comment['member']['icq']['link'] . '</li>' : '', '
								', !isset($context['disabled_fields']['msn']) && !empty($comment['member']['msn']['link']) ? '<li>' . $comment['member']['msn']['link'] . '</li>' : '', '
								', !isset($context['disabled_fields']['aim']) && !empty($comment['member']['aim']['link']) ? '<li>' . $comment['member']['aim']['link'] . '</li>' : '', '
								', !isset($context['disabled_fields']['yim']) && !empty($comment['member']['yim']['link']) ? '<li>' . $comment['member']['yim']['link'] . '</li>' : '', '
							</ul>
						</li>';

			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{
				echo '
						<li>
							<ul class="nolist">';
				// Don't show the profile button if you're not allowed to view the profile.
				if ($comment['member']['can_view_profile'])
					echo '
								<li><a href="', $comment['member']['href'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/icons/profile_sm.gif" alt="' . $txt['view_profile'] . '" title="' . $txt['view_profile'] . '" border="0" />' : $txt['view_profile']), '</a></li>';

				// Don't show an icon if they haven't specified a website.
				if ($comment['member']['website']['url'] != '' && !isset($context['disabled_fields']['website']))
					echo '
								<li><a href="', $comment['member']['website']['url'], '" title="' . $comment['member']['website']['title'] . '" target="_blank" class="new_win">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $txt['www'] . '" border="0" />' : $txt['www']), '</a></li>';

				// Don't show the email address if they want it hidden.
				if (in_array($comment['member']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
					echo '
								<li><a href="', $scripturl, '?action=emailuser;sa=email;com=', $comment['id'], '" rel="nofollow">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']), '</a></li>';

				// Since we know this person isn't a guest, you *can* message them.
				if ($context['can_send_pm'])
					echo '
								<li><a href="', $scripturl, '?action=pm;sa=send;u=', $comment['member']['id'], '" title="', $comment['member']['online']['label'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/im_' . ($comment['member']['online']['is_online'] ? 'on' : 'off') . '.gif" alt="' . $comment['member']['online']['label'] . '" border="0" />' : $comment['member']['online']['label'], '</a></li>';

				echo '
							</ul>
						</li>';
			}

			// Are we showing the warning status?
			if (!isset($context['disabled_fields']['warning_status']) && $comment['member']['warning_status'] && ($context['user']['can_mod'] || (!empty($modSettings['warning_show']) && ($modSettings['warning_show'] > 1 || $comment['member']['id'] == $context['user']))))
				echo '
						<li>', $context['can_issue_warning'] ? '<a href="' . $scripturl . '?action=profile;u=' . $comment['member']['id'] . ';sa=issueWarning">' : '', '<img src="', $settings['images_url'], '/warning_', $comment['member']['warning_status'], '.gif" alt="', $txt['user_warn_' . $comment['member']['warning_status']], '" />', $context['can_issue_warning'] ? '</a>' : '', '<span class="warn_', $comment['member']['warning_status'], '">', $txt['warn_' . $comment['member']['warning_status']], '</span></li>';
		}
		// Otherwise, show the guest's email.
		elseif (in_array($comment['member']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
			echo '
						<li><a href="', $scripturl, '?action=emailuser;sa=email;com=', $comment['id'], '" rel="nofollow">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" border="0" />' : $txt['email']), '</a></li>';

		echo '
					</ul>
				</div>
				<div class="postarea">
					<div class="keyinfo">
						<div class="smalltext">&#171; <strong>', !empty($comment['counter']) ? $txt['reply'] . ' #' . $comment['counter'] : '', ' ', $txt['on'], ':</strong> ', $comment['time'], ' &#187;</div>
					</div>
					<ul class="smalltext postingbuttons">';

		if ($context['can_comment'])
			echo '
						<li><a href="', project_get_url(array('issue' => $context['current_issue']['id'] . '.0', 'sa' => 'reply', 'quote' => $comment['id'], 'sesc' => $context['session_id'])), '">', $reply_button, '</a></li>';

		if ($comment['can_edit'])
			echo '
						<li><a href="', project_get_url(array('issue' => $context['current_issue']['id'] . '.0', 'sa' => 'edit', 'com' => $comment['id'], 'sesc' => $context['session_id'])), '">', $modify_button, '</a></li>';

		if ($comment['can_remove'])
			echo '
						<li><a href="', project_get_url(array('issue' => $context['current_issue']['id'] . '.0', 'sa' => 'removeComment', 'com' => $comment['id'], 'sesc' => $context['session_id'])), '" onclick="return confirm(\'', $txt['remove_comment_sure'], '?\');">', $remove_button, '</a></li>';

		echo '
					</ul>
					<div id="com_', $comment['id'], '" class="post">
						', $comment['body'];

		if (!empty($comment['changes']))
		{
			echo '
						<ul class="smalltext">';

			foreach ($comment['changes'] as $change)
			{
				echo '
							<li>
							', $change, '
							</li>';
			}

			echo '
						</ul>';
		}

		echo '
					</div>
				</div>
				<div class="moderatorbar">
					<div class="smalltext floatleft">';

		// Show "� Last Edit: Time by Person �" if this post was edited.
		if ($settings['show_modify'] && !empty($comment['modified']['name']))
			echo '
						&#171; <em>', $txt['last_edit'], ': ', $comment['modified']['time'], ' ', $txt['by'], ' ', $comment['modified']['name'], '</em> &#187;';

		echo '
					</div>
					<div class="smalltext floatright">';
		echo '
						<img src="', $settings['images_url'], '/ip.gif" alt="" border="0" />';

		// Show the IP to this user for this post - because you can moderate?
		if (allowedTo('moderate_forum') && !empty($comment['ip']))
			echo '
						<a href="', $scripturl, '?action=trackip;searchip=', $comment['ip'], '">', $comment['ip'], '</a> <a href="', $scripturl, '?action=helpadmin;help=see_admin_ip" onclick="return reqWin(this.href);" class="help">(?)</a>';
		// Or, should we show it because this is you?
		elseif ($comment['can_see_ip'])
			echo '
						<a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqWin(this.href);" class="help">', $comment['ip'], '</a>';
		// Okay, are you at least logged in?  Then we can show something about why IPs are logged...
		elseif (!$context['user']['is_guest'])
			echo '
						<a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqWin(this.href);" class="help">', $txt['logged'], '</a>';
		// Otherwise, you see NOTHING!
		else
			echo '
						', $txt['logged'];

		echo '
					</div>';

		// Show the member's signature?
		if (!empty($comment['member']['signature']) && empty($options['show_no_signatures']))
			echo '
						<hr width="100%" size="1" style="clear: right;" class="margintop hrcolor" />
						<div class="signature">', $comment['member']['signature'], '</div>';

		echo '
				</div>
			</div>';

		$alternate = !$alternate;
	}

	echo '
		</div>
	</div>
	<div class="modbuttons clearfix marginbottom">
		<div class="floatleft middletext">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#top"><b>' . $txt['go_up'] . '</b></a>' : '', '</div>
		', template_button_strip($buttons, 'top'), '
	</div><br />';
}

function template_issue_comments()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings, $settings;

	// Print out comments
	if ($context['num_comments'] == 0)
		return;

	$alternate = true;

	$reply_button = create_button('quote.gif', 'reply_quote', 'quote', 'align="middle"');
	$modify_button = create_button('modify.gif', 'modify_msg', 'modify', 'align="middle"');
	$remove_button = create_button('delete.gif', 'remove_comment_alt', 'remove_comment', 'align="middle"');

	$buttons = array(
		'reply' => array(
			'text' => 'reply',
			'test' => 'can_comment',
			'image' => 'reply_issue.gif',
			'url' => $scripturl . '?issue=' . $context['current_issue']['id'] . '.0;sa=reply',
			'lang' => true
		),
	);

	echo '
	<div class="modbuttons clearfix margintop">
		<div class="floatleft middletext">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#top"><b>' . $txt['go_up'] . '</b></a>' : '', '</div>
		', template_button_strip($buttons, 'bottom'), '
	</div>
	<div class="tborder">
		<h3 class="catbg3 headerpadding">
			', $txt['issue_comments'], '
		</h3>
		<div class="bordercolor">';

	while ($comment = getComment())
	{
		echo '
			<div class="clearfix topborder windowbg', $alternate ? '' : '2', ' largepadding" id="com' . $comment['id'] . '">
				<div class="floatleft poster">
					<h4>', $comment['member']['link'], '</h4>
					<ul class="smalltext">';

		// Show the member's custom title, if they have one.
		if (isset($comment['member']['title']) && $comment['member']['title'] != '')
			echo '
						<li>', $comment['member']['title'], '</li>';

		// Show the member's primary group (like 'Administrator') if they have one.
		if (isset($comment['member']['group']) && $comment['member']['group'] != '')
			echo '
						<li>', $comment['member']['group'], '</li>';

		// Don't show these things for guests.
		if (!$comment['member']['is_guest'])
		{
			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $comment['member']['group'] == '') && $comment['member']['post_group'] != '')
				echo '
						<li>', $comment['member']['post_group'], '</li>';
			echo '
						<li>', $comment['member']['group_stars'], '</li>';

			// Is karma display enabled?  Total or +/-?
			if ($modSettings['karmaMode'] == '1')
				echo '
						<li class="margintop">', $modSettings['karmaLabel'], ' ', $comment['member']['karma']['good'] - $comment['member']['karma']['bad'], '</li>';
			elseif ($modSettings['karmaMode'] == '2')
				echo '
						<li class="margintop">', $modSettings['karmaLabel'], ' +', $comment['member']['karma']['good'], '/-', $comment['member']['karma']['bad'], '</li>';

			// Is this user allowed to modify this member's karma?
			if ($comment['member']['karma']['allow'])
				echo '
						<li>
								<a href="', $scripturl, '?action=modifykarma;sa=applaud;uid=', $comment['member']['id'], ';issue=', $context['current_issue']['id'], '.' . $context['start'], ';com=', $comment['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaApplaudLabel'], '</a>
								<a href="', $scripturl, '?action=modifykarma;sa=smite;uid=', $comment['member']['id'], ';issue=', $context['current_issue']['id'], '.', $context['start'], ';com=', $comment['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaSmiteLabel'], '</a>
						</li>';

			// Show online and offline buttons?
			if (!empty($modSettings['onlineEnable']))
				echo '
						<li>', $context['can_send_pm'] ? '<a href="' . $comment['member']['online']['href'] . '" title="' . $comment['member']['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $comment['member']['online']['image_href'] . '" alt="' . $comment['member']['online']['text'] . '" border="0" style="margin-top: 2px;" />' : $comment['member']['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $comment['member']['online']['text'] . '</span>' : '', '</li>';

			// Show the member's gender icon?
			if (!empty($settings['show_gender']) && $comment['member']['gender']['image'] != '' && !isset($context['disabled_fields']['gender']))
				echo '
						<li>', $txt['gender'], ': ', $comment['member']['gender']['image'], '</li>';

			// Show how many posts they have made.
			if (!isset($context['disabled_fields']['posts']))
				echo '
						<li>', $txt['member_postcount'], ': ', $comment['member']['posts'], '</li>';

			// Any custom fields?
			if (!empty($comment['member']['custom_fields']))
			{
				foreach ($comment['member']['custom_fields'] as $custom)
					echo '
						<li>', $custom['title'], ': ', $custom['value'], '</li>';
			}

			// Show avatars, images, etc.?
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($comment['member']['avatar']['image']))
				echo '
						<li class="margintop" style="overflow: auto;">', $comment['member']['avatar']['image'], '</li>';

			// Show their personal text?
			if (!empty($settings['show_blurb']) && $comment['member']['blurb'] != '')
				echo '
						<li>', $comment['member']['blurb'], '</li>';

			// This shows the popular messaging icons.
			if ($comment['member']['has_messenger'] && $comment['member']['can_view_profile'])
				echo '
						<li>
							<ul class="nolist">
								', !isset($context['disabled_fields']['icq']) && !empty($comment['member']['icq']['link']) ? '<li>' . $comment['member']['icq']['link'] . '</li>' : '', '
								', !isset($context['disabled_fields']['msn']) && !empty($comment['member']['msn']['link']) ? '<li>' . $comment['member']['msn']['link'] . '</li>' : '', '
								', !isset($context['disabled_fields']['aim']) && !empty($comment['member']['aim']['link']) ? '<li>' . $comment['member']['aim']['link'] . '</li>' : '', '
								', !isset($context['disabled_fields']['yim']) && !empty($comment['member']['yim']['link']) ? '<li>' . $comment['member']['yim']['link'] . '</li>' : '', '
							</ul>
						</li>';

			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{
				echo '
						<li>
							<ul class="nolist">';
				// Don't show the profile button if you're not allowed to view the profile.
				if ($comment['member']['can_view_profile'])
					echo '
								<li><a href="', $comment['member']['href'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/icons/profile_sm.gif" alt="' . $txt['view_profile'] . '" title="' . $txt['view_profile'] . '" border="0" />' : $txt['view_profile']), '</a></li>';

				// Don't show an icon if they haven't specified a website.
				if ($comment['member']['website']['url'] != '' && !isset($context['disabled_fields']['website']))
					echo '
								<li><a href="', $comment['member']['website']['url'], '" title="' . $comment['member']['website']['title'] . '" target="_blank" class="new_win">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $txt['www'] . '" border="0" />' : $txt['www']), '</a></li>';

				// Don't show the email address if they want it hidden.
				if (in_array($comment['member']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
					echo '
								<li><a href="', $scripturl, '?action=emailuser;sa=email;com=', $comment['id'], '" rel="nofollow">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']), '</a></li>';

				// Since we know this person isn't a guest, you *can* message them.
				if ($context['can_send_pm'])
					echo '
								<li><a href="', $scripturl, '?action=pm;sa=send;u=', $comment['member']['id'], '" title="', $comment['member']['online']['label'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/im_' . ($comment['member']['online']['is_online'] ? 'on' : 'off') . '.gif" alt="' . $comment['member']['online']['label'] . '" border="0" />' : $comment['member']['online']['label'], '</a></li>';

				echo '
							</ul>
						</li>';
			}

			// Are we showing the warning status?
			if (!isset($context['disabled_fields']['warning_status']) && $comment['member']['warning_status'] && ($context['user']['can_mod'] || (!empty($modSettings['warning_show']) && ($modSettings['warning_show'] > 1 || $comment['member']['id'] == $context['user']))))
				echo '
						<li>', $context['can_issue_warning'] ? '<a href="' . $scripturl . '?action=profile;u=' . $comment['member']['id'] . ';sa=issueWarning">' : '', '<img src="', $settings['images_url'], '/warning_', $comment['member']['warning_status'], '.gif" alt="', $txt['user_warn_' . $comment['member']['warning_status']], '" />', $context['can_issue_warning'] ? '</a>' : '', '<span class="warn_', $comment['member']['warning_status'], '">', $txt['warn_' . $comment['member']['warning_status']], '</span></li>';
		}
		// Otherwise, show the guest's email.
		elseif (in_array($comment['member']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
			echo '
						<li><a href="', $scripturl, '?action=emailuser;sa=email;com=', $comment['id'], '" rel="nofollow">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" border="0" />' : $txt['email']), '</a></li>';

		echo '
					</ul>
				</div>
				<div class="postarea">
					<div class="keyinfo">
						<div class="smalltext">&#171; <strong>', !empty($comment['counter']) ? $txt['reply'] . ' #' . $comment['counter'] : '', ' ', $txt['on'], ':</strong> ', $comment['time'], ' &#187;</div>
					</div>
					<ul class="smalltext postingbuttons">';

		if ($context['can_comment'])
			echo '
						<li><a href="', project_get_url(array('issue' => $context['current_issue']['id'] . '.0', 'sa' => 'reply', 'quote' => $comment['id'], 'sesc' => $context['session_id'])), '">', $reply_button, '</a></li>';

		if ($comment['can_edit'])
			echo '
						<li><a href="', project_get_url(array('issue' => $context['current_issue']['id'] . '.0', 'sa' => 'edit', 'com' => $comment['id'], 'sesc' => $context['session_id'])), '">', $modify_button, '</a></li>';

		if ($comment['can_remove'])
			echo '
						<li><a href="', project_get_url(array('issue' => $context['current_issue']['id'] . '.0', 'sa' => 'removeComment', 'com' => $comment['id'], 'sesc' => $context['session_id'])), '" onclick="return confirm(\'', $txt['remove_comment_sure'], '?\');">', $remove_button, '</a></li>';

		echo '
					</ul>
					<div id="com_', $comment['id'], '" class="post">
						', $comment['body'];

		if (!empty($comment['changes']))
		{
			echo '
						<ul class="smalltext">';

			foreach ($comment['changes'] as $change)
			{
				echo '
							<li>
							', $change, '
							</li>';
			}

			echo '
						</ul>';
		}

		echo '
					</div>
				</div>
				<div class="moderatorbar">
					<div class="smalltext floatleft">';

		// Show "� Last Edit: Time by Person �" if this post was edited.
		if ($settings['show_modify'] && !empty($comment['modified']['name']))
			echo '
						&#171; <em>', $txt['last_edit'], ': ', $comment['modified']['time'], ' ', $txt['by'], ' ', $comment['modified']['name'], '</em> &#187;';

		echo '
					</div>
					<div class="smalltext floatright">';
		echo '
						<img src="', $settings['images_url'], '/ip.gif" alt="" border="0" />';

		// Show the IP to this user for this post - because you can moderate?
		if (allowedTo('moderate_forum') && !empty($comment['ip']))
			echo '
						<a href="', $scripturl, '?action=trackip;searchip=', $comment['ip'], '">', $comment['ip'], '</a> <a href="', $scripturl, '?action=helpadmin;help=see_admin_ip" onclick="return reqWin(this.href);" class="help">(?)</a>';
		// Or, should we show it because this is you?
		elseif ($comment['can_see_ip'])
			echo '
						<a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqWin(this.href);" class="help">', $comment['ip'], '</a>';
		// Okay, are you at least logged in?  Then we can show something about why IPs are logged...
		elseif (!$context['user']['is_guest'])
			echo '
						<a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqWin(this.href);" class="help">', $txt['logged'], '</a>';
		// Otherwise, you see NOTHING!
		else
			echo '
						', $txt['logged'];

		echo '
					</div>';

		// Show the member's signature?
		if (!empty($comment['member']['signature']) && empty($options['show_no_signatures']))
			echo '
						<hr width="100%" size="1" style="clear: right;" class="margintop hrcolor" />
						<div class="signature">', $comment['member']['signature'], '</div>';

		echo '
				</div>
			</div>';

		$alternate = !$alternate;
	}

	echo '
		</div>
	</div>
	<div class="modbuttons clearfix marginbottom">
		<div class="floatleft middletext">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#top"><b>' . $txt['go_up'] . '</b></a>' : '', '</div>
		', template_button_strip($buttons, 'top'), '
	</div><br />';
}

function template_issue_log()
{
	global $context, $settings, $options, $txt, $modSettings, $settings;

	echo '
	<div class="tborder">
		<h3 class="catbg3 headerpadding">
			', $txt['issue_changelog'], '
		</h3>
		<div class="windowbg2 clearfix">
			<ul class="changes">';

	foreach ($context['issue_log'] as $event)
	{
		echo '
				<li>
					', $event['time'], ' - ', sprintf($event['event_text'], $event['member_link']);


		if (!empty($event['changes']))
		{
			echo '
					<ul class="smalltext">';

			foreach ($event['changes'] as $change)
			{
				echo '
						<li>
							', sprintf($txt['change_' . $change['field']], $change['old_value'], $change['new_value']), '
						</li>';
			}

			echo '
					</ul>';
		}

		echo '
				</li>';
	}

	echo '
			</ul>
		</div>
	</div>';
}

function template_issue_view_below()
{
	global $context, $settings, $options, $txt, $modSettings, $settings;

	$mod_buttons = array(
		'delete' => array('test' => 'can_issue_moderate', 'text' => 'issue_delete', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt['issue_delete_confirm'] . '\');"', 'url' => project_get_url(array('issue' => $context['current_issue']['id'] . '.0', 'sa' => 'delete', 'sesc' => $context['session_id']))),
		'subscribe' => array('test' => 'can_subscribe', 'text' => empty($context['is_subscribed']) ? 'project_subscribe' : 'project_unsubscribe', 'image' => empty($context['is_subscribed']) ? 'subscribe.gif' : 'unsubscribe.gif', 'lang' => true, 'url' => project_get_url(array('issue' => $context['current_issue']['id'] . '.0', 'sa' => 'subscribe', 'sesc' => $context['session_id']))),
	);

	echo '
	<div id="moderationbuttons">', 	template_button_strip($mod_buttons, 'bottom'), '</div>';

	echo '
	<div class="tborder">
		<div class="titlebg2" style="padding: 4px;" align="', !$context['right_to_left'] ? 'right' : 'left', '">&nbsp;</div>
	</div><br />';

	if ($context['can_comment'])
	{
		echo '
	<form action="', project_get_url(array('issue' => $context['current_issue']['id'] . '.0', 'sa' => 'reply2')), '" method="post">
		<div class="tborder">
			<div class="catbg headerpadding">', $txt['comment_issue'], '</div>
			<div class="smallpadding windowbg" style="text-align: center">
				<textarea id="comment" name="comment" rows="7" cols="75"></textarea>';

		echo '
				<div style="text-align: right">
					<input type="submit" name="post" value="', $txt['add_comment'], '" onclick="return submitThisOnce(this);" accesskey="s" tabindex="2" />
					<input type="submit" name="preview" value="', $txt['preview'], '" onclick="return submitThisOnce(this);" accesskey="p" tabindex="4" />
				</div>
			</div>
		</div><br />
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form><br />';
	}

	echo '
	<form action="', project_get_url(array('issue' => $context['current_issue']['id'], '.0', 'sa' => 'tags')), '" method="post">
		<div class="tborder">
			<div class="catbg headerpadding">', $txt['issue_tags'], '</div>
			<div class="smallpadding windowbg">';

	if (!empty($context['current_tags']) || $context['can_add_tags'])
	{
		echo '
				<ul class="clearfix tags">';

		if (!empty($context['current_tags']))
		{
			foreach ($context['current_tags'] as $tag)
			{
				echo '
					<li>', $tag['link'];

				if ($context['can_remove_tags'])
					echo '
						<a href="', project_get_url(array('issue' => $context['current_issue']['id'], '.0', 'sa' => 'tags', 'remove', 'tag' => $tag['id'], 'sesc' => $context['session_id'])), '"><img src="', $settings['images_url'], '/icons/quick_remove.gif" alt="', $txt['remove_tag'], '" /></a>';

					echo '
					</li>';
			}
		}

		if ($context['can_add_tags'])
			echo '
					<li class="tag_editor">
						<input type="text" name="tag" value="" />
						<input type="submit" name="add_tag" value="', $txt['add_tag'], '" />
					</li>';

		echo '
				</ul>';
	}

	echo '
			</div>
		</div><br />
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form><br />';

	if ($context['can_issue_attach'])
	{
		echo '
	<form action="', project_get_url(array('issue' => $context['current_issue']['id'], '.0', 'sa' => 'upload')), '" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data">
		<div class="tborder">
			<div class="catbg headerpadding">', $txt['issue_attach'], '</div>
			<div class="smallpadding windowbg">
				<input type="file" size="48" name="attachment[]" /><br />';

		if (!empty($modSettings['attachmentCheckExtensions']))
			echo '
					', $txt['allowed_types'], ': ', $context['allowed_extensions'], '<br />';
		echo '
					', $txt['max_size'], ': ', $modSettings['attachmentSizeLimit'], ' ' . $txt['kilobyte'], '<br />';

		echo '
				<div style="text-align: right">
					<input type="submit" name="add_comment" value="', $txt['add_attach'], '" />
				</div>
			</div>
		</div>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
	}

}

?>