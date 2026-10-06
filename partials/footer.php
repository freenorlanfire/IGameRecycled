    <footer class="site-footer">
        <div class="footer-grid">
            <div class="footer-brand">
                <a
                    class="brand-logo footer-logo"
                    href="index.php"
                >
                    <span class="brand-mark">M</span>

                    <span class="brand-copy">
                        <strong>MARIO</strong>
                        <small>WORLD ARCADE</small>
                    </span>
                </a>

                <p>
                    Un universo de juegos neon creado con PHP,
                    JavaScript y Canvas.
                </p>
            </div>

            <div class="footer-column">
                <h3>Explorar</h3>

                <a href="index.php">Home</a>
                <a href="categories.php">Categorías</a>
                <a href="community.php">Community</a>
            </div>

            <div class="footer-column">
                <h3>Proyecto</h3>

                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
                <a href="login.php">Iniciar sesión</a>
            </div>

            <div class="footer-column">
                <h3>Contacto</h3>

                <p>
                    ¿Tienes una idea para un juego?
                </p>

                <a
                    class="footer-email"
                    href="mailto:freenorlanfire@gmail.com"
                >
                    freenorlanfire@gmail.com
                </a>
            </div>
        </div>

        <div class="footer-bottom">
            <span>
                © <?php echo date("Y"); ?> Mario World Arcade
            </span>

            <span>
                PHP · JavaScript · Canvas
            </span>
        </div>
    </footer>

    <script>
        "use strict";

        const menuToggle =
            document.getElementById("menuToggle");

        const mainNavigation =
            document.getElementById("mainNavigation");

        if (menuToggle && mainNavigation) {
            menuToggle.addEventListener(
                "click",
                function() {
                    const menuAbierto =
                        mainNavigation.classList.toggle("open");

                    menuToggle.classList.toggle(
                        "active",
                        menuAbierto
                    );

                    menuToggle.setAttribute(
                        "aria-expanded",
                        menuAbierto ? "true" : "false"
                    );
                }
            );

            mainNavigation
                .querySelectorAll("a")
                .forEach(function(enlace) {
                    enlace.addEventListener(
                        "click",
                        function() {
                            mainNavigation.classList.remove("open");
                            menuToggle.classList.remove("active");

                            menuToggle.setAttribute(
                                "aria-expanded",
                                "false"
                            );
                        }
                    );
                });

            window.addEventListener(
                "resize",
                function() {
                    if (window.innerWidth > 700) {
                        mainNavigation.classList.remove("open");
                        menuToggle.classList.remove("active");

                        menuToggle.setAttribute(
                            "aria-expanded",
                            "false"
                        );
                    }
                }
            );
        }
    </script>
</body>
</html>