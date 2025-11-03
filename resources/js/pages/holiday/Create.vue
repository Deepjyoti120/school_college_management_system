<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card'; 
import InputError from '@/components/InputError.vue'; 

const breadcrumbs = [
    { title: 'Holiday', href: '/holidays' },
    { title: 'Create new holiday', href: '/holiday.create' },
];  

const form = useForm({
    name: '',
    description: '',
    date: '',
});

const submit = () => {
    form.post(route('holiday.store'),
        {
            onSuccess: () => form.reset(),
        }
    );
};
</script>

<template>

    <Head title="Create Fee Structure" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6 space-y-6">
            <Card class="mx-auto shadow-none rounded-2xl">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 px-6">
                    <div class="md:col-span-4">
                        <CardHeader class="p-0">
                            <Heading title="Create Fee Structure"
                                description="Define fees for classes per academic year" />
                        </CardHeader>
                    </div>
                    <div class="md:col-span-8">
                        <CardContent class="p-0">
                            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="grid gap-2 ">
                                    <Label for="name">Holiday Name</Label>
                                    <Input v-model="form.name" id="name" type="text" placeholder="Enter Holiday Name" />
                                    <InputError :message="form.errors.name" />
                                </div>
                                <div class="grid gap-2 ">
                                    <Label for="name">Holiday Date</Label>
                                    <Input v-model="form.date" id="name" type="date" placeholder="Enter Holiday Name" />
                                    <InputError :message="form.errors.date" />
                                </div>
                                <div class="grid gap-2 col-span-2">
                                    <Label for="description">Description</Label>
                                    <textarea v-model="form.description" id="description" rows="3"
                                        class="border rounded-md p-2 w-full"></textarea>
                                    <InputError :message="form.errors.description" />
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
