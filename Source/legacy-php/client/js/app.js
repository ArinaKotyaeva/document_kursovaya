const API_BASE_URL = 'http://localhost/kursovaya/server';
const STORAGE_KEY = 'cachedUsers';

let currentRole = 'admin';
let users = [];

const roleInputs = document.querySelectorAll('input[name="role"]');
const userForm = document.getElementById('user-form');
const addBtn = document.getElementById('add-btn');
const exportBtn = document.getElementById('export-btn');
const errorMessage = document.getElementById('error-message');
const infoMessage = document.getElementById('info-message');
const tableBody = document.getElementById('users-table-body');
const actionsHeader = document.getElementById('actions-header');
const adminFormPanel = document.getElementById('admin-form-panel');

const deleteIcon = `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 3h6l1 2h5v2H3V5h5l1-2zm1 6h2v10h-2V9zm4 0h2v10h-2V9zM7 9h2v10H7V9z"/></svg>`;

function showError(text) {
  errorMessage.textContent = text;
  errorMessage.classList.add('visible');
}

function hideError() {
  errorMessage.textContent = '';
  errorMessage.classList.remove('visible');
}

function showInfo(text) {
  infoMessage.textContent = text;
  infoMessage.classList.add('visible');
}

function hideInfo() {
  infoMessage.textContent = '';
  infoMessage.classList.remove('visible');
}

function switchRole(role) {
  currentRole = role;
  const isGuest = role === 'guest';

  if (adminFormPanel) {
    adminFormPanel.hidden = isGuest;
  }

  if (actionsHeader) {
    actionsHeader.style.display = isGuest ? 'none' : '';
  }

  if (!isGuest) {
    hideError();
  }

  renderTable(users);
}

function renderTable(list) {
  tableBody.innerHTML = '';
  const isAdmin = currentRole === 'admin';
  const colCount = isAdmin ? 5 : 4;

  if (!list.length) {
    const row = document.createElement('tr');
    row.innerHTML = `<td colspan="${colCount}" class="empty-row">Записей пока нет</td>`;
    tableBody.appendChild(row);
    return;
  }

  list.forEach((user) => {
    const row = document.createElement('tr');
    const deleteCell = isAdmin
      ? `<td class="col-actions">
          <button type="button" class="btn-delete" data-id="${user.id}" title="Удалить" aria-label="Удалить запись">
            ${deleteIcon}
          </button>
        </td>`
      : '';

    row.innerHTML = `
      <td>${user.id}</td>
      <td>${escapeHtml(user.name)}</td>
      <td>${escapeHtml(user.email)}</td>
      <td>${user.age ?? '—'}</td>
      ${deleteCell}
    `;
    tableBody.appendChild(row);
  });
}

async function deleteUser(id) {
  if (currentRole !== 'admin') {
    showError('Гостям запрещено удалять записи');
    return;
  }

  if (!window.confirm('Удалить эту запись из реестра?')) {
    return;
  }

  hideError();
  hideInfo();

  try {
    const response = await fetch(`${API_BASE_URL}/users/${id}`, {
      method: 'DELETE',
      headers: {
        'X-User-Role': currentRole,
      },
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      showError(data.error || 'Не удалось удалить запись');
      return;
    }

    users = users.filter((user) => user.id !== id);
    saveUsersToCache(users);
    renderTable(users);
    await fetchUsers();
  } catch {
    showError('Ошибка сети. Не удалось удалить запись.');
  }
}

function escapeHtml(value) {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

function saveUsersToCache(list) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
}

function loadUsersFromCache() {
  const raw = localStorage.getItem(STORAGE_KEY);
  if (!raw) {
    return [];
  }

  try {
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

async function fetchUsers() {
  hideError();
  hideInfo();

  try {
    const response = await fetch(`${API_BASE_URL}/users`, {
      method: 'GET',
      headers: {
        'X-User-Role': currentRole,
      },
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const data = await response.json();
    users = Array.isArray(data) ? data : [];
    saveUsersToCache(users);
    renderTable(users);
  } catch {
    users = loadUsersFromCache();
    renderTable(users);
    showInfo('Сервер недоступен. Показаны кэшированные данные.');
  }
}

async function addUser(event) {
  event.preventDefault();
  hideError();
  hideInfo();

  if (currentRole === 'guest') {
    showError('Гостям запрещено добавлять записи');
    return;
  }

  const name = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const ageValue = document.getElementById('age').value.trim();

  const payload = { name, email };
  if (ageValue !== '') {
    payload.age = Number(ageValue);
  }

  try {
    const response = await fetch(`${API_BASE_URL}/users`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-User-Role': currentRole,
      },
      body: JSON.stringify(payload),
    });

    const data = await response.json();

    if (!response.ok) {
      if (data.errors) {
        const messages = Object.entries(data.errors)
          .map(([field, message]) => `${field}: ${message}`)
          .join('; ');
        showError(messages);
      } else if (data.error) {
        showError(data.error);
      } else {
        showError('Не удалось добавить пользователя');
      }
      return;
    }

    if (data && data.id) {
      users = [...users, data];
      saveUsersToCache(users);
      renderTable(users);
    }

    userForm.reset();
    await fetchUsers();
  } catch {
    showError('Ошибка сети. Не удалось связаться с сервером.');
  }
}

function usersToCsv(list) {
  const header = ['id', 'name', 'email', 'age'];
  const rows = list.map((user) => [
    user.id,
    `"${String(user.name).replaceAll('"', '""')}"`,
    `"${String(user.email).replaceAll('"', '""')}"`,
    user.age ?? '',
  ]);

  return [header.join(','), ...rows.map((row) => row.join(','))].join('\n');
}

async function exportData() {
  await fetchUsers();

  if (!users.length) {
    showError('Нет записей для отчёта. Добавьте пользователя в реестр.');
    return;
  }

  hideError();

  const report = {
    title: 'Документ учёта — реестр персональных данных',
    exportedAt: new Date().toISOString(),
    total: users.length,
    records: users,
  };

  const jsonBlob = new Blob([JSON.stringify(report, null, 2)], {
    type: 'application/json;charset=utf-8',
  });
  const csvBlob = new Blob(['\uFEFF' + usersToCsv(users)], {
    type: 'text/csv;charset=utf-8',
  });

  const date = new Date().toISOString().slice(0, 10);
  downloadBlob(jsonBlob, `document-report-${date}.json`);
  downloadBlob(csvBlob, `document-report-${date}.csv`);
}

function downloadBlob(blob, filename) {
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  link.remove();
  URL.revokeObjectURL(url);
}

roleInputs.forEach((input) => {
  input.addEventListener('change', (event) => {
    switchRole(event.target.value);
    fetchUsers();
  });
});

tableBody.addEventListener('click', (event) => {
  const button = event.target.closest('.btn-delete');
  if (!button) {
    return;
  }

  deleteUser(Number(button.dataset.id));
});

userForm.addEventListener('submit', addUser);
exportBtn.addEventListener('click', exportData);

switchRole('admin');
fetchUsers();
