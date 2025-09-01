<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css'
import { usePage } from '@inertiajs/vue3';
import { watchEffect } from 'vue';
import { toast } from 'vue-sonner';
interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
const page = usePage();

watchEffect(() => {
    const flash = page.props.flash;
    if (flash?.success) {
        toast.success(flash.success, {
            description: '',
            action: {
                label: 'Undo',
                onClick: () => console.log('Undo'),
            },
        });
    }
    if (flash?.error) {
        toast.error(flash.error, {
            description: '',
            action: {
                label: 'Undo',
                onClick: () => console.log('Undo'),
            },
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
