<div class="paginador">
		<?php if ($pages->previous) : ?>
		<a href="<?php echo $pages->previous->url; ?>"><?php echo $pages->previous->title; ?></a>
		<?php endif;
		foreach ($pages->pages as $pageNumber => $page) : ?>
		|
			<?php if($page->link) :?>
		<a href="<?php echo $page->url; ?>"><?php echo $page->title; ?></a>
			<?php else: ?>
		<strong style="color: red;"><?php echo $page->title; ?></strong>
		<?php	endif;
		endforeach; ?>
		|
		<?php if ($pages->next) : ?>
		<a href="<?php echo $pages->next->url; ?>"><?php echo $pages->next->title; ?></a>
		<?php endif; ?>
</div>