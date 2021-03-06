<?php
/**
 * Template for ProjectView.php
 *
 * @package admin
 * @version 0.5
 * @license http://download.smfproject.net/license.php New-BSD
 * @since 0.1
 * @see ProjectView.php
 */

function template_project_view()
{
	global $context, $settings, $options, $txt, $modSettings;

	$project_buttons = array(
		'subscribe' => array('test' => 'can_subscribe', 'text' => empty($context['is_subscribed']) ? 'project_subscribe' : 'project_unsubscribe', 'image' => empty($context['is_subscribed']) ? 'subscribe.gif' : 'unsubscribe.gif', 'lang' => true, 'url' => project_get_url(array('project' => $context['project']['id'], 'sa' => 'subscribe', $context['session_var'] => $context['session_id']))),
		'reportIssue' => array('test' => 'can_report_issues', 'text' => 'new_issue', 'image' => 'new_issue.gif', 'lang' => true, 'url' => project_get_url(array('project' => $context['project']['id'], 'area' => 'issues', 'sa' => 'report')),),
		'markRead' => array('text' => 'project_mark_read', 'image' => 'mark_project_read.gif', 'lang' => true, 'url' => project_get_url(array('project' => $context['project']['id'], 'sa' => 'markasread')))
	);

	echo '
	<div id="modbuttons_top" class="modbuttons clearfix margintop">
		', template_button_strip($project_buttons, 'right'), '
	</div>';

	$side = true;
	
	foreach ($context['project_blocks'] as $block)
	{
		echo '
		<div class="issuecolumn">
			<div class="issue_table columnmargin_', $side ? 'right' : 'left', '">';
			
		if (!empty($block['title']))
			echo '
				<div class="cat_bar">
					<h3 class="catbg">
						', $block['title'], '
					</h3>
				</div>';
		
		$templateFunction = 'template_' . $block['template'];
		$templateFunction($block, $block['data']);
			
		echo '
			</div>
		</div>';

		if (!$side)
			echo '
		<br class="clear" />';

		$side = !$side;
	}

	if (!$side)
		echo '
	<br class="clear" />';

	$width = floor(100 / count($context['issue_status']));
	$tWidth = 100 - ($width * count($context['issue_status'])) + $width;

	echo '
	<div class="tborder clearfix">';

	foreach ($context['issue_status'] as $status)
	{
		echo '
		<div class="floatleft expl issue_', $status['name'], '" style="width:', $tWidth, '%"><span>', $status['text'], '</span></div>';
		$tWidth = $width;
	}
	echo '
	</div>
	<br class="clear" />';

	// Statistics etc
	echo '
	<span class="clear upperframe"><span></span></span>
	<div class="roundframe"><div class="innerframe">
		<div class="cat_bar">
			<h3 class="catbg">
				', $context['project']['name'], '
			</h3>
		</div>
		<div id="upshrinkHeaderIC">
			<div class="title_bar">
				<h3 class="titlebg">
					', $txt['project_statistics'], '
				</h3>
			</div>
			<dl class="stats">';

	foreach ($context['project']['trackers'] as $type)
	{
		echo '
				<dt>
					<a href="', $type['link'], '" style="color: gray">', $type['tracker']['plural'], '</a>
				</dt>
				<dd class="statsbar">';
		if (!empty($type['progress']))
			echo '
					<span class="left"></span>
						<div style="width: ', $type['progress'] * 2, 'px;" class="stats_bar"></div>
					<span class="right"></span>';

		echo '
					<span class="righttext"><span>', $txt['project_open_issues'], ' ', $type['open'], '</span> / <span>', $txt['project_closed_issues'], ' ', $type['closed'], '</span></span>
				</dd>';
	}

	echo '
			</dl>
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['project_timeline'], '
				</h3>
			</div>';

	$first = true;

	foreach ($context['events'] as $date)
	{
		echo '
			<div class="title_bar">
				<h4 class="titlebg', $first ? ' first' : '' ,'">
					', $date['date'], '
				</h4>
			</div>
			<ul class="reset">';

		foreach ($date['events'] as $event)
			echo '
				<li>
					', $event['time'], ' - ', $event['link'], '<br />
					<span class="smalltext">', sprintf($txt['evt_' . (!empty($event['extra']) ? 'extra_' : '') . $event['event']], $event['member_link'], $event['extra']), '</span>
				</li>';

		echo '
			</ul>';

		$first = false;
	}

	echo '
		</div>
	</div></div>
	<span class="lowerframe"><span></span></span>';
}

function template_issue_list_block($block, $data)
{
	global $context, $txt, $settings;
	
	echo '
	<table cellspacing="0" class="table_grid">
		<thead>
			<tr class="catbg">';

		if (!empty($data))
			echo '
				<th scope="col" class="first_th"></th>
				<th scope="col">', $txt['issue_title'], '</th>
				<th scope="col">', $txt['issue_replies'], '</th>
				<th scope="col" class="last_th">', $txt['issue_last_update'], '</th>';
		else
			echo '
				<th scope="col" class="first_th" width="8%">&nbsp;</th>
				<th class="smalltext" colspan="2"><strong>', $txt['issue_no_issues'], '</strong></th>
				<th scope="col" class="last_th" width="8%">&nbsp;</th>';

		echo '
			</tr>
		</thead>
		<tbody>';

		if (!empty($data))
		{
			foreach ($data as $issue)
			{
				echo '
			<tr>
				<td class="windowbg icon">
					<a href="', project_get_url(array('project' => $context['project']['id'], 'area' => 'issues', 'tracker' => $issue['tracker']['short'])), '">
						<img src="', $settings['default_images_url'], '/', $issue['tracker']['image'], '" alt="', $issue['tracker']['name'], '" />
					</a>
				</td>
				<td class="windowbg2 info issue_', $issue['status']['name'], '">
					<h4>
						', !empty($issue['category']['link']) ? '[' . $issue['category']['link'] . '] ' : '', $issue['link'], ' ';

				// Is this topic new? (assuming they are logged in!)
				if ($issue['new'] && $context['user']['is_logged'])
					echo '
									<a href="', $issue['new_href'], '"><img src="', $settings['lang_images_url'], '/new.gif" alt="', $txt['new'], '" /></a>';
				echo '
					</h4>
					<p class="smalltext">', !empty($issue['version']['link']) ? '[' . $issue['version']['link'] . '] ' : '', $issue['reporter']['link'], '</p>
				</td>
				<td class="windowbg replies smalltext">
					', $issue['replies'], '
				</td>
				<td class="windowbg2 lastissue smalltext">
					', $issue['updater']['link'], '<br />
					', $issue['updated'], '
				</td>
			</tr>';
			}

			echo '
			<tr class="catbg">
				<td colspan="4" align="right" class="smalltext">
					<a href="', $block['href'], '">', $txt['issues_view_all'], '</a>
				</td>
			</tr>
		</tbody>';
	}
	
	echo '
	</table>';
}

?>