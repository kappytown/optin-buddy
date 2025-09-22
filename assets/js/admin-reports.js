jQuery(document).ready(function ($) {
	// Prevents multiple submissions
	let _processing = false;

	// Holds the datasets, labels, and title of the current chart
	// _chart, _chartRef variables are used so we can update the chart type (line, bar) without having to re-query the data
	const _chart = {};

	// Holds a reference to the chartJS chart
	let _chartRef = null;

	/**
	 * Click handler for Navigation 
	 * 
	 * Toggles the visibility of the selected tabs
	 */
	$(document).on('click', '.navigation li', function (e) {
		const $this = $(this);
		const tab = $this.data('section');
		$this.parent().find('.selected').removeClass('selected');
		$this.addClass('selected');

		$('.tab').addClass('hidden');
		$(`.${tab}`).removeClass('hidden');
	});

	/**
	 * Change handler for report selector
	 * 
	 * This will hide/show the chart type buttons
	 */
	$(document).on('change', 'select.select-report', function (e) {
		e.preventDefault();

		const $this = $(this);
		const val = $this.val();
		const $chartType = $('.chart-type');
		$chartType.addClass('hidden');

		if (val.includes('-top-5-') || val.includes('-daily-form-submissions')) {
			$chartType.removeClass('hidden');
		}
	});

	/**
	 * Click handler for run report button
	 */
	$(document).on('click', 'button.run', function (e) {
		e.preventDefault();

		const report = $('select.select-report').val();
		const start_date = $('input[name="start_date"]').val();
		const end_date = $('input[name="end_date"]').val();

		switch (report) {
			case 'report-submissions':
				render_report_submissions(start_date, end_date, 1);
				break;
			case 'report-all-forms':
				render_all_forms(start_date, end_date, 1);
				break;
			case 'report-top-5-forms':
				render_top_5_forms(start_date, end_date);
				break;
			case 'report-top-5-pages':
				render_top_5_pages(start_date, end_date);
				break;
			case 'report-daily-form-submissions':
				render_daily_form_submissions(end_date);
				break;
			case 'report-succeeded-emails':
				render_all_emails(start_date, end_date, 'succeeded', 1);
				break;
			case 'report-failed-emails':
				render_all_emails(start_date, end_date, 'failed', 1);
				break;
			case 'report-all-emails':
				render_all_emails(start_date, end_date, 'all', 1);
				break;
		}
	});

	/**
	 * 
	 * @param {string} start_date 
	 * @param {string} end_date 
	 */
	const render_report_submissions = (start_date, end_date, page) => {
		if (_processing) return;
		_processing = true;

		//const start_date_pretty = opt_bud.utils.pretty_date(start_date);
		//const end_date_pretty = opt_bud.utils.pretty_date(end_date);

		// Empty the results
		const $reportDetails = $('.report-details');
		const $title = $('.title', $reportDetails).html('All form optins');
		const $results = $('.results', $reportDetails).empty();
		$results.append(`
				<table>
					<tr>
						<th>Title</th>
						<th>Optins</th>
						<th>Last Submitted</th>
					</tr>
				</table>`);

		const $table = $('table', $results);

		const onCompleteHandler = (error, response) => {
			_processing = false;
			hasResults = false;

			if (error) {
				opt_bud.utils.showAdminNotice(error, 'error');
			}

			// Loop over the results and add them to the table
			if (response.results) {
				const results = response.results;

				for (let key in results) {
					hasResults = true;
					const result = results[key];
					$table.append(`
					<tr>
						<td>${result.header}</td>
						<td>${result.num}<br /><a href="#" class="list-submissions" data-form_id="${result.form_id}">View Optins</a></td>
						<td>${result.time}</td>
					</tr>`);
				};

				// Handle pagination
				opt_bud.utils.paginate(response.pagination, $results, (selected_page) => {
					if (page == selected_page) return false;

					render_report_submissions(start_date, end_date, filter, selected_page);
				});
			}

			if (!hasResults) {
				$table.append('<tr><td colspan="3">No results found</td></tr>');
			}
		}

		const params = {
			'action': 'handle_reports_submissions',
			'nonce': AdminReportsAjax.nonce,
			'start_date': start_date,
			'end_date': end_date
		}
		opt_bud.utils.ajax(AdminReportsAjax.ajax_url, 'POST', params, onCompleteHandler);
	}

	/**
	 * 
	 * @param {string} start_date 
	 * @param {string} end_date 
	 */
	const render_all_forms = (start_date, end_date, page) => {
		if (_processing) return;
		_processing = true;

		// Empty the results
		const $reportDetails = $('.report-details');
		const $title = $('.title', $reportDetails).html('All Forms');
		const $results = $('.results', $reportDetails).empty();
		$results.append(`
				<table>
					<tr>
						<th>Title:</th>
						<th>Type:</th>
						<th>Template:</th>
						<th>Total Optins:</th>
					</tr>
				</table>`);

		const $table = $('table', $results);

		const onCompleteHandler = (error, response) => {
			_processing = false;
			hasResults = false;

			if (error) {
				opt_bud.utils.showAdminNotice(error, 'error');
			}

			// Loop over the results and add them to the table
			if (response.results) {
				const results = response.results;

				for (let key in results) {
					hasResults = true;
					const result = results[key];
					$table.append(`
					<tr>
						<td>${result.header}${result.deactivate == 1 ? '<span class="deactivated">deactivated</span>' : ''}</td>
						<td>${result.type_name}</td>
						<td>${result.template_name}</td>
						<td>${result.count}</td>
					</tr>`);
				};

				// Handle pagination
				opt_bud.utils.paginate(response.pagination, $results, (selected_page) => {
					if (page == selected_page) return false;

					render_all_forms(start_date, end_date, selected_page);
				});
			}

			if (!hasResults) {
				$table.append('<tr><td colspan="4">No results found</td></tr>');
			}
		}

		const params = {
			'action': 'handle_reports_list_forms',
			'nonce': AdminReportsAjax.nonce,
			'start_date': start_date,
			'end_date': end_date
		}
		opt_bud.utils.ajax(AdminReportsAjax.ajax_url, 'POST', params, onCompleteHandler);
	}

	/**
	 * 
	 * @param {string} start_date 
	 * @param {string} end_date 
	 */
	const render_top_5_forms = (start_date, end_date) => {
		if (_processing) return;
		_processing = true;

		// Empty the results
		const $reportDetails = $('.report-details');
		const $title = $('.title', $reportDetails).html('Top 5 Forms');
		const $results = $('.results', $reportDetails).empty();
		$results.append(`
		<div class="chart-wrapper" style="max-width: 800px; height: auto; margin: 20px auto; min-height: 250px;">
			<canvas id="chart"></canvas>
		</div>`);

		const $chartWrapper = $('.chart-wrapper', $results);

		const onCompleteHandler = (error, response) => {
			_processing = false;

			if (response && response.results && response.results.length) {
				const data = response.results;
				_chart.datasets = [
					{
						label: '',
						backgroundColor: "rgba(34,113,177,.1)",
						borderColor: "rgba(34,113,177,1)",
						pointBorderColor: "rgba(34,113,177,1)",
						pointBackgroundColor: "rgba(34,113,177,1)",
						borderWidth: 5,
						pointRadius: 5,
						pointHoverRadius: 5,
						fill: true,
						tension: .3,
						data: data.map(row => row.count)
					}
				];
				_chart.labels = data.map(row => opt_bud.utils.truncate(row.title, 20));
				// _chart.title = 'Top 5 Forms';

				if (_chartRef) _chartRef.destroy();
				_chartRef = create_chart('chart', _chart, $('select.select-chart-type').val());
			}
		}

		const params = {
			'action': 'handle_reports_top_5_forms',
			'nonce': AdminReportsAjax.nonce,
			'start_date': start_date,
			'end_date': end_date
		}
		opt_bud.utils.ajax(AdminReportsAjax.ajax_url, 'POST', params, onCompleteHandler);
	}

	/**
	 * 
	 * @param {string} start_date 
	 * @param {string} end_date 
	 */
	const render_top_5_pages = (start_date, end_date) => {
		if (_processing) return;
		_processing = true;

		// Empty the results
		const $reportDetails = $('.report-details');
		const $title = $('.title', $reportDetails).html('Top 5 Pages');
		const $results = $('.results', $reportDetails).empty();
		$results.append(`
			<div class="chart-wrapper" style="max-width: 800px; height: auto; margin: 20px auto; min-height: 250px;">
				<canvas id="chart"></canvas>
			</div>`);

		const $chartWrapper = $('.chart-wrapper', $results);

		const onCompleteHandler = (error, response) => {
			_processing = false;

			if (response && response.results && response.results.length) {
				const data = response.results;
				_chart.datasets = [
					{
						label: '',
						backgroundColor: "rgba(34,113,177,.1)",
						borderColor: "rgba(34,113,177,1)",
						pointBorderColor: "rgba(34,113,177,1)",
						pointBackgroundColor: "rgba(34,113,177,1)",
						borderWidth: 5,
						pointRadius: 5,
						pointHoverRadius: 5,
						fill: true,
						tension: .3,
						data: data.map(row => row.count)
					}
				];
				_chart.labels = data.map(row => opt_bud.utils.truncate(row.title, 20));
				// _chart.title = 'Top 5 Pages';

				if (_chartRef) _chartRef.destroy();
				_chartRef = create_chart('chart', _chart, $('select.select-chart-type').val());
			}
		}

		const params = {
			'action': 'handle_reports_top_5_pages',
			'nonce': AdminReportsAjax.nonce,
			'start_date': start_date,
			'end_date': end_date
		}
		opt_bud.utils.ajax(AdminReportsAjax.ajax_url, 'POST', params, onCompleteHandler);
	}

	/**
	 * Creates a new chart in the specified element and returns the instance
	 * 
	 * @param {string} id 
	 * @param {JSON} data 
	 * @param {string} type 
	 * @returns 
	 */
	const create_chart = (id, data, type) => {
		return new Chart(
			document.getElementById(id),
			{
				type: type,
				data: {
					labels: data.labels,
					datasets: data.datasets
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						title: {
							display: data.title !== '',
							text: data.title
						},
						legend: {
							display: false
						},
						tooltip: {
							callbacks: {
								label: function (context) {
									let label = '';
									if (context.parsed.y !== null) {
										label += context.parsed.y + ' optins';
									}
									return label;
								}
							}
						}
					},
					scales: {
						x: {
							grid: {
								display: true,
								drawBorder: false,
								drawOnChartArea: true,
								drawTicks: true
							}
						},
						y: {
							grid: {
								display: true,
								drawBorder: false,
								drawOnChartArea: true,
								drawTicks: true
							}
						}
					},
					chartArea: {
						backgroundColors: ["rgba(255, 255, 255, 1)", "rgba(255, 255, 255, 1)"]
					}
				},
				plugins: [{}]
			}
		);
	}

	/**
	 * 
	 * @param {string} start_date 
	 * @param {string} end_date 
	 * @param {string} filter 
	 * @param {int} page 
	 * @returns 
	 */
	const render_all_emails = (start_date, end_date, filter, page) => {
		if (_processing) return;
		_processing = true;

		// Empty the results
		const $reportDetails = $('.report-details');
		const $title = $('.title', $reportDetails).html('All Emails');
		const $results = $('.results', $reportDetails).empty();
		$results.append(`
				<table>
					<tr>
						<th>Email:</th>
						<th>Provider:</th>
						<th>Sent:</th>
						<th>Date:</th>
					</tr>
				</table>`);

		const $table = $('table', $results);

		const onCompleteHandler = (error, response) => {
			_processing = false;
			hasResults = false;

			if (error) {
				opt_bud.utils.showAdminNotice(error, 'error');
			}

			// Loop over the results and add them to the table
			if (response.results) {
				const results = response.results;
				const pagination = response.pagination;

				for (let key in results) {
					hasResults = true;
					const result = results[key];
					$table.append(`
					<tr>
						<td>${result.email}</td>
						<td>${result.meta.provider}</td>
						<td>${result.failed == 1 ? '<span class="icon outlined close">close</span>' : '<span class="icon outlined check">check</span>'}</td>
						<td>${result.date}</td>
					</tr>`);
				};

				// Handle pagination
				opt_bud.utils.paginate(pagination, $results, (selected_page) => {
					if (page == selected_page) return false;

					render_all_emails(start_date, end_date, filter, selected_page);
				});

				// Show Export Results button
				if (pagination && pagination.total_items > 0) {
					$results.append('<p><button class="button export">Export Results</button></p>');
					// Export Results click handler
					$('.export').click(function (e) {
						e.preventDefault();
						if (_processing) return;
						_processing = true;

						const onCompleteHandler = (error, response) => {
							_processing = false;

							if (error) {
								opt_bud.utils.showAdminNotice(error, 'error');
							}

							if (response.file_url) {
								window.location = response.file_url;
							} else {
								opt_bud.utils.showAdminNotice('error', 'Unable to export at this time, please try again later.');
							}
						}

						const params = {
							'action': 'handle_reports_list_emails',
							'nonce': AdminReportsAjax.nonce,
							'start_date': start_date,
							'end_date': end_date,
							'filter': filter,
							'limit': 25000,
							'page': 1,
							'format': 'csv'
						}

						// Before exporting list, ask the user which rows they want to export
						// Generate the list of rows to export starting at rows 1-25000
						$select = $('<select name="select-export"><option value="" selected>Select the Rows to Export</option></select>');
						const rows = Math.ceil(pagination.total_items / 25000);
						for (let i = 0; i < rows; i++) {
							const startRow = (i * 25000) + 1;
							let endRow = (i + 1) * 25000;
							if (endRow > pagination.total_items) {
								endRow = pagination.total_items;
							}
							$select.append(`<option value="${i + 1}">${startRow} - ${endRow}</option>`);
						}
						// Show confirmation dialog
						const id = 'export_report';
						opt_bud.modal.show(id, { class: 'info', title: 'Export Results', body: 'You are about to export the results of this report. <p>Please select the rows you wish to export from the list below.</p><p>' + $select[0].outerHTML + '</p>', closeButton: true, actionButtonText: 'Export' });
						$('#' + id + ' .modal-footer .button-primary').click(function (e) {
							const $export = $('select[name="select-export"]');
							if (isNaN(parseInt($export.val()))) {
								alert('Please select the rows you with to export.');
								$export.focus();
							} else {
								opt_bud.modal.hide(id);
								_processing = false;
								params.page = $export.val();
								opt_bud.utils.ajax(AdminReportsAjax.ajax_url, 'POST', params, onCompleteHandler);
							}
						});
						// Cancel handler
						$('#' + id + ' .modal-footer .btn-close, .modal-header button.close').click(function (e) {
							opt_bud.modal.hide(id);
							_processing = false;
						});
					});
				}
			}

			if (!hasResults) {
				$table.append('<tr><td colspan="3">No results found</td></tr>');
			}
		}

		const params = {
			'action': 'handle_reports_list_emails',
			'nonce': AdminReportsAjax.nonce,
			'start_date': start_date,
			'end_date': end_date,
			'filter': filter,
			'page': page
		}
		opt_bud.utils.ajax(AdminReportsAjax.ajax_url, 'POST', params, onCompleteHandler);
	}

	/**
	 * Click handler for view submissions link
	 */
	$(document).on('click', '.list-submissions', function (e) {
		e.preventDefault();

		if (_processing) return;
		_processing = true;

		const onCompleteHandler = (error, response) => {
			_processing = false;
			hasResults = false;

			if (error) {
				opt_bud.utils.showAdminNotice(error, 'error');
			}

			if (response.results) {
				let body = '<section class="obp-admin" style="font-size:12px;"><table><tr><th>Title</th><th>Type</th><th>Location</th><th>Date</th></tr>';
				for (let key in response.results) {
					const result = response.results[key];
					const location = result.meta.location.join(' - ');
					body += `<tr><td>${result.post_title}</td><td>${result.post_type}</td><td>${location}</td><td>${result.time}</td></tr>`;
				}
				body += '</table></section>';
				opt_bud.modal.show('my_id', { title: 'Optins', closeButton: true, body: body });
			}

			if (!hasResults) {
				// do something as this should always return a value
			}
		}

		const params = {
			'action': 'handle_reports_list_submissions',
			'nonce': AdminReportsAjax.nonce,
			'form_id': $(this).data('form_id'),
			'start_date': $('input[name="start_date"]').val(),
			'end_date': $('input[name="end_date"]').val()
		}
		opt_bud.utils.ajax(AdminReportsAjax.ajax_url, 'POST', params, onCompleteHandler);
	});

	/**
	 * This will initialize the dashboard view.
	 * Currently we are ONLY displaying the current days submission cound via admin notice view
	 * Stats: # optins today, *top converting form, *top page with form conversions, 
	 */
	const init_dashboard = () => {
		// Get todays submission count
		/*const date = $('input[name="end_date"]').val();
		let params = {
			'action': 'handle_reports_list_emails',
			'nonce': AdminReportsAjax.nonce,
			'start_date': date,
			'end_date': date,
			'filter': 'all',
			'page': 1
		}
		opt_bud.utils.ajax(AdminReportsAjax.ajax_url, 'POST', params, (error, response) => {
			if (error) {
				console.log(error);
				return;
			}
	
			if (response.pagination && response.pagination.total_items > 0) {
				opt_bud.utils.showAdminNotice(`<strong>${response.pagination.total_items}</strong> forms were submitted today.`, 'info', 0);
			}
		});*/

		const createChart = (data, $context, type, id, title) => {
			let chart = {};
			$context.append(`
				<div class="chart-wrapper">
					<canvas id="${id}"></canvas>
				</div>
			`);

			chart.datasets = [
				{
					label: '',
					backgroundColor: "rgba(34,113,177,.1)",
					borderColor: "rgba(34,113,177,1)",
					pointBorderColor: "rgba(34,113,177,1)",
					pointBackgroundColor: "rgba(34,113,177,1)",
					borderWidth: 3,
					pointRadius: 2,
					pointHoverRadius: 3,
					fill: true,
					tension: .3,
					data: data.map(row => row.count)
				}
			];
			chart.labels = data.map(row => row.date);
			chart.title = '';

			create_chart(id, chart, type);
		}

		opt_bud.utils.loader.show_in($('.daily, .monthly, .yearly, .monthly-chart, .daily-chart, .list'));

		const onCompleteHandlerCount = (error, response) => {
			if (response.results && response.results.length) {
				const result = response.results[0];
				$('.daily').empty().html(`
					<h5 class="title">${result.day} Optins</h5>
					<div class="value">${(Number(result.daily) + .00).toLocaleString()}</div>
				`);
				$('.monthly').empty().html(`
					<h5 class="title">${result.month} Optins</h5>
					<div class="value">${(Number(result.monthly) + .00).toLocaleString()}</div>
				`);
				$('.yearly').empty().html(`
					<h5 class="title">${result.year} Optins</h5>
					<div class="value">${(Number(result.yearly) + .00).toLocaleString()}</div>
				`);
			}

			opt_bud.utils.loader.remove_from($('.daily, .monthly, .yearly'));
		}

		const onCompleteHandlerMonthly = (error, response) => {
			const $context = $('.chart.monthly-chart').empty();
			$context.append('<h5>Monthly Optins</h5>');
			if (response && response.results && response.results.length) {
				const data = response.results.reverse();
				createChart(data, $context, 'bar', 'chart-monthly');
			} else {
				$context.append('<p>This will contain a bar chart of the total optins per month for the past year.</p>');
			}
			opt_bud.utils.loader.remove_from($('.monthly-chart'));
		}

		const onCompleteHandlerDaily = (error, response) => {
			const $context = $('.chart.daily-chart').empty();
			$context.append('<h5>Daily Optins</h5>');
			if (response && response.results && response.results.length) {
				const data = response.results;
				createChart(data, $context, 'line', 'chart-daily');
			} else {
				$context.append('<p>This will contain a line chart of the total optins per day for the past month.</p>');
			}
			opt_bud.utils.loader.remove_from($('.daily-chart'));
		}

		const onCompleteHandlerForms = (error, response) => {
			const $context = $('.box.list').empty();
			$context.append('<h5>Top 5 Forms:</h5>');
			if (response && response.results && response.results.length) {
				const data = response.results;

				$context.append(`
					<table>
						<tr>
							<th>Title:</th>
							<th>Optins:</th>
						</tr>
					</table>
				`);

				const $table = $('table', $context);

				for (let key in data) {
					hasResults = true;
					const result = data[key];
					$table.append(`
						<tr>
							<td>${result.title}</td>
							<td>${result.count}</td>
						</tr>
					`);
				};
			} else {
				$context.append('<p>This will contain a list of your top 5 forms users subscribed to.</p>');
			}
			opt_bud.utils.loader.remove_from($('.list'));
		}

		params = {
			'action': 'handle_dashboard_count',
			'nonce': AdminReportsAjax.nonce
		}

		opt_bud.utils.ajax(AdminReportsAjax.ajax_url, 'POST', params, onCompleteHandlerCount);
		params.action = 'handle_dashboard_chart_monthly';
		opt_bud.utils.ajax(AdminReportsAjax.ajax_url, 'POST', params, onCompleteHandlerMonthly);
		params.action = 'handle_dashboard_chart_daily';
		opt_bud.utils.ajax(AdminReportsAjax.ajax_url, 'POST', params, onCompleteHandlerDaily);
		params.action = 'handle_dashboard_forms';
		opt_bud.utils.ajax(AdminReportsAjax.ajax_url, 'POST', params, onCompleteHandlerForms);
	}

	/**
	 * Initializer
	 */
	const init = () => {
		// set date fields
		try {
			const date = new Date();

			// End Date (current date)
			let day = ('0' + date.getDate()).slice(-2);
			let month = ('0' + (date.getMonth() + 1)).slice(-2);
			let year = date.getFullYear();
			$('input[name="end_date"]').val(year + '-' + month + '-' + day);

			// Start date (current date - 1 month)
			date.setMonth(date.getMonth() - 1);
			day = ('0' + date.getDate()).slice(-2);
			month = ('0' + (date.getMonth() + 1)).slice(-2);
			year = date.getFullYear();
			$('input[name="start_date"]').val(year + '-' + month + '-' + day);

		} catch (e) {
			console.log(e);
		}

		// Initialize the dashboard
		init_dashboard();

		// Request new nonce every hour and on tab change to ensure the form submits
		opt_bud.utils.setup_nonce_renewal('handle_reports_new_nonce', AdminReportsAjax.ajax_url, nonce => {
			if (nonce) {
				AdminReportsAjax.nonce = nonce;
			}
		});
	}

	init();
});