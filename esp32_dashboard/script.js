document.addEventListener("DOMContentLoaded", () => {
    fetchData();
});

async function fetchData() {
    const response = await fetch('get_data.php');
    const data = await response.json();

    const labels = data.map(item => new Date(item.created_at).toLocaleTimeString());
    const distances = data.map(item => item.distance);

    // Distance Line Chart with Enhanced Scaling
    const ctx = document.getElementById('distanceChart').getContext('2d');
    const distanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Distance (cm)',
                data: distances,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderWidth: 2
            }]
        },
        options: {
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'minute'
                    }
                },
                y: {
                    beginAtZero: true,
                    max: Math.max(...distances) + 10 // Dynamically set max Y scale
                }
            }
        }
    });

    // Pie Chart for Distance Status
    const distanceThreshold = 10; // Define threshold
    const safeCount = distances.filter(d => d >= distanceThreshold).length;
    const warningCount = distances.length - safeCount;

    const pieCtx = document.getElementById('statusPieChart').getContext('2d');
    const statusPieChart = new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: ['Safe Distance', 'Warning Distance'],
            datasets: [{
                data: [safeCount, warningCount],
                backgroundColor: ['#36A2EB', '#FF6384'],
                hoverBackgroundColor: ['#36A2EB', '#FF6384']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: (tooltipItem) => {
                            return `${tooltipItem.label}: ${tooltipItem.raw}`;
                        }
                    }
                }
            }
        }
    });
}
