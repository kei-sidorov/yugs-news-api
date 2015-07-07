<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="http://<?=$_SERVER["HTTP_HOST"].$path?>assets/css/style.css?v=1" />
</head>

<body>

<h2><?=$item['header']?></h2>

<?php $image = $item['images'][0]; $i = 0; ?>
<a href="showimage.php?images=<?=json_encode($item['images'])?>&imageNum=<?=$i?>#gid=1&pid=<?=$i + 1?>" target="_blank">
    <img src="<?=$image?>" style="width: 100%; height: auto;" />
</a>

<?php unset($item['images'][0]); ?>

<p>
    <?=$item['text']?>
</p>

<?php if (sizeof($item['images']) > 0): ?>
    <div class="photos">
        <?php  $i = 0;
        foreach ($item['images'] as $image): ?>
            <a href="showimage.php?images=<?=json_encode($item['images'])?>&imageNum=<?=$i?>#gid=1&pid=<?=$i + 1?>" target="_blank">
                <img src="<?=$image?>" />
            </a>
            <?php $i++; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</body>
</html>