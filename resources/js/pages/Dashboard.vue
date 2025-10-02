<script setup lang="ts">
import Heading from '@/components/Heading.vue'
import { Card, CardContent } from '@/components/ui/card'
import CardHeader from '@/components/ui/card/CardHeader.vue'
import CardTitle from '@/components/ui/card/CardTitle.vue'
// import BarChart from '@/components/ui/custom-chart/BarChart.vue'
// import LineChart from '@/components/ui/custom-chart/LineChart.vue'
// import PieChart from '@/components/ui/custom-chart/PieChart.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    stats: {
        type: Object,
        required: true,
        default: () => ({
            totalStudents: 0,
            totalFeeStructures: 0,
            totalPayments: 0,
            pendingPayments: 0,
            successfulPayments: 0,
            revenueThisMonth: 0,
            avgPaymentValue: 0,
            paymentsToday: 0,
            myPayments: 0,
        })
    },
    charts: Object,
    showRevenue: Boolean,
})

const completionRate = computed(() => {
    return props.stats.totalOrders > 0
        ? ((props.stats.totalOrders - props.stats.pendingOrders) / props.stats.totalOrders) * 100
        : 0
})
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    }
]; 
</script>

<template>

    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <div class="flex justify-between">
                <Heading title="Dashboard" description="See your dashboard Here" />
            </div>
            <CardContent>
                <div class="grid gap-4 md:grid-cols-2" :class="showRevenue ? 'lg:grid-cols-4' : 'lg:grid-cols-3'">

                    <!-- Total Students -->
                    <Card class="shadow-none">
                        <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle class="text-sm font-medium">Total Students</CardTitle>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-muted-foreground" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                            </svg>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.totalStudents }}</div>
                        </CardContent>
                    </Card>

                    <!-- Payments -->
                    <Card class="shadow-none">
                        <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle class="text-sm font-medium">Payments</CardTitle>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-muted-foreground" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M2 7h20M2 12h20M2 17h20" />
                            </svg>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.totalPayments }}</div>
                            <p class="text-xs text-muted-foreground mt-1">
                                {{ stats.pendingPayments }} pending
                            </p>
                        </CardContent>
                    </Card>

                    <!-- Successful Payments -->
                    <Card class="shadow-none">
                        <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle class="text-sm font-medium">Successful Payments</CardTitle>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-muted-foreground" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M5 13l4 4L19 7" />
                            </svg>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.successfulPayments }}</div>
                            <p class="text-xs text-muted-foreground mt-1">
                                {{ stats.paymentsToday }} today
                            </p>
                        </CardContent>
                    </Card>

                    <!-- Revenue -->
                    <Card v-if="showRevenue" class="shadow-none">
                        <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle class="text-sm font-medium">Revenue</CardTitle>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-muted-foreground" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">₹{{ stats.revenueThisMonth }}</div>
                            <p class="text-xs text-muted-foreground mt-1">
                                ₹{{ stats.avgPaymentValue }} avg payment
                            </p>
                        </CardContent>
                    </Card>

                    <!-- My Payments (for student role) -->
                    <Card v-if="stats.myPayments > 0" class="shadow-none">
                        <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle class="text-sm font-medium">My Payments</CardTitle>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-muted-foreground" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M12 8v4l3 3" />
                            </svg>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.myPayments }}</div>
                        </CardContent>
                    </Card>

                </div>

                <!-- Charts -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 mt-6">
                    <!-- Order Status Donut Chart -->
                    <!-- <Card class="shadow-none">
                        <CardHeader>
                            <CardTitle>Order Status</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <PieChart :data="charts?.statusDistribution" />
                        </CardContent>
                    </Card> -->

                    <!-- <Card class="shadow-none">
                        <CardHeader>
                            <CardTitle>Top Products</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <BarChart :data="charts.productSales" color="#10b981" />
                        </CardContent>
                    </Card> -->
                    <!-- <Card class="shadow-none">
                        <CardHeader>
                            <CardTitle>Orders by Day of Week</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <BarChart :data="charts.ordersByDayOfWeek.map(item => ({
                                product: item.day,
                                quantity: item.count
                            }))" color="#f59e0b" />
                        </CardContent>
                    </Card> -->
                    <!-- <Card class="shadow-none">
                        <CardHeader>
                            <CardTitle>Order Volume</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <LineChart :data="charts.orderVolume" />
                        </CardContent>
                    </Card> -->

                    <!-- <Card class="shadow-none">
                        <CardHeader>
                            <CardTitle>Processing Time</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <BarChart :data="charts.processingTime.map(item => ({
                                product: item.days,
                                quantity: item.count
                            }))" color="#6366f1" />
                        </CardContent>
                    </Card> -->
                    <!-- <Card class="shadow-none">
                        <CardHeader>
                            <CardTitle>Orders by Hour of Day</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <BarChart :data="charts.ordersByHourOfDay.map(item => ({
                                product: item.hour,
                                quantity: item.count
                            }))" color="#3b82f6" />
                        </CardContent>
                    </Card> -->
                </div>
            </CardContent>
        </div>
    </AppLayout>
</template>