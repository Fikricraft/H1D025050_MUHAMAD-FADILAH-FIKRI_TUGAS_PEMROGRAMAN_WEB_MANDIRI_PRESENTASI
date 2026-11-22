/* ============================================================
   IKAN// PROTOCOL - Script halaman dalam
   Berisi interaksi kecil yang dipakai banyak halaman:
   konfirmasi hapus, penghitung karakter, pencarian tabel,
   dan tombol cetak.
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* ---------- 1. Konfirmasi hapus ---------- */
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (event) {
      const pesan = el.getAttribute('data-confirm') || 'Yakin ingin melanjutkan?';
      if (!window.confirm(pesan)) {
        event.preventDefault();
      }
    });
  });

  /* ---------- 2. Penghitung karakter textarea ---------- */
  document.querySelectorAll('[data-counter]').forEach(function (textarea) {
    const targetId = textarea.getAttribute('data-counter');
    const target = document.getElementById(targetId);
    const maks = parseInt(textarea.getAttribute('maxlength') || '0', 10);

    function perbarui() {
      const panjang = textarea.value.length;
      if (target) {
        target.textContent = maks > 0
          ? panjang + ' / ' + maks + ' karakter'
          : panjang + ' karakter';
        target.style.color = (maks > 0 && panjang > maks * 0.9) ? '#ffbf47' : '';
      }
    }

    textarea.addEventListener('input', perbarui);
    perbarui();
  });

  /* ---------- 3. Pencarian / filter tabel ---------- */
  document.querySelectorAll('[data-table-filter]').forEach(function (input) {
    const table = document.querySelector(input.getAttribute('data-table-filter'));
    if (!table) return;

    const kosong = document.querySelector('[data-filter-empty]');

    input.addEventListener('input', function () {
      const kata = input.value.toLowerCase().trim();
      const baris = table.querySelectorAll('tbody tr');
      let tampil = 0;

      baris.forEach(function (tr) {
        const cocok = tr.textContent.toLowerCase().includes(kata);
        tr.style.display = cocok ? '' : 'none';
        if (cocok) tampil++;
      });

      if (kosong) {
        kosong.style.display = tampil === 0 ? 'block' : 'none';
      }
    });
  });

  /* ---------- 4. Tombol cetak ---------- */
  document.querySelectorAll('[data-print]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      window.print();
    });
  });

  /* ---------- 5. Pilih semua checkbox (dipakai halaman riwayat) ---------- */
  const pilihSemua = document.getElementById('pilihSemua');
  if (pilihSemua) {
    pilihSemua.addEventListener('change', function () {
      document.querySelectorAll('.pilih-baris').forEach(function (cb) {
        cb.checked = pilihSemua.checked;
      });
    });
  }

  /* ---------- 6. Konfirmasi keluar dari halaman form yang belum disimpan ---------- */
  const formBerubah = document.querySelector('form[data-warn-unsaved]');
  if (formBerubah) {
    let kotor = false;

    formBerubah.addEventListener('input', function () { kotor = true; });
    formBerubah.addEventListener('submit', function () { kotor = false; });

    window.addEventListener('beforeunload', function (event) {
      if (kotor) {
        event.preventDefault();
        event.returnValue = '';
      }
    });
  }
});
