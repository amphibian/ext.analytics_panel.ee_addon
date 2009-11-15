<table class="analytics-panel" cellspacing="0">

	<tr>
		<th colspan="4"><?php echo $LANG->line('today'); ?></th>
	</tr>
	<tr>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo number_format($today->getVisits()) ?></span> <?php echo $LANG->line('visits'); ?></td>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo number_format($today->getPageviews()) ?></span> <?php echo $LANG->line('pageviews'); ?></td>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php $this->analytics_avg_pages($today->getPageviews(), $today->getVisits()) ?></span> <?php echo $LANG->line('pages_per_visit'); ?></td>
		<td class="analytics-stat-col end"><span class="analytics-stat"><?php $this->analytics_avg_visit($today->getTimeOnSite(), $today->getVisits()) ?></span> <?php echo $LANG->line('avg_visit'); ?></td>
	</tr>

	<tr>
		<th colspan="4"><?php echo $LANG->line('yesterday'); ?></th>
	</tr>
	<tr>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo number_format($yesterday->getVisits()) ?></span> <?php echo $LANG->line('visits'); ?></td>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php echo number_format($yesterday->getPageviews()) ?></span> <?php echo $LANG->line('pageviews'); ?></td>
		<td class="analytics-stat-col"><span class="analytics-stat"><?php $this->analytics_avg_pages($yesterday->getPageviews(), $yesterday->getVisits()) ?></span> <?php echo $LANG->line('pages_per_visit'); ?></td>		
		<td class="analytics-stat-col end"><span class="analytics-stat"><?php $this->analytics_avg_visit($yesterday->getTimeOnSite(), $yesterday->getVisits()) ?></span> <?php echo $LANG->line('avg_visit'); ?></td>
	</tr>				

	<tr>
		<th colspan="4"><?php echo date('F jS Y', strtotime('31 days ago')).' &ndash; '.date('F jS Y', strtotime('yesterday')); ?></th>
	</tr>
	<tr>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php echo number_format($lastmonth->getVisits()) ?></span> <?php echo $LANG->line('visits'); ?></td>
		<td class="analytics-sparkline border-right-dotted"><?php $this->analytics_sparkline($lastmonth->getResults(), 'visits'); ?></td>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php echo round( ($lastmonth->getBounces() / $lastmonth->getEntrances()) * 100, 2 ).'%'; ?></span> <?php echo $LANG->line('bounce_rate'); ?></td>
		<td class="analytics-sparkline"><?php $this->analytics_sparkline($lastmonth->getResults(), 'bouncerate'); ?></td>
	</tr>
	<tr>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php echo number_format($lastmonth->getPageviews()) ?></span> <?php echo $LANG->line('pageviews'); ?></td>
		<td class="analytics-sparkline border-right-dotted"><?php $this->analytics_sparkline($lastmonth->getResults(), 'pageviews'); ?></td>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php $this->analytics_avg_visit($lastmonth->getTimeOnSite(), $lastmonth->getVisits()) ?></span> <?php echo $LANG->line('avg_visit'); ?></td>
		<td class="analytics-sparkline"><?php $this->analytics_sparkline($lastmonth->getResults(), 'time'); ?></td>
	</tr>
	<tr>
		<td class="analytics-stat-row bottom"><span class="analytics-stat"><?php $this->analytics_avg_pages($lastmonth->getPageviews(), $lastmonth->getVisits()) ?></span> <?php echo $LANG->line('pages_per_visit'); ?></td>
		<td class="analytics-sparkline bottom border-right-dotted"><?php $this->analytics_sparkline($lastmonth->getResults(), 'avgpages'); ?></td>
		<td class="analytics-stat-row bottom"><span class="analytics-stat"><?php echo round( ($lastmonth->getNewVisits() / $lastmonth->getVisits()) * 100, 2).'%'; ?></span> <?php echo $LANG->line('new_visits'); ?></td>
		<td class="analytics-sparkline bottom"><?php $this->analytics_sparkline($lastmonth->getResults(), 'newvisits'); ?></td>
	</tr>

	<tr>
		<td colspan="2" class="analytics-inset-container border-right-solid">
		
			<table class="analytics-inset" cellspacing="0">
				<tr>
					<th><?php echo $LANG->line('top_content'); ?></th>
					<th class="analytics-count-type"><?php echo $LANG->line('pageviews'); ?></th>
				</tr>
				<?php foreach($topcontent->getResults() as $result): ?>
				<tr>
					<td class="analytics-top-content-row"><a target="_blank" href="http://<?php echo $result->getHostname() . $result->getPagePath();?>"><?php echo $result->getPagePath(); ?></a></td>
					<td class="analytics-count"><?php echo number_format($result->getPageviews()) ?></td>
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
			<?php foreach($referrers->getResults() as $result): ?>
				<tr>
					<td class="analytics-top-referrer-row">
						<?php if($result->getMedium() == 'referral') : ?>
						<a target="_blank" href="http://<?php echo $result->getSource() . $result->getReferralPath();?>"><?php echo $result->getSource(); ?></a>
						<?php else : ?><?php echo $result->getSource(); ?><?php endif; ?>
					</td>
					<td class="analytics-count"><?php echo number_format($result->getVisits()) ?></td>
				</tr>
			<?php endforeach; ?>
				<tr>
					<td class="analytics-report-link" colspan="2"><a href="https://www.google.com/analytics/reporting/sources?id=<?php echo $ga_profile_id; ?>" target="_blank"><?php echo $LANG->line('more'); ?></a></td>
				</tr>
			</table>	
				
		</td>
	</tr>
	
	<tr>
		<td colspan="3" class="analytics-footer"><?php echo $LANG->line('viewing_profile'); ?> <?php echo $profile['id']; ?> (<?php echo $profile['title']; ?>)</td>
		<td class="analytics-footer defaultRightBold"><a href="https://www.google.com/analytics/reporting/?id=<?php echo $ga_profile_id; ?>" target="_blank"><?php echo $LANG->line('full_report'); ?></a></td>
	</tr>
</table>