// Function to update the chart.
function updateChart() {
	jQuery.post(
		ajaxurl,
		{
			action: "tptn_chart_data",
			security: tptn_chart_data.security,
			from_date: jQuery("#datepicker-from").val(),
			to_date: jQuery("#datepicker-to").val(),
		},
		function (data) {
			var date = [];
			var visits = [];

			for (var i in data) {
				date.push(data[i].date);
				visits.push(data[i].visits);
			}

			// Determine chart type based on number of datapoints
			var chartType = "bar";
			var chartFill = false;
			var backgroundColor = "#70c4e1";
			var pointBackgroundColor = "#70c4e1";
			var borderWidth = 0;
			var pointBorderColor = "#70c4e1";
			var pointRadius = 0;
			var showDataLabels = true;

			// If we have more than 50 datapoints, switch to area chart for better visualization
			if (date.length > 100) {
				chartType = "line";
				chartFill = true;
				backgroundColor = "rgba(112, 196, 225, 0.8)"; // Transparent blue
				pointBackgroundColor = "#70c4e1"; // Solid blue dots
				borderWidth = 2;
				pointBorderColor = "#ffffff"; // White border around dots
				pointRadius = 3;
				showDataLabels = false; // Hide data labels for area chart to avoid clutter
			}

			// Update chart configuration if type needs to change
			if (window.top10chart.config.type !== chartType) {
				window.top10chart.config.type = chartType;
				window.top10chart.data.datasets.forEach((dataset) => {
					dataset.fill = chartFill;
					dataset.backgroundColor = backgroundColor;
					dataset.pointBackgroundColor = pointBackgroundColor;
					dataset.borderWidth = borderWidth;
					dataset.pointBorderColor = pointBorderColor;
					dataset.pointRadius = pointRadius;
				});

				// Update data labels plugin display setting
				if (window.top10chart.options && window.top10chart.options.plugins && window.top10chart.options.plugins.datalabels) {
					window.top10chart.options.plugins.datalabels.display = showDataLabels;
				}
			}

			window.top10chart.data.labels = date;
			window.top10chart.data.datasets.forEach((dataset) => {
				dataset.data = visits;
			});
			window.top10chart.update();
		},
		"json"
	);
}

jQuery(document).ready(function ($) {
	$.ajax({
		type: "POST",
		dataType: "json",
		url: ajaxurl,
		data: {
			action: "tptn_chart_data",
			security: tptn_chart_data.security,
			from_date: $("#datepicker-from").val(),
			to_date: $("#datepicker-to").val(),
		},
		success: function (data) {
			var date = [];
			var visits = [];

			for (var i in data) {
				date.push(data[i].date);
				visits.push(data[i].visits);
			}

			// Determine chart type based on number of datapoints
			var chartType = "bar";
			var chartFill = false;
			var backgroundColor = "#70c4e1";
			var pointBackgroundColor = "#70c4e1";
			var borderWidth = 0;
			var pointBorderColor = "#70c4e1";
			var pointRadius = 0;
			var showDataLabels = true;

			// If we have more than 50 datapoints, switch to area chart for better visualization
			if (date.length > 50) {
				chartType = "line";
				chartFill = true;
				backgroundColor = "rgba(112, 196, 225, 0.2)"; // Transparent blue
				pointBackgroundColor = "#70c4e1"; // Solid blue dots
				borderWidth = 2;
				pointBorderColor = "#ffffff"; // White border around dots
				pointRadius = 3;
				showDataLabels = false; // Hide data labels for area chart to avoid clutter
			}

			var ctx = $("#visits");
			var config = {
				type: chartType,
				data: {
					labels: date,
					datasets: [
						{
							label: tptn_chart_data.datasetlabel,
							backgroundColor: backgroundColor,
							borderColor: "#70c4e1",
							hoverBackgroundColor: "#ffbf00",
							hoverBorderColor: "#ffbf00",
							data: visits,
							fill: chartFill,
							pointBackgroundColor: pointBackgroundColor,
							pointBorderColor: pointBorderColor,
							pointRadius: pointRadius,
							borderWidth: borderWidth,
						},
					],
				},
				plugins: [ChartDataLabels],
				options: {
					plugins: {
						title: {
							text: tptn_chart_data.charttitle,
							display: true,
						},
						legend: {
							display: false,
							position: "bottom",
						},
						datalabels: {
							color: "#000000",
							anchor: "end",
							align: "top",
							display: showDataLabels,
						},
					},
					scales: {
						x: {
							type: "time",
							time: {
								tooltipFormat: "DD",
								unit: "day",
								displayFormats: {
									day: "DD",
								},
							},
							title: {
								display: false,
								labelString: "Date",
							},
						},
						y: {
							grace: "5%",
							suggestedMin: 0,
							display: true,
							title: {
								display: false,
								text: tptn_chart_data.datasetlabel,
								color: "#000",
								padding: { top: 30, left: 0, right: 0, bottom: 0 },
							},
						},
					},
				},
			};

			window.top10chart = new Chart(ctx, config);
		},
		error: function (data) {
			console.log(data);
		},
	});
});
