<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quán cà phê gần tôi</title>

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: #1f2937;
            background: #f3f4f6;
        }

        header {
            padding: 20px 24px;
            color: white;
            background: #166534;
        }

        header h1 { margin: 0 0 6px; }
        header p { margin: 0; }

        main {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 16px;
            padding: 16px;
            min-height: calc(100vh - 100px);
        }

        .panel, #map {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .panel { padding: 16px; }

        .controls {
            display: grid;
            gap: 10px;
            margin-bottom: 14px;
        }

        input, button {
            width: 100%;
            padding: 11px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
        }

        button {
            border: 0;
            color: white;
            background: #15803d;
            cursor: pointer;
        }

        button:hover { background: #166534; }

        #status {
            min-height: 22px;
            color: #4b5563;
            font-size: 14px;
        }

        .cafe {
            padding: 12px 0;
            border-top: 1px solid #e5e7eb;
        }

        .cafe h3 { margin: 0 0 5px; }
        .cafe p { margin: 4px 0; font-size: 14px; }

        #map { min-height: 600px; }

        @media (max-width: 760px) {
            main { grid-template-columns: 1fr; }
            #map { min-height: 420px; }
        }
    </style>
</head>
<body>
<header>
    <h1>☕ Quán cà phê gần tôi</h1>
    <p>Tìm địa điểm quanh bạn bằng dữ liệu không gian PostGIS.</p>
</header>

<main>
    <section class="panel">
        <div class="controls">
            <button id="locateButton">Dùng vị trí hiện tại</button>

            <label for="radius">Bán kính tìm kiếm</label>
            <select id="radius">
                <option value="500">500 mét</option>
                <option value="1000" selected>1.000 mét</option>
                <option value="2000">2.000 mét</option>
                <option value="5000">5.000 mét</option>
            </select>

            <button id="searchButton">Tìm quán gần đây</button>
        </div>

        <div id="status">Đang tải bản đồ...</div>
        <div id="results"></div>
    </section>

    <div id="map"></div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const defaultPosition = [21.0285, 105.8542];

    const map = L.map('map').setView(defaultPosition, 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const markers = L.layerGroup().addTo(map);
    let currentPosition = defaultPosition;

    const statusBox = document.getElementById('status');
    const resultsBox = document.getElementById('results');

    async function searchNearby() {
        const radius = document.getElementById('radius').value;
        const [lat, lng] = currentPosition;

        statusBox.textContent = 'Đang tìm quán gần bạn...';
        resultsBox.innerHTML = '';
        markers.clearLayers();

        const params = new URLSearchParams({
            lat: lat,
            lng: lng,
            radius: radius
        });

        try {
            const response = await fetch(
                `{{ route('cafes.nearby') }}?${params.toString()}`
            );

            if (!response.ok) {
                throw new Error('Không thể tải dữ liệu quán.');
            }

            const data = await response.json();

            L.marker(currentPosition)
                .addTo(markers)
                .bindPopup('Vị trí tìm kiếm của bạn');

            if (data.cafes.length === 0) {
                statusBox.textContent =
                    `Không tìm thấy quán trong bán kính ${radius} m.`;
                return;
            }

            statusBox.textContent =
                `Tìm thấy ${data.count} quán trong bán kính ${radius} m.`;

            data.cafes.forEach(cafe => {
                const position = [Number(cafe.latitude), Number(cafe.longitude)];

                L.marker(position)
                    .addTo(markers)
                    .bindPopup(`<strong>${escapeHtml(cafe.name)}</strong><br>
                                ${escapeHtml(cafe.address)}<br>
                                Cách bạn ${cafe.distance_meters} m`);

                const item = document.createElement('article');
                item.className = 'cafe';
                item.innerHTML = `
                    <h3>${escapeHtml(cafe.name)}</h3>
                    <p>${escapeHtml(cafe.address)}</p>
                    <p><strong>${cafe.distance_meters} m</strong> từ vị trí tìm kiếm</p>
                `;

                resultsBox.appendChild(item);
            });
        } catch (error) {
            statusBox.textContent = error.message;
        }
    }

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value ?? '';
        return element.innerHTML;
    }

    document.getElementById('searchButton')
        .addEventListener('click', searchNearby);

    document.getElementById('radius')
        .addEventListener('change', searchNearby);

    document.getElementById('locateButton').addEventListener('click', () => {
        if (!navigator.geolocation) {
            statusBox.textContent = 'Trình duyệt không hỗ trợ định vị.';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            position => {
                currentPosition = [
                    position.coords.latitude,
                    position.coords.longitude
                ];

                map.setView(currentPosition, 15);
                searchNearby();
            },
            () => {
                statusBox.textContent =
                    'Không lấy được vị trí. Hãy cấp quyền định vị hoặc dùng vị trí mặc định.';
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });

    searchNearby();
</script>
</body>
</html>
