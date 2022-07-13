<div class="card graph-container">

    <div class="card" style="width: 500px;">

        <h2><?php echo $data['title'] ?></h2>

        <table border="1">
            <thead>
                <tr>
                    <th></th>
                    <?php foreach ($data['total'] as $l => $value) : ?>

                        <th><?php echo $l ?></th>

                    <?php endforeach; ?>
                    <th>TOT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Visite mese corrente</td>
                    <?php foreach ($data['current'] as $l => $value) : ?>
                        <td><?php echo $value ?></td>
                    <?php endforeach; ?>
                    <td><?php echo array_sum($data['current']) ?></td>
                </tr>
                <tr>
                    <td>Visite mese precedente</td>
                    <?php foreach ($data['prev'] as $l => $value) : ?>
                        <td><?php echo $value ?></td>
                    <?php endforeach; ?>
                    <td><?php echo array_sum($data['prev']) ?></td>
                </tr>
                <tr>
                    <td>Visite mese totali</td>
                    <?php foreach ($data['total'] as $l => $value) : ?>
                        <td><?php echo $value ?></td>
                    <?php endforeach; ?>
                    <td><?php echo array_sum($data['total']) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <img src="<?php echo $data['graph']->EncodeImage() ?>" alt="<?php echo $data['title'] ?>">

</div>