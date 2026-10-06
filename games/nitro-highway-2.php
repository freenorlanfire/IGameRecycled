<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $postedDistance = filter_input(
        INPUT_POST,
        'max_distance',
        FILTER_VALIDATE_FLOAT
    );

    if ($postedDistance === false || $postedDistance === null) {
        echo json_encode([
            'success' => false,
            'message' => 'Distancia no válida.'
        ]);
        exit;
    }

    $postedDistance = max(0, round((float) $postedDistance, 2));

    $storedDistance = isset($_SESSION['max_distance'])
        ? (float) $_SESSION['max_distance']
        : 0;

    if ($postedDistance > $storedDistance) {
        $_SESSION['max_distance'] = $postedDistance;
        $storedDistance = $postedDistance;
    }

    echo json_encode([
        'success' => true,
        'max_distance' => $storedDistance
    ]);
    exit;
}

$serverBestDistance = isset($_SESSION['max_distance'])
    ? (float) $_SESSION['max_distance']
    : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neon Highway Runner</title>

    <style>
        :root {
            --cyan: #00f6ff;
            --pink: #ff1493;
            --yellow: #ffe600;
            --dark: #050611;
            --panel: rgba(8, 11, 29, 0.92);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            background:
                radial-gradient(circle at 50% 0%, rgba(0, 246, 255, 0.12), transparent 32%),
                radial-gradient(circle at 20% 100%, rgba(255, 20, 147, 0.14), transparent 30%),
                #050611;
            color: #ffffff;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
            overflow-x: hidden;
        }

        .game-shell {
            width: min(100%, 520px);
            position: relative;
            padding: 18px;
            border: 1px solid rgba(0, 246, 255, 0.55);
            background:
                linear-gradient(145deg, rgba(0, 246, 255, 0.08), transparent 30%),
                linear-gradient(325deg, rgba(255, 20, 147, 0.08), transparent 34%),
                rgba(4, 6, 17, 0.9);
            box-shadow:
                0 0 25px rgba(0, 246, 255, 0.25),
                0 0 70px rgba(255, 20, 147, 0.13),
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

        .top-bar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .brand {
            color: var(--cyan);
            font-size: 1.2rem;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-shadow:
                0 0 5px var(--cyan),
                0 0 18px var(--cyan);
        }

        .brand span {
            color: var(--pink);
        }

        .stats {
            display: flex;
            gap: 9px;
        }

        .stat {
            min-width: 92px;
            padding: 8px 10px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.04);
            text-align: right;
            clip-path: polygon(8px 0, 100% 0, 100% calc(100% - 8px), calc(100% - 8px) 100%, 0 100%, 0 8px);
        }

        .stat-label {
            display: block;
            color: rgba(255, 255, 255, 0.58);
            font-size: 0.62rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .stat-value {
            display: block;
            color: var(--yellow);
            font-size: 1rem;
            font-weight: 900;
            text-shadow: 0 0 8px rgba(255, 230, 0, 0.75);
        }

        .canvas-wrap {
            position: relative;
            width: 450px;
            max-width: 100%;
            margin: 0 auto;
            overflow: hidden;
            border: 2px solid var(--cyan);
            box-shadow:
                0 0 10px var(--cyan),
                0 0 35px rgba(0, 246, 255, 0.55),
                inset 0 0 30px rgba(0, 246, 255, 0.18);
            background: #070817;
        }

        canvas {
            display: block;
            width: 450px;
            height: 550px;
            max-width: 100%;
            aspect-ratio: 450 / 550;
            image-rendering: auto;
            cursor: crosshair;
        }

        .canvas-wrap::after {
            content: "";
            pointer-events: none;
            position: absolute;
            inset: 0;
            background:
                repeating-linear-gradient(
                    to bottom,
                    rgba(255, 255, 255, 0.025) 0,
                    rgba(255, 255, 255, 0.025) 1px,
                    transparent 1px,
                    transparent 5px
                ),
                linear-gradient(
                    90deg,
                    transparent 0%,
                    rgba(0, 246, 255, 0.05) 50%,
                    transparent 100%
                );
            mix-blend-mode: screen;
        }

        .overlay {
            position: absolute;
            inset: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
            background: rgba(2, 3, 12, 0.67);
            backdrop-filter: blur(5px);
            z-index: 5;
        }

        .overlay.hidden {
            display: none;
        }

        .panel {
            width: 100%;
            padding: 26px 20px;
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
            margin: 0 0 8px;
            color: var(--cyan);
            font-size: clamp(1.7rem, 8vw, 2.45rem);
            letter-spacing: 0.12em;
            text-shadow:
                0 0 5px var(--cyan),
                0 0 20px var(--cyan);
        }

        .panel h1 span {
            color: var(--pink);
        }

        .panel p {
            margin: 9px 0;
            color: rgba(255, 255, 255, 0.78);
            line-height: 1.45;
        }

        .panel .danger {
            color: #ff477e;
            font-size: 1.5rem;
            font-weight: 900;
            text-shadow: 0 0 12px #ff477e;
        }

        .controls {
            margin: 17px 0;
            color: var(--yellow);
            font-size: 0.83rem;
            letter-spacing: 0.06em;
        }

        .main-button {
            border: 1px solid var(--cyan);
            padding: 13px 24px;
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
            color: rgba(255, 255, 255, 0.48);
            font-size: 0.7rem;
            letter-spacing: 0.06em;
            text-align: center;
        }

        @media (max-width: 560px) {
            body {
                padding: 10px;
            }

            .game-shell {
                padding: 12px;
            }

            .top-bar {
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
        <div class="top-bar">
            <div class="brand">NEON<span>RUN</span></div>

            <div class="stats">
                <div class="stat">
                    <span class="stat-label">Distancia</span>
                    <span class="stat-value" id="distanceValue">0 m</span>
                </div>

                <div class="stat">
                    <span class="stat-label">Récord</span>
                    <span class="stat-value" id="bestValue"><?php echo htmlspecialchars(number_format($serverBestDistance, 0, ',', '.')); ?> m</span>
                </div>
            </div>
        </div>

        <div class="canvas-wrap">
            <canvas id="gameCanvas" width="450" height="550"></canvas>

            <div class="overlay" id="startOverlay">
                <div class="panel">
                    <h1>NEON<span>RUN</span></h1>
                    <p>Conduce, esquiva y sobrevive al tráfico digital.</p>
                    <div class="controls">
                        [A] [D] &nbsp; o &nbsp; [←] [→] para cambiar de carril
                    </div>
                    <button class="main-button" id="startButton">Iniciar carrera</button>
                </div>
            </div>

            <div class="overlay hidden" id="gameOverOverlay">
                <div class="panel">
                    <div class="danger">GAME OVER</div>
                    <p id="finalDistance">Distancia: 0 m</p>
                    <p id="newRecordMessage"></p>
                    <button class="main-button" id="restartButton">Reintentar</button>
                </div>
            </div>
        </div>

        <div class="footer">
            SISTEMA DE CONTROL ONLINE · EVITA LOS IMPACTOS · ALCANZA LA MÁXIMA DISTANCIA
        </div>
    </main>

    <script>
        "use strict";

        const canvas = document.getElementById("gameCanvas");
        const context = canvas.getContext("2d");

        const distanceValue = document.getElementById("distanceValue");
        const bestValue = document.getElementById("bestValue");
        const startOverlay = document.getElementById("startOverlay");
        const gameOverOverlay = document.getElementById("gameOverOverlay");
        const startButton = document.getElementById("startButton");
        const restartButton = document.getElementById("restartButton");
        const finalDistance = document.getElementById("finalDistance");
        const newRecordMessage = document.getElementById("newRecordMessage");

        const WIDTH = 450;
        const HEIGHT = 550;

        // Posiciones exactas de los cuatro carriles.
        const LANE_X = [56, 168, 280, 392];

        let currentLane = 1;
        let targetLane = 1;

        let player = {
            x: LANE_X[1],
            y: 458,
            width: 39,
            height: 70
        };

        let enemies = [];
        let particles = [];
        let sparks = [];

        let animationFrame = 0;
        let lastTime = 0;
        let elapsedTime = 0;
        let distance = 0;
        let serverBest = <?php echo json_encode($serverBestDistance); ?>;

        let spawnTimer = 0;
        let roadOffset = 0;
        let screenShake = 0;
        let flashEffect = 0;
        let collisionHandled = false;
        let gameState = "menu";

        function random(minimum, maximum) {
            return Math.random() * (maximum - minimum) + minimum;
        }

        function randomInteger(minimum, maximum) {
            return Math.floor(random(minimum, maximum + 1));
        }

        function formatDistance(value) {
            return Math.floor(value).toLocaleString("es-ES");
        }

        function resizeCanvasForDisplay() {
            const devicePixelRatio = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = WIDTH * devicePixelRatio;
            canvas.height = HEIGHT * devicePixelRatio;
            canvas.style.width = WIDTH + "px";
            canvas.style.height = HEIGHT + "px";
            context.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0);
        }

        function resetGame() {
            currentLane = 1;
            targetLane = 1;

            player = {
                x: LANE_X[1],
                y: 458,
                width: 39,
                height: 70
            };

            enemies = [];
            particles = [];
            sparks = [];

            elapsedTime = 0;
            distance = 0;
            spawnTimer = 0;
            roadOffset = 0;
            screenShake = 0;
            flashEffect = 0;
            collisionHandled = false;

            distanceValue.textContent = "0 m";
            bestValue.textContent = formatDistance(serverBest) + " m";
        }

        function startGame() {
            resetGame();
            gameState = "running";
            startOverlay.classList.add("hidden");
            gameOverOverlay.classList.add("hidden");
            lastTime = performance.now();
        }

        function endGame() {
            if (gameState !== "running") {
                return;
            }

            gameState = "gameover";
            screenShake = 16;
            flashEffect = 1;
            createExplosion(player.x, player.y + player.height / 2);

            finalDistance.textContent = "Distancia: " + formatDistance(distance) + " m";
            newRecordMessage.textContent = "Guardando récord en el servidor...";
            gameOverOverlay.classList.remove("hidden");

            saveRecord(distance);
        }

        async function saveRecord(value) {
            try {
                const formData = new FormData();
                formData.append("max_distance", value.toFixed(2));

                const response = await fetch(window.location.href, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });

                const result = await response.json();

                if (result.success) {
                    const previousBest = serverBest;
                    serverBest = Number(result.max_distance);

                    bestValue.textContent = formatDistance(serverBest) + " m";

                    if (distance > previousBest) {
                        newRecordMessage.textContent = "¡NUEVO RÉCORD DE DISTANCIA!";
                        newRecordMessage.style.color = "#ffe600";
                    } else {
                        newRecordMessage.textContent = "Récord actual: " + formatDistance(serverBest) + " m";
                        newRecordMessage.style.color = "#00f6ff";
                    }
                } else {
                    newRecordMessage.textContent = "No se pudo guardar el récord.";
                }
            } catch (error) {
                newRecordMessage.textContent = "Servidor no disponible para guardar el récord.";
            }
        }

        function movePlayer(direction) {
            if (gameState !== "running") {
                return;
            }

            targetLane += direction;

            if (targetLane < 0) {
                targetLane = 0;
            }

            if (targetLane > LANE_X.length - 1) {
                targetLane = LANE_X.length - 1;
            }

            currentLane = targetLane;
        }

        function createEnemy() {
            const lane = randomInteger(0, LANE_X.length - 1);
            const isTruck = Math.random() < 0.25;
            const width = isTruck ? 48 : 39;
            const height = isTruck ? 92 : 70;

            const colors = isTruck
                ? ["#ff287d", "#9b5cff", "#ff6b00", "#00c8ff"]
                : ["#ff264d", "#a5ff00", "#ff42ce", "#7b5cff", "#00deff"];

            enemies.push({
                lane: lane,
                x: LANE_X[lane],
                y: -height - random(20, 120),
                width: width,
                height: height,
                speed: random(155, 250) + elapsedTime * 2.1,
                type: isTruck ? "truck" : "car",
                color: colors[randomInteger(0, colors.length - 1)],
                tilt: random(-0.025, 0.025),
                pulse: random(0, Math.PI * 2)
            });
        }

        function createExplosion(x, y) {
            for (let index = 0; index < 75; index += 1) {
                const angle = random(0, Math.PI * 2);
                const speed = random(70, 350);

                particles.push({
                    x: x,
                    y: y,
                    velocityX: Math.cos(angle) * speed,
                    velocityY: Math.sin(angle) * speed,
                    life: random(0.45, 1.3),
                    maxLife: 1.3,
                    size: random(2, 6),
                    color: Math.random() < 0.5 ? "#ffe600" : "#ff1493"
                });
            }

            for (let index = 0; index < 28; index += 1) {
                sparks.push({
                    x: x,
                    y: y,
                    velocityX: random(-230, 230),
                    velocityY: random(-250, 130),
                    life: random(0.3, 0.8),
                    size: random(1, 3)
                });
            }
        }

        function updateParticles(deltaTime) {
            for (let index = particles.length - 1; index >= 0; index -= 1) {
                const particle = particles[index];

                particle.x += particle.velocityX * deltaTime;
                particle.y += particle.velocityY * deltaTime;
                particle.velocityY += 230 * deltaTime;
                particle.life -= deltaTime;

                if (particle.life <= 0) {
                    particles.splice(index, 1);
                }
            }

            for (let index = sparks.length - 1; index >= 0; index -= 1) {
                const spark = sparks[index];

                spark.x += spark.velocityX * deltaTime;
                spark.y += spark.velocityY * deltaTime;
                spark.velocityY += 420 * deltaTime;
                spark.life -= deltaTime;

                if (spark.life <= 0) {
                    sparks.splice(index, 1);
                }
            }
        }

        function rectanglesCollide(first, second) {
            const firstLeft = first.x - first.width / 2 + 6;
            const firstRight = first.x + first.width / 2 - 6;
            const firstTop = first.y + 7;
            const firstBottom = first.y + first.height - 7;

            const secondLeft = second.x - second.width / 2 + 6;
            const secondRight = second.x + second.width / 2 - 6;
            const secondTop = second.y + 7;
            const secondBottom = second.y + second.height - 7;

            return (
                firstLeft < secondRight &&
                firstRight > secondLeft &&
                firstTop < secondBottom &&
                firstBottom > secondTop
            );
        }

        function update(deltaTime) {
            if (gameState === "running") {
                elapsedTime += deltaTime;

                const roadSpeed = 210 + Math.min(elapsedTime * 10, 310);
                distance += roadSpeed * deltaTime * 0.09;
                roadOffset += roadSpeed * deltaTime;

                distanceValue.textContent = formatDistance(distance) + " m";

                const playerAcceleration = 14;
                player.x += (LANE_X[targetLane] - player.x) * Math.min(1, playerAcceleration * deltaTime);

                spawnTimer -= deltaTime;

                const spawnInterval = Math.max(0.27, 0.72 - elapsedTime * 0.008);

                if (spawnTimer <= 0) {
                    createEnemy();
                    spawnTimer = spawnInterval * random(0.72, 1.16);
                }

                for (let index = enemies.length - 1; index >= 0; index -= 1) {
                    const enemy = enemies[index];

                    enemy.y += enemy.speed * deltaTime;
                    enemy.pulse += deltaTime * 5;

                    if (rectanglesCollide(player, enemy)) {
                        endGame();
                    }

                    if (enemy.y > HEIGHT + 120) {
                        enemies.splice(index, 1);
                    }
                }

                screenShake = Math.max(0, screenShake - deltaTime * 42);
                flashEffect = Math.max(0, flashEffect - deltaTime * 3);
            }

            updateParticles(deltaTime);
        }

        function drawBackground() {
            const gradient = context.createLinearGradient(0, 0, 0, HEIGHT);
            gradient.addColorStop(0, "#090b25");
            gradient.addColorStop(0.5, "#10112d");
            gradient.addColorStop(1, "#050611");

            context.fillStyle = gradient;
            context.fillRect(0, 0, WIDTH, HEIGHT);

            context.fillStyle = "rgba(0, 246, 255, 0.08)";
            context.fillRect(0, 0, 4, HEIGHT);
            context.fillRect(WIDTH - 4, 0, 4, HEIGHT);

            const horizonGlow = context.createRadialGradient(
                WIDTH / 2,
                80,
                5,
                WIDTH / 2,
                80,
                310
            );

            horizonGlow.addColorStop(0, "rgba(0, 246, 255, 0.22)");
            horizonGlow.addColorStop(0.5, "rgba(255, 20, 147, 0.08)");
            horizonGlow.addColorStop(1, "rgba(0, 0, 0, 0)");

            context.fillStyle = horizonGlow;
            context.fillRect(0, 0, WIDTH, 260);
        }

        function drawRoad() {
            context.save();

            context.fillStyle = "rgba(8, 10, 24, 0.92)";
            context.fillRect(0, 0, WIDTH, HEIGHT);

            context.strokeStyle = "rgba(0, 246, 255, 0.32)";
            context.lineWidth = 2;
            context.beginPath();
            context.moveTo(4, 0);
            context.lineTo(4, HEIGHT);
            context.moveTo(WIDTH - 4, 0);
            context.lineTo(WIDTH - 4, HEIGHT);
            context.stroke();

            const intensity = Math.min(1, distance / 500);

            context.lineWidth = 3;
            context.setLineDash([31, 31]);

            const dashSpeed = 1 + intensity * 2.7;
            const animatedOffset = -((roadOffset * dashSpeed) % 62);

            for (let index = 1; index < LANE_X.length; index += 1) {
                const separatorX = (LANE_X[index - 1] + LANE_X[index]) / 2;

                context.strokeStyle = "rgba(0, 246, 255, " + (0.38 + intensity * 0.45) + ")";
                context.shadowColor = "#00f6ff";
                context.shadowBlur = 5 + intensity * 11;
                context.lineDashOffset = animatedOffset;

                context.beginPath();
                context.moveTo(separatorX, -80);
                context.lineTo(separatorX, HEIGHT + 80);
                context.stroke();
            }

            context.setLineDash([]);
            context.shadowBlur = 0;

            const sideGlow = context.createLinearGradient(0, 0, WIDTH, 0);
            sideGlow.addColorStop(0, "rgba(255, 20, 147, 0.22)");
            sideGlow.addColorStop(0.15, "rgba(255, 20, 147, 0)");
            sideGlow.addColorStop(0.85, "rgba(0, 246, 255, 0)");
            sideGlow.addColorStop(1, "rgba(0, 246, 255, 0.22)");

            context.fillStyle = sideGlow;
            context.fillRect(0, 0, WIDTH, HEIGHT);

            context.restore();
        }

        function drawCarBody(x, y, width, height, color, isPlayer) {
            context.save();
            context.translate(x, y);

            const glowColor = isPlayer ? "#ffe600" : color;
            context.shadowColor = glowColor;
            context.shadowBlur = isPlayer ? 19 : 12;

            context.fillStyle = color;
            context.beginPath();
            context.roundRect(
                -width / 2,
                0,
                width,
                height,
                isPlayer ? 10 : 7
            );
            context.fill();

            context.shadowBlur = 0;

            context.fillStyle = isPlayer ? "#171305" : "#130a19";
            context.beginPath();
            context.roundRect(
                -width * 0.32,
                height * 0.16,
                width * 0.64,
                height * 0.27,
                5
            );
            context.fill();

            context.fillStyle = isPlayer
                ? "rgba(0, 246, 255, 0.88)"
                : "rgba(0, 246, 255, 0.7)";

            context.beginPath();
            context.roundRect(
                -width * 0.23,
                height * 0.2,
                width * 0.46,
                height * 0.14,
                3
            );
            context.fill();

            context.fillStyle = isPlayer ? "#fff7a3" : "#ffefff";
            context.fillRect(-width * 0.39, height * 0.75, width * 0.19, 5);
            context.fillRect(width * 0.2, height * 0.75, width * 0.19, 5);

            context.fillStyle = isPlayer ? "#ff2a84" : "#ff152f";
            context.fillRect(-width * 0.39, height * 0.1, width * 0.19, 5);
            context.fillRect(width * 0.2, height * 0.1, width * 0.19, 5);

            context.fillStyle = "rgba(0, 0, 0, 0.75)";
            context.fillRect(-width / 2 - 3, height * 0.22, 6, height * 0.2);
            context.fillRect(width / 2 - 3, height * 0.22, 6, height * 0.2);
            context.fillRect(-width / 2 - 3, height * 0.65, 6, height * 0.2);
            context.fillRect(width / 2 - 3, height * 0.65, 6, height * 0.2);

            if (isPlayer) {
                context.strokeStyle = "#fff176";
                context.lineWidth = 2;
                context.beginPath();
                context.roundRect(-width / 2, 0, width, height, 10);
                context.stroke();
            }

            context.restore();
        }

        function drawEnemies() {
            for (const enemy of enemies) {
                context.save();
                context.translate(enemy.x, enemy.y);
                context.rotate(enemy.tilt);

                drawCarBody(
                    0,
                    0,
                    enemy.width,
                    enemy.height,
                    enemy.color,
                    false
                );

                if (enemy.type === "truck") {
                    context.fillStyle = "rgba(255, 255, 255, 0.3)";
                    context.fillRect(
                        -enemy.width * 0.31,
                        enemy.height * 0.45,
                        enemy.width * 0.62,
                        3
                    );

                    context.fillStyle = "#ff1493";
                    context.fillRect(
                        -enemy.width * 0.4,
                        enemy.height * 0.88,
                        enemy.width * 0.8,
                        4
                    );
                }

                context.restore();
            }
        }

        function drawPlayer() {
            const enginePulse = 5 + Math.sin(elapsedTime * 18) * 2;

            context.save();
            context.globalAlpha = 0.75;
            context.shadowColor = "#00f6ff";
            context.shadowBlur = 18;

            context.fillStyle = "#00f6ff";
            context.beginPath();
            context.ellipse(
                player.x,
                player.y + player.height + 4,
                10 + enginePulse,
                19,
                0,
                0,
                Math.PI * 2
            );
            context.fill();

            context.restore();

            drawCarBody(
                player.x,
                player.y,
                player.width,
                player.height,
                "#ffe600",
                true
            );
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
                    particle.size * opacity + 0.5,
                    0,
                    Math.PI * 2
                );
                context.fill();
                context.restore();
            }

            for (const spark of sparks) {
                context.save();
                context.globalAlpha = Math.max(0, spark.life);
                context.strokeStyle = "#ffffff";
                context.shadowColor = "#ffe600";
                context.shadowBlur = 8;
                context.lineWidth = spark.size;
                context.beginPath();
                context.moveTo(spark.x, spark.y);
                context.lineTo(
                    spark.x - spark.velocityX * 0.025,
                    spark.y - spark.velocityY * 0.025
                );
                context.stroke();
                context.restore();
            }
        }

        function drawHudEffects() {
            const dangerLevel = Math.min(1, elapsedTime / 70);

            if (dangerLevel > 0) {
                const edgeGradient = context.createLinearGradient(0, 0, WIDTH, 0);
                edgeGradient.addColorStop(0, "rgba(255, 20, 147, " + dangerLevel * 0.15 + ")");
                edgeGradient.addColorStop(0.5, "rgba(0, 0, 0, 0)");
                edgeGradient.addColorStop(1, "rgba(255, 20, 147, " + dangerLevel * 0.15 + ")");

                context.fillStyle = edgeGradient;
                context.fillRect(0, 0, WIDTH, HEIGHT);
            }

            if (flashEffect > 0) {
                context.fillStyle = "rgba(255, 255, 255, " + flashEffect * 0.34 + ")";
                context.fillRect(0, 0, WIDTH, HEIGHT);
            }
        }

        function draw() {
            context.save();

            if (screenShake > 0) {
                context.translate(
                    random(-screenShake, screenShake),
                    random(-screenShake, screenShake)
                );
            }

            drawBackground();
            drawRoad();
            drawEnemies();

            if (gameState !== "gameover" || particles.length === 0) {
                drawPlayer();
            }

            drawParticles();
            drawHudEffects();

            context.restore();
        }

        function loop(currentTime) {
            const deltaTime = Math.min(
                0.034,
                Math.max(0.001, (currentTime - lastTime) / 1000)
            );

            lastTime = currentTime;

            update(deltaTime);
            draw();

            animationFrame = requestAnimationFrame(loop);
        }

        window.addEventListener("keydown", function(event) {
            const key = event.key.toLowerCase();

            if (
                key === "arrowleft" ||
                key === "arrowright" ||
                key === "a" ||
                key === "d" ||
                key === " "
            ) {
                event.preventDefault();
            }

            if (key === "arrowleft" || key === "a") {
                movePlayer(-1);
            }

            if (key === "arrowright" || key === "d") {
                movePlayer(1);
            }

            if (key === " " && gameState === "menu") {
                startGame();
            }

            if (key === " " && gameState === "gameover") {
                startGame();
            }
        });

        startButton.addEventListener("click", startGame);
        restartButton.addEventListener("click", startGame);

        canvas.addEventListener("pointerdown", function(event) {
            if (gameState !== "running") {
                return;
            }

            const bounds = canvas.getBoundingClientRect();
            const pointerX = event.clientX - bounds.left;

            if (pointerX < WIDTH / 2) {
                movePlayer(-1);
            } else {
                movePlayer(1);
            }
        });

        window.addEventListener("resize", resizeCanvasForDisplay);

        resizeCanvasForDisplay();
        resetGame();
        lastTime = performance.now();
        animationFrame = requestAnimationFrame(loop);
    </script>
</body>
</html>