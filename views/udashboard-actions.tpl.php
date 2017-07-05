<div class="btn-group udashboard-actions" role="group" aria-label="actions">
  <?php if ($primary): ?>
    <?php foreach ($primary as $group): ?>
      <?php foreach ($group as $link): ?>
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
      <?php endforeach; ?>
    <?php endforeach; ?>
  <?php endif; ?>
  <?php if ($secondary): ?>
    <div class="btn-group" role="group">
      <a type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" name="actions">
        <?php if (!$primary): ?>
          <?php if ($icon): ?>
            <span class="fa fa-<?php echo $icon; ?>" aria-hidden="true"></span>
          <?php endif; ?>
          <?php if ($title): ?>
            <?php if ($show_title): ?>
              <?php echo $title; ?>
            <?php else: ?>
              <span class="sr-only"><?php echo $title; ?></span>
            <?php endif; ?>
          <?php endif; ?>
        <?php else: ?>
          <span<?php if ($primary): ?> class="sr-only"<?php endif; ?>><?php echo t("More actions"); ?></span>
        <?php endif; ?>
        <span class="caret"></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-right">
        <?php $sep = false; ?>
        <?php foreach ($secondary as $group): ?>
          <?php if ($sep): ?>
            <li role="separator" class="divider"></li>
          <?php endif; ?>
          <?php foreach($group as $link): ?>
            <li>
              <a href="<?php echo url($link['href'], $link['options']); ?>"<?php echo drupal_attributes($link['options']['attributes']); ?>>
                <?php if ($link['icon']): ?>
                  <span class="fa fa-<?php echo $link['icon']; ?>" aria-hidden="true"></span>
                <?php endif; ?>
                <?php echo $link['title']; ?>
              </a>
            </li>
          <?php endforeach; ?>
          <?php $sep = true ?>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
</div>

