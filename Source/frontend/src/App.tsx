import { FormEvent, useEffect, useState } from 'react';
import {
  AuthUser,
  RegistryRecord,
  createUser,
  deleteUser,
  exportReport,
  fetchUsers,
  login,
} from './api';

const STORAGE_KEY = 'cachedUsers';

export default function App() {
  const [tokenUser, setTokenUser] = useState<AuthUser | null>(() => {
    const raw = localStorage.getItem('authUser');
    return raw ? (JSON.parse(raw) as AuthUser) : null;
  });
  const [email, setEmail] = useState('admin@document.local');
  const [password, setPassword] = useState('admin123');
  const [users, setUsers] = useState<RegistryRecord[]>([]);
  const [error, setError] = useState('');
  const [info, setInfo] = useState('');
  const [name, setName] = useState('');
  const [userEmail, setUserEmail] = useState('');
  const [age, setAge] = useState('');

  const isAdmin = tokenUser?.role === 'admin';

  async function loadUsers() {
    setError('');
    setInfo('');
    try {
      const data = await fetchUsers();
      setUsers(data);
      localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    } catch {
      const cached = localStorage.getItem(STORAGE_KEY);
      setUsers(cached ? JSON.parse(cached) : []);
      setInfo('Сервер недоступен. Показаны кэшированные данные.');
    }
  }

  useEffect(() => {
    if (tokenUser && localStorage.getItem('accessToken')) {
      loadUsers();
    }
  }, [tokenUser]);

  async function handleLogin(e: FormEvent) {
    e.preventDefault();
    setError('');
    try {
      const result = await login(email, password);
      localStorage.setItem('accessToken', result.accessToken);
      localStorage.setItem('authUser', JSON.stringify(result.user));
      setTokenUser(result.user);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Ошибка входа');
    }
  }

  function handleLogout() {
    localStorage.removeItem('accessToken');
    localStorage.removeItem('authUser');
    setTokenUser(null);
    setUsers([]);
  }

  async function handleAdd(e: FormEvent) {
    e.preventDefault();
    if (!isAdmin) return;
    setError('');
    try {
      await createUser({
        name,
        email: userEmail,
        age: age ? Number(age) : undefined,
      });
      setName('');
      setUserEmail('');
      setAge('');
      await loadUsers();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Ошибка добавления');
    }
  }

  async function handleDelete(id: number) {
    if (!isAdmin || !window.confirm('Удалить запись?')) return;
    try {
      await deleteUser(id);
      await loadUsers();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Ошибка удаления');
    }
  }

  async function handleExport() {
    try {
      const report = await exportReport();
      const blob = new Blob([JSON.stringify(report, null, 2)], {
        type: 'application/json',
      });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `document-report-${new Date().toISOString().slice(0, 10)}.json`;
      link.click();
      URL.revokeObjectURL(url);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Ошибка экспорта');
    }
  }

  if (!tokenUser) {
    return (
      <div className="container">
        <header className="page-header">
          <h1>Документ — вход в систему</h1>
        </header>
        <section className="panel">
          <form onSubmit={handleLogin} className="form-grid">
            <label>
              Email
              <input value={email} onChange={(e) => setEmail(e.target.value)} />
            </label>
            <label>
              Пароль
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
              />
            </label>
            <button type="submit" className="btn btn-primary">
              Войти
            </button>
          </form>
          {error && <div className="error-message visible">{error}</div>}
          <p className="hint">admin@document.local / admin123 · guest@document.local / guest123</p>
        </section>
      </div>
    );
  }

  return (
    <div className="container">
      <header className="page-header">
        <h1>Реестр персональных данных (Документ учёта)</h1>
        <p className="subtitle">
          {tokenUser.fullName} · роль: <strong>{tokenUser.role}</strong>
          <button type="button" className="btn btn-link" onClick={handleLogout}>
            Выйти
          </button>
        </p>
      </header>

      {isAdmin && (
        <section className="panel">
          <h2>Добавить учётную карточку</h2>
          <form onSubmit={handleAdd} className="form-grid">
            <label>
              Name
              <input value={name} onChange={(e) => setName(e.target.value)} required />
            </label>
            <label>
              Email
              <input
                type="email"
                value={userEmail}
                onChange={(e) => setUserEmail(e.target.value)}
                required
              />
            </label>
            <label>
              Age
              <input value={age} onChange={(e) => setAge(e.target.value)} />
            </label>
            <button type="submit" className="btn btn-primary">
              Добавить пользователя
            </button>
          </form>
        </section>
      )}

      <section className="panel">
        <div className="table-header">
          <h2>Реестр записей</h2>
          <button type="button" className="btn btn-secondary" onClick={handleExport}>
            Скачать отчёт
          </button>
        </div>
        {error && <div className="error-message visible">{error}</div>}
        {info && <div className="info-message visible">{info}</div>}
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Имя</th>
              <th>Email</th>
              <th>Возраст</th>
              {isAdmin && <th>Действия</th>}
            </tr>
          </thead>
          <tbody>
            {!users.length ? (
              <tr>
                <td colSpan={isAdmin ? 5 : 4} className="empty-row">
                  Записей нет
                </td>
              </tr>
            ) : (
              users.map((user) => (
                <tr key={user.id}>
                  <td>{user.id}</td>
                  <td>{user.name}</td>
                  <td>{user.email}</td>
                  <td>{user.age ?? '—'}</td>
                  {isAdmin && (
                    <td>
                      <button
                        type="button"
                        className="btn-delete"
                        onClick={() => handleDelete(user.id)}
                      >
                        🗑
                      </button>
                    </td>
                  )}
                </tr>
              ))
            )}
          </tbody>
        </table>
      </section>
    </div>
  );
}
