<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $postedScore = filter_input(
        INPUT_POST,
        'score',
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
        !isset($_SESSION['snake_max_score']) ||
        $postedScore > (int) $_SESSION['snake_max_score']
    ) {
        $_SESSION['snake_max_score'] = $postedScore;
    }

    echo json_encode([
        'success' => true,
        'max_score' => (int) $_SESSION['snake_max_score']
    ]);

    exit;
}

$maxScore = isset($_SESSION['snake_max_score'])
    ? (int) $_SESSION['snake_max_score']
    : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neon Snake</title>

    <style>
        :root {
            --cyan: #00f6ff;
            --pink: #ff1493;
            --green: #a8ff00;
            --yellow: #ffe600;
            --dark: #050611;
            --panel: rgba(7, 10, 28, 0.94);
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
                    rgba(0, 246, 255, 0.15),
                    transparent 35%
                ),
                radial-gradient(
                    circle at 20% 100%,
                    rgba(255, 20, 147, 0.15),
                    transparent 35%
                ),
                var(--dark);
            color: #ffffff;
            font-family: "Segoe UI", Arial, sans-serif;
            overflow-x: hidden;
        }

        .game-shell {
            width: min(100%, 570px);
            padding: 18px;
            border: 1px solid rgba(0, 246, 255, 0.7);
            background:
                linear-gradient(
                    145deg,
                    rgba(0, 246, 255, 0.08),
                    transparent 30%
                ),
                linear-gradient(
                    325deg,
                    rgba(255, 20, 147, 0.08),
                    transparent 35%
                ),
                rgba(4, 6, 17, 0.93);
            box-shadow:
                0 0 25px rgba(0, 246, 255, 0.25),
                0 0 70px rgba(255, 20, 147, 0.14),
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
            gap: 12px;
            margin-bottom: 14px;
        }

        .title {
            color: var(--cyan);
            font-size: 1.35rem;
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
            gap: 8px;
        }

        .stat {
            min-width: 96px;
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
            width: 512px;
            max-width: 100%;
            margin: 0 auto;
            border: 2px solid var(--cyan);
            box-shadow:
                0 0 12px var(--cyan),
                0 0 35px rgba(0, 246, 255, 0.5),
                inset 0 0 30px rgba(0, 246, 255, 0.12);
            overflow: hidden;
            background: #070817;
        }

        canvas {
            display: block;
            width: 512px;
            height: 512px;
            max-width: 100%;
            aspect-ratio: 1 / 1;
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
            background: rgba(2, 3, 12, 0.72);
            backdrop-filter: blur(5px);
        }

        .overlay.hidden {
            display: none;
        }

        .panel {
            width: 100%;
            padding: 28px 20px;
            border: 1px solid var(--pink);
            background: var(--panel);
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
            font-size: clamp(2rem, 9vw, 3rem);
            letter-spacing: 0.12em;
            text-shadow:
                0 0 5px var(--cyan),
                0 0 22px var(--cyan);
        }

        .panel h1 span {
            color: var(--pink);
        }

        .panel p {
            color: rgba(255, 255, 255, 0.78);
            line-height: 1.45;
        }

        .game-over-text {
            color: #ff477e;
            font-size: 1.7rem;
            font-weight: 900;
            text-shadow: 0 0 12px #ff477e;
        }

        .controls {
            margin: 18px 0;
            color: var(--green);
            font-family: monospace;
            font-size: 0.85rem;
        }

        .main-button {
            padding: 13px 24px;
            border: 1px solid var(--cyan);
            color: #001114;
            background: var(--cyan);
            box-shadow:
                0 0 8px var(--cyan),
                0 0 24px rgba(0, 246, 255, 0.72);
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
            letter-spacing: 0.05em;
            text-align: center;
        }

        @media (max-width: 570px) {
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
            NEON<span>SNAKE</span>
        </div>

        <div class="stats">
            <div class="stat">
                <span class="stat-label">Puntos</span>
                <span class="stat-value" id="scoreValue">0</span>
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
        <canvas id="gameCanvas" width="512" height="512"></canvas>

        <div class="overlay" id="startOverlay">
            <div class="panel">
                <h1>NEON<span>SNAKE</span></h1>
                <p>
                    Come los núcleos de energía y haz crecer tu serpiente
                    digital sin chocar contra los muros ni contra ti mismo.
                </p>

                <div class="controls">
                    FLECHAS o W A S D para moverte<br>
                    ESPACIO para pausar
                </div>

                <button class="main-button" id="startButton">
                    Iniciar partida
                </button>
            </div>
        </div>

        <div class="overlay hidden" id="gameOverOverlay">
            <div class="panel">
                <div class="game-over-text">GAME OVER</div>

                <p id="finalScore">
                    Puntuación: 0
                </p>

                <p id="recordMessage">
                    Guardando puntuación...
                </p>

                <button class="main-button" id="restartButton">
                    Reintentar
                </button>
            </div>
        </div>
    </div>

    <div class="footer">
        MANTÉN EL CONTROL · CRECE · SOBREVIVE · SUPERA EL RÉCORD
    </div>
</main>

<script>
"use strict";

const canvas = document.getElementById("gameCanvas");
const context = canvas.getContext("2d");

const scoreValue = document.getElementById("scoreValue");
const bestValue = document.getElementById("bestValue");
const startOverlay = document.getElementById("startOverlay");
const gameOverOverlay = document.getElementById("gameOverOverlay");
const startButton = document.getElementById("startButton");
const restartButton = document.getElementById("restartButton");
const finalScore = document.getElementById("finalScore");
const recordMessage = document.getElementById("recordMessage");

const CANVAS_SIZE = 512;
const GRID_SIZE = 16;
const CELL_SIZE = CANVAS_SIZE / GRID_SIZE;

let snake = [];
let food = null;
let particles = [];

let direction = {
    x: 1,
    y: 0
};

let nextDirection = {
    x: 1,
    y: 0
};

let score = 0;
let bestScore = <?php echo json_encode($maxScore); ?>;
let gameState = "menu";
let gameSpeed = 125;
let lastUpdateTime = 0;
let animationFrame = 0;
let screenShake = 0;
let pulse = 0;
let audioContext = null;

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

    if (type === "eat") {
        oscillator.type = "sine";
        oscillator.frequency.setValueAtTime(420, now);
        oscillator.frequency.exponentialRampToValueAtTime(880, now + 0.12);

        gain.gain.setValueAtTime(0.12, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.12);

        oscillator.start(now);
        oscillator.stop(now + 0.12);
    }

    if (type === "crash") {
        oscillator.type = "sawtooth";
        oscillator.frequency.setValueAtTime(180, now);
        oscillator.frequency.exponentialRampToValueAtTime(28, now + 0.55);

        gain.gain.setValueAtTime(0.35, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.55);

        oscillator.start(now);
        oscillator.stop(now + 0.55);
    }
}

function randomInteger(minimum, maximum) {
    return Math.floor(
        Math.random() * (maximum - minimum + 1)
    ) + minimum;
}

function formatNumber(value) {
    return Math.floor(value).toLocaleString("es-ES");
}

function resetGame() {
    snake = [
        {
            x: 7,
            y: 8
        },
        {
            x: 6,
            y: 8
        },
        {
            x: 5,
            y: 8
        }
    ];

    direction = {
        x: 1,
        y: 0
    };

    nextDirection = {
        x: 1,
        y: 0
    };

    score = 0;
    gameSpeed = 125;
    particles = [];
    screenShake = 0;
    pulse = 0;
    lastUpdateTime = performance.now();
    gameState = "running";

    scoreValue.textContent = "0";
    bestValue.textContent = formatNumber(bestScore);

    createFood();

    startOverlay.classList.add("hidden");
    gameOverOverlay.classList.add("hidden");
}

function createFood() {
    let validPosition = false;
    let foodX = 0;
    let foodY = 0;

    while (!validPosition) {
        foodX = randomInteger(0, GRID_SIZE - 1);
        foodY = randomInteger(0, GRID_SIZE - 1);

        validPosition = !snake.some(function(segment) {
            return segment.x === foodX && segment.y === foodY;
        });
    }

    food = {
        x: foodX,
        y: foodY,
        rotation: 0
    };
}

function setDirection(x, y) {
    if (gameState !== "running") {
        return;
    }

    const isOppositeDirection =
        x === -direction.x &&
        y === -direction.y;

    if (isOppositeDirection) {
        return;
    }

    nextDirection = {
        x: x,
        y: y
    };
}

function handleKeyDown(event) {
    if (
        event.code === "ArrowUp" ||
        event.code === "ArrowDown" ||
        event.code === "ArrowLeft" ||
        event.code === "ArrowRight" ||
        event.code === "KeyW" ||
        event.code === "KeyA" ||
        event.code === "KeyS" ||
        event.code === "KeyD" ||
        event.code === "Space"
    ) {
        event.preventDefault();
    }

    if (event.code === "Space") {
        if (gameState === "running") {
            gameState = "paused";
        } else if (gameState === "paused") {
            gameState = "running";
            lastUpdateTime = performance.now();
        } else if (
            gameState === "menu" ||
            gameState === "gameover"
        ) {
            resetGame();
        }

        return;
    }

    if (
        event.code === "ArrowUp" ||
        event.code === "KeyW"
    ) {
        setDirection(0, -1);
    }

    if (
        event.code === "ArrowDown" ||
        event.code === "KeyS"
    ) {
        setDirection(0, 1);
    }

    if (
        event.code === "ArrowLeft" ||
        event.code === "KeyA"
    ) {
        setDirection(-1, 0);
    }

    if (
        event.code === "ArrowRight" ||
        event.code === "KeyD"
    ) {
        setDirection(1, 0);
    }
}

function createParticles(gridX, gridY) {
    const centerX = gridX * CELL_SIZE + CELL_SIZE / 2;
    const centerY = gridY * CELL_SIZE + CELL_SIZE / 2;

    for (let index = 0; index < 26; index += 1) {
        const angle = Math.random() * Math.PI * 2;
        const speed = 45 + Math.random() * 150;

        particles.push({
            x: centerX,
            y: centerY,
            velocityX: Math.cos(angle) * speed,
            velocityY: Math.sin(angle) * speed,
            size: 2 + Math.random() * 4,
            life: 0.55 + Math.random() * 0.65,
            maxLife: 1.2,
            color: Math.random() > 0.5
                ? "#a8ff00"
                : "#00f6ff"
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
        particle.velocityY += 80 * deltaTime;
        particle.life -= deltaTime;

        if (particle.life <= 0) {
            particles.splice(index, 1);
        }
    }
}

function isSnakePosition(position, includeTail) {
    const limit = includeTail
        ? snake.length
        : snake.length - 1;

    for (let index = 0; index < limit; index += 1) {
        if (
            snake[index].x === position.x &&
            snake[index].y === position.y
        ) {
            return true;
        }
    }

    return false;
}

function moveSnake() {
    direction = {
        x: nextDirection.x,
        y: nextDirection.y
    };

    const head = snake[0];

    const newHead = {
        x: head.x + direction.x,
        y: head.y + direction.y
    };

    const hitWall =
        newHead.x < 0 ||
        newHead.x >= GRID_SIZE ||
        newHead.y < 0 ||
        newHead.y >= GRID_SIZE;

    const hitBody = isSnakePosition(newHead, false);

    if (hitWall || hitBody) {
        endGame();
        return;
    }

    snake.unshift(newHead);

    const ateFood =
        food &&
        newHead.x === food.x &&
        newHead.y === food.y;

    if (ateFood) {
        score += 10;
        gameSpeed = Math.max(58, gameSpeed - 3);
        scoreValue.textContent = formatNumber(score);

        createParticles(food.x, food.y);
        playSound("eat");
        createFood();
    } else {
        snake.pop();
    }
}

function endGame() {
    if (gameState === "gameover") {
        return;
    }

    gameState = "gameover";
    screenShake = 12;

    const head = snake[0];

    createParticles(head.x, head.y);
    playSound("crash");

    finalScore.textContent =
        "Puntuación: " + formatNumber(score);

    recordMessage.textContent =
        "Guardando puntuación en el servidor...";

    gameOverOverlay.classList.remove("hidden");

    saveScore(score);
}

async function saveScore(finalScoreValue) {
    try {
        const formData = new FormData();

        formData.append(
            "score",
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
            "No se pudo guardar la puntuación.";

        recordMessage.style.color = "#ff477e";
    }
}

function drawBackground() {
    const backgroundGradient = context.createLinearGradient(
        0,
        0,
        CANVAS_SIZE,
        CANVAS_SIZE
    );

    backgroundGradient.addColorStop(0, "#080b20");
    backgroundGradient.addColorStop(0.5, "#111733");
    backgroundGradient.addColorStop(1, "#050611");

    context.fillStyle = backgroundGradient;
    context.fillRect(0, 0, CANVAS_SIZE, CANVAS_SIZE);

    context.strokeStyle = "rgba(0, 246, 255, 0.1)";
    context.lineWidth = 1;

    for (let line = 0; line <= GRID_SIZE; line += 1) {
        const coordinate = line * CELL_SIZE;

        context.beginPath();
        context.moveTo(coordinate, 0);
        context.lineTo(coordinate, CANVAS_SIZE);
        context.stroke();

        context.beginPath();
        context.moveTo(0, coordinate);
        context.lineTo(CANVAS_SIZE, coordinate);
        context.stroke();
    }

    context.fillStyle = "rgba(255, 20, 147, 0.04)";
    context.fillRect(0, 0, CANVAS_SIZE, CANVAS_SIZE);
}

function drawFood() {
    if (!food) {
        return;
    }

    const centerX = food.x * CELL_SIZE + CELL_SIZE / 2;
    const centerY = food.y * CELL_SIZE + CELL_SIZE / 2;
    const foodSize = 9 + Math.sin(pulse * 7) * 2;

    context.save();

    context.translate(centerX, centerY);
    context.rotate(food.rotation);

    context.fillStyle = "#a8ff00";
    context.shadowColor = "#a8ff00";
    context.shadowBlur = 22;

    context.beginPath();
    context.moveTo(0, -foodSize);
    context.lineTo(foodSize, 0);
    context.lineTo(0, foodSize);
    context.lineTo(-foodSize, 0);
    context.closePath();
    context.fill();

    context.fillStyle = "#ffffff";
    context.shadowBlur = 0;

    context.beginPath();
    context.arc(0, 0, 3, 0, Math.PI * 2);
    context.fill();

    context.restore();
}

function drawSnake() {
    for (
        let index = snake.length - 1;
        index >= 0;
        index -= 1
    ) {
        const segment = snake[index];
        const isHead = index === 0;
        const padding = isHead ? 2 : 3;
        const x = segment.x * CELL_SIZE + padding;
        const y = segment.y * CELL_SIZE + padding;
        const size = CELL_SIZE - padding * 2;

        const segmentGradient = context.createLinearGradient(
            x,
            y,
            x + size,
            y + size
        );

        if (isHead) {
            segmentGradient.addColorStop(0, "#eaff85");
            segmentGradient.addColorStop(0.5, "#a8ff00");
            segmentGradient.addColorStop(1, "#35c900");
        } else {
            segmentGradient.addColorStop(0, "#00f6ff");
            segmentGradient.addColorStop(1, "#0089ff");
        }

        context.save();

        context.fillStyle = segmentGradient;
        context.shadowColor = isHead
            ? "#a8ff00"
            : "#00f6ff";
        context.shadowBlur = isHead ? 18 : 10;

        context.beginPath();
        context.roundRect(
            x,
            y,
            size,
            size,
            isHead ? 8 : 5
        );
        context.fill();

        context.shadowBlur = 0;

        if (isHead) {
            context.fillStyle = "#07100a";

            const eyeSize = 3;
            const eyeOffset = 7;

            if (direction.x !== 0) {
                const eyeX = direction.x > 0
                    ? x + size - eyeOffset
                    : x + eyeOffset - eyeSize;

                context.fillRect(
                    eyeX,
                    y + 6,
                    eyeSize,
                    eyeSize
                );

                context.fillRect(
                    eyeX,
                    y + size - 9,
                    eyeSize,
                    eyeSize
                );
            } else {
                const eyeY = direction.y > 0
                    ? y + size - eyeOffset
                    : y + eyeOffset - eyeSize;

                context.fillRect(
                    x + 6,
                    eyeY,
                    eyeSize,
                    eyeSize
                );

                context.fillRect(
                    x + size - 9,
                    eyeY,
                    eyeSize,
                    eyeSize
                );
            }
        }

        context.restore();
    }
}

function drawParticles() {
    for (const particle of particles) {
        const opacity = Math.max(
            0,
            particle.life / particle.maxLife
        );

        context.save();

        context.globalAlpha = opacity;
        context.fillStyle = particle.color;
        context.shadowColor = particle.color;
        context.shadowBlur = 12;

        context.beginPath();
        context.arc(
            particle.x,
            particle.y,
            particle.size * opacity,
            0,
            Math.PI * 2
        );
        context.fill();

        context.restore();
    }
}

function drawPauseMessage() {
    if (gameState !== "paused") {
        return;
    }

    context.fillStyle = "rgba(2, 3, 12, 0.68)";
    context.fillRect(0, 0, CANVAS_SIZE, CANVAS_SIZE);

    context.textAlign = "center";
    context.fillStyle = "#00f6ff";
    context.shadowColor = "#00f6ff";
    context.shadowBlur = 18;
    context.font = "900 42px Arial";
    context.fillText(
        "PAUSA",
        CANVAS_SIZE / 2,
        CANVAS_SIZE / 2
    );

    context.shadowBlur = 0;
    context.fillStyle = "#ffffff";
    context.font = "16px Arial";
    context.fillText(
        "Pulsa ESPACIO para continuar",
        CANVAS_SIZE / 2,
        CANVAS_SIZE / 2 + 38
    );
}

function draw() {
    context.save();

    if (screenShake > 0) {
        context.translate(
            (Math.random() - 0.5) * screenShake,
            (Math.random() - 0.5) * screenShake
        );
    }

    context.clearRect(
        -20,
        -20,
        CANVAS_SIZE + 40,
        CANVAS_SIZE + 40
    );

    drawBackground();
    drawFood();
    drawSnake();
    drawParticles();
    drawPauseMessage();

    context.restore();
}

function update(deltaTime) {
    pulse += deltaTime;

    if (screenShake > 0) {
        screenShake = Math.max(
            0,
            screenShake - deltaTime * 30
        );
    }

    updateParticles(deltaTime);

    if (food) {
        food.rotation += deltaTime * 2;
    }

    if (gameState !== "running") {
        return;
    }

    if (
        performance.now() - lastUpdateTime >= gameSpeed
    ) {
        moveSnake();
        lastUpdateTime = performance.now();
    }
}

function loop(currentTime) {
    const deltaTime = Math.min(
        0.05,
        Math.max(0.001, (currentTime - loop.previousTime) / 1000)
    );

    loop.previousTime = currentTime;

    update(deltaTime);
    draw();

    animationFrame = requestAnimationFrame(loop);
}

loop.previousTime = performance.now();

startButton.addEventListener("click", function() {
    resetGame();
});

restartButton.addEventListener("click", function() {
    resetGame();
});

window.addEventListener("keydown", handleKeyDown);

canvas.addEventListener("pointerdown", function(event) {
    if (gameState === "gameover" || gameState === "menu") {
        resetGame();
        return;
    }

    const bounds = canvas.getBoundingClientRect();
    const pointerX = event.clientX - bounds.left;
    const pointerY = event.clientY - bounds.top;

    const relativeX = pointerX - bounds.width / 2;
    const relativeY = pointerY - bounds.height / 2;

    if (Math.abs(relativeX) > Math.abs(relativeY)) {
        if (relativeX < 0) {
            setDirection(-1, 0);
        } else {
            setDirection(1, 0);
        }
    } else {
        if (relativeY < 0) {
            setDirection(0, -1);
        } else {
            setDirection(0, 1);
        }
    }
});

requestAnimationFrame(loop);
</script>

</body>
</html>