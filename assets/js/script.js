/* ============================================================
   IKAN// PROTOCOL - Core Logic (Reliable Hybrid Offline + Online API)
   ============================================================ */

(() => {
  'use strict';

  // ── DOM Helper Cache ──
  const $ = (selector) => document.querySelector(selector);
  const $$ = (selector) => document.querySelectorAll(selector);

  const elements = {
    // Nav & Controls
    dateDisplay: $('#dateDisplay'),
    prevDayBtn: $('#prevDayBtn'),
    nextDayBtn: $('#nextDayBtn'),
    todayBtn: $('#todayBtn'),
    
    // Progress & Stats
    progressPercent: $('#progressPercent'),
    progressRatio: $('#progressRatio'),
    meterBar: $('#meterBar'),
    streakVal: $('#streakVal'),
    completedTotalVal: $('#completedTotalVal'),
    efficiencyVal: $('#efficiencyVal'),
    healthStatusFootnote: $('#healthStatusFootnote'),
    weeklyGrid: $('#weeklyGrid'),

    // Habit List & Empty State
    habitList: $('#habitList'),
    emptyState: $('#emptyState'),
    habitCountBadge: $('#habitCountBadge'),
    btnOpenAddModal: $('#btnOpenAddModal'),

    // CRUD Modal
    crudModal: $('#crudModal'),
    modalTitle: $('#modalTitle'),
    btnCloseModal: $('#btnCloseModal'),
    btnCancelModal: $('#btnCancelModal'),
    habitForm: $('#habitForm'),
    editHabitId: $('#editHabitId'),
    habitTitleInput: $('#habitTitleInput'),
    habitCategoryInput: $('#habitCategoryInput'),
    habitTimeInput: $('#habitTimeInput'),
    emojiSelector: $('#emojiSelector'),

    // Delete Confirmation Modal
    deleteConfirmModal: $('#deleteConfirmModal'),
    deleteMsg: $('#deleteMsg'),
    btnCancelDelete: $('#btnCancelDelete'),
    btnAbortDelete: $('#btnAbortDelete'),
    btnExecuteDelete: $('#btnExecuteDelete'),

    // Background Stream & Toast
    toastContainer: $('#toastContainer')
  };

  // ── State ──
  let activeDate = new Date();
  let chosenEmoji = '🐟';
  let targetDeleteId = null;
  let isMySQLMode = false;

  const INDO_DAYS = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];
  const INDO_MONTHS = [
    'JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN',
    'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'
  ];

  // ── Date Formatting Helpers ──
  function toDateKey(dateObj) {
    const y = dateObj.getFullYear();
    const m = String(dateObj.getMonth() + 1).padStart(2, '0');
    const d = String(dateObj.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  }

  function formatDisplayDate(dateObj) {
    const dayName = INDO_DAYS[dateObj.getDay()];
    const dateNum = dateObj.getDate();
    const monthName = INDO_MONTHS[dateObj.getMonth()];
    const year = dateObj.getFullYear();
    return `${dayName}, ${dateNum} ${monthName} ${year}`;
  }

  function isSameDay(d1, d2) {
    return toDateKey(d1) === toDateKey(d2);
  }

  // ── Guaranteed Default Seed Data ──
  const DEFAULT_HABITS = [
    {
      id: 1,
      name: 'Beri pakan pelet berprotein tinggi',
      category: 'PAKAN',
      time: '07:30 WIB',
      emoji: '🐟'
    },
    {
      id: 2,
      name: 'Cek suhu air (25°C - 28°C) & heater',
      category: 'SUHU & LAMPU',
      time: '09:00 WIB',
      emoji: '🌡️'
    },
    {
      id: 3,
      name: 'Siphon kotoran dasar & cuci busa filter',
      category: 'SANITASI',
      time: '14:00 WIB',
      emoji: '🧽'
    },
    {
      id: 4,
      name: 'Inspeksi sirip, sisik & keaktifan renang',
      category: 'KESEHATAN',
      time: '16:00 WIB',
      emoji: '🐠'
    },
    {
      id: 5,
      name: 'Pakan cacing sutra / artemia sore',
      category: 'PAKAN',
      time: '17:30 WIB',
      emoji: '🐡'
    },
    {
      id: 6,
      name: 'Water change 20% air endapan anti-klorin',
      category: 'KUALITAS AIR',
      time: 'Setiap 2 Hari',
      emoji: '💧'
    }
  ];

  const STORAGE_KEY = 'IKAN_HABIT_PROTOCOL_V1';

  function fetchStorage() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (raw) {
        const parsed = JSON.parse(raw);
        if (parsed.habits && parsed.habits.length > 0) {
          return parsed;
        }
      }
    } catch (e) {
      console.error('Storage read error', e);
    }
    // Return default habits if empty
    return { habits: [...DEFAULT_HABITS], records: {} };
  }

  function commitStorage(data) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    } catch (e) {
      console.error('Storage write error', e);
    }
  }

  // ── Toast Notification Feed ──
  function triggerToast(message) {
    if (!elements.toastContainer) return;
    const toast = document.createElement('div');
    toast.className = 'toast-item';
    toast.textContent = `// ${message}`;
    elements.toastContainer.appendChild(toast);

    setTimeout(() => {
      toast.style.animation = 'toastOut 0.25s forwards';
      setTimeout(() => toast.remove(), 250);
    }, 2800);
  }

  // ── [CRUD] READ: Ambil Data Habit ──
  async function loadAndRenderHabits() {
    const currentKey = toDateKey(activeDate);
    if (elements.dateDisplay) {
      elements.dateDisplay.textContent = formatDisplayDate(activeDate);
    }

    let habits = [];
    let completedIds = [];

    // Cek apakah dijalankan di lingkungan HTTP server (PHP)
    const isHttpProtocol = window.location.protocol.startsWith('http');

    if (isHttpProtocol) {
      try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 2000); // 2 detik timeout

        const res = await fetch(`api.php?action=get_habits&tanggal=${currentKey}`, {
          signal: controller.signal
        });
        clearTimeout(timeoutId);

        if (res.ok) {
          const data = await res.json();
          if (data.status === 'success' && Array.isArray(data.habits) && data.habits.length > 0) {
            isMySQLMode = true;
            habits = data.habits.map(h => ({
              id: h.id,
              name: h.nama_habit,
              category: h.kategori,
              time: h.waktu,
              emoji: h.emblem
            }));
            completedIds = (data.completed_ids || []).map(Number);
          }
        }
      } catch (e) {
        isMySQLMode = false;
      }
    }

    // Jika mode offline atau API belum ada data, gunakan LocalStorage
    if (habits.length === 0) {
      isMySQLMode = false;
      const store = fetchStorage();
      habits = store.habits || [];
      completedIds = (store.records[currentKey] || []).map(Number);
    }

    renderHabitRows(habits, completedIds, currentKey);
  }

  // ── Manipulasi DOM: Render Baris Habit ──
  function renderHabitRows(habits, completedIds, currentKey) {
    if (!elements.habitList) return;
    elements.habitList.innerHTML = '';
    const totalHabits = habits.length;
    
    if (elements.habitCountBadge) {
      elements.habitCountBadge.textContent = `${totalHabits} PROTOKOL TERDAFTAR`;
    }

    if (totalHabits === 0) {
      if (elements.emptyState) elements.emptyState.classList.add('visible');
    } else {
      if (elements.emptyState) elements.emptyState.classList.remove('visible');
    }

    habits.forEach((habit) => {
      const isCompleted = completedIds.includes(Number(habit.id));

      const row = document.createElement('div');
      row.className = `habit-row ${isCompleted ? 'completed' : ''}`;
      row.dataset.id = habit.id;

      row.innerHTML = `
        <label class="habit-checkbox-wrapper" title="Checklist tugas">
          <input type="checkbox" class="habit-check-input" data-id="${habit.id}" ${isCompleted ? 'checked' : ''} />
          <div class="habit-check-box"></div>
        </label>
        
        <div class="habit-avatar">${habit.emoji || '🐟'}</div>
        
        <div class="habit-details">
          <div class="habit-title-text" title="${habit.name}">${escapeHtml(habit.name)}</div>
          <div class="habit-meta-row">
            <span class="badge-category">${escapeHtml(habit.category || 'RUTIN')}</span>
            <span class="habit-time-stamp">⏰ ${escapeHtml(habit.time || 'Fleksibel')}</span>
          </div>
        </div>

        <div class="habit-crud-actions">
          <button type="button" class="action-btn btn-edit" data-id="${habit.id}" title="Edit habit">EDIT //</button>
          <button type="button" class="action-btn btn-delete" data-id="${habit.id}" title="Hapus habit">DEL ✕</button>
        </div>
      `;

      elements.habitList.appendChild(row);
    });

    calculateMetrics(habits, completedIds, currentKey);
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // ── [CRUD] CREATE & UPDATE ──
  function openAddModal() {
    elements.editHabitId.value = '';
    elements.modalTitle.textContent = '[ ENTRY PROTOKOL : BARU ]';
    elements.habitTitleInput.value = '';
    elements.habitCategoryInput.value = 'PAKAN';
    elements.habitTimeInput.value = '';
    selectEmojiBtn('🐟');
    elements.crudModal.classList.add('active');
    setTimeout(() => elements.habitTitleInput.focus(), 150);
  }

  function openEditModal(habitId) {
    const row = $(`[data-id="${habitId}"]`);
    if (!row) return;

    const title = row.querySelector('.habit-title-text').textContent;
    const cat = row.querySelector('.badge-category').textContent;
    const timeText = row.querySelector('.habit-time-stamp').textContent.replace('⏰ ', '');
    const emoji = row.querySelector('.habit-avatar').textContent;

    elements.editHabitId.value = habitId;
    elements.modalTitle.textContent = `[ EDIT PROTOKOL : ID #${habitId} ]`;
    elements.habitTitleInput.value = title;
    elements.habitCategoryInput.value = cat;
    elements.habitTimeInput.value = timeText;
    selectEmojiBtn(emoji);
    elements.crudModal.classList.add('active');
    setTimeout(() => elements.habitTitleInput.focus(), 150);
  }

  function closeCrudModal() {
    elements.crudModal.classList.remove('active');
  }

  function selectEmojiBtn(emoji) {
    chosenEmoji = emoji;
    $$('.emoji-btn').forEach(btn => {
      btn.classList.toggle('selected', btn.dataset.emoji === emoji);
    });
  }

  async function handleHabitSubmit(e) {
    e.preventDefault();
    const name = elements.habitTitleInput.value.trim();
    const category = elements.habitCategoryInput.value;
    const time = elements.habitTimeInput.value.trim() || 'Rutin harian';
    const habitId = elements.editHabitId.value;

    if (!name) return;

    if (isMySQLMode) {
      const endpoint = habitId ? 'api.php?action=update_habit' : 'api.php?action=create_habit';
      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: habitId, nama: name, kategori: category, waktu: time, emblem: chosenEmoji })
        });
        const resData = await res.json();
        triggerToast(resData.message || 'Data berhasil disimpan ke MySQL');
      } catch (err) {
        triggerToast('Gagal terhubung ke MySQL, menyimpan lokal...');
        saveToLocalStorage(habitId, name, category, time, chosenEmoji);
      }
    } else {
      saveToLocalStorage(habitId, name, category, time, chosenEmoji);
    }

    closeCrudModal();
    loadAndRenderHabits();
  }

  function saveToLocalStorage(habitId, name, category, time, emoji) {
    const store = fetchStorage();
    if (habitId) {
      const idx = store.habits.findIndex(h => String(h.id) === String(habitId));
      if (idx !== -1) {
        store.habits[idx].name = name;
        store.habits[idx].category = category;
        store.habits[idx].time = time;
        store.habits[idx].emoji = emoji;
        triggerToast(`PROTOKOL #${habitId} BERHASIL DIPERBARUI`);
      }
    } else {
      const newHabit = {
        id: Date.now(),
        name,
        category,
        time,
        emoji
      };
      store.habits.unshift(newHabit);
      triggerToast(`PROTOKOL BARU "${name}" TERSIMPAN`);
    }
    commitStorage(store);
  }

  // ── [CRUD] DELETE: Hapus Data Habit ──
  function promptDeleteHabit(habitId) {
    targetDeleteId = habitId;
    elements.deleteMsg.innerHTML = `Yakin menghapus kebiasaan <strong>ID #${habitId}</strong> secara permanen?<br>Riwayat checklist harian akan dibersihkan.`;
    elements.deleteConfirmModal.classList.add('active');
  }

  function closeDeleteModal() {
    targetDeleteId = null;
    elements.deleteConfirmModal.classList.remove('active');
  }

  async function executeDelete() {
    if (!targetDeleteId) return;

    if (isMySQLMode) {
      try {
        const res = await fetch(`api.php?action=delete_habit&id=${targetDeleteId}`);
        const data = await res.json();
        triggerToast(data.message || 'Habit dihapus dari database MySQL');
      } catch (err) {
        triggerToast('Gagal menghapus dari MySQL');
      }
    }

    // Selalu bersihkan juga dari LocalStorage
    const store = fetchStorage();
    store.habits = store.habits.filter(h => String(h.id) !== String(targetDeleteId));
    Object.keys(store.records).forEach(k => {
      store.records[k] = store.records[k].filter(id => String(id) !== String(targetDeleteId));
    });
    commitStorage(store);

    if (!isMySQLMode) {
      triggerToast('DATA PROTOKOL DIHAPUS');
    }

    closeDeleteModal();
    loadAndRenderHabits();
  }

  // ── Checklist Toggle ──
  async function toggleHabitCheck(habitId) {
    const currentKey = toDateKey(activeDate);

    if (isMySQLMode) {
      try {
        await fetch('api.php?action=toggle_log', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ habit_id: habitId, tanggal: currentKey })
        });
      } catch (err) {
        console.error('Toggle error', err);
      }
    }

    // Update state di LocalStorage
    const store = fetchStorage();
    if (!store.records[currentKey]) store.records[currentKey] = [];
    const numId = Number(habitId);
    const pos = store.records[currentKey].indexOf(numId);
    if (pos > -1) {
      store.records[currentKey].splice(pos, 1);
    } else {
      store.records[currentKey].push(numId);
    }
    commitStorage(store);

    loadAndRenderHabits();
  }

  // ── Algoritma Perhitungan Metrik & Progress Meter ──
  function calculateMetrics(habits, completedIds, currentKey) {
    const total = habits.length;
    const completed = completedIds.length;
    const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;

    if (elements.progressPercent) elements.progressPercent.textContent = `${percentage}%`;
    if (elements.progressRatio) elements.progressRatio.textContent = `${completed}/${total}`;
    if (elements.meterBar) elements.meterBar.style.width = `${percentage}%`;

    if (elements.efficiencyVal && elements.healthStatusFootnote) {
      if (percentage >= 80) {
        elements.efficiencyVal.textContent = 'PRIMA';
        elements.efficiencyVal.style.color = 'var(--brand-lime)';
        elements.healthStatusFootnote.textContent = '> KONDISI : SEHAT & AKTIF';
      } else if (percentage >= 40) {
        elements.efficiencyVal.textContent = 'SEDANG';
        elements.efficiencyVal.style.color = 'var(--brand-yellow)';
        elements.healthStatusFootnote.textContent = '> PERHATIAN : BUTUH CHECKLIST';
      } else {
        elements.efficiencyVal.textContent = 'KRITIS';
        elements.efficiencyVal.style.color = 'var(--brand-blue)';
        elements.healthStatusFootnote.textContent = '> WASPADA : IKAN BELUM DIURUS';
      }
    }

    const store = fetchStorage();
    let grandTotal = 0;
    Object.keys(store.records).forEach(k => {
      grandTotal += store.records[k].length;
    });
    if (elements.completedTotalVal) {
      elements.completedTotalVal.textContent = grandTotal + (isMySQLMode ? completed : 0);
    }

    let streak = 0;
    const checkDate = new Date();
    while (true) {
      const key = toDateKey(checkDate);
      const doneIds = store.records[key] || [];
      if (total > 0 && doneIds.length >= total) {
        streak++;
        checkDate.setDate(checkDate.getDate() - 1);
      } else {
        break;
      }
    }
    if (elements.streakVal) elements.streakVal.textContent = streak;

    renderWeeklyOverview(store, habits);
  }

  // ── Algoritma Perulangan For: Weekly 7-Day Box ──
  function renderWeeklyOverview(store, habits) {
    if (!elements.weeklyGrid) return;
    elements.weeklyGrid.innerHTML = '';
    const dayOfWeek = activeDate.getDay();
    const startOfWeek = new Date(activeDate);
    startOfWeek.setDate(startOfWeek.getDate() - dayOfWeek);

    for (let i = 0; i < 7; i++) {
      const d = new Date(startOfWeek);
      d.setDate(d.getDate() + i);

      const dKey = toDateKey(d);
      const completed = (store.records[dKey] || []).length;
      const total = habits.length;
      const isAllDone = total > 0 && completed >= total;
      const isSelectedDay = isSameDay(d, activeDate);

      const cell = document.createElement('div');
      cell.className = `weekly-cell ${isSelectedDay ? 'active-today' : ''} ${isAllDone ? 'all-done' : ''}`;

      cell.innerHTML = `
        <div class="w-day-label">${INDO_DAYS[d.getDay()].substr(0, 3)}</div>
        <div class="w-day-num">${d.getDate()}</div>
        <div class="w-dots-status">${isAllDone ? '🟢' : completed > 0 ? '🟡' : '⚪'}</div>
      `;

      cell.addEventListener('click', () => {
        activeDate = new Date(d);
        loadAndRenderHabits();
      });

      elements.weeklyGrid.appendChild(cell);
    }
  }

  // ── Event Bindings ──
  function setupEventListeners() {
    if (elements.prevDayBtn) {
      elements.prevDayBtn.addEventListener('click', () => {
        activeDate.setDate(activeDate.getDate() - 1);
        loadAndRenderHabits();
      });
    }

    if (elements.nextDayBtn) {
      elements.nextDayBtn.addEventListener('click', () => {
        activeDate.setDate(activeDate.getDate() + 1);
        loadAndRenderHabits();
      });
    }

    if (elements.todayBtn) {
      elements.todayBtn.addEventListener('click', () => {
        activeDate = new Date();
        loadAndRenderHabits();
      });
    }

    // Modal Add / Edit
    if (elements.btnOpenAddModal) {
      elements.btnOpenAddModal.addEventListener('click', openAddModal);
    }
    if (elements.btnCloseModal) {
      elements.btnCloseModal.addEventListener('click', closeCrudModal);
    }
    if (elements.btnCancelModal) {
      elements.btnCancelModal.addEventListener('click', closeCrudModal);
    }
    if (elements.crudModal) {
      elements.crudModal.addEventListener('click', (e) => {
        if (e.target === elements.crudModal) closeCrudModal();
      });
    }

    if (elements.habitForm) {
      elements.habitForm.addEventListener('submit', handleHabitSubmit);
    }

    if (elements.emojiSelector) {
      elements.emojiSelector.addEventListener('click', (e) => {
        const btn = e.target.closest('.emoji-btn');
        if (btn) selectEmojiBtn(btn.dataset.emoji);
      });
    }

    // Event Delegation: Checkbox, Edit, Delete
    if (elements.habitList) {
      elements.habitList.addEventListener('change', (e) => {
        if (e.target.classList.contains('habit-check-input')) {
          toggleHabitCheck(e.target.dataset.id);
        }
      });

      elements.habitList.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.btn-edit');
        if (editBtn) {
          openEditModal(editBtn.dataset.id);
          return;
        }

        const delBtn = e.target.closest('.btn-delete');
        if (delBtn) {
          promptDeleteHabit(delBtn.dataset.id);
          return;
        }
      });
    }

    // Modal Konfirmasi Hapus
    if (elements.btnCancelDelete) elements.btnCancelDelete.addEventListener('click', closeDeleteModal);
    if (elements.btnAbortDelete) elements.btnAbortDelete.addEventListener('click', closeDeleteModal);
    if (elements.btnExecuteDelete) elements.btnExecuteDelete.addEventListener('click', executeDelete);
    if (elements.deleteConfirmModal) {
      elements.deleteConfirmModal.addEventListener('click', (e) => {
        if (e.target === elements.deleteConfirmModal) closeDeleteModal();
      });
    }

    // Keyboard ESC
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        closeCrudModal();
        closeDeleteModal();
      }
    });
  }

  // ── Initializer ──
  function initialize() {
    setupEventListeners();
    loadAndRenderHabits();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
  } else {
    initialize();
  }
})();
