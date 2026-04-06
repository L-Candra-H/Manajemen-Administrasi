document.addEventListener('DOMContentLoaded', function () {
  // === Jumlah Anak ===
  const statusPernikahan = document.getElementById('statusPernikahan');
  const jumlahAnak = document.getElementById('jumlahAnak');

  if (statusPernikahan && jumlahAnak) {
    const toggleJumlahAnak = () => {
      const value = String(statusPernikahan.value);
      const disable = value === '' || value === '2'; // kosong atau belum menikah
      jumlahAnak.disabled = disable;
      if (disable) jumlahAnak.value = '';
    };
    statusPernikahan.addEventListener('change', toggleJumlahAnak);
    toggleJumlahAnak(); // inisialisasi saat halaman dimuat
  }

  // === STR ===
  const kategoriSelect = document.getElementById('kategoriKepegawaian');
  const nomorSTR = document.getElementById('nomorSTR');
  const berkasSTR = document.getElementById('berkasSTR');
  if (kategoriSelect && nomorSTR && berkasSTR) {
    const toggleSTRFields = () => {
      const disable = kategoriSelect.value === '' || ['4', '5'].includes(kategoriSelect.value);
      nomorSTR.disabled = disable;
      berkasSTR.disabled = disable;
      const preview = berkasSTR.previousElementSibling;
      if (disable) {
        nomorSTR.value = '';
        berkasSTR.value = '';
        if (preview?.tagName === 'A') preview.style.display = 'none';
      } else {
        if (preview?.tagName === 'A') preview.style.display = 'inline-block';
      }
    };
    kategoriSelect.addEventListener('change', toggleSTRFields);
    toggleSTRFields();
  }

  // === Golongan Pegawai ===
  const statusKepegawaian = document.getElementById('statusKepegawaian');
  const golonganSelect = document.getElementById('golonganPegawai');

  if (statusKepegawaian && golonganSelect) {
    const allOptions = Array.from(golonganSelect.querySelectorAll('option'));

    const filterGolongan = () => {
      const status = statusKepegawaian.value;
      // 1 = Kontrak, 2 = Tetap, 3 = Non Pegawai
      const isValid = status === '1' || status === '2' || status === '3';

      golonganSelect.disabled = !isValid;
      golonganSelect.innerHTML = '<option value="">-- Pilih --</option>';

      if (!isValid) return;

      allOptions.forEach(opt => {
        const id = parseInt(opt.value);
        if (isNaN(id)) return;

        // Kontrak (1) → Honorer (id=1)
        // Non Pegawai (3) → Honorer (id=1)
        // Tetap (2) → I A – IV D (id > 1)
        if ((status === '1' && id === 1) ||
          (status === '3' && id === 1) ||
          (status === '2' && id > 1)) {

          golonganSelect.appendChild(opt.cloneNode(true));
        }
      });
    };

    statusKepegawaian.addEventListener('change', filterGolongan);
    filterGolongan(); // inisialisasi saat halaman dimuat
  }

  // === SIP ===
  const sipFields = ['nomorSIP', 'mulaiSIP', 'berakhirSIP', 'berkasSIP'].map(id => document.getElementById(id));
  if (kategoriSelect) {
    const toggleSIPFields = () => {
      const disable = ['4', '5'].includes(kategoriSelect.value);
      sipFields.forEach(field => {
        if (field) {
          field.disabled = disable;
          if (disable) field.value = '';
        }
      });
    };
    kategoriSelect.addEventListener('change', toggleSIPFields);
    toggleSIPFields();
  }

  // === BPJS Ketenagakerjaan ===
  const bpjsKerja = document.getElementById('bpjsKetenagakerjaan');
  const noBpjsKerja = document.getElementById('noBpjsKetenagakerjaan');
  if (bpjsKerja && noBpjsKerja) {
    const toggleBpjsKerja = () => {
      const aktif = bpjsKerja.value === 'Ya';
      noBpjsKerja.disabled = !aktif;
      if (!aktif) noBpjsKerja.value = '';
    };
    bpjsKerja.addEventListener('change', toggleBpjsKerja);
    toggleBpjsKerja();
  }

  // === BPJS Kesehatan ===
  const bpjsKesehatan = document.getElementById('bpjsKesehatan');
  const statusBpjs = document.getElementById('statusBpjsKesehatan');
  const noBpjs = document.getElementById('noBpjsKesehatan');

  const keluargaFields = ['1', '2', '3', '4'].map(i => ({
    nama: document.getElementById(`keluargaNama${i}`),
    no: document.getElementById(`keluargaNo${i}`),
    status: document.getElementById(i === '1' ? 'statusSuamiIstri' : `statusAnak${i}`)
  }));

  const tambahanFields = ['1', '2', '3', '4'].map(i => ({
    nama: document.getElementById(`tambahanNama${i}`),
    no: document.getElementById(`tambahanNo${i}`),
    status: document.getElementById(`statusTambahan${i}`)
  }));

  const toggleFieldGroup = (group, enable) => {
    group.forEach(f => {
      if (f.nama) {
        f.nama.disabled = !enable;
        if (!enable) f.nama.value = '';
      }
      if (f.no) {
        f.no.disabled = !enable;
        if (!enable) f.no.value = '';
      }
      if (f.status) {
        f.status.disabled = !enable;
        if (!enable) f.status.value = '';
      }
    });
  };

  const toggleKeluargaTambahan = () => {
    if (!statusBpjs) return;
    const status = statusBpjs.value;
    const isKeluarga = ['Keluarga', 'Keluarga dan Tambahan'].includes(status);
    const isTambahan = status === 'Keluarga dan Tambahan';
    toggleFieldGroup(keluargaFields, isKeluarga);
    toggleFieldGroup(tambahanFields, isTambahan);
  };

  const toggleBpjsStatus = () => {
    const aktif = bpjsKesehatan?.value === 'Ya';
    if (statusBpjs) {
      statusBpjs.disabled = !aktif;
      if (!aktif) statusBpjs.value = '';
    }
    if (noBpjs) {
      noBpjs.disabled = !aktif;
      if (!aktif) noBpjs.value = '';
    }
    toggleKeluargaTambahan();
  };

  if (bpjsKesehatan && statusBpjs) {
    bpjsKesehatan.addEventListener('change', toggleBpjsStatus);
    statusBpjs.addEventListener('change', toggleKeluargaTambahan);
    toggleBpjsStatus();
  }

  // === Auto Nama Lengkap dari NIP ===
  const pegawaiMap = window.pegawaiMap || {};
  const nipSIP = document.getElementById('nipSIP');
  const namaSIP = document.getElementById('namaLengkapSIP');
  const nipSK = document.getElementById('nipSK');
  const namaSK = document.getElementById('namaLengkapSK');

  const updateNama = (nip, target) => {
    target.value = pegawaiMap[nip] || '';
  };

  nipSIP?.addEventListener('change', () => updateNama(nipSIP.value, namaSIP));
  nipSK?.addEventListener('change', () => updateNama(nipSK.value, namaSK));

  // === Masa Aktif Handler ===
  const hitungMasaAktif = (startId, endId, outputId) => {
    const startEl = document.getElementById(startId);
    const endEl = document.getElementById(endId);
    const outputEl = document.getElementById(outputId);
    if (!startEl || !endEl || !outputEl) return;

    const startDate = new Date(startEl.value);
    const endDate = new Date(endEl.value);

    if (!startEl.value || !endEl.value || isNaN(startDate) || isNaN(endDate)) {
      outputEl.textContent = 'Belum dihitung';
      outputEl.className = 'badge badge-secondary p-2';
      return;
    }

    const diffDays = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24));
    let statusLabel = '';
    let badgeClass = '';

    if (diffDays > 180) {
      statusLabel = 'AKTIF';
      badgeClass = 'badge badge-success';   // Hijau
    } else if (diffDays > 0) {
      statusLabel = 'Waktunya pembaruan';
      badgeClass = 'badge badge-warning text-dark'; // Kuning
    } else {
      statusLabel = 'Tidak Aktif';
      badgeClass = 'badge badge-danger';   // Merah
    }

    outputEl.innerHTML = `<span class="${badgeClass} p-2">${statusLabel} - ${diffDays} hari</span>`;
  };

  const bindMasaAktif = (prefix) => {
    const startId = `mulai${prefix}`;
    const endId = `berakhir${prefix}`;
    const outputId = `masaAktif${prefix}`;

    const startEl = document.getElementById(startId);
    const endEl = document.getElementById(endId);
    const outputEl = document.getElementById(outputId);

    if (!startEl || !endEl || !outputEl) return;

    [startEl, endEl].forEach(el => {
      el.addEventListener('change', () => hitungMasaAktif(startId, endId, outputId));
    });

    hitungMasaAktif(startId, endId, outputId); // inisialisasi saat halaman dimuat
  };

  // === Inisialisasi Global ===
  bindMasaAktif('SIP');
  bindMasaAktif('SK');

  // === Realtime Search di Tabel ===
  const searchBox = document.getElementById('searchBox');
  const tableBody = document.querySelector('tbody');

  if (searchBox && tableBody) {
    const originalRows = Array.from(tableBody.querySelectorAll('tr'));

    searchBox.addEventListener('keyup', function () {
      const keyword = this.value.toLowerCase();
      let found = false;

      // kosongkan isi tabel dulu
      tableBody.innerHTML = '';

      originalRows.forEach(row => {
        const text = row.innerText.toLowerCase();
        if (text.includes(keyword) || keyword === '') {
          tableBody.appendChild(row);
          found = true;
        }
      });

      // kalau tidak ada data sesuai
      if (!found && keyword !== '') {
        tableBody.innerHTML =
          '<tr><td colspan="99" class="text-center text-muted">Tidak ada data sesuai pencarian</td></tr>';
      }
    });
  }

  // === Status Pegawai ===
  const statusBadges = document.querySelectorAll('.badge-status');
  statusBadges.forEach(badge => {
    badge.addEventListener('click', function () {
      const nip = this.dataset.nip;
      const currentStatus = this.textContent.trim();
      openStatusModal(nip, currentStatus);
    });
  });

  // Fungsi untuk membuka modal status
  window.openStatusModal = function (nip, currentStatus) {
    // isi hidden input NIP
    const nipInput = document.getElementById('statusNIP');
    if (nipInput) {
      nipInput.value = nip;
    }

    // set dropdown status sesuai badge yang diklik
    const select = document.querySelector('#statusModal select[name="status_keaktifan"]');
    if (select) {
      select.value = currentStatus;
      toggleAlasan(currentStatus);
    }

    // tampilkan modal bootstrap
    const modalEl = document.getElementById('statusModal');
    if (modalEl) {
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    }
  };

  // Fungsi untuk menampilkan/menyembunyikan alasan
  window.toggleAlasan = function (status) {
    const alasanGroup = document.getElementById('alasanGroup');
    if (!alasanGroup) return;
    alasanGroup.style.display = (status === 'Non Aktif') ? 'block' : 'none';
  };

  // Validasi Panjang Nomor (exact atau range)
  function validateLength(id, length, label, min = null, max = null) {
    const input = document.getElementById(id);
    if (!input) return;

    input.addEventListener('input', () => {
      const val = input.value.length;

      // Jika ada min & max → validasi range
      if (min !== null && max !== null) {
        if (val > 0 && (val < min || val > max)) {
          input.setCustomValidity(label + " harus antara " + min + " sampai " + max + " digit");
        } else {
          input.setCustomValidity('');
        }
      } else {
        // Default: exact length
        if (val > 0 && val !== length) {
          input.setCustomValidity(label + " harus " + length + " digit");
        } else {
          input.setCustomValidity('');
        }
      }
    });
  }

  // NIK harus tepat 16 digit
  validateLength('nomor_induk_kependudukan', 16, 'NIK');

  // NIP boleh 4–6 digit
  validateLength('nomor_induk_pegawai', null, 'NIP', 4, 6);

  // BPJS TK harus tepat 11 digit
  validateLength('noBpjsKetenagakerjaan', 11, 'BPJS Ketenagakerjaan');

  // BPJS Kes harus tepat 13 digit
  validateLength('noBpjsKesehatan', 13, 'BPJS Kesehatan');

  // Tambahan Nomor harus tepat 13 digit
  validateLength('tambahanNo', 13, 'Tambahan Nomor');

});