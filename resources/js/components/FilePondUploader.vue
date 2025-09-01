<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import vueFilePond from 'vue-filepond'
import FilePondPluginImagePreview from 'filepond-plugin-image-preview'
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type'

import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css'

const FilePond = vueFilePond(FilePondPluginImagePreview, FilePondPluginFileValidateType)

import type { FilePondFile } from 'filepond'

const emit = defineEmits(['update:files'])
const props = defineProps<{
  allowMultiple?: boolean
  existingImageUrl?: string
}>()
const files = ref<any[]>([])

watch(files, () => {
  const plainFiles = files.value.map(f => f?.file ?? f).filter(Boolean)
  emit('update:files', plainFiles)
})
const pickFiles = (items: FilePondFile[]) => {
  files.value = items;
}
onMounted(() => {
  if (props.existingImageUrl) {
    files.value = [
      {
        source: props.existingImageUrl,
        options: {
          type: 'live',
        },
      },
    ]
  }
})
</script>

<template>
  <FilePond name="file" :allow-multiple="props.allowMultiple" :files="files" @updatefiles="(items: any) => pickFiles(items)"
    label-idle="Drag & Drop your files or <span class='filepond--label-action'>Browse</span>"
    />
</template>
