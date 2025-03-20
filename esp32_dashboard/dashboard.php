<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP32 Real-Time Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        #data-graph, #pie-chart {
            margin: 20px 0;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        canvas {
            max-width: 100%;
            height: 400px;
        }
        .alert {
            color: red;
            font-weight: bold;
        }
        .button-container {
            margin: 10px 0;
        }
        .button {
            padding: 10px 20px;
            margin-right: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>ESP32 Real-Time Distance Monitoring Dashboard</h1>
    <div class="button-container">
        <button class="button" onclick="refreshData()">Refresh Data</button>
        <button class="button" onclick="clearData()">Clear Data</button>
    </div>
    <div id="alert"></div>
    <div id="data-graph">
        <canvas id="myChart"></canvas>
    </div>
    <div id="pie-chart">
        <canvas id="myPieChart"></canvas>
    </div>
    <h2>Last 10 Distance Readings</h2>
    <table id="readingsTable">
        <thead>
            <tr>
                <th>Distance (cm)</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <!-- Readings will be dynamically inserted here -->
        </tbody>
    </table>
    <script>
        let distances = [];
        let labels = [];
        let myChart;
        let myPieChart;

        async function fetchReadings() {
            const response = await fetch('get_data.php');
            const data = await response.json();
            
            const tableBody = document.getElementById('readingsTable').getElementsByTagName('tbody')[0];
            tableBody.innerHTML = ''; // Clear previous data

            distances = [];
            labels = [];
            let warningTriggered = false;

            data.forEach(item => {
                const row = tableBody.insertRow();
                const distanceCell = row.insertCell(0);
                const timestampCell = row.insertCell(1);
                
                distanceCell.innerHTML = item.distance;
                timestampCell.innerHTML = new Date(item.created_at).toLocaleString(); // Format timestamp
                
                distances.push(item.distance);
                labels.push(new Date(item.created_at).toLocaleTimeString());

                // Check for warning condition and trigger alert
                if (item.distance < 15) { // Replace '20' with your threshold distance
                    warningTriggered = true;
                }
            });

            // Trigger real-time alert if an object is detected within the threshold
            const alertDiv = document.getElementById('alert');
            if (warningTriggered) {
                alertDiv.innerHTML = '<p class="alert">⚠️ Warning: Object detected within the threshold distance!</p>';
                alertDiv.scrollIntoView({ behavior: 'smooth' });
                playAlertSound(); // Optional: function to play an alert sound
            } else {
                alertDiv.innerHTML = ' ';
            }

            updateChart();
            updatePieChart();
        }

        function updateChart() {
            const ctx = document.getElementById('myChart').getContext('2d');

            if (myChart) {
                myChart.destroy();
            }

            myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Distance (cm)',
                        data: distances,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(75, 192, 192, 1)',
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            min: 0,
                            max: 30, // Set maximum to 20 cm for y-axis
                            title: {
                                display: true,
                                text: 'Distance (cm)',
                                font: {
                                    size: 16
                                }
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + ' cm';
                                },
                            },
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Time',
                                font: {
                                    size: 16
                                }
                            },
                            ticks: {
                                autoSkip: true,
                                maxTicksLimit: 10,
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                    },
                }
            });
        }

        function updatePieChart() {
            const ctxPie = document.getElementById('myPieChart').getContext('2d');

            if (myPieChart) {
                myPieChart.destroy();
            }

            const distanceCounts = distances.reduce((acc, distance) => {
                const range = Math.floor(distance / 5) * 5;
                acc[range] = (acc[range] || 0) + 1;
                return acc;
            }, {});

            const labels = Object.keys(distanceCounts);
            const counts = Object.values(distanceCounts);

            myPieChart = new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: labels.map(label => label + ' cm'),
                    datasets: [{
                        label: 'Distance Distribution',
                        data: counts,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 206, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)',
                            'rgba(255, 159, 64, 0.6)',
                        ],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Distance Distribution',
                        },
                    },
                },
            });
        }

        function clearData() {
            distances = [];
            labels = [];
            document.getElementById('readingsTable').getElementsByTagName('tbody')[0].innerHTML = '';
            document.getElementById('alert').innerHTML = '';

            if (myChart) {
                myChart.destroy();
            }
            if (myPieChart) {
                myPieChart.destroy();
            }
        }

        function refreshData() {
            fetchReadings();
        }

        // Optional: Function to play a sound alert
        function playAlertSound() {
            const audio = new Audio('alert_sound.mp3'); // Ensure you have this file in your project folder
            audio.play();
        }

        // Automatically fetch data every 5 seconds for real-time updates
        setInterval(fetchReadings, 5000);

        window.onload = function() {
            fetchReadings();
        };
    </script>
</body>
</html>