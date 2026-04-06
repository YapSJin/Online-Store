document.addEventListener('DOMContentLoaded', function() {
    // 1. 初始化地图 - 设置中心点为 TAR UMT Setapak 附近
    // 坐标: [3.2147, 101.7300]
    var map = L.map('leafletMap').setView([3.2147, 101.7300], 15);

    // 2. 加载免费地图瓦片
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // 3. 门店数据 - 只保留 TAR UMT 一个点
    const stores = [
        {
            name: "TAR UMT SETAPAK STORE",
            address: "Jalan Genting Kelang, Setapak, 53300 Kuala Lumpur",
            lat: 3.2147,
            lng: 101.7300,
            phone: "+603-4145 0123"
        }
    ];

    const storeList = document.getElementById('store-list');

    stores.forEach((store) => {
        // A. 添加地图标记 (Marker)
        var marker = L.marker([store.lat, store.lng]).addTo(map);
        marker.bindPopup(`<b>${store.name}</b><br>${store.address}`).openPopup();

        // B. 创建左侧列表 UI
        const item = document.createElement('div');
        item.className = 'store-item active'; // 默认设为 active
        item.innerHTML = `
            <h3>${store.name}</h3>
            <p><i class="fas fa-map-marker-alt"></i> ${store.address}</p>
            <p><i class="fas fa-phone"></i> ${store.phone}</p>
        `;

        // C. 点击列表联动地图
        item.onclick = function() {
            map.flyTo([store.lat, store.lng], 16, {
                animate: true,
                duration: 1.5
            });
            marker.openPopup();
        };

        storeList.appendChild(item);
    });
});