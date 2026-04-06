<?php
$bpjs_ketenagakerjaan  = $pegawai['kepesertaan_BPJS_ketenagakerjaan'] ?? '';
$bpjs_kesehatan        = $pegawai['kepesertaan_BPJS_kesehatan'] ?? '';
$status_bpjs_kesehatan = $pegawai['status_BPJS_kesehatan'] ?? '';
?>

<div class="card border-warning mb-4">
  <div class="card-body">

    <!-- BPJS Ketenagakerjaan -->
    <div class="form-row">
      <div class="col">
        <label>Kepesertaan BPJS Ketenagakerjaan </label>
        <select name="kepesertaan_BPJS_ketenagakerjaan" class="form-control" id="bpjsKetenagakerjaan">
          <option value="">-- Pilih --</option>
          <option value="Ya" <?= $bpjs_ketenagakerjaan === 'Ya' ? 'selected' : '' ?>>Ya</option>
          <option value="Belum" <?= $bpjs_ketenagakerjaan === 'Belum' ? 'selected' : '' ?>>Belum</option>
        </select>
      </div>
      <div class="col">
        <label>Nomor Kartu BPJS Ketenagakerjaan </label>
        <input type="text"
               name="nomor_kartu_BPJS_ketenagakerjaan"
               maxlength="11"
               class="form-control"
               id="noBpjsKetenagakerjaan"
               value="<?= $pegawai['nomor_kartu_BPJS_ketenagakerjaan'] ?? '' ?>">
      </div>
    </div>

    <!-- BPJS Kesehatan -->
    <div class="form-row mt-3">
      <div class="col">
        <label>Kepesertaan BPJS Kesehatan</label>
        <select name="kepesertaan_BPJS_kesehatan" class="form-control" id="bpjsKesehatan">
          <option value="">-- Pilih --</option>
          <option value="Ya" <?= $bpjs_kesehatan === 'Ya' ? 'selected' : '' ?>>Ya</option>
          <option value="Belum" <?= $bpjs_kesehatan === 'Belum' ? 'selected' : '' ?>>Belum</option>
        </select>
      </div>
      <div class="col">
        <label>Nomor Kartu BPJS Kesehatan </label>
        <input type="text"
               name="nomor_kartu_BPJS_kesehatan"
               maxlength="13"
               class="form-control"
               id="noBpjsKesehatan"
               value="<?= $pegawai['nomor_kartu_BPJS_kesehatan'] ?? '' ?>">
      </div>
    </div>

    <!-- Status BPJS Kesehatan -->
    <div class="form-group mt-3">
      <label>Status BPJS Kesehatan</label>
      <select name="status_BPJS_kesehatan" class="form-control" id="statusBpjsKesehatan">
        <option value="">-- Pilih --</option>
        <?php foreach (['Sendiri', 'Keluarga', 'Keluarga dan Tambahan'] as $status): ?>
          <option value="<?= $status ?>" <?= $status_bpjs_kesehatan === $status ? 'selected' : '' ?>><?= $status ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- BPJS Keluarga -->
    <h6 class="text-secondary mt-4">BPJS Kesehatan Keluarga</h6>
    <div class="form-row mt-2">
      <div class="col">
        <label>Nama Lengkap Suami/Istri</label>
        <input type="text" name="nama_lengkap_suami_istri" id="keluargaNama1" class="form-control"
               value="<?= $bpjsKeluarga['nama_lengkap_suami_istri'] ?? '' ?>">
      </div>
      <div class="col">
        <label>Nomor Kartu BPJS Kesehatan Suami/Istri</label>
        <input type="text" name="nomor_kartu_BPJS_kesehatan_suami_istri" maxlength="13" id="keluargaNo1" class="form-control"
               value="<?= $bpjsKeluarga['nomor_kartu_BPJS_kesehatan_suami_istri'] ?? '' ?>">
      </div>
      <div class="col">
        <label>Status Kepesertaan</label>
        <select name="status_keaktifan_BPJS_kesehatan_suami_istri" id="statusSuamiIstri" class="form-control">
          <option value="">-- Pilih --</option>
          <option value="Aktif" <?= ($bpjsKeluarga['status_keaktifan_BPJS_kesehatan_suami_istri'] ?? '') === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
          <option value="Non Aktif" <?= ($bpjsKeluarga['status_keaktifan_BPJS_kesehatan_suami_istri'] ?? '') === 'Non Aktif' ? 'selected' : '' ?>>Non Aktif</option>
        </select>
      </div>
    </div>

    <?php foreach (['I','II','III'] as $idx => $label): $i = $idx+2; ?>
      <div class="form-row mt-2">
        <div class="col">
          <label>Nama Lengkap Anak <?= $label ?></label>
          <input type="text" name="nama_lengkap_anak_<?= $label ?>" id="keluargaNama<?= $i ?>" class="form-control"
                 value="<?= $bpjsKeluarga["nama_lengkap_anak_$label"] ?? '' ?>">
        </div>
        <div class="col">
          <label>Nomor Kartu BPJS Kesehatan Anak <?= $label ?></label>
          <input type="text" name="nomor_kartu_BPJS_kesehatan_anak_<?= $label ?>" maxlength="13" id="keluargaNo<?= $i ?>" class="form-control"
                 value="<?= $bpjsKeluarga["nomor_kartu_BPJS_kesehatan_anak_$label"] ?? '' ?>">
        </div>
        <div class="col">
          <label>Status Kepesertaan</label>
          <select name="status_keaktifan_BPJS_kesehatan_anak_<?= $label ?>" id="statusAnak<?= $i ?>" class="form-control">
            <option value="">-- Pilih --</option>
            <option value="Aktif" <?= ($bpjsKeluarga["status_keaktifan_BPJS_kesehatan_anak_$label"] ?? '') === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="Non Aktif" <?= ($bpjsKeluarga["status_keaktifan_BPJS_kesehatan_anak_$label"] ?? '') === 'Non Aktif' ? 'selected' : '' ?>>Non Aktif</option>
          </select>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- BPJS Tambahan -->
    <h6 class="text-secondary mt-4">BPJS Kesehatan Tambahan</h6>
    <?php foreach (['I','II','III','IV'] as $idx => $label): $i = $idx+1; ?>
      <div class="form-row mt-2">
        <div class="col">
          <label>Nama Lengkap Tambahan <?= $label ?></label>
          <input type="text" name="nama_lengkap_tambahan_<?= $label ?>" id="tambahanNama<?= $i ?>" class="form-control"
                 value="<?= $bpjsTambahan["nama_lengkap_tambahan_$label"] ?? '' ?>">
        </div>
        <div class="col">
          <label>Nomor Kartu BPJS Kesehatan Tambahan <?= $label ?></label>
          <input type="text" name="nomor_kartu_BPJS_kesehatan_tambahan_<?= $label ?>" maxlength="13" id="tambahanNo<?= $i ?>" class="form-control"
                 value="<?= $bpjsTambahan["nomor_kartu_BPJS_kesehatan_tambahan_$label"] ?? '' ?>">
        </div>
        <div class="col">
          <label>Status Kepesertaan</label>
          <select name="status_keaktifan_BPJS_kesehatan_tambahan_<?= $label ?>" id="statusTambahan<?= $i ?>" class="form-control">
            <option value="">-- Pilih --</option>
            <option value="Aktif" <?= ($bpjsTambahan["status_keaktifan_BPJS_kesehatan_tambahan_$label"] ?? '') === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="Non Aktif" <?= ($bpjsTambahan["status_keaktifan_BPJS_kesehatan_tambahan_$label"] ?? '') === 'Non Aktif' ? 'selected' : '' ?>>Non Aktif</option>
          </select>
        </div>
      </div>
    <?php endforeach; ?>