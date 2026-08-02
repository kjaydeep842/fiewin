/**
 * Fiewin Real-Time WebSocket Manager
 * Supports Reverb / Pusher / WebSockets with Silent Automatic Polling Fallback
 */
class FiewinWebSocketManager {
    constructor(gameCode, userId = null) {
        this.gameCode = gameCode;
        this.userId = userId;
        this.connectionStatus = 'DISCONNECTED';
        this.socket = null;
        this.pollingTimer = null;
        this.eventCallbacks = {};
        this.init();
    }

    init() {
        this.connectWebSocket();
    }

    connectWebSocket() {
        // Check if Echo or Pusher client library is available
        if (window.Pusher || window.Echo) {
            this.initEchoClient();
            return;
        }

        // Only attempt raw WebSocket connection if explicitly enabled via window config
        if (window.ENABLE_WEBSOCKETS && window.REVERB_APP_KEY) {
            try {
                const wsHost = window.REVERB_HOST || window.location.hostname;
                const wsPort = window.REVERB_PORT || 8080;
                const wsScheme = window.location.protocol === 'https:' ? 'wss' : 'ws';
                const wsUrl = `${wsScheme}://${wsHost}:${wsPort}/app/${window.REVERB_APP_KEY}?protocol=7&client=js&version=7.0.3`;

                this.socket = new WebSocket(wsUrl);

                this.socket.onopen = () => {
                    this.connectionStatus = 'CONNECTED';
                    this.updateStatusBadge('LIVE WS', 'bg-success');
                    this.stopPollingFallback();
                    this.subscribeChannels();
                };

                this.socket.onmessage = (event) => {
                    this.handleRawMessage(event.data);
                };

                this.socket.onerror = () => {
                    this.handleDisconnect();
                };

                this.socket.onclose = () => {
                    this.handleDisconnect();
                };
            } catch (e) {
                this.handleDisconnect();
            }
        } else {
            // Default directly & silently to HTTP sync polling
            this.handleDisconnect();
        }
    }

    initEchoClient() {
        if (window.Echo) {
            this.connectionStatus = 'CONNECTED';
            this.updateStatusBadge('LIVE WS', 'bg-success');
            this.stopPollingFallback();

            const channelName = 'game.' + this.gameCode.toLowerCase();
            window.Echo.channel(channelName)
                .listen('.GameStateUpdated', (data) => this.triggerEvent('GameStateUpdated', data.state))
                .listen('.BetPlaced', (data) => this.triggerEvent('BetPlaced', data.betData))
                .listen('.HistoryUpdated', (data) => this.triggerEvent('HistoryUpdated', data.latestResult));

            if (this.userId) {
                window.Echo.private('wallet.' + this.userId)
                    .listen('.WalletUpdated', (data) => this.triggerEvent('WalletUpdated', data));
            }
        }
    }

    subscribeChannels() {
        if (!this.socket || this.socket.readyState !== WebSocket.OPEN) return;

        const publicChannel = 'game.' + this.gameCode.toLowerCase();
        const subscribeMsg = JSON.stringify({
            event: 'pusher:subscribe',
            data: { channel: publicChannel }
        });
        this.socket.send(subscribeMsg);
    }

    handleRawMessage(dataStr) {
        try {
            const parsed = JSON.parse(dataStr);
            if (parsed.event === 'GameStateUpdated' || parsed.event === '.GameStateUpdated') {
                const payload = typeof parsed.data === 'string' ? JSON.parse(parsed.data) : parsed.data;
                this.triggerEvent('GameStateUpdated', payload.state || payload);
            } else if (parsed.event === 'BetPlaced' || parsed.event === '.BetPlaced') {
                const payload = typeof parsed.data === 'string' ? JSON.parse(parsed.data) : parsed.data;
                this.triggerEvent('BetPlaced', payload.betData || payload);
            } else if (parsed.event === 'WalletUpdated' || parsed.event === '.WalletUpdated') {
                const payload = typeof parsed.data === 'string' ? JSON.parse(parsed.data) : parsed.data;
                this.triggerEvent('WalletUpdated', payload);
            }
        } catch (e) {
            // Ignore heartbeat or system messages
        }
    }

    handleDisconnect() {
        this.connectionStatus = 'FALLBACK_POLLING';
        this.updateStatusBadge('LIVE POLL', 'bg-info');
        this.startPollingFallback();
    }

    startPollingFallback() {
        if (this.pollingTimer) return;
        this.triggerEvent('RequestPollingSync', {});
        this.pollingTimer = setInterval(() => {
            this.triggerEvent('RequestPollingSync', {});
        }, 1000);
    }

    stopPollingFallback() {
        if (this.pollingTimer) {
            clearInterval(this.pollingTimer);
            this.pollingTimer = null;
        }
    }

    on(eventName, callback) {
        if (!this.eventCallbacks[eventName]) {
            this.eventCallbacks[eventName] = [];
        }
        this.eventCallbacks[eventName].push(callback);
    }

    triggerEvent(eventName, data) {
        if (this.eventCallbacks[eventName]) {
            this.eventCallbacks[eventName].forEach(cb => cb(data));
        }
    }

    updateStatusBadge(text, bgClass) {
        const badge = document.getElementById('wsConnectionBadge');
        if (badge) {
            badge.className = `badge ${bgClass} rounded-pill px-2 py-1 small`;
            badge.textContent = text;
        }
    }

    destroy() {
        this.stopPollingFallback();
        if (this.socket) {
            this.socket.close();
            this.socket = null;
        }
    }
}

window.FiewinWebSocketManager = FiewinWebSocketManager;
