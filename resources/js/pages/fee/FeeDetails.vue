<script setup lang="ts">
import SheetContent from '@/components/ui/sheet/SheetContent.vue';
import SheetHeader from '@/components/ui/sheet/SheetHeader.vue';
import SheetTitle from '@/components/ui/sheet/SheetTitle.vue';
import { nextTick, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Check, LoaderCircle } from 'lucide-vue-next';
import { Utils } from '@/lib/utils';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import SheetClose from '@/components/ui/sheet/SheetClose.vue';
import { Order } from '@/types/Order';
import Stepper from '@/components/ui/stepper/Stepper.vue';
import StepperItem from '@/components/ui/stepper/StepperItem.vue';
import StepperSeparator from '@/components/ui/stepper/StepperSeparator.vue';
import StepperTitle from '@/components/ui/stepper/StepperTitle.vue';
import StepperDescription from '@/components/ui/stepper/StepperDescription.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import { OrderStatus } from '@/types/enums';
import { FeeStructure } from '@/types/FeeStructure';

const currentNameInput = ref<HTMLInputElement | null>(null);
const emit = defineEmits(['close']);
const props = defineProps<{
    open: boolean;
    feeStructure?: FeeStructure | null;
}>();
// const progressItemsLength = props.feeStructure?.progresses?.length || 0;
const isLoading = ref(false);
watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            // form.reset();
        } else {
            // form.clearErrors();
        }
    },
    { immediate: true }
);
// const actionBtnPressed = (isApprove: boolean) => {
    // const url = isApprove
    //     ? route('order.action.approved', props.order)
    //     : route('order.action.reject', props.order);
    // if (isApprove) {
    //     isLoading.value = true;
    // } else {
    //     isRejecting.value = true;
    // }
    // form.submit('post', url, {
    //     preserveScroll: true,
    //     // forceFormData: true,
    //     onSuccess: () => {
    //         isLoading.value = false;
    //         isRejecting.value = false;
    //         form.reset();
    //         emit('close');
    //     },
    //     onFinish: () => {
    //         isLoading.value = false;
    //         isRejecting.value = false;
    //         // emit('close');
    //     },
    //     onError: (errors: any) => {
    //         if (errors.name) {
    //             nextTick(() => {
    //                 if (currentNameInput.value instanceof HTMLInputElement) {
    //                     currentNameInput.value?.focus?.();
    //                 }
    //             });
    //         }
    //     },
    // });
// };
</script>

<template>
   <SheetContent class="w-[100vw] sm:w-[100vw] md:w-[80vw] lg:max-w-[60vw]">
        <SheetHeader>
            <SheetTitle>Fee Details</SheetTitle>
            <!-- <SheetDescription>
                Action
            </SheetDescription> -->
        </SheetHeader>
        <ScrollArea class="h-full overflow-auto">
           <div>
             {{ props.feeStructure?.name }}
           </div>
        </ScrollArea>
    </SheetContent>
</template>