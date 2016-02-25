<?php

$this->loadHelper('Html');

$cakeDescription = __d('cake_dev', 'CakePHP: the rapid development php framework');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <?php echo $this->Html->charset(); ?>
    <title>
        <?php echo $cakeDescription ?>:
        <?php echo $this->fetch('title'); ?>
    </title>
    <?php
        echo $this->Html->meta('icon');

        echo $this->Html->css('cake.generic');

        echo $scripts_for_layout;
    ?>
    <link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css"/>
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css"/>

    <script src="http://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>

</head>
<body>
    <div id="container">
        <div id="header">
            <h1><?php echo $this->Html->link($cakeDescription, 'http://cakephp.org'); ?></h1>
        </div>
        <div id="content">
            <?php echo $this->fetch('content'); ?>
        </div>
        <div id="footer">
            <?php echo $this->Html->link(
                    $this->Html->image('cake.power.gif', array('alt' => $cakeDescription, 'border' => '0')),
                    'http://www.cakephp.org/',
                    array('target' => '_blank', 'escape' => false)
                );
            ?>
        </div>
    </div>
</body>
</html>