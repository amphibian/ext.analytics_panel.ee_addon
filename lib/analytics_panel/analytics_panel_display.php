<table class="analytics-panel" cellspacing="0">

	<tr>
		<th colspan="4"><?php echo $LANG->line('today'); ?></th>
	</tr>
	<tr>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo number_format($today->getVisits()) ?></span> <?php echo $LANG->line('visits'); ?></td>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo number_format($today->getPageviews()) ?></span> <?php echo $LANG->line('pageviews'); ?></td>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo $this->analytics_avg_pages($today->getPageviews(), $today->getVisits()) ?></span> <?php echo $LANG->line('pages_per_visit'); ?></td>
		<td class="analytics-stat-col end"><span class="analytics-stat"><?php echo $this->analytics_avg_visit($today->getTimeOnSite(), $today->getVisits()) ?></span> <?php echo $LANG->line('avg_visit'); ?></td>
	</tr>

	<tr>
		<th colspan="4"><?php echo $LANG->line('yesterday'); ?></th>
	</tr>
	<tr>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo $data['yesterday']['visits']; ?></span> <?php echo $LANG->line('visits'); ?></td>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo $data['yesterday']['pageviews'] ?></span> <?php echo $LANG->line('pageviews'); ?></td>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo $data['yesterday']['pages_per_visit']; ?></span> <?php echo $LANG->line('pages_per_visit'); ?></td>		
		<td class="analytics-stat-col end"><span class="analytics-stat"><?php echo $data['yesterday']['avg_visit']; ?></span> <?php echo $LANG->line('avg_visit'); ?></td>
	</tr>				

	<tr>
		<th colspan="4"><?php echo $data['lastmonth']['date_span']; ?></th>
	</tr>
	<tr>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php echo $data['lastmonth']['visits']; ?></span> <?php echo $LANG->line('visits'); ?></td>
		<td class="analytics-sparkline border-right-dotted"><?php echo $data['lastmonth']['visits_sparkline']; ?></td>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php echo $data['lastmonth']['bounce_rate']; ?></span> <?php echo $LANG->line('bounce_rate'); ?></td>
		<td class="analytics-sparkline"><?php echo $data['lastmonth']['bounce_rate_sparkline']; ?></td>
	</tr>
	<tr>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php echo $data['lastmonth']['pageviews']; ?></span> <?php echo $LANG->line('pageviews'); ?></td>
		<td class="analytics-sparkline border-right-dotted"><?php echo $data['lastmonth']['pageviews_sparkline']; ?></td>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php echo $data['lastmonth']['avg_visit']; ?></span> <?php echo $LANG->line('avg_visit'); ?></td>
		<td class="analytics-sparkline"><?php echo $data['lastmonth']['avg_visit_sparkline']; ?></td>
	</tr>
	<tr>
		<td class="analytics-stat-row bottom"><span class="analytics-stat"><?php echo $data['lastmonth']['pages_per_visit']; ?></span> <?php echo $LANG->line('pages_per_visit'); ?></td>
		<td class="analytics-sparkline bottom border-right-dotted"><?php echo $data['lastmonth']['pages_per_visit_sparkline']; ?></td>
		<td class="analytics-stat-row bottom"><span class="analytics-stat"><?php echo $data['lastmonth']['new_visits']; ?></span> <?php echo $LANG->line('new_visits'); ?></td>
		<td class="analytics-sparkline bottom"><?php echo $data['lastmonth']['new_visits_sparkline']; ?></td>
	</tr>

	<tr>
		<td colspan="2" class="analytics-inset-container border-right-solid">
		
			<table class="analytics-inset" cellspacing="0">
				<tr>
					<th><?php echo $LANG->line('top_content'); ?></th>
					<th class="analytics-count-type"><?php echo $LANG->line('pageviews'); ?></th>
				</tr>
				<?php foreach($data['lastmonth']['content'] as $result): ?>
				<tr>
					<td class="analytics-top-content-row"><div><?php echo $result['title']; ?></div></td>
					<td class="analytics-count"><?php echo number_format($result['count']); ?></td>
				</tr>
				<?php endforeach; ?>
				<tr>
					<td class="analytics-report-link" colspan="2"><a href="https://www.google.com/analytics/reporting/content?id=<?php echo $ga_profile_id; ?>" target="_blank"><?php echo $LANG->line('more'); ?></a></td>
				</tr>
			</table>
			
		</td>
		<td colspan="2" class="analytics-inset-container">
			
			<table class="analytics-inset analytics-top-referrers" cellspacing="0">
				<tr>
					<th><?php echo $LANG->line('top_referrers'); ?></th>
					<th class="analytics-count-type"><?php echo $LANG->line('visits'); ?></th>			
				</tr>
			<?php foreach($data['lastmonth']['referrers'] as $result): ?>
				<tr>
					<td class="analytics-top-referrer-row"><?php echo $result['title']; ?></td>
					<td class="analytics-count"><?php echo $result['count']; ?></td>
				</tr>
			<?php endforeach; ?>
				<tr>
					<td class="analytics-report-link" colspan="2"><a href="https://www.google.com/analytics/reporting/sources?id=<?php echo $ga_profile_id; ?>" target="_blank"><?php echo $LANG->line('more'); ?></a></td>
				</tr>
			</table>	
				
		</td>
	</tr>
	
	<tr>
		<td colspan="3" class="analytics-footer"><?php echo $LANG->line('viewing_profile'); ?> <?php echo $data['profile']['id']; ?> (<?php echo $data['profile']['title']; ?>)</td>
		<td class="analytics-footer defaultRightBold"><a href="https://www.google.com/analytics/reporting/?id=<?php echo $ga_profile_id; ?>" target="_blank"><?php echo $LANG->line('full_report'); ?></a></td>
	</tr>
</table>