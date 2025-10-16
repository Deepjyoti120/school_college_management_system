<script setup lang="ts">
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Heading from '@/components/Heading.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Avatar from '@/components/ui/avatar/Avatar.vue';
import AvatarFallback from '@/components/ui/avatar/AvatarFallback.vue';
import { getInitials } from '@/composables/useInitials';
import { User } from '@/types/User';
import SearchInput from '@/components/SearchInput.vue';
import Select from '@/components/ui/select/Select.vue';
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue';
import SelectValue from '@/components/ui/select/SelectValue.vue';
import SelectGroup from '@/components/ui/select/SelectGroup.vue';
import SelectItem from '@/components/ui/select/SelectItem.vue';
import TableHeader from '@/components/ui/table/TableHeader.vue';
import TableRow from '@/components/ui/table/TableRow.vue';
import TableHead from '@/components/ui/table/TableHead.vue';
import TableBody from '@/components/ui/table/TableBody.vue';
import TableCell from '@/components/ui/table/TableCell.vue';
import Pagination from '@/components/ui/pagination/Pagination.vue';
import PaginationContent from '@/components/ui/pagination/PaginationContent.vue';
import PaginationPrevious from '@/components/ui/pagination/PaginationPrevious.vue';
import PaginationItem from '@/components/ui/pagination/PaginationItem.vue';
import PaginationEllipsis from '@/components/ui/pagination/PaginationEllipsis.vue';
import PaginationNext from '@/components/ui/pagination/PaginationNext.vue';
import SelectContent from '@/components/ui/select/SelectContent.vue';
import { onMounted, ref } from 'vue';
import { SelectOption } from '@/types/SelectOption';
import { Payment } from '@/types/Payment';
import { PaginatedResponse } from '@/types/PaginatedResponse';
import Button from '@/components/ui/button/Button.vue';
import { ArchiveX, LoaderCircle } from 'lucide-vue-next';
import Table from '@/components/ui/table/Table.vue';

interface Props {
    user: User;
    feeTypes: SelectOption[],
    paymentStatuses: SelectOption[],
    academicYears: SelectOption[],
}
const payments = ref<PaginatedResponse<Payment>>();
const loading = ref(false);
const search = ref('');
const feeType = ref('')
const paymentStatus = ref('paid')
const academicYear = ref('')
const props = defineProps<Props>();
onMounted(async () => {
    // academicYear.value = props.academicYears?.[0]?.value || ''
    await loadPayments(1);
});
const loadPayments = async (page = 1) => {
    loading.value = true;
    try {
        const query = new URLSearchParams({
            page: page.toString(), 
            search: search.value || '',
            feeType: feeType.value || '',
            paymentStatus: paymentStatus.value || '',
            academicYear: academicYear.value || '',
        });
        const res = await fetch(`${route('user.payments', props.user)}?${query}`);
        const data = await res.json();
        console.log(data);
        payments.value = data || null;
        // totalPages.value = data.meta?.last_page || 1;
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
};
const onSearch = async () => {
    await loadPayments(1);
};
const goToPage = async (p: number) => {
    if (p >= 1 && p <= (payments.value?.total ?? 0)) await loadPayments(p);
};
</script>

<template>
    <CardContent>
        <Heading title="Student Payments" description="View all student payments details." />
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
                <Button @click="onSearch" type="submit" :tabindex="4" :disabled="loading" class="w-full md:w-32">
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
                    <TableBody v-if="(payments?.data ?? []).length > 0" class="bg-white dark:bg-slate-950">
                        <TableRow v-for="payment in payments?.data" :key="payment.id">
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

                <div v-if="payments?.data?.length === 0"
                    class="flex flex-col items-center justify-center py-10 text-gray-500 dark:text-gray-400">
                    <ArchiveX :size="60" />
                    <p>No payments found</p>
                </div>
            </Card>
        </div>
    </CardContent>
    <Pagination v-if="payments?.data?.length != 0" :items-per-page="payments?.per_page ?? 0" :total="payments?.total"
        :default-page="payments?.current_page">
        <PaginationContent v-slot="{ items }">
            <PaginationPrevious v-if="payments?.prev_page_url" @click="goToPage(payments.current_page - 1)" />
            <template v-for="(item, index) in items" :key="index">
                <PaginationItem v-if="item.type === 'page'" :value="item.value"
                    :is-active="item.value === payments?.current_page" @click="goToPage(item.value)">
                    {{ item.value }}
                </PaginationItem>
            </template>
            <PaginationEllipsis v-if="(payments?.last_page ?? 0) > 5" :index="4" />
            <PaginationNext v-if="payments?.next_page_url" @click="goToPage(payments?.current_page + 1)" />
        </PaginationContent>
    </Pagination>
</template>
