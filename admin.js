// Premium Admin Dashboard Logic - Creative Revamp
let currentProducts = [];

async function checkAdminAuth() {
  try {
    const rawResp = await fetch('auth.php?action=verify');
    const text = await rawResp.text();
    console.log('Auth Verify Response:', text); // DEBUG
    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      console.error('JSON Parse Error:', e);
      alert('Server error: Invalid JSON response. Check console.');
      return false;
    }

    if (!data.authenticated || data.user.role !== 'admin') {
      console.warn('Redirecting to login: Not authenticated or not admin', data);
      window.location.href = 'index.html';
      return false;
    }

    const userInfo = document.getElementById('admin-user-info');
    if (userInfo) {
      const span = userInfo.querySelector('span');
      if (span) span.textContent = data.user.username;
    } else {
      console.warn('DOM Element "admin-user-info" not found. User is logged in as:', data.user.username);
    }

    localStorage.setItem('currentUser', JSON.stringify(data.user));
    return true;
  } catch (e) {
    console.error('Auth verification failed', e);
    // Only redirect if it's a network/fetch error, not a DOM error
    if (e.message.includes('NetworkError') || e.message.includes('fetch')) {
      alert('Network error during auth check. Redirecting...');
      window.location.href = 'index.html';
    } else {
      console.error('Non-critical auth error (staying on page):', e);
    }
    return false;
  }
}

// Data Fetching & Rendering
async function refreshDashboard() {
  await Promise.all([
    renderMetrics(),
    renderOrders(),
    renderProducts(),
    renderUsers()
  ]);
  addActivity('Dashboard Refreshed', 'All metrics and data have been updated.');
}

async function renderMetrics() {
  try {
    const [ordersResp, productsResp, usersResp] = await Promise.all([
      fetch('orders.php?action=list'),
      fetch('menu.php'),
      fetch('auth.php?action=list_users')
    ]);

    const orders = await ordersResp.json();
    const products = await productsResp.json();
    const users = await usersResp.json();

    const revenue = orders.reduce((s, o) => s + (parseFloat(o.total_price) || 0), 0);

    document.getElementById('metric-orders').textContent = orders.length;
    document.getElementById('metric-revenue').textContent = `TSh ${Math.round(revenue).toLocaleString()}`;
    document.getElementById('metric-users').textContent = users.length;
    document.getElementById('metric-products').textContent = products.length;
  } catch (e) {
    console.error('Failed to load metrics', e);
  }
}

async function renderOrders() {
  try {
    const resp = await fetch('orders.php?action=list');
    const orders = await resp.json();

    const recentTbody = document.querySelector('#orders-table tbody');
    const allTbody = document.querySelector('#all-orders-table tbody');
    if (!recentTbody || !allTbody) return;
    recentTbody.innerHTML = '';
    allTbody.innerHTML = '';

    orders.forEach((o, index) => {
      const row = `
        <td>#${o.id}</td>
        <td>${o.username || 'Guest'}</td>
        <td>TSh ${Math.round(o.total_price).toLocaleString()}</td>
        <td><span class="status-badge ${o.status.toLowerCase().replace(/\s/g, '-')}">${o.status}</span></td>
        <td>
          <select class="status-select" data-id="${o.id}">
            <option value="Pending" ${o.status === 'Pending' ? 'selected' : ''}>Pending</option>
            <option value="Preparing" ${o.status === 'Preparing' ? 'selected' : ''}>Preparing</option>
            <option value="Out for delivery" ${o.status === 'Out for delivery' ? 'selected' : ''}>Out for delivery</option>
            <option value="Delivered" ${o.status === 'Delivered' ? 'selected' : ''}>Delivered</option>
            <option value="Cancelled" ${o.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
          </select>
        </td>
      `;

      if (index < 5) {
        const tr = document.createElement('tr');
        tr.innerHTML = row;
        recentTbody.appendChild(tr);
      }

      const trAll = document.createElement('tr');
      trAll.innerHTML = `<td>#${o.id}</td><td>${o.username || 'Guest'}</td><td>TSh ${Math.round(o.total_price).toLocaleString()}</td><td><span class="status-badge ${o.status.toLowerCase().replace(/\s/g, '-')}">${o.status}</span></td><td>${new Date(o.created_at).toLocaleDateString()}</td><td><select class="status-select" data-id="${o.id}"><option value="Pending" ${o.status === 'Pending' ? 'selected' : ''}>Pending</option><option value="Delivered" ${o.status === 'Delivered' ? 'selected' : ''}>Delivered</option></select></td>`;
      allTbody.appendChild(trAll);
    });

    document.querySelectorAll('.status-select').forEach(sel => {
      sel.onchange = async (e) => await updateOrderStatus(e.target.dataset.id, e.target.value);
    });
  } catch (e) {
    console.error('Failed to render orders', e);
  }
}

async function renderProducts() {
  try {
    const resp = await fetch('menu.php');
    currentProducts = await resp.json();
    const tbody = document.querySelector('#products-table tbody');
    tbody.innerHTML = '';

    currentProducts.forEach(p => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><div style="width:40px;height:40px;border-radius:8px;background:url('${p.image_url}') center/cover"></div></td>
        <td>${p.name}</td>
        <td>${p.category_name}</td>
        <td>TSh ${p.price}</td>
        <td>${p.is_available == 1 ? '✅' : '❌'}</td>
        <td style="display:flex; gap:0.5rem;">
          <button class="action-btn" onclick="editProduct(${p.id})">Edit</button>
          <button class="action-btn delete" onclick="deleteProduct(${p.id})">Del</button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  } catch (e) {
    console.error('Failed to render products', e);
  }
}

async function renderUsers() {
  try {
    const resp = await fetch('auth.php?action=list_users');
    const users = await resp.json();
    const tbody = document.querySelector('#users-table tbody');
    tbody.innerHTML = '';

    users.forEach(u => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${u.username}</td>
        <td>${u.role}</td>
        <td>${new Date(u.created_at).toLocaleDateString()}</td>
        <td><button class="action-btn delete" onclick="alert('Disabled')">Ban</button></td>
      `;
      tbody.appendChild(tr);
    });
  } catch (e) {
    console.error('Failed to render users', e);
  }
}

async function updateOrderStatus(id, status) {
  try {
    const resp = await fetch('orders.php?action=update_status', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, status })
    });
    const res = await resp.json();
    if (res.success) {
      showToast('Order Update Success');
      addActivity('Order Update', `Order #${id} changed to ${status}`);
      refreshDashboard();
    }
  } catch (e) { console.error(e); }
}

async function deleteProduct(id) {
  if (!confirm('Confirm delete?')) return;
  try {
    const resp = await fetch('menu.php?action=delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    if ((await resp.json()).success) {
      showToast('Product Removed');
      addActivity('Menu Change', `Product ID ${id} deleted.`);
      renderProducts();
      renderMetrics();
    }
  } catch (e) { console.error(e); }
}

function editProduct(id) {
  const p = currentProducts.find(x => x.id == id);
  if (!p) return;
  document.getElementById('modal-title').textContent = 'Modify Item';
  document.getElementById('product-id').value = p.id;
  document.getElementById('prod-name').value = p.name;
  document.getElementById('prod-category').value = p.category_id;
  document.getElementById('prod-price').value = p.price;
  document.getElementById('prod-image').value = p.image_url;
  document.getElementById('prod-discount').value = p.discount;
  document.getElementById('product-modal').style.display = 'flex';
}

function addActivity(title, desc) {
  const list = document.getElementById('activity-list');
  const li = document.createElement('li');
  li.style.cssText = 'margin-bottom: 1rem; border-left: 2px solid var(--primary); padding-left: 1rem; animation: fadeIn 0.3s ease;';
  li.innerHTML = `<strong>${title}</strong><br><small>${desc}</small>`;
  list.prepend(li);
  if (list.children.length > 8) list.lastElementChild.remove();
}

document.addEventListener('DOMContentLoaded', async () => {
  if (!await checkAdminAuth()) return;
  refreshDashboard();

  document.querySelectorAll('.admin-sidebar li').forEach(li => {
    li.onclick = () => {
      document.querySelectorAll('.admin-sidebar li').forEach(l => l.classList.remove('active'));
      li.classList.add('active');
      const section = li.dataset.section;
      document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
      document.getElementById(section).classList.add('active');
      addActivity('Navigation', `Switched to ${section} view.`);
    };
  });

  document.getElementById('open-add-product').onclick = () => {
    document.getElementById('modal-title').textContent = 'Add New Item';
    document.getElementById('product-form').reset();
    document.getElementById('product-id').value = '';
    document.getElementById('product-modal').style.display = 'flex';
  };

  document.getElementById('product-form').onsubmit = async (e) => {
    e.preventDefault();
    const id = document.getElementById('product-id').value;
    const action = id ? 'edit' : 'add';
    const payload = {
      id: id || null,
      name: document.getElementById('prod-name').value,
      category_id: document.getElementById('prod-category').value,
      price: document.getElementById('prod-price').value,
      image_url: document.getElementById('prod-image').value,
      discount: document.getElementById('prod-discount').value,
      is_available: 1
    };

    try {
      const resp = await fetch(`menu.php?action=${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      if ((await resp.json()).success) {
        showToast('Save Complete');
        addActivity('Menu Change', `Product "${payload.name}" was ${id ? 'updated' : 'added'}.`);
        document.getElementById('product-modal').style.display = 'none';
        renderProducts();
        renderMetrics();
      }
    } catch (e) { console.error(e); }
  };

  document.getElementById('admin-logout').onclick = async () => {
    await fetch('auth.php?action=logout');
    localStorage.removeItem('currentUser');
    window.location.href = 'index.html';
  };
});

function showToast(msg) {
  const t = document.createElement('div');
  t.className = 'toast';
  t.style.cssText = 'position:fixed;bottom:20px;right:20px;background:var(--primary);color:white;padding:1rem 2rem;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,0.3);z-index:10000;animation:slideIn 0.3s ease-out;';
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => {
    t.style.opacity = '0';
    t.style.transform = 'translateX(100%)';
    t.style.transition = 'all 0.3s ease';
    setTimeout(() => t.remove(), 300);
  }, 3000);
}

const style = document.createElement('style');
style.innerHTML = `@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } } @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }`;
document.head.appendChild(style);
