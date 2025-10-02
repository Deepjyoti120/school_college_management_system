<script setup lang="ts">
import SheetContent from '@/components/ui/sheet/SheetContent.vue';
import SheetHeader from '@/components/ui/sheet/SheetHeader.vue';
import SheetTitle from '@/components/ui/sheet/SheetTitle.vue';
import SheetDescription from '@/components/ui/sheet/SheetDescription.vue';
import { nextTick, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Check, Circle, Dot, LoaderCircle } from 'lucide-vue-next';
import { Utils } from '@/lib/utils';
import FilePondUploader from '@/components/FilePondUploader.vue';
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
import StepperTrigger from '@/components/ui/stepper/StepperTrigger.vue';
import StepperTitle from '@/components/ui/stepper/StepperTitle.vue';
import StepperDescription from '@/components/ui/stepper/StepperDescription.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import { OrderStatus } from '@/types/enums';

const currentNameInput = ref<HTMLInputElement | null>(null);
const emit = defineEmits(['close']);
const props = defineProps<{
    open: boolean;
    order?: Order | null;
}>();
const progressItemsLength = props.order?.progresses?.length || 0;
const isLoading = ref(false);
const isRejecting = ref(false);
const form = useForm({
    remarks: '',
    vehicle_number: '',
    driver_phone: '',
});
watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.reset();
        } else {
            form.clearErrors();
        }
    },
    { immediate: true }
);
const actionBtnPressed = (isApprove: boolean) => {
    const url = isApprove
        ? route('order.action.approved', props.order)
        : route('order.action.reject', props.order);
    if (isApprove) {
        isLoading.value = true;
    } else {
        isRejecting.value = true;
    }
    form.submit('post', url, {
        preserveScroll: true,
        // forceFormData: true,
        onSuccess: () => {
            isLoading.value = false;
            isRejecting.value = false;
            form.reset();
            emit('close');
        },
        onFinish: () => {
            isLoading.value = false;
            isRejecting.value = false;
            // emit('close');
        },
        onError: (errors: any) => {
            if (errors.name) {
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
    <SheetContent>
        <SheetHeader>
            <SheetTitle> Action</SheetTitle>
            <!-- <SheetDescription>
                Action
            </SheetDescription> -->
        </SheetHeader>
        <ScrollArea class="h-full overflow-auto">
            <div class="px-6">
                <div v-if="order?.product" class="col-span-full">
                    <div
                        class="p-4 border rounded-2xl bg-slate-50 flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 sm:gap-6">
                        <div class="space-y-1 flex-1">
                            <h3 class="text-base sm:text-lg font-semibold break-words">
                                {{ order?.product?.name }}
                            </h3>
                            <p class="text-xs sm:text-sm text-muted-foreground">
                                SKU: {{ order?.product?.sku }}
                            </p>
                        </div>
                        <div class="w-full sm:w-auto text-left sm:text-right space-y-1">
                            <div>
                                <div class="text-xs sm:text-sm text-muted-foreground">Price per unit:</div>
                                <div class="text-sm sm:text-base font-medium text-foreground">
                                    ₹{{ order?.product?.price }}
                                </div>
                            </div>

                            <div class="mt-2">
                                <div class="text-xs sm:text-sm text-muted-foreground">Quantity:</div>
                                <div class="text-sm sm:text-base font-medium text-foreground">
                                    {{ order?.quantity }} {{ Utils.pluralize(order?.quantity, 'bag') }}
                                </div>
                            </div>

                            <div class="border-t mt-3 pt-3">
                                <div class="text-xs sm:text-sm text-muted-foreground">Total Price:</div>
                                <div class="text-lg sm:text-xl font-bold text-primary">
                                    ₹{{ order?.total_price }}
                                </div>
                            </div>
                        </div>
                    </div> 
                    <div v-if="order?.documents?.length" class="mt-4 p-4 border rounded-2xl bg-white space-y-3">
                        <h4 class="text-sm font-semibold">Documents</h4>
                        <div v-for="doc in order.documents" :key="doc.id" class="flex items-center gap-3">
                            <component :is="Utils.getFileIcon(doc.mime_type)" class="w-5 h-5 text-primary flex-shrink-0" />
                            <div class="flex-1 min-w-0">
                                <a :href="doc.file_path_url || '#'" target="_blank" rel="noopener noreferrer"
                                    class="text-sm font-medium text-blue-600 hover:underline truncate">
                                    {{ doc.original_name }}
                                </a>
                                <div class="text-xs text-muted-foreground">
                                    {{ doc.mime_type }} • {{ Utils.formatFileSize(doc.size) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <Stepper orientation="vertical" class="mx-auto flex w-full max-w-md flex-col justify-start gap-10 mt-8">
                    <StepperItem v-for="(progress, index) in props.order?.progresses" :key="progress.id ?? index"
                        class="relative flex w-full items-start gap-6" :step="progressItemsLength">
                        {{}}
                        <StepperSeparator
                            v-if="props.order?.progresses?.[props.order.progresses.length - 1].id !== progress.id"
                            class="absolute left-[18px] top-[38px] block h-[105%] w-0.5 shrink-0 rounded-full bg-muted group-data-[true]:bg-primary" />
                        <!-- <StepperTrigger as-child> -->
                        <Button :variant="'default'" size="icon" class="z-10 rounded-full shrink-0"
                            :class="'ring-2 ring-ring ring-offset-2 ring-offset-background'">
                            <Check class="size-5" />
                            <!-- <Circle /> -->
                            <!-- <Dot v-if="index > progressItemsLength" /> -->
                        </Button>
                        <!-- </StepperTrigger> -->
                        <div class="flex flex-col gap-1">
                            <StepperTitle :class="['text-primary']"
                                class="text-sm font-semibold transition lg:text-base">
                                <div>
                                    {{ progress.title }} by
                                    <Badge :variant="progress?.updater?.role_color"
                                        :class="progress?.updater?.role_color">
                                        {{ progress?.updater?.role_label }}
                                    </Badge>
                                </div>
                            </StepperTitle>
                            <StepperDescription :class="['text-primary']"
                                class="sr-only text-xs text-muted-foreground transition md:not-sr-only lg:text-sm">
                                Status:
                                <Badge :variant="progress?.status_color" :class="progress?.status_color">
                                    {{ progress?.status_label }}
                                </Badge>
                                <br>
                                {{ progress?.created_at_formatted }}

                                <p v-if="progress.remarks" class="mt-2 text-slate-600 font-semibold">
                                    Remarks: <span class="font-normal">{{ progress.remarks }}</span>
                                </p>
                                <div v-if="(progress?.status ?? '') === OrderStatus.DISPATCHED" class="mt-2 space-y-1">
                                    <p v-if="order?.driver_phone" class="text-sm text-gray-700">
                                        <span class="font-semibold text-gray-900">Driver:</span> {{ order.driver_phone
                                        }}
                                    </p>
                                    <p v-if="order?.vehicle_number" class="text-sm text-gray-700">
                                        <span class="font-semibold text-gray-900">Vehicle:</span> {{
                                            order?.vehicle_number }}
                                    </p>
                                </div>
                            </StepperDescription>

                        </div>
                    </StepperItem>
                </Stepper>
                <form v-if="order?.show_action_button" @submit.prevent class="space-y-6 mt-8">
                    <div v-if="((order?.new_status ?? '') === OrderStatus.DISPATCHED)" class="grid gap-2">
                        <div class="grid gap-2">
                            <Label for="driver_phone">Driver Phone</Label>
                            <Input id="driver_phone" ref="currentNameInput" v-bind="$attrs" v-model="form.driver_phone"
                                type="text" class="mt-1 block w-full" placeholder="Driver name" />
                            <InputError :message="form.errors.driver_phone" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="vehicle_number">Vehicle Number</Label>
                            <Input id="vehicle_number" v-model="form.vehicle_number" type="text"
                                class="mt-1 block w-full" placeholder="please enter vehicle number" />
                            <InputError :message="form.errors.vehicle_number" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="remarks">Remarks</Label>
                        <Textarea v-model="form.remarks" id="remarks" placeholder="Type your remarks here." />
                        <InputError :message="form.errors.remarks" />
                    </div>
                    <div class="mt-8 flex justify-between">
                        <Button @click="actionBtnPressed(true)" type="submit" :disabled="form.processing">
                            <LoaderCircle v-if="isLoading" class="h-4 w-4 animate-spin" />
                            <span>{{ ((order?.new_status ?? '') !== OrderStatus.DISPATCHED) ? 'Approve' : 'Dispatch'
                            }}</span>
                        </Button>
                        <SheetClose ref="closeButton" class="hidden" />
                        <Button @click="actionBtnPressed(false)" :variant="'destructive'" type="submit"
                            :disabled="form.processing">
                            <LoaderCircle v-if="isRejecting" class="h-4 w-4 animate-spin" />
                            <span>Reject</span>
                        </Button>
                    </div>
                </form>
            </div>
        </ScrollArea>
    </SheetContent>
</template>
