const dashboardRoot = document.getElementById('dashboard-growth');

if (dashboardRoot) {
    const userGrowthElements = {
        chart: document.getElementById('user-growth-chart'),
        empty: document.getElementById('user-growth-empty'),
        total: document.getElementById('user-growth-total'),
        selectedLabel: document.getElementById('user-growth-selected-label'),
        labels: document.getElementById('user-growth-labels'),
        buttons: Array.from(document.querySelectorAll('.user-growth-period')),
    };

    const architectGrowthElements = {
        chart: document.getElementById('architect-growth-chart'),
        empty: document.getElementById('architect-growth-empty'),
        total: document.getElementById('architect-growth-total'),
        selectedLabel: document.getElementById('architect-growth-selected-label'),
        labels: document.getElementById('architect-growth-labels'),
        buttons: Array.from(document.querySelectorAll('.architect-growth-period')),
    };

    const growthState = {
        userPeriod: '7d',
        architectPeriod: '7d',
    };

    const buildChartPath = (values, width, height, padding) => {
        if (!values.length) {
            return '';
        }

        if (values.length === 1) {
            const centerY = height / 2;
            return `M ${padding} ${centerY} L ${width - padding} ${centerY}`;
        }

        const minValue = Math.min(...values);
        const maxValue = Math.max(...values);
        const range = Math.max(maxValue - minValue, 1);
        const chartWidth = width - (padding * 2);
        const chartHeight = height - (padding * 2);

        return values
            .map((value, index) => {
                const x = padding + (chartWidth * index) / (values.length - 1);
                const normalized = (value - minValue) / range;
                const y = height - padding - (normalized * chartHeight);

                return `${index === 0 ? 'M' : 'L'} ${x.toFixed(2)} ${y.toFixed(2)}`;
            })
            .join(' ');
    };

    const formatCompactValue = (value) => {
        if (value >= 1000) {
            const compact = value / 1000;
            const display = compact >= 10 || Number.isInteger(compact)
                ? compact.toFixed(0)
                : compact.toFixed(1);

            return `${display}k`;
        }

        return value.toLocaleString();
    };

    const closeGrowthMenus = () => {
        document.querySelectorAll('.growth-menu').forEach((menu) => {
            menu.classList.add('hidden');
        });

        document.querySelectorAll('.growth-menu-toggle').forEach((button) => {
            button.setAttribute('aria-expanded', 'false');
        });
    };

    const renderGrowthChart = (elements, dataset, summaryKey) => {
        const width = 640;
        const height = 240;
        const padding = 18;
        const labels = Array.isArray(dataset?.chart?.labels) ? dataset.chart.labels : [];
        const values = Array.isArray(dataset?.chart?.values) ? dataset.chart.values.map(Number) : [];
        const total = Number(dataset?.summary?.[summaryKey] ?? 0);
        const linePath = buildChartPath(values, width, height, padding);
        const hasActivity = values.some((value) => value > 0);

        elements.total.textContent = formatCompactValue(total);
        elements.empty.classList.toggle('hidden', hasActivity);
        elements.chart.innerHTML = `
            <path d="${linePath}" fill="none" stroke="#E8820C" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></path>
        `;
        elements.labels.style.gridTemplateColumns = `repeat(${Math.max(labels.length, 1)}, minmax(0, 1fr))`;
        elements.labels.innerHTML = labels
            .map((label) => `<span class="text-center">${label}</span>`)
            .join('');
    };

    const syncSelectedLabel = (elements, period) => {
        elements.buttons.forEach((button) => {
            button.dataset.active = button.dataset.period === period ? 'true' : 'false';
        });

        const activeButton = elements.buttons.find((button) => button.dataset.period === period);
        elements.selectedLabel.textContent = activeButton?.dataset.label ?? 'Last 7 days';
    };

    const fetchJson = async (url) => {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            return null;
        }

        return response.json();
    };

    const loadUserGrowth = async (period) => {
        growthState.userPeriod = period;
        syncSelectedLabel(userGrowthElements, period);

        try {
            const payload = await fetchJson(`${dashboardRoot.dataset.userGrowthUrl}?period=${period}`);

            if (payload) {
                renderGrowthChart(userGrowthElements, payload.data ?? {}, 'total_new_users');
            }
        } catch (error) {
            console.error('Error fetching user growth data:', error);
        }
    };

    const loadArchitectGrowth = async (period) => {
        growthState.architectPeriod = period;
        syncSelectedLabel(architectGrowthElements, period);

        try {
            const payload = await fetchJson(`${dashboardRoot.dataset.architectGrowthUrl}?period=${period}`);

            if (payload) {
                renderGrowthChart(architectGrowthElements, payload.data ?? {}, 'total_new_architects');
            }
        } catch (error) {
            console.error('Error fetching architect growth data:', error);
        }
    };

    userGrowthElements.buttons.forEach((button) => {
        button.addEventListener('click', () => {
            closeGrowthMenus();
            loadUserGrowth(button.dataset.period ?? '7d');
        });
    });

    architectGrowthElements.buttons.forEach((button) => {
        button.addEventListener('click', () => {
            closeGrowthMenus();
            loadArchitectGrowth(button.dataset.period ?? '7d');
        });
    });

    document.querySelectorAll('.growth-menu-toggle').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();

            const targetId = button.dataset.target;
            const targetMenu = targetId ? document.getElementById(targetId) : null;
            const willOpen = targetMenu?.classList.contains('hidden') ?? false;

            closeGrowthMenus();

            if (targetMenu && willOpen) {
                targetMenu.classList.remove('hidden');
                button.setAttribute('aria-expanded', 'true');
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element) || !event.target.closest('.relative')) {
            closeGrowthMenus();
        }
    });

    fetchJson(dashboardRoot.dataset.statsUrl)
        .then((payload) => {
            const stats = payload?.data ?? {};

            document.getElementById('total-users').textContent = Number(stats.total_users ?? 0).toLocaleString();
            document.getElementById('total-architects').textContent = Number(stats.total_architects ?? 0).toLocaleString();
            document.getElementById('total-designs').textContent = Number(stats.total_designs ?? 0).toLocaleString();
        })
        .catch((error) => {
            console.error('Error fetching dashboard stats:', error);
        });

    loadUserGrowth(growthState.userPeriod);
    loadArchitectGrowth(growthState.architectPeriod);
}
