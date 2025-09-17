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
import { FeeStructure } from '@/types/FeeStructure';


interface Props {
    fees: PaginatedResponse<FeeStructure>,
    filters: Record<string, any>,
    feeTypes: SelectOption[],
}
const props = defineProps<Props>();
const search = ref(props.filters?.search || '')
const status = ref(props.filters?.statusOptions)
const feeType = ref(props.feeTypes)
const loading = ref(false)
const onSearch = async () => {
    loading.value = true
    router.get(route('fees.index'), {
        search: search.value || '',
        status: status.value,
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
    router.get(route('fees.index'), {
        page,
        search: search.value || '',
        status: status.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => loading.value = false
    })
}
const generateFee = () => {
    loading.value = true
    router.get(route('fees.generate'), {
        type: feeType.value || '',
    }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => loading.value = false,
        onSuccess: () => {
            // toast.success('Fee generated successfully.');
        },
    })
}
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
            <CardContent>
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:gap-4 w-full">
                    <div class="flex gap-4 w-full">
                        <SearchInput v-model="search" placeholder="Search by name..." />
                        <Select v-model="status">
                            <SelectTrigger>
                                <SelectValue placeholder="Select Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="all">All</SelectItem>
                                    <SelectItem v-for="r in props.statusOptions" :key="r.value" :value="r.value">
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
                                    <TableHead class="font-bold text-black dark:text-white">Name</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Type</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Frequency</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Amount</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Action</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody v-if="props.fees?.data?.length > 0" class="bg-white dark:bg-slate-950">
                                <TableRow v-for="fee in props.fees?.data" :key="fee.id">
                                    <TableCell class="text-black dark:text-gray-200">
                                        {{ fee.name }}
                                    </TableCell>

                                    <TableCell>
                                        <Badge :variant="fee.type_color" :class="fee.type_color">
                                            {{ fee.type_label }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="fee.frequency_color" :class="fee.frequency_color">
                                            {{ fee.frequency_label }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                        ₹{{ fee.amount }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-2">
                                            <!-- <Link :href="route('fees.edit', fee.id)">
                                            <Button size="sm" variant="secondary" class="h-8 w-8">
                                                <Pen :size="16" />
                                            </Button>
                                            </Link> -->
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
