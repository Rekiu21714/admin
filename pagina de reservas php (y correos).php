function yoga_reservas_shortcode() {
    ob_start();
    ?>
    <div id="yogaBookingContainer" class="yoga-booking-system">
        
              <!-- HEADER PRINCIPAL ELEGANTE -->
        <div class="yoga-main-header">
            <div class="header-decoration"></div>
            <div class="header-content">
                <div class="header-logo">
                    <img src="https://kuruntayoga.com.mx/wp-content/uploads/2025/06/cropped-cropped-cropped-logoVerde.png" alt="Kurunta Yoga" class="main-logo-img">
                </div>
                <div class="header-text">
                    <h1 class="header-title">Reserva tu Clase</h1>
                    <p class="header-subtitle">Bienvenido • Encuentra tu momento de paz</p>
                    <div class="header-stats">
                        <div class="stat">
                            <span class="stat-number">500+</span>
                            <span class="stat-label">Clases</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">150+</span>
                            <span class="stat-label">Estudiantes</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">5</span>
                            <span class="stat-label">Años</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Filtros de tiempo mejorados -->
        <div class="time-filters">
            <button class="filter-btn active" data-filter="all">TODOS</button>
            <button class="filter-btn" data-filter="morning">MAÑANA</button>
            <button class="filter-btn" data-filter="afternoon">TARDE</button>
            <button class="filter-btn" data-filter="evening">NOCHE</button>
        </div>

        <!-- Navegación de semana -->
        <div class="week-navigation">
            <button class="nav-btn" id="prevWeek">‹</button>
            <div class="date-info">
                <div class="date-range" id="dateRange">01 - 07, jun 2025</div>
                <div class="day-labels">
                    <span>D</span>
                    <span>L</span>
                    <span>M</span>
                    <span>M</span>
                    <span>J</span>
                    <span>V</span>
                    <span>S</span>
                </div>
                <div class="day-numbers" id="dayNumbers">
                    <!-- Los números se generan dinámicamente -->
                </div>
            </div>
            <button class="nav-btn" id="nextWeek">›</button>
        </div>

        <!-- Lista de clases -->
        <div class="classes-list" id="classList">
            <div class="loading-classes">
                <p>Cargando clases disponibles...</p>
            </div>
        </div>

        <!-- Modal de reserva -->
        <div id="reservationModal" class="yoga-modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Reservar Clase</h2>
                <div class="modal-details" id="modalDetails"></div>
                <form class="reservation-form" id="reservationForm">
                    <div class="form-group">
                        <label for="userName">Nombre:</label>
                        <input type="text" id="userName" name="userName" required>
                    </div>
                    <div class="form-group">
                        <label for="userEmail">Email:</label>
                        <input type="email" id="userEmail" name="userEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="userPhone">Teléfono:</label>
                        <input type="tel" id="userPhone" name="userPhone" required>
                    </div>
                </form>
                <div class="modal-actions">
                    <button class="btn-cancel" type="button">Cancelar</button>
                    <button class="btn-confirm" type="button">Confirmar Reserva</button>
                </div>
            </div>
        </div>

        <!-- Modal de éxito -->
        <div id="successModal" class="yoga-modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div class="success-content">
                    <div class="success-icon">✓</div>
                    <h2>¡Reserva Confirmada!</h2>
                    <p>Tu clase ha sido reservada exitosamente.</p>
                    <p>Recibirás un email de confirmación en breve.</p>
                </div>
                <div class="modal-actions">
                    <button class="btn-confirm" type="button">Cerrar</button>
                </div>
            </div>
        </div>
		        <!-- Modal de éxito -->
        <div id="successModal" class="yoga-modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div class="success-content">
                    <div class="success-icon">✓</div>
                    <h2>¡Reserva Confirmada!</h2>
                    <p>Tu clase ha sido reservada exitosamente.</p>
                    <p>Recibirás un email de confirmación en breve.</p>
                </div>
                <div class="modal-actions">
                    <button class="btn-confirm" type="button">Cerrar</button>
                </div>
            </div>
        </div>

        <!-- 🔥 AGREGAR AQUÍ EL MODAL DE CANCELACIÓN 🔥 -->
        <!-- Modal de cancelación de reserva -->
        <div id="cancelModal" class="yoga-modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Cancelar Reserva</h2>
                <div class="cancel-details" id="cancelDetails">
                    <div class="modal-class-info">
                        <h3 class="modal-class-title" id="cancelClassName">Clase de Yoga</h3>
                        <div class="cancel-info">
                            <p>¿Estás seguro de que deseas cancelar tu reserva para esta clase?</p>
                        </div>
                    </div>
                </div>
                <div class="cancel-warning">
                    <div class="warning-icon">⚠️</div>
                    <p><strong>¡Atención!</strong></p>
                    <p>Para cancelar tu reserva, necesitamos confirmar tu identidad.</p>
                </div>
                <form class="cancel-form" id="cancelForm">
                    <div class="form-group">
                        <label for="cancelEmail">Email de confirmación:</label>
                        <input type="email" id="cancelEmail" name="cancelEmail" placeholder="Ingresa el email con el que reservaste" required>
                    </div>
                </form>
                <div class="modal-actions">
                    <button class="btn-cancel-action" type="button">No, mantener reserva</button>
                    <button class="btn-confirm-cancel" type="button">Sí, cancelar reserva</button>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* NUEVO DISEÑO MEJORADO */
    .yoga-booking-system {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        font-family: 'Montserrat', Arial, sans-serif;
        background: linear-gradient(135deg, #f4f3f1 0%, rgba(148, 164, 132, 0.1) 100%);
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

             /* HEADER PRINCIPAL ELEGANTE - MEJORADO */
    .yoga-main-header {
        background: linear-gradient(135deg, rgba(148, 164, 132, 0.05) 0%, rgba(148, 164, 132, 0.1) 100%);
        border-radius: 20px;
        padding: 40px 30px;
        margin-bottom: 30px;
        box-shadow: 
            0 8px 32px rgba(0, 0, 0, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.5);
        border: 1px solid rgba(148, 164, 132, 0.2);
        position: relative;
        overflow: hidden;
    }

    .header-decoration {
        position: absolute;
        top: -50%;
        right: -10%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(148, 164, 132, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .header-decoration::before {
        content: '';
        position: absolute;
        top: 20%;
        left: 20%;
        width: 60%;
        height: 60%;
        background: radial-gradient(circle, rgba(140, 22, 42, 0.05) 0%, transparent 70%);
        border-radius: 50%;
    }

    .header-content {
        display: flex;
        align-items: center;
        gap: 40px;
        max-width: 700px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }

    .header-logo {
        flex-shrink: 0;
    }

        .main-logo-img {
        width: 200px;
        height: auto;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(148, 164, 132, 0.25);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
        padding: 10px;
    }

    .main-logo-img:hover {
        transform: scale(1.05) rotate(2deg);
        box-shadow: 0 12px 32px rgba(148, 164, 132, 0.35);
    }

    .header-text {
        flex: 1;
        text-align: left;
    }

    .header-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2c2c2c;
        margin: 0 0 12px 0;
        letter-spacing: 0.5px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .header-subtitle {
        font-size: 1.2rem;
        color: #666;
        margin: 0 0 20px 0;
        font-weight: 400;
        line-height: 1.4;
    }

    .header-stats {
        display: flex;
        gap: 30px;
        margin-top: 5px;
    }

    .stat {
        text-align: center;
    }

    .stat-number {
        display: block;
        font-size: 1.5rem;
        font-weight: 700;
        color: #94a484;
        line-height: 1;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* QUITAR EL HEADER SECUNDARIO */
    .yoga-booking-header {
        display: none;
    }
		
    /* FILTROS MEJORADOS */
    .time-filters {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 12px 24px;
        border: 2px solid #94a484;
        background: white;
        color: #94a484;
        border-radius: 25px;
        cursor: pointer;
        font-weight: 500;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(148, 164, 132, 0.2);
    }

    .filter-btn:hover {
        background: #94a484;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(148, 164, 132, 0.3);
    }

    .filter-btn.active {
        background: #94a484;
        color: white;
        box-shadow: 0 4px 12px rgba(148, 164, 132, 0.4);
    }

    /* NAVEGACIÓN DE SEMANA MEJORADA */
    .week-navigation {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 30px;
        padding: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .nav-btn {
        width: 50px;
        height: 50px;
        border: none;
        background: #94a484;
        color: white;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(148, 164, 132, 0.3);
    }

    .nav-btn:hover {
        background: #8c162a;
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(140, 22, 42, 0.4);
    }

    .date-info {
        margin: 0 30px;
        text-align: center;
        min-width: 200px;
    }

    .date-range {
        font-size: 1.2rem;
        font-weight: 600;
        color: #2c2c2c;
        margin-bottom: 15px;
    }

    .day-labels {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-weight: 500;
        color: #94a484;
    }

    .day-numbers {
        display: flex;
        justify-content: space-between;
        gap: 5px;
    }

    .day-number {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
        background: rgba(148, 164, 132, 0.1);
    }

    .day-number:hover {
        background: #94a484;
        color: white;
        transform: scale(1.1);
    }

    .day-number.selected {
        background: #8c162a;
        color: white;
        box-shadow: 0 2px 8px rgba(140, 22, 42, 0.4);
    }

    .day-number.today {
        background: #94a484;
        color: white;
        font-weight: bold;
    }

        /* SECCIÓN DE CLASES MEJORADA CON ANIMACIONES */
    .classes-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-top: 25px;
    }

    .class-item {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 
            0 4px 20px rgba(0, 0, 0, 0.08),
            0 1px 3px rgba(0, 0, 0, 0.05);
        border: 2px solid transparent;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        animation: slideInUp 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(30px);
    }

    /* Animación de entrada escalonada */
    .class-item:nth-child(1) { animation-delay: 0.1s; }
    .class-item:nth-child(2) { animation-delay: 0.2s; }
    .class-item:nth-child(3) { animation-delay: 0.3s; }
    .class-item:nth-child(4) { animation-delay: 0.4s; }
    .class-item:nth-child(5) { animation-delay: 0.5s; }

    @keyframes slideInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Efecto de brillo sutil */
    .class-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(148, 164, 132, 0.1), transparent);
        transition: left 0.6s ease;
        z-index: 1;
    }

    .class-item:hover::before {
        left: 100%;
    }

    .class-item:hover {
        transform: translateY(-8px);
        box-shadow: 
            0 12px 40px rgba(0, 0, 0, 0.15),
            0 4px 12px rgba(148, 164, 132, 0.2);
        border-color: rgba(148, 164, 132, 0.3);
    }

    /* Estados de disponibilidad con animaciones */
    .class-item.available {
        border-left: 5px solid #94a484;
    }

    .class-item.available:hover {
        border-left-color: #7a8a6f;
        background: linear-gradient(135deg, rgba(148, 164, 132, 0.02) 0%, white 100%);
    }

    .class-item.almost-full {
        border-left: 5px solid #ffc107;
        animation: gentlePulse 3s infinite;
    }

    .class-item.full {
        border-left: 5px solid #dc3545;
        opacity: 0.7;
        filter: grayscale(0.3);
    }

    @keyframes gentlePulse {
        0%, 100% { 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        50% { 
            box-shadow: 0 4px 20px rgba(255, 193, 7, 0.2);
        }
    }

    /* HEADER DE CLASE CON MEJOR LAYOUT */
    .class-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
        position: relative;
        z-index: 2;
    }

    .class-main-info {
        flex: 1;
    }

    .class-time {
        font-size: 2rem;
        font-weight: 700;
        color: #2c2c2c;
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
    }

    .class-time::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 0;
        height: 3px;
        background: linear-gradient(90deg, #94a484, #8c162a);
        transition: width 0.4s ease;
        border-radius: 2px;
    }

    .class-item:hover .class-time::after {
        width: 80px;
    }

    .class-name {
        font-size: 1.4rem;
        font-weight: 600;
        color: #333;
        margin: 0 0 4px 0;
        background: linear-gradient(135deg, #2c2c2c 0%, #4a4a4a 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .class-instructor {
        font-size: 0.95rem;
        color: #94a484;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }

    /* INFORMACIÓN ADICIONAL CON ICONOS */
    .class-details {
        display: flex;
        gap: 15px;
        margin: 15px 0 20px 0;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
    }

    .class-detail-item {
        display: flex;
        align-items: center;
        gap: 6px;
        background: rgba(148, 164, 132, 0.1);
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        color: #555;
        transition: all 0.3s ease;
    }

    .class-detail-item:hover {
        background: rgba(148, 164, 132, 0.2);
        transform: scale(1.05);
    }

    .detail-icon {
        font-size: 1rem;
    }

    /* STATUS BADGE MEJORADO */
    .class-status {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
        flex-shrink: 0;
    }

    .availability-badge {
        padding: 8px 16px;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .availability-badge.available {
        background: rgba(148, 164, 132, 0.1);
        color: #7a8a6f;
        border: 2px solid rgba(148, 164, 132, 0.2);
        font-weight: 500;
    }

    .availability-badge.almost-full {
        background: rgba(255, 193, 7, 0.15);
        color: #d39e00;
        border: 2px solid rgba(255, 193, 7, 0.3);
        animation: shimmer 2s infinite;
    }

    .availability-badge.full {
        background: rgba(220, 53, 69, 0.15);
        color: #dc3545;
        border: 2px solid rgba(220, 53, 69, 0.3);
    }

    @keyframes shimmer {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

       .recurring-badge {
        background: rgba(140, 22, 42, 0.1);
        color: #8c162a;
        border: 2px solid rgba(140, 22, 42, 0.2);
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        box-shadow: none;
        position: relative;
        overflow: hidden;
    }

    /* BOTONES MEJORADOS CON ANIMACIONES */
    .class-buttons {
        display: flex;
        gap: 15px;
        margin-top: 20px;
        position: relative;
        z-index: 2;
    }

    .reserve-btn, .cancel-btn {
        flex: 1;
        padding: 14px 24px;
        border: none;
        border-radius: 25px;
        font-size: 0.95rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        text-align: center;
    }

    /* Efecto de onda en botones */
    .reserve-btn::before, .cancel-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .reserve-btn:active::before, .cancel-btn:active::before {
        width: 300px;
        height: 300px;
    }

        .reserve-btn.available {
        background: linear-gradient(135deg, #94a484 0%, #7a8a6f 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(148, 164, 132, 0.4);
        animation: reserveGlow 2s infinite;
        position: relative;
        overflow: hidden;
    }

    .reserve-btn.available::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        animation: reserveShine 3s infinite;
    }

    @keyframes reserveGlow {
        0%, 100% { 
            box-shadow: 0 4px 15px rgba(148, 164, 132, 0.4);
        }
        50% { 
            box-shadow: 0 6px 20px rgba(148, 164, 132, 0.6);
        }
    }

    @keyframes reserveShine {
        0% { left: -100%; }
        100% { left: 100%; }
    }

    .reserve-btn.available:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(148, 164, 132, 0.7);
        background: linear-gradient(135deg, #7a8a6f 0%, #94a484 100%);
        animation: none;
    }

    .reserve-btn.full {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        color: #6c757d;
        cursor: not-allowed;
        box-shadow: none;
    }

    .reserve-btn.almost-full {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #333;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
        animation: pulseButton 2s infinite;
    }

    @keyframes pulseButton {
        0%, 100% { 
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
        }
        50% { 
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.6);
        }
    }

    .cancel-btn {
        background: linear-gradient(135deg, #8c162a 0%, #a91d35 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(140, 22, 42, 0.4);
    }

    .cancel-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(140, 22, 42, 0.5);
        background: linear-gradient(135deg, #a91d35 0%, #8c162a 100%);
    }

    /* RESPONSIVE MEJORADO */
    @media (max-width: 768px) {
        .class-item {
            padding: 20px;
        }
        
        .class-header {
            flex-direction: column;
            gap: 15px;
        }
        
        .class-status {
            margin-left: 0;
            justify-content: flex-start;
        }
        
        .class-buttons {
            flex-direction: column;
            gap: 12px;
        }
        
        .class-time {
            font-size: 1.6rem;
        }
        
        .class-details {
            gap: 10px;
        }
    }

    @media (max-width: 480px) {
        .class-item {
            padding: 18px;
        }
        
        .class-time {
            font-size: 1.4rem;
        }
        
        .class-name {
            font-size: 1.2rem;
        }
        
        .reserve-btn, .cancel-btn {
            padding: 12px 20px;
            font-size: 0.9rem;
        }
    }
/* ========== CSS HÍBRIDO - FUNCIONAL Y BONITO ========== */

/* ASEGURAR QUE LAS CLASES APAREZCAN */
.classes-list {
    display: flex !important;
    flex-direction: column !important;
    gap: 20px !important;
    margin-top: 25px !important;
}

.class-item {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
    /* Dejar que el CSS original maneje el resto del diseño */
}

.class-time,
.class-name,
.class-instructor,
.class-details,
.class-buttons {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
    /* Dejar que el CSS original maneje colores y estilos */
}

.class-details {
    display: flex !important; /* Mantener flex para el layout horizontal */
}

.class-buttons {
    display: flex !important; /* Mantener flex para botones lado a lado */
}

.reserve-btn, .cancel-btn {
    display: block !important;
    cursor: pointer !important;
    /* Dejar que el CSS original maneje colores y efectos */
}
/* CORREGIR COLORES Y EFECTOS - VERSIÓN COMPLETA */

/* LÍNEA IZQUIERDA - TODAS VERDES */
.class-item.available {
    border-left-color: #94a484 !important;
}

.class-item.almost-full {
    border-left-color: #94a484 !important; /* CAMBIADO: ahora verde en lugar de amarillo */
}

.class-item.full {
    border-left-color: #dc3545 !important;
}

/* BOTÓN RESERVAR - CON BRILLO Y ANIMACIONES */
.reserve-btn.available {
    background: linear-gradient(135deg, #94a484 0%, #7a8a6f 100%) !important;
    color: white !important; /* TEXTO BLANCO */
    box-shadow: 0 4px 15px rgba(148, 164, 132, 0.4) !important;
    animation: reserveGlow 2s infinite !important; /* BRILLO RESTAURADO */
    position: relative !important;
    overflow: hidden !important;
}

/* EFECTO SHINE RESTAURADO */
.reserve-btn.available::after {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: -100% !important;
    width: 100% !important;
    height: 100% !important;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent) !important;
    animation: reserveShine 3s infinite !important;
}

.reserve-btn.almost-full {
    background: linear-gradient(135deg, #94a484 0%, #7a8a6f 100%) !important;
    color: white !important; /* TEXTO BLANCO */
    box-shadow: 0 4px 15px rgba(148, 164, 132, 0.4) !important;
    animation: reserveGlow 2s infinite !important; /* BRILLO RESTAURADO */
    position: relative !important;
    overflow: hidden !important;
}

/* EFECTO SHINE PARA ALMOST-FULL */
.reserve-btn.almost-full::after {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: -100% !important;
    width: 100% !important;
    height: 100% !important;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent) !important;
    animation: reserveShine 3s infinite !important;
}

.reserve-btn.available:hover {
    background: linear-gradient(135deg, #7a8a6f 0%, #94a484 100%) !important;
    box-shadow: 0 8px 25px rgba(148, 164, 132, 0.7) !important;
    animation: none !important; /* Quitar animación en hover */
}

.reserve-btn.almost-full:hover {
    background: linear-gradient(135deg, #7a8a6f 0%, #94a484 100%) !important;
    box-shadow: 0 8px 25px rgba(148, 164, 132, 0.7) !important;
    animation: none !important; /* Quitar animación en hover */
}

/* BOTÓN CANCELAR */
.cancel-btn {
    background: linear-gradient(135deg, #8c162a 0%, #a91d35 100%) !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(140, 22, 42, 0.4) !important;
}

.cancel-btn:hover {
    background: linear-gradient(135deg, #a91d35 0%, #8c162a 100%) !important;
    box-shadow: 0 8px 25px rgba(140, 22, 42, 0.5) !important;
}

/* BADGE DE DISPONIBILIDAD - VERDE CLARO LLAMATIVO */
.availability-badge.available,
.class-status {
    background: rgba(148, 164, 132, 0.2) !important; /* MÁS INTENSO */
    color: #5d6b52 !important; /* MÁS OSCURO PARA CONTRASTE */
    border: 2px solid rgba(148, 164, 132, 0.4) !important; /* BORDE MÁS VISIBLE */
    font-weight: 600 !important; /* MÁS LLAMATIVO */
}

/* SOMBRA DE BRILLO VERDE MUY CLARO */
.class-item::before {
    background: linear-gradient(90deg, transparent, rgba(148, 164, 132, 0.15), transparent) !important; /* VERDE MUY CLARO */
}

/* ASEGURAR QUE LAS ANIMACIONES KEYFRAMES FUNCIONEN */
@keyframes reserveGlow {
    0%, 100% { 
        box-shadow: 0 4px 15px rgba(148, 164, 132, 0.4) !important;
    }
    50% { 
        box-shadow: 0 6px 20px rgba(148, 164, 132, 0.6) !important;
    }
}

@keyframes reserveShine {
    0% { left: -100% !important; }
    100% { left: 100% !important; }
}
		/* ASEGURAR QUE EL MODAL FUNCIONE CORRECTAMENTE */
.yoga-modal {
    display: none !important; /* Oculto por defecto */
}

.yoga-modal.show {
    display: flex !important; /* Mostrar cuando tenga clase 'show' */
}

.modal-content {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
    transform: scale(1) !important;
}

/* ASEGURAR QUE LOS BOTONES DEL MODAL FUNCIONEN */
.modal-actions button,
.btn-confirm,
.btn-cancel {
    display: inline-block !important;
    opacity: 1 !important;
    visibility: visible !important;
    cursor: pointer !important;
}

/* ASEGURAR QUE EL FORMULARIO FUNCIONE */
.reservation-form,
.form-group,
.form-group input,
.form-group label {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.form-group input {
    cursor: text !important;
}

/* OVERLAY DEL MODAL */
.yoga-modal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    z-index: 9999 !important;
}
		/* DEBUG - FORZAR CLICK EVENTS */
.reserve-btn, .cancel-btn {
    pointer-events: auto !important;
    z-index: 10 !important;
}

.class-buttons {
    z-index: 10 !important;
    position: relative !important;
}
	
		/* ========== MODAL LIMPIO Y ELEGANTE ========== */
/* QUITAR FONDO GRIS COMPLETAMENTE */
.modal-class-info {
    margin-bottom: 30px !important;
    background: transparent !important;
    background-color: transparent !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
}

.modal-details-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 20px !important;
    margin-bottom: 30px !important;
    background: transparent !important;
    background-color: transparent !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
}
/* OVERLAY DEL MODAL */
.yoga-modal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(0, 0, 0, 0.7) !important;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    z-index: 9999 !important;
    backdrop-filter: blur(8px) !important;
    animation: fadeIn 0.3s ease-out !important;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* CONTENIDO DEL MODAL */
.modal-content {
    background: white !important;
    border-radius: 24px !important;
    padding: 40px !important;
    max-width: 500px !important;
    width: 90% !important;
    max-height: 90vh !important;
    overflow-y: auto !important;
    box-shadow: 
        0 20px 60px rgba(0, 0, 0, 0.3),
        0 4px 16px rgba(0, 0, 0, 0.1) !important;
    position: relative !important;
    animation: slideInScale 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

@keyframes slideInScale {
    from { 
        opacity: 0;
        transform: scale(0.8) translateY(20px);
    }
    to { 
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* BOTÓN CERRAR */
.close {
    position: absolute !important;
    top: 20px !important;
    right: 20px !important;
    width: 40px !important;
    height: 40px !important;
    background: rgba(148, 164, 132, 0.1) !important;
    border: none !important;
    border-radius: 50% !important;
    font-size: 1.5rem !important;
    color: #666 !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.3s ease !important;
}

.close:hover {
    background: rgba(148, 164, 132, 0.2) !important;
    color: #94a484 !important;
    transform: scale(1.1) !important;
}

/* TÍTULO DEL MODAL */
.modal-content h2 {
    font-size: 2.2rem !important;
    font-weight: 700 !important;
    color: #2c2c2c !important;
    margin: 0 0 30px 0 !important;
    text-align: center !important;
    background: linear-gradient(135deg, #94a484 0%, #7a8a6f 100%) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    background-clip: text !important;
}

/* INFORMACIÓN DE LA CLASE - DISEÑO LIMPIO */
.modal-class-info {
    margin-bottom: 30px !important;
    background: none !important;
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
}
.modal-class-title {
    font-size: 1.8rem !important;
    font-weight: 600 !important;
    color: #2c2c2c !important;
    margin: 0 0 25px 0 !important;
    text-align: center !important;
    padding-bottom: 15px !important;
    border-bottom: 2px solid rgba(148, 164, 132, 0.2) !important;
}

.modal-details-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 20px !important;
    margin-bottom: 30px !important;
    background: transparent !important; /* COMPLETAMENTE TRANSPARENTE */
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
}

.modal-detail-item {
    display: block !important;
    background: rgba(148, 164, 132, 0.08) !important; /* VERDE MUY CLARO */
    padding: 20px !important;
    border-radius: 12px !important;
    border: 1px solid rgba(148, 164, 132, 0.15) !important;
    transition: all 0.3s ease !important;
    text-align: center !important;
}

.modal-detail-item:hover {
    border-color: rgba(148, 164, 132, 0.4) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(148, 164, 132, 0.15) !important;
}

.detail-label {
    display: block !important;
    font-size: 0.8rem !important;
    color: #94a484 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    margin-bottom: 8px !important;
    font-weight: 600 !important;
}

.detail-value {
    display: block !important;
    font-size: 1.1rem !important;
    font-weight: 600 !important;
    color: #2c2c2c !important;
}

/* FORMULARIO */
.reservation-form {
    margin-bottom: 30px !important;
}

.form-group {
    margin-bottom: 20px !important;
}

.form-group label {
    display: block !important;
    font-size: 0.9rem !important;
    font-weight: 600 !important;
    color: #2c2c2c !important;
    margin-bottom: 8px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}

.form-group input {
    width: 100% !important;
    padding: 14px 16px !important;
    border: 2px solid rgba(148, 164, 132, 0.2) !important;
    border-radius: 12px !important;
    font-size: 1rem !important;
    transition: all 0.3s ease !important;
    background: white !important;
    box-sizing: border-box !important;
}

.form-group input:focus {
    outline: none !important;
    border-color: #94a484 !important;
    box-shadow: 0 0 0 4px rgba(148, 164, 132, 0.1) !important;
}

/* BOTONES DEL MODAL - SIN FONDO GRIS */
.modal-actions {
    display: flex !important;
    gap: 15px !important;
    justify-content: center !important;
    background: none !important;
    padding: 0 !important;
    border: none !important;
    border-radius: 0 !important;
}

.btn-cancel, .btn-confirm {
    flex: 1 !important;
    padding: 16px 24px !important;
    border: none !important;
    border-radius: 25px !important;
    font-size: 1rem !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    cursor: pointer !important;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative !important;
    overflow: hidden !important;
}

.btn-cancel {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%) !important;
    color: #6c757d !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}

.btn-cancel:hover {
    background: linear-gradient(135deg, #dee2e6 0%, #ced4da 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15) !important;
}

.btn-confirm {
    background: linear-gradient(135deg, #94a484 0%, #7a8a6f 100%) !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(148, 164, 132, 0.4) !important;
    animation: confirmGlow 2s infinite !important;
}

.btn-confirm:hover {
    background: linear-gradient(135deg, #7a8a6f 0%, #94a484 100%) !important;
    transform: translateY(-3px) !important;
    box-shadow: 0 8px 25px rgba(148, 164, 132, 0.6) !important;
    animation: none !important;
}

@keyframes confirmGlow {
    0%, 100% { 
        box-shadow: 0 4px 15px rgba(148, 164, 132, 0.4) !important;
    }
    50% { 
        box-shadow: 0 6px 20px rgba(148, 164, 132, 0.6) !important;
    }
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .modal-content {
        padding: 30px 20px !important;
        margin: 20px !important;
    }
    
    .modal-details-grid {
        grid-template-columns: 1fr !important;
        gap: 15px !important;
    }
    
    .modal-actions {
        flex-direction: column !important;
    }
}
		
		/* FORZAR MODALES OCULTOS POR DEFECTO */
#reservationModal,
#successModal {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
}

/* SOLO MOSTRAR CUANDO TENGAN CLASE 'show' */
#reservationModal.show,
#successModal.show {
    display: flex !important;
    opacity: 1 !important;
    visibility: visible !important;
}
		/* FORZAR TRANSPARENCIA TOTAL - AL FINAL DEL CSS */
.modal-class-info,
.modal-class-info > *,
.modal-details-grid,
.modal-details-grid > * {
    background: transparent !important;
    background-color: transparent !important;
    background-image: none !important;
}

/* SOLO LOS CARDS INDIVIDUALES TENDRÁN COLOR */
.modal-detail-item {
    background: rgba(148, 164, 132, 0.08) !important;
}
		/* ========== MODAL DE CONFIRMACIÓN DE CANCELACIÓN ELEGANTE ========== */
#cancelSuccessModal {
    display: none !important;
}

#cancelSuccessModal.show {
    display: flex !important;
}

.cancel-success-content {
    text-align: center;
    padding: 40px 30px;
    max-width: 500px;
    width: 90%;
    position: relative;
    background: linear-gradient(135deg, #fafbfa 0%, #f5f7f5 100%);
}

/* Logo de Kurunta Yoga */
.success-logo {
    margin-bottom: 20px;
}

.success-logo .yoga-logo-img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(148, 164, 132, 0.1);
    padding: 10px;
    transition: all 0.3s ease;
    animation: logoGlow 3s infinite;
}

@keyframes logoGlow {
    0%, 100% { 
        box-shadow: 0 4px 20px rgba(148, 164, 132, 0.3);
        transform: scale(1);
    }
    50% { 
        box-shadow: 0 8px 30px rgba(148, 164, 132, 0.5);
        transform: scale(1.05);
    }
}

/* Icono de despedida */
.farewell-icon {
    margin-bottom: 25px;
}

.icon-emoji {
    font-size: 3.5rem;
    display: block;
    animation: floatIcon 4s ease-in-out infinite;
}

@keyframes floatIcon {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.cancel-success-content h2 {
    color: #8c162a;
    font-size: 2.2rem;
    margin: 0 0 30px 0;
    font-weight: 700;
    background: linear-gradient(135deg, #8c162a 0%, #a91d35 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Mensaje de cancelación */
.cancel-message {
    background: rgba(140, 22, 42, 0.03);
    padding: 25px;
    border-radius: 16px;
    margin: 25px 0;
    border: 2px solid rgba(140, 22, 42, 0.1);
    backdrop-filter: blur(10px);
}

.user-greeting {
    font-size: 1.2rem;
    color: #2c2c2c;
    margin: 0 0 12px 0;
}

.user-greeting strong {
    color: #8c162a;
    font-weight: 700;
}

.cancellation-info {
    font-size: 1.1rem;
    color: #4a5568;
    margin: 12px 0;
}

.cancellation-info strong {
    color: #8c162a;
}

.class-details {
    font-size: 1rem;
    color: #666;
    margin: 12px 0 0 0;
    font-style: italic;
}

.class-details strong {
    color: #94a484;
    font-style: normal;
}

/* Mensaje de despedida */
.farewell-message {
    background: linear-gradient(135deg, rgba(148, 164, 132, 0.05) 0%, rgba(148, 164, 132, 0.1) 100%);
    padding: 30px 25px;
    border-radius: 16px;
    margin: 25px 0;
    border: 2px solid rgba(148, 164, 132, 0.2);
    position: relative;
}

.farewell-message::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(148, 164, 132, 0.1), transparent);
    border-radius: 14px;
    z-index: -1;
}

.main-message {
    font-size: 1.2rem;
    color: #2c2c2c;
    margin: 0 0 15px 0;
    font-weight: 500;
}

.hope-message {
    font-size: 1.3rem;
    color: #94a484;
    margin: 15px 0;
    font-weight: 600;
    text-shadow: 0 2px 4px rgba(148, 164, 132, 0.2);
}

.gratitude {
    font-size: 1rem;
    color: #666;
    margin: 15px 0 0 0;
    font-style: italic;
}

/* Botón de despedida elegante */
.btn-close-farewell {
    background: linear-gradient(135deg, #94a484 0%, #7a8a6f 100%);
    color: white;
    border: none;
    padding: 18px 40px;
    border-radius: 50px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1.1rem;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    min-width: 220px;
    box-shadow: 0 6px 20px rgba(148, 164, 132, 0.3);
    animation: farewellGlow 3s infinite;
    position: relative;
    overflow: hidden;
}

.btn-close-farewell::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
    transition: all 0.3s ease;
    border-radius: 50%;
    transform: translate(-50%, -50%);
}

.btn-close-farewell:hover::before {
    width: 200px;
    height: 200px;
}

.btn-close-farewell:hover {
    background: linear-gradient(135deg, #7a8a6f 0%, #94a484 100%);
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(148, 164, 132, 0.5);
    animation: none;
}

.btn-icon {
    margin-right: 8px;
    font-size: 1.2rem;
}

@keyframes farewellGlow {
    0%, 100% { 
        box-shadow: 0 6px 20px rgba(148, 164, 132, 0.3);
        transform: scale(1);
    }
    50% { 
        box-shadow: 0 8px 25px rgba(148, 164, 132, 0.5);
        transform: scale(1.02);
    }
}

/* Responsive para móviles */
@media (max-width: 768px) {
    .cancel-success-content {
        padding: 30px 20px;
        margin: 20px;
    }
    
    .success-logo .yoga-logo-img {
        width: 60px;
        height: 60px;
    }
    
    .cancel-success-content h2 {
        font-size: 1.8rem;
    }
    
    .user-greeting, .main-message {
        font-size: 1.1rem;
    }
    
    .hope-message {
        font-size: 1.2rem;
    }
    
    .btn-close-farewell {
        padding: 16px 30px;
        font-size: 1rem;
        min-width: 180px;
    }
}

/* ========== MODAL DE CONFIRMACIÓN DE RESERVA EXITOSA ========== */
#reservationSuccessModal {
    display: none !important;
}

#reservationSuccessModal.show {
    display: flex !important;
}

.reservation-success-content {
    text-align: center;
    padding: 40px 30px;
    max-width: 500px;
    width: 90%;
    position: relative;
    background: linear-gradient(135deg, #f9fdf9 0%, #f0f8f0 100%);
}

/* Logo de Kurunta Yoga para éxito */
.reservation-success-content .success-logo {
    margin-bottom: 20px;
}

.reservation-success-content .success-logo .yoga-logo-img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(148, 164, 132, 0.15);
    padding: 10px;
    transition: all 0.3s ease;
    animation: successLogoGlow 3s infinite;
}

@keyframes successLogoGlow {
    0%, 100% { 
        box-shadow: 0 4px 20px rgba(148, 164, 132, 0.4);
        transform: scale(1);
    }
    50% { 
        box-shadow: 0 8px 30px rgba(148, 164, 132, 0.6);
        transform: scale(1.08);
    }
}

/* Icono de celebración */
.celebration-icon {
    margin-bottom: 25px;
}

.celebration-icon .icon-emoji {
    font-size: 4rem;
    display: block;
    animation: celebrateIcon 2s ease-in-out infinite;
}

@keyframes celebrateIcon {
    0%, 100% { 
        transform: rotate(-5deg) scale(1);
    }
    25% { 
        transform: rotate(5deg) scale(1.1);
    }
    50% { 
        transform: rotate(-5deg) scale(1);
    }
    75% { 
        transform: rotate(5deg) scale(1.1);
    }
}

.reservation-success-content h2 {
    color: #94a484;
    font-size: 2.4rem;
    margin: 0 0 30px 0;
    font-weight: 700;
    background: linear-gradient(135deg, #94a484 0%, #7a8a6f 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 0 2px 10px rgba(148, 164, 132, 0.3);
}

/* Mensaje de éxito */
.success-message {
    background: linear-gradient(135deg, rgba(148, 164, 132, 0.05) 0%, rgba(148, 164, 132, 0.1) 100%);
    padding: 25px;
    border-radius: 16px;
    margin: 25px 0;
    border: 2px solid rgba(148, 164, 132, 0.2);
    backdrop-filter: blur(10px);
}

.welcome-greeting {
    font-size: 1.3rem;
    color: #2c2c2c;
    margin: 0 0 15px 0;
}

.welcome-greeting strong {
    color: #94a484;
    font-weight: 700;
}

.confirmation-info {
    font-size: 1.2rem;
    color: #4a5568;
    margin: 15px 0;
}

.confirmation-info strong {
    color: #94a484;
}

.success-message .class-details {
    font-size: 1.1rem;
    color: #666;
    margin: 15px 0 0 0;
    padding: 15px;
    background: rgba(148, 164, 132, 0.08);
    border-radius: 12px;
    border: 1px solid rgba(148, 164, 132, 0.15);
}

.success-message .class-details strong {
    color: #94a484;
}

/* Mensaje de bienvenida */
.welcome-message {
    background: linear-gradient(135deg, rgba(148, 164, 132, 0.08) 0%, rgba(148, 164, 132, 0.15) 100%);
    padding: 30px 25px;
    border-radius: 16px;
    margin: 25px 0;
    border: 2px solid rgba(148, 164, 132, 0.25);
    position: relative;
    overflow: hidden;
}

.welcome-message::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(148, 164, 132, 0.15), transparent);
    border-radius: 14px;
    z-index: -1;
}

.main-message {
    font-size: 1.3rem;
    color: #2c2c2c;
    margin: 0 0 15px 0;
    font-weight: 600;
}

.preparation-message {
    font-size: 1.4rem;
    color: #94a484;
    margin: 15px 0;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(148, 164, 132, 0.3);
    animation: preparationGlow 3s infinite;
}

@keyframes preparationGlow {
    0%, 100% { 
        color: #94a484;
        text-shadow: 0 2px 4px rgba(148, 164, 132, 0.3);
    }
    50% { 
        color: #7a8a6f;
        text-shadow: 0 2px 8px rgba(148, 164, 132, 0.5);
    }
}

.reminder {
    font-size: 1rem;
    color: #666;
    margin: 15px 0 0 0;
    font-style: italic;
}

/* Información de contacto */
.contact-info {
    background: rgba(148, 164, 132, 0.05);
    padding: 20px;
    border-radius: 12px;
    margin: 20px 0;
    border: 1px solid rgba(148, 164, 132, 0.15);
}

.email-sent {
    font-size: 1rem;
    color: #4a5568;
    margin: 0;
}

.email-sent strong {
    color: #94a484;
}

/* Botón de éxito elegante */
.btn-close-success {
    background: linear-gradient(135deg, #94a484 0%, #7a8a6f 100%);
    color: white;
    border: none;
    padding: 18px 40px;
    border-radius: 50px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1.2rem;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    min-width: 200px;
    box-shadow: 0 6px 20px rgba(148, 164, 132, 0.4);
    animation: successButtonGlow 3s infinite;
    position: relative;
    overflow: hidden;
}

.btn-close-success::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.4) 0%, transparent 70%);
    transition: all 0.3s ease;
    border-radius: 50%;
    transform: translate(-50%, -50%);
}

.btn-close-success:hover::before {
    width: 200px;
    height: 200px;
}

.btn-close-success:hover {
    background: linear-gradient(135deg, #7a8a6f 0%, #94a484 100%);
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(148, 164, 132, 0.6);
    animation: none;
}

.btn-close-success .btn-icon {
    margin-right: 8px;
    font-size: 1.3rem;
}

@keyframes successButtonGlow {
    0%, 100% { 
        box-shadow: 0 6px 20px rgba(148, 164, 132, 0.4);
        transform: scale(1);
    }
    50% { 
        box-shadow: 0 8px 25px rgba(148, 164, 132, 0.6);
        transform: scale(1.03);
    }
}

/* Responsive para móviles - Modal de éxito */
@media (max-width: 768px) {
    .reservation-success-content {
        padding: 30px 20px;
        margin: 20px;
    }
    
    .reservation-success-content .success-logo .yoga-logo-img {
        width: 60px;
        height: 60px;
    }
    
    .celebration-icon .icon-emoji {
        font-size: 3rem;
    }
    
    .reservation-success-content h2 {
        font-size: 2rem;
    }
    
    .welcome-greeting, .main-message {
        font-size: 1.2rem;
    }
    
    .preparation-message {
        font-size: 1.3rem;
    }
    
    .btn-close-success {
        padding: 16px 30px;
        font-size: 1.1rem;
        min-width: 180px;
    }
}

/* ========== FIN CSS HÍBRIDO ========== */
		/* ========== MODAL DE CANCELACIÓN ========== */
#cancelModal {
    display: none !important;
}

#cancelModal.show {
    display: flex !important;
}

.cancel-warning {
    text-align: center;
    padding: 20px;
    background: rgba(220, 53, 69, 0.05);
    border-radius: 12px;
    border: 2px solid rgba(220, 53, 69, 0.1);
    margin: 20px 0;
}

.warning-icon {
    font-size: 2.5rem;
    margin-bottom: 10px;
    animation: pulse 2s infinite;
}

.cancel-warning p {
    margin: 8px 0;
    color: #2c2c2c;
}

.cancel-warning p:first-of-type {
    font-weight: 600;
    color: #dc3545;
}

.cancel-info {
    background: rgba(148, 164, 132, 0.05);
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    text-align: center;
}

.cancel-info p {
    margin: 0;
    color: #555;
    font-size: 1.1rem;
}

.cancel-form {
    margin: 20px 0;
}

.btn-cancel-action {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    border: none;
    padding: 16px 24px;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    flex: 1;
}

.btn-cancel-action:hover {
    background: linear-gradient(135deg, #5a6268 0%, #6c757d 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(108, 117, 125, 0.3);
}

.btn-confirm-cancel {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border: none;
    padding: 16px 24px;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    flex: 1;
    animation: dangerGlow 2s infinite;
}

.btn-confirm-cancel:hover {
    background: linear-gradient(135deg, #c82333 0%, #dc3545 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
    animation: none;
}

@keyframes dangerGlow {
    0%, 100% { 
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
    }
    50% { 
        box-shadow: 0 6px 20px rgba(220, 53, 69, 0.5);
    }
}

/* Asegurar que el modal se vea correctamente */
#cancelModal .modal-content {
    max-width: 500px;
    width: 90%;
}

#cancelModal .modal-actions {
    display: flex !important;
    gap: 15px !important;
}
    </style>

<script>
	/**
 * Returns a Date representing the start of the ISO week (Monday) for the given date.
 * If you want Sunday as first day, change (dow + 6)%7 logic to just dow.
 *
 * @param {Date|string|number} d
 * @returns {Date}
 */
function getWeekStart(d) {
  // normalize to a Date
  const date = d instanceof Date ? new Date(d) : new Date(d);
  // ISO week: Monday is day-1, Sunday is day-0 → shift Sunday to 6
  const dow = date.getDay();
  const isoDay = (dow + 6) % 7; 
  date.setHours(0,0,0,0);
  date.setDate(date.getDate() - isoDay);
  return date;
}
document.addEventListener("DOMContentLoaded", function() {
    // Evitar múltiples inicializaciones
    if (window.yogaBookingPublicInitialized) {
        console.log("🚫 Sistema público ya inicializado");
        return;
    }
    window.yogaBookingPublicInitialized = true;

    console.log("🧘‍♀️ Sistema Público Kurunta Yoga v3 - INICIADO");

let currentWeekStart = getWeekStart(selectedDate); 
	let selectedDate = new Date();
    let currentFilter = "all";

    // ========== FUNCIONES AUXILIARES (PRIMERO) ==========
    function formatTime(timeString) {
        const time = new Date('2000-01-01 ' + timeString);
        return time.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    }

    function filterClasses(classes) {
        console.log("🎯 FILTRO EJECUTADO con:", currentFilter);
        if (currentFilter === "all") return classes;
        
        return classes.filter(function(classItem) {
            const hour = parseInt(classItem.time.split(":")[0]);
            switch (currentFilter) {
                case "morning":   return hour >= 6  && hour < 12;
                case "afternoon": return hour >= 12 && hour < 18;
                case "evening":   return hour >= 18 && hour <= 23;
                default:          return true;
            }
        });
    }

    function getStatusClass(classItem) {
        if (classItem.available_spots <= 0) return "full";
        if (classItem.available_spots <= 3) return "almost-full";
        return "available";
    }

    function getStatusText(classItem) {
        if (classItem.available_spots <= 0) return "Completo";
        return classItem.available_spots + "/" + classItem.max_spots + " disponibles";
    }

    // ========== FUNCIONES PRINCIPALES ==========
    function init() {
        setCurrentWeek();
        generateDayNumbers();
        loadClassesFromDB();
        setupEventListeners();
    }

    function setCurrentWeek() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const startOfWeek = new Date(today);
        const day = today.getDay();
        const diff = today.getDate() - day;
        startOfWeek.setDate(diff);
        startOfWeek.setHours(0, 0, 0, 0);
        
        currentWeekStart = startOfWeek;
        selectedDate = new Date(today);
        
        console.log("📅 Fecha hoy:", today.toISOString().split('T')[0]);
        console.log("📅 Fecha seleccionada:", selectedDate.toISOString().split('T')[0]);
    }

    function generateDayNumbers() {
        const dayNumbers = document.getElementById("dayNumbers");
        const dateRange  = document.getElementById("dateRange");
        
        if (!dayNumbers || !dateRange) return;

        dayNumbers.innerHTML = "";
        
        const endOfWeek = new Date(currentWeekStart);
        endOfWeek.setDate(currentWeekStart.getDate() + 6);
        
        const startStr = currentWeekStart.toLocaleDateString("es-ES", {
            day: "2-digit",
            month: "short"
        });
        const endStr = endOfWeek.toLocaleDateString("es-ES", {
            day: "2-digit",
            month: "short",
            year: "numeric"
        });
        dateRange.textContent = startStr + " - " + endStr;

        for (let i = 0; i < 7; i++) {
            const date = new Date(currentWeekStart);
            date.setDate(currentWeekStart.getDate() + i);
            date.setHours(0, 0, 0, 0);
            
            const dayElement = document.createElement("div");
            dayElement.className = "day-number";
            dayElement.textContent = date.getDate().toString().padStart(2, "0");
            
            const localDate = new Date(date.getTime() - (date.getTimezoneOffset() * 60000));
            dayElement.dataset.date = localDate.toISOString().split("T")[0];
            
            if (isToday(date)) {
                dayElement.classList.add("today");
            }
            if (isSameDay(date, selectedDate)) {
                dayElement.classList.add("selected");
            }
            dayNumbers.appendChild(dayElement);
        }
    }

    function isToday(date) {
        const today = new Date();
        return isSameDay(date, today);
    }

    function isSameDay(date1, date2) {
        return date1.toDateString() === date2.toDateString();
    }

    function loadClassesFromDB() {
    const classList = document.getElementById("classList");
    if (!classList) return;

    const dateString = selectedDate.toISOString().split("T")[0];
    console.log("📡 Cargando clases reales para:", dateString);

    classList.innerHTML = '<div class="loading-classes"><p>Cargando clases...</p></div>';

    fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'get_yoga_classes_public',
            date: dateString,
            filter: currentFilter,
            nonce: '<?php echo wp_create_nonce("yoga_public_nonce"); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log("📊 Respuesta del servidor:", data);

        if (data && data.success && data.data) {
            const classes = data.data.classes || [];
            console.log("✅ DATOS VÁLIDOS RECIBIDOS:", classes.length, "clases");
            displayClasses(classes);
            console.log("🎯 displayClasses() EJECUTADA");
        } else {
            console.log("❌ DATOS INVÁLIDOS:", data);
            classList.innerHTML = '<div class="no-classes"><p>Error al cargar las clases. Intenta refrescar la página.</p></div>';
        }
    })
    .catch(error => {
        console.error("❌ Error AJAX:", error);
        classList.innerHTML = '<div class="error-classes"><p>Error de conexión. Intenta recargar la página.</p></div>';
    });
}

    function displayClasses(classes) {
        const classList = document.getElementById("classList");
        
        console.log("🔍 DEBUGGING displayClasses:", classes.length);

        if (!classes || classes.length === 0) {
            classList.innerHTML = '<div class="no-classes"><p>No hay clases disponibles para ' + 
                selectedDate.toLocaleDateString("es-ES", {
                    weekday: "long",
                    day: "numeric",
                    month: "long"
                }) + '.</p><p>Selecciona otro día para ver las clases disponibles.</p></div>';
            return;
        }

        const filteredClasses = filterClasses(classes);
        
        console.log("✅ Clases después del filtro:", filteredClasses.length);

        if (filteredClasses.length === 0) {
            classList.innerHTML = '<div class="no-classes"><p>No hay clases en el horario seleccionado.</p><p>Cambia el filtro para ver más opciones.</p></div>';
            return;
        }

        classList.innerHTML = filteredClasses.map(function(classItem) {
            const statusClass = getStatusClass(classItem);
            const statusText  = getStatusText(classItem);
            return `
                <div class="class-item ${statusClass}" data-class-id="${classItem.class_id}">
                    <div class="class-time">${formatTime(classItem.time)}</div>
                    <div class="class-name">${classItem.name}</div>
                    <div class="class-instructor">${classItem.instructor}</div>
                    <div class="class-details">
                        <span class="class-duration">⏱️ ${classItem.duration} min</span>
                        <span class="class-status status-${statusClass}">${statusText}</span>
                        ${classItem.is_recurring == 1 ? '<span class="recurring-badge">Clase Regular</span>' : ''}
                    </div>
                    <div class="class-buttons">
                        <button class="reserve-btn ${statusClass}" ${classItem.available_spots <= 0 ? 'disabled' : ''}>
                            ${classItem.available_spots <= 0 ? 'COMPLETO' : 'RESERVAR'}
                        </button>
                        <button class="cancel-btn" data-class-id="${classItem.class_id}" data-class-name="${classItem.name}">
                            CANCELAR RESERVA
                        </button>
                    </div>
                </div>
            `;
        }).join("");

        // Event listeners para botones de cancelar
        document.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const classId   = this.dataset.classId;
                const className = this.dataset.className;
                openCancelModal(classId, className);
            });
        });
    }

    // ========== EVENT LISTENERS ==========
    function setupEventListeners() {
        // Navegación de semana
        document.getElementById("prevWeek")?.addEventListener("click", () => {
            currentWeekStart.setDate(currentWeekStart.getDate() - 7);
            generateDayNumbers();
            loadClassesFromDB();
        });
        document.getElementById("nextWeek")?.addEventListener("click", () => {
            currentWeekStart.setDate(currentWeekStart.getDate() + 7);
            generateDayNumbers();
            loadClassesFromDB();
        });

        // Selección de día
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("day-number")) {
                document.querySelectorAll(".day-number.selected")
                        .forEach(el => el.classList.remove("selected"));
                e.target.classList.add("selected");
                selectedDate = new Date(e.target.dataset.date);
                loadClassesFromDB();
            }
        });

        // Filtros de tiempo
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("filter-btn")) {
                document.querySelectorAll(".filter-btn.active")
                        .forEach(btn => btn.classList.remove("active"));
                e.target.classList.add("active");
                currentFilter = e.target.dataset.filter;
                loadClassesFromDB();
            }
        });

        // Botón reservar
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("reserve-btn") && !e.target.disabled) {
                const classItem = e.target.closest(".class-item");
                openReservationModal(classItem.dataset.classId);
            }
        });

        // Cerrar modales (✕ o botón "Cancelar")
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("close") 
             || e.target.classList.contains("btn-cancel")) {
                closeModals();
            }
        });

        // Confirmar reserva
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("btn-confirm")) {
                const modal = e.target.closest(".yoga-modal");
                if (modal && modal.id === "reservationModal") {
                    confirmReservation();
                } else {
                    closeModals();
                }
            }
        });
    }

    // Inicializar todo
    init();

    // FUNCIÓN PARA ABRIR EL MODAL DE RESERVA
    function openReservationModal(classData) {
        console.log('ABRIENDO MODAL:', classData);
        const modal        = document.getElementById('reservationModal');
        const modalDetails = document.getElementById('modalDetails');
        
        if (!modal || !modalDetails) {
            console.error('Modal no encontrado');
            return;
        }

        let className  = 'Clase de Yoga';
        let instructor = 'Instructor';
        let date       = 'Fecha';
        let time       = 'Hora';
        let duration   = '60';
        
        if (typeof classData === 'string') {
            const classElement = document.querySelector(`[data-class-id="${classData}"]`);
            if (classElement) {
                className = classElement.querySelector('.class-name')?.textContent || className;
                instructor = classElement.querySelector('.class-instructor')?.textContent || instructor;
                time       = classElement.querySelector('.class-time')?.textContent || time;
                duration   = classElement.querySelector('.class-duration')?.textContent
                                 ?.replace('⏱️ ', '')
                                 ?.replace(' min', '') || duration;
                
                const parts = classData.split('_');
                if (parts.length >= 2) {
                    date = parts[1]; // Formato: YYYY-MM-DD
                }
            }
        }
        
        modalDetails.innerHTML = `
            <div class="modal-class-info">
                <h3 class="modal-class-title">${className}</h3>
                <div class="modal-details-grid">
                    <div class="modal-detail-item">
                        <span class="detail-label">Instructor</span>
                        <span class="detail-value">${instructor}</span>
                    </div>
                    <div class="modal-detail-item">
                        <span class="detail-label">Fecha</span>
                        <span class="detail-value">${date}</span>
                    </div>
                    <div class="modal-detail-item">
                        <span class="detail-label">Hora</span>
                        <span class="detail-value">${time}</span>
                    </div>
                    <div class="modal-detail-item">
                        <span class="detail-label">Duración</span>
                        <span class="detail-value">${duration} min</span>
                    </div>
                </div>
            </div>
        `;
        
        modal.style.display = 'flex';
        modal.classList.add('show');
        console.log('MODAL ABIERTO CON DATOS:', { className, instructor, date, time, duration });
    }

    // FUNCIÓN PARA CERRAR MODALES
    function closeModal() {
        const modal        = document.getElementById('reservationModal');
        const successModal = document.getElementById('successModal');
        
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
        if (successModal) {
            successModal.style.display = 'none';
            successModal.classList.remove('show');
        }
        console.log('MODAL CERRADO');
    }

    // FUNCIÓN MEJORADA PARA CONFIRMAR RESERVA
    function confirmReservation() {
        const confirmBtn = document.querySelector('.btn-confirm');
        if (!confirmBtn || confirmBtn.disabled) {
            console.log('⚠️ Reserva en proceso o botón no encontrado');
            return;
        }
        const originalText = confirmBtn.textContent;
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'PROCESANDO...';

        const name  = document.getElementById('userName') ?.value?.trim();
        const email = document.getElementById('userEmail')?.value?.trim();
        const phone = document.getElementById('userPhone')?.value?.trim();
        if (!name || !email) {
            alert('Por favor completa el nombre y email');
            confirmBtn.disabled = false;
            confirmBtn.textContent = originalText;
            return;
        }

        const classTitle    = document.querySelector('.modal-class-title')?.textContent;
        const detailValues  = document.querySelectorAll('.detail-value');
        const classDate     = detailValues[1]?.textContent;
        const classTime     = detailValues[2]?.textContent;
        const activeClass   = document.querySelector('.class-item[data-class-id]');
        const classId       = activeClass ?.getAttribute('data-class-id');

        console.log('🚀 ENVIANDO RESERVA:', { name, email, phone, classId, classDate, classTime });

        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action:         'yoga_book_class',
                class_id:       classId,
                participant_name:  name,
                participant_email: email,
                participant_phone: phone,
                class_date:     classDate,
                class_time:     classTime,
                nonce:          '<?php echo wp_create_nonce("yoga_public_nonce"); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('✅ RESPUESTA DEL SERVIDOR:', data);
            if (data && data.success) {
                closeModal();
                
                // Mostrar modal bonito de confirmación
                showReservationSuccessModal({
                    user_name: name,
                    class_name: classTitle,
                    class_date: classDate,
                    class_time: classTime,
                    user_email: email
                });
                
                setTimeout(() => {
                    if (typeof loadClassesFromDB === 'function') {
                        loadClassesFromDB();
                    }
                }, 1000);
            } else {
                alert(data?.data || 'Error al procesar la reserva');
                confirmBtn.disabled = false;
                confirmBtn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('❌ ERROR:', error);
            alert('Error de conexión. Intenta de nuevo.');
            confirmBtn.disabled = false;
            confirmBtn.textContent = originalText;
        });
    }

    // ========== FUNCIONES PARA CANCELAR RESERVAS ==========
    function openCancelModal(classId, className) {
        console.log('🗑️ ABRIENDO MODAL CANCELACIÓN:', classId, className);
        
        const cancelModal = document.getElementById('cancelModal');
        const cancelClassName = document.getElementById('cancelClassName');
        
        if (!cancelModal) {
            console.error('❌ Modal de cancelación no encontrado');
            return;
        }
        
        // Actualizar el nombre de la clase
        if (cancelClassName) {
            cancelClassName.textContent = className || 'Clase de Yoga';
        }
        
        // Limpiar el campo de email
        const cancelEmail = document.getElementById('cancelEmail');
        if (cancelEmail) {
            cancelEmail.value = '';
        }
        
        // Mostrar el modal
        cancelModal.style.display = 'flex';
        cancelModal.classList.add('show');
        
        // Configurar event listeners para este modal específico
        setupCancelModalEvents(classId, className);
        
        console.log('✅ Modal de cancelación abierto');
    }

    function closeCancelModal() {
        const cancelModal = document.getElementById('cancelModal');
        if (cancelModal) {
            cancelModal.style.display = 'none';
            cancelModal.classList.remove('show');
            
            // Limpiar el campo de email
            const cancelEmail = document.getElementById('cancelEmail');
            if (cancelEmail) {
                cancelEmail.value = '';
            }
        }
        console.log('✅ Modal de cancelación cerrado');
    }

    function setupCancelModalEvents(classId, className) {
        // Botón "No, mantener reserva"
        const btnCancelAction = document.querySelector('.btn-cancel-action');
        if (btnCancelAction) {
            btnCancelAction.onclick = closeCancelModal;
        }
        
        // Botón "Sí, cancelar reserva"
        const btnConfirmCancel = document.querySelector('.btn-confirm-cancel');
        if (btnConfirmCancel) {
            btnConfirmCancel.onclick = () => confirmCancelReservation(classId, className);
        }
        
        // Botón cerrar (X)
        const closeBtn = document.querySelector('#cancelModal .close');
        if (closeBtn) {
            closeBtn.onclick = closeCancelModal;
        }
    }

   function confirmCancelReservation(classId, className) {
    const email = document.getElementById('cancelEmail')?.value?.trim();
    
    if (!email) {
        alert('Por favor ingresa tu email para confirmar la cancelación');
        return;
    }
    
    if (!email.includes('@')) {
        alert('Por favor ingresa un email válido');
        return;
    }
    
    // 🔥 CORREGIR EL CLASS_ID - EXTRAER SOLO EL NÚMERO
    let realClassId = classId;
    if (typeof classId === 'string' && classId.includes('_')) {
        // Si el classId viene como "Yin Yoga_2025-06-19_1000_1749812912"
        // Extraer solo la última parte que es el ID real
        const parts = classId.split('_');
        realClassId = parts[parts.length - 1]; // Tomar la última parte
    }
    
    console.log('🔧 Class ID original:', classId);
    console.log('🔧 Class ID corregido:', realClassId);
    
    const cancelBtn = document.querySelector('.btn-confirm-cancel');
    const originalText = cancelBtn.textContent;
    
    // Mostrar estado de carga
    cancelBtn.textContent = 'CANCELANDO...';
    cancelBtn.disabled = true;
    
    console.log('🗑️ PROCESANDO CANCELACIÓN:', { 
        classId: realClassId, 
        className: className, 
        email: email 
    });
    
    // Llamada AJAX real para cancelar la reserva
    fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'yoga_cancel_reservation',
            class_id: realClassId, // 🔥 USAR EL ID CORREGIDO
            email: email,
            nonce: '<?php echo wp_create_nonce("yoga_public_nonce"); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ RESPUESTA CANCELACIÓN:', data);
        
        if (data && data.success) {
            // Cerrar modal de cancelación
            closeCancelModal();
            
            // Mostrar modal de confirmación con datos completos
            showCancelSuccessModal(data.data);
            
            // Recargar las clases para mostrar disponibilidad actualizada
            setTimeout(() => {
                if (typeof loadClassesFromDB === 'function') {
                    loadClassesFromDB();
                }
            }, 1000);
            
        } else {
            const errorMsg = data?.data || 'Error al cancelar la reserva. Verifica que el email sea correcto.';
            console.error('❌ ERROR DEL SERVIDOR:', errorMsg);
            alert(errorMsg);
        }
        
        // Restaurar el botón
        cancelBtn.textContent = originalText;
        cancelBtn.disabled = false;
    })
    .catch(error => {
        console.error('❌ ERROR CANCELACIÓN:', error);
        alert('Error de conexión. Intenta de nuevo.');
        
        // Restaurar el botón
        cancelBtn.textContent = originalText;
        cancelBtn.disabled = false;
    });
}

    // Nueva función para mostrar el modal de confirmación de cancelación
    function showCancelSuccessModal(cancelData) {
        // Crear el modal dinámicamente con diseño elegante
        const modalHtml = `
            <div id="cancelSuccessModal" class="yoga-modal show" style="display: flex;">
                <div class="modal-content cancel-success-content">
                    <button class="close" onclick="closeCancelSuccessModal()">&times;</button>
                    
                    <!-- Logo de Kurunta Yoga -->
                    <div class="success-logo">
                        <img src="https://kuruntayoga.com.mx/wp-content/uploads/2025/06/icono.png" alt="Kurunta Yoga" class="yoga-logo-img">
                    </div>
                    
                    <!-- Icono de despedida -->
                    <div class="farewell-icon">
                        <span class="icon-emoji">🧘‍♀️</span>
                    </div>
                    
                    <h2>Reserva Cancelada</h2>
                    
                    <div class="cancel-message">
                        <p class="user-greeting">Hola <strong>${cancelData.user_name || 'querido estudiante'}</strong>,</p>
                        <p class="cancellation-info">Tu reserva para <strong>${cancelData.class_name || 'la clase'}</strong> ha sido cancelada exitosamente.</p>
                    </div>
                    
                    <!-- Bloque de información de la clase mejorado -->
                    ${cancelData.class_date ? `
                    <div class="class-info-block">
                        <div class="class-info-header">
                            <span class="calendar-icon">📅</span>
                            <span class="info-title">Clase cancelada</span>
                        </div>
                        <div class="class-info-details">
                            <div class="date-time-container">
                                <div class="date-info">
                                    <span class="date-label">Fecha</span>
                                    <span class="date-value">${formatDate(cancelData.class_date)}</span>
                                </div>
                                <div class="time-info">
                                    <span class="time-label">Hora</span>
                                    <span class="time-value">${formatTime(cancelData.class_time)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    
                    <div class="farewell-message">
                        <p class="main-message">Lamentamos que no puedas acompañarnos en esta ocasión.</p>
                        <p class="hope-message">✨ Esperamos verte pronto en nuestra próxima clase ✨</p>
                        <p class="gratitude">Gracias por formar parte de la comunidad Kurunta Yoga</p>
                    </div>
                    
                    <div class="modal-actions">
                        <button class="btn-confirm btn-close-farewell" onclick="closeCancelSuccessModal()">
                            <span class="btn-icon">🙏</span>
                            Hasta pronto
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Eliminar modal existente si existe
        const existingModal = document.getElementById('cancelSuccessModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Agregar el nuevo modal al body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Agregar event listener para cerrar con click fuera del modal
        const modal = document.getElementById('cancelSuccessModal');
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeCancelSuccessModal();
            }
        });
        
        console.log('✅ Modal de confirmación de cancelación mostrado');
    }
    
    // Funciones auxiliares para formatear fecha y hora
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }
    
    function formatTime(timeString) {
        if (!timeString) return '';
        const [hours, minutes] = timeString.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
    }

    // ========== MODAL DE CONFIRMACIÓN DE RESERVA EXITOSA ==========
    function showReservationSuccessModal(reservationData) {
        // Crear el modal dinámicamente con diseño elegante
        const modalHtml = `
            <div id="reservationSuccessModal" class="yoga-modal show" style="display: flex;">
                <div class="modal-content reservation-success-content">
                    <button class="close" onclick="closeReservationSuccessModal()">&times;</button>
                    
                    <!-- Logo de Kurunta Yoga -->
                    <div class="success-logo">
                        <img src="https://kuruntayoga.com.mx/wp-content/uploads/2025/06/icono.png" alt="Kurunta Yoga" class="yoga-logo-img">
                    </div>
                    
                    <!-- Icono de celebración -->
                    <div class="celebration-icon">
                        <span class="icon-emoji">🎉</span>
                    </div>
                    
                    <h2>¡Reserva Confirmada!</h2>
                    
                    <div class="success-message">
                        <p class="welcome-greeting">¡Hola <strong>${reservationData.user_name || 'querido estudiante'}</strong>!</p>
                        <p class="confirmation-info">Tu reserva para <strong>${reservationData.class_name || 'la clase'}</strong> ha sido confirmada exitosamente.</p>
                    </div>
                    
                    <!-- Bloque de información de la clase mejorado -->
                    ${reservationData.class_date ? `
                    <div class="class-info-block">
                        <div class="class-info-header">
                            <span class="calendar-icon">📅</span>
                            <span class="info-title">Detalles de tu clase</span>
                        </div>
                        <div class="class-info-details">
                            <div class="date-time-container">
                                <div class="date-info">
                                    <span class="date-label">Fecha</span>
                                    <span class="date-value">${formatDate(reservationData.class_date)}</span>
                                </div>
                                <div class="time-info">
                                    <span class="time-label">Hora</span>
                                    <span class="time-value">${formatTime(reservationData.class_time)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    
                    <div class="welcome-message">
                        <p class="main-message">¡Estamos emocionados de tenerte en nuestra clase!</p>
                        <p class="preparation-message">🧘‍♀️ Prepárate para una experiencia transformadora 🧘‍♀️</p>
                        <p class="reminder">Te esperamos con tu mat y una botella de agua</p>
                    </div>
                    
                    <div class="contact-info">
                        <p class="email-sent">📧 Te hemos enviado un email de confirmación a <strong>${reservationData.user_email}</strong></p>
                    </div>
                    
                    <div class="modal-actions">
                        <button class="btn-confirm btn-close-success" onclick="closeReservationSuccessModal()">
                            <span class="btn-icon">🙏</span>
                            ¡Perfecto!
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Eliminar modal existente si existe
        const existingModal = document.getElementById('reservationSuccessModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Agregar el nuevo modal al body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Agregar event listener para cerrar con click fuera del modal
        const modal = document.getElementById('reservationSuccessModal');
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeReservationSuccessModal();
            }
        });
        
        console.log('✅ Modal de confirmación de reserva mostrado');
    }

    function closeReservationSuccessModal() {
        const successModal = document.getElementById('reservationSuccessModal');
        if (successModal) {
            successModal.style.display = 'none';
            successModal.classList.remove('show');
            successModal.remove();
        }
        console.log('✅ Modal de confirmación de reserva cerrado');
    }

    // Función para cerrar el modal de confirmación
    function closeCancelSuccessModal() {
        const successModal = document.getElementById('cancelSuccessModal');
        if (successModal) {
            successModal.style.display = 'none';
            successModal.classList.remove('show');
        }
        console.log('✅ Modal de confirmación de cancelación cerrado');
    }

    // ========= FUNCIONES GLOBALES DE CIERRE ==========
    function closeModals() {
        closeModal();                    // Cerrar modal de reserva y modal de éxito
        closeCancelModal();              // Cerrar modal de cancelación
        closeCancelSuccessModal();       // Cerrar modal de confirmación de cancelación
        closeReservationSuccessModal();  // Cerrar modal de confirmación de reserva
    }

}); // ÚNICO "DOMContentLoaded" CIERRE AQUÍ
</script>
    <?php
    return ob_get_clean();
}

add_shortcode('yoga_reservas', 'yoga_reservas_shortcode');

// ========== FUNCIÓN AJAX PARA CANCELAR RESERVAS ==========
function yoga_cancel_reservation_ajax() {
    // FORZAR LOG PARA VER SI SE EJECUTA ESTA FUNCIÓN
    error_log('=== EJECUTANDO FUNCIÓN DEL ARCHIVO PAGINA DE RESERVAS ===');
    file_put_contents('/tmp/yoga_debug.log', "=== EJECUTANDO FUNCIÓN PAGINA DE RESERVAS ===\n", FILE_APPEND);
    
    error_log('=== YOGA CANCEL DEBUG START ===');
    error_log('POST data: ' . json_encode($_POST));
    
    // Verificar nonce de seguridad
    if (!wp_verify_nonce($_POST['nonce'], 'yoga_public_nonce')) {
        wp_die(json_encode(array('success' => false, 'data' => 'Error de seguridad')));
    }

    global $wpdb;
    
    $class_id = sanitize_text_field($_POST['class_id']);
    $email = sanitize_email($_POST['email']);
    
    // Debug información
    error_log('Class ID: ' . $class_id);
    error_log('Email: ' . $email);
    
    // Validar datos
    if (empty($class_id) || empty($email)) {
        wp_die(json_encode(array('success' => false, 'data' => 'Datos incompletos')));
    }

    // Buscar tabla existente con diferentes nombres posibles
    $possible_tables = [
        $wpdb->prefix . 'yoga_reservations',
        $wpdb->prefix . 'reservations', 
        $wpdb->prefix . 'yoga_bookings',
        $wpdb->prefix . 'bookings',
        $wpdb->prefix . 'yoga_classes_bookings'
    ];
    
    $table_name = null;
    foreach ($possible_tables as $test_table) {
        if ($wpdb->get_var("SHOW TABLES LIKE '$test_table'") == $test_table) {
            $table_name = $test_table;
            break;
        }
    }
    
    if (!$table_name) {
        wp_die(json_encode(['success' => false, 'data' => 'No se encontró tabla de reservas']));
    }
    
    error_log('Tabla encontrada: ' . $table_name);
    
    // Obtener estructura de la tabla existente
    $columns = $wpdb->get_results("DESCRIBE $table_name");
    $column_names = array_column($columns, 'Field');
    
    error_log('Columnas disponibles: ' . json_encode($column_names));
    
    // Mapear nombres de columnas
    $column_mapping = [
        'email' => ['participant_email', 'email', 'client_email', 'user_email', 'customer_email'],
        'name' => ['participant_name', 'name', 'client_name', 'user_name', 'customer_name'],
        'class_id' => ['class_id', 'classe_id', 'yoga_class_id', 'booking_class_id'],
        'status' => ['status', 'booking_status', 'reservation_status', 'state'],
        'id' => ['id', 'reservation_id', 'booking_id']
    ];
    
    // Encontrar las columnas correctas
    $mapped_columns = [];
    foreach ($column_mapping as $our_field => $possible_names) {
        foreach ($possible_names as $possible_name) {
            if (in_array($possible_name, $column_names)) {
                $mapped_columns[$our_field] = $possible_name;
                break;
            }
        }
    }
    
    error_log('Mapeo de columnas: ' . json_encode($mapped_columns));
    
    // Verificar que tenemos las columnas esenciales
    if (!isset($mapped_columns['email']) || !isset($mapped_columns['class_id'])) {
        wp_die(json_encode(['success' => false, 'data' => 'Estructura de tabla incompatible']));
    }

    // Construir query con nombres de columnas correctos
    $email_column = $mapped_columns['email'];
    $class_id_column = $mapped_columns['class_id'];
    $status_column = isset($mapped_columns['status']) ? $mapped_columns['status'] : 'status';
    $id_column = isset($mapped_columns['id']) ? $mapped_columns['id'] : 'id';
    
    // Buscar la reserva usando los nombres correctos de columnas
    $query = "SELECT * FROM $table_name WHERE $class_id_column = %s AND $email_column = %s";
    
    // Agregar condición de status si la columna existe
    if (in_array($status_column, $column_names)) {
        $query .= " AND $status_column = 'confirmed'";
        $reservation = $wpdb->get_row($wpdb->prepare($query, $class_id, $email));
    } else {
        $reservation = $wpdb->get_row($wpdb->prepare($query, $class_id, $email));
    }
    
    error_log('Query ejecutada: ' . $wpdb->prepare($query, $class_id, $email));
    error_log('Reserva encontrada: ' . ($reservation ? json_encode($reservation) : 'NO'));
    
    if (!$reservation) {
        wp_die(json_encode(array('success' => false, 'data' => 'No se encontró una reserva activa con ese email para esta clase')));
    }

    // Cancelar la reserva
    $update_data = [];
    $update_format = [];
    $where_condition = [];
    $where_format = [];
    
    // Actualizar status si la columna existe
    if (in_array($status_column, $column_names)) {
        $update_data[$status_column] = 'cancelled';
        $update_format[] = '%s';
    }
    
    // Agregar timestamp de cancelación si existe una columna apropiada
    $timestamp_columns = ['cancelled_at', 'updated_at', 'modified_at'];
    foreach ($timestamp_columns as $ts_col) {
        if (in_array($ts_col, $column_names)) {
            $update_data[$ts_col] = current_time('mysql');
            $update_format[] = '%s';
            break;
        }
    }
    
    // Definir condición WHERE usando el ID correcto
    $where_condition[$id_column] = $reservation->{$id_column};
    $where_format[] = '%d';
    
    $cancelled = $wpdb->update(
        $table_name,
        $update_data,
        $where_condition,
        $update_format,
        $where_format
    );

    error_log('Update result: ' . ($cancelled === false ? 'FALSE' : $cancelled));
    error_log('Last error: ' . $wpdb->last_error);

    if ($cancelled === false) {
        wp_die(json_encode(array('success' => false, 'data' => 'Error al cancelar la reserva: ' . $wpdb->last_error)));
    }

    // Aumentar los espacios disponibles en la clase
    $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->prefix}yoga_classes 
         SET available_spots = available_spots + 1 
         WHERE class_id = %s",
        $class_id
    ));

    // Obtener datos de la clase para el email
    $class_data = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}yoga_classes WHERE class_id = %s",
        $class_id
    ));

    // Enviar email de confirmación de cancelación
    send_cancellation_confirmation_email($reservation, $class_data);

    // Obtener el nombre del usuario usando el mapeo correcto
    $user_name = '';
    $name_column = isset($mapped_columns['name']) ? $mapped_columns['name'] : 'user_name';
    if (isset($reservation->{$name_column})) {
        $user_name = $reservation->{$name_column};
    }

    // Registrar en log
    error_log("YOGA: Reserva cancelada - ID: {$reservation->{$id_column}}, Email: {$email}, Clase: {$class_id}");
    error_log('=== YOGA CANCEL SUCCESS ===');

    wp_die(json_encode(array(
        'success' => true, 
        'data' => array(
            'message' => 'Reserva cancelada exitosamente',
            'user_name' => $user_name,
            'class_name' => $class_data ? $class_data->name : 'Clase'
        )
    )));
}

// Registrar la función AJAX - COMENTADO PARA EVITAR CONFLICTO CON ADMIN.PHP
// add_action('wp_ajax_yoga_cancel_reservation', 'yoga_cancel_reservation_ajax');
// add_action('wp_ajax_nopriv_yoga_cancel_reservation', 'yoga_cancel_reservation_ajax');


// ========== FUNCIONES ADICIONALES DE UTILIDAD ==========

// Función para limpiar datos antiguos (opcional)
function cleanup_old_yoga_data() {
    global $wpdb;
    
    // Limpiar clases de hace más de 30 días
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}yoga_classes WHERE date < %s",
        date('Y-m-d', strtotime('-30 days'))
    ));
    
    // Limpiar reservas canceladas de hace más de 7 días
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}yoga_reservations 
         WHERE status = 'cancelled' AND created_at < %s",
        date('Y-m-d H:i:s', strtotime('-7 days'))
    ));
}

// Programar limpieza automática (opcional)
add_action('wp', function() {
    if (!wp_next_scheduled('yoga_cleanup_cron')) {
        wp_schedule_event(time(), 'daily', 'yoga_cleanup_cron');
    }
});
add_action('yoga_cleanup_cron', 'cleanup_old_yoga_data');

// Función para obtener estadísticas (opcional)
function get_yoga_stats() {
    global $wpdb;
    
    $stats = array();
    
    // Total de clases activas
    $stats['total_classes'] = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->prefix}yoga_classes WHERE date >= CURDATE()"
    );
    
    // Total de reservas confirmadas
    $stats['total_reservations'] = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->prefix}yoga_reservations WHERE status = 'confirmed'"
    );
    
    // Reservas de hoy
    $stats['today_reservations'] = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}yoga_reservations r
         LEFT JOIN {$wpdb->prefix}yoga_classes c ON r.class_id = c.class_id
         WHERE r.status = 'confirmed' AND c.date = %s",
        date('Y-m-d')
    ));
    
    return $stats;
}

// Shortcode para mostrar estadísticas (opcional)
function yoga_stats_shortcode() {
    $stats = get_yoga_stats();
    
    ob_start();
    ?>
    <div class="yoga-stats">
        <div class="stat-item">
            <span class="stat-number"><?php echo $stats['total_classes']; ?></span>
            <span class="stat-label">Clases Disponibles</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $stats['total_reservations']; ?></span>
            <span class="stat-label">Reservas Totales</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $stats['today_reservations']; ?></span>
            <span class="stat-label">Reservas Hoy</span>
        </div>
    </div>
    <style>
    .yoga-stats {
        display: flex;
        gap: 20px;
        margin: 20px 0;
        flex-wrap: wrap;
    }
    .stat-item {
        background: linear-gradient(135deg, #94a484 0%, #95a485 100%);
        color: white;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        flex: 1;
        min-width: 150px;
        box-shadow: 0 4px 12px rgba(148, 164, 132, 0.3);
    }
    .stat-number {
        display: block;
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('yoga_stats', 'yoga_stats_shortcode');

// Función para enviar email de recordatorio (opcional)
function send_yoga_reminder_email($class_id) {
    global $wpdb;
    
    // Obtener reservas de la clase
    $reservations = $wpdb->get_results($wpdb->prepare(
        "SELECT r.*, c.name as class_name, c.date, c.time, c.instructor
         FROM {$wpdb->prefix}yoga_reservations r
         LEFT JOIN {$wpdb->prefix}yoga_classes c ON r.class_id = c.class_id
         WHERE r.class_id = %s AND r.status = 'confirmed'",
        $class_id
    ));
    
    foreach ($reservations as $reservation) {
        $to = $reservation->user_email;
        $subject = 'Recordatorio: Tu clase de yoga es mañana - Kurunta Yoga';
        
        $date = new DateTime($reservation->date);
        $days = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
        $months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 
                   'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        
        $day_name = $days[$date->format('w')];
        $day_number = $date->format('d');
        $month_name = $months[$date->format('n') - 1];
        $year = $date->format('Y');
        
        $date_formatted = ucfirst($day_name) . ', ' . $day_number . ' de ' . $month_name . ' de ' . $year;
        $time_formatted = date('H:i', strtotime($reservation->time));
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Montserrat', Arial, sans-serif; background-color: #f4f3f1; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; }
                .header { background: linear-gradient(135deg, #94a484 0%, #95a485 100%); color: white; padding: 30px; text-align: center; }
                .content { padding: 30px; }
                .reminder-box { background: #fff9e6; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='https://kuruntayoga.com.mx/wp-content/uploads/2025/06/icono.png' alt='Kurunta Yoga' style='width: 80px; height: auto; margin-bottom: 15px;'>
                    <h1>Kurunta Yoga</h1>
                    <p>by Ana Sordo</p>
                </div>
                
                <div class='content'>
                    <h2 style='color: #94a484;'>Hola {$reservation->user_name},</h2>
                    
                    <p>Te recordamos que tienes una clase programada:</p>
                    
                    <div class='reminder-box'>
                        <h3>🧘‍♀️ {$reservation->class_name}</h3>
                        <p><strong>Instructor:</strong> {$reservation->instructor}</p>
                        <p><strong>Fecha:</strong> {$date_formatted}</p>
                        <p><strong>Hora:</strong> {$time_formatted}</p>
                        <p><strong>⏰ ¡No olvides llegar 10 minutos antes!</strong></p>
                    </div>
                    
                    <p>Nos vemos mañana.</p>
                    <p><em>Namaste</em></p>
                </div>
            </div>
        </body>
        </html>";
        
        $from_email = 'anasordo@kuruntayoga.com.mx';
        $from_name = 'Ana Sordo - Kurunta Yoga';
        
        $headers = array(
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: ' . $from_email,
            'Content-Type: text/html; charset=UTF-8'
        );
        
        wp_mail($to, $subject, $message, $headers);
    }
}

// HOOK para debug de emails (opcional)
add_action('wp_mail_failed', function($wp_error) {
    error_log('YOGA EMAIL ERROR: ' . $wp_error->get_error_message());
});
?>

<style id="yoga-extra-styles">
/* ========== EFECTOS ADICIONALES PARA HACER LA PÁGINA MÁS LLAMATIVA ========== */

/* Animaciones suaves */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(148, 164, 132, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(148, 164, 132, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(148, 164, 132, 0);
    }
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Aplicar animaciones */
.yoga-booking-system {
    animation: fadeInUp 0.6s ease-out;
}

.class-item {
    animation: fadeInUp 0.4s ease-out;
    animation-fill-mode: both;
}

.class-item:nth-child(1) { animation-delay: 0.1s; }
.class-item:nth-child(2) { animation-delay: 0.2s; }
.class-item:nth-child(3) { animation-delay: 0.3s; }
.class-item:nth-child(4) { animation-delay: 0.4s; }
.class-item:nth-child(5) { animation-delay: 0.5s; }

/* Efecto de pulso en botones importantes */
.reserve-btn.available:hover {
    animation: pulse 1.5s infinite;
}

/* Gradientes más suaves con shimmer */
.yoga-main-header {
    background: linear-gradient(135deg, #94a484 0%, #95a485 50%, #96a586 100%);
    position: relative;
    overflow: hidden;
}

.yoga-main-header::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
    animation: shimmer 4s infinite;
    z-index: 0;
}

.yoga-main-title,
.yoga-main-subtitle {
    position: relative;
    z-index: 2;
}

/* Sombras más profundas y elegantes */
.yoga-booking-system {
    box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.1),
        0 2px 8px rgba(0, 0, 0, 0.05),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.class-item {
    box-shadow: 
        0 4px 12px rgba(0, 0, 0, 0.1),
        0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.class-item:hover {
    box-shadow: 
        0 12px 32px rgba(0, 0, 0, 0.15),
        0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-4px);
}

/* Efectos de glassmorphism mejorados */
.yoga-booking-header {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
}

.week-navigation {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 4px 16px rgba(31, 38, 135, 0.1);
}

/* Indicadores visuales mejorados */
.class-item.available {
    border-left-color: #94a484;
    border-left-width: 5px;
}

.class-item.almost-full {
    border-left-color: #ffc107;
    border-left-width: 5px;
}

.class-item.full {
    border-left-color: #dc3545;
    border-left-width: 5px;
    opacity: 0.75;
}

/* Estados de hover más suaves */
.filter-btn, .nav-btn, .reserve-btn, .cancel-btn {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.filter-btn::before,
.reserve-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.filter-btn:hover::before,
.reserve-btn:hover::before {
    left: 100%;
}

/* Microinteracciones en días */
.day-number {
    position: relative;
    overflow: hidden;
}

.day-number::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(148, 164, 132, 0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.day-number:hover::before {
    width: 120%;
    height: 120%;
}

.day-number.selected::before {
    width: 100%;
    height: 100%;
    background: rgba(140, 22, 42, 0.1);
}

/* Mejoras en tipografía con gradientes */
.class-name {
    background: linear-gradient(135deg, #2c2c2c 0%, #4a4a4a 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 600;
}

.class-time {
    position: relative;
}

.class-time::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #94a484, #8c162a);
    transition: width 0.3s ease;
}

.class-item:hover .class-time::after {
    width: 100%;
}

/* Loading spinner mejorado */
.loading-classes {
    position: relative;
}

.loading-classes::after {
    content: '';
    display: inline-block;
    width: 24px;
    height: 24px;
    border: 3px solid rgba(148, 164, 132, 0.3);
    border-radius: 50%;
    border-top-color: #94a484;
    animation: spin 1s ease-in-out infinite;
    margin-left: 15px;
    vertical-align: middle;
}

/* Efectos en modales */
.modal-content {
    position: relative;
    overflow: hidden;
}

.modal-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #94a484, #8c162a, #94a484);
    background-size: 200% 100%;
    animation: gradientMove 3s ease-in-out infinite;
}

@keyframes gradientMove {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

/* Efectos de foco mejorados */
.form-group input:focus {
    outline: none;
    border-color: #94a484;
    box-shadow: 
        0 0 0 3px rgba(148, 164, 132, 0.1),
        0 4px 12px rgba(148, 164, 132, 0.2);
    transform: translateY(-1px);
}

/* Botones con efectos de onda */
.btn-confirm {
    position: relative;
    overflow: hidden;
}

.btn-confirm::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-confirm:active::after {
    width: 300px;
    height: 300px;
}

/* Scroll suave personalizado */
.classes-list {
    scroll-behavior: smooth;
}

.classes-list::-webkit-scrollbar {
    width: 8px;
}

.classes-list::-webkit-scrollbar-track {
    background: rgba(148, 164, 132, 0.1);
    border-radius: 4px;
}

.classes-list::-webkit-scrollbar-thumb {
    background: rgba(148, 164, 132, 0.5);
    border-radius: 4px;
}

.classes-list::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 164, 132, 0.7);
}

/* Efectos de parallax sutil */
.yoga-main-header {
    background-attachment: fixed;
    background-size: cover;
}

/* Indicadores de estado con animación */
.class-status {
    position: relative;
    animation: fadeInUp 0.5s ease-out;
}

.status-available {
    color: #28a745;
}

.status-almost-full {
    color: #ffc107;
    animation: pulse 2s infinite;
}

.status-full {
    color: #dc3545;
}

/* Efectos de hover en logos */
.yoga-logo-img {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.yoga-logo-img:hover {
    transform: scale(1.05) rotate(2deg);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

/* Responsive mejorado con animaciones */
@media (max-width: 768px) {
    .class-item {
        animation-delay: 0s;
    }
    
    .yoga-main-header::after {
        animation-duration: 2s;
    }
}

/* Efectos de partículas sutiles */
.yoga-booking-system::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        radial-gradient(circle at 25% 25%, rgba(148, 164, 132, 0.1) 1px, transparent 1px),
        radial-gradient(circle at 75% 75%, rgba(140, 22, 42, 0.1) 1px, transparent 1px);
    background-size: 50px 50px;
    pointer-events: none;
    z-index: -1;
}

/* Estados de éxito con celebración */
.success-icon {
    animation: celebrate 0.6s ease-out;
}

@keyframes celebrate {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* Efectos de brillo en elementos importantes */
.recurring-badge {
    position: relative;
    overflow: hidden;
}

.recurring-badge::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    animation: shine 2s infinite;
}

@keyframes shine {
    0% { left: -100%; }
    100% { left: 100%; }
}
</style>