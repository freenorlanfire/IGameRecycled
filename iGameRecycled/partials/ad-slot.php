<?php
$adLabel = isset($adLabel) ? (string) $adLabel : 'Ad Space';
$adHint = isset($adHint) ? (string) $adHint : 'Reserved for ad integration';
?>
<section class="ad-slot" aria-label="Advertisement placeholder">
    <p class="ad-label">Advertisement</p>
    <strong><?php echo ir_escape($adLabel); ?></strong>
    <p><?php echo ir_escape($adHint); ?></p>
</section>
