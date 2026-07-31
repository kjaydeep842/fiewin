/**
 * AnimationManager — Premium UI Micro-Interactions, Confetti, Coin Particles, Toast & Skeleton Engine
 */
class AnimationManager {
    constructor() {
        this.animationsEnabled = localStorage.getItem('gh_anim_disabled') !== 'true';
        this.initRipples();
    }

    toggleAnimations() {
        this.animationsEnabled = !this.animationsEnabled;
        localStorage.setItem('gh_anim_disabled', !this.animationsEnabled);
        return this.animationsEnabled;
    }

    // Ripple Effect on Buttons & Cards
    initRipples() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.gh-btn-primary, .gh-btn-success, .gh-card, .btn');
            if (!btn || !this.animationsEnabled) return;

            const rect = btn.getBoundingClientRect();
            const ripple = document.createElement('span');
            const diameter = Math.max(rect.width, rect.height);
            const radius = diameter / 2;

            ripple.style.width = ripple.style.height = `${diameter}px`;
            ripple.style.left = `${e.clientX - rect.left - radius}px`;
            ripple.style.top = `${e.clientY - rect.top - radius}px`;
            ripple.className = 'gh-ripple-effect';

            const existing = btn.querySelector('.gh-ripple-effect');
            if (existing) existing.remove();

            btn.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    }

    // Confetti Explosion
    triggerConfetti(count = 50) {
        if (!this.animationsEnabled) return;

        const container = document.createElement('div');
        container.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;pointer-events:none;z-index:99999;overflow:hidden;';
        document.body.appendChild(container);

        const colors = ['#1E88E5', '#22C55E', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#3B82F6'];

        for (let i = 0; i < count; i++) {
            const confetti = document.createElement('div');
            const color = colors[Math.floor(Math.random() * colors.length)];
            const size = Math.random() * 8 + 6;
            const startX = Math.random() * 100;
            const duration = Math.random() * 2 + 1.5;
            const rotation = Math.random() * 360;

            confetti.style.cssText = `
                position: absolute;
                top: -20px;
                left: ${startX}vw;
                width: ${size}px;
                height: ${size * (Math.random() > 0.5 ? 1.5 : 1)}px;
                background-color: ${color};
                border-radius: ${Math.random() > 0.5 ? '50%' : '2px'};
                opacity: 0.9;
                transform: rotate(${rotation}deg);
                transition: transform ${duration}s linear, top ${duration}s ease-in, opacity ${duration}s ease-out;
            `;

            container.appendChild(confetti);

            setTimeout(() => {
                confetti.style.top = '105vh';
                confetti.style.transform = `rotate(${rotation + 720}deg) scale(0.5)`;
                confetti.style.opacity = '0';
            }, 20);
        }

        setTimeout(() => container.remove(), 3500);

        if (window.soundManager) {
            window.soundManager.play('reward');
        }
    }

    // Flying Coins to Wallet Animation
    animateCoinsToWallet(sourceElement) {
        if (!this.animationsEnabled) return;

        const walletPill = document.querySelector('.gh-wallet-pill') || document.getElementById('topWalletBalance');
        if (!walletPill) return;

        const targetRect = walletPill.getBoundingClientRect();
        const sourceRect = sourceElement ? sourceElement.getBoundingClientRect() : {
            left: window.innerWidth / 2,
            top: window.innerHeight / 2,
            width: 0,
            height: 0
        };

        const startX = sourceRect.left + sourceRect.width / 2;
        const startY = sourceRect.top + sourceRect.height / 2;

        for (let i = 0; i < 8; i++) {
            setTimeout(() => {
                const coin = document.createElement('div');
                coin.className = 'gh-flying-coin';
                coin.innerHTML = '<i class="bi bi-coin text-warning"></i>';
                coin.style.cssText = `
                    position: fixed;
                    left: ${startX}px;
                    top: ${startY}px;
                    font-size: 1.4rem;
                    z-index: 9999;
                    pointer-events: none;
                    transition: all 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                `;

                document.body.appendChild(coin);

                // Add random trajectory variance
                const offsetX = (Math.random() - 0.5) * 60;
                const offsetY = (Math.random() - 0.5) * 60;

                setTimeout(() => {
                    coin.style.left = `${targetRect.left + 15}px`;
                    coin.style.top = `${targetRect.top + 10}px`;
                    coin.style.transform = 'scale(0.5) rotate(360deg)';
                    coin.style.opacity = '0.3';
                }, 30);

                setTimeout(() => {
                    coin.remove();
                    if (i === 7) {
                        walletPill.classList.add('gh-pulse-warning');
                        setTimeout(() => walletPill.classList.remove('gh-pulse-warning'), 600);
                        if (window.soundManager) window.soundManager.play('coin');
                    }
                }, 750);
            }, i * 65);
        }
    }

    // Animated Counter for Numbers
    animateCounter(element, endVal, prefix = '₹', duration = 800) {
        if (!element) return;
        const startText = element.innerText.replace(/[^0-9.]/g, '');
        const startVal = parseFloat(startText) || 0;
        const startTime = performance.now();

        const step = (now) => {
            const progress = Math.min((now - startTime) / duration, 1);
            const currentVal = startVal + (endVal - startVal) * this.easeOutQuad(progress);
            element.innerText = `${prefix}${currentVal.toFixed(2)}`;
            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };
        requestAnimationFrame(step);
    }

    easeOutQuad(t) {
        return t * (2 - t);
    }

    // Screen Shake Effect
    shakeScreen() {
        if (!this.animationsEnabled) return;
        const app = document.querySelector('.gh-app-viewport') || document.body;
        app.classList.add('gh-shake-impact');
        if (navigator.vibrate) {
            navigator.vibrate([100, 50, 100]);
        }
        setTimeout(() => app.classList.remove('gh-shake-impact'), 500);
    }

    // Toast Notification System
    toast(message, type = 'success') {
        let container = document.getElementById('ghToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'ghToastContainer';
            container.style.cssText = 'position:fixed;top:16px;right:50%;transform:translateX(50%);z-index:99999;width:90%;max-width:400px;display:flex;flex-direction:column;gap:8px;pointer-events:none;';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const iconMap = {
            success: 'bi-check-circle-fill text-success',
            error: 'bi-exclamation-triangle-fill text-danger',
            warning: 'bi-exclamation-circle-fill text-warning',
            info: 'bi-info-circle-fill text-primary'
        };

        const bgMap = {
            success: 'border-success bg-white',
            error: 'border-danger bg-white',
            warning: 'border-warning bg-white',
            info: 'border-primary bg-white'
        };

        toast.className = `gh-toast-item p-3 rounded-4 shadow-lg border ${bgMap[type] || bgMap.info} d-flex align-items-center gap-3`;
        toast.style.cssText = 'pointer-events:auto;transition:all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);transform:translateY(-20px) scale(0.9);opacity:0;';
        
        toast.innerHTML = `
            <i class="bi ${iconMap[type] || iconMap.info} fs-4"></i>
            <div class="flex-grow-1 fw-semibold small text-dark">${message}</div>
            <button type="button" class="btn-close btn-close-sm ms-auto" onclick="this.parentElement.remove()"></button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.transform = 'translateY(0) scale(1)';
            toast.style.opacity = '1';
        }, 20);

        if (window.soundManager) {
            window.soundManager.play(type === 'error' ? 'lose' : 'notification');
        }

        setTimeout(() => {
            toast.style.transform = 'translateY(-20px) scale(0.9)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, 3500);
    }
}

// Global instance
window.animationManager = new AnimationManager();
