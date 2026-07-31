/**
 * CrashEngine — AAA Canvas Particle Manager & Flight Renderer for Rocket & Jet Modes
 */

class ParticleManager {
    constructor() {
        this.particles = [];
    }

    addSmoke(x, y, vx, vy, size = 10, color = 'rgba(200,210,225,0.4)') {
        this.particles.push({
            type: 'smoke',
            x, y, vx, vy,
            size,
            maxSize: size * (1.5 + Math.random() * 1.5),
            life: 1.0,
            decay: 0.02 + Math.random() * 0.02,
            color
        });
    }

    addFire(x, y, vx, vy, size = 8) {
        const colors = ['#FF4500', '#FF8C00', '#FFD700', '#FF1493'];
        this.particles.push({
            type: 'fire',
            x, y, vx, vy,
            size,
            life: 1.0,
            decay: 0.04 + Math.random() * 0.04,
            color: colors[Math.floor(Math.random() * colors.length)]
        });
    }

    addSpark(x, y, count = 5) {
        for (let i = 0; i < count; i++) {
            const angle = Math.random() * Math.PI * 2;
            const speed = 2 + Math.random() * 6;
            this.particles.push({
                type: 'spark',
                x, y,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed,
                size: 2 + Math.random() * 2,
                life: 1.0,
                decay: 0.05 + Math.random() * 0.05,
                color: '#FFF566'
            });
        }
    }

    addExplosion(x, y, count = 50) {
        this.addSpark(x, y, 25);
        for (let i = 0; i < count; i++) {
            const angle = Math.random() * Math.PI * 2;
            const speed = 1 + Math.random() * 9;
            this.particles.push({
                type: 'explosion',
                x, y,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed,
                size: 8 + Math.random() * 14,
                life: 1.0,
                decay: 0.02 + Math.random() * 0.02,
                color: Math.random() > 0.4 ? '#FF3D00' : '#FFA000'
            });
        }
    }

    updateAndDraw(ctx) {
        for (let i = this.particles.length - 1; i >= 0; i--) {
            const p = this.particles[i];
            p.x += p.vx;
            p.y += p.vy;
            p.life -= p.decay;

            if (p.type === 'smoke') {
                p.size = Math.min(p.maxSize, p.size + 0.4);
            }

            if (p.life <= 0) {
                this.particles.splice(i, 1);
                continue;
            }

            ctx.save();
            ctx.globalAlpha = Math.max(0, p.life);
            ctx.fillStyle = p.color;

            if (p.type === 'spark') {
                ctx.shadowColor = '#FFF';
                ctx.shadowBlur = 6;
                ctx.fillRect(p.x, p.y, p.size, p.size);
            } else {
                ctx.beginPath();
                ctx.arc(p.x, p.y, Math.max(1, p.size), 0, Math.PI * 2);
                ctx.fill();
            }
            ctx.restore();
        }
    }

    clear() {
        this.particles = [];
    }
}


class CrashCanvasRenderer {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        if (!this.canvas) return;
        this.ctx = this.canvas.getContext('2d');
        this.pm = new ParticleManager();
        this.mode = 'jet'; // 'jet' default or 'rocket'
        this.state = 'idle'; // 'idle', 'countdown', 'launching', 'flying', 'crashed'
        this.multiplier = 1.00;
        this.progress = 0;
        this.flightX = 0;
        this.flightY = 0;
        this.timeOfDay = 0; // 0: morning, 0.5: sunset, 1: night
        this.stars = [];
        this.clouds = [];
        this.animFrame = null;
        this.countdownVal = 5;

        this.resize();
        this.initEnvironment();
        window.addEventListener('resize', () => this.resize());
        this.startLoop();
    }

    resize() {
        if (!this.canvas) return;
        this.width = this.canvas.offsetWidth || 400;
        this.height = this.canvas.offsetHeight || 260;
        this.canvas.width = this.width;
        this.canvas.height = this.height;
    }

    setMode(modeName) {
        this.mode = modeName;
        this.resetState();
    }

    initEnvironment() {
        this.stars = [];
        for (let i = 0; i < 45; i++) {
            this.stars.push({
                x: Math.random() * this.width,
                y: Math.random() * (this.height * 0.75),
                size: Math.random() * 2 + 0.5,
                alpha: Math.random()
            });
        }

        this.clouds = [];
        for (let i = 0; i < 7; i++) {
            this.clouds.push({
                x: Math.random() * this.width,
                y: Math.random() * (this.height * 0.6) + 15,
                speed: 0.3 + Math.random() * 0.6,
                scale: 0.6 + Math.random() * 0.8
            });
        }
    }

    resetState() {
        this.state = 'idle';
        this.multiplier = 1.00;
        this.progress = 0;
        this.flightX = 45;
        this.flightY = this.height - 45;
        this.timeOfDay = 0;
        this.pm.clear();
    }

    startCountdown(seconds = 5, callback) {
        this.state = 'countdown';
        this.countdownVal = seconds;
        if (window.soundManager) window.soundManager.play('launchCountdown');

        const interval = setInterval(() => {
            this.countdownVal--;
            if (this.countdownVal > 0) {
                if (window.soundManager) window.soundManager.play('launchCountdown');
            } else {
                clearInterval(interval);
                this.state = 'launching';
                if (window.soundManager) window.soundManager.play('engineIgnition');
                if (callback) callback();
            }
        }, 1000);
    }

    setFlightState(mult) {
        this.state = 'flying';
        this.multiplier = mult;
    }

    /**
     * User cashout early trigger:
     * Plays celebration sounds and coin animations BUT keeps the plane flying seamlessly!
     */
    triggerPlayerCashOut() {
        if (window.soundManager) window.soundManager.play('cashout');
        if (window.animationManager) {
            window.animationManager.triggerConfetti(60);
            window.animationManager.animateCoinsToWallet();
        }
    }

    triggerCrash(finalMult) {
        this.state = 'crashed';
        this.multiplier = finalMult;
        this.pm.addExplosion(this.flightX, this.flightY, 60);
        if (window.soundManager) {
            window.soundManager.stopEngineSound();
            window.soundManager.play('explosion');
            window.soundManager.play('alarm');
        }
        if (window.animationManager) window.animationManager.shakeScreen();
    }

    startLoop() {
        const render = () => {
            this.update();
            this.draw();
            this.animFrame = requestAnimationFrame(render);
        };
        render();
    }

    update() {
        if (this.state === 'flying') {
            this.timeOfDay = Math.min(1.0, this.timeOfDay + 0.0004);
            this.progress += 0.015;

            // Flight coordinate curve — plane continuously ascends
            this.flightX = Math.min(this.width * 0.78, 45 + (this.progress * 26));
            this.flightY = Math.max(55, (this.height - 45) - (Math.pow(this.progress, 1.2) * 19));

            // Engine Smoke & Flame Trail
            if (this.mode === 'rocket') {
                this.pm.addFire(this.flightX - 12, this.flightY + 12, -2 - Math.random() * 2, 2 + Math.random() * 2);
                this.pm.addSmoke(this.flightX - 18, this.flightY + 18, -1.5, 1.5, 8);
            } else {
                // Jet Mode afterburner & wingtip smoke
                this.pm.addFire(this.flightX - 25, this.flightY + 4, -4 - Math.random() * 2, (Math.random() - 0.5) * 2, 6);
                this.pm.addSmoke(this.flightX - 30, this.flightY + 4, -3, (Math.random() - 0.5) * 1, 6, 'rgba(255,255,255,0.3)');
            }
        }

        // Scroll clouds
        this.clouds.forEach(c => {
            c.x -= c.speed * (this.state === 'flying' ? 2 : 0.4);
            if (c.x < -90) c.x = this.width + 90;
        });
    }

    draw() {
        if (!this.ctx) return;
        this.ctx.clearRect(0, 0, this.width, this.height);

        // 1. Dark Sky Flight Area Background Gradient (Morning -> Deep Space Night)
        let bgGrad;
        if (this.timeOfDay < 0.4) {
            bgGrad = this.ctx.createLinearGradient(0, 0, 0, this.height);
            bgGrad.addColorStop(0, '#0F172A');
            bgGrad.addColorStop(1, '#1E293B');
        } else if (this.timeOfDay < 0.75) {
            bgGrad = this.ctx.createLinearGradient(0, 0, 0, this.height);
            bgGrad.addColorStop(0, '#2D1B4E');
            bgGrad.addColorStop(1, '#1E1B4B');
        } else {
            bgGrad = this.ctx.createLinearGradient(0, 0, 0, this.height);
            bgGrad.addColorStop(0, '#030712');
            bgGrad.addColorStop(1, '#0B0F19');
        }
        this.ctx.fillStyle = bgGrad;
        this.ctx.fillRect(0, 0, this.width, this.height);

        // 2. Stars
        if (this.timeOfDay > 0.25) {
            this.ctx.save();
            this.stars.forEach(s => {
                this.ctx.fillStyle = `rgba(255,255,255,${s.alpha * (this.timeOfDay)})`;
                this.ctx.fillRect(s.x, s.y, s.size, s.size);
            });
            this.ctx.restore();
        }

        // 3. Moon (in Night Mode)
        if (this.timeOfDay > 0.6) {
            this.ctx.save();
            this.ctx.fillStyle = 'rgba(254, 240, 138, 0.8)';
            this.ctx.shadowColor = 'rgba(254, 240, 138, 0.6)';
            this.ctx.shadowBlur = 15;
            this.ctx.beginPath();
            this.ctx.arc(this.width - 50, 45, 14, 0, Math.PI * 2);
            this.ctx.fill();
            this.ctx.restore();
        }

        // 4. Moving Clouds
        this.ctx.save();
        this.clouds.forEach(c => {
            this.ctx.fillStyle = 'rgba(255,255,255,0.06)';
            this.ctx.beginPath();
            this.ctx.arc(c.x, c.y, 25 * c.scale, 0, Math.PI * 2);
            this.ctx.arc(c.x + 20 * c.scale, c.y - 10 * c.scale, 20 * c.scale, 0, Math.PI * 2);
            this.ctx.arc(c.x + 40 * c.scale, c.y, 22 * c.scale, 0, Math.PI * 2);
            this.ctx.fill();
        });
        this.ctx.restore();

        // 5. Flight Path Curve
        if (this.state === 'flying') {
            this.ctx.save();
            this.ctx.beginPath();
            this.ctx.moveTo(45, this.height - 45);
            this.ctx.quadraticCurveTo(this.flightX / 2, this.height - 30, this.flightX, this.flightY);
            this.ctx.strokeStyle = this.mode === 'rocket' ? '#3B82F6' : '#22C55E';
            this.ctx.lineWidth = 4;
            this.ctx.shadowColor = this.mode === 'rocket' ? '#3B82F6' : '#22C55E';
            this.ctx.shadowBlur = 12;
            this.ctx.stroke();
            this.ctx.restore();
        }

        // 6. Particle Systems
        this.pm.updateAndDraw(this.ctx);

        // 7. Aircraft Vehicle
        if (this.state !== 'crashed') {
            this.drawCraft(this.flightX, this.flightY);
        }

        // 8. Countdown Overlay
        if (this.state === 'countdown') {
            this.ctx.save();
            this.ctx.fillStyle = '#F59E0B';
            this.ctx.font = '900 3.5rem sans-serif';
            this.ctx.textAlign = 'center';
            this.ctx.textBaseline = 'middle';
            this.ctx.shadowColor = 'rgba(245,158,11,0.8)';
            this.ctx.shadowBlur = 20;
            this.ctx.fillText(`NEXT ROUND IN ${this.countdownVal}s`, this.width / 2, this.height / 2);
            this.ctx.restore();
        }
    }

    drawCraft(x, y) {
        this.ctx.save();
        this.ctx.translate(x, y);

        if (this.mode === 'rocket') {
            this.ctx.rotate(-Math.PI / 4);

            // Engine Flame
            if (this.state === 'flying' || this.state === 'launching') {
                this.ctx.fillStyle = '#FF4500';
                this.ctx.shadowColor = '#FF8C00';
                this.ctx.shadowBlur = 15;
                this.ctx.beginPath();
                this.ctx.moveTo(-6, 20);
                this.ctx.lineTo(0, 35 + Math.random() * 10);
                this.ctx.lineTo(6, 20);
                this.ctx.fill();
            }

            // Body
            this.ctx.fillStyle = '#E2E8F0';
            this.ctx.beginPath();
            this.ctx.moveTo(0, -25);
            this.ctx.quadraticCurveTo(12, 0, 10, 20);
            this.ctx.lineTo(-10, 20);
            this.ctx.quadraticCurveTo(-12, 0, 0, -25);
            this.ctx.fill();

            // Nose Tip
            this.ctx.fillStyle = '#EF4444';
            this.ctx.beginPath();
            this.ctx.moveTo(0, -25);
            this.ctx.quadraticCurveTo(6, -15, 6, -10);
            this.ctx.lineTo(-6, -10);
            this.ctx.quadraticCurveTo(-6, -15, 0, -25);
            this.ctx.fill();

            // Window Glass
            this.ctx.fillStyle = '#38BDF8';
            this.ctx.beginPath();
            this.ctx.arc(0, -2, 5, 0, Math.PI * 2);
            this.ctx.fill();

        } else {
            // Modern Animated Jet Fighter
            this.ctx.rotate(-Math.PI / 12);

            // Jet Afterburner Flame
            if (this.state === 'flying' || this.state === 'launching') {
                this.ctx.fillStyle = '#38BDF8';
                this.ctx.shadowColor = '#38BDF8';
                this.ctx.shadowBlur = 15;
                this.ctx.beginPath();
                this.ctx.moveTo(-25, -3);
                this.ctx.lineTo(-42 - Math.random() * 8, 0);
                this.ctx.lineTo(-25, 3);
                this.ctx.fill();
            }

            // Jet Fuselage
            this.ctx.fillStyle = '#CBD5E1';
            this.ctx.beginPath();
            this.ctx.moveTo(32, 0);
            this.ctx.lineTo(-22, -9);
            this.ctx.lineTo(-26, 9);
            this.ctx.closePath();
            this.ctx.fill();

            // Wings & Wingtip Lights
            this.ctx.fillStyle = '#64748B';
            this.ctx.beginPath();
            this.ctx.moveTo(5, 0);
            this.ctx.lineTo(-16, -26);
            this.ctx.lineTo(-19, -4);
            this.ctx.fill();

            // Green Wing Navigation Light
            this.ctx.fillStyle = '#22C55E';
            this.ctx.shadowColor = '#22C55E';
            this.ctx.shadowBlur = 8;
            this.ctx.beginPath();
            this.ctx.arc(-16, -26, 2.5, 0, Math.PI * 2);
            this.ctx.fill();

            // Cockpit Canopy
            this.ctx.fillStyle = '#0EA5E9';
            this.ctx.shadowColor = '#0EA5E9';
            this.ctx.shadowBlur = 6;
            this.ctx.beginPath();
            this.ctx.ellipse(10, -3, 9, 4.5, 0, 0, Math.PI * 2);
            this.ctx.fill();
        }

        this.ctx.restore();
    }
}

// Attach renderer class globally
window.CrashCanvasRenderer = CrashCanvasRenderer;
