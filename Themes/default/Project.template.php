<?php
// Version: 0.1 Alpha; Project

function template_project_list()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<div class="projectlistframe tborder">';

	if (!empty($context['projects']))
	{
		echo '
		<h3 class="catbg headerpadding">', $txt['project'], '</h3>
		<table cellspacing="1" class="bordercolor projectsframe">';

		foreach ($context['projects'] as $i => $project)
		{
			echo '
			<tr>
				<td class="windowbg icon">
				</td>
				<td class="windowbg2 info">
					<h4><a href="', $project['link'], '">', $project['name'], '</a></h4>
					<p class="smalltext">', $project['description'], '</p>
				</td>
				<td class="windowbg stats smalltext">';

			/*foreach ($project['issues'] as $type)
				echo '
				<div class="smalltext" title="', sprintf($txt['project_open_closed'], $type['open'], $type['closed']), '"><span><a href="', $type['link'], '" style="color: gray">', $type['info']['plural'], '</a></span> <span><a href="', $type['link'], '">', $type['total'], '</a></span></div>';*/

			foreach ($project['issues'] as $type)
				echo '
					', $type['total'], ' ', $type['info']['plural'], '<br />';

			echo '
				</td>
				<td class="windowbg lastissue">
				</td>
			</tr>';
		}

		echo '
		</table>';
	}
	else
	{
		echo '
				<tr>
					<td class="catbg3"><b>', $txt['no_projects'], '</b></td>
				</tr>';
	}

	echo '
	</div>';
}

?>