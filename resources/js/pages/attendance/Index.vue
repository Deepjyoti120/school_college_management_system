<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Button from '@/components/ui/button/Button.vue';
import Heading from '@/components/Heading.vue';
import SearchInput from '@/components/SearchInput.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { ArchiveX, LoaderCircle } from 'lucide-vue-next';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectGroup, SelectItem } from '@/components/ui/select';
import Input from '@/components/ui/input/Input.vue';

interface Props {
    users: any[],
    days: any[],
    filters: Record<string, any>,
    academicYears: Array<{ value: string | number, label: string }>,
    canCreate: boolean
}
const props = defineProps<Props>();

const search = ref('');
const month = ref(props.filters?.month || new Date().getMonth() + 1);
const year = ref(props.filters?.year || new Date().getFullYear());
const academicYear = ref(props.filters?.academic_year_id || '');
const loading = ref(false);
const onSearch = () => {
    loading.value = true;
    router.get(route('attendance.index', {
        schoolClass: route().params.schoolClass,
        school_id: route().params.school_id
    }), {
        search: search.value,
        month: month.value,
        year: year.value,
        academic_year_id: academicYear.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => loading.value = false
    });
};
const breadcrumbs = [
    { title: 'Classes', href: '/classes-atendances' },
    { title: 'Attendance', href: '/attendance' },
]; 
</script>

<template>
    <Head title="Attendance" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6 space-y-6">
            <Heading title="Teachers Attendance" description="Visit All Teachers Attendance" />
            <CardContent>
                <div class="flex flex-col  md:flex-row md:items-end md:gap-4 w-full">
                    <div class="grid grid-cols-12 gap-3 w-full">
                        <div class="col-span-12 md:col-span-10">
                            <SearchInput class="w-full" v-model="search" placeholder="Search by name..." />
                        </div>
                        <div class="col-span-6 md:col-span-1">
                            <Select v-model="month">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Month" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="m in 12" :key="m" :value="m">
                                            {{ new Date(0, m - 1).toLocaleString('default', { month: 'long' }) }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="col-span-6 md:col-span-1">
                            <Input type="number" v-model="year" placeholder="Year" class="w-full" />
                        </div>
                    </div>
                    <div class="md:w-auto w-full">
                        <Button @click="onSearch" type="submit" :disabled="loading" class="w-full md:w-32">
                            <LoaderCircle v-if="loading" class="h-4 w-4 animate-spin mr-2" />
                            Search
                        </Button>
                    </div>
                </div>
                <Card class="mx-auto shadow-none rounded-2xl mt-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-200 dark:border-slate-700 text-sm">
                            <thead class="bg-slate-100 dark:bg-slate-800 text-gray-700 dark:text-gray-200">
                                <tr>
                                    <th class="p-2 text-left sticky left-0 bg-slate-100 dark:bg-slate-800">Teacher</th>
                                    <th class="p-2 text-center">Total</th>
                                    <th v-for="day in props.days" :key="day.date" class="p-1 text-center">
                                        <div>{{ day.day }}</div>
                                        <div>{{ day.day_number }}</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in props.users" :key="user.user.id"
                                    class="odd:bg-white even:bg-slate-50 dark:odd:bg-slate-950 dark:even:bg-slate-900">
                                    <td class="p-2 font-medium sticky left-0 bg-white dark:bg-slate-950">
                                        {{ user.user.name }}
                                        <div class="text-black dark:text-gray-200 leading-tight">
                                            <p v-if="user.user.role == 'student'"
                                                class="text-sm text-gray-500 dark:text-gray-400">
                                                Roll No:{{ user.user.roll_number }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="p-2 text-center">
                                        <div class="font-semibold text-gray-700 dark:text-gray-300">
                                            {{
                                                user.attendances.filter(a => a.status === 'present').length
                                            }}/{{user.attendances.filter(a => a.status === 'absent').length}}/{{
                                                user.attendances.filter(a => a.status === 'leave').length}}
                                        </div>
                                    </td>
                                    <td v-for="day in props.days" :key="day.date" class="p-1 text-center">
                                        <div v-if="user.attendances.some(a => a.date === day.date)">
                                            <Badge
                                                :class="user.attendances.find(a => a.date === day.date)?.status_color"
                                                class="text-xs px-2 py-1 font-semibold">
                                                {{
                                                    user.attendances.find(a => a.date === day.date)?.status_label?.charAt(0)
                                                }}
                                            </Badge>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="users?.length === 0"
                            class="flex flex-col items-center justify-center py-10 text-gray-500 dark:text-gray-400">
                            <ArchiveX :size="60" />
                            <p>No attendance found</p>
                        </div>
                    </div>
                </Card>
            </CardContent>
        </div>
    </AppLayout>
</template>
