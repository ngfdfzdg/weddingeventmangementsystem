<footer>
    <p>© <?php echo date('Y'); ?> Wedding Management. All rights reserved.</p>
    <div class="rating-info">
        <?php
        include 'db_connect.php';
        $avg_rating_query = $conn->query("SELECT AVG(rating) as avg_rating FROM feedback");
        $avg_rating = round($avg_rating_query->fetch_assoc()['avg_rating'], 1) ?: 'No ratings';
        ?>
        <p>Our Average Rating: <span class="stars"><?php echo is_numeric($avg_rating) ? str_repeat('★', floor($avg_rating)) . str_repeat('☆', 5 - floor($avg_rating)) : 'No ratings'; ?></span> (<?php echo $avg_rating; ?>/5)</p>
    </div>
</footer>