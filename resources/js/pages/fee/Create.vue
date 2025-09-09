<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
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
import { Product } from '@/types/Product';
import { SelectOption } from '@/types/SelectOption';
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput,
} from '@/components/ui/number-field'
import FilePondUploader from '@/components/FilePondUploader.vue';

const breadcrumbs = [
    { title: 'Orders', href: '/orders' },
    { title: 'Create Order', href: '/order/create' },
];
interface Props {
    roles: SelectOption[];
    products: Product[];
}
const props = defineProps<Props>();

const form = useForm({
    product_id: '',
    quantity: 1,
    documents: [] as File[],
    total_price: 0,
});
const product = ref<Product | null>(null);
const submit = () => {
    form.post(route('order.store'), {
        forceFormData: true,
        // onSuccess: () => history.back(),
    });
};
watch([product, () => form.quantity], ([newProduct, newQty], [oldProduct, oldQty]) => {
    if (newQty < 1) {
        form.quantity = 1;
        return;
    }
    updateProductPrice();
});
const updateProductPrice = () => {
    form.product_id = product.value?.id ?? '';
    form.total_price = ((product.value?.price ?? 0) * form.quantity)
}
const pickFiles = (files: File[]) => {
    form.documents = files;
}

</script>
<template>

    <Head title="Create order" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6 space-y-6">
            <Card class="mx-auto shadow-none rounded-2xl ">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 px-6">
                    <div class="md:col-span-4">
                        <CardHeader class="p-0">
                            <Heading title="Create order" description="Create a new order" />
                        </CardHeader>
                    </div>
                    <div class="md:col-span-8">
                        <CardContent class="p-0">
                            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label for="product">Select Product</Label>
                                    <Select v-model="product">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Choose a product" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem v-for="product in props.products" :key="product.id"
                                                    :value="product">
                                                    {{ product.name }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.product_id" />
                                </div>
                                <div class="grid gap-2">
                                    <NumberField v-model="form.quantity">
                                        <Label>Quantity</Label>
                                        <NumberFieldContent>
                                            <NumberFieldDecrement />
                                            <NumberFieldInput />
                                            <NumberFieldIncrement />
                                        </NumberFieldContent>
                                        <InputError :message="form.errors.quantity" />
                                    </NumberField>
                                </div>
                                <div class="grid gap-2 col-span-2">
                                    <Label for="documents">Documents</Label>
                                    <FilePondUploader :allow-multiple="true"
                                        accepted-file-types="application/pdf,image/jpeg,image/jpg,image/png"
                                        @update:files="files => pickFiles(files)" />
                                    <InputError :message="form.errors.documents" />
                                </div>
                                <div v-if="product" class="col-span-full">
                                    <div
                                        class="p-4 border rounded-2xl bg-slate-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                                        <div class="space-y-1">
                                            <h3 class="text-lg font-semibold">{{ product.name }}</h3>
                                            <p class="text-sm text-muted-foreground">SKU: {{ product.sku }}</p>
                                            <!-- <p class="text-sm text-muted-foreground">Stock Available: {{ product.stock
                                            }}</p> -->
                                        </div>
                                        <div class="text-right space-y-1 md:min-w-[200px]">
                                            <div class="text-sm text-muted-foreground">Price per unit:</div>
                                            <div class="text-base font-medium text-foreground">₹{{
                                                product.price }}</div>

                                            <div class="text-sm text-muted-foreground mt-2">Quantity:</div>
                                            <div class="text-base font-medium text-foreground">{{ form.quantity }}</div>

                                            <div class="border-t mt-3 pt-3">
                                                <div class="text-sm text-muted-foreground">Total Price:</div>
                                                <div class="text-xl font-bold text-primary">₹{{
                                                    form.total_price.toFixed(2) }}</div>
                                            </div>
                                        </div>
                                    </div>
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
