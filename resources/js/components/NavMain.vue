<script setup lang="ts">
import { ChevronRight, type LucideIcon } from 'lucide-vue-next'
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible'
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar'
import { Link, usePage } from '@inertiajs/vue3';
defineProps<{
    items: {
        title: string
        items?: {
        title: string
        url: string
        icon?: LucideIcon
        isActive?: boolean
        items?: {
            title: string
            url: string
        }[]
    }[]
    }[]
}>()
const page = usePage();
</script>

<template>
    <SidebarGroup v-for="title in items" :key="title.title">
        <SidebarGroupLabel>{{ title.title }}</SidebarGroupLabel>
        <SidebarMenu>
            <Collapsible v-for="item in title.items" :key="item.title" as-child
                :default-open="item.items?.some(sub => page.url.includes(sub.url))" class="group/collapsible">
                <SidebarMenuItem>
                    <template v-if="item.items?.length">
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton :tooltip="item.title" as-child :is-active="page.url.includes(item.url)">
                                <div class="flex items-center w-full">
                                    <component :is="item.icon" v-if="item.icon" />
                                    <span>{{ item.title }}</span>
                                    <ChevronRight
                                        class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                                </div>
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                    </template>
                    <template v-else>
                        <SidebarMenuButton :tooltip="item.title" as-child :is-active="page.url === item.url">
                            <Link :href="item.url" class="flex items-center w-full">
                            <component :is="item.icon" v-if="item.icon" />
                            <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </template>
                    <CollapsibleContent>
                        <SidebarMenuSub>
                            <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                                <SidebarMenuSubButton as-child :is-active="page.url.includes(subItem.url)">
                                    <Link :href="subItem.url"> <span>{{ subItem.title }}</span></Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </SidebarMenuItem>
            </Collapsible>
        </SidebarMenu>
    </SidebarGroup>
</template>
