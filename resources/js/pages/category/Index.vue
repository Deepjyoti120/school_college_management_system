<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
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
import { ArchiveX, LoaderCircle, } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import SearchInput from '@/components/SearchInput.vue';
import Sheet from '@/components/ui/sheet/Sheet.vue';
import Create from '@/components/category/Create.vue';
import Avatar from '@/components/ui/avatar/Avatar.vue';
import AvatarImage from '@/components/ui/avatar/AvatarImage.vue';
import { Category } from '@/types/Category';
import { PaginatedResponse } from '@/types/PaginatedResponse';
import Switch from '@/components/ui/switch/Switch.vue';

// const props = defineProps({
//     categories: Object,
//     filters: Object,
//     roles: Array,
// })
interface Props {
  categories: PaginatedResponse<Category>
  filters: Record<string, any>
//   roles: string[]
}

const props = defineProps<Props>()
const search = ref(props.filters?.search || '')
const role = ref(props.filters?.role)
const loading = ref(false)
const onSearch = async () => {
    loading.value = true
    router.get(route('categories.index'), {
        search: search.value || '',
        role: role.value,

    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            loading.value = false;
            toast('Data retrieved successfully.', {
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
    router.get(route('categories.index'), {
        page,
        search: search.value || '',
    }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => loading.value = false
    })
}
const breadcrumbs = [
    { title: 'Category', href: '/categories' }
];
const isSheetOpen = ref(false);
const sheetCloseBtn = async () => {
    isSheetOpen.value = false;
}
const category = ref<Category | null>(null);
watch(() => isSheetOpen.value, (isSheetOpen) => {
    if (!isSheetOpen) {
        category.value = null;
    }
});
const updateCategory = (cat: Category)  => {
    category.value = cat;
    console.log(cat.name)
    isSheetOpen.value = true;
}
const toggleActive = (val: boolean, category: Category) => {
  category.is_active = val;
  router.put(route('category.toggle', category.id), { is_active: val });
};
</script>

<template>

    <Head title="Category" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <div class="flex justify-between">
                <Heading title="Category" description="Manage and add new categories to your food items." />
                <Sheet v-model:open="isSheetOpen">
                    <Button @click="isSheetOpen = true" :variant="'outline'" :tabindex="0" class="w-full md:w-32">
                        Add New
                    </Button>
                    <Create :category="category!" :open="isSheetOpen" @close="sheetCloseBtn" />
                </Sheet>
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
                <div>
                    <Card class="shadow-none my-4 bg-slate-50 dark:bg-slate-900">
                        <Table class="w-full">
                            <TableHeader class="bg-slate-100 dark:bg-slate-800">
                                <TableRow>
                                    <TableHead class="font-bold text-black dark:text-white">Name</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Email</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Role</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Active</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Edit</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="bg-white dark:bg-slate-950">
                                <TableRow v-for="category in categories?.data" :key="category.id">
                                    <TableCell class="text-black dark:text-gray-200">{{ category.name }}</TableCell>
                                    <TableCell class="text-black dark:text-gray-200">{{ category.slug }}</TableCell>
                                    <TableCell class="capitalize text-black dark:text-gray-200">
                                        <Avatar class="h-8 w-8  rounded-sm">
                                            <AvatarImage :src="category?.icon_url ?? ''" :alt="category.icon_url" />
                                        </Avatar>
                                    </TableCell>
                                    <TableCell class="capitalize text-black dark:text-gray-200">
                                       <Switch v-model="category.is_active" @update:modelValue="(val) => toggleActive(val, category)"/>
                                    </TableCell>
                                    <TableCell class="capitalize text-black dark:text-gray-200">
                                        <Button @click="updateCategory(category as Category)" :variant="'outline'" :tabindex="0"
                                            class="w-full md:w-32">
                                            Add New
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>

                        <div v-if="categories?.data?.length === 0"
                            class="flex flex-col items-center justify-center py-10 text-gray-500 dark:text-gray-400">
                            <ArchiveX :size="60" />
                            <p>No category found</p>
                        </div>
                    </Card>

                </div>
            </CardContent>
            <Pagination v-if="categories?.data?.length != 0" :items-per-page="categories?.per_page"
                :total="categories?.total" :default-page="categories?.current_page">
                <PaginationContent v-slot="{ items }">
                    <PaginationPrevious v-if="categories?.prev_page_url"
                        @click="goToPage(categories.current_page - 1)" />
                    <template v-for="(item, index) in items" :key="index">
                        <PaginationItem v-if="item.type === 'page'" :value="item.value"
                            :is-active="item.value === categories?.current_page" @click="goToPage(item.value)">
                            {{ item.value }}
                        </PaginationItem>
                    </template>
                    <PaginationEllipsis v-if="categories?.last_page > 5" :index="4" />
                    <PaginationNext v-if="categories?.next_page_url" @click="goToPage(categories?.current_page + 1)" />
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
