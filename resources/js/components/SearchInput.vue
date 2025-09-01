<script setup lang="ts">
import { Input } from '@/components/ui/input'
import { X } from 'lucide-vue-next'
import { toRefs } from 'vue'

const props = defineProps<{
  modelValue: string
  placeholder?: string
}>()

const emit = defineEmits(['update:modelValue'])

const { modelValue, placeholder } = toRefs(props)

function clearInput() {
  emit('update:modelValue', '')
}
</script>

<template>
  <div class="relative w-full">
    <Input
      :model-value="modelValue"
      :placeholder="placeholder || 'Search...'"
      class="pr-10"
      @update:model-value="value => emit('update:modelValue', value)"
    />
    <X
      v-if="modelValue"
      class="absolute right-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground hover:text-destructive cursor-pointer transition-colors"
      @click="clearInput"
    />
  </div>
</template>
