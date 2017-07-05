<div class="udashboard-actions">
  <a href="<?php echo url($link['href'], $link['options']); ?>"<?php echo drupal_attributes($link['options']['attributes']); ?>>
    <?php if ($link['icon']): ?>
      <span class="fa fa-<?php echo $link['icon']; ?>" aria-hidden="true"></span>
    <?php endif; ?>
    <?php if ($link['icon'] && !$show_title): ?>
      <span class="sr-only"><?php echo $link['title']; ?></span>
    <?php else: ?>
      <?php echo $link['title']; ?>
    <?php endif; ?>
  </a>
</div>