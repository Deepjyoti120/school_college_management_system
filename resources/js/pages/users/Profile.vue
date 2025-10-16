<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Heading from '@/components/Heading.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Avatar from '@/components/ui/avatar/Avatar.vue';
import AvatarFallback from '@/components/ui/avatar/AvatarFallback.vue';
import { getInitials } from '@/composables/useInitials';
import { User } from '@/types/User';
import UserPaymants from './UserPaymants.vue';
import { SelectOption } from '@/types/SelectOption';

interface Props {
    user: User;
    feeTypes: SelectOption[],
    paymentStatuses: SelectOption[],
    academicYears: SelectOption[],
}
const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Users', href: '/users' },
    { title: 'Profile', href: `/user/${props.user.id}/profile` },
];
</script>

<template>

    <Head :title="`${props.user.name} | Profile`" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="px-4 py-6 space-y-6">
            <Heading title="Student Profile"
                description="View full details about the student, including class, section, and school information." />
            <!-- Profile Card -->
            <CardContent>
                <Card class="max-full mx-auto shadow-none rounded-2xl">
                    <CardContent class="p-6">
                        <div class="flex flex-col md:flex-row gap-6">
                            <!-- Avatar -->
                            <div class="flex flex-col items-center md:w-1/4">
                                <Avatar class="h-24 w-24 rounded-lg overflow-hidden bg-amber-300">
                                    <AvatarFallback class="rounded-lg text-xl text-black dark:text-white">
                                        {{ getInitials(props.user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="mt-3 text-center">
                                    <Badge :class="props.user.role_color" class="capitalize">
                                        {{ props.user.role_label }}
                                    </Badge>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="flex-1 space-y-3">
                                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ props.user.name }}
                                </h2>
                                <p class="text-gray-600 dark:text-gray-400">{{ props.user.email }}</p>
                                <p class="text-gray-600 dark:text-gray-400">
                                    📞 {{ props.user.country_code }} {{ props.user.phone }}
                                </p>

                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 text-gray-700 dark:text-gray-300">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Date of Birth</p>
                                        <p>{{ props.user.dob_formatted || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Date of Joining</p>
                                        <p>{{ props.user.doj_formatted || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">School</p>
                                        <p>{{ props.user.school?.name || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Class</p>
                                        <p>{{ props.user.class?.name || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Section</p>
                                        <p>{{ props.user.section?.name || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Roll Number</p>
                                        <p>{{ props.user.roll_number || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Account Status</p>
                                        <Badge :variant="props.user.is_active ? 'success' : 'destructive'">
                                            {{ props.user.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </CardContent>
                <UserPaymants :user="user" :academic-years="academicYears" :fee-types="feeTypes" :payment-statuses="paymentStatuses" ></UserPaymants>
        </div>
    </AppLayout>
</template>
