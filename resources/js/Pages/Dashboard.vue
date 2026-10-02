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

<style scoped src="../../css/pages/dashboard.css"></style>