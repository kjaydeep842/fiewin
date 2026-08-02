/**
 * Jet Canvas Renderer (Dedicated Jet Fighter ✈️ Engine)
 */
class JetParticleManager {
    constructor() {
        this.particles = [];
    }

    spawnTrail(x, y, angle) {
        for (let i = 0; i < 2; i++) {
            this.particles.push({
                x: x - Math.cos(angle) * 15 + (Math.random() - 0.5) * 4,
                y: y - Math.sin(angle) * 15 + (Math.random() - 0.5) * 4,
                vx: -Math.cos(angle) * (2 + Math.random() * 2),
                vy: -Math.sin(angle) * (2 + Math.random() * 2),
                radius: Math.random() * 3 + 2,
                alpha: 0.8,
                color: '#06b6d4',
                life: 0,
                maxLife: 20 + Math.random() * 10
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

    clear() {
        this.particles = [];
    }
}

class JetCanvasRenderer {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        if (!this.canvas) return;
        this.ctx = this.canvas.getContext('2d');
        this.particles = new JetParticleManager();

        this.clouds = [];
        this.initClouds();
        this.resize();
        window.addEventListener('resize', () => this.resize());
    }

    initClouds() {
        this.clouds = [];
        for (let i = 0; i < 12; i++) {
            this.clouds.push({
                x: Math.random() * 800,
                y: Math.random() * 300,
                scale: Math.random() * 0.8 + 0.4,
                speed: Math.random() * 0.5 + 0.2,
                opacity: Math.random() * 0.2 + 0.1
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
        grad.addColorStop(0, '#0f172a');
        grad.addColorStop(0.5, '#1e293b');
        grad.addColorStop(1, '#0284c7');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, this.width, this.height);

        // Drifting sky clouds
        ctx.save();
        for (const cloud of this.clouds) {
            cloud.x -= cloud.speed;
            if (cloud.x < -100) cloud.x = this.width + 100;

            ctx.fillStyle = `rgba(255, 255, 255, ${cloud.opacity})`;
            ctx.beginPath();
            ctx.arc(cloud.x, cloud.y, 25 * cloud.scale, 0, Math.PI * 2);
            ctx.arc(cloud.x + 20 * cloud.scale, cloud.y - 10 * cloud.scale, 30 * cloud.scale, 0, Math.PI * 2);
            ctx.arc(cloud.x + 45 * cloud.scale, cloud.y, 25 * cloud.scale, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.restore();
    }

    drawJetFighter(x, y, angle) {
        const ctx = this.ctx;
        ctx.save();
        ctx.translate(x, y);
        ctx.rotate(angle);

        // Jet Fuselage
        ctx.fillStyle = '#cbd5e1';
        ctx.beginPath();
        ctx.moveTo(28, 0);
        ctx.lineTo(-18, -8);
        ctx.lineTo(-24, -4);
        ctx.lineTo(-24, 4);
        ctx.lineTo(-18, 8);
        ctx.closePath();
        ctx.fill();

        // Delta Wings
        ctx.fillStyle = '#94a3b8';
        ctx.beginPath();
        ctx.moveTo(4, 0);
        ctx.lineTo(-12, -26);
        ctx.lineTo(-20, -22);
        ctx.lineTo(-12, 0);
        ctx.lineTo(-20, 22);
        ctx.lineTo(-12, 26);
        ctx.closePath();
        ctx.fill();

        // Glass Canopy
        ctx.fillStyle = '#06b6d4';
        ctx.beginPath();
        ctx.ellipse(8, 0, 10, 4, 0, 0, Math.PI * 2);
        ctx.fill();

        // Cyan Afterburner Flame
        ctx.fillStyle = '#38bdf8';
        ctx.beginPath();
        ctx.moveTo(-24, -3);
        ctx.lineTo(-38 - Math.random() * 8, 0);
        ctx.lineTo(-24, 3);
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
        this.ctx.strokeStyle = '#10b981';
        this.ctx.lineWidth = 4;
        this.ctx.stroke();
        this.ctx.restore();

        if (status === 'FLYING') {
            this.particles.spawnTrail(currentX, currentY, angle);
            this.particles.update();
            this.particles.draw(this.ctx);
            this.drawJetFighter(currentX, currentY, angle);
        } else if (status === 'BETTING_OPEN') {
            this.drawJetFighter(startX, startY, 0);
        } else if (status === 'CRASHED') {
            this.ctx.fillStyle = '#ef4444';
            this.ctx.font = '900 28px sans-serif';
            this.ctx.textAlign = 'center';
            this.ctx.fillText('JET FLEW AWAY!', this.width / 2, this.height / 2 + 50);
        }
    }
}

window.JetCanvasRenderer = JetCanvasRenderer;
