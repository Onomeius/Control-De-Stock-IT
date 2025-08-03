<?php require_once 'includes/header.php'; ?>

<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="page-heading">
                    <i class="glyphicon glyphicon-stats"></i> Salidas por Mes
                    <div class="pull-right">
                        <select id="year-filter-monthly" class="form-control" style="display: inline-block; width: auto;">
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                        </select>
                        <div class="btn-group" role="group" style="margin-left: 10px;">
                            <button type="button" class="btn btn-default btn-sm active" id="bar-chart-btn">Barras</button>
                            <button type="button" class="btn btn-default btn-sm" id="line-chart-btn">Líneas</button>
                        </div>
                    </div>
                </div>
            </div> <div class="panel-body">
                <canvas id="monthlySalesChart" style="height: 300px;"></canvas>
            </div> </div> </div> <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="page-heading">
                    <i class="glyphicon glyphicon-stats"></i> Productos por Categoría
                    <div class="pull-right">
                        <select id="year-filter-category" class="form-control" style="display: inline-block; width: auto;">
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                        </select>
                    </div>
                </div>
            </div> <div class="panel-body">
                <canvas id="categoryChart" style="height: 300px;"></canvas>
            </div> </div> </div> </div> <script src="assests/plugins/moment/moment.min.js"></script>
<script src="assests/plugins/fullcalendar/fullcalendar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script type="text/javascript">
$(function() {
    // top bar active
    $('#navDashboard').addClass('active');

    // Inicializar el calendario (si lo necesitas)
    $('#calendar').fullCalendar({
        monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        header: {
            left: '',
            center: 'title'
        },
        buttonText: {
            today: 'hoy',
            month: 'mes'
        }
    });

    // --- Lógica para la gráfica de Salidas por Mes ---
    let monthlySalesChartInstance = null; // Para guardar la instancia de la gráfica y poder destruirla

    const fetchMonthlySales = (year, type) => {
        $.ajax({
            url: 'php_action/fetchMonthlySales.php',
            type: 'post',
            dataType: 'json',
            data: { year: year },
            success: (response) => {
                if (monthlySalesChartInstance) {
                    monthlySalesChartInstance.destroy(); // Destruye la gráfica anterior antes de crear una nueva
                }
                const ctx = document.getElementById('monthlySalesChart').getContext('2d');
                monthlySalesChartInstance = new Chart(ctx, {
                    type: type, // 'bar' o 'line'
                    data: {
                        labels: response.labels,
                        datasets: [{
                            label: 'Número de Salidas',
                            data: response.data,
                            backgroundColor: (type === 'bar') ? 'rgba(54, 162, 235, 0.5)' : 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2,
                            fill: (type === 'line'), // Rellena el área bajo la línea
                            tension: 0.4 // Suaviza la línea
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            },
                            title: {
                                display: true,
                                text: `Salidas por Mes - ${year}`
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Cantidad'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Mes'
                                }
                            }
                        },
                        animation: {
                            duration: 1000 // Animación más larga y visible
                        }
                    }
                });
            }
        });
    };

    // Eventos para el filtro de año y el selector de tipo de gráfica
    $('#year-filter-monthly').on('change', function() {
        const selectedYear = $(this).val();
        const chartType = $('#bar-chart-btn').hasClass('active') ? 'bar' : 'line';
        fetchMonthlySales(selectedYear, chartType);
    });

    $('#bar-chart-btn').on('click', function() {
        $(this).addClass('active').siblings().removeClass('active');
        const selectedYear = $('#year-filter-monthly').val();
        fetchMonthlySales(selectedYear, 'bar');
    });

    $('#line-chart-btn').on('click', function() {
        $(this).addClass('active').siblings().removeClass('active');
        const selectedYear = $('#year-filter-monthly').val();
        fetchMonthlySales(selectedYear, 'line');
    });

    // Carga inicial de la gráfica de ventas (para el año actual por defecto)
    fetchMonthlySales($('#year-filter-monthly').val(), 'bar');


    // --- Lógica para la gráfica de Productos por Categoría ---
    let categoryChartInstance = null; // Instancia de la gráfica de pastel

    const fetchCategoryChart = (year) => {
        $.ajax({
            url: 'php_action/fetchCategoryChartData.php',
            type: 'post',
            dataType: 'json',
            data: { year: year },
            success: (response) => {
                if (categoryChartInstance) {
                    categoryChartInstance.destroy();
                }
                const ctx = document.getElementById('categoryChart').getContext('2d');
                categoryChartInstance = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: response.labels,
                        datasets: [{
                            label: 'Productos por Categoría',
                            data: response.data,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)',
                                'rgba(255, 159, 64, 0.7)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'right', // Mueve la leyenda a la derecha para mejor visibilidad
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((sum, current) => sum + current, 0);
                                        const percentage = ((value / total) * 100).toFixed(2) + '%';
                                        return `${label}: ${value} (${percentage})`;
                                    }
                                }
                            },
                            title: {
                                display: true,
                                text: `Productos por Categoría - ${year}`
                            }
                        },
                        animation: {
                            animateRotate: true,
                            animateScale: true
                        }
                    }
                });
            }
        });
    };

    // Evento para el filtro de año de la gráfica de categorías
    $('#year-filter-category').on('change', function() {
        const selectedYear = $(this).val();
        fetchCategoryChart(selectedYear);
    });

    // Carga inicial de la gráfica de categorías (para el año actual por defecto)
    fetchCategoryChart($('#year-filter-category').val());
});
</script>

<?php require_once 'includes/footer.php'; ?>