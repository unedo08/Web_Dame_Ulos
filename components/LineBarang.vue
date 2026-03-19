<template>
    <div>
        <apexchart type="line" height="350" :options="chartOptions" :series="dataBarang.series" />
    </div>
</template>

<script setup>
import { computed } from "vue"

const props = defineProps({
    dataBarang: Object
})

const chartOptions = computed(() => ({
    chart: {
        type: "line",
        toolbar: { show: false },
        zoom: { enabled: false }
    },

    stroke: {
        curve: "smooth",
        width: 3
    },

    markers: {
        size: 4
    },

    xaxis: {
        categories: props.dataBarang?.categories || [],
        labels: {
            show:false
        }
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

    legend: {
        position: "top"
    },

    title: {
        // text: "Grafik Omset Per Hari",
        align: "center"
    }
}))
</script>