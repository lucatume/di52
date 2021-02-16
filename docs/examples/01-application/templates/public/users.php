<ul>
    <?php
    foreach ($users as $user) : ?>
        <li>
            <strong><?php $user['id']; ?></strong> - <?php echo $user['username']; ?>
        </li>
        <?php
    endforeach; ?>
</ul>
