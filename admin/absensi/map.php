<?php
// SECURITY: Admin page to view attendance locations on map
require_once '../../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get date filter
$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// SECURITY: Get attendance data with location
$sql = "SELECT a.*, s.nama_lengkap, s.kelas, s.jurusan, s.foto_profil
        FROM absensi a
        JOIN siswa s ON a.siswa_id = s.id
        WHERE a.tanggal = :tanggal
        AND a.latitude IS NOT NULL
        AND a.longitude IS NOT NULL
        ORDER BY a.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute(['tanggal' => $filter_date]);
$attendance_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// SECURITY: Get school configuration
$schoolConfig = getSchoolConfig($conn);
$schoolLat = floatval($schoolConfig['school_latitude'] ?? -7.9297);
$schoolLon = floatval($schoolConfig['school_longitude'] ?? 110.2538);
$maxDistance = floatval($schoolConfig['max_distance_meters'] ?? 100);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Lokasi Absensi - SMKN 1 Sanden</title>
    
    <!-- Apply saved theme immediately -->
    <script>
        (function() {
            const theme = localStorage.getItem('app-theme');
            if (theme === 'light') {
                document.documentElement.classList.add('light-theme');
            }
        })();
    </script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/theme.css">
    
    <!-- SECURITY: Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <style>
        .glass-effect {
            background: rgba(10, 10, 10, 0.9);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(20, 184, 166, 0.2);
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.8), 0 2px 4px rgba(0, 0, 0, 0.5);
            border-radius: 4px !important;
        }

        body {
            background: #0f0f13;
            color: #d1d5db;
        }

        /* SECURITY: Map container */
        #map {
            height: 600px;
            width: 100%;
            border-radius: 12px;
            border: 2px solid rgba(20, 184, 166, 0.3);
        }

        /* SECURITY: Custom marker styles */
        .marker-suspicious {
            background-color: #ef4444;
            border: 2px solid #dc2626;
        }

        .marker-normal {
            background-color: #10b981;
            border: 2px solid #059669;
        }

        .marker-school {
            background-color: #3b82f6;
            border: 2px solid #2563eb;
        }
    </style>
</head>

<body class="min-h-screen text-white">
    <!-- Side Navigation -->
    <aside id="sidebar" class="fixed top-0 left-0 h-screen w-64 glass-effect border-r border-teal-500/20 z-50">
        <div class="flex items-center justify-between p-6 border-b border-teal-500/20">
            <div class="flex items-center gap-3">
                <img src="../../assets/default/smkn1sanden.png" alt="SMKN 1 Sanden" class="h-10 w-auto">
                <div>
                    <h1 class="font-semibold">SMKN 1 Sanden</h1>
                    <p class="text-xs text-gray-400">Sistem Absensi</p>
                </div>
            </div>
        </div>

        <nav class="p-4 space-y-2">
            <a href="../dashboard/" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-400/10 transition-colors">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="../absensi/" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-400/10 transition-colors">
                <i class="fas fa-calendar-check"></i>
                <span>Absensi</span>
            </a>
            <a href="map.php" class="flex items-center gap-3 text-white/90 p-3 rounded-lg bg-teal-500/10 border-l-3 border-teal-500">
                <i class="fas fa-map-marked-alt text-teal-400"></i>
                <span>Peta Lokasi</span>
            </a>
            <a href="../siswa/" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-400/10 transition-colors">
                <i class="fas fa-users"></i>
                <span>Data Siswa</span>
            </a>
            <a href="../laporan/" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-400/10 transition-colors">
                <i class="fas fa-file-alt"></i>
                <span>Laporan</span>
            </a>

            <hr class="border-gray-700/50 my-4">

            <!-- Theme Toggle -->
            <button id="theme-toggle-btn" onclick="toggleTheme()" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-teal-400/10 transition-colors w-full text-left">
                <i class="fas fa-sun text-yellow-400"></i>
                <span>Mode Terang</span>
            </button>

            <a href="../logout.php" class="flex items-center gap-3 text-gray-400 p-3 rounded-lg hover:bg-red-500/10 hover:text-red-500 transition-colors">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 min-h-screen p-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <header class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold flex items-center gap-2">
                            <i class="fas fa-map-marked-alt text-teal-400"></i>
                            Peta Lokasi Absensi
                        </h1>
                        <p class="text-gray-400 mt-1">Tracking lokasi GPS siswa saat absensi</p>
                    </div>
                    
                    <!-- Date Filter -->
                    <div class="flex items-center gap-3">
                        <form method="GET" class="flex items-center gap-2">
                            <input type="date" name="date" value="<?= $filter_date ?>" 
                                   class="px-4 py-2 rounded-lg glass-effect text-white border border-teal-500/30 focus:border-teal-500 focus:outline-none">
                            <button type="submit" class="px-4 py-2 bg-teal-500 hover:bg-teal-600 rounded-lg transition-colors">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Stats -->
            <div class="grid grid-cols-4 gap-6 mb-8">
                <div class="glass-effect rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Total Absensi</p>
                            <h3 class="text-2xl font-bold text-white"><?= count($attendance_data) ?></h3>
                        </div>
                        <div class="h-12 w-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                            <i class="fas fa-map-marker-alt text-blue-400 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="glass-effect rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Normal</p>
                            <h3 class="text-2xl font-bold text-green-400">
                                <?= count(array_filter($attendance_data, fn($a) => !$a['is_suspicious'])) ?>
                            </h3>
                        </div>
                        <div class="h-12 w-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-400 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="glass-effect rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Mencurigakan</p>
                            <h3 class="text-2xl font-bold text-red-400">
                                <?= count(array_filter($attendance_data, fn($a) => $a['is_suspicious'])) ?>
                            </h3>
                        </div>
                        <div class="h-12 w-12 rounded-xl bg-red-500/20 flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="glass-effect rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Mock GPS</p>
                            <h3 class="text-2xl font-bold text-yellow-400">
                                <?= count(array_filter($attendance_data, fn($a) => $a['mock_detected'])) ?>
                            </h3>
                        </div>
                        <div class="h-12 w-12 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                            <i class="fas fa-satellite-dish text-yellow-400 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div class="glass-effect rounded-xl p-6 mb-8">
                <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                    <i class="fas fa-globe text-teal-400"></i>
                    Peta Lokasi
                </h3>
                <div id="map"></div>
            </div>

            <!-- Legend -->
            <div class="glass-effect rounded-xl p-6 mb-8">
                <h3 class="font-semibold text-lg mb-4">Keterangan</h3>
                <div class="grid grid-cols-4 gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full bg-blue-500"></div>
                        <span class="text-sm">Lokasi Sekolah</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full bg-green-500"></div>
                        <span class="text-sm">Absensi Normal</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full bg-red-500"></div>
                        <span class="text-sm">Mencurigakan</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full bg-yellow-500"></div>
                        <span class="text-sm">Mock GPS</span>
                    </div>
                </div>
            </div>

            <!-- Attendance List -->
            <div class="glass-effect rounded-xl p-6">
                <h3 class="font-semibold text-lg mb-4">Detail Absensi</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-teal-500/20">
                                <th class="text-left p-3">Siswa</th>
                                <th class="text-left p-3">Status</th>
                                <th class="text-left p-3">Waktu</th>
                                <th class="text-left p-3">Koordinat</th>
                                <th class="text-left p-3">Device</th>
                                <th class="text-left p-3">Validasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance_data as $data): ?>
                                <tr class="border-b border-teal-500/10 hover:bg-teal-500/5">
                                    <td class="p-3">
                                        <div class="flex items-center gap-2">
                                            <img src="../../<?= $data['foto_profil'] ?>" class="h-8 w-8 rounded-full object-cover" alt="">
                                            <div>
                                                <p class="font-medium"><?= htmlspecialchars($data['nama_lengkap']) ?></p>
                                                <p class="text-xs text-gray-400"><?= $data['kelas'] ?> <?= $data['jurusan'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 rounded-full text-xs <?php
                                            echo match($data['status']) {
                                                'Hadir' => 'bg-green-500/10 text-green-500',
                                                'Sakit' => 'bg-yellow-500/10 text-yellow-500',
                                                'Izin' => 'bg-blue-500/10 text-blue-500',
                                                'Terlambat' => 'bg-orange-500/10 text-orange-500',
                                                default => 'bg-gray-500/10 text-gray-500'
                                            };
                                        ?>">
                                            <?= $data['status'] ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm">
                                        <?= date('H:i', strtotime($data['created_at'])) ?>
                                    </td>
                                    <td class="p-3 text-xs font-mono">
                                        <?= number_format($data['latitude'], 6) ?>,<br>
                                        <?= number_format($data['longitude'], 6) ?>
                                    </td>
                                    <td class="p-3 text-sm">
                                        <?= $data['device_type'] ?? '-' ?><br>
                                        <span class="text-xs text-gray-400"><?= $data['network_type'] ?? '-' ?></span>
                                    </td>
                                    <td class="p-3">
                                        <?php if ($data['is_suspicious']): ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-red-500/10 text-red-500">
                                                <i class="fas fa-exclamation-triangle"></i> Suspicious
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($data['mock_detected']): ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-yellow-500/10 text-yellow-500">
                                                <i class="fas fa-satellite-dish"></i> Mock GPS
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!$data['is_suspicious'] && !$data['mock_detected']): ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-green-500/10 text-green-500">
                                                <i class="fas fa-check"></i> Normal
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- SECURITY: Initialize Leaflet Map -->
    <script>
        // SECURITY: Map initialization
        const map = L.map('map').setView([<?= $schoolLat ?>, <?= $schoolLon ?>], 16);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // SECURITY: Add school location marker
        const schoolIcon = L.divIcon({
            className: 'custom-marker',
            html: '<div style="background-color: #3b82f6; width: 30px; height: 30px; border-radius: 50%; border: 3px solid #2563eb; display: flex; align-items: center; justify-content: center;"><i class="fas fa-school" style="color: white; font-size: 14px;"></i></div>',
            iconSize: [30, 30]
        });

        L.marker([<?= $schoolLat ?>, <?= $schoolLon ?>], { icon: schoolIcon })
            .addTo(map)
            .bindPopup('<b>SMKN 1 Sanden</b><br>Lokasi Sekolah');

        // SECURITY: Add radius circle
        L.circle([<?= $schoolLat ?>, <?= $schoolLon ?>], {
            color: '#14b8a6',
            fillColor: '#14b8a6',
            fillOpacity: 0.1,
            radius: <?= $maxDistance ?>
        }).addTo(map);

        // SECURITY: Add attendance markers
        const attendanceData = <?= json_encode($attendance_data) ?>;

        attendanceData.forEach(data => {
            const isSuspicious = data.is_suspicious == 1;
            const isMock = data.mock_detected == 1;
            
            let markerColor = '#10b981'; // green
            let iconClass = 'fa-check';
            
            if (isSuspicious) {
                markerColor = '#ef4444'; // red
                iconClass = 'fa-exclamation-triangle';
            } else if (isMock) {
                markerColor = '#eab308'; // yellow
                iconClass = 'fa-satellite-dish';
            }

            const customIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="background-color: ${markerColor}; width: 24px; height: 24px; border-radius: 50%; border: 2px solid white; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"><i class="fas ${iconClass}" style="color: white; font-size: 10px;"></i></div>`,
                iconSize: [24, 24]
            });

            const popupContent = `
                <div style="min-width: 200px;">
                    <h3 style="font-weight: bold; margin-bottom: 8px;">${data.nama_lengkap}</h3>
                    <p style="font-size: 12px; color: #666; margin-bottom: 4px;">${data.kelas} ${data.jurusan}</p>
                    <p style="font-size: 12px; margin-bottom: 4px;"><strong>Status:</strong> ${data.status}</p>
                    <p style="font-size: 12px; margin-bottom: 4px;"><strong>Waktu:</strong> ${new Date(data.created_at).toLocaleTimeString('id-ID')}</p>
                    <p style="font-size: 12px; margin-bottom: 4px;"><strong>Device:</strong> ${data.device_type || '-'}</p>
                    <p style="font-size: 12px; margin-bottom: 4px;"><strong>Network:</strong> ${data.network_type || '-'}</p>
                    <p style="font-size: 12px; margin-bottom: 4px;"><strong>Accuracy:</strong> ${data.location_accuracy ? data.location_accuracy.toFixed(2) + 'm' : '-'}</p>
                    ${isSuspicious ? '<p style="font-size: 12px; color: #ef4444; margin-top: 8px;"><i class="fas fa-exclamation-triangle"></i> Mencurigakan</p>' : ''}
                    ${isMock ? '<p style="font-size: 12px; color: #eab308; margin-top: 8px;"><i class="fas fa-satellite-dish"></i> Mock GPS Detected</p>' : ''}
                    ${data.validation_notes ? `<p style="font-size: 11px; color: #999; margin-top: 8px; padding-top: 8px; border-top: 1px solid #eee;">${data.validation_notes}</p>` : ''}
                </div>
            `;

            L.marker([parseFloat(data.latitude), parseFloat(data.longitude)], { icon: customIcon })
                .addTo(map)
                .bindPopup(popupContent);
        });
    </script>

    <!-- Theme Toggle Handler -->
    <script>
        window.toggleTheme = function() {
            const html = document.documentElement;
            const isLight = html.classList.toggle('light-theme');
            localStorage.setItem('app-theme', isLight ? 'light' : 'dark');
            
            const btn = document.getElementById('theme-toggle-btn');
            const icon = btn.querySelector('i');
            const text = btn.querySelector('span');
            
            if (isLight) {
                icon.className = 'fas fa-moon';
                text.textContent = 'Mode Gelap';
            } else {
                icon.className = 'fas fa-sun text-yellow-400';
                text.textContent = 'Mode Terang';
            }
        };
    </script>

    <script src="../../assets/js/theme.js"></script>
</body>
</html>
