<script setup lang="ts">
import { DoughnutChart } from 'vue-chart-3'
import { Chart, ArcElement, Tooltip, Legend, DoughnutController } from 'chart.js'
import { computed } from 'vue'

Chart.register(ArcElement, Tooltip, Legend, DoughnutController)

const props = defineProps({
  data: {
    type: Array,
    required: true,
    default: () => []
  }
})

const chartData = computed(() => {
  const colors = {
    primary: '#3b82f6',
    success: '#10b981',
    destructive: '#ef4444',
    secondary: '#64748b'
  }

  return {
    labels: props.data.map(item => item.label),
    datasets: [{
      data: props.data.map(item => item.value),
      backgroundColor: props.data.map(item => colors[item.color]),
      borderWidth: 0
    }]
  }
})

const options = {
  cutout: '70%',
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'right',
      labels: {
        boxWidth: 12,
        padding: 20
      }
    }
  },
}
</script>

<template>
   <DoughnutChart :chartData="chartData" :options="options" />
</template>