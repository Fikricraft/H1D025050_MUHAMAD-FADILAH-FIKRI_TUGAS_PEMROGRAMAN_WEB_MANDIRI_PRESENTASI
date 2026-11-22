<?php
$jenis_manual = $input['jenis_pakan'] !== '' && !in_array($input['jenis_pakan'], $jenis_pakan, true);
$satuan_manual = $input['satuan'] !== '' && !in_array($input['satuan'], $satuan_pilihan, true);
?>
<div class="form-grid">
  <div class="form-row">
    <label class="form-label" for="tanggal">TANGGAL MAKAN</label>
    <input class="form-input" type="date" id="tanggal" name="tanggal"
           value="<?= e($input['tanggal']) ?>" required>
  </div>

  <div class="form-row">
    <label class="form-label" for="waktu">WAKTU MAKAN</label>
    <input class="form-input" type="time" id="waktu" name="waktu"
           value="<?= e($input['waktu']) ?>" required>
  </div>

  <div class="form-row">
    <label class="form-label" for="fish_id">IKAN YANG DIBERI MAKAN</label>
    <select class="form-select" id="fish_id" name="fish_id">
      <option value="">Semua / Umum</option>
      <?php foreach ($fishes as $f) { ?>
        <option value="<?= (int)$f['id'] ?>" <?= (string)$input['fish_id'] === (string)$f['id'] ? 'selected' : '' ?>>
          <?= e($f['nama']) ?> (<?= e($f['jenis']) ?>)
        </option>
      <?php } ?>
    </select>
  </div>

  <div class="form-row">
    <label class="form-label" for="jenis_pakan_pilih">JENIS PAKAN</label>
    <select class="form-select" id="jenis_pakan_pilih" name="jenis_pakan_pilih">
      <option value="">— Pilih jenis pakan —</option>
      <?php foreach ($jenis_pakan as $jp) { ?>
        <option value="<?= e($jp) ?>" <?= (!$jenis_manual && $input['jenis_pakan'] === $jp) ? 'selected' : '' ?>>
          <?= e($jp) ?>
        </option>
      <?php } ?>
      <option value="__baru" <?= $jenis_manual ? 'selected' : '' ?>>+ Jenis pakan baru (tulis sendiri)</option>
    </select>

    <input class="form-input" type="text" id="jenis_pakan_baru" name="jenis_pakan_baru"
           maxlength="60" placeholder="Tulis jenis pakan baru"
           value="<?= $jenis_manual ? e($input['jenis_pakan']) : '' ?>"
           style="margin-top:8px;<?= $jenis_manual ? '' : 'display:none;' ?>">
    <div class="form-hint">Pilih dari daftar, atau tulis jenis baru agar tersimpan untuk pemakaian berikutnya.</div>
  </div>

  <div class="form-row">
    <label class="form-label" for="jumlah">JUMLAH</label>
    <input class="form-input" type="number" step="0.01" min="0.01"
           id="jumlah" name="jumlah" value="<?= e($input['jumlah']) ?>" required>
    <div class="form-hint">Contoh 15.00</div>
  </div>

  <div class="form-row">
    <label class="form-label" for="satuan_pilih">SATUAN</label>
    <select class="form-select" id="satuan_pilih" name="satuan_pilih">
      <?php foreach ($satuan_pilihan as $st) { ?>
        <option value="<?= e($st) ?>" <?= (!$satuan_manual && $input['satuan'] === $st) ? 'selected' : '' ?>>
          <?= e($st) ?>
        </option>
      <?php } ?>
      <option value="__baru" <?= $satuan_manual ? 'selected' : '' ?>>+ Satuan lain (tulis sendiri)</option>
    </select>

    <input class="form-input" type="text" id="satuan_baru" name="satuan_baru"
           maxlength="20" placeholder="Tulis satuan lain"
           value="<?= $satuan_manual ? e($input['satuan']) : '' ?>"
           style="margin-top:8px;<?= $satuan_manual ? '' : 'display:none;' ?>">
  </div>
</div>

<div class="form-row">
  <label class="auth-check" style="margin:12px 0;">
    <input type="checkbox" name="habis" value="1" <?= $input['habis'] === '1' ? 'checked' : '' ?>>
    <span>Pakan habis dimakan dalam dua menit</span>
  </label>
</div>

<div class="form-row">
  <label class="form-label" for="catatan">CATATAN</label>
  <textarea class="form-textarea" id="catatan" name="catatan" maxlength="300" rows="3"
            data-counter="hitungCatatanPakan"><?= e($input['catatan']) ?></textarea>
  <div class="form-hint" id="hitungCatatanPakan">0 / 300 karakter</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  function pasang(pilihId, inputId) {
    var pilih = document.getElementById(pilihId);
    var input = document.getElementById(inputId);

    if (!pilih || !input) {
      return;
    }

    function segarkan() {
      var manual = pilih.value === '__baru';
      input.style.display = manual ? '' : 'none';

      if (manual) {
        input.focus();
      } else {
        input.value = '';
      }
    }

    pilih.addEventListener('change', segarkan);
  }

  pasang('jenis_pakan_pilih', 'jenis_pakan_baru');
  pasang('satuan_pilih', 'satuan_baru');
});
</script>
