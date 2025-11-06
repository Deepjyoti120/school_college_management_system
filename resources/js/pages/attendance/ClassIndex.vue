<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { nextTick, ref, watch } from 'vue';
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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import Button from '@/components/ui/button/Button.vue';
import { ArchiveX, LoaderCircle, LucideCheckCircle, LucideEye, Pen, } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import SearchInput from '@/components/SearchInput.vue';
import { PaginatedResponse } from '@/types/PaginatedResponse';
import { Holiday } from '@/types/Holiday';
import Switch from '@/components/ui/switch/Switch.vue';
import { SchoolClass } from '@/types/SchoolClass';
import { User } from '@/types/User';

interface Props {
    schoolClasses: PaginatedResponse<SchoolClass>,
    filters: Record<string, any>,
    canCreate: boolean,
    user?: User | null,
}
const props = defineProps<Props>();
const search = ref(props.filters?.search || '')
const loading = ref(false)
const onSearch = async () => {
    loading.value = true
    router.get(route('classes.attendances.index'), {
        search: search.value || '',
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
    router.get(route('classes.attendances.index'), {
        page,
        search: search.value || '',
    }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => loading.value = false
    })
}
const toggleActive = (val: boolean, holiday: Holiday) => {
    holiday.is_active = val;
    router.put(route('holiday.toggle', holiday.id), { is_active: val });
};
const breadcrumbs = [ { title: 'Classes', href: '/classes-atendances' }];
</script>

<template>

    <Head title="Attendance" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <div class="flex justify-between">
                <Heading title="Attendance" description="Check attendance here" />
                <div class="flex gap-2">
                    <!-- <Link 
                    :href="route('attendance.index', { schoolClass: 'd', school_id: props.user?.id })"
                    >
                    <Button :variant="'default'" :tabindex="0">
                        <LucideCheckCircle class="h-4 w-4" /> View Teachers Attendance
                    </Button>
                    </Link> -->
                </div>
            </div>
            <CardContent>
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:gap-4 w-full">
                    <div class="flex gap-4 w-full">
                        <SearchInput v-model="search" placeholder="Search by name..." />
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
                                    <TableHead class="font-bold text-black dark:text-white">Name
                                    </TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Total Students
                                    </TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Is Active</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="bg-white dark:bg-slate-950">
                                <TableRow v-for="schoolClass in props.schoolClasses?.data" :key="schoolClass.id">
                                    <TableCell class="text-black dark:text-gray-200">
                                        <div class="text-black dark:text-gray-200 leading-tight">
                                            <div class="font-medium">{{ schoolClass?.name }}</div>
                                            <!-- <div class="text-black dark:text-gray-200 leading-tight"> -->
                                            <!-- <div class="font-medium">{{ holiday.description }}</div> -->
                                            <!-- <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Order-{{ order.order_number }}
                                            </p> -->
                                            <!-- </div> -->
                                            <!-- <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ (fee.creator?.country_code ?? '') + (' ' + fee.creator?.phone ||
                                                    '') }} <Badge class="mt-1" :variant="fee.creator?.role_color"
                                                    :class="fee.creator?.role_color">
                                                    {{ fee.creator?.role_label }}
                                                </Badge>
                                            </p> -->
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                        {{ schoolClass?.students?.length || 0 }}
                                    </TableCell>
                                    <!-- <TableCell class="text-black dark:text-gray-200">
                                        {{ schoolClass.date_formatted }}
                                    </TableCell> -->
                                    <TableCell class="capitalize text-black dark:text-gray-200">
                                        <Switch :id="schoolClass.id" disabled v-model="schoolClass.is_active" />
                                    </TableCell>
                                    <TableCell class="capitalize text-black dark:text-gray-200">
                                        <Link
                                            :href="route('attendance.index', [schoolClass.id, schoolClass.school_id])">
                                        <Button size="sm" variant="outline" :tabindex="0" class="h-8 w-8">
                                            <LucideEye :size="60" />
                                        </Button>
                                        </Link>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <div v-if="props.schoolClasses?.data?.length === 0"
                            class="flex flex-col items-center justify-center py-10 text-gray-500 dark:text-gray-400">
                            <ArchiveX :size="60" />
                            <p>No data found</p>
                        </div>
                    </Card>
                </div>
            </CardContent>
            <Pagination v-if="props.schoolClasses?.data?.length != 0" :items-per-page="props.schoolClasses?.per_page"
                :total="props.schoolClasses?.total" :default-page="props.schoolClasses?.current_page">
                <PaginationContent v-slot="{ items }">
                    <PaginationPrevious v-if="props.schoolClasses?.prev_page_url"
                        @click="goToPage(props.schoolClasses.current_page - 1)" />
                    <template v-for="(item, index) in items" :key="index">
                        <PaginationItem v-if="item.type === 'page'" :value="item.value"
                            :is-active="item.value === props.schoolClasses?.current_page" @click="goToPage(item.value)">
                            {{ item.value }}
                        </PaginationItem>
                    </template>
                    <PaginationEllipsis v-if="props.schoolClasses?.last_page > 5" :index="4" />
                    <PaginationNext v-if="props.schoolClasses?.next_page_url"
                        @click="goToPage(props.schoolClasses?.current_page + 1)" />
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
