<script setup lang="ts">
import type { SidebarProps } from '@/components/ui/sidebar'

import {
    AudioWaveform,
    BaggageClaim,
    BaggageClaimIcon,
    BookOpen,
    Bot,
    Car,
    Command,
    Frame,
    GalleryVerticalEnd,
    Map,
    PieChart,
    Settings,
    Settings2,
    SquareTerminal,
    Users2Icon,
} from 'lucide-vue-next'
import NavMain from '@/components/NavMain.vue'
import NavProjects from '@/components/NavProjects.vue'
import NavUser from '@/components/NavUser.vue'
import TeamSwitcher from '@/components/TeamSwitcher.vue'

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarRail,
} from '@/components/ui/sidebar'
import { usePage } from '@inertiajs/vue3'
import BrandCard from './BrandCard.vue'
import { UserRole } from '@/types/enums'

const props = withDefaults(defineProps<SidebarProps>(), {
    collapsible: 'icon',
})

const page = usePage()
const user = page.props.auth.user;
const appName = page.props.name;
const data = {
    user: {
        name: 'shadcn',
        email: 'm@example.com',
        avatar: '/avatars/shadcn.jpg',
    },
    brand: {
        name: appName,
        logo: GalleryVerticalEnd,
    },
    teams: [
        {
            name: appName,
            logo: GalleryVerticalEnd,
            plan: 'Enterprise',
        },
        {
            name: 'Acme Corp.',
            logo: AudioWaveform,
            plan: 'Startup',
        },
        {
            name: 'Evil Corp.',
            logo: Command,
            plan: 'Free',
        },
    ],
    navMain: [
        {
            title: "Platform",
            items: [
                {
                    title: 'Dashboard',
                    url: '/dashboard',
                    icon: SquareTerminal,
                    isActive: false,
                },
                {
                    title: 'Products',
                    url: '/products',
                    icon: BaggageClaimIcon,
                    isActive: false,
                },
                {
                    title: 'Orders',
                    url: '/orders',
                    icon: Car,
                    isActive: false,
                },
            ],
        },
    ],
    projects: [
        {
            name: 'Design Engineering',
            url: '#',
            icon: Frame,
        },
        {
            name: 'Sales & Marketing',
            url: '#',
            icon: PieChart,
        },
        {
            name: 'Travel',
            url: '#',
            icon: Map,
        },
    ],
    admin: [
        {
            title: 'Admin',
            url: '#',
            icon: Settings,
            items: [
                {
                    title: 'Users',
                    url: '/users',
                }
            ],
        },
        {
            title: 'Admin',
            url: '#',
            icon: Settings,
            items: [
                {
                    title: 'Users',
                    url: '/users',
                }
            ],
        },
    ]
}

if (user.role === UserRole.ADMIN) {
    const adminItems = [
        {
            title: "Admin",
            items: [
                {
                    title: 'Users',
                    url: '/users',
                    icon: Users2Icon,
                    isActive: false,
                },
            ],
        },
    ]
    data.navMain.push(...adminItems);
}
// if (user.role === UserRole.GM) {
//     const adminItems = [
//         {
//             title: "Admin",
//             items: [
//                 {
//                     title: 'Users',
//                     url: '/users',
//                     icon: Users2Icon,
//                     isActive: false,
//                 },
//             ],
//         },
//     ]
//     data.navMain.push(...adminItems);
// }
const settings =
{
    title: "Settings",
    items: [
        {
            title: 'Settings',
            url: '/settings',
            icon: Settings2,
            isActive: false,
        },
    ],
};
data.navMain.push(settings);
</script>

<template>
    <Sidebar v-bind="props">
        <SidebarHeader>
            <BrandCard :brand="data.brand" />
            <!-- <TeamSwitcher :teams="data.teams" /> -->
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="data.navMain" />
            <!-- <NavProjects :projects="data.projects" /> -->
        </SidebarContent>
        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
        <SidebarRail />
    </Sidebar>
</template>
