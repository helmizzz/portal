<?php
require_once 'includes/init.php';
require_once 'includes/theme_handler.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_permissions = getUserPermissions($conn, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Directory</title>
    <link rel="icon" type="image/png" href="uploads/EIP.png">    
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/theme.js" defer></script>
    <style>
        #org-chart-container {
            width: 100%;
            height: 80vh;
        }

        .org-chart-node .node-image {
            border-radius: 50%;
            width: 60px;
            height: 60px;
            object-fit: cover;
            border: 2px solid #fff;
        }

        .org-chart-node .node-name {
            font-size: 14px;
            font-weight: bold;
        }

        .org-chart-node .node-title {
            font-size: 12px;
            color: #555;
        }
        
        body.dark-mode .org-chart-node .node-name {
             color: #e0e0e0;
        }
        
        body.dark-mode .org-chart-node .node-title {
            color: #aaa;
        }
        
        body.dark-mode .org-chart-node .node-image {
            border-color: #333;
        }

        body.dark-mode .bg-light {
            background-color: #2a2a2a !important;
        }

        .node-container {
             background-color: #f8f9fa !important;
        }

        body.dark-mode .node-container {
            background-color: #1e1e1e !important;
        }
    </style>
</head>
<body class="<?= $theme_class ?>">
    <?php include 'main_nav.php'; ?>

    <main class="container-fluid mt-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Employee Directory</h4>
                <div class="d-flex align-items-center">
                    <input type="text" id="search-input" class="form-control form-control-sm me-2" placeholder="Cari nama karyawan...">
                    <button id="search-btn" class="btn btn-primary btn-sm">Cari</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="org-chart-container">
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-org-chart@2"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-flextree@2.1.2/build/d3-flextree.js"></script>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector("#org-chart-container");
        let chart;

        fetch('api/get_employee_data.php')
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 1) { 
                    container.innerHTML = ''; 
                    chart = new d3.OrgChart()
                        .container('#org-chart-container')
                        .data(data)
                        .nodeWidth(d => 150)
                        .nodeHeight(d => 120)
                        .nodeContent(function(d, i, arr, state) {
                            const lightCardBg = '#f8f9fa';
                            const darkCardBg = '#1e1e1e';
                            const cardBg = document.body.classList.contains('dark-mode') ? darkCardBg : lightCardBg;
                            
                            return `
                                <div style="background-color:${cardBg}; width:${d.width}px; height:${d.height}px; border-radius:10px; border: 1px solid #ddd;">
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100 p-2 org-chart-node">
                                        ${
                                            d.data.imageUrl ? `
                                            <img src="${d.data.imageUrl}" class="node-image" />
                                            ` : ''
                                        }
                                        <div class="node-name mt-2 text-center">${d.data.name}</div>
                                        <div class="node-title text-center">${d.data.title || ''}</div>
                                    </div>
                                </div>
                            `;
                        })
                        .render();
                } else {
                     container.innerHTML = '<div class="d-flex justify-content-center align-items-center h-100"><p class="text-muted">Tidak ada data karyawan untuk ditampilkan.</p></div>';
                }
            })
            .catch(error => {
                console.error('Error fetching employee data:', error);
                 container.innerHTML = '<div class="d-flex justify-content-center align-items-center h-100"><p class="text-danger">Gagal memuat data direktori.</p></div>';
            });

        // --- BLOK KODE PENCARIAN YANG DIPERBAIKI ---
        const searchFunction = () => {
            const value = document.getElementById('search-input').value.toLowerCase();
            if (chart) {
                chart.clearHighlighting();

                if (!value) {
                    // Jika input kosong, kembalikan ke tampilan awal
                    chart.fit();
                    return;
                }
                
                // Cari semua node yang cocok
                const matchingNodes = chart.getNodes().filter(node => 
                    node.data.name && node.data.name.toLowerCase().includes(value)
                );

                if (matchingNodes.length > 0) {
                    // Sorot semua node yang cocok
                    matchingNodes.forEach(node => chart.setHighlighted(node.id));
                    
                    // Arahkan ke node pertama yang ditemukan
                    chart.setCentered(matchingNodes[0].id);
                    chart.render();
                } else {
                    alert('Karyawan tidak ditemukan.');
                }
            }
        };

        document.getElementById('search-btn').addEventListener('click', searchFunction);
        
        document.getElementById('search-input').addEventListener('keyup', function(event) {
            if (event.key === "Enter") {
                searchFunction();
            }
        });
    });
    </script>
</body>
</html>