// Simple state
let authToken = null;
let currentUser = null;

// Helper: set token to header
function getHeaders() {
    return {
        'Content-Type': 'application/json',
        ...(authToken ? { 'Authorization': 'Bearer ' + authToken } : {})
    };
}

// Login
const loginForm = document.getElementById('login-form');
if (loginForm) {
    loginForm.onsubmit = async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        let res, data;
        try {
            res = await fetch('/api/login', {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({ email, password })
            });
            try {
                data = await res.json();
            } catch {
                data = { message: 'Server error or invalid response' };
            }
        } catch {
            data = { message: 'Network error' };
            res = { ok: false };
        }
        if (res.ok && data && data.token) {
            authToken = data.token;
            currentUser = data.user;
            showDashboard();
        } else {
            document.getElementById('login-error').innerText = data.message || 'Login failed';
        }
    };
}

// Show dashboard
function showDashboard() {
    document.getElementById('login-section').classList.add('d-none');
    document.getElementById('dashboard-section').classList.remove('d-none');
    document.getElementById('user-info').innerHTML = `<b>${currentUser.name}</b> (${currentUser.role})`;
    if (currentUser.role === 'admin') {
        document.getElementById('admin-section').classList.remove('d-none');
        document.getElementById('user-list-section').classList.remove('d-none');
        loadUserList();
    } else {
        document.getElementById('admin-section').classList.add('d-none');
        document.getElementById('user-list-section').classList.add('d-none');
    }
    loadTaskList();
    loadUserOptions();
}

// Logout
const logoutBtn = document.getElementById('logout-btn');
if (logoutBtn) {
    logoutBtn.onclick = async () => {
        await fetch('/api/logout', { method: 'POST', headers: getHeaders() });
        authToken = null;
        currentUser = null;
        document.getElementById('dashboard-section').classList.add('d-none');
        document.getElementById('login-section').classList.remove('d-none');
    };
}

// Load user list (admin)
async function loadUserList() {
    const res = await fetch('/api/users', { headers: getHeaders() });
    const users = await res.json();
    const tbody = document.querySelector('#user-list-table tbody');
    tbody.innerHTML = '';
    users.forEach(u => {
        tbody.innerHTML += `<tr><td>${u.name}</td><td>${u.email}</td><td>${u.role}</td><td>${u.status ? 'Active' : 'Inactive'}</td></tr>`;
    });
}

// Create user (admin)
const createUserForm = document.getElementById('create-user-form');
if (createUserForm) {
    createUserForm.onsubmit = async (e) => {
        e.preventDefault();
        const name = document.getElementById('new-user-name').value;
        const email = document.getElementById('new-user-email').value;
        const password = document.getElementById('new-user-password').value;
        const role = document.getElementById('new-user-role').value;
        const status = document.getElementById('new-user-status').value;
        let res, data;
        try {
            res = await fetch('/api/users', {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({ name, email, password, role, status })
            });
            try {
                data = await res.json();
            } catch {
                data = { message: 'Server error or invalid response' };
            }
        } catch {
            data = { message: 'Network error' };
            res = { ok: false };
        }
        if (res.ok) {
            loadUserList();
            createUserForm.reset();
            document.getElementById('user-create-error').innerText = '';
        } else {
            document.getElementById('user-create-error').innerText = data.message || 'Failed to create user';
        }
    };
}

// Load user options for task assignment
async function loadUserOptions() {
    const select = document.getElementById('task-assigned-to');
    if (!select) return;
    select.innerHTML = '<option value="">Unassigned</option>';
    if (currentUser.role === 'admin' || currentUser.role === 'manager') {
        const res = await fetch('/api/users', { headers: getHeaders() });
        const users = await res.json();
        users.forEach(u => {
            if (currentUser.role === 'manager' && u.role !== 'staff') return;
            select.innerHTML += `<option value="${u.id}">${u.name} (${u.role})</option>`;
        });
    } else {
        select.innerHTML += `<option value="${currentUser.id}">${currentUser.name} (self)</option>`;
    }
}

// Show/hide task form
const showTaskFormBtn = document.getElementById('show-task-form');
const createTaskForm = document.getElementById('create-task-form');
if (showTaskFormBtn && createTaskForm) {
    showTaskFormBtn.onclick = () => {
        createTaskForm.classList.toggle('d-none');
    };
}

// Create task
if (createTaskForm) {
    createTaskForm.onsubmit = async (e) => {
        e.preventDefault();
        const title = document.getElementById('task-title').value;
        const description = document.getElementById('task-desc').value;
        const due_date = document.getElementById('task-due').value;
        const assigned_to = document.getElementById('task-assigned-to').value;
        let res, data;
        try {
            res = await fetch('/api/tasks', {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({ title, description, due_date, assigned_to })
            });
            try {
                data = await res.json();
            } catch {
                data = { message: 'Server error or invalid response' };
            }
        } catch {
            data = { message: 'Network error' };
            res = { ok: false };
        }
        if (res.ok) {
            loadTaskList();
            createTaskForm.reset();
            document.getElementById('task-create-error').innerText = '';
        } else {
            document.getElementById('task-create-error').innerText = data.message || 'Failed to create task';
        }
    };
}

// Load task list
async function loadTaskList() {
    const res = await fetch('/api/tasks', { headers: getHeaders() });
    const tasks = await res.json();
    const tbody = document.querySelector('#task-list-table tbody');
    tbody.innerHTML = '';
    tasks.forEach(t => {
        let badge = '<span class="badge bg-secondary">' + t.status + '</span>';
        if (t.status === 'pending') badge = '<span class="badge bg-warning text-dark">Pending</span>';
        if (t.status === 'in_progress') badge = '<span class="badge bg-info text-dark">In Progress</span>';
        if (t.status === 'done') badge = '<span class="badge bg-success">Done</span>';
        // Kolom Action: tombol edit/delete aktif
        let action = '';
        if (currentUser && (currentUser.role === 'admin' || currentUser.role === 'manager' || t.assigned_to === currentUser.id)) {
            action = `<button class='btn btn-sm btn-primary me-1 btn-edit-task' data-id='${t.id}'>Edit</button><button class='btn btn-sm btn-danger btn-delete-task' data-id='${t.id}'>Delete</button>`;
        }
        tbody.innerHTML += `<tr>
            <td>${t.title}</td>
            <td>${t.description || ''}</td>
            <td>${badge}</td>
            <td>${t.due_date || ''}</td>
            <td>${t.assigned_to || '-'}</td>
            <td>${action}</td>
        </tr>`;
    });
    // Tambahkan event listener untuk tombol delete
    document.querySelectorAll('.btn-delete-task').forEach(btn => {
        btn.onclick = async function() {
            if (confirm('Yakin ingin menghapus task ini?')) {
                const id = this.getAttribute('data-id');
                const res = await fetch(`/api/tasks/${id}`, {
                    method: 'DELETE',
                    headers: getHeaders()
                });
                if (res.ok) {
                    loadTaskList();
                } else {
                    let msg = 'Gagal menghapus task';
                    try {
                        const data = await res.json();
                        if (data && data.message) msg += ": " + data.message;
                    } catch {}
                    alert(msg);
                }
            }
        };
    });
    // Tambahkan event listener untuk tombol edit
    document.querySelectorAll('.btn-edit-task').forEach(btn => {
        btn.onclick = async function() {
            const id = this.getAttribute('data-id');
            // Fetch data task
            let res, data;
            try {
                res = await fetch(`/api/tasks/${id}`, { headers: getHeaders() });
                data = await res.json();
            } catch {
                alert('Gagal mengambil data task');
                return;
            }
            if (!res.ok) {
                alert('Gagal mengambil data task: ' + (data.message || 'Unknown error'));
                return;
            }
            // Isi form modal
            document.getElementById('edit-task-id').value = data.id;
            document.getElementById('edit-task-title').value = data.title;
            document.getElementById('edit-task-desc').value = data.description || '';
            document.getElementById('edit-task-due').value = data.due_date || '';
            document.getElementById('edit-task-status').value = data.status || 'pending';
            await loadEditUserOptions(data.assigned_to);
            document.getElementById('edit-task-error').innerText = '';
            // Tampilkan modal (Bootstrap 5)
            const modal = new bootstrap.Modal(document.getElementById('edit-task-modal'));
            modal.show();
        };
    });
}

// Tambah modal edit task ke HTML jika belum ada
if (!document.getElementById('edit-task-modal')) {
    const modalHtml = `
    <div class="modal fade" id="edit-task-modal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Task</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="edit-task-form">
              <input type="hidden" id="edit-task-id">
              <div class="mb-2">
                <label for="edit-task-title" class="form-label">Title</label>
                <input type="text" class="form-control" id="edit-task-title" required>
              </div>
              <div class="mb-2">
                <label for="edit-task-desc" class="form-label">Description</label>
                <textarea class="form-control" id="edit-task-desc"></textarea>
              </div>
              <div class="mb-2">
                <label for="edit-task-due" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="edit-task-due">
              </div>
              <div class="mb-2">
                <label for="edit-task-status" class="form-label">Status</label>
                <select class="form-select" id="edit-task-status">
                  <option value="pending">Pending</option>
                  <option value="in_progress">In Progress</option>
                  <option value="done">Done</option>
                </select>
              </div>
              <div class="mb-2">
                <label for="edit-task-assigned-to" class="form-label">Assigned To</label>
                <select class="form-select" id="edit-task-assigned-to"></select>
              </div>
              <div class="text-danger" id="edit-task-error"></div>
              <button type="submit" class="btn btn-primary">Update</button>
            </form>
          </div>
        </div>
      </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

// Helper untuk load user options ke select edit-task-assigned-to
async function loadEditUserOptions(selectedId) {
    const select = document.getElementById('edit-task-assigned-to');
    if (!select) return;
    select.innerHTML = '<option value="">Unassigned</option>';
    if (currentUser.role === 'admin' || currentUser.role === 'manager') {
        const res = await fetch('/api/users', { headers: getHeaders() });
        const users = await res.json();
        users.forEach(u => {
            if (currentUser.role === 'manager' && u.role !== 'staff') return;
            select.innerHTML += `<option value="${u.id}" ${u.id === selectedId ? 'selected' : ''}>${u.name} (${u.role})</option>`;
        });
    } else {
        select.innerHTML += `<option value="${currentUser.id}" selected>${currentUser.name} (self)</option>`;
    }
}

// Event handler submit edit task
const editTaskForm = document.getElementById('edit-task-form');
if (editTaskForm) {
    editTaskForm.onsubmit = async function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-task-id').value;
        const title = document.getElementById('edit-task-title').value;
        const description = document.getElementById('edit-task-desc').value;
        const due_date = document.getElementById('edit-task-due').value;
        const status = document.getElementById('edit-task-status').value;
        const assigned_to = document.getElementById('edit-task-assigned-to').value;
        let res, data;
        try {
            res = await fetch(`/api/tasks/${id}`, {
                method: 'PUT',
                headers: getHeaders(),
                body: JSON.stringify({ title, description, due_date, status, assigned_to })
            });
            data = await res.json();
        } catch {
            document.getElementById('edit-task-error').innerText = 'Network error';
            return;
        }
        if (res.ok) {
            // Sembunyikan modal dan reload task list
            bootstrap.Modal.getInstance(document.getElementById('edit-task-modal')).hide();
            loadTaskList();
        } else {
            document.getElementById('edit-task-error').innerText = data.message || 'Gagal update task';
        }
    };
}
