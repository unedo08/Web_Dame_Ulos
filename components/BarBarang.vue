<template>
  <div v-if="series.length">
    <apexchart
      type="bar"
      height="350"
      :options="chartOptions"
      :series="series"
    />
  </div>

  <div v-else class="loading-placeholder">
    Data kosong...
  </div>
</template>

<script setup>
import { computed } from "vue"

const props = defineProps({
  dataBarang: Object
})

const categories = computed(() =>
  Object.keys(props.dataBarang || {})
)

const series = computed(() => {
  const values = Object.values(props.dataBarang || {}).map(v => Number(v))

  return values.length
    ? [{ name: "Total", data: values }]
    : []
})

const chartOptions = computed(() => ({
  chart: {
    type: "bar",
    toolbar: { show: false }
  },

  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: "50%",
      borderRadius: 8,
      distributed: true
    }
  },

  dataLabels: {
    enabled: true,
    formatter: val => "Rp " + val.toLocaleString("id-ID")
  },

  xaxis: {
    categories: categories.value
  },

  yaxis: {
    title: {
      text: "Nilai (Rp)"
    },
    labels: {
      formatter: val => val.toLocaleString("id-ID")
    }
  },

  tooltip: {
    y: {
      formatter: val => "Rp " + val.toLocaleString("id-ID")
    }
  },

  colors: [
    "#22c55e",
    "#3b82f6",
    "#f59e0b"
  ],

  title: {
    align: "center"
  }
}))
</script>