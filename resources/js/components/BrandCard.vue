<script setup lang="ts">
import { type Component, ref } from 'vue'

import {
  SidebarMenuButton,
} from '@/components/ui/sidebar'
import AppLogoIcon from './AppLogoIcon.vue';

const props = defineProps<{
  brand: {
    name: string
    logo: Component
  }
}>()

const brand = ref(props.brand)
</script>

<template>
 <SidebarMenuButton
            size="lg"
            class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
          >
            <div class="flex aspect-square size-8 items-center justify-center rounded-lg ">
              <!-- <component :is="brand.logo" class="size-4" /> -->
                <AppLogoIcon class="size-9 fill-current text-[var(--foreground)] dark:text-white" />
            </div>
            <div class="grid flex-1 text-left text-sm leading-tight">
              <span class="truncate font-medium">
                {{ brand.name }}
              </span>
              <span class="truncate text-xs">{{ brand.plan }}</span>
            </div>
            <!-- <ChevronsUpDown class="ml-auto" /> -->
          </SidebarMenuButton>
</template>
