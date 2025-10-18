<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
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
import InputError from '@/components/InputError.vue';
import { SelectOption } from '@/types/SelectOption';
import { onMounted, ref, watch } from 'vue';

interface Props {
    roles: SelectOption[],
    boards: SelectOption[],
    classes: SelectOption[],
    user: Record<string, any> | null,
}
const props = defineProps<Props>()

const form = useForm({
    id: props.user?.id || null,
    name: props.user?.name || '',
    email: props.user?.email || '',
    dob: props.user?.dob || '',
    doj: props.user?.doj || '',
    password: '',
    password_confirmation: '',
    role: props.user?.role || '',
    board: props.user?.board || '',
    class_id: props.user?.class_id || '',
    section_id: props.user?.section_id || '',
    phone: props.user?.phone || '',
    roll_number: props.user?.roll_number || '',
});

const sections = ref<SelectOption[]>([]);
onMounted(async () => {
    if (form.class_id) {
        const res = await fetch(route('class.sections', { class_id: form.class_id }));
        sections.value = await res.json();
        if (!sections.value.find(s => s.value === form.section_id)) {
            form.section_id = '';
        }
    }
});

watch(() => form.class_id, async (newVal) => {
    form.section_id = '';
    sections.value = [];
    if (newVal) {
        const res = await fetch(route('class.sections', { class_id: newVal }));
        sections.value = await res.json();
    }
});

const submit = () => {
    form.post(route('user.store'), {
        onSuccess: () => form.reset(),
    });
};

const breadcrumbs = [
    { title: 'Users', href: '/users' },
    { title: props.user ? 'Edit User' : 'Create User', href: props.user ? `/users/create/${props.user.id}` : '/users/create' },
];
</script>
<template>

    <Head :title="props.user ? 'Edit User' : 'Create User'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6 space-y-6">
            <Card class="mx-auto shadow-none rounded-2xl ">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 px-6">
                    <div class="md:col-span-4">
                        <CardHeader class="p-0">
                            <Heading :title="props.user ? 'Edit User' : 'Create User'"
                                :description="props.user ? 'Update existing user details' : 'Create a new user'" />

                        </CardHeader>
                    </div>
                    <div class="md:col-span-8">
                        <CardContent class="p-0">
                            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="grid gap-2 col-span-full md:col-span-1">
                                    <Label for="name">Name</Label>
                                    <Input id="name" v-model="form.name" autofocus placeholder="Enter name"
                                        autocomplete="name" />
                                    <InputError :message="form.errors.name" />
                                </div>
                                <div class="grid gap-2 col-span-full md:col-span-1">
                                    <Label for="email">Email</Label>
                                    <Input id="email" type="email" v-model="form.email" placeholder="Enter email"
                                        autocomplete="email" />
                                    <InputError :message="form.errors.email" />
                                </div>
                                <div class="grid gap-2 col-span-full md:col-span-1">
                                    <Label for="dob">Date of Birth</Label>
                                    <Input id="dob" type="date" v-model="form.dob" />
                                    <InputError :message="form.errors.dob" />
                                </div>
                                <div class="grid gap-2 col-span-full md:col-span-1">
                                    <Label for="doj">Date of Joining</Label>
                                    <Input id="doj" type="date" v-model="form.doj" />
                                    <InputError :message="form.errors.doj" />
                                </div>
                                <div class="grid gap-2 ">
                                    <Label for="role">Class Room</Label>
                                    <Select v-model="form.class_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select Class Room" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem v-for="r in props.classes" :key="r.value" :value="r.value">
                                                    {{ r.label }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.class_id" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="role">Class Section</Label>
                                    <Select v-model="form.section_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select Class Room Section" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem v-for="r in sections" :key="r.value" :value="r.value">
                                                    {{ r.label }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.section_id" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="role">Role</Label>
                                    <Select v-model="form.role">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Role" />
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
                                <div class="grid gap-2">
                                    <Label for="board">Board</Label>
                                    <Select v-model="form.board">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Board" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem v-for="r in props.boards" :key="r.value" :value="r.value">
                                                    {{ r.label }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.role" />
                                </div>

                                <div class="grid gap-2 col-span-full md:col-span-1">
                                    <Label for="phone">Phone</Label>
                                    <Input @input="form.phone = form.phone?.toString().slice(0, 10)" id="phone"
                                        type="number" v-model="form.phone" maxlength="10" />
                                    <InputError :message="form.errors.phone" />
                                </div>
                                <div class="grid gap-2 col-span-full md:col-span-1">
                                    <Label for="roll_number">Rool Number</Label>
                                    <Input id="roll_number" type="text" v-model="form.roll_number" />
                                    <InputError :message="form.errors.roll_number" />
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
                                        v-model="form.password_confirmation" autocomplete="new-password"
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
