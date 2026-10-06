    </main>
    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <h2>iGameRecycled</h2>
                <p>Original arcade portal focused on fast-loading, browser-first mini games.</p>
            </div>
            <div>
                <h3>Explore</h3>
                <ul>
                    <li><a href="<?php echo ir_escape(ir_url('games.php')); ?>">All Games</a></li>
                    <li><a href="<?php echo ir_escape(ir_url('categories.php')); ?>">Categories</a></li>
                    <li><a href="<?php echo ir_escape(ir_url('guides.php')); ?>">Game Guides</a></li>
                </ul>
            </div>
            <div>
                <h3>Legal</h3>
                <ul>
                    <li><a href="<?php echo ir_escape(ir_url('privacy.php')); ?>">Privacy</a></li>
                    <li><a href="<?php echo ir_escape(ir_url('cookies.php')); ?>">Cookies</a></li>
                    <li><a href="<?php echo ir_escape(ir_url('terms.php')); ?>">Terms</a></li>
                    <li><a href="<?php echo ir_escape(ir_url('affiliate-disclosure.php')); ?>">Affiliate Disclosure</a></li>
                </ul>
            </div>
        </div>
        <p class="footer-note">&copy; <?php echo date('Y'); ?> iGameRecycled. Legacy Mario World files remain available at repository root.</p>
    </footer>
</div>
<script src="<?php echo ir_escape(ir_url('js/navigation.js')); ?>"></script>
<script src="<?php echo ir_escape(ir_url('js/app.js')); ?>"></script>
</body>
</html>
