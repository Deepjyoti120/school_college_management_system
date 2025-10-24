<script setup lang="ts">
import SheetContent from '@/components/ui/sheet/SheetContent.vue';
import SheetHeader from '@/components/ui/sheet/SheetHeader.vue';
import SheetTitle from '@/components/ui/sheet/SheetTitle.vue';
import SheetDescription from '@/components/ui/sheet/SheetDescription.vue';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import { User } from '@/types/User';
import { FeeStructure } from '@/types/FeeStructure';
import { LoaderCircle } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import SheetClose from '@/components/ui/sheet/SheetClose.vue';
import Button from '@/components/ui/button/Button.vue';
import InputError from '@/components/InputError.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import { nextTick, ref, watch } from 'vue';
import { Discount } from '@/types/Discount';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';

const currentNameInput = ref<HTMLInputElement | null>(null);
const emit = defineEmits(['close']);
const props = defineProps<{
    feeStructure?: FeeStructure | null;
    user?: User | null;
    open: boolean;
}>();

const discount = ref<Discount | null>(null);
watch(
    () => props.open,
    (newVal) => {
        if (newVal) {
            if (props.feeStructure && props.user) {
                fetchDiscount();
            }
        }
    }, { immediate: true }
);
const fetchDiscount = async () => {
    try {
        const url = route('fees.get.custom.amount', [props.feeStructure?.id, props.user?.id]);
        const res = await fetch(url);
        const data = await res.json();
        console.log('Fetched discount data:', data);
        discount.value = data.data as Discount;
        form.amount = discount.value?.amount ? Math.floor(discount.value?.amount) : 0;
    } catch (err) {
        console.error('Failed to fetch discount:', err);
    }
};

const form = useForm({
    amount: 0,
});

const actionBtnPressed = () => {
    const url = route('fees.custom.amount', [props.feeStructure?.id, props.user?.id]);
    // isLoading.value = true;
    form.submit('post', url, {
        preserveScroll: true,
        // forceFormData: true,
        onSuccess: () => {
            // isLoading.value = false;
            // isRejecting.value = false;
            // form.reset();
            discount.value = null;
            emit('close');
        },
        onFinish: () => {
            // isLoading.value = false;
            // isRejecting.value = false;
            // emit('close');
        },
        onError: (errors: any) => {
            if (errors.name) {
                fetchDiscount();
                nextTick(() => {
                    if (currentNameInput.value instanceof HTMLInputElement) {
                        currentNameInput.value?.focus?.();
                    }
                });
            }
        },
    });
};
</script>

<template>
    <SheetContent class="">
        <SheetHeader>
            <SheetTitle>Custom Discount</SheetTitle>
            <SheetDescription></SheetDescription>
        </SheetHeader>
        <ScrollArea class="h-full overflow-auto">
            <div class="p-4 space-y-4">
                <Card>
                    <CardContent class="p-4 space-y-2">
                        <h3 class="font-semibold text-base text-gray-900">Student Details</h3>
                        <div class="text-sm text-gray-600 grid grid-cols-2 gap-x-4 gap-y-1">
                            <p><span class="font-medium ">Name:</span> {{ props.user?.name }}</p>
                            <p><span class="font-medium ">Class:</span> {{ props.user?.class?.name }}</p>
                            <p><span class="font-medium ">Section:</span> {{ props.user?.section?.name }}
                            </p>
                            <p><span class="font-medium ">Roll No:</span> {{ props.user?.roll_number }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4 space-y-2">
                        <h3 class="font-semibold text-base text-gray-900">Fee Details</h3>
                        <div class="text-sm text-gray-600 grid grid-cols-2 gap-x-4 gap-y-1">
                            <p><span class="font-medium ">Fee Name:</span> {{ props.feeStructure?.name }}
                            </p>
                            <p><span class="font-medium ">Month:</span> {{ props.feeStructure?.month_name
                                }}</p>
                            <p><span class="font-medium ">Total:</span>
                                {{ user?.payment ? ' ₹ ' + user?.payment?.total_amount : '₹ ' +
                                    feeStructure?.total_amount }} </p>
                            <p><span class="font-medium ">Pending:</span>
                                {{ '₹' + (Number(user?.payment ? + user?.payment?.total_amount :
                                    feeStructure?.total_amount) - Number(user?.discount_amount ?? 0))
                                }}</p>
                        </div>
                    </CardContent>
                </Card>
                <form @submit.prevent class="space-y-6 mt-8">
                    <div class="grid gap-2">
                        <div class="grid gap-2">
                            <Label for="amount">Discount Amount</Label>
                            <Input id="amount" ref="currentNameInput" v-bind="$attrs" v-model="form.amount" type="text"
                                class="mt-1 block w-full" placeholder="Driver name" />
                            <InputError :message="form.errors.amount" />
                        </div>
                    </div>
                    <div v-if="discount" class="mt-8 flex justify-end">
                        <SheetClose ref="closeButton" class="hidden" />
                        <Button @click="actionBtnPressed()" :variant="'destructive'" type="submit"
                            :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            <span>Save</span>
                        </Button>
                    </div>
                </form>
            </div>
        </ScrollArea>
    </SheetContent>
</template>
