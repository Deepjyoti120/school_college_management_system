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
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput,
} from '@/components/ui/number-field'
import { SelectOption } from '@/types/SelectOption';

const breadcrumbs = [
    { title: 'Fees Structure', href: '/fees/structure' },
    { title: 'Create Fees Structure', href: '/fees/structure/create' },
];

interface Props {
    classes: SelectOption[];
    feeTypes: SelectOption[];
    frequencyTypes: SelectOption[];
    months: SelectOption[];
}
const props = defineProps<Props>();

const form = useForm({
    class_id: '',
    name: '',
    type: '',
    amount: 0,
    frequency: '',
    description: '',
    month: null,
});

const submit = () => {
    form.post(route('fee.store'),
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
                                <div class="grid gap-2">
                                    <Label>Class</Label>
                                    <Select v-model="form.class_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Choose Class" />
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

                                <div class="grid gap-2 ">
                                    <Label for="name">Fee Name</Label>
                                    <Input v-model="form.name" id="name" type="text" placeholder="Enter fee name" />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label>Fee Type</Label>
                                    <Select v-model="form.type">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select Fee Type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem v-for="t in props.feeTypes" :key="t.value" :value="t.value">
                                                    {{ t.label }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.type" />
                                </div>

                                <div class="grid gap-2">
                                    <Label>Frequency</Label>
                                    <Select v-model="form.frequency">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select Frequency" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem v-for="f in props.frequencyTypes" :key="f.value"
                                                    :value="f.value">
                                                    {{ f.label }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.frequency" />
                                </div>
                                <div class="grid gap-2">
                                    <Label>Month</Label>
                                    <Select v-model="form.month">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select Month" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem v-for="f in props.months" :key="f.value"
                                                    :value="f.value">
                                                    {{ f.label }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.month" />
                                </div>

                                <div class="grid gap-2">
                                    <NumberField v-model="form.amount">
                                        <Label>Amount</Label>
                                        <NumberFieldContent>
                                            <NumberFieldDecrement />
                                            <NumberFieldInput />
                                            <NumberFieldIncrement />
                                        </NumberFieldContent>
                                    </NumberField>
                                    <InputError :message="form.errors.amount" />
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
