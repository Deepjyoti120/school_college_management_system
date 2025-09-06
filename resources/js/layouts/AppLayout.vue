<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { AppPageProps, BreadcrumbItemType } from '@/types';
import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css'
import { usePage } from '@inertiajs/vue3';
import { h, watchEffect } from 'vue';
import { toast } from 'vue-sonner';
import { CircleAlert, CircleCheck, CircleCheckBigIcon, Home } from 'lucide-vue-next';
interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
const page = usePage<AppPageProps>();

watchEffect(() => {
    const flash = page.props.flash;
    if (flash?.success) {
        toast.success(flash.success, {
            description: '',
            action: {
                label: 'Undo',
                onClick: () => console.log('Undo'),
            },
            actionButtonStyle: {
                backgroundColor: '#10B981',
                color: 'white',
                border: 'none',
            },
            icon: h(CircleCheckBigIcon, { class: "w-5 h-5 text-green-500" })
        });
    }
    if (flash?.error) {
        toast.error(flash.error, {
            description: '',
            action: {
                label: 'Undo',
                onClick: () => console.log('Undo'),
            },
            actionButtonStyle: {
                backgroundColor: '#EF4444',
                color: 'white',
                border: 'none',
            },
            icon: h(CircleAlert, { class: "w-5 h-5 text-red-500 " })
        });
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Toaster position="top-center" />
        <slot />
    </AppLayout>
</template>
