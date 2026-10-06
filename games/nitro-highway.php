<?php
session_start();

header_remove('X-Powered-By');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $currentDistance = filter_input(
        INPUT_POST,
        'distance',
        FILTER_VALIDATE_INT
    );

    if ($currentDistance === false || $currentDistance === null) {
        http_response_code(400);

        echo json_encode([
            'status' => 'error',
            'message' => 'Distancia inválida.'
        ]);

        exit;
    }

    $currentDistance = max(0, (int) $currentDistance);

    if (
        !isset($_SESSION['max_distance']) ||
        $currentDistance > (int) $_SESSION['max_distance']
    ) {
        $_SESSION['max_distance'] = $currentDistance;
    }

    echo json_encode([
        'status' => 'success',
        'max_distance' => (int) $_SESSION['max_distance']
    ]);

    exit;
}

$maxDistance = isset($_SESSION['max_distance'])
    ? (int) $_SESSION['max_distance']
    : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nitro Highway - Test de Servidor</title>

    <style>
        :root {
            --pink: #ff007f;
            --cyan: #00ffff;
            --yellow: #ffea00;
            --dark: #111424;
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
            background:
                radial-gradient(
                    circle at 50% 0%,
                    rgba(0, 255, 255, 0.12),
                    transparent 35%
                ),
                radial-gradient(
                    circle at 50% 100%,
                    rgba(255, 0, 127, 0.12),
                    transparent 38%
                ),
                var(--dark);
            color: #ffffff;
            font-family: "Impact", "Arial Black", Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow-x: hidden;
            padding: 18px;
        }

        #ui {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            width: 450px;
            max-width: 100%;
            margin-bottom: 10px;
            font-size: 1.08rem;
            letter-spacing: 1px;
            text-align: right;
            text-shadow: 0 0 10px var(--pink);
        }

        .score-box {
            color: var(--cyan);
            text-align: left;
            text-shadow: 0 0 10px var(--cyan);
        }

        .score-box span,
        #max-val {
            color: var(--yellow);
        }

        canvas {
            display: block;
            width: 450px;
            height: 550px;
            max-width: 100%;
            border: 4px solid var(--pink);
            background: #222;
            box-shadow:
                0 0 14px var(--pink),
                0 0 35px rgba(255, 0, 127, 0.45),
                inset 0 0 25px rgba(0, 255, 255, 0.12);
            touch-action: none;
        }

        .instructions {
            width: 450px;
            max-width: 100%;
            margin-top: 13px;
            color: #a0aec0;
            font-family: monospace;
            font-size: 0.9rem;
            line-height: 1.5;
            text-align: center;
        }

        .instructions strong {
            color: var(--cyan);
        }

        @media (max-width: 520px) {
            #ui {
                font-size: 0.83rem;
                gap: 8px;
            }

            body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<div id="ui">
    <div class="score-box">
        METROS: <span id="dist-val">0</span>
    </div>

    <div>
        MÁXIMO PHP:
        <span id="max-val">
            <?php echo htmlspecialchars((string) $maxDistance, ENT_QUOTES, 'UTF-8'); ?>
        </span>
        m
    </div>
</div>

<canvas id="roadCanvas" width="450" height="550"></canvas>

<div class="instructions">
    Usa <strong>← / A</strong> y <strong>→ / D</strong> para cambiar de carril.
    Pulsa <strong>ESPACIO</strong> para reiniciar.
</div>

<script>
"use strict";

const canvas = document.getElementById("roadCanvas");
const ctx = canvas.getContext("2d");

const distanceElement = document.getElementById("dist-val");
const maxDistanceElement = document.getElementById("max-val");

const CANVAS_WIDTH = 450;
const CANVAS_HEIGHT = 550;

/*
 * Centros exactos de los cuatro carriles.
 */
const lanes = [56, 168, 280, 392];

const carWidth = 50;
const carHeight = 85;

let audioContext = null;

let player = {
    lane: 1,
    x: lanes[1],
    targetX: lanes[1],
    y: 440
};

let traffic = [];
let particles = [];

let distance = 0;
let gameOver = false;
let gameSpeed = 5;
let spawnTimer = 0;
let shakeTime = 0;
let lastTime = 0;
let roadOffset = 0;
let gameStarted = false;

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

    oscillator.connect(gain);
    gain.connect(currentAudioContext.destination);

    const now = currentAudioContext.currentTime;

    if (type === "dodge") {
        oscillator.type = "sine";
        oscillator.frequency.setValueAtTime(300, now);
        oscillator.frequency.exponentialRampToValueAtTime(700, now + 0.1);

        gain.gain.setValueAtTime(0.08, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.1);

        oscillator.start(now);
        oscillator.stop(now + 0.1);
    }

    if (type === "crash") {
        oscillator.type = "sawtooth";
        oscillator.frequency.setValueAtTime(130, now);
        oscillator.frequency.exponentialRampToValueAtTime(22, now + 0.5);

        gain.gain.setValueAtTime(0.35, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.5);

        oscillator.start(now);
        oscillator.stop(now + 0.5);
    }
}

function randomInteger(minimum, maximum) {
    return Math.floor(
        Math.random() * (maximum - minimum + 1)
    ) + minimum;
}

function randomFloat(minimum, maximum) {
    return Math.random() * (maximum - minimum) + minimum;
}

function clamp(value, minimum, maximum) {
    return Math.max(minimum, Math.min(maximum, value));
}

function resetGame() {
    player.lane = 1;
    player.x = lanes[1];
    player.targetX = lanes[1];

    traffic = [];
    particles = [];

    distance = 0;
    gameSpeed = 5;
    spawnTimer = 0;
    shakeTime = 0;
    roadOffset = 0;
    gameOver = false;
    gameStarted = true;

    distanceElement.textContent = "0";
}

function movePlayer(direction) {
    if (gameOver) {
        return;
    }

    const nextLane = clamp(
        player.lane + direction,
        0,
        lanes.length - 1
    );

    if (nextLane !== player.lane) {
        player.lane = nextLane;
        player.targetX = lanes[nextLane];
        playSound("dodge");
    }
}

function handleKeyDown(event) {
    if (
        event.code === "ArrowLeft" ||
        event.code === "ArrowRight" ||
        event.code === "KeyA" ||
        event.code === "KeyD" ||
        event.code === "Space"
    ) {
        event.preventDefault();
    }

    if (event.code === "Space") {
        if (gameOver || !gameStarted) {
            resetGame();
        }

        return;
    }

    if (event.repeat) {
        return;
    }

    if (
        event.code === "ArrowLeft" ||
        event.code === "KeyA"
    ) {
        movePlayer(-1);
    }

    if (
        event.code === "ArrowRight" ||
        event.code === "KeyD"
    ) {
        movePlayer(1);
    }
}

function spawnVehicle() {
    spawnTimer -= 1;

    const spawnInterval = Math.max(
        25,
        78 - Math.floor(gameSpeed * 4)
    );

    if (spawnTimer > 0) {
        return;
    }

    spawnTimer = spawnInterval;

    const vehiclesNearTop = traffic.filter(function(vehicle) {
        return vehicle.y < 140;
    }).length;

    if (vehiclesNearTop >= 2) {
        return;
    }

    const randomLane = randomInteger(0, lanes.length - 1);
    const isTruck = Math.random() > 0.65;

    const vehicleWidth = isTruck ? 58 : carWidth;
    const vehicleHeight = isTruck ? 130 : carHeight;

    traffic.push({
        lane: randomLane,
        x: lanes[randomLane],
        y: -vehicleHeight - randomFloat(20, 130),
        w: vehicleWidth,
        h: vehicleHeight,
        speed: randomFloat(0.5, 2.5),
        color: isTruck
            ? "#e53e3e"
            : "hsl(" + randomInteger(0, 360) + ", 85%, 50%)",
        isTruck: isTruck,
        passed: false
    });
}

function createExplosion(x, y, color, amount) {
    for (let index = 0; index < amount; index += 1) {
        const angle = randomFloat(0, Math.PI * 2);
        const speed = randomFloat(2, 9);

        particles.push({
            x: x,
            y: y,
            vx: Math.cos(angle) * speed,
            vy: Math.sin(angle) * speed,
            radius: randomFloat(2, 6),
            alpha: 1,
            decay: randomFloat(0.012, 0.026),
            gravity: randomFloat(0.05, 0.18),
            color: color
        });
    }
}

function sendDistanceToPHP(finalDistance) {
    const formData = new FormData();

    formData.append(
        "distance",
        String(Math.max(0, Math.floor(finalDistance)))
    );

    fetch(window.location.href, {
        method: "POST",
        body: formData,
        credentials: "same-origin",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    })
        .then(function(response) {
            if (!response.ok) {
                throw new Error("Error HTTP " + response.status);
            }

            return response.json();
        })
        .then(function(data) {
            if (
                data.status === "success" &&
                data.max_distance !== undefined
            ) {
                maxDistanceElement.textContent = data.max_distance;
            }
        })
        .catch(function(error) {
            console.error("No se pudo guardar el récord:", error);
        });
}

function checkCollision(firstObject, secondObject) {
    const firstLeft = firstObject.x - firstObject.w / 2 + 6;
    const firstRight = firstObject.x + firstObject.w / 2 - 6;
    const firstTop = firstObject.y + 7;
    const firstBottom = firstObject.y + firstObject.h - 7;

    const secondLeft = secondObject.x - secondObject.w / 2 + 6;
    const secondRight = secondObject.x + secondObject.w / 2 - 6;
    const secondTop = secondObject.y + 7;
    const secondBottom = secondObject.y + secondObject.h - 7;

    return (
        firstLeft < secondRight &&
        firstRight > secondLeft &&
        firstTop < secondBottom &&
        firstBottom > secondTop
    );
}

function update(deltaTime) {
    if (shakeTime > 0) {
        shakeTime -= deltaTime * 60;
    }

    for (let index = particles.length - 1; index >= 0; index -= 1) {
        const particle = particles[index];

        particle.x += particle.vx * deltaTime * 60;
        particle.y += particle.vy * deltaTime * 60;
        particle.vy += particle.gravity * deltaTime * 60;
        particle.alpha -= particle.decay * deltaTime * 60;

        if (particle.alpha <= 0) {
            particles.splice(index, 1);
        }
    }

    if (gameOver) {
        return;
    }

    gameSpeed += 0.12 * deltaTime * 60;

    distance += gameSpeed * deltaTime * 0.85;
    distanceElement.textContent = String(Math.floor(distance));

    player.targetX = lanes[player.lane];

    player.x += (
        player.targetX - player.x
    ) * Math.min(1, 18 * deltaTime);

    roadOffset += gameSpeed * deltaTime * 60;

    spawnVehicle();

    for (let index = traffic.length - 1; index >= 0; index -= 1) {
        const vehicle = traffic[index];

        vehicle.y += (
            gameSpeed - vehicle.speed
        ) * deltaTime * 60;

        if (checkCollision(
            {
                x: player.x,
                y: player.y,
                w: carWidth,
                h: carHeight
            },
            {
                x: vehicle.x,
                y: vehicle.y,
                w: vehicle.w,
                h: vehicle.h
            }
        )) {
            gameOver = true;
            shakeTime = 25;

            playSound("crash");

            createExplosion(
                player.x,
                player.y + carHeight / 2,
                "#ffaa00",
                36
            );

            createExplosion(
                vehicle.x,
                vehicle.y + vehicle.h / 2,
                "#ff0055",
                36
            );

            sendDistanceToPHP(distance);
        }

        if (
            !vehicle.passed &&
            vehicle.y > player.y - 20
        ) {
            vehicle.passed = true;

            if (vehicle.lane !== player.lane) {
                distance += 150;
            }
        }

        if (vehicle.y > CANVAS_HEIGHT + 160) {
            traffic.splice(index, 1);
        }
    }
}

function drawRoad() {
    ctx.fillStyle = "#1a1a24";
    ctx.fillRect(0, 0, CANVAS_WIDTH, CANVAS_HEIGHT);

    const roadGradient = ctx.createLinearGradient(
        0,
        0,
        CANVAS_WIDTH,
        0
    );

    roadGradient.addColorStop(0, "rgba(255, 0, 127, 0.18)");
    roadGradient.addColorStop(0.08, "rgba(0, 0, 0, 0)");
    roadGradient.addColorStop(0.92, "rgba(0, 0, 0, 0)");
    roadGradient.addColorStop(1, "rgba(0, 255, 255, 0.18)");

    ctx.fillStyle = roadGradient;
    ctx.fillRect(0, 0, CANVAS_WIDTH, CANVAS_HEIGHT);

    ctx.strokeStyle = "#ff007f";
    ctx.lineWidth = 6;
    ctx.shadowColor = "#ff007f";
    ctx.shadowBlur = 16;

    ctx.beginPath();
    ctx.moveTo(20, 0);
    ctx.lineTo(20, CANVAS_HEIGHT);
    ctx.stroke();

    ctx.strokeStyle = "#00ffff";
    ctx.shadowColor = "#00ffff";

    ctx.beginPath();
    ctx.moveTo(430, 0);
    ctx.lineTo(430, CANVAS_HEIGHT);
    ctx.stroke();

    ctx.shadowBlur = 0;

    ctx.strokeStyle = "rgba(255, 255, 255, 0.18)";
    ctx.lineWidth = 4;
    ctx.setLineDash([40, 40]);
    ctx.lineDashOffset = -(roadOffset * 1.6);

    for (let laneIndex = 0; laneIndex < 3; laneIndex += 1) {
        const separatorX = (
            lanes[laneIndex] + lanes[laneIndex + 1]
        ) / 2;

        ctx.beginPath();
        ctx.moveTo(separatorX, -50);
        ctx.lineTo(separatorX, CANVAS_HEIGHT + 50);
        ctx.stroke();
    }

    ctx.setLineDash([]);

    const speedGlow = Math.min(1, gameSpeed / 22);

    ctx.fillStyle = "rgba(0, 255, 255, " + (speedGlow * 0.08) + ")";
    ctx.fillRect(0, 0, CANVAS_WIDTH, CANVAS_HEIGHT);
}

function drawVehicle(vehicle) {
    ctx.save();

    ctx.translate(vehicle.x, vehicle.y);

    ctx.fillStyle = vehicle.color;
    ctx.shadowColor = vehicle.color;
    ctx.shadowBlur = 13;

    ctx.beginPath();
    ctx.roundRect(
        -vehicle.w / 2,
        0,
        vehicle.w,
        vehicle.h,
        vehicle.isTruck ? 7 : 10
    );
    ctx.fill();

    ctx.shadowBlur = 0;

    ctx.fillStyle = "#111";

    if (vehicle.isTruck) {
        ctx.fillRect(
            -vehicle.w / 2 + 8,
            17,
            vehicle.w - 16,
            35
        );

        ctx.fillStyle = "rgba(0, 255, 255, 0.75)";
        ctx.fillRect(
            -vehicle.w / 2 + 12,
            22,
            vehicle.w - 24,
            16
        );
    } else {
        ctx.fillRect(
            -vehicle.w / 2 + 8,
            20,
            vehicle.w - 16,
            23
        );

        ctx.fillStyle = "rgba(0, 255, 255, 0.75)";
        ctx.fillRect(
            -vehicle.w / 2 + 12,
            24,
            vehicle.w - 24,
            12
        );
    }

    ctx.fillStyle = "#ff3333";
    ctx.fillRect(
        -vehicle.w / 2 + 5,
        vehicle.h - 8,
        9,
        6
    );

    ctx.fillRect(
        vehicle.w / 2 - 14,
        vehicle.h - 8,
        9,
        6
    );

    ctx.fillStyle = "#ffffff";
    ctx.fillRect(
        -vehicle.w / 2 + 5,
        5,
        9,
        5
    );

    ctx.fillRect(
        vehicle.w / 2 - 14,
        5,
        9,
        5
    );

    ctx.restore();
}

function drawPlayer() {
    if (gameOver) {
        return;
    }

    ctx.save();

    const enginePulse = 5 + Math.sin(performance.now() / 70) * 2;

    ctx.fillStyle = "#00ffff";
    ctx.shadowColor = "#00ffff";
    ctx.shadowBlur = 18;

    ctx.beginPath();
    ctx.ellipse(
        player.x,
        player.y + carHeight + 5,
        9 + enginePulse,
        20,
        0,
        0,
        Math.PI * 2
    );
    ctx.fill();

    ctx.shadowBlur = 16;
    ctx.fillStyle = "#ffea00";
    ctx.shadowColor = "#ffea00";

    ctx.beginPath();
    ctx.roundRect(
        player.x - carWidth / 2,
        player.y,
        carWidth,
        carHeight,
        9
    );
    ctx.fill();

    ctx.shadowBlur = 0;

    ctx.fillStyle = "#000000";

    ctx.fillRect(
        player.x - carWidth / 2 - 2,
        player.y + carHeight - 10,
        carWidth + 4,
        6
    );

    ctx.fillRect(
        player.x - carWidth / 2 + 8,
        player.y + 28,
        carWidth - 16,
        22
    );

    ctx.fillStyle = "#00ffff";

    ctx.fillRect(
        player.x - carWidth / 2 + 6,
        player.y + 12,
        carWidth - 12,
        10
    );

    ctx.fillStyle = "#ffffff";
    ctx.shadowBlur = 15;
    ctx.shadowColor = "#ffffff";

    ctx.fillRect(
        player.x - carWidth / 2 + 4,
        player.y,
        8,
        4
    );

    ctx.fillRect(
        player.x + carWidth / 2 - 12,
        player.y,
        8,
        4
    );

    ctx.restore();
}

function drawParticles() {
    for (const particle of particles) {
        ctx.save();

        ctx.globalAlpha = particle.alpha;
        ctx.fillStyle = particle.color;
        ctx.shadowColor = particle.color;
        ctx.shadowBlur = 12;

        ctx.beginPath();
        ctx.arc(
            particle.x,
            particle.y,
            particle.radius,
            0,
            Math.PI * 2
        );
        ctx.fill();

        ctx.restore();
    }
}

function drawGameOver() {
    if (!gameOver) {
        return;
    }

    ctx.fillStyle = "rgba(10, 10, 20, 0.84)";
    ctx.fillRect(0, 0, CANVAS_WIDTH, CANVAS_HEIGHT);

    ctx.textAlign = "center";

    ctx.fillStyle = "#ff0055";
    ctx.shadowColor = "#ff0055";
    ctx.shadowBlur = 18;
    ctx.font = "bold 38px Impact";
    ctx.fillText(
        "¡SIN CONTROL!",
        CANVAS_WIDTH / 2,
        CANVAS_HEIGHT / 2 - 35
    );

    ctx.shadowBlur = 0;
    ctx.fillStyle = "#00ffff";
    ctx.font = "20px Arial";

    ctx.fillText(
        "Distancia: " + Math.floor(distance) + " m",
        CANVAS_WIDTH / 2,
        CANVAS_HEIGHT / 2 + 8
    );

    ctx.fillStyle = "#ffffff";
    ctx.font = "16px Arial";

    ctx.fillText(
        "Pulsa ESPACIO para reiniciar",
        CANVAS_WIDTH / 2,
        CANVAS_HEIGHT / 2 + 44
    );
}

function draw() {
    ctx.save();

    if (shakeTime > 0) {
        const shakeX = randomFloat(-8, 8);
        const shakeY = randomFloat(-8, 8);

        ctx.translate(shakeX, shakeY);
    }

    ctx.clearRect(
        -20,
        -20,
        CANVAS_WIDTH + 40,
        CANVAS_HEIGHT + 40
    );

    drawRoad();

    for (const vehicle of traffic) {
        drawVehicle(vehicle);
    }

    drawPlayer();
    drawParticles();
    drawGameOver();

    ctx.restore();
}

function loop(currentTime) {
    if (!lastTime) {
        lastTime = currentTime;
    }

    const deltaTime = Math.min(
        0.033,
        (currentTime - lastTime) / 1000
    );

    lastTime = currentTime;

    update(deltaTime);
    draw();

    requestAnimationFrame(loop);
}

canvas.addEventListener("pointerdown", function(event) {
    if (gameOver) {
        resetGame();
        return;
    }

    const bounds = canvas.getBoundingClientRect();
    const pointerX = event.clientX - bounds.left;

    if (pointerX < bounds.width / 2) {
        movePlayer(-1);
    } else {
        movePlayer(1);
    }
});

window.addEventListener("keydown", handleKeyDown);

resetGame();
requestAnimationFrame(loop);
</script>

</body>
</html>
