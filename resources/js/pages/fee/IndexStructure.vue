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
import { ArchiveX, LoaderCircle, LucideCheckCircle, LucideClock, LucideEye, LucideWallet, Pen, } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import SearchInput from '@/components/SearchInput.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { SelectOption } from '@/types/SelectOption';
import { PaginatedResponse } from '@/types/PaginatedResponse';
import { FeeStructure } from '@/types/FeeStructure';
import Switch from '@/components/ui/switch/Switch.vue';
import Sheet from '@/components/ui/sheet/Sheet.vue';
import FeeDetails from './FeeDetails.vue';


interface Props {
    fees: PaginatedResponse<FeeStructure>,
    filters: Record<string, any>,
    feeTypes: SelectOption[],
    academicYears: SelectOption[],
}
const props = defineProps<Props>();
const search = ref(props.filters?.search || '')
const feeType = ref(props.filters?.feeType)
const academicYear = ref(props.filters?.academicYear || '')
const loading = ref(false)
const onSearch = async () => {
    loading.value = true
    router.get(route('fees.structure'), {
        search: search.value || '',
        feeType: feeType.value,
        academicYear: academicYear.value,
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
    router.get(route('fees.structure'), {
        page,
        search: search.value || '',
        feeType: feeType.value,
        academicYear: academicYear.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => loading.value = false
    })
}
// const generateFee = () => {
//     loading.value = true
//     router.get(route('fees.generate'), {
//         type: feeType.value || '',
//     }, {
//         preserveScroll: true,
//         preserveState: true,
//         onFinish: () => loading.value = false,
//         onSuccess: () => {
//             // toast.success('Fee generated successfully.');
//         },
//     })
// }
const feeStructure = ref<FeeStructure | null>(null);
const isSheetOpen = ref(false);
const sheetCloseBtn = async () => {
    feeStructure.value = null;
    isSheetOpen.value = false;
}
const takeAction = (feeS: FeeStructure) => {
    feeStructure.value = feeS;
    isSheetOpen.value = true;
}
const toggleActive = (val: boolean, fee: any) => {
    fee.is_active = val;
    router.put(route('fee.toggle', fee.id), { is_active: val });
};
const breadcrumbs = [{ title: 'Fee Structure', href: '/fees/structure' }];

</script>

<template>

    <Head title="Fee Structure" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <div class="flex justify-between">
                <Heading title="Fee Structure" description="Manage or create academic fees structure" />
                <Link :href="route('fees.create')">
                <Button :variant="'default'" :tabindex="0" class="w-full md:w-32">
                    Create New
                </Button>
                </Link>
            </div>
            <Sheet v-model:open="isSheetOpen">
                <FeeDetails :fee-structure="feeStructure!" :open="isSheetOpen" @close="sheetCloseBtn" />
            </Sheet>
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
                                    <TableHead class="font-bold text-black dark:text-white">Name | Period</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Type | Frequency</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Class</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Amount | GST</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Total Amount</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Payable | Paid | Pending
                                        Amount
                                    </TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Active</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">View</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody v-if="props.fees?.data?.length > 0" class="bg-white dark:bg-slate-950">
                                <TableRow v-for="fee in props.fees?.data" :key="fee.id">
                                    <TableCell class="text-black dark:text-gray-200">
                                        <div class="text-black dark:text-gray-200 leading-tight">
                                            <div class="font-medium">{{ fee.name }}</div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ fee.month_name }} {{
                                                fee.month_name ? '|' : '' }} {{ fee.year }}</p>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-black dark:text-gray-200 leading-tight grid gap-0.5">
                                            <Badge :variant="fee.type_color" :class="fee.type_color">
                                                {{ fee.type_label }}
                                            </Badge>
                                            <p>
                                                <Badge :variant="fee.frequency_color" :class="fee.frequency_color">
                                                    {{ fee.frequency_label }}
                                                </Badge>
                                            </p>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        {{ fee.class.name }}
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                        ₹{{ fee.amount }}
                                        <p> ₹{{ fee.gst_amount }}</p>
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                        ₹{{ fee.total_amount }}
                                    </TableCell>
                                    <TableCell class="flex items-center gap-4 text-black dark:text-gray-200">
                                        <div class="flex flex-col text-black dark:text-gray-200">
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center gap-1">
                                                    <LucideWallet class="w-4 h-4 text-blue-500" />
                                                    <span>₹{{ fee.total_payable }}</span>
                                                </div>
                                                <span class="text-gray-400">|</span>
                                                <div class="flex items-center gap-1">
                                                    <LucideCheckCircle class="w-4 h-4 text-green-500" />
                                                    <span>₹{{ fee.total_paid }}</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1 mt-1">
                                                <LucideClock class="w-4 h-4 text-yellow-500" />
                                                <span>Pending: ₹{{ fee.pending_amount }}</span>
                                            </div>
                                        </div>

                                    </TableCell>
                                    <TableCell>
                                        <Switch @update:modelValue="(val) => toggleActive(val, fee)"
                                            v-model="fee.is_active" />
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-2 ">
                                            <!-- <Link :href="route('user.profile', fee.id)"> -->
                                            <!-- <Button size="sm" variant="outline" :tabindex="0" class="h-8 w-8">
                                                <LucideEye :size="60" />
                                            </Button> -->
                                            <!-- </Link> -->

                                            <Button @click="takeAction(fee)" size="sm" variant="outline" :tabindex="0"
                                                class="h-8 w-8">
                                                <LucideEye :size="60" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>

                        <div v-if="props.fees?.data?.length === 0"
                            class="flex flex-col items-center justify-center py-10 text-gray-500 dark:text-gray-400">
                            <ArchiveX :size="60" />
                            <p>No fees found</p>
                        </div>

                    </Card>
                </div>
            </CardContent>
            <Pagination v-if="props.fees?.data?.length != 0" :items-per-page="props.fees?.per_page"
                :total="props.fees?.total" :default-page="props.fees?.current_page">
                <PaginationContent v-slot="{ items }">
                    <PaginationPrevious v-if="props.fees?.prev_page_url"
                        @click="goToPage(props.fees.current_page - 1)" />
                    <template v-for="(item, index) in items" :key="index">
                        <PaginationItem v-if="item.type === 'page'" :value="item.value"
                            :is-active="item.value === props.fees?.current_page" @click="goToPage(item.value)">
                            {{ item.value }}
                        </PaginationItem>
                    </template>
                    <PaginationEllipsis v-if="props.fees?.last_page > 5" :index="4" />
                    <PaginationNext v-if="props.fees?.next_page_url" @click="goToPage(props.fees?.current_page + 1)" />
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
