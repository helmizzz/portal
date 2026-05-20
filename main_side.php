<?php
require_once 'includes/init.php';
// Memastikan variabel tema selalu tersedia saat navigasi ini dipanggil
require_once __DIR__ . '/includes/theme_handler.php';
?>
<button class="menu-toggler" onclick="toggleSidebar()">
    <div class="hamburger-lines">
        <span class="line line1"></span>
        <span class="line line2"></span>
        <span class="line line3"></span>
    </div>
</button>

<div id="overlay" class="overlay" onclick="toggleSidebar()"></div>
<div id="mySidebar" class="sidebar">
    <div class="sidebar-header">
        <h3 class="ms-3">Navigation</h3>
    </div>
    <div class="sidebar-body">
        <a href="documents.php" class="menu-item active">
            <i class="bi bi-file-earmark-text me-2"></i>
            <span>Documents</span>
        </a>
    </div>
</div>  
<script>
    function toggleSidebar() {
        document.getElementById("mySidebar").classList.toggle("active");
        document.getElementById("overlay").classList.toggle("active");
        document.querySelector(".menu-toggler").classList.toggle("active");
    }
</script>