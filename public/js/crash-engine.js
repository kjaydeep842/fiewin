/**
 * Crash Canvas Renderer (Dedicated Space Rocket 🚀 Engine)
 */
class CrashParticleManager {
    constructor() {
        this.particles = [];
    }

    spawnPlume(x, y, angle) {
        for (let i = 0; i < 3; i++) {
            this.particles.push({
                x: x - Math.cos(angle) * 18 + (Math.random() - 0.5) * 6,
                y: y - Math.sin(angle) * 18 + (Math.random() - 0.5) * 6,
                vx: -Math.cos(angle) * (3 + Math.random() * 3),
                vy: -Math.sin(angle) * (3 + Math.random() * 3),
                radius: Math.random() * 5 + 3,
                alpha: 0.9,
                color: Math.random() > 0.4 ? '#f97316' : '#eab308',
                life: 0,
                maxLife: 25 + Math.random() * 10
            });
        }
    }

    update() {
        for (let i = this.particles.length - 1; i >= 0; i--) {
            const p = this.particles[i];
            p.x += p.vx;
            p.y += p.vy;
            p.life++;
            p.alpha = 1 - (p.life / p.maxLife);
            if (p.life >= p.maxLife) {
                this.particles.splice(i, 1);
            }
        }
    }

    draw(ctx) {
        ctx.save();
        for (const p of this.particles) {
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fillStyle = p.color;
            ctx.globalAlpha = Math.max(0, p.alpha);
            ctx.fill();
        }
        ctx.restore();
    }
}

class CrashCanvasRenderer {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        if (!this.canvas) return;
        this.ctx = this.canvas.getContext('2d');
        this.particles = new CrashParticleManager();

        this.stars = [];
        this.initStars();
        this.resize();
        window.addEventListener('resize', () => this.resize());
    }

    initStars() {
        this.stars = [];
        for (let i = 0; i < 40; i++) {
            this.stars.push({
                x: Math.random() * 800,
                y: Math.random() * 400,
                radius: Math.random() * 1.8 + 0.5,
                alpha: Math.random() * 0.8 + 0.2
            });
        }
    }

    resize() {
        if (!this.canvas) return;
        const rect = this.canvas.getBoundingClientRect();
        this.width = rect.width || 600;
        this.height = rect.height || 350;
        this.canvas.width = this.width * window.devicePixelRatio;
        this.canvas.height = this.height * window.devicePixelRatio;
        this.ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
    }

    drawBackground() {
        const ctx = this.ctx;
        const grad = ctx.createLinearGradient(0, 0, 0, this.height);
        grad.addColorStop(0, '#030712');
        grad.addColorStop(0.6, '#0f172a');
        grad.addColorStop(1, '#1e1b4b');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, this.width, this.height);

        // Twinkling Space Starfield
        ctx.save();
        for (const star of this.stars) {
            ctx.fillStyle = '#ffffff';
            ctx.globalAlpha = star.alpha;
            ctx.beginPath();
            ctx.arc(star.x, star.y, star.radius, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.restore();
    }

    drawRocket(x, y, angle) {
        const ctx = this.ctx;
        ctx.save();
        ctx.translate(x, y);
        ctx.rotate(angle);

        // Rocket Body
        ctx.fillStyle = '#e2e8f0';
        ctx.beginPath();
        ctx.moveTo(26, 0);
        ctx.lineTo(-14, -8);
        ctx.lineTo(-14, 8);
        ctx.closePath();
        ctx.fill();

        // Crimson Nosecone
        ctx.fillStyle = '#ef4444';
        ctx.beginPath();
        ctx.moveTo(26, 0);
        ctx.lineTo(12, -7);
        ctx.lineTo(12, 7);
        ctx.closePath();
        ctx.fill();

        // Side Fins
        ctx.fillStyle = '#dc2626';
        ctx.beginPath();
        ctx.moveTo(-6, -8);
        ctx.lineTo(-18, -16);
        ctx.lineTo(-14, -4);
        ctx.closePath();
        ctx.fill();

        ctx.beginPath();
        ctx.moveTo(-6, 8);
        ctx.lineTo(-18, 16);
        ctx.lineTo(-14, 4);
        ctx.closePath();
        ctx.fill();

        // Thruster Fire Plume
        ctx.fillStyle = '#f97316';
        ctx.beginPath();
        ctx.moveTo(-14, -4);
        ctx.lineTo(-32 - Math.random() * 8, 0);
        ctx.lineTo(-14, 4);
        ctx.closePath();
        ctx.fill();

        ctx.restore();
    }

    renderFrame(multiplierProgress, status) {
        if (!this.ctx) return;
        this.ctx.clearRect(0, 0, this.width, this.height);
        this.drawBackground();

        const padding = 50;
        const startX = padding;
        const startY = this.height - padding;
        const endX = this.width - padding;
        const endY = padding + 20;

        const clampedProgress = Math.min(Math.max(multiplierProgress, 0), 1);
        const currentX = startX + (endX - startX) * clampedProgress;
        const currentY = startY - (startY - endY) * Math.pow(clampedProgress, 0.85);

        const dx = (endX - startX);
        const dy = -(startY - endY) * 0.85 * Math.pow(Math.max(clampedProgress, 0.05), -0.15);
        const angle = Math.atan2(dy, dx);

        // Trajectory line
        this.ctx.save();
        this.ctx.beginPath();
        this.ctx.moveTo(startX, startY);
        this.ctx.quadraticCurveTo(startX + (currentX - startX) * 0.5, startY, currentX, currentY);
        this.ctx.strokeStyle = '#3b82f6';
        this.ctx.lineWidth = 4;
        this.ctx.stroke();
        this.ctx.restore();

        if (status === 'FLYING') {
            this.particles.spawnPlume(currentX, currentY, angle);
            this.particles.update();
            this.particles.draw(this.ctx);
            this.drawRocket(currentX, currentY, angle);
        } else if (status === 'BETTING_OPEN') {
            this.drawRocket(startX, startY, 0);
        } else if (status === 'CRASHED') {
            this.ctx.fillStyle = '#ef4444';
            this.ctx.font = '900 28px sans-serif';
            this.ctx.textAlign = 'center';
            this.ctx.fillText('ROCKET CRASHED!', this.width / 2, this.height / 2 + 50);
        }
    }
}

window.CrashCanvasRenderer = CrashCanvasRenderer;
