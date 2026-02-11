// Admin dashboard - check authentication first
function checkAdminAuth() {
  const user = JSON.parse(localStorage.getItem('currentUser'));
  if (!user || user.role !== 'admin') {
    alert('Admin access required. Redirecting to login...');
    window.location.href = 'index.html';
    return false;
  }
  return true;
}

async function renderMetrics() {
  try {
    const resp = await fetch('orders.php?action=list');
    const orders = await resp.json();

    const respUsers = await fetch('auth.php?action=list_users'); // Wait, I need to add list_users to auth.php or similar
    // Actually, I'll just fetch orders and calculate from there for now, 
    // and maybe add a generic stats endpoint later.

    const totalOrders = orders.length;
    const revenue = orders.reduce((s, o) => s + parseFloat(o.total_price), 0).toFixed(2);

    document.getElementById('metric-orders').textContent = totalOrders;
    document.getElementById('metric-revenue').textContent = `TSh ${revenue}`;

    renderUsers();
  } catch (e) {
    console.error('Failed to render metrics', e);
  }
}

async function renderUsers() {
  try {
    const resp = await fetch('auth.php?action=list_users');
    const users = await resp.json();
    const tbody = document.querySelector('#users-table tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    users.forEach(u => {
      const tr = document.createElement('tr');
      const role = u.role || 'user';
      tr.innerHTML = `<td>${u.username}</td><td><span class="role-badge ${role}">${role}</span></td><td><button class="action-btn" data-user="${u.username}">Remove</button></td>`;
      tbody.appendChild(tr);
    });
    document.getElementById('metric-users').textContent = users.length;
  } catch (e) {
    console.error('Failed to render users', e);
  }
}

async function renderOrders() {
  try {
    const resp = await fetch('orders.php?action=list');
    const orders = await resp.json();

    const recentTbody = document.querySelector('#orders-table tbody');
    const allTbody = document.querySelector('#all-orders-table tbody');
    recentTbody.innerHTML = '';
    allTbody.innerHTML = '';

    orders.forEach((o, index) => {
      const tr = document.createElement('tr');
      const content = `<td>${o.id}</td><td>${o.username || 'Guest'}</td><td>-</td><td>TSh ${parseFloat(o.total_price).toFixed(2)}</td><td><span class="status ${o.status.toLowerCase().replace(/\s/g, '-')}">${o.status}</span></td>`;
      tr.innerHTML = content;

      if (index < 5) recentTbody.appendChild(tr.cloneNode(true));

      const trAll = document.createElement('tr');
      trAll.innerHTML = content + `<td>${o.created_at}</td>`;
      allTbody.appendChild(trAll);
    });
  } catch (e) {
    console.error('Failed to render orders', e);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  if (!checkAdminAuth()) return;

  renderMetrics();
  renderOrders();

  // Sidebar navigation
  document.querySelectorAll('.admin-sidebar li').forEach(li => {
    li.addEventListener('click', () => {
      const section = li.dataset.section;
      switchSection(section);
    });
  });

  // Settings form remains localStorage for now as it's store-wide config
  const settingsForm = document.getElementById('settings-form');
  if (settingsForm) {
    settingsForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const settings = {
        storeName: document.getElementById('store-name').value,
        deliveryFee: document.getElementById('delivery-fee').value,
        openingHours: document.getElementById('opening-hours').value,
        closingHours: document.getElementById('closing-hours').value,
        savedAt: new Date().toLocaleString()
      };
      localStorage.setItem('storeSettings', JSON.stringify(settings));
      const msg = document.getElementById('settings-msg');
      msg.textContent = '✓ Settings saved successfully!';
      msg.style.color = '#2e7d32';
      setTimeout(() => msg.textContent = '', 3000);
    });
  }

  // Logout
  const logoutBtn = document.getElementById('admin-logout');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
      localStorage.removeItem('currentUser');
      window.location.href = 'index.html';
    });
  }
});

function switchSection(sectionId) {
  document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
  document.getElementById(sectionId).classList.add('active');

  document.querySelectorAll('.admin-sidebar li').forEach(li => li.classList.remove('active'));
  document.querySelector(`[data-section="${sectionId}"]`).classList.add('active');
}
