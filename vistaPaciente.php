<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas Médicas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2196F3, #21CBF3);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .patient-info {
            background: #f8f9fa;
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
        }

        .patient-card {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .patient-details h3 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .patient-details p {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .appointments-section {
            padding: 30px;
        }

        .section-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .appointments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .appointment-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border-left: 5px solid;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .appointment-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }

        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .appointment-card.upcoming {
            border-left-color: #28a745;
        }

        .appointment-card.today {
            border-left-color: #ffc107;
            background: linear-gradient(135deg, #fff9c4, #ffffff);
        }

        .appointment-card.completed {
            border-left-color: #6c757d;
            opacity: 0.8;
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .appointment-date {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .appointment-time {
            font-size: 1rem;
            color: #6c757d;
            margin-top: 5px;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-upcoming {
            background: #d4edda;
            color: #155724;
        }

        .status-today {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background: #e2e3e5;
            color: #383d41;
        }

        .appointment-details {
            margin-bottom: 15px;
        }

        .appointment-details h4 {
            color: #495057;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .detail-item strong {
            color: #495057;
            margin-right: 8px;
            min-width: 80px;
        }

        .appointment-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state img {
            width: 120px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .appointments-grid {
                grid-template-columns: 1fr;
            }

            .patient-card {
                flex-direction: column;
                text-align: center;
            }

            .appointment-header {
                flex-direction: column;
                gap: 10px;
            }

            .appointment-actions {
                flex-direction: column;
            }
        }

        .notification {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏥 Mis Citas Médicas</h1>
            <p>Portal del Paciente - Consulta tu horario de atención</p>
        </div>

        <div class="patient-info">
            <div class="patient-card">
                <div class="avatar">JD</div>
                <div class="patient-details">
                    <h3>Juan Pérez Domínguez</h3>
                    <p>ID: 12345678 | Fecha de nacimiento: 15/03/1985</p>
                </div>
            </div>
        </div>

        <div class="appointments-section">
            <div class="notification">
                <strong>📅 Recordatorio:</strong> Se recomienda llegar 15 minutos antes de su cita programada.
            </div>

            <h2 class="section-title">
                📋 Próximas Citas
            </h2>

            <div class="appointments-grid">
                <!-- Cita de Hoy -->
                <div class="appointment-card today">
                    <div class="appointment-header">
                        <div>
                            <div class="appointment-date">Hoy - 1 Septiembre 2025</div>
                            <div class="appointment-time">⏰ 10:30 AM</div>
                        </div>
                        <span class="status-badge status-today">Hoy</span>
                    </div>
                    <div class="appointment-details">
                        <h4>Dr. María González</h4>
                        <div class="detail-item">
                            <strong>Especialidad:</strong> Cardiología
                        </div>
                        <div class="detail-item">
                            <strong>Consultorio:</strong> 205 - Piso 2
                        </div>
                        <div class="detail-item">
                            <strong>Tipo:</strong> Consulta de control
                        </div>
                        <div class="detail-item">
                            <strong>Teléfono:</strong> (503) 2234-5678
                        </div>
                    </div>
                    <div class="appointment-actions">
                        <a href="#" class="btn btn-secondary">📞 Llamar</a>
                    </div>
                </div>

                <!-- Próximas Citas -->
                <div class="appointment-card upcoming">
                    <div class="appointment-header">
                        <div>
                            <div class="appointment-date">5 Septiembre 2025</div>
                            <div class="appointment-time">⏰ 2:15 PM</div>
                        </div>
                        <span class="status-badge status-upcoming">Próxima</span>
                    </div>
                    <div class="appointment-details">
                        <h4>Dr. Carlos Mendoza</h4>
                        <div class="detail-item">
                            <strong>Especialidad:</strong> Medicina General
                        </div>
                        <div class="detail-item">
                            <strong>Consultorio:</strong> 101 - Piso 1
                        </div>
                        <div class="detail-item">
                            <strong>Tipo:</strong> Revisión general
                        </div>
                        <div class="detail-item">
                            <strong>Teléfono:</strong> (503) 2234-5679
                        </div>
                    </div>
                    <div class="appointment-actions">
                        <a href="#" class="btn btn-secondary">📅 Reagendar</a>
                    </div>
                </div>

                <div class="appointment-card upcoming">
                    <div class="appointment-header">
                        <div>
                            <div class="appointment-date">12 Septiembre 2025</div>
                            <div class="appointment-time">⏰ 9:00 AM</div>
                        </div>
                        <span class="status-badge status-upcoming">Próxima</span>
                    </div>
                    <div class="appointment-details">
                        <h4>Dra. Ana Rodríguez</h4>
                        <div class="detail-item">
                            <strong>Especialidad:</strong> Oftalmología
                        </div>
                        <div class="detail-item">
                            <strong>Consultorio:</strong> 308 - Piso 3
                        </div>
                        <div class="detail-item">
                            <strong>Tipo:</strong> Examen de la vista
                        </div>
                        <div class="detail-item">
                            <strong>Teléfono:</strong> (503) 2234-5680
                        </div>
                    </div>
                    <div class="appointment-actions">
                        <a href="#" class="btn btn-secondary">📅 Reagendar</a>
                    </div>
                </div>

                <div class="appointment-card upcoming">
                    <div class="appointment-header">
                        <div>
                            <div class="appointment-date">20 Septiembre 2025</div>
                            <div class="appointment-time">⏰ 11:45 AM</div>
                        </div>
                        <span class="status-badge status-upcoming">Próxima</span>
                    </div>
                    <div class="appointment-details">
                        <h4>Dr. Roberto Silva</h4>
                        <div class="detail-item">
                            <strong>Especialidad:</strong> Dermatología
                        </div>
                        <div class="detail-item">
                            <strong>Consultorio:</strong> 150 - Piso 1
                        </div>
                        <div class="detail-item">
                            <strong>Tipo:</strong> Consulta especializada
                        </div>
                        <div class="detail-item">
                            <strong>Teléfono:</strong> (503) 2234-5681
                        </div>
                    </div>
                    <div class="appointment-actions">
                        <a href="#" class="btn btn-secondary">📅 Reagendar</a>
                    </div>
                </div>
            </div>

            <h2 class="section-title" style="margin-top: 40px;">
                ✅ Historial de Citas
            </h2>

            <div class="appointments-grid">
                <div class="appointment-card completed">
                    <div class="appointment-header">
                        <div>
                            <div class="appointment-date">25 Agosto 2025</div>
                            <div class="appointment-time">⏰ 3:30 PM</div>
                        </div>
                        <span class="status-badge status-completed">Completada</span>
                    </div>
                    <div class="appointment-details">
                        <h4>Dr. María González</h4>
                        <div class="detail-item">
                            <strong>Especialidad:</strong> Cardiología
                        </div>
                        <div class="detail-item">
                            <strong>Tipo:</strong> Consulta inicial
                        </div>
                        <div class="detail-item">
                            <strong>Estado:</strong> Completada con éxito
                        </div>
                    </div>
                    <div class="appointment-actions">
                        <a href="#" class="btn btn-secondary">📋 Ver detalles</a>
                    </div>
                </div>

                <div class="appointment-card completed">
                    <div class="appointment-header">
                        <div>
                            <div class="appointment-date">18 Agosto 2025</div>
                            <div class="appointment-time">⏰ 10:00 AM</div>
                        </div>
                        <span class="status-badge status-completed">Completada</span>
                    </div>
                    <div class="appointment-details">
                        <h4>Dr. Carlos Mendoza</h4>
                        <div class="detail-item">
                            <strong>Especialidad:</strong> Medicina General
                        </div>
                        <div class="detail-item">
                            <strong>Tipo:</strong> Chequeo anual
                        </div>
                        <div class="detail-item">
                            <strong>Estado:</strong> Completada con éxito
                        </div>
                    </div>
                    <div class="appointment-actions">
                        <a href="#" class="btn btn-secondary">📋 Ver detalles</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

   
</body>
</html>
