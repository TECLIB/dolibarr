<!--
    table.tpl.php
-->
<?php if (array_key_exists('title_container_options', $table) || array_key_exists('title', $table)): ?>>
<div 
    <?php if (array_key_exists('title_container_options', $table)) print $table['title_container_options']; ?>>
        <?php if (array_key_exists('title', $table)) print_titre($table['title']); ?>
</div>
<?php endif; ?>
<table <?php print $table['options']; ?>>
    <?php if (array_key_exists('labels', $table)): ?>
        <tr class="liste_titre">
            <?php foreach ($table['labels'] as $label): ?>
                <td <?php print $label['options']; ?>><?php print $label['html']; ?></td>
            <?php endforeach; ?>
        </tr>
    <?php endif; ?>
    <?php $var = true; ?>
    <?php foreach ($table['lines'] as $line): ?>
        <?php if ($table['alt_lines_bg']) $var = !$var; ?>
        <tr 
        <?php
        if ($table['print_lines_bg'])
            print $bc[$var];
        print $line['options'];
        ?>>                
                <?php foreach ($line['cells'] as $cell): ?>

                <td <?php print $cell['options']; ?>><?php print $cell['html']; ?></td>
            <?php endforeach; ?>
        </tr>            
    <?php endforeach; ?>
</table>