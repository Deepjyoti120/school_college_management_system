<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import Heading from '@/components/Heading.vue';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import Select from '@/components/ui/select/Select.vue';
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue';
import SelectValue from '@/components/ui/select/SelectValue.vue';
import SelectContent from '@/components/ui/select/SelectContent.vue';
import SelectGroup from '@/components/ui/select/SelectGroup.vue';
import SelectItem from '@/components/ui/select/SelectItem.vue';
import SelectLabel from '@/components/ui/select/SelectLabel.vue';
import InputError from '@/components/InputError.vue';

const breadcrumbs = [
    { title: 'Users', href: '/users' },
    { title: 'Create user', href: '/users/create' },
];
const props = defineProps({
    roles: Array,
})
const form = useForm({
    name: '',
    email: '',
    dob: '',
    doj: '',
    password: '',
    password_confirmation: '',
    role: '',
});

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
<template>

    <Head title="Create user" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6 space-y-6">
            <Card class="mx-auto shadow-none rounded-2xl ">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 px-6">
                    <div class="md:col-span-4">
                        <CardHeader class="p-0">
                            <Heading title="Create user" description="Create a new user" />
                        </CardHeader>
                    </div>
                    <div class="md:col-span-8">
                        <CardContent class="p-0">
                            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="grid gap-2 col-span-full md:col-span-1">
                                    <Label for="name">Name</Label>
                                    <Input id="name" v-model="form.name"  autofocus placeholder="Enter name"
                                        autocomplete="name" />
                                    <InputError :message="form.errors.name" />
                                </div>
                                <div class="grid gap-2 col-span-full md:col-span-1">
                                    <Label for="email">Email</Label>
                                    <Input id="email" type="email" v-model="form.email"
                                        placeholder="Enter email" autocomplete="email" />
                                    <InputError :message="form.errors.email" />
                                </div>
                                <div class="grid gap-2 col-span-full md:col-span-1">
                                    <Label for="dob">Date of Birth</Label>
                                    <Input id="dob" type="date" v-model="form.dob"  />
                                    <InputError :message="form.errors.dob" />
                                </div>
                                <div class="grid gap-2 col-span-full md:col-span-1">
                                    <Label for="doj">Date of Joining</Label>
                                    <Input id="doj" type="date" v-model="form.doj"  />
                                    <InputError :message="form.errors.doj" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="role">Role</Label>
                                    <Select v-model="form.role">
                                        <SelectTrigger  class="w-full">
                                            <SelectValue placeholder="Select Role" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem v-for="r in props.roles" :key="r.value" :value="r.value">
                                                    {{ r.label }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.role" />
                                </div>
                                <div class="grid gap-2 col-span-full md:col-span-1">
                                    <Label for="password">Password</Label>
                                    <Input id="password" type="password" v-model="form.password"
                                        autocomplete="new-password" placeholder="Password" />
                                    <InputError :message="form.errors.password" />
                                </div>
                                <div class="grid gap-2 col-span-full md:col-span-1">
                                    <Label for="password_confirmation">Confirm Password</Label>
                                    <Input id="password_confirmation" type="password"
                                        v-model="form.password_confirmation"  autocomplete="new-password"
                                        placeholder="Confirm password" />
                                    <!-- <InputError :message="form.errors.password_confirmation" /> -->
                                     <InputError :message="form.errors.password_confirmation || form.errors.password" />
                                </div>
                                <div class="col-span-full pt-4 flex justify-end">
                                    <Button type="submit" class="w-full md:w-auto" :disabled="form.processing">
                                        <LoaderCircle v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                                        Save
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
