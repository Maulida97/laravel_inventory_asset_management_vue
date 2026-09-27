<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const stats = [
    {
        title: 'Total Assets',
        value: '2,847',
        badge: '+12%',
        badgeType: 'positive',
        subtitle: 'from last month',
        icon: 'box',
    },
    {
        title: 'Available',
        value: '2,180',
        badge: '76%',
        badgeType: 'neutral',
        subtitle: 'of total assets',
        icon: 'check-circle',
    },
    {
        title: 'In Maintenance',
        value: '42',
        badge: 'Action req.',
        badgeType: 'warning',
        subtitle: '3 overdue',
        icon: 'wrench',
    },
    {
        title: 'Total Asset Value',
        value: '$4.2M',
        badge: '+8%',
        badgeType: 'positive',
        subtitle: 'YoY growth',
        icon: 'dollar',
    },
];

const categories = [
    { name: 'IT Equipment', value: 842, percent: 100, barClass: 'bar-blue' },
    { name: 'Furniture', value: 456, percent: 54, barClass: 'bar-teal' },
    { name: 'Electronics', value: 398, percent: 47, barClass: 'bar-amber' },
    { name: 'Office Supplies', value: 350, percent: 41, barClass: 'bar-purple' },
    { name: 'Vehicles', value: 234, percent: 27, barClass: 'bar-rose' },
];

const monthlyActivity = [
    { month: 'Mar', acq: 67, disp: 17 },
    { month: 'Apr', acq: 56, disp: 22 },
    { month: 'May', acq: 77, disp: 11 },
    { month: 'Jun', acq: 61, disp: 26 },
    { month: 'Jul', acq: 100, disp: 32 },
    { month: 'Aug', acq: 82, disp: 20 },
];

const activities = [
    { title: 'MacBook Pro #A-2841 assigned to Engineering', time: '2 hours ago' },
    { title: 'Server Rack #S-102 moved to Maintenance', time: '5 hours ago' },
    { title: 'New delivery of Office Supplies (24 items)', time: 'Yesterday' },
    { title: 'Delivery Van #V-09 returned from lease', time: 'Yesterday' },
    { title: 'Herman Miller Chair #F-931 disposed', time: '2 days ago' },
    { title: '15 Dell Monitors added to inventory', time: '3 days ago' },
];

const attentionItems = [
    { title: 'Forklift #M-042', desc: 'Annual maintenance overdue by 3 days', priority: 'High Priority', badgeClass: 'dash-badge-danger' },
    { title: 'Server UPS Battery', desc: 'Reporting low capacity, needs replacement', priority: 'High Priority', badgeClass: 'dash-badge-danger' },
    { title: 'Printer Ink Cartridges', desc: 'Low stock (3 remaining)', priority: 'Review', badgeClass: 'dash-badge-warning' },
    { title: 'Company Car #V-12', desc: 'Scheduled inspection next week', priority: 'Review', badgeClass: 'dash-badge-warning' },
    { title: 'Projector #E-118', desc: 'Reported faulty by Marketing team', priority: 'Review', badgeClass: 'dash-badge-warning' },
];
</script>

<template>
    <AppLayout>
        <Head title="Dashboard — AssetFlow" />

        <div class="dash-container">
            <div class="dash-header">
                <h1 class="dash-title">Dashboard</h1>
                <p class="dash-subtitle">Overview of your inventory and assets</p>
            </div>

            <!-- 4 Stat Cards Row -->
            <div class="dash-grid dash-stats-row">
                <div v-for="stat in stats" :key="stat.title" class="dash-card dash-stat-card">
                    <div class="dash-stat-top">
                        <span class="dash-stat-label">{{ stat.title }}</span>
                        <span 
                            class="dash-badge" 
                            :class="{
                                'dash-badge-positive': stat.badgeType === 'positive',
                                'dash-badge-warning': stat.badgeType === 'warning',
                                'dash-badge-neutral': stat.badgeType === 'neutral',
                            }"
                        >
                            {{ stat.badge }}
                        </span>
                    </div>
                    <div class="dash-stat-number">{{ stat.value }}</div>
                    <div class="dash-stat-sub">{{ stat.subtitle }}</div>
                </div>
            </div>

            <!-- Charts Row (2 Columns) -->
            <div class="dash-grid dash-2col">
                <!-- Assets by Category -->
                <div class="dash-card">
                    <h2 class="dash-card-title">Assets by Category</h2>
                    <div class="dash-bar-chart">
                        <div v-for="cat in categories" :key="cat.name" class="dash-bar-item">
                            <div class="dash-bar-label">{{ cat.name }}</div>
                            <div class="dash-bar-track">
                                <div class="dash-bar-fill" :class="cat.barClass" :style="{ width: `${cat.percent}%` }">
                                    <span class="dash-bar-value">{{ cat.value }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Activity Chart -->
                <div class="dash-card">
                    <h2 class="dash-card-title">Monthly Asset Activity</h2>
                    <div class="dash-chart-y-axis">
                        <div v-for="item in monthlyActivity" :key="item.month" class="dash-v-bar-group">
                            <div class="dash-v-bars">
                                <div class="dash-v-bar1" :style="{ height: `${item.acq}%` }"></div>
                                <div class="dash-v-bar2" :style="{ height: `${item.disp}%` }"></div>
                            </div>
                            <span class="dash-v-label">{{ item.month }}</span>
                        </div>
                    </div>
                    <div class="dash-chart-legend">
                        <div class="dash-legend-item">
                            <div class="dash-legend-color" style="background-color: var(--primary);"></div>
                            <span>Acquisitions</span>
                        </div>
                        <div class="dash-legend-item">
                            <div class="dash-legend-color" style="background-color: var(--danger);"></div>
                            <span>Disposals</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activities & Attention Row (2 Columns) -->
            <div class="dash-grid dash-2col">
                <!-- Recent Activities -->
                <div class="dash-card">
                    <h2 class="dash-card-title">Recent Activities</h2>
                    <div class="dash-list">
                        <div v-for="(act, idx) in activities" :key="idx" class="dash-list-item">
                            <div class="dash-list-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                                </svg>
                            </div>
                            <div class="dash-list-content">
                                <p class="dash-list-text">{{ act.title }}</p>
                                <span class="dash-list-time">{{ act.time }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assets Requiring Attention -->
                <div class="dash-card">
                    <h2 class="dash-card-title">Assets Requiring Attention</h2>
                    <div class="dash-list">
                        <div v-for="(item, idx) in attentionItems" :key="idx" class="dash-attention-item">
                            <div class="dash-attention-info">
                                <span class="dash-attention-title">{{ item.title }}</span>
                                <span class="dash-attention-desc">{{ item.desc }}</span>
                            </div>
                            <span class="dash-badge" :class="item.badgeClass">{{ item.priority }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.dash-container {
    color: var(--text-primary);
}

.dash-header {
    margin-bottom: 24px;
}

.dash-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 6px 0;
    color: var(--text-primary);
    letter-spacing: -0.02em;
}

.dash-subtitle {
    color: var(--text-secondary);
    margin: 0;
    font-size: 0.875rem;
}

.dash-grid {
    display: grid;
    gap: 20px;
    margin-bottom: 24px;
}

.dash-stats-row {
    grid-template-columns: repeat(4, 1fr);
}

.dash-2col {
    grid-template-columns: repeat(2, 1fr);
}

@media (max-width: 1024px) {
    .dash-stats-row { grid-template-columns: repeat(2, 1fr); }
    .dash-2col { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
    .dash-stats-row { grid-template-columns: 1fr; }
}

.dash-card {
    background-color: var(--bg-card);
    border-radius: var(--radius-lg);
    padding: 22px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border);
    transition: background-color var(--transition), border-color var(--transition);
}

.dash-card-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 18px 0;
    color: var(--text-primary);
}

.dash-stat-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.dash-stat-label {
    font-size: 0.8125rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.dash-stat-number {
    font-size: 1.875rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 6px;
    color: var(--text-primary);
    letter-spacing: -0.02em;
}

.dash-stat-sub {
    font-size: 0.75rem;
    color: var(--text-tertiary);
}

.dash-badge {
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: var(--radius-full);
    font-weight: 600;
    display: inline-flex;
    align-items: center;
}

.dash-badge-positive {
    background-color: var(--success-light);
    color: var(--success-dark);
}

.dash-badge-warning {
    background-color: var(--warning-light);
    color: var(--warning-dark);
}

.dash-badge-danger {
    background-color: var(--danger-light);
    color: var(--danger-dark);
}

.dash-badge-neutral {
    background-color: var(--bg-muted);
    color: var(--text-secondary);
}

/* Horizontal Bar Chart */
.dash-bar-chart {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.dash-bar-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.dash-bar-label {
    font-size: 0.8125rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.dash-bar-track {
    background-color: var(--bg-muted);
    border-radius: var(--radius-sm);
    height: 24px;
    width: 100%;
    overflow: hidden;
    display: flex;
}

.dash-bar-fill {
    height: 100%;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 8px;
    transition: width 0.8s ease;
}

.bar-blue { background-color: #6366f1; }
.bar-teal { background-color: #14b8a6; }
.bar-amber { background-color: #f59e0b; }
.bar-purple { background-color: #a855f7; }
.bar-rose { background-color: #f43f5e; }

.dash-bar-value {
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Vertical Bar Chart */
.dash-chart-y-axis {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    height: 160px;
    padding: 10px 0;
    gap: 12px;
}

.dash-v-bar-group {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    height: 100%;
    justify-content: flex-end;
    gap: 8px;
}

.dash-v-bars {
    display: flex;
    align-items: flex-end;
    gap: 4px;
    height: 100%;
    width: 100%;
    justify-content: center;
}

.dash-v-bar1 {
    width: 12px;
    background-color: var(--primary);
    border-radius: 3px 3px 0 0;
    transition: height 0.6s ease;
}

.dash-v-bar2 {
    width: 12px;
    background-color: var(--danger);
    border-radius: 3px 3px 0 0;
    transition: height 0.6s ease;
}

.dash-v-label {
    font-size: 0.75rem;
    color: var(--text-tertiary);
    font-weight: 500;
}

.dash-chart-legend {
    display: flex;
    gap: 18px;
    margin-top: 14px;
    justify-content: center;
}

.dash-legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    color: var(--text-secondary);
}

.dash-legend-color {
    width: 10px;
    height: 10px;
    border-radius: 2px;
}

/* Lists */
.dash-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.dash-list-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}
.dash-list-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.dash-list-icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm);
    background-color: var(--bg-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    flex-shrink: 0;
}

.dash-list-content {
    flex: 1;
    min-width: 0;
}

.dash-list-text {
    font-size: 0.8125rem;
    color: var(--text-primary);
    margin: 0 0 2px 0;
    line-height: 1.4;
}

.dash-list-time {
    font-size: 0.6875rem;
    color: var(--text-tertiary);
}

.dash-attention-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
    gap: 12px;
}
.dash-attention-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.dash-attention-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.dash-attention-title {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--text-primary);
}

.dash-attention-desc {
    font-size: 0.75rem;
    color: var(--text-secondary);
}
</style>