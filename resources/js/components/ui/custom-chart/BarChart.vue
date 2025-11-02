<script setup lang="ts">
import { BarChart } from 'vue-chart-3'
import { Chart, BarElement, CategoryScale, LinearScale, Tooltip, BarController } from 'chart.js'
import { computed } from 'vue'

Chart.register(BarElement, CategoryScale, LinearScale, Tooltip, BarController)

const props = defineProps({
  data: {
    type: Array,
    required: true,
    default: () => []
  },
  color: {
    type: String,
    default: '#3b82f6'
  },
})

const chartData = computed(() => ({
  labels: props.data.map(item => item.item),
  datasets: [{
    label:props.data.map(item => item.item),
    data: props.data.map(item => item.quantity),
    backgroundColor: props.color,
    borderRadius: 4,
    barThickness: 20,
  }]
}))

const options = {
  responsive: true,
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        drawBorder: false
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  }
}
</script>

<template>
  <BarChart :chartData="chartData" :options="options" />
</template>