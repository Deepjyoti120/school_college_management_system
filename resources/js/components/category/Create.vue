<script setup lang="ts">
import SheetContent from '@/components/ui/sheet/SheetContent.vue';
import SheetHeader from '@/components/ui/sheet/SheetHeader.vue';
import SheetTitle from '@/components/ui/sheet/SheetTitle.vue';
import SheetDescription from '@/components/ui/sheet/SheetDescription.vue';
import Button from '../ui/button/Button.vue';
import Label from '../ui/label/Label.vue';
import Input from '../ui/input/Input.vue';
import InputError from '../InputError.vue';
import { nextTick, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import SheetClose from '../ui/sheet/SheetClose.vue';
import { LoaderCircle } from 'lucide-vue-next';
import { Utils } from '@/lib/utils';
import FilePondUploader from '../FilePondUploader.vue';
import ScrollArea from '../ui/scroll-area/ScrollArea.vue';
import { Category } from '@/types/Category';

const currentNameInput = ref<HTMLInputElement | null>(null);
const emit = defineEmits(['close']);
const props = defineProps<{
    open: boolean;
    category?: Category;
}>();

const form = useForm<{
    name: string;
    slug: string;
    description: string;
    icon: File | null;
}>({
    name: '',
    slug: '',
    description: '',
    icon: null,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.reset();
            selectedFiles.value = [];
            userModifiedSlug.value = false;
            if (props.category) {
                const cat = props.category
                form.name = cat.name
                form.slug = cat.slug
                form.description = cat.description
                form.icon = null;
            }
        } else {
            form.clearErrors();
        }
    },
    { immediate: true }
);
const userModifiedSlug = ref(false);

// watch(
//   () => form,
//   () => {
//     const autoSlug = Utils.slugify(form.name);
//     if (form.slug !== autoSlug) {
//       userModifiedSlug.value = true;
//     }
//     if (!userModifiedSlug.value) {
//       form.slug = autoSlug;
//     }
//   },
//   { deep: true }
// );

watch(() => form.slug, (newSlug) => {
    if (newSlug !== Utils.slugify(form.name)) {
        userModifiedSlug.value = true;
    }
});

watch(() => form.name, (newName) => {
    if (!userModifiedSlug.value && !props.category) {
        form.slug = Utils.slugify(newName);
    }
});

const selectedFiles = ref<File[]>([]);
const pickFiles = (files: File[]) => {
    selectedFiles.value = files;
    form.icon =  files[0] || null;
}
const saveCategory = () => {
    const isUpdate = !!props.category;
    const url = isUpdate
        ? route('category.update', props.category)
        : route('category.create');
    form.submit('post', url, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            userModifiedSlug.value = false;
            emit('close');
        },
        onError: (errors: any) => {
            if (errors.name) {
                // form.reset('name');
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
            <SheetTitle> {{ props.category ? 'Update' : 'Create New' }} Category</SheetTitle>
            <SheetDescription>
                This form is for creating new category
            </SheetDescription>
        </SheetHeader>
        <ScrollArea class="h-full overflow-auto">
            <div class="px-6">
                <form @submit.prevent="saveCategory" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" ref="currentNameInput" v-bind="$attrs" v-model="form.name" type="text"
                            class="mt-1 block w-full" placeholder="Category name" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="slug">Slug</Label>
                        <Input :disabled="!!props.category" id="slug" v-model="form.slug" type="text"
                            class="mt-1 block w-full" placeholder="Auto-generated if left blank" />
                        <InputError :message="form.errors.slug" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Input id="description" v-model="form.description" type="text" class="mt-1 block w-full"
                            placeholder="Optional description" />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="icon">Icon</Label>
                        <FilePondUploader :existing-image-url="props.category?.icon_url"
                            @update:files="files => pickFiles(files)" />
                        <InputError :message="form.errors.icon" />
                    </div>
                    <div class="flex items-end gap-4 mb-6">
                        <Button type="submit" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            <span>Save</span>
                        </Button>
                        <SheetClose ref="closeButton" class="hidden" />
                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </ScrollArea>
    </SheetContent>
</template>
