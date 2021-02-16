<ul>
    <?php
    foreach ($posts as $post) : ?>
        <li>
            <h3><?php echo $post['title']; ?></h3>
            <p><?php echo $post['body']; ?></p>
        </li>
        <?php
    endforeach; ?>
</ul>
