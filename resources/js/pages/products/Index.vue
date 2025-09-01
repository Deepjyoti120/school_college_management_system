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
import { ArchiveX, LoaderCircle, Pen } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import SearchInput from '@/components/SearchInput.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { getInitials } from '@/composables/useInitials';
import Avatar from '@/components/ui/avatar/Avatar.vue';
import AvatarImage from '@/components/ui/avatar/AvatarImage.vue';
import AvatarFallback from '@/components/ui/avatar/AvatarFallback.vue';
import Switch from '@/components/ui/switch/Switch.vue';
const props = defineProps({
    products: Object,
    filters: Object,
})
const search = ref(props.filters?.search || '')
const loading = ref(false)
const onSearch = async () => {
    toast('Oops! Search isn’t ready just yet. 🚧');
    // loading.value = true
    // router.get(route('users.index'), {
    //     search: search.value || '',
    //     role: role.value,

    // }, {
    //     preserveState: true,
    //     preserveScroll: true,
    //     onFinish: () => {
    //         loading.value = false;
    //         toast('Search completed successfully.', {
    //             description: '',
    //             action: {
    //                 label: 'Undo',
    //                 onClick: () => console.log('Undo'),
    //             },
    //         })
    //     }
    // })

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
const breadcrumbs = [{ title: 'Products', href: '/products' }];
const toggleActive = (val: boolean, user: any) => {
  user.is_active = val;
  router.put(route('user.toggle', user.id), { is_active: val });
};
</script>

<template>

    <Head title="Products" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
             <div class="flex justify-between">
                <Heading title="Products"
                    description="Manage your Products here. You can view, edit, and delete products as needed" />
                <!-- <Link :href="route('users.create')">
                <Button :variant="'default'" :tabindex="0" class="w-full md:w-32">
                    Add New
                </Button>
                </Link> -->
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
                                    <TableHead class="font-bold text-black dark:text-white">Name</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Description</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Price</TableHead>
                                    <!-- <TableHead class="font-bold text-black dark:text-white">Stock</TableHead> -->
                                    <TableHead class="font-bold text-black dark:text-white">Sku</TableHead>
                                    <TableHead class="font-bold text-black dark:text-white">Is Active</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="bg-white dark:bg-slate-950">
                                <TableRow v-for="product in products?.data" :key="product.id">
                                    <TableCell class="text-black dark:text-gray-200">
                                        {{ product.name }}
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                      {{ product.description }}
                                    </TableCell>
                                    <TableCell class="text-black dark:text-gray-200">
                                      {{ product.price }}
                                    </TableCell>
                                    <!-- <TableCell class="capitalize text-black dark:text-gray-200">
                                       {{ product.stock }}
                                    </TableCell> -->
                                    <TableCell class="capitalize text-black dark:text-gray-200">
                                         {{ product.sku }}
                                    </TableCell>
                                    <TableCell class="capitalize text-black dark:text-gray-200">
                                        <Switch disabled  v-model="product.is_active"/>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>

                        <div v-if="products?.data?.length === 0"
                            class="flex flex-col items-center justify-center py-10 text-gray-500 dark:text-gray-400">
                            <ArchiveX :size="60" />
                            <p>No Products found</p>
                        </div>
                    </Card>

                </div>
            </CardContent>
            <Pagination v-if="products?.data?.length != 0" :items-per-page="products?.per_page" :total="products?.total"
                :default-page="products?.current_page">
                <PaginationContent v-slot="{ items }">
                    <PaginationPrevious v-if="products?.prev_page_url" @click="goToPage(products.current_page - 1)" />
                    <template v-for="(item, index) in items" :key="index">
                        <PaginationItem v-if="item.type === 'page'" :value="item.value"
                            :is-active="item.value === products?.current_page" @click="goToPage(item.value)">
                            {{ item.value }}
                        </PaginationItem>
                    </template>
                    <PaginationEllipsis v-if="products?.last_page > 5" :index="4" />
                    <PaginationNext v-if="products?.next_page_url" @click="goToPage(products?.current_page + 1)" />
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>
</template>
