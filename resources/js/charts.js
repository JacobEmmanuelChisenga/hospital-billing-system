import Chart from 'chart.js/auto';

const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'bottom',
            labels: {
                boxWidth: 12,
                padding: 16,
            },
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            ticks: {
                maxRotation: 0,
                autoSkip: true,
                maxTicksLimit: 8,
            },
        },
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0,
            },
        },
    },
};

function buildConfig(payload) {
    const config = {
        type: payload.type,
        data: payload.data,
        options: structuredClone(defaultOptions),
    };

    if (payload.type === 'pie') {
        config.options.plugins.legend.position = 'right';
        delete config.options.scales;
    }

    if (payload.type === 'line' && payload.data.datasets?.length > 1) {
        config.options.plugins.legend.display = true;
    }

    if (payload.type === 'bar' && payload.data.datasets?.[0]?.data?.some((value) => value > 1000)) {
        config.options.scales.y.ticks.callback = (value) => `K ${Number(value).toLocaleString()}`;
    }

    return config;
}

document.querySelectorAll('[data-dashboard-chart]').forEach((canvas) => {
    const payload = JSON.parse(canvas.dataset.dashboardChart);

    new Chart(canvas, buildConfig(payload));
});
