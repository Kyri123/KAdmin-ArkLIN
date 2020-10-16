// CPU
var diagramCanvas = $('#stats').get(0).getContext('2d');
var diagramData = {
    labels : vars.labels,
    datasets: [
        {
            backgroundColor     : 'rgba(23, 162, 184, 1)',
            borderColor         : 'rgba(20, 162, 184, 1)',
            pointRadius         : false,
            pointColor          : '#17a2b8',
            pointStrokeColor    : '#17a2b8',
            pointHighlightFill  : '#fff',
            pointHighlightStroke: 'rgba(220,220,220,1)',
            data: vars.data
        }
    ]
}

var diagramOptions = {
    maintainAspectRatio : false,
    responsive : true,
    legend: {
        display: false
    },
    scales: {
        xAxes: [{
            gridLines : {
                display : false,
            }
        }],
        yAxes: [{
            ticks: {
                max: vars.max,
                beginAtZero: false
            },
            gridLines : {
                display : false,
            }
        }]
    }
}

var diagram = new Chart(diagramCanvas, {
    type: 'line',
    data: diagramData,
    options: diagramOptions
});