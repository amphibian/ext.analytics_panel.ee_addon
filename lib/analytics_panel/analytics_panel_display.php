<table class="analytics-panel" cellspacing="0">

	<tr>
		<th colspan="4"><?php echo $LANG->line('today'); ?><?php if(isset($hourly_cache)) echo(" <!-- Cached at ".date('g:ia', $today['cache_time']).". Will refresh at ".date('g:ia', strtotime('+60 minutes', $today['cache_time'])).". -->"); ?></th>
	</tr>
	<tr>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo $today['visits']; ?></span> <?php echo $LANG->line('visits'); ?></td>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo $today['pageviews'] ?></span> <?php echo $LANG->line('pageviews'); ?></td>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo $today['pages_per_visit']; ?></span> <?php echo $LANG->line('pages_per_visit'); ?></td>		
		<td class="analytics-stat-col end"><span class="analytics-stat"><?php echo $today['avg_visit']; ?></span> <?php echo $LANG->line('avg_visit'); ?></td>
	</tr>

	<tr>
		<th colspan="4"><?php echo $LANG->line('yesterday'); ?><?php if(isset($daily_cache)) echo(" <!-- Cached on ".$daily['cache_date'].". -->"); ?></th>
	</tr>
	<tr>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo $daily['yesterday']['visits']; ?></span> <?php echo $LANG->line('visits'); ?></td>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo $daily['yesterday']['pageviews'] ?></span> <?php echo $LANG->line('pageviews'); ?></td>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo $daily['yesterday']['pages_per_visit']; ?></span> <?php echo $LANG->line('pages_per_visit'); ?></td>		
		<td class="analytics-stat-col end"><span class="analytics-stat"><?php echo $daily['yesterday']['avg_visit']; ?></span> <?php echo $LANG->line('avg_visit'); ?></td>
	</tr>				

	<tr>
		<th colspan="4"><?php echo $daily['lastmonth']['date_span']; ?><?php if(isset($daily_cache)) echo(" <!-- Cached on ".$daily['cache_date'].". -->"); ?></th>
	</tr>
	<tr>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php echo $daily['lastmonth']['visits']; ?></span> <?php echo $LANG->line('visits'); ?></td>
		<td class="analytics-sparkline border-right-dotted"><?php echo $daily['lastmonth']['visits_sparkline']; ?></td>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php echo $daily['lastmonth']['bounce_rate']; ?></span> <?php echo $LANG->line('bounce_rate'); ?></td>
		<td class="analytics-sparkline"><?php echo $daily['lastmonth']['bounce_rate_sparkline']; ?></td>
	</tr>
	<tr>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php echo $daily['lastmonth']['pageviews']; ?></span> <?php echo $LANG->line('pageviews'); ?></td>
		<td class="analytics-sparkline border-right-dotted"><?php echo $daily['lastmonth']['pageviews_sparkline']; ?></td>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php echo $daily['lastmonth']['avg_visit']; ?></span> <?php echo $LANG->line('avg_visit'); ?></td>
		<td class="analytics-sparkline"><?php echo $daily['lastmonth']['avg_visit_sparkline']; ?></td>
	</tr>
	<tr>
		<td class="analytics-stat-row bottom"><span class="analytics-stat"><?php echo $daily['lastmonth']['pages_per_visit']; ?></span> <?php echo $LANG->line('pages_per_visit'); ?></td>
		<td class="analytics-sparkline bottom border-right-dotted"><?php echo $daily['lastmonth']['pages_per_visit_sparkline']; ?></td>
		<td class="analytics-stat-row bottom"><span class="analytics-stat"><?php echo $daily['lastmonth']['new_visits']; ?></span> <?php echo $LANG->line('new_visits'); ?></td>
		<td class="analytics-sparkline bottom"><?php echo $daily['lastmonth']['new_visits_sparkline']; ?></td>
	</tr>

	<tr>
		<td colspan="2" class="analytics-inset-container border-right-solid">
		
			<table class="analytics-inset" cellspacing="0">
				<tr>
					<th><?php echo $LANG->line('top_content'); ?></th>
					<th class="analytics-count-type"><?php echo $LANG->line('pageviews'); ?></th>
				</tr>
				<?php foreach($daily['lastmonth']['content'] as $result): ?>
				<tr>
					<td class="analytics-top-content-row"><?php echo $result['title']; ?></td>
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
			<?php foreach($daily['lastmonth']['referrers'] as $result): ?>
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
		<td colspan="3" class="analytics-footer"><?php echo $LANG->line('viewing_profile'); ?> <?php echo $daily['profile']['id']; ?> (<?php echo $daily['profile']['title']; ?>)</td>
		<td class="analytics-footer defaultRightBold"><a href="https://www.google.com/analytics/reporting/?id=<?php echo $ga_profile_id; ?>" target="_blank"><?php echo $LANG->line('full_report'); ?></a></td>
	</tr>
</table>