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
        !isset($_SESSION['duck_max_score']) ||
        $postedScore > (int) $_SESSION['duck_max_score']
    ) {
        $_SESSION['duck_max_score'] = $postedScore;
    }

    echo json_encode([
        'success' => true,
        'max_score' => (int) $_SESSION['duck_max_score']
    ]);

    exit;
}

$maxScore = isset($_SESSION['duck_max_score'])
    ? (int) $_SESSION['duck_max_score']
    : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neon Duck Hunt</title>

    <style>
        :root {
            --cyan: #00f6ff;
            --pink: #ff1493;
            --yellow: #ffe600;
            --green: #a8ff00;
            --red: #ff3d71;
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
            min-width: 86px;
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
            color: var(--red);
            font-size: 1.9rem;
            font-weight: 900;
            text-shadow: 0 0 14px var(--red);
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
                min-width: 0;
            }
        }
    </style>
</head>
<body>

<main class="game-shell">
    <header class="header">
        <div class="title">
            NEON<span>DUCK</span>
        </div>

        <div class="stats">
            <div class="stat">
                <span class="stat-label">Puntos</span>
                <span class="stat-value" id="scoreValue">0</span>
            </div>

            <div class="stat">
                <span class="stat-label">Ronda</span>
                <span class="stat-value" id="roundValue">1</span>
            </div>

            <div class="stat">
                <span class="stat-label">Munición</span>
                <span class="stat-value" id="ammoValue">3</span>
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
                <h1>NEON<span>DUCK</span></h1>

                <p>
                    El clásico juego de patos vuelve en versión cyberpunk.
                    Apunta con el mouse y dispara con clic.
                </p>

                <div class="controls">
                    CLIC IZQUIERDO: DISPARAR<br>
                    Derriba los patos antes de quedarte sin munición.<br>
                    Cada ronda aumenta la velocidad.
                </div>

                <button class="main-button" id="startButton">
                    Comenzar cacería
                </button>
            </div>
        </div>

        <div class="overlay hidden" id="gameOverOverlay">
            <div class="panel">
                <div class="danger">GAME OVER</div>

                <p id="finalScore">
                    Puntuación final: 0
                </p>

                <p id="recordMessage">
                    Guardando récord...
                </p>

                <button class="main-button" id="restartButton">
                    Nueva partida
                </button>
            </div>
        </div>
    </div>

    <div class="footer">
        APUNTA CON EL MOUSE · DISPARA CON CLIC · NO DEJES ESCAPAR A LOS PATOS
    </div>
</main>

<script>
"use strict";

const canvas = document.getElementById("gameCanvas");
const context = canvas.getContext("2d");

const scoreValue = document.getElementById("scoreValue");
const roundValue = document.getElementById("roundValue");
const ammoValue = document.getElementById("ammoValue");
const bestValue = document.getElementById("bestValue");
const startOverlay = document.getElementById("startOverlay");
const gameOverOverlay = document.getElementById("gameOverOverlay");
const startButton = document.getElementById("startButton");
const restartButton = document.getElementById("restartButton");
const finalScore = document.getElementById("finalScore");
const recordMessage = document.getElementById("recordMessage");

const WIDTH = 800;
const HEIGHT = 520;
const MAX_AMMO = 3;

let score = 0;
let bestScore = <?php echo json_encode($maxScore); ?>;
let round = 1;
let ammo = MAX_AMMO;
let gameState = "menu";

let duck = null;
let particles = [];
let floatingTexts = [];

let mouse = {
    x: WIDTH / 2,
    y: HEIGHT / 2
};

let dog = {
    mode: "idle",
    timer: 0,
    jump: 0
};

let lastTime = 0;
let pulse = 0;
let screenShake = 0;
let nextRoundTimer = 0;
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

    if (type === "shot") {
        oscillator.type = "square";
        oscillator.frequency.setValueAtTime(95, now);
        oscillator.frequency.exponentialRampToValueAtTime(
            35,
            now + 0.18
        );

        gain.gain.setValueAtTime(0.28, now);
        gain.gain.exponentialRampToValueAtTime(
            0.001,
            now + 0.18
        );

        oscillator.start(now);
        oscillator.stop(now + 0.18);
    }

    if (type === "hit") {
        oscillator.type = "sine";
        oscillator.frequency.setValueAtTime(380, now);
        oscillator.frequency.exponentialRampToValueAtTime(
            1050,
            now + 0.22
        );

        gain.gain.setValueAtTime(0.18, now);
        gain.gain.exponentialRampToValueAtTime(
            0.001,
            now + 0.22
        );

        oscillator.start(now);
        oscillator.stop(now + 0.22);
    }

    if (type === "miss") {
        oscillator.type = "triangle";
        oscillator.frequency.setValueAtTime(150, now);
        oscillator.frequency.exponentialRampToValueAtTime(
            70,
            now + 0.18
        );

        gain.gain.setValueAtTime(0.1, now);
        gain.gain.exponentialRampToValueAtTime(
            0.001,
            now + 0.18
        );

        oscillator.start(now);
        oscillator.stop(now + 0.18);
    }
}

function resetGame() {
    score = 0;
    round = 1;
    ammo = MAX_AMMO;
    gameState = "running";

    duck = null;
    particles = [];
    floatingTexts = [];

    dog.mode = "idle";
    dog.timer = 0;
    dog.jump = 0;

    screenShake = 0;
    nextRoundTimer = 0;
    pulse = 0;
    lastTime = performance.now();

    scoreValue.textContent = "0";
    roundValue.textContent = "1";
    ammoValue.textContent = String(MAX_AMMO);
    bestValue.textContent = formatNumber(bestScore);

    startOverlay.classList.add("hidden");
    gameOverOverlay.classList.add("hidden");

    spawnDuck();
}

function spawnDuck() {
    const speed = 125 + round * 17;
    const direction = Math.random() > 0.5 ? 1 : -1;

    duck = {
        x: direction === 1
            ? -70
            : WIDTH + 70,
        y: randomFloat(90, 280),
        radius: 28,
        velocityX: speed * direction,
        velocityY: randomFloat(-32, 32),
        wingPhase: 0,
        rotation: 0,
        state: "flying",
        fallingVelocity: 0,
        hitTimer: 0
    };
}

function startNextRound() {
    round += 1;
    ammo = MAX_AMMO;

    roundValue.textContent = String(round);
    ammoValue.textContent = String(ammo);

    dog.mode = "idle";
    dog.timer = 0;
    dog.jump = 0;

    spawnDuck();
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

function updateParticles(deltaTime) {
    for (
        let index = particles.length - 1;
        index >= 0;
        index -= 1
    ) {
        const particle = particles[index];

        particle.x += particle.velocityX * deltaTime;
        particle.y += particle.velocityY * deltaTime;
        particle.velocityY += 120 * deltaTime;
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
        const text = floatingTexts[index];

        text.y += text.velocityY * deltaTime;
        text.life -= deltaTime;

        if (text.life <= 0) {
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

function shoot() {
    if (gameState !== "running") {
        return;
    }

    if (ammo <= 0) {
        return;
    }

    ammo -= 1;
    ammoValue.textContent = String(ammo);

    playSound("shot");

    const muzzleX = 92;
    const muzzleY = 402;

    createParticles(muzzleX, muzzleY, "#ffe600", 8);

    if (
        duck &&
        duck.state === "flying"
    ) {
        const distanceX = mouse.x - duck.x;
        const distanceY = mouse.y - duck.y;
        const distance = Math.sqrt(
            distanceX * distanceX +
            distanceY * distanceY
        );

        if (distance <= duck.radius + 18) {
            hitDuck();
            return;
        }
    }

    addFloatingText(
        mouse.x,
        mouse.y,
        "FALLO",
        "#ff477e"
    );

    playSound("miss");

    if (ammo <= 0) {
        missDuck();
    }
}

function hitDuck() {
    if (!duck || duck.state !== "flying") {
        return;
    }

    duck.state = "falling";
    duck.fallingVelocity = 60;
    duck.hitTimer = 0;

    const points = 100 + (round - 1) * 25;

    score += points;
    scoreValue.textContent = formatNumber(score);

    addFloatingText(
        duck.x,
        duck.y - 35,
        "+" + points,
        "#ffe600"
    );

    createParticles(
        duck.x,
        duck.y,
        "#ffe600",
        34
    );

    playSound("hit");

    screenShake = 7;
    dog.mode = "catch";
    dog.timer = 1.3;
    dog.jump = 1;
}

function missDuck() {
    if (!duck || duck.state !== "flying") {
        return;
    }

    duck.state = "escaped";
    duck.hitTimer = 0;

    dog.mode = "sad";
    dog.timer = 1.2;
    dog.jump = 0;

    addFloatingText(
        WIDTH / 2,
        80,
        "¡SE ESCAPÓ!",
        "#ff477e"
    );

    nextRoundTimer = 1.1;
}

function updateDuck(deltaTime) {
    if (!duck) {
        return;
    }

    if (duck.state === "flying") {
        duck.wingPhase += deltaTime * 16;

        duck.x += duck.velocityX * deltaTime;
        duck.y += duck.velocityY * deltaTime;

        duck.velocityY += Math.sin(
            duck.wingPhase * 0.4
        ) * 20 * deltaTime;

        duck.y = clamp(duck.y, 60, 335);

        duck.rotation = Math.atan2(
            duck.velocityY,
            duck.velocityX
        ) * 0.12;

        const escapedRight =
            duck.velocityX > 0 &&
            duck.x > WIDTH + 90;

        const escapedLeft =
            duck.velocityX < 0 &&
            duck.x < -90;

        if (escapedRight || escapedLeft) {
            missDuck();
        }
    }

    if (duck.state === "falling") {
        duck.wingPhase += deltaTime * 8;
        duck.fallingVelocity += 470 * deltaTime;
        duck.y += duck.fallingVelocity * deltaTime;
        duck.rotation += deltaTime * 5;
        duck.hitTimer += deltaTime;

        if (duck.y > 430) {
            duck.state = "caught";
            dog.mode = "catch";
            dog.timer = 1.2;
            dog.jump = 1;
            nextRoundTimer = 1.4;
        }
    }

    if (
        duck.state === "escaped" ||
        duck.state === "caught"
    ) {
        duck.hitTimer += deltaTime;

        if (duck.hitTimer > 0.2) {
            nextRoundTimer -= deltaTime;
        }

        if (nextRoundTimer <= 0) {
            startNextRound();
        }
    }
}

function updateDog(deltaTime) {
    if (dog.timer > 0) {
        dog.timer -= deltaTime;
    } else {
        dog.mode = "idle";
    }

    if (dog.jump > 0) {
        dog.jump -= deltaTime * 1.8;

        if (dog.jump < 0) {
            dog.jump = 0;
        }
    }
}

function update(deltaTime) {
    pulse += deltaTime;

    if (screenShake > 0) {
        screenShake = Math.max(
            0,
            screenShake - deltaTime * 26
        );
    }

    updateParticles(deltaTime);

    if (gameState !== "running") {
        return;
    }

    updateDuck(deltaTime);
    updateDog(deltaTime);

    if (duck && duck.state === "flying") {
        if (ammo <= 0) {
            missDuck();
        }
    }
}

function drawBackground() {
    const gradient = context.createLinearGradient(
        0,
        0,
        0,
        HEIGHT
    );

    gradient.addColorStop(0, "#090b25");
    gradient.addColorStop(0.55, "#101733");
    gradient.addColorStop(1, "#050611");

    context.fillStyle = gradient;
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

    const cityGradient = context.createLinearGradient(
        0,
        330,
        0,
        HEIGHT
    );

    cityGradient.addColorStop(
        0,
        "rgba(0, 246, 255, 0)"
    );

    cityGradient.addColorStop(
        1,
        "rgba(255, 20, 147, 0.18)"
    );

    context.fillStyle = cityGradient;
    context.fillRect(0, 330, WIDTH, 190);

    for (let buildingX = 0; buildingX < WIDTH; buildingX += 55) {
        const buildingHeight = randomInteger(35, 100);

        context.fillStyle = "rgba(3, 5, 18, 0.9)";
        context.fillRect(
            buildingX,
            420 - buildingHeight,
            43,
            buildingHeight
        );

        context.fillStyle = "rgba(0, 246, 255, 0.45)";

        for (
            let windowY = 435 - buildingHeight;
            windowY < 415;
            windowY += 14
        ) {
            context.fillRect(
                buildingX + 8,
                windowY,
                5,
                4
            );

            context.fillRect(
                buildingX + 24,
                windowY + 5,
                5,
                4
            );
        }
    }

    context.fillStyle = "#07101c";
    context.fillRect(0, 420, WIDTH, 100);

    context.strokeStyle = "#00f6ff";
    context.shadowColor = "#00f6ff";
    context.shadowBlur = 12;
    context.lineWidth = 3;

    context.beginPath();
    context.moveTo(0, 420);
    context.lineTo(WIDTH, 420);
    context.stroke();

    context.shadowBlur = 0;
}

function drawDuck() {
    if (!duck) {
        return;
    }

    if (
        duck.state === "escaped" ||
        duck.state === "caught"
    ) {
        return;
    }

    context.save();

    context.translate(duck.x, duck.y);
    context.rotate(duck.rotation);

    const wingFlap = Math.sin(duck.wingPhase) * 0.55;

    context.fillStyle = "#a8ff00";
    context.shadowColor = "#a8ff00";
    context.shadowBlur = 18;

    context.beginPath();
    context.ellipse(
        0,
        0,
        30,
        21,
        0,
        0,
        Math.PI * 2
    );
    context.fill();

    context.fillStyle = "#00f6ff";
    context.shadowColor = "#00f6ff";

    context.beginPath();
    context.ellipse(
        -5,
        -14 - wingFlap * 12,
        25,
        10,
        wingFlap,
        0,
        Math.PI * 2
    );
    context.fill();

    context.fillStyle = "#ffe600";
    context.shadowColor = "#ffe600";

    context.beginPath();
    context.arc(
        28,
        -12,
        17,
        0,
        Math.PI * 2
    );
    context.fill();

    context.fillStyle = "#ff7b00";
    context.shadowColor = "#ff7b00";

    context.beginPath();
    context.moveTo(42, -10);
    context.lineTo(59, -5);
    context.lineTo(42, 0);
    context.closePath();
    context.fill();

    context.fillStyle = "#ff1493";
    context.shadowColor = "#ff1493";

    context.beginPath();
    context.arc(
        32,
        -17,
        4,
        0,
        Math.PI * 2
    );
    context.fill();

    context.fillStyle = "#07101c";
    context.shadowBlur = 0;

    context.beginPath();
    context.arc(
        33,
        -18,
        2,
        0,
        Math.PI * 2
    );
    context.fill();

    context.restore();
}

function drawDog() {
    const dogX = 90;
    const baseY = 463;
    const jumpOffset = dog.jump > 0
        ? Math.sin(dog.jump * Math.PI) * 70
        : 0;

    const dogY = baseY - jumpOffset;

    context.save();

    context.translate(dogX, dogY);

    const dogColor = dog.mode === "sad"
        ? "#ff477e"
        : "#ff1493";

    context.fillStyle = dogColor;
    context.shadowColor = dogColor;
    context.shadowBlur = 17;

    context.beginPath();
    context.ellipse(
        0,
        0,
        48,
        28,
        0,
        0,
        Math.PI * 2
    );
    context.fill();

    context.fillStyle = "#ffe600";
    context.shadowColor = "#ffe600";

    context.beginPath();
    context.arc(
        42,
        -18,
        23,
        0,
        Math.PI * 2
    );
    context.fill();

    context.fillStyle = "#00f6ff";
    context.shadowColor = "#00f6ff";

    context.beginPath();
    context.ellipse(
        29,
        -44,
        11,
        22,
        -0.4,
        0,
        Math.PI * 2
    );
    context.fill();

    context.beginPath();
    context.ellipse(
        53,
        -43,
        11,
        22,
        0.4,
        0,
        Math.PI * 2
    );
    context.fill();

    context.fillStyle = "#ff7b00";
    context.shadowColor = "#ff7b00";

    context.beginPath();
    context.arc(
        63,
        -14,
        8,
        0,
        Math.PI * 2
    );
    context.fill();

    context.fillStyle = "#07101c";
    context.shadowBlur = 0;

    context.beginPath();
    context.arc(
        48,
        -22,
        3,
        0,
        Math.PI * 2
    );
    context.fill();

    context.strokeStyle = "#00f6ff";
    context.lineWidth = 5;
    context.lineCap = "round";

    context.beginPath();
    context.moveTo(-22, 21);
    context.lineTo(-27, 45);
    context.stroke();

    context.beginPath();
    context.moveTo(20, 21);
    context.lineTo(25, 45);
    context.stroke();

    if (dog.mode === "catch") {
        context.fillStyle = "#ffe600";
        context.font = "900 16px Arial";
        context.textAlign = "center";
        context.fillText(
            "¡BUEN TIRO!",
            5,
            -70
        );
    }

    if (dog.mode === "sad") {
        context.fillStyle = "#ff477e";
        context.font = "900 16px Arial";
        context.textAlign = "center";
        context.fillText(
            "¡SE FUE!",
            5,
            -70
        );
    }

    context.restore();
}

function drawCrosshair() {
    context.save();

    context.translate(mouse.x, mouse.y);

    context.strokeStyle = "#ffffff";
    context.shadowColor = "#ff1493";
    context.shadowBlur = 14;
    context.lineWidth = 2;

    context.beginPath();
    context.arc(
        0,
        0,
        17 + Math.sin(pulse * 8) * 2,
        0,
        Math.PI * 2
    );
    context.stroke();

    context.beginPath();
    context.moveTo(-28, 0);
    context.lineTo(-8, 0);
    context.moveTo(8, 0);
    context.lineTo(28, 0);
    context.moveTo(0, -28);
    context.lineTo(0, -8);
    context.moveTo(0, 8);
    context.lineTo(0, 28);
    context.stroke();

    context.fillStyle = "#ff1493";
    context.beginPath();
    context.arc(0, 0, 3, 0, Math.PI * 2);
    context.fill();

    context.restore();
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

    for (const text of floatingTexts) {
        context.save();

        context.globalAlpha = text.life;
        context.fillStyle = text.color;
        context.shadowColor = text.color;
        context.shadowBlur = 12;
        context.font = "900 22px Arial";
        context.textAlign = "center";

        context.fillText(
            text.text,
            text.x,
            text.y
        );

        context.restore();
    }
}

function drawHud() {
    context.save();

    context.fillStyle = "rgba(4, 6, 17, 0.78)";
    context.fillRect(22, 18, 210, 62);

    context.strokeStyle = "rgba(0, 246, 255, 0.6)";
    context.strokeRect(22, 18, 210, 62);

    context.fillStyle = "#00f6ff";
    context.font = "13px monospace";
    context.textAlign = "left";

    context.fillText(
        "OBJETIVO: DERRIBAR",
        36,
        43
    );

    context.fillStyle = "#ffe600";

    context.fillText(
        "RONDA " + round,
        36,
        65
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
    drawDuck();
    drawDog();
    drawParticles();
    drawHud();
    drawCrosshair();

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
                "¡NUEVO RÉCORD EN EL SERVIDOR!";

            recordMessage.style.color = "#ffe600";
        } else {
            recordMessage.textContent =
                "Récord actual: " + formatNumber(bestScore);

            recordMessage.style.color = "#00f6ff";
        }
    } catch (error) {
        recordMessage.textContent =
            "No se pudo guardar el récord.";

        recordMessage.style.color = "#ff477e";
    }
}

function handlePointerMove(event) {
    const pointer = getPointerPosition(event);

    mouse.x = pointer.x;
    mouse.y = pointer.y;
}

function handlePointerDown(event) {
    event.preventDefault();

    if (gameState !== "running") {
        return;
    }

    const pointer = getPointerPosition(event);

    mouse.x = pointer.x;
    mouse.y = pointer.y;

    shoot();
}

function handleKeyDown(event) {
    if (event.code === "Space") {
        event.preventDefault();

        if (gameState === "running") {
            shoot();
        }

        if (gameState === "gameover") {
            resetGame();
        }
    }
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