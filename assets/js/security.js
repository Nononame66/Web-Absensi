// ============================================================
// SECURITY: Frontend Security Detection & GPS Validation
// ============================================================

(function() {
    'use strict';

    // SECURITY: Mock GPS Detection
    window.SecurityCheck = {
        // SECURITY: Detect if mock location is enabled
        detectMockGPS: function(position) {
            let isMock = false;
            
            // SECURITY: Check if position has mock flag (Android)
            if (position.coords && position.coords.mocked !== undefined) {
                isMock = position.coords.mocked;
            }
            
            // SECURITY: Check accuracy (very high accuracy might indicate fake GPS)
            if (position.coords && position.coords.accuracy < 5) {
                console.warn('[SECURITY] Suspiciously high accuracy detected');
            }
            
            // SECURITY: Check if altitude is exactly 0 (common in fake GPS)
            if (position.coords && position.coords.altitude === 0 && position.coords.altitudeAccuracy === null) {
                console.warn('[SECURITY] Suspicious altitude data');
            }
            
            return isMock;
        },

        // SECURITY: Get network type
        getNetworkType: function() {
            if (navigator.connection) {
                const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                return conn.effectiveType || conn.type || 'unknown';
            }
            return 'unknown';
        },

        // SECURITY: Get device info
        getDeviceInfo: function() {
            const ua = navigator.userAgent;
            let deviceType = 'Desktop';
            
            if (/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i.test(ua)) {
                deviceType = 'Mobile';
            } else if (/tablet|ipad/i.test(ua)) {
                deviceType = 'Tablet';
            }
            
            return {
                type: deviceType,
                userAgent: ua
            };
        },

        // SECURITY: Request GPS location with validation
        getLocation: function(callback, errorCallback) {
            if (!navigator.geolocation) {
                errorCallback('Geolocation tidak didukung oleh browser Anda');
                return;
            }

            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // SECURITY: Detect mock GPS
                    const mockDetected = SecurityCheck.detectMockGPS(position);
                    
                    const locationData = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        altitude: position.coords.altitude,
                        altitudeAccuracy: position.coords.altitudeAccuracy,
                        heading: position.coords.heading,
                        speed: position.coords.speed,
                        timestamp: position.timestamp,
                        mockDetected: mockDetected
                    };

                    if (mockDetected) {
                        console.warn('[SECURITY] Mock GPS detected!');
                    }

                    callback(locationData);
                },
                function(error) {
                    let errorMsg = 'Gagal mendapatkan lokasi';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg = 'Izin lokasi ditolak. Mohon aktifkan GPS dan izinkan akses lokasi.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg = 'Informasi lokasi tidak tersedia.';
                            break;
                        case error.TIMEOUT:
                            errorMsg = 'Waktu permintaan lokasi habis.';
                            break;
                    }
                    errorCallback(errorMsg);
                },
                options
            );
        }
    };

    // SECURITY: Auto-attach to attendance form if exists
    document.addEventListener('DOMContentLoaded', function() {
        const attendanceForm = document.getElementById('attendance-form');
        
        if (attendanceForm) {
            console.log('[SECURITY] Attendance form detected, initializing security checks');
            
            // SECURITY: Add hidden fields for security data
            const hiddenFields = [
                { name: 'latitude', value: '' },
                { name: 'longitude', value: '' },
                { name: 'accuracy', value: '' },
                { name: 'mock_detected', value: 'false' },
                { name: 'network_info', value: SecurityCheck.getNetworkType() }
            ];

            hiddenFields.forEach(field => {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = field.name;
                input.id = field.name;
                input.value = field.value;
                attendanceForm.appendChild(input);
            });

            // SECURITY: Get location on form submit
            attendanceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = attendanceForm.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : '';
                
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengambil lokasi...';
                }

                // SECURITY: Get GPS location
                SecurityCheck.getLocation(
                    function(locationData) {
                        // SECURITY: Fill hidden fields
                        document.getElementById('latitude').value = locationData.latitude;
                        document.getElementById('longitude').value = locationData.longitude;
                        document.getElementById('accuracy').value = locationData.accuracy;
                        document.getElementById('mock_detected').value = locationData.mockDetected;
                        document.getElementById('network_info').value = SecurityCheck.getNetworkType();

                        // SECURITY: Show warning if mock GPS detected
                        if (locationData.mockDetected) {
                            const warning = document.createElement('div');
                            warning.className = 'bg-yellow-500/10 border border-yellow-500/30 text-yellow-500 p-3 rounded-lg mb-4';
                            warning.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Terdeteksi fake GPS. Absensi akan ditandai sebagai mencurigakan.';
                            attendanceForm.insertBefore(warning, attendanceForm.firstChild);
                        }

                        console.log('[SECURITY] Location data collected:', locationData);

                        // Submit form
                        attendanceForm.submit();
                    },
                    function(error) {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                        
                        alert('❌ ' + error + '\n\nAbsensi memerlukan akses lokasi GPS.');
                    }
                );
            });
        }
    });
})();
