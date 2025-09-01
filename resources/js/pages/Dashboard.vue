<script setup lang="ts">
import Heading from '@/components/Heading.vue'
import { Card, CardContent} from '@/components/ui/card'
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
            totalOrders: 0,
            pendingOrders: 0,
            dispatchedOrders: 0,
            dispatchedToday: 0,
            avgProcessingTime: 0,
            rejectedOrders: 0,
            revenueThisMonth: 0,
            avgOrderValue: 0,
            fastestProcessingTime: 0,
            ordersByCurrentUser: 0
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
                <div class="grid gap-4 md:grid-cols-2"
                 :class="showRevenue ? 'lg:grid-cols-4' : 'lg:grid-cols-3'">
                    <!-- <Card class="shadow-none">
                        <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle class="text-sm font-medium">
                                Total Orders
                            </CardTitle>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                                class="h-4 w-4 text-muted-foreground">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.totalOrders }}</div>
                            <p class="text-xs text-muted-foreground mt-1">
                                {{ stats.ordersByCurrentUser }} created by you
                            </p>
                        </CardContent>
                    </Card> -->
                    <Card class="shadow-none">
                        <!-- <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle class="text-sm font-medium">
                                Pending Order
                            </CardTitle>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                                class="h-4 w-4 text-muted-foreground">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                            </svg>
                        </CardHeader> -->
                        <CardContent>
                            <!-- <div class="text-2xl font-bold">{{ stats.pendingOrders }}</div> -->
                            <!-- <div class="text-2xl font-bold">{{ Math.round(completionRate) }}%</div> -->
                            <!-- <Progress :model-value="completionRate" class="h-2 mt-2" /> -->
                            <!-- <p class="text-xs text-muted-foreground mt-1">
                                {{ stats.rejectedOrders }} Rejected
                            </p> -->
                        </CardContent>
                    </Card>
                    <!-- <Card class="shadow-none">
                        <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle class="text-sm font-medium">
                                Completed Order
                            </CardTitle>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                                class="h-4 w-4 text-muted-foreground">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 6v6l4 2" />
                            </svg>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.dispatchedOrders }}</div>
                            <p class="text-xs text-muted-foreground mt-1">
                                {{ stats.dispatchedToday }} today completed
                            </p>
                        </CardContent>
                    </Card> -->
                    <!-- <Card v-if="showRevenue" class="shadow-none">
                        <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle class="text-sm font-medium">
                                Revenue
                            </CardTitle>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                                class="h-4 w-4 text-muted-foreground">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">₹{{ stats.revenueThisMonth }}</div>
                            <p class="text-xs text-muted-foreground mt-1">
                                ₹{{ stats.avgOrderValue }} average order
                            </p>
                        </CardContent>
                    </Card> -->
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