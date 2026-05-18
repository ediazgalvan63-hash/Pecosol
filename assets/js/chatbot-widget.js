/**
 * Chatbot Widget - Cliente JavaScript
 * Conecta con la API Python de FastAPI
 */

class ChatbotWidget {
    constructor() {
        const configuredUrl = (typeof window !== 'undefined' && window.CHATBOT_API_URL)
            ? String(window.CHATBOT_API_URL).trim()
            : '';
        const localDefault = 'http://127.0.0.1:8000/api/chat';
        if (!configuredUrl) {
            this.apiUrl = localDefault;
        } else {
            this.apiUrl = this.normalizeApiUrl(configuredUrl);
            if (configuredUrl === localDefault && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                console.warn('⚠️ CHATBOT_API_URL está usando el valor local por defecto en un dominio remoto. Ajusta CHATBOT_API_URL para la URL correcta del servicio Python.');
            }
        }
        this.isOpen = false;
        this.apiUrlResolved = false;
        this.sessionId = this.generateSessionId();
        this.init();
    }

    normalizeApiUrl(url) {
        if (!url) {
            return '';
        }

        let normalized = String(url).trim();
        if (!/^https?:\/\//i.test(normalized)) {
            normalized = `${window.location.origin}${normalized.startsWith('/') ? '' : '/'}${normalized}`;
        }

        normalized = normalized.replace(/\/+$/g, '');
        if (!/\/api\/chat$/i.test(normalized)) {
            normalized += '/api/chat';
        }

        return normalized;
    }

    buildHealthUrl(url) {
        try {
            const normalizedUrl = new URL(url, window.location.origin);
            if (/\/api\/chat\/?$/i.test(normalizedUrl.pathname)) {
                normalizedUrl.pathname = normalizedUrl.pathname.replace(/\/api\/chat\/?$/i, '/health');
            } else {
                normalizedUrl.pathname = normalizedUrl.pathname.replace(/\/+$/g, '') + '/health';
            }
            return normalizedUrl.toString();
        } catch (error) {
            return `${String(url).replace(/\/+$/g, '')}/health`;
        }
    }

    getApiCandidates() {
        const candidates = [];
        const isLocalhost = window.location && (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1');

        if (typeof window !== 'undefined' && window.CHATBOT_API_URL) {
            candidates.push(this.normalizeApiUrl(String(window.CHATBOT_API_URL).trim()));
        }
        if (window.location && window.location.origin) {
            candidates.push(`${window.location.origin}/api/chat`);
        }
        if (isLocalhost) {
            candidates.push('http://localhost:8000/api/chat');
            candidates.push('http://127.0.0.1:8000/api/chat');
        }

        // Garantizar que el actual this.apiUrl también se pruebe si ya existe.
        if (this.apiUrl && !candidates.includes(this.apiUrl)) {
            candidates.unshift(this.apiUrl);
        }

        return [...new Set(candidates.filter(Boolean))];
    }

    async resolveApiUrl(retries = 5, delayMs = 1500) {
        const uniqueCandidates = this.getApiCandidates();

        for (let attempt = 1; attempt <= retries; attempt++) {
            for (const url of uniqueCandidates) {
                const healthUrl = this.buildHealthUrl(url);
                try {
                    const controller = new AbortController();
                    const timeout = setTimeout(() => controller.abort(), 1500);
                    const response = await fetch(healthUrl, {
                        method: 'GET',
                        mode: 'cors',
                        signal: controller.signal,
                    });
                    clearTimeout(timeout);
                    if (!response.ok) {
                        continue;
                    }
                    const json = await response.json().catch(() => null);
                    const hasDatabaseHealth = json && typeof json.database === 'string';
                    const hasServiceHealth = json && typeof json.service === 'string' && json.service !== 'Pecosol Chatbot API Test';
                    const isTestServer = json && json.service === 'Pecosol Chatbot API Test';
                    if ((!hasDatabaseHealth && !hasServiceHealth) || isTestServer) {
                        console.warn(`⚠️ Endpoint no válido o de prueba detectado en ${url}, ignorando.`);
                        continue;
                    }
                    this.apiUrl = url;
                    this.apiUrlResolved = true;
                    console.log(`✅ Chatbot endpoint activo detectado: ${url}`);
                    return true;
                } catch (error) {
                    // Intentar siguiente candidato
                }
            }

            if (attempt < retries) {
                console.log(`⏳ Intento ${attempt} fallido. Reintentando en ${delayMs}ms...`);
                await this.sleep(delayMs);
            }
        }

        this.apiUrlResolved = false;
        console.warn(`⚠️ No se encontró un endpoint de chatbot activo. Usando: ${this.apiUrl}`);
        return false;
    }

    async requestChatApi(message) {
        const requestBody = JSON.stringify({
            message: message,
            user_id: typeof window.CHATBOT_USER_ID !== 'undefined' ? window.CHATBOT_USER_ID : null,
            session_id: this.sessionId
        });

        const triedUrls = new Set();
        const candidates = this.getApiCandidates();

        for (const url of candidates) {
            if (!url || triedUrls.has(url)) {
                continue;
            }
            triedUrls.add(url);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    mode: 'cors',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: requestBody,
                });

                if (!response.ok) {
                    const status = response.status;
                    if (status === 404) {
                        console.warn(`⚠️ Endpoint ${url} devolvió 404. Intentando siguiente candidato.`);
                        continue;
                    }
                    const text = await response.text().catch(() => '');
                    throw new Error(`HTTP error ${status}: ${text}`);
                }

                const data = await response.json();
                if (data && typeof data.success !== 'undefined') {
                    this.apiUrl = url;
                    this.apiUrlResolved = true;
                    return data;
                }

                throw new Error('Respuesta inválida del servidor de chatbot');
            } catch (error) {
                console.warn(`⚠️ Error conectando con ${url}: ${error.message}`);
            }
        }

        throw new Error('No se pudo conectar con ningún endpoint del chatbot');
    }

    generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    sleep(ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    }

    init() {
        this.createWidget();
        this.attachEventListeners();
        this.resolveApiUrl();
        console.log('✅ Chatbot Widget inicializado');
    }

    createWidget() {
        // Crear estructura HTML del widget
        const widgetHTML = `
            <!-- Botón flotante -->
            <button class="chatbot-fab" id="chatbotFab" title="Abrir Asistente IA">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-5-9h10v2H7v-2z"/>
                    <circle cx="8" cy="10" r="1.5"/>
                    <circle cx="16" cy="10" r="1.5"/>
                    <path d="M12 17.5c2.33 0 4.32-1.45 5.12-3.5H6.88c.8 2.05 2.79 3.5 5.12 3.5z"/>
                </svg>
            </button>

            <!-- Widget de chat -->
            <div class="chatbot-widget" id="chatbotWidget">
                <div class="chatbot-widget-header">
                    <h3>Asistente IA</h3>
                    <button class="chatbot-close-btn" id="chatbotClose">×</button>
                </div>
                
                <div class="chatbot-widget-messages" id="chatbotMessages">
                    <div class="welcome-message">
                        <h4>¡Hola! 👋 Soy tu asistente IA de Pecosol</h4>
                        <ul>
                            <li>Consultar inventario y stock</li>
                            <li>Analizar ventas y estadísticas</li>
                            <li>Generar reportes</li>
                            <li>Responder preguntas sobre tu negocio</li>
                        </ul>
                    </div>
                    <div class="typing-indicator" id="typingIndicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
                
                <div class="chatbot-widget-input">
                    <textarea 
                        id="chatbotInput" 
                        placeholder="Escribe tu pregunta aquí..."
                        rows="1"
                        maxlength="500"
                    ></textarea>
                    <button class="chatbot-send-btn" id="chatbotSend" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        // Insertar en el body
        const container = document.createElement('div');
        container.innerHTML = widgetHTML;
        document.body.appendChild(container);

        // Referencias a elementos
        this.fab = document.getElementById('chatbotFab');
        this.widget = document.getElementById('chatbotWidget');
        this.closeBtn = document.getElementById('chatbotClose');
        this.messagesContainer = document.getElementById('chatbotMessages');
        this.input = document.getElementById('chatbotInput');
        this.sendBtn = document.getElementById('chatbotSend');
        this.typingIndicator = document.getElementById('typingIndicator');
    }

    attachEventListeners() {
        // Abrir/cerrar widget
        this.fab.addEventListener('click', () => this.toggleWidget());
        this.closeBtn.addEventListener('click', () => this.closeWidget());

        // Input
        this.input.addEventListener('input', () => this.handleInput());
        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // Botón enviar
        this.sendBtn.addEventListener('click', () => this.sendMessage());

        // Auto-resize textarea
        this.input.addEventListener('input', () => {
            this.input.style.height = 'auto';
            this.input.style.height = this.input.scrollHeight + 'px';
        });
    }

    toggleWidget() {
        if (this.isOpen) {
            this.closeWidget();
        } else {
            this.openWidget();
        }
    }

    openWidget() {
        this.widget.classList.add('open');
        this.fab.classList.add('active');
        this.isOpen = true;
        this.input.focus();
    }

    closeWidget() {
        this.widget.classList.remove('open');
        this.fab.classList.remove('active');
        this.isOpen = false;
    }

    handleInput() {
        const hasText = this.input.value.trim().length > 0;
        this.sendBtn.disabled = !hasText;
    }

    async sendMessage() {
        const message = this.input.value.trim();
        if (!message) return;

        // Limpiar input
        this.input.value = '';
        this.input.style.height = 'auto';
        this.sendBtn.disabled = true;

        // Agregar mensaje del usuario
        this.addMessage(message, 'user');

        // Mostrar indicador de escritura
        this.showTyping();

        await this.resolveApiUrl(10, 2000);

        try {
            const data = await this.requestChatApi(message);
            this.hideTyping();

            if (data.success) {
                this.addMessage(data.response, 'bot');
            } else {
                this.addMessage(data.error || 'Error al procesar tu pregunta', 'error');
            }

        } catch (error) {
            console.error('Error:', error);
            this.hideTyping();

            let errorMessage = '';
            const text = String(error.message || 'Error inesperado');

            if (text.includes('Failed to fetch') || text.includes('NetworkError')) {
                errorMessage = `
                    <strong>⚠️ Servicio de chatbot no disponible</strong><br><br>
                    El asistente IA requiere que el servidor Python esté ejecutándose o que la URL configurada sea correcta.<br><br>
                    <strong>Para iniciar el servidor local:</strong><br>
                    1. Abre: <code>python_api/INICIAR_CHATBOT.bat</code><br>
                    2. Espera a que el servidor responda en <code>http://127.0.0.1:8000</code><br>
                    3. Vuelve a intentar<br><br>
                    <strong>Endpoint configurado:</strong> <code>${this.apiUrl}</code><br><br>
                    <strong>Si necesitas ayuda:</strong><br>
                    • Lee: <code>python_api/INICIO_RAPIDO.md</code><br>
                    • Revisa: <code>verify_chatbot.php</code>
                `;
            } else if (text.includes('404')) {
                errorMessage = `
                    <strong>❌ Servidor encontrado pero endpoint no disponible</strong><br><br>
                    El servidor Python está corriendo, pero el endpoint de chat no se encontró.<br>
                    Verifica que tu URL termine en <code>/api/chat</code> y que el servicio esté activo.
                `;
            } else {
                errorMessage = `
                    <strong>❌ Error inesperado</strong><br><br>
                    ${text}<br><br>
                    Revisa la consola del navegador (F12) para más detalles.
                `;
            }

            this.addMessage(errorMessage, 'error');
        }

    }

    addMessage(text, type = 'bot') {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${type}`;

        const now = new Date();
        const time = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });

        const avatar = type === 'bot' ? '🤖' : '👤';
        
        messageDiv.innerHTML = `
            <div class="chat-message-avatar">${avatar}</div>
            <div class="chat-message-content">
                ${this.formatMessage(text)}
                <div class="chat-message-time">${time}</div>
            </div>
        `;

        // Insertar antes del indicador de escritura
        this.messagesContainer.insertBefore(messageDiv, this.typingIndicator);
        
        // Scroll al final
        this.scrollToBottom();
    }

    formatMessage(text) {
        // Convertir saltos de línea a <br>
        text = text.replace(/\n/g, '<br>');
        
        // Convertir URLs a enlaces
        const urlRegex = /(https?:\/\/[^\s]+)/g;
        text = text.replace(urlRegex, '<a href="$1" target="_blank">$1</a>');
        
        return text;
    }

    showTyping() {
        this.typingIndicator.classList.add('active');
        this.scrollToBottom();
    }

    hideTyping() {
        this.typingIndicator.classList.remove('active');
    }

    scrollToBottom() {
        setTimeout(() => {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }, 100);
    }
}

// Inicializar el widget cuando el DOM esté listo
function initializeChatbotWidget() {
    if (window.chatbotWidget instanceof ChatbotWidget) {
        return;
    }
    window.chatbotWidget = new ChatbotWidget();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeChatbotWidget);
} else {
    initializeChatbotWidget();
}
