<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $postedScore = filter_input(
        INPUT_POST,
        'max_score',
        FILTER_VALIDATE_INT
    );

    if ($postedScore === false || $postedScore === null) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Puntuación inválida.'
        ]);

        exit;
    }

    $postedScore = max(0, (int) $postedScore);

    if (
        !isset($_SESSION['archery_max_score']) ||
        $postedScore > (int) $_SESSION['archery_max_score']
    ) {
        $_SESSION['archery_max_score'] = $postedScore;
    }

    echo json_encode([
        'success' => true,
        'max_score' => (int) $_SESSION['archery_max_score']
    ]);

    exit;
}

$maxScore = isset($_SESSION['archery_max_score'])
    ? (int) $_SESSION['archery_max_score']
    : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neon Archer</title>

    <style>
        :root {
            --cyan: #00f6ff;
            --pink: #ff1493;
            --yellow: #ffe600;
            --green: #a8ff00;
            --dark: #050611;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            padding: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            background:
                radial-gradient(
                    circle at 50% 0%,
                    rgba(0, 246, 255, 0.16),
                    transparent 35%
                ),
                radial-gradient(
                    circle at 50% 100%,
                    rgba(255, 20, 147, 0.15),
                    transparent 38%
                ),
                var(--dark);
            color: #ffffff;
            font-family: Arial, sans-serif;
            overflow-x: hidden;
        }

        .game-shell {
            width: min(100%, 860px);
            padding: 18px;
            border: 1px solid rgba(0, 246, 255, 0.7);
            background:
                linear-gradient(
                    145deg,
                    rgba(0, 246, 255, 0.08),
                    transparent 32%
                ),
                linear-gradient(
                    325deg,
                    rgba(255, 20, 147, 0.08),
                    transparent 35%
                ),
                rgba(4, 6, 17, 0.94);
            box-shadow:
                0 0 25px rgba(0, 246, 255, 0.25),
                0 0 70px rgba(255, 20, 147, 0.15),
                inset 0 0 30px rgba(0, 246, 255, 0.05);
            clip-path: polygon(
                0 16px,
                16px 0,
                calc(100% - 16px) 0,
                100% 16px,
                100% calc(100% - 16px),
                calc(100% - 16px) 100%,
                16px 100%,
                0 calc(100% - 16px)
            );
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 14px;
        }

        .title {
            color: var(--cyan);
            font-size: 1.45rem;
            font-weight: 900;
            letter-spacing: 0.14em;
            text-shadow:
                0 0 5px var(--cyan),
                0 0 18px var(--cyan);
        }

        .title span {
            color: var(--pink);
        }

        .stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }

        .stat {
            min-width: 92px;
            padding: 8px 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.04);
            text-align: right;
            clip-path: polygon(
                8px 0,
                100% 0,
                100% calc(100% - 8px),
                calc(100% - 8px) 100%,
                0 100%,
                0 8px
            );
        }

        .stat-label {
            display: block;
            color: rgba(255, 255, 255, 0.55);
            font-size: 0.62rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .stat-value {
            display: block;
            color: var(--yellow);
            font-size: 1rem;
            font-weight: 900;
            text-shadow: 0 0 8px rgba(255, 230, 0, 0.8);
        }

        .canvas-wrapper {
            position: relative;
            width: 800px;
            max-width: 100%;
            margin: 0 auto;
            overflow: hidden;
            border: 2px solid var(--cyan);
            background: #070817;
            box-shadow:
                0 0 12px var(--cyan),
                0 0 35px rgba(0, 246, 255, 0.5),
                inset 0 0 30px rgba(0, 246, 255, 0.12);
        }

        canvas {
            display: block;
            width: 800px;
            height: 520px;
            max-width: 100%;
            aspect-ratio: 800 / 520;
            cursor: crosshair;
            touch-action: none;
        }

        .canvas-wrapper::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                repeating-linear-gradient(
                    to bottom,
                    rgba(255, 255, 255, 0.025) 0,
                    rgba(255, 255, 255, 0.025) 1px,
                    transparent 1px,
                    transparent 5px
                );
            mix-blend-mode: screen;
        }

        .overlay {
            position: absolute;
            inset: 0;
            z-index: 5;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
            background: rgba(2, 3, 12, 0.75);
            backdrop-filter: blur(5px);
        }

        .overlay.hidden {
            display: none;
        }

        .panel {
            width: min(100%, 490px);
            padding: 28px 22px;
            border: 1px solid var(--pink);
            background: rgba(7, 10, 28, 0.96);
            box-shadow:
                0 0 15px rgba(255, 20, 147, 0.8),
                inset 0 0 24px rgba(255, 20, 147, 0.1);
            text-align: center;
            clip-path: polygon(
                12px 0,
                100% 0,
                100% calc(100% - 12px),
                calc(100% - 12px) 100%,
                0 100%,
                0 12px
            );
        }

        .panel h1 {
            margin: 0 0 10px;
            color: var(--cyan);
            font-size: clamp(2rem, 8vw, 3.2rem);
            letter-spacing: 0.12em;
            text-shadow:
                0 0 5px var(--cyan),
                0 0 22px var(--cyan);
        }

        .panel h1 span {
            color: var(--pink);
        }

        .panel p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.45;
        }

        .danger {
            color: #ff477e;
            font-size: 1.9rem;
            font-weight: 900;
            text-shadow: 0 0 14px #ff477e;
        }

        .controls {
            margin: 18px 0;
            color: var(--green);
            font-family: monospace;
            font-size: 0.87rem;
            line-height: 1.6;
        }

        .main-button {
            padding: 13px 25px;
            border: 1px solid var(--cyan);
            color: #001114;
            background: var(--cyan);
            box-shadow:
                0 0 8px var(--cyan),
                0 0 24px rgba(0, 246, 255, 0.75);
            cursor: pointer;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            transition: transform 120ms ease, background 120ms ease;
        }

        .main-button:hover {
            background: #ffffff;
            transform: translateY(-2px) scale(1.03);
        }

        .main-button:active {
            transform: translateY(1px) scale(0.98);
        }

        .footer {
            margin-top: 13px;
            color: rgba(255, 255, 255, 0.5);
            font-family: monospace;
            font-size: 0.72rem;
            text-align: center;
        }

        @media (max-width: 650px) {
            body {
                padding: 10px;
            }

            .game-shell {
                padding: 12px;
            }

            .header {
                align-items: flex-start;
                flex-direction: column;
            }

            .stats {
                width: 100%;
                justify-content: stretch;
            }

            .stat {
                flex: 1;
            }
        }
    </style>
</head>
<body>

<main class="game-shell">
    <header class="header">
        <div class="title">
            NEON<span>ARCHER</span>
        </div>

        <div class="stats">
            <div class="stat">
                <span class="stat-label">Puntos</span>
                <span class="stat-value" id="scoreValue">0</span>
            </div>

            <div class="stat">
                <span class="stat-label">Tiempo</span>
                <span class="stat-value" id="timeValue">45</span>
            </div>

            <div class="stat">
                <span class="stat-label">Récord PHP</span>
                <span class="stat-value" id="bestValue">
                    <?php echo htmlspecialchars(
                        (string) $maxScore,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </span>
            </div>
        </div>
    </header>

    <div class="canvas-wrapper">
        <canvas id="gameCanvas" width="800" height="520"></canvas>

        <div class="overlay" id="startOverlay">
            <div class="panel">
                <h1>NEON<span>ARCHER</span></h1>

                <p>
                    Apunta a los objetivos móviles y consigue la máxima
                    puntuación antes de que termine el tiempo.
                </p>

                <div class="controls">
                    Mueve el ratón o el dedo para apuntar.<br>
                    Haz clic o pulsa ESPACIO para disparar.<br>
                    Usa las flechas para ajustar manualmente el arco.
                </div>

                <button class="main-button" id="startButton">
                    Comenzar misión
                </button>
            </div>
        </div>

        <div class="overlay hidden" id="gameOverOverlay">
            <div class="panel">
                <div class="danger">MISIÓN TERMINADA</div>

                <p id="finalScore">
                    Puntuación: 0
                </p>

                <p id="recordMessage">
                    Guardando puntuación...
                </p>

                <button class="main-button" id="restartButton">
                    Nueva misión
                </button>
            </div>
        </div>
    </div>

    <div class="footer">
        PRECISIÓN · VELOCIDAD · ESTRATEGIA · CADA DISPARO CUENTA
    </div>
</main>

<script>
"use strict";

const canvas = document.getElementById("gameCanvas");
const context = canvas.getContext("2d");

const scoreValue = document.getElementById("scoreValue");
const timeValue = document.getElementById("timeValue");
const bestValue = document.getElementById("bestValue");
const startOverlay = document.getElementById("startOverlay");
const gameOverOverlay = document.getElementById("gameOverOverlay");
const startButton = document.getElementById("startButton");
const restartButton = document.getElementById("restartButton");
const finalScore = document.getElementById("finalScore");
const recordMessage = document.getElementById("recordMessage");

const WIDTH = 800;
const HEIGHT = 520;
const GAME_DURATION = 45;

const archer = {
    x: 92,
    y: 410,
    angle: -0.55
};

let targets = [];
let arrows = [];
let particles = [];
let floatingTexts = [];

let score = 0;
let bestScore = <?php echo json_encode($maxScore); ?>;
let timeRemaining = GAME_DURATION;
let combo = 0;
let wind = 0;
let gameState = "menu";
let lastTime = 0;
let targetSpawnTimer = 0;
let targetId = 0;
let screenShake = 0;
let pulse = 0;
let audioContext = null;

function randomFloat(minimum, maximum) {
    return Math.random() * (maximum - minimum) + minimum;
}

function randomInteger(minimum, maximum) {
    return Math.floor(
        Math.random() * (maximum - minimum + 1)
    ) + minimum;
}

function clamp(value, minimum, maximum) {
    return Math.max(minimum, Math.min(maximum, value));
}

function formatNumber(value) {
    return Math.floor(value).toLocaleString("es-ES");
}

function getAudioContext() {
    if (!audioContext) {
        const AudioContextClass =
            window.AudioContext || window.webkitAudioContext;

        if (AudioContextClass) {
            audioContext = new AudioContextClass();
        }
    }

    return audioContext;
}

function playSound(type) {
    const currentAudioContext = getAudioContext();

    if (!currentAudioContext) {
        return;
    }

    if (currentAudioContext.state === "suspended") {
        currentAudioContext.resume();
    }

    const oscillator = currentAudioContext.createOscillator();
    const gain = currentAudioContext.createGain();
    const now = currentAudioContext.currentTime;

    oscillator.connect(gain);
    gain.connect(currentAudioContext.destination);

    if (type === "shoot") {
        oscillator.type = "triangle";
        oscillator.frequency.setValueAtTime(260, now);
        oscillator.frequency.exponentialRampToValueAtTime(
            720,
            now + 0.12
        );

        gain.gain.setValueAtTime(0.09, now);
        gain.gain.exponentialRampToValueAtTime(
            0.001,
            now + 0.12
        );

        oscillator.start(now);
        oscillator.stop(now + 0.12);
    }

    if (type === "hit") {
        oscillator.type = "sine";
        oscillator.frequency.setValueAtTime(520, now);
        oscillator.frequency.exponentialRampToValueAtTime(
            1150,
            now + 0.2
        );

        gain.gain.setValueAtTime(0.16, now);
        gain.gain.exponentialRampToValueAtTime(
            0.001,
            now + 0.2
        );

        oscillator.start(now);
        oscillator.stop(now + 0.2);
    }

    if (type === "miss") {
        oscillator.type = "sawtooth";
        oscillator.frequency.setValueAtTime(120, now);
        oscillator.frequency.exponentialRampToValueAtTime(
            55,
            now + 0.16
        );

        gain.gain.setValueAtTime(0.07, now);
        gain.gain.exponentialRampToValueAtTime(
            0.001,
            now + 0.16
        );

        oscillator.start(now);
        oscillator.stop(now + 0.16);
    }
}

function resetGame() {
    score = 0;
    timeRemaining = GAME_DURATION;
    combo = 0;
    wind = randomFloat(-0.18, 0.18);
    targetSpawnTimer = 0;
    targetId = 0;
    screenShake = 0;
    pulse = 0;

    archer.angle = -0.55;

    targets = [];
    arrows = [];
    particles = [];
    floatingTexts = [];

    gameState = "running";
    lastTime = performance.now();

    scoreValue.textContent = "0";
    timeValue.textContent = String(GAME_DURATION);
    bestValue.textContent = formatNumber(bestScore);

    startOverlay.classList.add("hidden");
    gameOverOverlay.classList.add("hidden");

    for (let index = 0; index < 4; index += 1) {
        spawnTarget(true);
    }
}

function spawnTarget(isInitialTarget) {
    const targetRadius = randomInteger(22, 34);
    const targetType = Math.random() > 0.78
        ? "gold"
        : "normal";

    const target = {
        id: targetId,
        x: isInitialTarget
            ? randomFloat(350, 700)
            : randomFloat(370, 740),
        y: randomFloat(90, 330),
        radius: targetRadius,
        speedX: randomFloat(-85, 85),
        speedY: randomFloat(-45, 45),
        pulse: randomFloat(0, Math.PI * 2),
        type: targetType,
        alive: true
    };

    targetId += 1;
    targets.push(target);
}

function createParticles(x, y, color, amount) {
    for (let index = 0; index < amount; index += 1) {
        const angle = randomFloat(0, Math.PI * 2);
        const speed = randomFloat(50, 260);

        particles.push({
            x: x,
            y: y,
            velocityX: Math.cos(angle) * speed,
            velocityY: Math.sin(angle) * speed,
            size: randomFloat(2, 6),
            life: randomFloat(0.45, 1),
            maxLife: 1,
            color: color
        });
    }
}

function addFloatingText(x, y, text, color) {
    floatingTexts.push({
        x: x,
        y: y,
        text: text,
        color: color,
        life: 1,
        velocityY: -42
    });
}

function updateParticles(deltaTime) {
    for (
        let index = particles.length - 1;
        index >= 0;
        index -= 1
    ) {
        const particle = particles[index];

        particle.x += particle.velocityX * deltaTime;
        particle.y += particle.velocityY * deltaTime;
        particle.velocityY += 110 * deltaTime;
        particle.life -= deltaTime;

        if (particle.life <= 0) {
            particles.splice(index, 1);
        }
    }

    for (
        let index = floatingTexts.length - 1;
        index >= 0;
        index -= 1
    ) {
        const floatingText = floatingTexts[index];

        floatingText.y += floatingText.velocityY * deltaTime;
        floatingText.life -= deltaTime;

        if (floatingText.life <= 0) {
            floatingTexts.splice(index, 1);
        }
    }
}

function getPointerPosition(event) {
    const bounds = canvas.getBoundingClientRect();

    return {
        x: (event.clientX - bounds.left) * WIDTH / bounds.width,
        y: (event.clientY - bounds.top) * HEIGHT / bounds.height
    };
}

function aimAt(x, y) {
    const deltaX = x - archer.x;
    const deltaY = y - archer.y;

    archer.angle = clamp(
        Math.atan2(deltaY, deltaX),
        -1.48,
        -0.08
    );
}

function shootArrow() {
    if (gameState !== "running") {
        return;
    }

    const arrowSpeed = 690;
    const startX = archer.x + Math.cos(archer.angle) * 38;
    const startY = archer.y + Math.sin(archer.angle) * 38;

    arrows.push({
        x: startX,
        y: startY,
        previousX: startX,
        previousY: startY,
        velocityX: Math.cos(archer.angle) * arrowSpeed,
        velocityY: Math.sin(archer.angle) * arrowSpeed,
        rotation: archer.angle,
        life: 4
    });

    createParticles(startX, startY, "#00f6ff", 5);
    playSound("shoot");
}

function updateTargets(deltaTime) {
    for (const target of targets) {
        target.pulse += deltaTime * 5;
        target.x += target.speedX * deltaTime;
        target.y += target.speedY * deltaTime;

        if (
            target.x - target.radius < 315 ||
            target.x + target.radius > WIDTH - 22
        ) {
            target.speedX *= -1;
            target.x = clamp(
                target.x,
                315 + target.radius,
                WIDTH - 22 - target.radius
            );
        }

        if (
            target.y - target.radius < 62 ||
            target.y + target.radius > 355
        ) {
            target.speedY *= -1;
            target.y = clamp(
                target.y,
                62 + target.radius,
                355 - target.radius
            );
        }
    }
}

function updateArrows(deltaTime) {
    for (
        let arrowIndex = arrows.length - 1;
        arrowIndex >= 0;
        arrowIndex -= 1
    ) {
        const arrow = arrows[arrowIndex];

        arrow.previousX = arrow.x;
        arrow.previousY = arrow.y;

        arrow.velocityX += wind * 35 * deltaTime;
        arrow.velocityY += 105 * deltaTime;

        arrow.x += arrow.velocityX * deltaTime;
        arrow.y += arrow.velocityY * deltaTime;
        arrow.rotation = Math.atan2(
            arrow.velocityY,
            arrow.velocityX
        );

        arrow.life -= deltaTime;

        let arrowHitTarget = false;

        for (
            let targetIndex = targets.length - 1;
            targetIndex >= 0;
            targetIndex -= 1
        ) {
            const target = targets[targetIndex];

            const distanceX = arrow.x - target.x;
            const distanceY = arrow.y - target.y;
            const hitDistance = Math.sqrt(
                distanceX * distanceX +
                distanceY * distanceY
            );

            if (hitDistance <= target.radius) {
                registerHit(target, hitDistance);
                targets.splice(targetIndex, 1);
                arrowHitTarget = true;
                break;
            }
        }

        if (arrowHitTarget) {
            arrows.splice(arrowIndex, 1);
            continue;
        }

        if (
            arrow.life <= 0 ||
            arrow.x < -80 ||
            arrow.x > WIDTH + 80 ||
            arrow.y < -80 ||
            arrow.y > HEIGHT + 100
        ) {
            arrows.splice(arrowIndex, 1);
            combo = 0;
            playSound("miss");
        }
    }
}

function registerHit(target, distanceFromCenter) {
    const accuracy = distanceFromCenter / target.radius;
    let points = 10;

    if (accuracy <= 0.25) {
        points = 100;
    } else if (accuracy <= 0.55) {
        points = 50;
    } else if (accuracy <= 0.82) {
        points = 25;
    }

    if (target.type === "gold") {
        points *= 2;
    }

    combo += 1;

    const comboMultiplier = Math.min(5, combo);
    const totalPoints = points * comboMultiplier;

    score += totalPoints;

    scoreValue.textContent = formatNumber(score);

    createParticles(
        target.x,
        target.y,
        target.type === "gold"
            ? "#ffe600"
            : "#ff1493",
        34
    );

    addFloatingText(
        target.x,
        target.y,
        "+" + totalPoints,
        target.type === "gold"
            ? "#ffe600"
            : "#a8ff00"
    );

    playSound("hit");
    screenShake = 5;

    spawnTarget(false);
}

function update(deltaTime) {
    pulse += deltaTime;

    if (screenShake > 0) {
        screenShake = Math.max(
            0,
            screenShake - deltaTime * 25
        );
    }

    updateParticles(deltaTime);

    if (gameState !== "running") {
        return;
    }

    timeRemaining -= deltaTime;
    timeValue.textContent = String(
        Math.max(0, Math.ceil(timeRemaining))
    );

    wind += randomFloat(-0.025, 0.025) * deltaTime;
    wind = clamp(wind, -0.35, 0.35);

    updateTargets(deltaTime);
    updateArrows(deltaTime);

    targetSpawnTimer -= deltaTime;

    if (targetSpawnTimer <= 0) {
        if (targets.length < 6) {
            spawnTarget(false);
        }

        targetSpawnTimer = randomFloat(0.8, 1.8);
    }

    if (timeRemaining <= 0) {
        endGame();
    }
}

function drawBackground() {
    const backgroundGradient = context.createLinearGradient(
        0,
        0,
        0,
        HEIGHT
    );

    backgroundGradient.addColorStop(0, "#090b25");
    backgroundGradient.addColorStop(0.55, "#101733");
    backgroundGradient.addColorStop(1, "#050611");

    context.fillStyle = backgroundGradient;
    context.fillRect(0, 0, WIDTH, HEIGHT);

    context.strokeStyle = "rgba(0, 246, 255, 0.09)";
    context.lineWidth = 1;

    for (let x = 0; x <= WIDTH; x += 40) {
        context.beginPath();
        context.moveTo(x, 0);
        context.lineTo(x, HEIGHT);
        context.stroke();
    }

    for (let y = 0; y <= HEIGHT; y += 40) {
        context.beginPath();
        context.moveTo(0, y);
        context.lineTo(WIDTH, y);
        context.stroke();
    }

    context.fillStyle = "rgba(255, 20, 147, 0.05)";
    context.fillRect(0, 0, WIDTH, HEIGHT);

    context.fillStyle = "rgba(0, 246, 255, 0.08)";
    context.fillRect(0, 370, WIDTH, 150);
}

function drawWindIndicator() {
    const centerX = 680;
    const centerY = 456;
    const length = 70;
    const windDirection = wind >= 0 ? 1 : -1;
    const strength = Math.abs(wind);

    context.save();

    context.strokeStyle = "#00f6ff";
    context.fillStyle = "#00f6ff";
    context.shadowColor = "#00f6ff";
    context.shadowBlur = 10;
    context.lineWidth = 3;

    context.beginPath();
    context.moveTo(
        centerX - length / 2,
        centerY
    );

    context.lineTo(
        centerX + length / 2 * windDirection,
        centerY
    );

    context.stroke();

    context.beginPath();
    context.moveTo(
        centerX + length / 2 * windDirection,
        centerY
    );

    context.lineTo(
        centerX + (length / 2 - 12) * windDirection,
        centerY - 8
    );

    context.lineTo(
        centerX + (length / 2 - 12) * windDirection,
        centerY + 8
    );

    context.closePath();
    context.fill();

    context.shadowBlur = 0;
    context.font = "12px monospace";
    context.textAlign = "center";
    context.fillText(
        "VIENTO " + Math.round(strength * 100),
        centerX,
        centerY + 25
    );

    context.restore();
}

function drawTargets() {
    for (const target of targets) {
        const glowColor = target.type === "gold"
            ? "#ffe600"
            : "#ff1493";

        const outerRadius = target.radius + 7 +
            Math.sin(target.pulse) * 2;

        context.save();

        context.fillStyle = "rgba(255, 255, 255, 0.08)";
        context.strokeStyle = glowColor;
        context.shadowColor = glowColor;
        context.shadowBlur = 18;
        context.lineWidth = 3;

        context.beginPath();
        context.arc(
            target.x,
            target.y,
            outerRadius,
            0,
            Math.PI * 2
        );
        context.fill();
        context.stroke();

        context.shadowBlur = 0;

        context.fillStyle = "#17152b";
        context.beginPath();
        context.arc(
            target.x,
            target.y,
            target.radius,
            0,
            Math.PI * 2
        );
        context.fill();

        context.strokeStyle = "#ffffff";
        context.lineWidth = 2;

        context.beginPath();
        context.arc(
            target.x,
            target.y,
            target.radius * 0.76,
            0,
            Math.PI * 2
        );
        context.stroke();

        context.strokeStyle = glowColor;

        context.beginPath();
        context.arc(
            target.x,
            target.y,
            target.radius * 0.48,
            0,
            Math.PI * 2
        );
        context.stroke();

        context.fillStyle = target.type === "gold"
            ? "#ffe600"
            : "#ff1493";

        context.beginPath();
        context.arc(
            target.x,
            target.y,
            target.radius * 0.22,
            0,
            Math.PI * 2
        );
        context.fill();

        context.restore();
    }
}

function drawArcher() {
    const handX = archer.x + Math.cos(archer.angle) * 30;
    const handY = archer.y + Math.sin(archer.angle) * 30;

    context.save();

    context.translate(archer.x, archer.y);

    context.fillStyle = "#101326";
    context.strokeStyle = "#00f6ff";
    context.shadowColor = "#00f6ff";
    context.shadowBlur = 16;
    context.lineWidth = 3;

    context.beginPath();
    context.arc(0, -24, 19, 0, Math.PI * 2);
    context.fill();
    context.stroke();

    context.fillStyle = "#ff1493";
    context.shadowColor = "#ff1493";
    context.shadowBlur = 12;

    context.beginPath();
    context.arc(0, -24, 7, 0, Math.PI * 2);
    context.fill();

    context.strokeStyle = "#00f6ff";
    context.lineWidth = 11;
    context.lineCap = "round";
    context.shadowColor = "#00f6ff";

    context.beginPath();
    context.moveTo(-5, -4);
    context.lineTo(0, 42);
    context.stroke();

    context.lineWidth = 7;

    context.beginPath();
    context.moveTo(0, 8);
    context.lineTo(Math.cos(archer.angle) * 42, Math.sin(archer.angle) * 42);
    context.stroke();

    context.restore();

    context.save();

    context.strokeStyle = "#ffe600";
    context.shadowColor = "#ffe600";
    context.shadowBlur = 12;
    context.lineWidth = 4;

    context.beginPath();
    context.arc(
        handX - 8,
        handY,
        38,
        archer.angle - 1.0,
        archer.angle + 1.0
    );
    context.stroke();

    context.strokeStyle = "#ffffff";
    context.lineWidth = 2;

    context.beginPath();
    context.moveTo(
        handX - Math.cos(archer.angle) * 38,
        handY - Math.sin(archer.angle) * 38
    );

    context.lineTo(handX, handY);
    context.stroke();

    context.restore();

    context.save();

    context.strokeStyle = "rgba(255, 230, 0, 0.3)";
    context.setLineDash([7, 8]);
    context.lineWidth = 1;

    context.beginPath();
    context.moveTo(handX, handY);
    context.lineTo(
        handX + Math.cos(archer.angle) * 160,
        handY + Math.sin(archer.angle) * 160
    );
    context.stroke();

    context.setLineDash([]);
    context.restore();
}

function drawArrows() {
    for (const arrow of arrows) {
        context.save();

        context.translate(arrow.x, arrow.y);
        context.rotate(arrow.rotation);

        context.strokeStyle = "#ffe600";
        context.shadowColor = "#ffe600";
        context.shadowBlur = 10;
        context.lineWidth = 3;
        context.lineCap = "round";

        context.beginPath();
        context.moveTo(-25, 0);
        context.lineTo(18, 0);
        context.stroke();

        context.fillStyle = "#ffffff";
        context.beginPath();
        context.moveTo(22, 0);
        context.lineTo(10, -6);
        context.lineTo(10, 6);
        context.closePath();
        context.fill();

        context.restore();
    }
}

function drawParticles() {
    for (const particle of particles) {
        const alpha = Math.max(
            0,
            particle.life / particle.maxLife
        );

        context.save();

        context.globalAlpha = alpha;
        context.fillStyle = particle.color;
        context.shadowColor = particle.color;
        context.shadowBlur = 12;

        context.beginPath();
        context.arc(
            particle.x,
            particle.y,
            particle.size * alpha,
            0,
            Math.PI * 2
        );
        context.fill();

        context.restore();
    }

    for (const floatingText of floatingTexts) {
        context.save();

        context.globalAlpha = floatingText.life;
        context.fillStyle = floatingText.color;
        context.shadowColor = floatingText.color;
        context.shadowBlur = 12;
        context.font = "900 22px Arial";
        context.textAlign = "center";

        context.fillText(
            floatingText.text,
            floatingText.x,
            floatingText.y
        );

        context.restore();
    }
}

function drawHud() {
    context.save();

    context.fillStyle = "rgba(4, 6, 17, 0.75)";
    context.fillRect(20, 18, 238, 58);

    context.strokeStyle = "rgba(0, 246, 255, 0.55)";
    context.strokeRect(20, 18, 238, 58);

    context.fillStyle = "#00f6ff";
    context.font = "13px monospace";
    context.textAlign = "left";
    context.fillText(
        "COMBO x" + Math.max(1, Math.min(5, combo)),
        34,
        42
    );

    context.fillStyle = "#a8ff00";
    context.fillText(
        "OBJETIVOS: " + targets.length,
        34,
        62
    );

    context.restore();
}

function draw() {
    context.save();

    if (screenShake > 0) {
        context.translate(
            randomFloat(-screenShake, screenShake),
            randomFloat(-screenShake, screenShake)
        );
    }

    context.clearRect(
        -30,
        -30,
        WIDTH + 60,
        HEIGHT + 60
    );

    drawBackground();
    drawTargets();
    drawArrows();
    drawArcher();
    drawParticles();
    drawWindIndicator();
    drawHud();

    context.restore();
}

function endGame() {
    if (gameState === "gameover") {
        return;
    }

    gameState = "gameover";

    finalScore.textContent =
        "Puntuación final: " + formatNumber(score);

    recordMessage.textContent =
        "Guardando récord en el servidor...";

    gameOverOverlay.classList.remove("hidden");

    saveScore(score);
}

async function saveScore(finalScoreValue) {
    try {
        const formData = new FormData();

        formData.append(
            "max_score",
            String(Math.max(0, Math.floor(finalScoreValue)))
        );

        const response = await fetch(window.location.href, {
            method: "POST",
            body: formData,
            credentials: "same-origin",
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        });

        if (!response.ok) {
            throw new Error("Respuesta HTTP inválida.");
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error("El servidor rechazó la puntuación.");
        }

        const previousBest = bestScore;
        bestScore = Number(data.max_score);

        bestValue.textContent = formatNumber(bestScore);

        if (score > previousBest) {
            recordMessage.textContent =
                "¡NUEVO RÉCORD DE PUNTUACIÓN!";

            recordMessage.style.color = "#ffe600";
        } else {
            recordMessage.textContent =
                "Récord actual: " + formatNumber(bestScore);

            recordMessage.style.color = "#00f6ff";
        }
    } catch (error) {
        recordMessage.textContent =
            "No se pudo guardar la puntuación.";

        recordMessage.style.color = "#ff477e";
    }
}

function handlePointerMove(event) {
    if (gameState !== "running") {
        return;
    }

    const pointer = getPointerPosition(event);

    aimAt(pointer.x, pointer.y);
}

function handlePointerDown(event) {
    event.preventDefault();

    if (gameState === "menu" || gameState === "gameover") {
        return;
    }

    if (gameState !== "running") {
        return;
    }

    const pointer = getPointerPosition(event);

    aimAt(pointer.x, pointer.y);
    shootArrow();
}

function handleKeyDown(event) {
    if (
        event.code === "Space" ||
        event.code === "ArrowUp" ||
        event.code === "ArrowDown" ||
        event.code === "ArrowLeft" ||
        event.code === "ArrowRight"
    ) {
        event.preventDefault();
    }

    if (event.code === "Space") {
        if (gameState === "running") {
            shootArrow();
        }

        return;
    }

    if (gameState !== "running") {
        return;
    }

    if (event.code === "ArrowUp") {
        archer.angle -= 0.06;
    }

    if (event.code === "ArrowDown") {
        archer.angle += 0.06;
    }

    if (event.code === "ArrowLeft") {
        archer.angle -= 0.06;
    }

    if (event.code === "ArrowRight") {
        archer.angle += 0.06;
    }

    archer.angle = clamp(
        archer.angle,
        -1.48,
        -0.08
    );
}

function loop(currentTime) {
    const deltaTime = Math.min(
        0.05,
        Math.max(0.001, (currentTime - lastTime) / 1000)
    );

    lastTime = currentTime;

    update(deltaTime);
    draw();

    requestAnimationFrame(loop);
}

startButton.addEventListener("click", function() {
    resetGame();
});

restartButton.addEventListener("click", function() {
    resetGame();
});

canvas.addEventListener("pointermove", handlePointerMove);
canvas.addEventListener("pointerdown", handlePointerDown);
window.addEventListener("keydown", handleKeyDown);

lastTime = performance.now();
requestAnimationFrame(loop);
</script>

</body>
</html>