<?php defined('ABSPATH') || exit; ?>

<section class="obp-admin reports wrap">
	<h1>Reports</h1>
	<p>View all capture emails, forms, and form optins by date range.</p>

	<div class="container">
		<!-- Navigation Tabs -->
		<div class="navigation">
			<ul class="tabs">
				<li class="tab selected" data-section="dashboard">Dashboard</li>
				<li class="tab" data-section="custom">Custom Reports</li>
			</ul>
		</div>

		<!-- Main Content -->
		<div class="main">
			<!-- Dashboard Tab Section -->
			<div class="tab dashboard">
				<div class="flexbox align-start">
					<div class="flexbox row justify-center">
						<div class="box daily"></div>
						<div class="box monthly"></div>
						<div class="box yearly"></div>
					</div>

					<div class="flexbox row justify-center">
						<div class="box chart monthly-chart"></div>
						<div class="box chart daily-chart"></div>
					</div>

					<div class="flexbox row justify-center">
						<div class="box list"></div>
					</div>
				</div>
			</div>

			<!-- Custom Report Tab Section -->
			<div class="tab custom hidden">
				<!-- Report Criteria -->
				<form class="form-reports">
					<div class="actions">
						<div class="field">
							<label>Start Date:</label><input type="date" name="start_date" min="" max="<?php echo date("Y-m-d"); ?>" style="min-width: 0 !important;" required="required" format="yyyy-mm-dd" pattern="\d{4}-\d{2}-\d{2}" />
						</div>
						<div class="field">
							<label>End Date:</label><input type="date" name="end_date" min="" max="<?php echo date("Y-m-d"); ?>" style="min-width: 0 !important;" required="required" format="yyyy-mm-dd" pattern="\d{4}-\d{2}-\d{2}" /">
						</div>
						<div class="field">
							<select class="select-report">
								<option value="">Select Report</option>
								<option value="report-submissions">View Optins</option>
								<option value="report-all-forms">View All Forms</option>
								<option value="report-top-5-forms">View Top 5 Forms</option>
								<option value="report-top-5-pages">View Top 5 Pages</option>
								<option value="report-succeeded-emails">View Succeeded Emails</option>
								<option value="report-failed-emails">View Failed Emails</option>
								<option value="report-all-emails">View All Emails</option>
							</select>
						</div>
						<div class="field chart-type hidden">
							<select class="select-chart-type">
								<option value="line" selected>Line Chart</option>
								<option value="bar">Bar Chart</option>
							</select>
						</div>
						<div class="field"><button type="submit" class="button button-primary run">Run Report</button></div>
					</div>
				</form>

				<!-- Report Results Section -->
				<div class="report-details" style="margin-top:20px;">
					<div class="title"></div>
					<div class="results"></div>
				</div>
			</div>
		</div>
	</div>
</section>