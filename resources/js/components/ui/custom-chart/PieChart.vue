<script setup lang="ts">
import { PieChart } from 'vue-chart-3'
import { Chart, ArcElement, Tooltip, Legend, PieController } from 'chart.js'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import { computed } from 'vue'

Chart.register(ArcElement, Tooltip, Legend, PieController, ChartDataLabels)

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
    secondary: '#64748b',
    warning: '#ef4444'
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
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'right',
      labels: {
        boxWidth: 12,
        padding: 20
      }
    },
    datalabels: {
      color: '#fff',
      font: {
        weight: 'bold',
        size: 12
      },
      formatter: (value, context) => {
        const label = context.chart.data.labels?.[context.dataIndex] || ''
        return `${label}: ${value}`
      }
    }
  }
}

// const plugins = [ChartDataLabels]
</script>

<template>
  <PieChart :chart-data="chartData" :options="options"/>
</template>
