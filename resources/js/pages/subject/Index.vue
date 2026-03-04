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
import { ArchiveX, LoaderCircle } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import SearchInput from '@/components/SearchInput.vue';
import { PaginatedResponse } from '@/types/PaginatedResponse';
import { Subject } from '@/types/Subject';
import { SelectOption } from '@/types/SelectOption';
import Switch from '@/components/ui/switch/Switch.vue';
import Badge from '@/components/ui/badge/Badge.vue';

interface Props {
    subjects: PaginatedResponse<Subject>,
    filters: Record<string, any>,
    classes: SelectOption[],
    canCreate: boolean,
}

const props = defineProps<Props>();
const search = ref(props.filters?.search || '')
const classId = ref(props.filters?.class_id || '')
const loading = ref(false)

const onSearch = async () => {
    loading.value = true
    router.get(route('subjects.index'), {
        search: search.value || '',
        class_id: classId.value || '',
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
    router.get(route('subjects.index'), {
        page,
        search: search.value || '',
        class_id: classId.value || '',
    }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => loading.value = false
    })
}

const toggleActive = (val: boolean, subject: Subject) => {
    subject.is_active = val;
    router.put(route('subject.toggle', subject.id), { is_active: val });
};

const breadcrumbs = [{ title: 'Subjects', href: '/subjects' }];
</script>

<template>
    <Head title="Subjects" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <div class="flex justify-between">
                <Heading title="Subjects" description="Manage class-wise subjects for students." />
                <Link v-if="props.canCreate" :href="route('subject.create')">
                <Button :variant="'default'" :tabindex="0" class="w-full md:w-32">
                    Create
                </Button>
                </Link>
            </div>

            <CardContent>
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:gap-4 w-full">
                    <div class="flex gap-4 w-full">
                        <SearchInput v-model="search" placeholder="Search subject/class/code..." />
                        <Select v-model="classId">
                            <SelectTrigger>
                                <SelectValue placeholder="Select Class" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="all">All</SelectItem>
                                    <SelectItem v-for="c in props.classes" :key="c.value" :value="c.value">
                                        {{ c.label }}
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

                <div>
                    <Card class="shadow-none my-4 bg-slate-50 dark:bg-slate-900">
                        <Table class="w-full">
                            <TableHeader class="bg-slate-100 dark:bg-slate-800">
                                <TableRow>
                                    <TableHead class="font-bold text-black dark:text-white">Subject | Code</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Class</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Status</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Active</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="bg-white dark:bg-slate-950">
                                <TableRow v-for="subject in props.subjects?.data" :key="subject.id">
                                    <TableCell class="text-black dark:text-gray-200">
                                        <div class="text-black dark:text-gray-200 leading-tight">
                                            <div class="font-medium">{{ subject.name }}</div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ subject.code || '-'
                                                }}</p>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                        {{ subject.class?.name || '-' }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="subject.is_active ? 'default' : 'outline'">
                                            {{ subject.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="capitalize text-black dark:text-gray-200">
                                        <Switch :disabled="!props.canCreate" v-model="subject.is_active"
                                            @update:modelValue="(val) => toggleActive(val, subject)" />
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>

                        <div v-if="props.subjects?.data?.length === 0"
                            class="flex flex-col items-center justify-center py-10 text-gray-500 dark:text-gray-400">
                            <ArchiveX :size="60" />
                            <p>No subjects found</p>
                        </div>
                    </Card>
                </div>
            </CardContent>

            <Pagination v-if="props.subjects?.data?.length != 0" :items-per-page="props.subjects?.per_page"
                :total="props.subjects?.total" :default-page="props.subjects?.current_page">
                <PaginationContent v-slot="{ items }">
                    <PaginationPrevious v-if="props.subjects?.prev_page_url"
                        @click="goToPage(props.subjects.current_page - 1)" />
                    <template v-for="(item, index) in items" :key="index">
                        <PaginationItem v-if="item.type === 'page'" :value="item.value"
                            :is-active="item.value === props.subjects?.current_page" @click="goToPage(item.value)">
                            {{ item.value }}
                        </PaginationItem>
                    </template>
                    <PaginationEllipsis v-if="props.subjects?.last_page > 5" :index="4" />
                    <PaginationNext v-if="props.subjects?.next_page_url"
                        @click="goToPage(props.subjects?.current_page + 1)" />
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
