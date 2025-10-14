<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import { toast } from 'vue-sonner'
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import Button from '@/components/ui/button/Button.vue';
import { ArchiveX, LoaderCircle, Pen, } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import SearchInput from '@/components/SearchInput.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { SelectOption } from '@/types/SelectOption';
import { PaginatedResponse } from '@/types/PaginatedResponse';
import Switch from '@/components/ui/switch/Switch.vue';
import { Payment } from '@/types/Payment';
import Avatar from '@/components/ui/avatar/Avatar.vue';
import AvatarFallback from '@/components/ui/avatar/AvatarFallback.vue';
import { getInitials } from '@/composables/useInitials';


interface Props {
    payments: PaginatedResponse<Payment>,
    filters: Record<string, any>,
    feeTypes: SelectOption[],
    paymentStatuses: SelectOption[],
    academicYears: SelectOption[],
}
const props = defineProps<Props>();
const search = ref(props.filters?.search || '')
const feeType = ref(props.filters?.feeType)
const paymentStatus = ref(props.filters?.paymentStatus)
const academicYear = ref(props.filters?.academicYear || '')
const loading = ref(false)
const onSearch = async () => {
    loading.value = true
    router.get(route('payments.index'), {
        search: search.value || '',
        feeType: feeType.value,
        academicYear: academicYear.value,
        paymentStatus: paymentStatus.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            loading.value = false;
            toast('Search completed successfully.', {
                description: '',
                action: {
                    label: 'Undo',
                    onClick: () => console.log('Undo'),
                },
            })
        }
    })

    await nextTick()
}
const goToPage = (page: number) => {
    loading.value = true
    router.get(route('payments.index'), {
        page,
        search: search.value || '',
        feeType: feeType.value,
        academicYear: academicYear.value,
        paymentStatus: paymentStatus.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => loading.value = false
    })
}
// const toggleActive = (val: boolean, fee: any) => {
//     fee.is_active = val;
//     router.put(route('fee.toggle', fee.id), { is_active: val });
// };
const breadcrumbs = [{ title: 'Payments', href: '/payments' }];
</script>

<template>

    <Head title="Payments" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <div class="flex justify-between">
                <Heading title="Payments"
                    description="View and manage your payments, and transaction history in one place." />
            </div>
            <CardContent>
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:gap-4 w-full">
                    <div class="flex gap-4 w-full">
                        <SearchInput v-model="search" placeholder="Search by name..." />
                        <Select v-model="academicYear">
                            <SelectTrigger>
                                <SelectValue placeholder="Select Academic Year" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="r in props.academicYears" :key="r.value" :value="r.value">
                                        {{ r.label }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <Select v-model="feeType">
                            <SelectTrigger>
                                <SelectValue placeholder="Select FeeType" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="all">All</SelectItem>
                                    <SelectItem v-for="r in props.feeTypes" :key="r.value" :value="r.value">
                                        {{ r.label }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <Select v-model="paymentStatus">
                            <SelectTrigger>
                                <SelectValue placeholder="Payment Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="all">All</SelectItem>
                                    <SelectItem v-for="r in props.paymentStatuses" :key="r.value" :value="r.value">
                                        {{ r.label }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="md:w-auto w-full">
                        <Button @click="onSearch" type="submit" :tabindex="4" :disabled="loading"
                            class="w-full md:w-32">
                            <LoaderCircle v-if="loading" class="h-4 w-4 animate-spin mr-2" />
                            Search
                        </Button>
                    </div>
                </div>
                <!-- <div v-if="loading" class="flex items-center justify-center py-10">
                    <LoaderCircle class="animate-spin" :size="24" />
                </div> -->
                <div>
                    <Card class="shadow-none my-4 bg-slate-50 dark:bg-slate-900">
                        <Table class="w-full">
                            <TableHeader class="bg-slate-100 dark:bg-slate-800">
                                <TableRow>
                                    <TableHead class="font-bold text-black dark:text-white">Name | Email | Phone
                                    </TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Fee Name | Period
                                    </TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Type | Frequency</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Class</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Amount | GST</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Total Amount</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Status</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody v-if="props.payments?.data?.length > 0" class="bg-white dark:bg-slate-950">
                                <TableRow v-for="payment in props.payments?.data" :key="payment.id">
                                    <TableCell class="text-black dark:text-gray-200">
                                        <div class="flex items-center gap-x-4">
                                            <Avatar class="h-8 w-8 overflow-hidden rounded-lg bg-amber-300">
                                                <AvatarFallback class="rounded-lg text-black dark:text-white">
                                                    {{ getInitials(payment.user?.name) }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div class="text-black dark:text-gray-200 leading-tight">
                                                <div class="font-medium">{{ payment.user?.name }}</div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ payment.user?.email }}
                                                    <!-- {{ payment.fee_structure?.month_name ? '|' : '' }}  -->
                                                    <!-- {{ payment.user?.phone ?? '' }} -->
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ payment.user?.phone ?? '' }}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                        <div class="text-black dark:text-gray-200 leading-tight">
                                            <div class="font-medium">{{ payment.fee_structure?.name }}</div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{
                                                payment.fee_structure?.month_name }} {{
                                                    payment.fee_structure?.month_name ? '|' : '' }} {{ payment.year }}</p>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-black dark:text-gray-200 leading-tight grid gap-0.5">
                                            <Badge :variant="payment.fee_structure?.type_color"
                                                :class="payment.fee_structure?.type_color">
                                                {{ payment.fee_structure?.type_label }}
                                            </Badge>
                                            <p>
                                                <Badge :variant="payment.fee_structure?.frequency_color"
                                                    :class="payment.fee_structure?.frequency_color">
                                                    {{ payment.fee_structure?.frequency_label }}
                                                </Badge>
                                            </p>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        {{ payment.fee_structure?.class.name }}
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                        ₹{{ payment.amount }}
                                        <p> ₹{{ payment.gst_amount }}</p>
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                        ₹{{ payment.total_amount }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-black dark:text-gray-200 leading-tight grid gap-0.5">
                                            <Badge :variant="payment.status_color" :class="payment.status_color">
                                                {{ payment.status_label }}
                                            </Badge>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>

                        <div v-if="props.payments?.data?.length === 0"
                            class="flex flex-col items-center justify-center py-10 text-gray-500 dark:text-gray-400">
                            <ArchiveX :size="60" />
                            <p>No payments found</p>
                        </div>

                    </Card>
                </div>
            </CardContent>
            <Pagination v-if="props.payments?.data?.length != 0" :items-per-page="props.payments?.per_page"
                :total="props.payments?.total" :default-page="props.payments?.current_page">
                <PaginationContent v-slot="{ items }">
                    <PaginationPrevious v-if="props.payments?.prev_page_url"
                        @click="goToPage(props.payments.current_page - 1)" />
                    <template v-for="(item, index) in items" :key="index">
                        <PaginationItem v-if="item.type === 'page'" :value="item.value"
                            :is-active="item.value === props.payments?.current_page" @click="goToPage(item.value)">
                            {{ item.value }}
                        </PaginationItem>
                    </template>
                    <PaginationEllipsis v-if="props.payments?.last_page > 5" :index="4" />
                    <PaginationNext v-if="props.payments?.next_page_url"
                        @click="goToPage(props.payments?.current_page + 1)" />
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
