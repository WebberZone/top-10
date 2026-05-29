// Format large numbers as abbreviated strings (e.g. 15437 → "15.4K").
function tptnFormatNumber( value ) {
	if ( value >= 1000000 ) {
		return ( value / 1000000 ).toFixed( 1 ) + 'M';
	}
	if ( value >= 1000 ) {
		return ( value / 1000 ).toFixed( 1 ) + 'K';
	}
	return value.toLocaleString();
}

// Resolve chart type and display options based on data length.
function tptnChartTypeConfig( length ) {
	var config = {
		chartType: 'bar',
		chartFill: false,
		backgroundColor: '#70c4e1',
		pointBackgroundColor: '#70c4e1',
		borderWidth: 0,
		pointBorderColor: '#70c4e1',
		pointRadius: 0,
		showDataLabels: true,
	};

	if ( length > 50 ) {
		config.chartType = 'line';
		config.chartFill = true;
		config.backgroundColor = 'rgba(112, 196, 225, 0.2)';
		config.borderWidth = 2;
		config.pointBorderColor = '#ffffff';
		config.pointRadius = 3;
		config.showDataLabels = false;
	}

	return config;
}

// Function to update the chart.
function updateChart() {
	jQuery.post(
		ajaxurl,
		{
			action: 'tptn_chart_data',
			security: tptn_chart_data.security,
			from_date: jQuery( '#datepicker-from' ).val(),
			to_date: jQuery( '#datepicker-to' ).val(),
			network: tptn_chart_data.network,
		},
		function ( data ) {
			var date   = [];
			var visits = [];

			for ( var i in data ) {
				date.push( data[ i ].date );
				visits.push( data[ i ].visits );
			}

			var cfg = tptnChartTypeConfig( date.length );

			if ( window.top10chart.config.type !== cfg.chartType ) {
				window.top10chart.config.type = cfg.chartType;
				window.top10chart.data.datasets.forEach( function ( dataset ) {
					dataset.fill               = cfg.chartFill;
					dataset.backgroundColor    = cfg.backgroundColor;
					dataset.pointBackgroundColor = cfg.pointBackgroundColor;
					dataset.borderWidth        = cfg.borderWidth;
					dataset.pointBorderColor   = cfg.pointBorderColor;
					dataset.pointRadius        = cfg.pointRadius;
				} );

				if ( window.top10chart.options && window.top10chart.options.plugins && window.top10chart.options.plugins.datalabels ) {
					window.top10chart.options.plugins.datalabels.display = cfg.showDataLabels ? 'auto' : false;
				}
			}

			window.top10chart.data.labels = date;
			window.top10chart.data.datasets.forEach( function ( dataset ) {
				dataset.data = visits;
			} );
			window.top10chart.update();
		},
		'json'
	);
}

jQuery( document ).ready( function ( $ ) {
	$.ajax( {
		type: 'POST',
		dataType: 'json',
		url: ajaxurl,
		data: {
			action: 'tptn_chart_data',
			security: tptn_chart_data.security,
			from_date: $( '#datepicker-from' ).val(),
			to_date: $( '#datepicker-to' ).val(),
			network: tptn_chart_data.network,
		},
		success: function ( data ) {
			var date   = [];
			var visits = [];

			for ( var i in data ) {
				date.push( data[ i ].date );
				visits.push( data[ i ].visits );
			}

			var cfg             = tptnChartTypeConfig( date.length );
			var ctx             = $( '#visits' );
			var enableBarClick  = !! ( tptn_chart_data && parseInt( tptn_chart_data.enableBarClick, 10 ) === 1 );

			var config = {
				type: cfg.chartType,
				data: {
					labels: date,
					datasets: [
						{
							label: tptn_chart_data.datasetlabel,
							backgroundColor: cfg.backgroundColor,
							borderColor: '#70c4e1',
							hoverBackgroundColor: '#ffbf00',
							hoverBorderColor: '#ffbf00',
							data: visits,
							fill: cfg.chartFill,
							pointBackgroundColor: cfg.pointBackgroundColor,
							pointBorderColor: cfg.pointBorderColor,
							pointRadius: cfg.pointRadius,
							borderWidth: cfg.borderWidth,
						},
					],
				},
				plugins: [ ChartDataLabels ],
				options: {
					plugins: {
						title: {
							text: tptn_chart_data.charttitle,
							display: true,
						},
						legend: {
							display: false,
							position: 'bottom',
						},
						datalabels: {
							color: '#000000',
							anchor: 'end',
							align: 'top',
							display: cfg.showDataLabels ? 'auto' : false,
							font: {
								size: 11,
							},
							formatter: tptnFormatNumber,
						},
					},
					scales: {
						x: {
							type: 'time',
							time: {
								tooltipFormat: 'DD',
								unit: 'day',
								displayFormats: {
									day: 'DD',
								},
							},
							title: {
								display: false,
								labelString: 'Date',
							},
						},
						y: {
							grace: '5%',
							suggestedMin: 0,
							display: true,
							ticks: {
								callback: function ( value ) {
									return tptnFormatNumber( value );
								},
							},
							title: {
								display: false,
								text: tptn_chart_data.datasetlabel,
								color: '#000',
								padding: { top: 30, left: 0, right: 0, bottom: 0 },
							},
						},
					},
				},
			};

			if ( enableBarClick ) {
				config.options.onClick = function ( event, elements ) {
					if ( elements.length > 0 ) {
						var index       = elements[ 0 ].index;
						var clickedDate = date[ index ];
						var dateObj     = new Date( clickedDate );
						var day         = String( dateObj.getDate() ).padStart( 2, '0' );
						var monthNames  = [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ];
						var month       = monthNames[ dateObj.getMonth() ];
						var year        = dateObj.getFullYear();
						var formattedDate = day + '+' + month + '+' + year;

						var url = tptn_chart_data.statsUrl + '&orderby=daily_count&order=desc&post-date-filter-from=' + formattedDate + '&post-date-filter-to=' + formattedDate;
						window.open( url, '_blank' );
					}
				};

				config.options.onHover = function ( event, elements ) {
					event.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
				};

				config.options.plugins.tooltip = {
					callbacks: {
						label: function ( context ) {
							return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
						},
						afterBody: function () {
							return tptn_chart_data.clickBarHint;
						},
					},
				};
			} else {
				config.options.plugins.tooltip = {
					callbacks: {
						label: function ( context ) {
							return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
						},
					},
				};
			}

			window.top10chart = new Chart( ctx, config );
		},
		error: function ( data ) {
			// console.log(data);
		},
	} );
} );
