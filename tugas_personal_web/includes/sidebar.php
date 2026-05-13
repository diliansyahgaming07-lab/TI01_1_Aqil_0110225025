<?php
// includes/sidebar.php
$page = $_GET['page'] ?? 'home';
?>
<div class="list-group shadow-sm">
    <a href="?page=home" class="list-group-item list-group-item-action <?= ($page == 'home') ? 'active' : '' ?>">
        <i class="fas fa-home"></i> Home
    </a>
    <a href="?page=about" class="list-group-item list-group-item-action <?= ($page == 'about') ? 'active' : '' ?>">
        <i class="fas fa-user"></i> About Me
    </a>
    <a href="?page=contact" class="list-group-item list-group-item-action <?= ($page == 'contact') ? 'active' : '' ?>">
        <i class="fas fa-envelope"></i> Contact Me
    </a>
    
    <div class="dropdown">
        <a href="#" class="list-group-item list-group-item-action dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-graduation-cap"></i> My Studies
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="?page=level">📊 Level Pendidikan (CRUD)</a></li>
            <li><a class="dropdown-item" href="?page=studies">🏫 Riwayat Studi (CRUD)</a></li>
        </ul>
    </div>
</div>