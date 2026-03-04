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

interface Props {
    classes: SelectOption[];
}
const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Subjects', href: '/subjects' },
    { title: 'Create Subject', href: '/subject/create' },
];

const form = useForm({
    class_id: '',
    name: '',
    code: '',
});

const submit = () => {
    form.post(route('subject.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create Subject" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6 space-y-6">
            <Card class="mx-auto shadow-none rounded-2xl">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 px-6">
                    <div class="md:col-span-4">
                        <CardHeader class="p-0">
                            <Heading title="Create Subject"
                                description="Add a subject and map it to a class for student selection." />
                        </CardHeader>
                    </div>
                    <div class="md:col-span-8">
                        <CardContent class="p-0">
                            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label>Class</Label>
                                    <Select v-model="form.class_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select Class" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem v-for="c in props.classes" :key="c.value" :value="c.value">
                                                    {{ c.label }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.class_id" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="name">Subject Name</Label>
                                    <Input v-model="form.name" id="name" type="text" placeholder="Enter subject name" />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div class="grid gap-2 col-span-full md:col-span-1">
                                    <Label for="code">Code (Optional)</Label>
                                    <Input v-model="form.code" id="code" type="text" placeholder="Ex: MATH-101" />
                                    <InputError :message="form.errors.code" />
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
