<?php
$menu_structure = [
    'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'bi bi-speedometer2',
        'url' => 'index.php',
        'role' => ['admin', 'operator']
    ],
    'transaksi' => [
        'title' => 'Transaksi',
        'icon' => 'bi bi-cart',
        'role' => ['admin', 'operator'],
        'submenu' => [
            'barang_masuk' => [
                'title' => 'Barang Masuk',
                'url' => 'transaksi/barang_masuk.php',
                'role' => ['admin', 'operator']
            ],
            'barang_keluar' => [
                'title' => 'Barang Keluar',
                'url' => 'transaksi/barang_keluar.php',
                'role' => ['admin', 'operator']
            ],
            'stok' => [
                'title' => 'Stok',
                'url' => 'transaksi/stok.php',
                'role' => ['admin', 'operator']
            ]
        ]
    ],
    'master' => [
        'title' => 'Master',
        'icon' => 'bi bi-gear',
        'role' => ['admin'],
        'submenu' => [
            'barang' => [
                'title' => 'Barang',
                'url' => 'master/barang.php',
                'role' => ['admin']
            ],
            'user' => [
                'title' => 'User',
                'url' => 'master/user.php',
                'role' => ['admin']
            ]
        ]
    ]
];

function hasMenuAccess($menu_item, $user_role) {
    return in_array($user_role, $menu_item['role']);
}

function renderMenu($menu_structure, $user_role, $base_path = '') {
    $current_page = $_SERVER['PHP_SELF'];
    $output = '';
    
    foreach ($menu_structure as $key => $menu) {
        if (!hasMenuAccess($menu, $user_role)) continue;
        
        if (isset($menu['submenu'])) {
            $output .= '<li class="nav-item dropdown">'
                    . '<a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">'
                    . (isset($menu['icon']) ? "<i class='{$menu['icon']} me-2'></i>" : '')
                    . htmlspecialchars($menu['title'])
                    . '</a>'
                    . '<ul class="dropdown-menu">';
            
            foreach ($menu['submenu'] as $sub_key => $submenu) {
                if (!hasMenuAccess($submenu, $user_role)) continue;
                
                $url = $base_path . $submenu['url'];
                $active = ($current_page == $url) ? ' active' : '';
                
                $output .= "<li><a class='dropdown-item{$active}' href='{$url}'>"
                        . htmlspecialchars($submenu['title'])
                        . '</a></li>';
            }
            
            $output .= '</ul></li>';
        } else {
            $url = $base_path . $menu['url'];
            $active = ($current_page == $url) ? ' active' : '';
            
            $output .= "<li class='nav-item'>"
                    . "<a class='nav-link{$active}' href='{$url}'>"
                    . (isset($menu['icon']) ? "<i class='{$menu['icon']} me-2'></i>" : '')
                    . htmlspecialchars($menu['title'])
                    . '</a></li>';
        }
    }
    
    return $output;
}

function renderPageEnd() {
    $year = date('Y');
    $copyright = <<<EOT
    <footer class="footer mt-auto py-3 bg-primary text-white fixed-bottom" style="user-select: none;">
        <div class="container text-center">
            <span>Copyright © $year <a href="https://github.com/angganurbayu" class="text-black text-decoration-none" target="_blank">Sistem Inventory.</a> All rights reserved.</span>
            <style>
                .footer {
                    pointer-events: none;
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                    user-select: none;
                }
                .footer * {
                    pointer-events: none !important;
                }
                .footer a {
                    pointer-events: auto !important;
                }
                @media print { .footer { display: none !important; } }
            </style>
        </div>
    </footer>
    EOT;
    $protection = <<<EOT
    <script>
        (function() {
            // Mencegah akses ke view source, developer tools, dan save page
            document.addEventListener('keydown', function(e) {
                if (
                    // F12 key
                    (e.key === 'F12' || e.keyCode === 123) ||
                    // Ctrl+Shift+I, J, C (Developer Tools)
                    (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j' || e.key === 'C' || e.key === 'c')) ||
                    // Ctrl+U (View Source)
                    (e.ctrlKey && (e.key === 'U' || e.key === 'u')) ||
                    // Ctrl+S (Save Page)
                    (e.ctrlKey && (e.key === 'S' || e.key === 's'))
                ) {
                    e.stopPropagation();
                    e.preventDefault();
                    return false;
                }
            }, true);

            // Mencegah klik kanan
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            }, true);

            // Mencegah drag & select
            document.addEventListener('selectstart', function(e) {
                if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    return false;
                }
            }, true);

            // Mencegah copy paste kecuali di input dan textarea
            document.addEventListener('copy', function(e) {
                if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    return false;
                }
            }, true);

            // Tambahan: mencegah print screen
            document.addEventListener('keyup', function(e) {
                if (e.key === 'PrintScreen' || e.keyCode === 44) {
                    navigator.clipboard.writeText('');
                }
            }, true);

            // Tambahan: mencegah inspect element melalui menu
            setInterval(function(){
                debugger;
            }, 50);
        })();
    </script>
    <style>
        * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }
        input, textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
    </style>
    EOT;

    return $protection . $copyright;
}
?>