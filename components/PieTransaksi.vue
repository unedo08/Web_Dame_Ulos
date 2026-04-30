<template>
    <div class="chart-wrapper">
        <canvas ref="chartRef"></canvas>
    </div>
</template>

<script setup>
import { onMounted, watch, ref } from "vue";
import { Chart, ArcElement, Tooltip, Legend } from "chart.js";

Chart.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    dataTransaksi: Object
});

const chartRef = ref(null);
let chartInstance = null;

const renderChart = () => {
    if (!props.dataTransaksi) return;

    const labels = Object.keys(props.dataTransaksi);
    const values = Object.values(props.dataTransaksi);

    if (chartInstance) {
        chartInstance.destroy();
    }

    chartInstance = new Chart(chartRef.value, {
        type: "pie",
        data: {
            labels: labels,
            datasets: [
                {
                    data: values,
                    backgroundColor: [
                        "#10B981",
                        "#EF4444",
                        "#3B82F6",
                        "#8B5CF6",
                        "#F59E0B"
                    ]
                }
            ]
        },
        options: {
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: "bottom"
                },
                tooltip: {
                    callbacks: {
                        label(ctx) {
                            const label = ctx.label || ""
                            const value = Number(ctx.raw || 0)
                            return ` ${label}: Rp ${value.toLocaleString("id-ID")}`
                        }
                    }
                }
            }
        }
    });
};

onMounted(() => {
    renderChart();
});

watch(() => props.dataTransaksi, () => {
    renderChart();
});
</script>
<style scoped>
.chart-wrapper {
    width: 280px;
    height: 280px;
    margin: 0 auto;
}
</style>