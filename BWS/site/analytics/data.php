<div class="card">

    <h2><?php echo $data['title'] ?></h2>

    <table border="1">
        <thead>
            <tr>
                <?php foreach ($data['head'] as $index => $headValue) : ?>
                    <th><?php echo $headValue ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <?php foreach ($data['body'] as $bodyLine) : ?>
                    <?php foreach ($bodyLine as $index => $bodyValue) : ?>
                        <td style="text-align: center"><?php echo $bodyValue ?></td>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>

</div>