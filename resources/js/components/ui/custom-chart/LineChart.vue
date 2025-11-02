<script setup lang="ts">
import { LineChart } from 'vue-chart-3'
import { Chart, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, LineController } from 'chart.js'
import { computed } from 'vue'

Chart.register(LineElement, PointElement, CategoryScale, LinearScale, Tooltip, LineController)

const props = defineProps({
  data: {
    type: Array,
    required: true,
    default: () => []
  }
})

const chartData = computed(() => ({
  labels: props.data.map(item => item.date),
  datasets: [{
    label: props.data.map(item => item.date || ''),
    data: props.data.map(item => item.count),
    borderColor: '#3b82f6',
    backgroundColor: 'rgba(59, 130, 246, 0.1)',
    borderWidth: 2,
    tension: 0.1,
    fill: true
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
  <LineChart :chartData="chartData" :options="options" />
</template>