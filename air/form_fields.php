<?php

?>
<div class="form-grid">
  <div class="form-row">
    <label class="form-label" for="tanggal">TANGGAL TES</label>
    <input class="form-input" type="date" id="tanggal" name="tanggal"
           value="<?= e($input['tanggal']) ?>" required>
  </div>

  <div class="form-row">
    <label class="form-label" for="ph">pH AIR (6.5 - 8.0)</label>
    <input class="form-input" type="number" step="0.1" min="0" max="14"
           id="ph" name="ph" value="<?= e($input['ph']) ?>" required>
    <div class="form-hint">Contoh 7.2</div>
  </div>

  <div class="form-row">
    <label class="form-label" for="suhu_c">SUHU (°C)</label>
    <input class="form-input" type="number" step="0.1" min="0" max="40"
           id="suhu_c" name="suhu_c" value="<?= e($input['suhu_c']) ?>" required>
    <div class="form-hint">Ideal 24 - 30°C</div>
  </div>

  <div class="form-row">
    <label class="form-label" for="ammonia">AMMONIA (ppm)</label>
    <input class="form-input" type="number" step="0.01" min="0"
           id="ammonia" name="ammonia" value="<?= e($input['ammonia']) ?>" required>
    <div class="form-hint">Harus di bawah 0.05</div>
  </div>

  <div class="form-row">
    <label class="form-label" for="nitrit">NITRIT (ppm)</label>
    <input class="form-input" type="number" step="0.01" min="0"
           id="nitrit" name="nitrit" value="<?= e($input['nitrit']) ?>" required>
    <div class="form-hint">Harus di bawah 0.10</div>
  </div>

  <div class="form-row">
    <label class="form-label" for="nitrat">NITRAT (ppm)</label>
    <input class="form-input" type="number" step="0.01" min="0"
           id="nitrat" name="nitrat" value="<?= e($input['nitrat']) ?>" required>
    <div class="form-hint">Sebaiknya di bawah 40</div>
  </div>
</div>

<div class="form-row" style="margin-top:16px;">
  <label class="form-label" for="catatan">CATATAN TAMBAHAN</label>
  <textarea class="form-textarea" id="catatan" name="catatan" maxlength="300" rows="3"
            data-counter="hitungCatatanAir"><?= e($input['catatan']) ?></textarea>
  <div class="form-hint" id="hitungCatatanAir">0 / 300 karakter</div>
</div>
