<?php
declare(strict_types=1);

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json; charset=utf-8");

    $postedScore = filter_input(
        INPUT_POST,
        "max_score",
        FILTER_VALIDATE_INT
    );

    if (
        $postedScore === false ||
        $postedScore === null
    ) {
        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Puntuación inválida."
        ]);

        exit;
    }

    $postedScore = max(0, (int) $postedScore);

    if (
        !isset($_SESSION["neon_flappy_max_score"]) ||
        $postedScore > (int) $_SESSION["neon_flappy_max_score"]
    ) {
        $_SESSION["neon_flappy_max_score"] = $postedScore;
    }

    echo json_encode([
        "success" => true,
        "max_score" => (int) $_SESSION["neon_flappy_max_score"]
    ]);

    exit;
}

$maxScore = isset($_SESSION["neon_flappy_max_score"])
    ? (int) $_SESSION["neon_flappy_max_score"]
    : 0;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neon Flappy</title>

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

        html, body {
            min-height: 100%;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 16px;
            background:
                radial-gradient(
                    circle at 50% 0%,
                    rgba(0, 246, 255, 0.16),
                    transparent 36%
                ),
                radial-gradient(
                    circle at 50% 100%,
                    rgba(255, 20, 147, 0.16),
                    transparent 40%
                ),
                var(--dark);
            color: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
        }

        .game-shell {
            width: min(100%, 860px);
            padding: 18px;
            border: 1px solid rgba(0, 246, 255, 0.65);
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
                rgba(4, 6, 17, 0.95);
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
            gap: 16px;
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
            gap: 8px;
        }

        .stat {
            min-width: 100px;
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
            color: rgba(255, 255, 255, 0.56);
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
            cursor: pointer;
            touch-action: manipulation;
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
            background: rgba(2, 3, 12, 0.76);
            backdrop-filter: blur(5px);
        }

        .overlay.hidden {
            display: none;
        }

        .panel {
            width: min(100%, 500px);
            padding: 28px 22px;
            border: 1px solid var(--pink);
            background: rgba(6, 9, 26, 0.95);
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
            line-height: 1.5;
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
            font-size: 0.86rem;
            line-height: 1.65;
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
            transition:
                transform 0.15s ease,
                background 0.15s ease;
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
                NEON<span>FLAPPY</span>
            </div>

            <div class="stats">
                <div class="stat">
                    <span class="stat-label">Puntos</span>
                    <span class="stat-value" id="scoreValue">0</span>
                </div>

                <div class="stat">
                    <span class="stat-label">Récord PHP</span>
                    <span class="stat-value" id="bestValue">
                        <?php echo htmlspecialchars((string) $maxScore, ENT_QUOTES, "UTF-8"); ?>
                    </span>
                </div>
            </div>
        </header>

        <div class="canvas-wrapper">
            <canvas
                id="gameCanvas"
                width="800"
                height="520"
            ></canvas>

            <div class="overlay" id="startOverlay">
                <div class="panel">
                    <h1>NEON<span>FLAPPY</span></h1>

                    <p>
                        Controla el pájaro neon y atraviesa los portales
                        sin tocar los tubos.
                    </p>

                    <div class="controls">
                        CLIC, ESPACIO o TOQUE: saltar<br>
                        Evita los tubos y consigue la máxima puntuación.
                    </div>

                    <button
                        type="button"
                        class="main-button"
                        id="startButton"
                    >
                        Comenzar vuelo
                    </button>
                </div>
            </div>

            <div class="overlay hidden" id="gameOverOverlay">
                <div class="panel">
                    <div class="danger">IMPACTO DETECTADO</div>

                    <p id="finalScore">Puntuación final: 0</p>

                    <p id="recordMessage">Guardando récord...</p>

                    <button
                        type="button"
                        class="main-button"
                        id="restartButton"
                    >
                        Reintentar
                    </button>
                </div>
            </div>
        </div>

        <div class="footer">
            MANTÉN EL VUELO · ATRAVIESA LOS PORTALES · ROMPE EL RÉCORD
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

        const WIDTH = 800;
        const HEIGHT = 520;
        const GROUND_HEIGHT = 55;

        let gameState = "menu";
        let score = 0;
        let bestScore = <?php echo json_encode($maxScore); ?>;

        let lastTime = 0;
        let elapsedTime = 0;
        let spawnTimer = 0;
        let screenShake = 0;
        let pulse = 0;

        let pipes = [];
        let particles = [];
        let floatingTexts = [];

        let audioContext = null;

        const bird = {
            x: 190,
            y: 240,
            radius: 18,
            velocityY: 0,
            rotation: 0
        };

        function randomFloat(minimum, maximum) {
            return Math.random() * (maximum - minimum) + minimum;
        }

        function randomInteger(minimum, maximum) {
            return Math.floor(
                Math.random() * (maximum - minimum + 1)
            ) + minimum;
        }

        function formatNumber(value) {
            return Math.floor(value).toLocaleString("es-ES");
        }

        function getAudioContext() {
            if (!audioContext) {
                const AudioContextClass =
                    window.AudioContext ||
                    window.webkitAudioContext;

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

            if (type === "jump") {
                oscillator.type = "square";
                oscillator.frequency.setValueAtTime(300, now);
                oscillator.frequency.exponentialRampToValueAtTime(720, now + 0.1);
                gain.gain.setValueAtTime(0.08, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.1);
                oscillator.start(now);
                oscillator.stop(now + 0.1);
            }

            if (type === "score") {
                oscillator.type = "sine";
                oscillator.frequency.setValueAtTime(600, now);
                oscillator.frequency.exponentialRampToValueAtTime(1050, now + 0.16);
                gain.gain.setValueAtTime(0.09, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.16);
                oscillator.start(now);
                oscillator.stop(now + 0.16);
            }

            if (type === "crash") {
                oscillator.type = "sawtooth";
                oscillator.frequency.setValueAtTime(180, now);
                oscillator.frequency.exponentialRampToValueAtTime(24, now + 0.55);
                gain.gain.setValueAtTime(0.28, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.55);
                oscillator.start(now);
                oscillator.stop(now + 0.55);
            }
        }

        function resetGame() {
            gameState = "running";
            score = 0;
            elapsedTime = 0;
            spawnTimer = 0;
            screenShake = 0;
            pulse = 0;

            pipes = [];
            particles = [];
            floatingTexts = [];

            bird.x = 190;
            bird.y = 240;
            bird.velocityY = 0;
            bird.rotation = 0;

            scoreValue.textContent = "0";
            bestValue.textContent = formatNumber(bestScore);

            startOverlay.classList.add("hidden");
            gameOverOverlay.classList.add("hidden");

            createPipe();
            lastTime = performance.now();
        }

        function createPipe() {
            const pipeWidth = 76;
            const gapHeight = Math.max(132, 190 - elapsedTime * 1.1);
            const minimumGapTop = 80;
            const maximumGapTop = HEIGHT - GROUND_HEIGHT - gapHeight - 65;
            const gapTop = randomFloat(minimumGapTop, maximumGapTop);

            pipes.push({
                x: WIDTH + pipeWidth,
                width: pipeWidth,
                gapTop: gapTop,
                gapBottom: gapTop + gapHeight,
                speed: 215 + Math.min(elapsedTime * 2.5, 120),
                passed: false,
                glowPhase: randomFloat(0, Math.PI * 2)
            });
        }

        function flap() {
            if (gameState === "menu") {
                resetGame();
                return;
            }

            if (gameState === "gameover") {
                resetGame();
                return;
            }

            if (gameState !== "running") {
                return;
            }

            bird.velocityY = -440;
            bird.rotation = -0.42;

            createParticles(
                bird.x - 10,
                bird.y + 12,
                "#00f6ff",
                8
            );

            playSound("jump");
        }

        function createParticles(x, y, color, amount) {
            for (let index = 0; index < amount; index += 1) {
                const angle = randomFloat(0, Math.PI * 2);
                const speed = randomFloat(45, 190);

                particles.push({
                    x: x,
                    y: y,
                    velocityX: Math.cos(angle) * speed,
                    velocityY: Math.sin(angle) * speed,
                    size: randomFloat(2, 5),
                    life: randomFloat(0.35, 0.9),
                    maxLife: 0.9,
                    color: color
                });
            }
        }

        function createFloatingText(x, y, text, color) {
            floatingTexts.push({
                x: x,
                y: y,
                text: text,
                color: color,
                life: 1,
                velocityY: -40
            });
        }

        function updateParticles(deltaTime) {
            for (let index = particles.length - 1; index >= 0; index -= 1) {
                const particle = particles[index];

                particle.x += particle.velocityX * deltaTime;
                particle.y += particle.velocityY * deltaTime;
                particle.velocityY += 120 * deltaTime;
                particle.life -= deltaTime;

                if (particle.life <= 0) {
                    particles.splice(index, 1);
                }
            }

            for (let index = floatingTexts.length - 1; index >= 0; index -= 1) {
                const floatingText = floatingTexts[index];

                floatingText.y += floatingText.velocityY * deltaTime;
                floatingText.life -= deltaTime;

                if (floatingText.life <= 0) {
                    floatingTexts.splice(index, 1);
                }
            }
        }

        function checkCollision() {
            if (
                bird.y - bird.radius <= 0 ||
                bird.y + bird.radius >= HEIGHT - GROUND_HEIGHT
            ) {
                return true;
            }

            for (const pipe of pipes) {
                const birdLeft = bird.x - bird.radius;
                const birdRight = bird.x + bird.radius;
                const birdTop = bird.y - bird.radius;
                const birdBottom = bird.y + bird.radius;

                const overlapsPipeX =
                    birdRight > pipe.x &&
                    birdLeft < pipe.x + pipe.width;

                if (!overlapsPipeX) {
                    continue;
                }

                const touchesTopPipe = birdTop < pipe.gapTop;
                const touchesBottomPipe = birdBottom > pipe.gapBottom;

                if (touchesTopPipe || touchesBottomPipe) {
                    return true;
                }
            }

            return false;
        }

        function update(deltaTime) {
            pulse += deltaTime;

            if (screenShake > 0) {
                screenShake = Math.max(0, screenShake - deltaTime * 25);
            }

            updateParticles(deltaTime);

            if (gameState !== "running") {
                return;
            }

            elapsedTime += deltaTime;

            bird.velocityY += 1160 * deltaTime;
            bird.y += bird.velocityY * deltaTime;
            bird.rotation += (0.85 - bird.rotation) * Math.min(1, deltaTime * 5);

            spawnTimer -= deltaTime;

            const spawnInterval = Math.max(0.85, 1.65 - elapsedTime * 0.006);

            if (spawnTimer <= 0) {
                createPipe();
                spawnTimer = spawnInterval;
            }

            for (let index = pipes.length - 1; index >= 0; index -= 1) {
                const pipe = pipes[index];

                pipe.x -= pipe.speed * deltaTime;
                pipe.glowPhase += deltaTime * 4;

                if (!pipe.passed && pipe.x + pipe.width < bird.x) {
                    pipe.passed = true;
                    score += 1;

                    scoreValue.textContent = formatNumber(score);

                    createFloatingText(
                        bird.x,
                        bird.y - 30,
                        "+1",
                        "#a8ff00"
                    );

                    playSound("score");
                }

                if (pipe.x + pipe.width < -100) {
                    pipes.splice(index, 1);
                }
            }

            if (checkCollision()) {
                endGame();
            }
        }

        function drawBackground() {
            const gradient = context.createLinearGradient(0, 0, 0, HEIGHT);

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

            const moonGlow = context.createRadialGradient(640, 100, 5, 640, 100, 90);

            moonGlow.addColorStop(0, "rgba(255, 20, 147, 0.55)");
            moonGlow.addColorStop(1, "rgba(255, 20, 147, 0)");

            context.fillStyle = moonGlow;
            context.fillRect(540, 0, 200, 200);

            context.fillStyle = "rgba(255, 20, 147, 0.18)";
            context.beginPath();
            context.arc(640, 100, 35, 0, Math.PI * 2);
            context.fill();

            context.fillStyle = "rgba(0, 246, 255, 0.18)";
            context.fillRect(0, HEIGHT - GROUND_HEIGHT, WIDTH, GROUND_HEIGHT);
        }

        function drawPipes() {
            for (const pipe of pipes) {
                const glow = 12 + Math.sin(pipe.glowPhase) * 4;

                context.save();

                context.fillStyle = "#07101d";
                context.strokeStyle = "#00f6ff";
                context.shadowColor = "#00f6ff";
                context.shadowBlur = glow;
                context.lineWidth = 4;

                context.fillRect(pipe.x, 0, pipe.width, pipe.gapTop);
                context.strokeRect(pipe.x, 0, pipe.width, pipe.gapTop);

                context.fillRect(
                    pipe.x,
                    pipe.gapBottom,
                    pipe.width,
                    HEIGHT - GROUND_HEIGHT - pipe.gapBottom
                );

                context.strokeRect(
                    pipe.x,
                    pipe.gapBottom,
                    pipe.width,
                    HEIGHT - GROUND_HEIGHT - pipe.gapBottom
                );

                context.fillStyle = "#ff1493";
                context.shadowColor = "#ff1493";

                context.fillRect(pipe.x - 9, pipe.gapTop - 18, pipe.width + 18, 18);
                context.strokeRect(pipe.x - 9, pipe.gapTop - 18, pipe.width + 18, 18);

                context.fillRect(pipe.x - 9, pipe.gapBottom, pipe.width + 18, 18);
                context.strokeRect(pipe.x - 9, pipe.gapBottom, pipe.width + 18, 18);

                context.fillStyle = "rgba(0, 246, 255, 0.24)";
                context.fillRect(pipe.x + 12, 0, 8, pipe.gapTop);
                context.fillRect(
                    pipe.x + 12,
                    pipe.gapBottom,
                    8,
                    HEIGHT - GROUND_HEIGHT - pipe.gapBottom
                );

                context.restore();
            }
        }

        function drawBird() {
            context.save();

            context.translate(bird.x, bird.y);
            context.rotate(bird.rotation);

            const wingMovement = Math.sin(pulse * 18) * 5;

            context.fillStyle = "#00f6ff";
            context.shadowColor = "#00f6ff";
            context.shadowBlur = 18;

            context.beginPath();
            context.ellipse(0, 0, 28, 20, 0, 0, Math.PI * 2);
            context.fill();

            context.fillStyle = "#a8ff00";
            context.shadowColor = "#a8ff00";

            context.beginPath();
            context.ellipse(-5, 7 + wingMovement, 23, 10, -0.35, 0, Math.PI * 2);
            context.fill();

            context.fillStyle = "#ffe600";
            context.shadowColor = "#ffe600";

            context.beginPath();
            context.arc(22, -10, 15, 0, Math.PI * 2);
            context.fill();

            context.fillStyle = "#ff7b00";
            context.shadowColor = "#ff7b00";

            context.beginPath();
            context.moveTo(33, -10);
            context.lineTo(53, -3);
            context.lineTo(33, 4);
            context.closePath();
            context.fill();

            context.fillStyle = "#ff1493";
            context.shadowColor = "#ff1493";

            context.beginPath();
            context.arc(26, -15, 4, 0, Math.PI * 2);
            context.fill();

            context.fillStyle = "#07101d";
            context.shadowBlur = 0;

            context.beginPath();
            context.arc(27, -16, 2, 0, Math.PI * 2);
            context.fill();

            context.restore();
        }

        function drawGround() {
            context.fillStyle = "#07101d";
            context.fillRect(0, HEIGHT - GROUND_HEIGHT, WIDTH, GROUND_HEIGHT);

            context.strokeStyle = "#ff1493";
            context.shadowColor = "#ff1493";
            context.shadowBlur = 12;
            context.lineWidth = 4;

            context.beginPath();
            context.moveTo(0, HEIGHT - GROUND_HEIGHT);
            context.lineTo(WIDTH, HEIGHT - GROUND_HEIGHT);
            context.stroke();

            context.shadowBlur = 0;

            context.strokeStyle = "rgba(0, 246, 255, 0.18)";
            context.lineWidth = 2;

            for (let x = -40; x < WIDTH + 40; x += 40) {
                context.beginPath();
                context.moveTo(x, HEIGHT - GROUND_HEIGHT);
                context.lineTo(x + 28, HEIGHT);
                context.stroke();
            }
        }

        function drawParticles() {
            for (const particle of particles) {
                const opacity = Math.max(0, particle.life / particle.maxLife);

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

        function draw() {
            context.save();

            if (screenShake > 0) {
                context.translate(
                    randomFloat(-screenShake, screenShake),
                    randomFloat(-screenShake, screenShake)
                );
            }

            context.clearRect(-30, -30, WIDTH + 60, HEIGHT + 60);

            drawBackground();
            drawPipes();
            drawGround();
            drawBird();
            drawParticles();

            context.restore();
        }

        function endGame() {
            if (gameState === "gameover") {
                return;
            }

            gameState = "gameover";
            screenShake = 14;

            createParticles(bird.x, bird.y, "#ff1493", 42);
            playSound("crash");

            finalScore.textContent = "Puntuación final: " + formatNumber(score);
            recordMessage.textContent = "Guardando récord en el servidor...";
            gameOverOverlay.classList.remove("hidden");

            saveScore(score);
        }

        async function saveScore(finalScoreValue) {
            try {
                const formData = new FormData();
                formData.append("max_score", String(Math.max(0, Math.floor(finalScoreValue))));

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
                    recordMessage.textContent = "¡NUEVO RÉCORD EN EL SERVIDOR!";
                    recordMessage.style.color = "#ffe600";
                } else {
                    recordMessage.textContent = "Récord actual: " + formatNumber(bestScore);
                    recordMessage.style.color = "#00f6ff";
                }
            } catch (error) {
                recordMessage.textContent = "No se pudo guardar el récord.";
                recordMessage.style.color = "#ff477e";
            }
        }

        function handleInput(event) {
            if (event) {
                event.preventDefault();
            }

            flap();
        }

        function loop(currentTime) {
            if (!lastTime) {
                lastTime = currentTime;
            }

            const deltaTime = Math.min(
                0.034,
                Math.max(0.001, (currentTime - lastTime) / 1000)
            );

            lastTime = currentTime;

            update(deltaTime);
            draw();

            requestAnimationFrame(loop);
        }

        startButton.addEventListener("click", resetGame);
        restartButton.addEventListener("click", resetGame);

        canvas.addEventListener("pointerdown", handleInput);

        window.addEventListener("keydown", function(event) {
            if (
                event.code === "Space" ||
                event.code === "ArrowUp" ||
                event.code === "KeyW"
            ) {
                handleInput(event);
            }
        });

        lastTime = performance.now();
        requestAnimationFrame(loop);
    </script>
</body>
</html>