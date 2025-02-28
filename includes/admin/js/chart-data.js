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

			var ctx = $("#visits");
			var config = {
				type: "bar",
				data: {
					labels: date,
					datasets: [
						{
							label: tptn_chart_data.datasetlabel,
							backgroundColor: "#70c4e1",
							borderColor: "#70c4e1",
							hoverBackgroundColor: "#ffbf00",
							hoverBorderColor: "#ffbf00",
							data: visits,
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
