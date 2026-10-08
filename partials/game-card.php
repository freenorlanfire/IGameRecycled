<?php if (!isset($game) || !is_array($game)) { return; } ?>
<article class="game-card" data-category="<?php echo ir_escape(strtolower($game['category'])); ?>" data-name="<?php echo ir_escape(strtolower($game['title'])); ?>" data-tags="<?php echo ir_escape(strtolower(implode(' ', $game['tags']))); ?>">
    <a href="<?php echo ir_escape(ir_url('games/' . $game['file'])); ?>" class="game-thumb-link" aria-label="Play <?php echo ir_escape($game['title']); ?>">
        <img src="<?php echo ir_escape($game['thumb']); ?>" alt="<?php echo ir_escape($game['title']); ?> thumbnail" loading="lazy">
    </a>
    <div class="game-content">
        <p class="game-category"><?php echo ir_escape($game['category']); ?></p>
        <h3><?php echo ir_escape($game['title']); ?></h3>
        <p><?php echo ir_escape($game['description']); ?></p>
        <a class="btn btn-small" href="<?php echo ir_escape(ir_url('games/' . $game['file'])); ?>">Play now</a>
    </div>
</article>
