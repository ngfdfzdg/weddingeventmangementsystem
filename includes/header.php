<?php

?>
<header>
    <div class="logo">Book With Us</div>
    <nav>
        <ul>
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="services.php"><i class="fas fa-concierge-bell"></i> Services</a></li>
            <li><a href="contact.php"><i class="fas fa-phone"></i> Contact Us</a></li>
            <li><a href="about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="dropdown">
                    <a href="#"><i class="fas fa-user"></i> Profile <i class="fas fa-caret-down"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="profile.php">My Profile</a></li>
                        <li><a href="messages.php">Messages</a></li>
                        <li><a href="cart.php">Cart</a></li>
                        <li><a href="history.php">My History</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="signup.php"><i class="fas fa-user-plus"></i> Signup</a></li>
                <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>