<div class="card graph-container">

    <?php foreach ($data as $label => $value) : ?>
        <div style="margin-inline: 15px;">
            <h2 style="margin: 0px;"><?php echo $label ?></h2>
            <p style="margin: 0px;"><?php echo $value ?></p>
        </div>
    <?php endforeach; ?>

</div>