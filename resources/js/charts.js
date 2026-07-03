import Chart from 'chart.js/auto';

function baseOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    padding: 16,
                    usePointStyle: true,
                },
            },
            tooltip: {
                callbacks: {
                    label(context) {
                        const value = context.parsed.y ?? context.parsed.x ?? context.raw;

                        if (typeof value === 'number' && value >= 1000) {
                            return `${context.dataset.label}: K ${value.toLocaleString()}`;
                        }

                        return `${context.dataset.label}: ${value}`;
                    },
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
}

function buildConfig(payload) {
    const config = {
        type: payload.type,
        data: payload.data,
        options: baseOptions(),
    };

    if (payload.type === 'pie') {
        config.options.plugins.legend.position = 'right';
        config.options.plugins.legend.labels.padding = 12;
        delete config.options.scales;
    }

    if (payload.type === 'line' && payload.data.datasets?.length === 1) {
        config.options.plugins.legend.display = false;
    }

    if (payload.type === 'bar' && payload.horizontal) {
        config.options.indexAxis = 'y';
        config.options.scales.x.ticks.precision = 0;
        config.options.scales.y.grid.display = false;
    }

    if (payload.type === 'line' && payload.data.datasets?.length > 1) {
        config.options.plugins.legend.display = true;
        config.options.interaction = {
            mode: 'index',
            intersect: false,
        };
    }

    const barValues = payload.data.datasets?.[0]?.data ?? [];

    if (payload.type === 'bar' && barValues.some((value) => value > 1000)) {
        const axis = payload.horizontal ? 'x' : 'y';
        config.options.scales[axis].ticks.callback = (value) => `K ${Number(value).toLocaleString()}`;
    }

    return config;
}

function initDashboardCharts() {
    document.querySelectorAll('[data-dashboard-chart]').forEach((canvas) => {
        if (canvas.dataset.chartReady === 'true') {
            return;
        }

        try {
            const payload = JSON.parse(canvas.dataset.dashboardChart);

            new Chart(canvas, buildConfig(payload));
            canvas.dataset.chartReady = 'true';
        } catch (error) {
            console.error('Dashboard chart failed to render.', error, canvas.dataset.dashboardChart);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardCharts);
} else {
    initDashboardCharts();
}
