<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$nombreAutor = "Norlan Lao tse Mena Oropeza";

$fraseMotivacional = "Espero aprender mucho de JavaScript en el sentido de la programación dinámica de una aplicación web y combinarlo con el poder de PHP.";

$personajes = [
    "mario" => [
        "nombre" => "Mario",
        "img" => "./img/salta-mario.png",
        "velocidad" => 85,
        "salto" => 95
    ],
    "luigi" => [
        "nombre" => "Luigi",
        "img" => "./img/luigi.png",
        "velocidad" => 80,
        "salto" => 100
    ],
    "browser" => [
        "nombre" => "Bowser",
        "img" => "./img/browser.png",
        "velocidad" => 50,
        "salto" => 40
    ]
];

$juegos = [
    [
        "nombre" => "Neon Archer",
        "archivo" => "archery.php",
        "imagen" => "./img/thumbnail-archery.svg",
        "descripcion" => "Apunta con precisión y alcanza los objetivos neon.",
        "color" => "#00eaff",
        "icono" => "🏹",
        "categoria" => "Puntería"
    ],
    [
        "nombre" => "Neon Duck Hunt",
        "archivo" => "duck-hunt.php",
        "imagen" => "./img/thumbnail-duck-hunt.svg",
        "descripcion" => "Dispara a los patos voladores usando el mouse.",
        "color" => "#ff1493",
        "icono" => "🦆",
        "categoria" => "Disparos"
    ],
    [
        "nombre" => "Nitro Highway",
        "archivo" => "nitro-highway.php",
        "imagen" => "./img/thumbnail-nitro-highway.svg",
        "descripcion" => "Esquiva el tráfico y consigue la máxima distancia.",
        "color" => "#ffe600",
        "icono" => "🏎️",
        "categoria" => "Carreras"
    ],
    [
        "nombre" => "Nitro Highway 2",
        "archivo" => "nitro-highway-2.php",
        "imagen" => "./img/thumbnail-nitro-highway-2.svg",
        "descripcion" => "Acelera por una autopista futurista llena de peligro.",
        "color" => "#a8ff00",
        "icono" => "🚘",
        "categoria" => "Carreras"
    ],
    [
        "nombre" => "Neon Snake",
        "archivo" => "snake.php",
        "imagen" => "./img/thumbnail-snake.svg",
        "descripcion" => "Haz crecer tu serpiente digital sin chocar.",
        "color" => "#00ff88",
        "icono" => "🐍",
        "categoria" => "Arcade"
    ],
    [
        "nombre" => "Neon Flappy",
        "archivo" => "neon-flappy.php",
        "imagen" => "./img/thumbnail-flappy.svg",
        "descripcion" => "Controla el pájaro neon y atraviesa los portales.",
        "color" => "#00f6ff",
        "icono" => "🐦",
        "categoria" => "Arcade"
    ],
    [
        "nombre" => "Neon Spaceman",
        "archivo" => "spaceman.php",
        "imagen" => "./img/thumbnail-spaceman.svg",
        "descripcion" => "Sobrevive en el espacio y supera tus límites.",
        "color" => "#b56cff",
        "icono" => "🚀",
        "categoria" => "Acción"
    ]
];

function escapar(string $valor): string
{
    return htmlspecialchars(
        $valor,
        ENT_QUOTES,
        "UTF-8"
    );
}