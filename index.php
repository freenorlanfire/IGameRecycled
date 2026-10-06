<?php
declare(strict_types=1);

$pageTitle = "Mario World Arcade";
$activePage = "home";

require __DIR__ . "/partials/header.php";

$seleccionado = "mario";

if (
    isset($_POST["personaje"]) &&
    is_string($_POST["personaje"]) &&
    array_key_exists($_POST["personaje"], $personajes)
) {
    $seleccionado = $_POST["personaje"];
}

$personajeSeleccionado = $personajes[$seleccionado];
?>

<main>
    <h1>Mario World Arcade</h1>

    <p class="intro">
        Un mundo de juegos creado con PHP, JavaScript y Canvas.
    </p>

    <div class="titulo">
        <img
            class="titulo-mario"
            src="./img/titulo-mario.png"
            alt="Título del mundo de Mario"
        >
    </div>

    <section class="controles">
        <form
            id="formularioPersonaje"
            method="POST"
            action="index.php"
        >
            <label for="personaje">
                <strong>Elige tu personaje:</strong>
            </label>

            <select
                name="personaje"
                id="personaje"
            >
                <?php foreach ($personajes as $clave => $personaje): ?>
                    <option
                        value="<?php echo escapar($clave); ?>"
                        <?php echo $seleccionado === $clave
                            ? "selected"
                            : ""; ?>
                    >
                        <?php echo escapar($personaje["nombre"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </section>

    <section class="escenario">
        <img
            id="personajeInteractivo"
            class="personaje personaje-interactivo <?php echo escapar(
                $seleccionado
            ); ?>"
            src="<?php echo escapar(
                $personajeSeleccionado["img"]
            ); ?>"
            alt="<?php echo escapar(
                $personajeSeleccionado["nombre"]
            ); ?>"
            title="Haz clic sobre el personaje"
            role="button"
            tabindex="0"
        >

        <img
            class="personaje hongo"
            src="./img/star.png"
            alt="Estrella"
        >

        <button
            type="button"
            class="btn-jugar"
            id="btnJugarPersonaje"
        >
            Let's Play con
            <?php echo escapar(
                $personajeSeleccionado["nombre"]
            ); ?>
        </button>
    </section>

    <button
        type="button"
        class="btn-juegos"
        id="btnMostrarJuegos"
        aria-expanded="false"
        aria-controls="modoJuegos"
    >
        <span aria-hidden="true">▣</span>
        Abrir centro de juegos
    </button>

    <section
        class="modo-juegos"
        id="modoJuegos"
        aria-hidden="true"
    >
        <span
            class="anchor-target"
            id="categorias"
        ></span>

        <div class="launcher-header">
            <span class="launcher-kicker">
                MARIO WORLD // ONLINE ARCADE
            </span>

            <h2>Centro de juegos</h2>

            <p class="modo-juegos-subtitulo">
                Pulsa la imagen de cualquier juego para iniciar la partida.
            </p>
        </div>

        <div class="lista-juegos">
            <?php foreach ($juegos as $juego): ?>
                <?php
                $urlJuego = "./games/" . rawurlencode(
                    $juego["archivo"]
                );
                ?>

                <article
                    class="tarjeta-juego"
                    style="--color-juego: <?php echo escapar(
                        $juego["color"]
                    ); ?>"
                >
                    <div class="miniatura-juego">
                        <a
                            class="miniatura-enlace"
                            href="<?php echo escapar($urlJuego); ?>"
                            data-game-link
                            aria-label="Abrir <?php echo escapar(
                                $juego["nombre"]
                            ); ?>"
                        >
                            <img
                                src="<?php echo escapar(
                                    $juego["imagen"]
                                ); ?>"
                                alt="Miniatura de <?php echo escapar(
                                    $juego["nombre"]
                                ); ?>"
                                loading="lazy"
                            >

                            <span
                                class="miniatura-overlay"
                                aria-hidden="true"
                            ></span>

                            <span
                                class="miniatura-icono"
                                aria-hidden="true"
                            >
                                <?php echo escapar($juego["icono"]); ?>
                            </span>

                            <span class="categoria-juego">
                                <?php echo escapar(
                                    $juego["categoria"]
                                ); ?>
                            </span>
                        </a>
                    </div>

                    <div class="estado-juego">
                        <span class="estado-punto"></span>
                        ONLINE
                    </div>

                    <h3>
                        <?php echo escapar($juego["nombre"]); ?>
                    </h3>

                    <p>
                        <?php echo escapar(
                            $juego["descripcion"]
                        ); ?>
                    </p>

                    <a
                        class="btn-abrir-juego"
                        href="<?php echo escapar($urlJuego); ?>"
                        data-game-link
                    >
                        <span>Iniciar juego</span>
                        <span class="flecha-juego">↗</span>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <button
            type="button"
            class="btn-cerrar-juegos"
            id="btnCerrarJuegos"
        >
            Cerrar centro de juegos
        </button>
    </section>

    <section
        class="community-panel"
        id="community"
    >
        <div class="community-content">
            <span class="section-kicker">
                COMMUNITY HUB
            </span>

            <h2>La aventura continúa</h2>

            <p>
                Explora juegos, supera tus récords y comparte tus mejores
                partidas con otros jugadores.
            </p>
        </div>

        <div class="community-status">
            <span class="status-light"></span>
            SERVIDOR ONLINE
        </div>
    </section>

    <section
        class="frase"
        id="about"
    >
        <h3 class="bienvenida">
            Estadísticas de
            <?php echo escapar(
                $personajeSeleccionado["nombre"]
            ); ?>
        </h3>

        <p>
            Velocidad:
            <?php echo (int) $personajeSeleccionado["velocidad"]; ?>%
        </p>

        <div class="barra">
            <div
                class="progreso"
                style="width: <?php echo (int) $personajeSeleccionado["velocidad"]; ?>%;"
            ></div>
        </div>

        <p>
            Salto:
            <?php echo (int) $personajeSeleccionado["salto"]; ?>%
        </p>

        <div class="barra">
            <div
                class="progreso"
                style="
                    width: <?php echo (int) $personajeSeleccionado["salto"]; ?>%;
                    background: #00b259;
                
            ></div>
        </div>

        <p class="nombre-autor">
            Autor:
            <?php echo escapar($nombreAutor); ?>
        </p>

        <h3 class="texto-motivacional">
            "<?php echo escapar($fraseMotivacional); ?>"
        </h3>
    </section>
</main>

<audio
    id="sonidoNormal"
    preload="auto"
>
    <source
        src="./audio/fahhh-sound.mp3"
        type="audio/mpeg"
    >
</audio>

<audio
    id="sonidoAccion"
    preload="auto"
>
    <source
        src="./audio/fahhhh-gunshot.mp3"
        type="audio/mpeg"
    >
</audio>

<audio
    id="sonidoMario"
    preload="auto"
>
    <source
        src="./audio/mario-sound.mp3"
        type="audio/mpeg"
    >
</audio>

<audio
    id="sonidoLuigi"
    preload="auto"
>
    <source
        src="./audio/luigi-sound.mp3"
        type="audio/mpeg"
    >
</audio>

<audio
    id="sonidoBowser"
    preload="auto"
>
    <source
        src="./audio/browser-sound.mp3"
        type="audio/mpeg"
    >
</audio>

<script>
    "use strict";

    const btnMostrarJuegos =
        document.getElementById("btnMostrarJuegos");

    const btnCerrarJuegos =
        document.getElementById("btnCerrarJuegos");

    const modoJuegos =
        document.getElementById("modoJuegos");

    const btnJugarPersonaje =
        document.getElementById("btnJugarPersonaje");

    const formularioPersonaje =
        document.getElementById("formularioPersonaje");

    const selectorPersonaje =
        document.getElementById("personaje");

    const personajeInteractivo =
        document.getElementById("personajeInteractivo");

    const sonidoNormal =
        document.getElementById("sonidoNormal");

    const sonidoAccion =
        document.getElementById("sonidoAccion");
    
    const sonidosPersonajes = {
    mario: document.getElementById("sonidoMario"),
    luigi: document.getElementById("sonidoLuigi"),
    browser: document.getElementById("sonidoBowser") 
    };

    let navegacionEnCurso = false;
    let ultimoSonido = 0;

    function reproducirSonido(audio) {
        if (!audio) {
            return;
        }

        const ahora = Date.now();

        if (ahora - ultimoSonido < 100) {
            return;
        }

        ultimoSonido = ahora;
        audio.pause();
        audio.currentTime = 0;

        const promesa = audio.play();

        if (promesa !== undefined) {
            promesa.catch(function(error) {
                console.warn(
                    "El navegador bloqueó el sonido:",
                    error
                );
            });
        }
    }
    
    function reproducirSonidoPersonaje(clavePersonaje) {
    const audioPersonaje =
        sonidosPersonajes[clavePersonaje];

    if (!audioPersonaje) {
        return;
    }

    audioPersonaje.pause();
    audioPersonaje.currentTime = 0;

    const promesa =
        audioPersonaje.play();

    if (promesa !== undefined) {
        promesa.catch(function(error) {
            console.warn(
                "No se pudo reproducir el sonido del personaje:",
                error
            );
        });
    }
}

    function abrirCentroDeJuegos() {
        reproducirSonido(sonidoAccion);

        modoJuegos.classList.add("visible");
        modoJuegos.setAttribute("aria-hidden", "false");
        btnMostrarJuegos.setAttribute("aria-expanded", "true");

        btnMostrarJuegos.innerHTML =
            "<span aria-hidden=\"true\">⌃</span> Ocultar centro de juegos";

        modoJuegos.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });
    }

    function cerrarCentroDeJuegos() {
        reproducirSonido(sonidoAccion);

        modoJuegos.classList.remove("visible");
        modoJuegos.setAttribute("aria-hidden", "true");
        btnMostrarJuegos.setAttribute("aria-expanded", "false");

        btnMostrarJuegos.innerHTML =
            "<span aria-hidden=\"true\">▣</span> Abrir centro de juegos";
    }

    function navegarAlJuego(enlace) {
        if (
            navegacionEnCurso ||
            !enlace ||
            !enlace.href
        ) {
            return;
        }

        navegacionEnCurso = true;
        reproducirSonido(sonidoAccion);

        setTimeout(function() {
            window.location.href = enlace.href;
        }, 300);
    }

    btnMostrarJuegos.addEventListener(
        "click",
        function() {
            if (modoJuegos.classList.contains("visible")) {
                cerrarCentroDeJuegos();
            } else {
                abrirCentroDeJuegos();
            }
        }
    );

    btnCerrarJuegos.addEventListener(
        "click",
        cerrarCentroDeJuegos
    );

    btnJugarPersonaje.addEventListener(
        "click",
        abrirCentroDeJuegos
    );

  selectorPersonaje.addEventListener(
    "change",
    function() {
        const personajeElegido =
            selectorPersonaje.value;

        const audioPersonaje =
            sonidosPersonajes[personajeElegido];

        if (!audioPersonaje) {
            formularioPersonaje.submit();
            return;
        }

        audioPersonaje.pause();
        audioPersonaje.currentTime = 0;

        const promesa =
            audioPersonaje.play();

        if (promesa !== undefined) {
            promesa.catch(function() {
                formularioPersonaje.submit();
            });
        }

        audioPersonaje.onended = function() {
            formularioPersonaje.submit();
        };

        setTimeout(function() {
            formularioPersonaje.submit();
        }, 1800);
    }
  );

    document.addEventListener(
        "click",
        function(evento) {
            const enlaceJuego =
                evento.target.closest("[data-game-link]");

            if (enlaceJuego) {
                evento.preventDefault();
                navegarAlJuego(enlaceJuego);
                return;
            }

            const elementoInteractivo =
                evento.target.closest(
                    "a, button, select, input, textarea, " +
                    "[role='button']"
                );

            if (!elementoInteractivo) {
                reproducirSonido(sonidoNormal);
            }
        },
        true
    );

    personajeInteractivo.addEventListener(
        "click",
        function() {
            reproducirSonido(sonidoNormal);
        }
    );

    personajeInteractivo.addEventListener(
        "keydown",
        function(evento) {
            if (
                evento.key === "Enter" ||
                evento.key === " "
            ) {
                evento.preventDefault();
                reproducirSonido(sonidoNormal);
            }
        }
    );
</script>

<?php require __DIR__ . "/partials/footer.php"; ?>