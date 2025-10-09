<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
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
import { ArchiveX, LoaderCircle, LucideCircleArrowRight, LucideEye, LucideView, Pen, View } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import SearchInput from '@/components/SearchInput.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { getInitials } from '@/composables/useInitials';
import Avatar from '@/components/ui/avatar/Avatar.vue';
import AvatarFallback from '@/components/ui/avatar/AvatarFallback.vue';
import Switch from '@/components/ui/switch/Switch.vue';
import { User } from '@/types/User';
import { AppPageProps } from '@/types';
// const props = defineProps({
//     users: Object,
//     filters: Object,
//     roles: Array,
//     canUserCreate: Boolean,
// })
interface Props {
    users: User[],
    filters: object,
    roles: object,
    canUserCreate: boolean,
}
const props = defineProps<Props>();
const search = ref(props.filters?.search || '')
const role = ref(props.filters?.role)
const loading = ref(false)
const page = usePage<AppPageProps>()
const onSearch = async () => {
    loading.value = true
    router.get(route('users.index'), {
        search: search.value || '',
        role: role.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            loading.value = false;
            page.props.flash.success = 'Search completed successfully.';
        }
    })

    await nextTick()
}
const goToPage = (page: number) => {
    loading.value = true
    router.get(route('users.index'), {
        page,
        search: search.value || '',
        role: role.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => loading.value = false
    })
}
const breadcrumbs = [{ title: 'Users', href: '/users' }];
const toggleActive = (val: boolean, user: any) => {
    user.is_active = val;
    router.put(route('user.toggle', user.id), { is_active: val });
};
</script>

<template>

    <Head title="Users" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <!-- <Heading title="Users"
                description="Manage your users here. You can view, edit, and delete users as needed" /> -->
            <div class="flex justify-between">
                <Heading title="Users"
                    description="Manage your users here. You can view, edit, and delete users as needed" />
                <Link :href="route('user.create')">
                <Button v-if="canUserCreate" :variant="'default'" :tabindex="0" class="w-full md:w-32">
                    Add New
                </Button>
                </Link>
            </div>
            <CardContent>
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:gap-4 w-full">
                    <div class="flex gap-4 w-full">
                        <SearchInput v-model="search" placeholder="Search by name..." />
                        <Select v-model="role">
                            <SelectTrigger>
                                <SelectValue placeholder="Select Role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="all">All</SelectItem>
                                    <SelectItem v-for="r in props.roles" :key="r.value" :value="r.value">
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
                                    <TableHead class="font-bold text-black dark:text-white">Name | Email</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">DOB</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">DOJ</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Role</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Is Active</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="bg-white dark:bg-slate-950">
                                <TableRow v-for="user in users?.data" :key="user.id">
                                    <TableCell class="text-black dark:text-gray-200">
                                        <div class="flex items-center gap-x-4">
                                            <Avatar class="h-8 w-8 overflow-hidden rounded-lg bg-amber-300">
                                                <AvatarFallback class="rounded-lg text-black dark:text-white">
                                                    {{ getInitials(user.name) }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div class="text-black dark:text-gray-200 leading-tight">
                                                <div class="font-medium">{{ user.name }}</div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                        {{ user.dob_formatted }}
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                        {{ user.doj_formatted }}
                                    </TableCell>
                                    <TableCell class="capitalize text-black dark:text-gray-200">
                                        <Badge :variant="user.role_color" :class="user.role_color">
                                            {{ user.role_label }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="capitalize text-black dark:text-gray-200">
                                        <Switch :disabled="!props.canUserCreate" v-model="user.is_active"
                                            @update:modelValue="(val) => toggleActive(val, user)" />
                                    </TableCell>
                                    <TableCell class="capitalize text-black dark:text-gray-200">
                                        <div class="flex gap-2">
                                            <Link :href="route('user.create', user.id)">
                                            <Button size="sm" variant="outline" class="h-8 w-8">
                                                <Pen :size="60" />
                                            </Button>
                                            </Link>
                                            <Link :href="route('user.profile', user.id)">
                                            <Button size="sm" variant="outline" :tabindex="0" class="h-8 w-8">
                                                <LucideEye :size="60" />
                                            </Button>
                                            </Link>
                                            <!-- <Button v-if="user.role === ''"  @click="updateUser(user)" size="sm" variant="outline" :tabindex="0" class="h-8 w-8">
                                                <LucideCircleArrowRight :size="60" />
                                            </Button> -->
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>

                        <div v-if="users?.data?.length === 0"
                            class="flex flex-col items-center justify-center py-10 text-gray-500 dark:text-gray-400">
                            <ArchiveX :size="60" />
                            <p>No users found</p>
                        </div>
                    </Card>

                </div>
            </CardContent>
            <Pagination v-if="users?.data?.length != 0" :items-per-page="users?.per_page" :total="users?.total"
                :default-page="users?.current_page">
                <PaginationContent v-slot="{ items }">
                    <PaginationPrevious v-if="users?.prev_page_url" @click="goToPage(users.current_page - 1)" />
                    <template v-for="(item, index) in items" :key="index">
                        <PaginationItem v-if="item.type === 'page'" :value="item.value"
                            :is-active="item.value === users?.current_page" @click="goToPage(item.value)">
                            {{ item.value }}
                        </PaginationItem>
                    </template>
                    <PaginationEllipsis v-if="users?.last_page > 5" :index="4" />
                    <PaginationNext v-if="users?.next_page_url" @click="goToPage(users?.current_page + 1)" />
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
