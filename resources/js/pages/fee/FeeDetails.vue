<script setup lang="ts">
import SheetContent from '@/components/ui/sheet/SheetContent.vue';
import SheetHeader from '@/components/ui/sheet/SheetHeader.vue';
import SheetTitle from '@/components/ui/sheet/SheetTitle.vue';
import SheetDescription from '@/components/ui/sheet/SheetDescription.vue';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import Badge from '@/components/ui/badge/Badge.vue';
import Avatar from '@/components/ui/avatar/Avatar.vue';
import AvatarFallback from '@/components/ui/avatar/AvatarFallback.vue';
import Button from '@/components/ui/button/Button.vue';
import SearchInput from '@/components/SearchInput.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { LoaderCircle, ArchiveX, LucideEye, Pen } from 'lucide-vue-next';
import { getInitials } from '@/composables/useInitials';
import { User } from '@/types/User';
import { SelectOption } from '@/types/SelectOption';
import { FeeStructure } from '@/types/FeeStructure';
const emit = defineEmits(['close']);
const props = defineProps<{
    open: boolean;
    feeStructure?: FeeStructure | null;
    roles: SelectOption[],
}>();

const users = ref<User[]>([]);
const loading = ref(false);
const search = ref('');
const role = ref('all');
const pageNum = ref(1);
const totalPages = ref(1);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            loadUsers();
        }
    },
    { immediate: true }
);
const loadUsers = async (page = 1) => {
    loading.value = true;
    try {
        const query = new URLSearchParams({
            page: page.toString(),
            role: role.value || 'all',
            search: search.value || '',
        });

        const res = await fetch(`${route('fee.users', props.feeStructure)}?${query}`);
        const data = await res.json();
        console.log(data);
        users.value = data.data || [];
        totalPages.value = data.meta?.last_page || 1;
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const onSearch = async () => {
    await loadUsers(1);
};
const goToPage = async (p: number) => {
    if (p >= 1 && p <= totalPages.value) await loadUsers(p);
};
</script>

<template>
    <SheetContent class="w-[100vw] sm:w-[100vw] md:w-[80vw] lg:max-w-[60vw]">
        <SheetHeader>
            <SheetTitle>Fee Details of {{ props.feeStructure?.name }}</SheetTitle>
            <SheetDescription>Details of Student Payments and Dues</SheetDescription>
        </SheetHeader>

        <ScrollArea class="h-full overflow-auto">
            <div class="p-4 space-y-4">
                <div class="flex flex-col md:flex-row gap-4 items-end">
                    <SearchInput v-model="search" placeholder="Search by name or email..." class="flex-1" />
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
                    <Button @click="onSearch" :disabled="loading">
                        <LoaderCircle v-if="loading" class="w-4 h-4 animate-spin mr-2" />
                        Search
                    </Button>
                </div>
                <div v-if="loading" class="flex justify-center py-10">
                    <LoaderCircle class="animate-spin w-6 h-6" />
                </div>

                <div v-else>
                    <Card class="shadow-none my-4 bg-slate-50 dark:bg-slate-900">
                        <Table class="w-full">
                            <TableHeader class="bg-slate-100 dark:bg-slate-800">
                                <TableRow>
                                    <TableHead class="font-bold text-black dark:text-white">Name | Email | Phone
                                    </TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">DOB | DOJ</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Payment Status</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Total Amount</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Role</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="bg-white dark:bg-slate-950">
                                <TableRow v-for="user in users" :key="user.id">
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
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.phone }}</p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                        {{ user.dob_formatted }}
                                        <p>
                                            {{ user.doj_formatted }}
                                        </p>
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                        <Badge v-if="user?.payment" :variant="user?.payment?.status_color"
                                            :class="user?.payment?.status_color">
                                            {{ user?.payment?.status_label }}
                                        </Badge>
                                        <Badge v-else :variant="'destructive'">
                                            Pending
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                      {{user?.payment ? ' ₹ '+ user?.payment?.total_amount : '₹ ' + feeStructure?.total_amount }}
                                    </TableCell>
                                    <TableCell class="capitalize text-black dark:text-gray-200">
                                        <Badge :variant="user.role_color" :class="user.role_color">
                                            {{ user.role_label }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="capitalize text-black dark:text-gray-200">
                                        <div class="flex gap-2">
                                            <Link :href="route('user.profile', user.id)" target="_blank">
                                            <Button size="sm" variant="outline" :tabindex="0" class="h-8 w-8">
                                                <LucideEye :size="60" />
                                            </Button>
                                            </Link>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <div v-if="users.length === 0" class="flex flex-col items-center py-10 text-gray-500">
                            <ArchiveX :size="40" />
                            <p>No users found</p>
                        </div>
                        <div v-if="users.length > 0" class="flex justify-center mt-4 gap-2">
                            <Button @click="goToPage(pageNum - 1)" :disabled="pageNum === 1">Previous</Button>
                            <Button v-for="p in totalPages" :key="p" :variant="p === pageNum ? 'default' : 'outline'"
                                @click="goToPage(p)">
                                {{ p }}
                            </Button>
                            <Button @click="goToPage(pageNum + 1)" :disabled="pageNum === totalPages">Next</Button>
                        </div>
                    </Card>
                </div>
            </div>
        </ScrollArea>
    </SheetContent>
</template>
