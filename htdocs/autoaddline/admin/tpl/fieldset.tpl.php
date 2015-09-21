<!--
    fieldset.tpl.php
-->
    <fieldset <?php print $fieldset['options'] ?>>
        <?php $legend = $fieldset['legend']; ?>
        <legend <?php print $legend['options']; ?>><?php print_titre($legend['html']); ?></legend>
        <?php if (array_key_exists('hiddens', $fieldset)): ?>
            <?php foreach ($fieldset['hiddens'] as $hidden): ?>
                <input type="hidden" name="<?php print $hidden['name']; ?>" value="<?php print $hidden['value']; ?>"/>
            <?php endforeach; ?>
        <?php endif; ?>              
        <?php
        foreach ($fieldset['tables'] as $table)
            include ('tpl/table.tpl.php');
        ?>

    </fieldset>
    <div>&nbsp;</div>


