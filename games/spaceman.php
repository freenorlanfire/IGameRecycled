<?php
session_start();

// Si el juego envía una nueva puntuación máxima mediante POST, la guardamos en la sesión del servidor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['score'])) {
    $current_score = filter_input(INPUT_POST, 'score', FILTER_VALIDATE_INT);
    if (!isset($_SESSION['high_score']) || $current_score > $_SESSION['high_score']) {
        $_SESSION['high_score'] = $current_score;
    }
    echo json_encode(['status' => 'success', 'high_score' => $_SESSION['high_score']]);
    exit;
}


$high_score = isset($_SESSION['high_score']) ? $_SESSION['high_score'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arcade Espacial - Test de Servidor</title>
    <style>
        body {
            margin: 0;
            background: #050510;
            font-family: 'Courier New', Courier, monospace;
            color: #00ffcc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
        }
        #ui {
            display: flex;
            justify-content: space-between;
            width: 600px;
            margin-bottom: 10px;
            font-size: 1.2rem;
            text-shadow: 0 0 8px #00ffcc;
        }
        canvas {
            border: 4px solid #00ffcc;
            box-shadow: 0 0 20px rgba(0, 255, 204, 0.5);
            background: #000;
            display: block;
        }
        .instructions {
            margin-top: 15px;
            color: #888;
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>
<body>

<div id="ui">
    <div>PUNTOS: <span id="score-val">0</span></div>
    <div>RÉCORD (PHP): <span id="highscore-val"><?php echo $high_score; ?></span></div>
</div>

<canvas id="gameCanvas" width="600" height="500"></canvas>

<div class="instructions">
    Use <strong>← / A</strong> y <strong>→ / D</strong> para moverse. Presione <strong>ESPACIO</strong> para disparar.
</div>

<script>
const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');

// Variables de audio sintetizado (Dopamina auditiva sin archivos externos)
const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
function playSound(type) {
    if (audioCtx.state === 'suspended') audioCtx.resume();
    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.connect(gain);
    gain.connect(audioCtx.destination);
    
    if (type === 'laser') {
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(440, audioCtx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(110, audioCtx.currentTime + 0.1);
        gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
        gain.gain.linearRampToValueAtTime(0, audioCtx.currentTime + 0.1);
        osc.start(); osc.stop(audioCtx.currentTime + 0.1);
    } else if (type === 'explosion') {
        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(150, audioCtx.currentTime);
        osc.frequency.linearRampToValueAtTime(40, audioCtx.currentTime + 0.3);
        gain.gain.setValueAtTime(0.4, audioCtx.currentTime);
        gain.gain.linearRampToValueAtTime(0, audioCtx.currentTime + 0.3);
        osc.start(); osc.stop(audioCtx.currentTime + 0.3);
    }
}

// Estado del juego
const player = { x: 285, y: 440, w: 30, h: 30, speed: 7 };
let keys = {};
let bullets = [];
let enemies = [];
let particles = [];
let score = 0;
let highScore = <?php echo $high_score; ?>;
let gameOver = false;
let spawnRate = 0.02;

// Eventos de teclado
window.addEventListener('keydown', e => keys[e.code] = true);
window.addEventListener('keyup', e => keys[e.code] = false);

function spawnEnemy() {
    if (Math.random() < spawnRate && !gameOver) {
        enemies.push({
            x: Math.random() * (canvas.width - 30),
            y: -30,
            w: 25,
            h: 25,
            speed: 2 + Math.random() * 3,
            color: `hsl(${Math.random() * 360}, 100%, 60%)`
        });
    }
}

function createExplosion(x, y, color) {
    for (let i = 0; i < 12; i++) {
        particles.push({
            x: x, y: y,
            vx: (Math.random() - 0.5) * 6,
            vy: (Math.random() - 0.5) * 6,
            radius: Math.random() * 4 + 1,
            alpha: 1,
            color: color
        });
    }
}

function sendScoreToPHP(finalScore) {
    const formData = new FormData();
    formData.append('score', finalScore);
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.high_score) {
            document.getElementById('highscore-val').innerText = data.high_score;
        }
    });
}

function update() {
    if (gameOver) {
        if (keys['Space']) {
            // Reiniciar juego
            bullets = []; enemies = []; particles = [];
            score = 0; gameOver = false; spawnRate = 0.02;
            player.x = 285;
            document.getElementById('score-val').innerText = score;
        }
        return;
    }

    // Movimiento jugador
    if (keys['ArrowLeft'] || keys['KeyA']) player.x = Math.max(0, player.x - player.speed);
    if (keys['ArrowRight'] || keys['KeyD']) player.x = Math.min(canvas.width - player.w, player.x + player.speed);

    // Disparar
    if (keys['Space'] && (bullets.length === 0 || bullets[bullets.length - 1].y < player.y - 120)) {
        bullets.push({ x: player.x + player.w / 2 - 2, y: player.y, w: 4, h: 10 });
        playSound('laser');
    }

    // Actualizar disparos
    bullets.forEach((b, index) => {
        b.y -= 9;
        if (b.y < 0) bullets.splice(index, 1);
    });

    // Crear y actualizar enemigos
    spawnEnemy();
    enemies.forEach((e, eIdx) => {
        e.y += e.speed;
        
        // Colisión enemigo contra jugador
        if (e.x < player.x + player.w && e.x + e.w > player.x && e.y < player.y + player.h && e.y + e.h > player.y) {
            gameOver = true;
            playSound('explosion');
            createExplosion(player.x + player.w/2, player.y + player.h/2, '#ff0055');
            sendScoreToPHP(score);
        }

        // Si pasa el límite inferior
        if (e.y > canvas.height) enemies.splice(eIdx, 1);

        // Colisión bala contra enemigo
        bullets.forEach((b, bIdx) => {
            if (b.x < e.x + e.w && b.x + b.w > e.x && b.y < e.y + e.h && b.y + b.h > e.y) {
                playSound('explosion');
                createExplosion(e.x + e.w/2, e.y + e.h/2, e.color);
                enemies.splice(eIdx, 1);
                bullets.splice(bIdx, 1);
                score += 10;
                document.getElementById('score-val').innerText = score;
                // Aumentar dificultad gradualmente
                spawnRate += 0.001;
            }
        });
    });

    // Partículas
    particles.forEach((p, index) => {
        p.x += p.vx;
        p.y += p.vy;
        p.alpha -= 0.03;
        if (p.alpha <= 0) particles.splice(index, 1);
    });
}

function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Dibujar Jugador (Nave cian futurista)
    if (!gameOver) {
        ctx.fillStyle = '#00ffcc';
        ctx.beginPath();
        ctx.moveTo(player.x + player.w / 2, player.y);
        ctx.lineTo(player.x, player.y + player.h);
        ctx.lineTo(player.x + player.w, player.y + player.h);
        ctx.closePath();
        ctx.fill();
        // Efecto brillo de motor
        ctx.fillStyle = '#ffaa00';
        ctx.fillRect(player.x + player.w/2 - 4, player.y + player.h, 8, 5 + Math.random()*5);
    }

    // Dibujar Disparos
    ctx.fillStyle = '#00ffff';
    bullets.forEach(b => ctx.fillRect(b.x, b.y, b.w, b.h));

    // Dibujar Enemigos
    enemies.forEach(e => {
        ctx.fillStyle = e.color;
        ctx.fillRect(e.x, e.y, e.w, e.h);
        // Detalles visuales agresivos
        ctx.fillStyle = '#000';
        ctx.fillRect(e.x + 4, e.y + 4, e.w - 8, e.h - 8);
    });

    // Dibujar Partículas de explosión
    particles.forEach(p => {
        ctx.save();
        ctx.globalAlpha = p.alpha;
        ctx.fillStyle = p.color;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
    });

    // Pantalla de Game Over
    if (gameOver) {
        ctx.fillStyle = 'rgba(0, 0, 0, 0.8)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        ctx.fillStyle = '#ff0055';
        ctx.font = 'bold 36px Courier New';
        ctx.textAlign = 'center';
        ctx.fillText('FIN DEL JUEGO', canvas.width / 2, canvas.height / 2 - 20);
        
        ctx.fillStyle = '#00ffcc';
        ctx.font = '18px Courier New';
        ctx.fillText('Presiona ESPACIO para revivir', canvas.width / 2, canvas.height / 2 + 30);
    }
}

function loop() {
    update();
    draw();
    requestAnimationFrame(loop);
}

loop();
</script>
</body>
</html>
