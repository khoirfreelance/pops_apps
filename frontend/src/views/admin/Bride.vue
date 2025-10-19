<template>
  <div class="bride-wrapper">
    <!-- 🔄 Spinner Overlay -->
    <transition name="fade">
      <div
        v-if="isLoading"
        class="spinner-overlay d-flex justify-content-center align-items-center"
      >
        <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </transition>

    <!-- Header -->
    <HeaderAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

    <div class="d-flex flex-column flex-md-row">
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" />

      <!-- Main Content -->
      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <div
          class="flex-grow-1 p-4 bg-light container-fluid"
          :style="{
            backgroundImage: background ? `url(${background})` : 'none',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            backgroundAttachment: 'fixed',
          }"
        >
          <!-- Welcome Card -->
          <div class="card welcome-card shadow-sm mb-4 border-0">
            <div
              class="card-body d-flex flex-column flex-md-row align-items-start py-0 justify-content-between"
            >
              <!-- Kiri: Teks Welcome -->
              <div class="text-start">
                <div class="my-3">
                  <h2 class="fw-bold mt-3 mb-0 text-white">Data Calon Pengantin</h2>
                  <small class="text-white"> Daftar kunjungan calon pengantin </small>
                </div>
                <div class="text-white my-0">
                  <ul class="list-unstyled">
                    <!-- Jadwal intervensi -->
                    <li v-if="pendingCount > 0" class="d-flex align-items-center mb-2">
                      <div
                        class="bg-white rounded-circle d-flex align-items-center justify-content-center me-2"
                        style="width: 28px; height: 28px;"
                      >
                        <i class="bi bi-calendar2-check text-primary fs-6"></i>
                      </div>
                      <p class="mb-0 small">
                        Anda memiliki
                        <router-link
                          to="/admin/jadwal"
                          class="fw-bold text-light text-decoration-none"
                        >
                          1 jadwal intervensi
                        </router-link>
                        hari ini.
                      </p>
                    </li>

                    <!-- Data pending -->
                    <li v-if="pendingCount > 0" class="d-flex align-items-center">
                      <div
                        class="bg-white rounded-circle d-flex align-items-center justify-content-center me-2"
                        style="width: 28px; height: 28px;"
                      >
                        <i class="bi bi-upload text-primary fs-6"></i>
                      </div>
                      <p class="mb-0 small">
                        Anda memiliki
                        <a
                          href="javascript:void(0)"
                          class="fw-bold text-white text-decoration-none"
                          @click="toggleExpandPending"
                        >
                          {{ pendingCount }} data pending
                        </a>
                        belum terkirim.
                      </p>
                    </li>
                  </ul>
                </div>
                <nav aria-label="breadcrumb" class="mt-auto mb-2">
                  <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                      <router-link to="/admin" class="text-decoration-none text-white-50">
                        Beranda
                      </router-link>
                    </li>
                    <li class="breadcrumb-item active text-white" aria-current="page">
                      Calon Pengantin
                    </li>
                  </ol>
                </nav>
              </div>

              <!-- Kanan: Gambar -->
              <div class="mt-3 mt-md-0">
                <img src="/src/assets/admin.png" alt="Welcome" class="img-fluid welcome-img" />
              </div>
            </div>
          </div>

          <!-- Input Pencarian -->
          <div class="row py-3">
            <!-- 🔍 Input Pencarian & Tombol Aksi -->
            <div class="row align-items-center mb-3 g-2">
              <!-- Kolom Kiri: Input NIK + Tombol Cari -->
              <div class="col-md-6 col-sm-12 d-flex align-items-center">
                <input
                  type="text"
                  class="form-control w-50 me-3 flex-grow-1"
                  placeholder="Masukkan NIK"
                  v-model="searchNIK"
                />
                <button class="btn btn-gradient px-5" @click="cariData">
                  <i class="fa fa-search me-1"></i> Cari
                </button>
              </div>

              <!-- Kolom Kanan: Tombol Tambah & Import -->
              <div class="col-md-6 col-sm-12 d-flex justify-content-md-end justify-content-start mt-2 mt-md-0">
                <button
                  type="button"
                  class="btn btn-primary me-2"
                  @click="toggleExpandForm"
                >
                  <i :class="showForm ? 'bi bi-dash-square me-1' : 'bi bi-plus-square me-1'"></i>
                  {{ showForm ? 'Tutup Form' : 'Tambah Data' }}
                </button>

                <button
                  class="btn btn-outline-primary"
                  data-bs-toggle="modal"
                  data-bs-target="#modalImport_kunjungan"
                >
                  <i class="bi bi-filetype-csv me-1"></i> Unggah Data
                </button>
              </div>
            </div>

            <!-- Alert jika data ditemukan / tidak -->
            <div v-if="showForm">
              <div v-if="found" class="text-danger fw-semibold mb-3">Data Ditemukan</div>
              <div v-else-if="notFound" class="text-danger fw-semibold small mb-3">
                Data dengan NIK tersebut tidak ditemukan.
              </div>
            </div>

            <!-- FORM DATA CATIN & PENDAMPINGAN -->
            <transition name="fade">
              <form class="row g-4" v-if="showForm">

                <!-- ===================== -->
                <!-- DATA CATIN -->
                <!-- ===================== -->
                <div class="col-12">
                  <h4 class="fw-bold text-primary mb-3">
                    <i class="bi bi-person-hearts me-2"></i>Data Calon Pengantin
                  </h4>
                </div>

                <!-- PEREMPUAN -->
                <div class="col-md-6">
                  <h5 class="fw-semibold text-muted mb-3">
                    <i class="bi bi-gender-female me-2"></i>Perempuan
                  </h5>

                  <label class="form-label">Nama Perempuan</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input v-model="form.nama_perempuan" class="form-control" />
                  </div>

                  <label class="form-label">NIK Perempuan</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                    <input v-model="form.nik_perempuan" maxlength="16"
                          @input="form.nik_perempuan = form.nik_perempuan.replace(/\D/g, '')"
                          class="form-control" />
                  </div>

                  <label class="form-label">Pekerjaan</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                    <input v-model="form.pekerjaan_perempuan" class="form-control" />
                  </div>

                  <label class="form-label">Tanggal Lahir</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                    <input v-model="form.tgl_lahir_perempuan" type="date"
                          class="form-control" @change="hitungUsia('perempuan')" />
                  </div>

                  <label class="form-label">Usia</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-hourglass-split"></i></span>
                    <input v-model="form.usia_perempuan" type="number"
                          class="form-control" readonly />
                  </div>

                  <label class="form-label">No. HP</label>
                  <div class="input-group shadow-sm mb-4">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input v-model="form.hp_perempuan" class="form-control" />
                  </div>
                </div>

                <!-- PRIA -->
                <div class="col-md-6">
                  <h5 class="fw-semibold text-muted mb-3">
                    <i class="bi bi-gender-male me-2"></i>Pria
                  </h5>

                  <label class="form-label">Nama Pria</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input v-model="form.nama_pria" class="form-control" />
                  </div>

                  <label class="form-label">NIK Pria</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                    <input v-model="form.nik_pria" maxlength="16"
                          @input="form.nik_pria = form.nik_pria.replace(/\D/g, '')"
                          class="form-control" />
                  </div>

                  <label class="form-label">Pekerjaan</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                    <input v-model="form.pekerjaan_pria" class="form-control" />
                  </div>

                  <label class="form-label">Tanggal Lahir</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                    <input v-model="form.tgl_lahir_pria" type="date"
                          class="form-control" @change="hitungUsia('pria')" />
                  </div>

                  <label class="form-label">Usia</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-hourglass-split"></i></span>
                    <input v-model="form.usia_pria" type="number" class="form-control" readonly />
                  </div>

                  <label class="form-label">No. HP</label>
                  <div class="input-group shadow-sm mb-4">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input v-model="form.hp_pria" class="form-control" />
                  </div>
                </div>

                <!-- INFORMASI TAMBAHAN -->
                <div class="col-md-6">
                  <label class="form-label">Tanggal Rencana Nikah</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                    <input type="date" v-model="form.tgl_rencana_menikah" class="form-control" />
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Rencana Tempat Tinggal</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-house-door"></i></span>
                    <input v-model="form.rencana_tinggal" class="form-control" />
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Pernikahan ke</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-calculator"></i></span>
                    <input type="number" v-model="form.pernikahan_ke" class="form-control" />
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">RT / RW</label>
                  <div class="d-flex gap-3">
                    <div class="input-group shadow-sm">
                      <span class="input-group-text">RT</span>
                      <input type="number" v-model="form.rt" class="form-control" min="0" />
                    </div>
                    <div class="input-group shadow-sm">
                      <span class="input-group-text">RW</span>
                      <input type="number" v-model="form.rw" class="form-control" min="0" />
                    </div>
                  </div>
                </div>

                <hr class="mt-4" />

                <!-- ===================== -->
                <!-- DATA PENDAMPINGAN -->
                <!-- ===================== -->
                <div class="col-12">
                  <h4 class="fw-bold text-primary mb-3">
                    <i class="bi bi-clipboard-heart me-2 text-success"></i>Data Pendampingan Catin
                  </h4>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Tanggal Pemeriksaan Kesehatan</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                    <input type="date" v-model="form.tgl_pemeriksaan" class="form-control" />
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Tanggal Pendampingan</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                    <input type="date" v-model="form.tgl_pendampingan" class="form-control" />
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Dampingan Ke</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-calculator"></i></span>
                    <input type="number" readonly v-model="form.dampingan_ke" class="form-control" />
                  </div>
                </div>

                <div class="col-md-3" v-for="(label, field) in { bb:'Berat Badan (kg)', tb:'Tinggi Badan (cm)', lila:'Lingkar Lengan (cm)', hb:'Hb' }" :key="field">
                  <label class="form-label">{{ label }}</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-activity"></i></span>
                    <input type="number" v-model="form[field]" @input="hitungKondisiCatin" class="form-control" />
                  </div>
                </div>

                <!-- ===================== -->
                <!-- STATUS DAN KONDISI CATIN -->
                <!-- ===================== -->
                <div class="col-md-4" v-for="(label, field) in { imt:'Indeks Massa Tubuh', status_hb:'Status HB', status_gizi:'Status Gizi' }" :key="field">
                  <label class="form-label">{{ label }}</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-file-medical"></i></span>
                    <input type="text" readonly v-model="form[field]" class="form-control" />
                  </div>
                </div>

                <div class="col-md-6" v-for="(label, field) in { catin_terpapar_rokok:'Terpapar Rokok?', catin_ttd:'Catin TTD?' }" :key="field">
                  <label class="form-label">{{ label }}</label>
                  <div class="d-flex align-items-center gap-3 p-2">
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" value="ya" v-model="form[field]" :id="field + '_ya'" />
                      <label class="form-check-label" :for="field + '_ya'">Ya</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" value="tidak" v-model="form[field]" :id="field + '_tidak'" />
                      <label class="form-check-label" :for="field + '_tidak'">Tidak</label>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Riwayat Penyakit?</label>
                  <div class="d-flex align-items-center gap-3 p-2">
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" @click="isRiwayat = false" type="radio" value="ya" v-model="form.punya_riwayat_penyakit" id="riwayat_ya" />
                      <label class="form-check-label" for="riwayat_ya">Ya</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" @click="isRiwayat = true" type="radio" value="tidak" v-model="form.punya_riwayat_penyakit" id="riwayat_tidak" />
                      <label class="form-check-label" for="riwayat_tidak">Tidak</label>
                    </div>
                  </div>

                  <label class="form-label">Keterangan Riwayat Penyakit</label>
                  <div class="input-group shadow-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-journal-medical"></i></span>
                    <textarea rows="5" :readonly="isRiwayat" v-model="form.riwayat_penyakit" class="form-control"></textarea>
                  </div>
                </div>

                <div class="col-md-6">
                  <div v-for="(label, field) in { fasilitas_rujukan:'Fasilitas Rujukan', edukasi:'Edukasi', pmt:'PMT' }" :key="field" class="mb-3">
                    <label class="form-label fw-semibold">{{ label }}</label>
                    <div class="d-flex align-items-center ms-2">
                      <div class="form-check me-3">
                        <input class="form-check-input" type="radio" :id="field + '_ya'" :name="field" value="ya" v-model="form[field]" />
                        <label class="form-check-label" :for="field + '_ya'">Ya</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" :id="field + '_tidak'" :name="field" value="tidak" v-model="form[field]" />
                        <label class="form-check-label" :for="field + '_tidak'">Tidak</label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- BUTTONS -->
                <div class="col-12 d-flex justify-content-between mt-4">
                  <button class="btn btn-outline-secondary rounded-pill px-4" @click="resetForm">
                    <i class="bi bi-x-circle me-2"></i>Batal
                  </button>
                  <button class="btn btn-success rounded-pill px-4"
                          @click="modalMode === 'add' ? saveData() : updateData()">
                    <i class="bi bi-save me-2"></i>{{ modalMode === 'add' ? 'Simpan' : 'Perbarui' }}
                  </button>
                </div>

              </form>
            </transition>

          </div>

          <!-- FILTER CATIN -->
          <div class="bg-light rounded shadow-sm p-3 mt-3 container-fluid">
            <form class="row g-3" @submit.prevent="applyFilter_catin">
              <!-- Baris utama filter -->
              <div class="row mt-2">
                <!-- Status HB -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold text-primary">Status HB</label>
                  <div class="row">
                    <div
                      class="col-md-6"
                      v-for="item in ['Normal','Anemia Ringan','Anemia Sedang','Anemia Berat']"
                      :key="item"
                    >
                      <div class="form-check mb-2">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          :id="'hb_'+item"
                          :value="item"
                          v-model="advancedFilter_catin.status_hb_list"
                        />
                        <label class="form-check-label" :for="'hb_'+item">{{ item }}</label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Status Gizi -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold text-primary">Status Gizi</label>
                  <div class="row">
                    <div
                      class="col-md-6"
                      v-for="item in ['Normal','KEK']"
                      :key="item"
                    >
                      <div class="form-check mb-2">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          :id="'gizi_'+item"
                          :value="item"
                          v-model="advancedFilter_catin.status_gizi_list"
                        />
                        <label class="form-check-label" :for="'gizi_'+item">{{ item }}</label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tombol -->
              <div class="col-md-12 mt-3">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-search"></i> Terapkan Filter
                </button>
                <button type="button" class="btn btn-secondary float-end" @click="resetFilter_catin">
                  <i class="bi bi-arrow-clockwise"></i> Reset
                </button>
              </div>
            </form>
          </div>

          <!-- Table -->
          <div class="container-fluid bg-light rounded shadow-sm p-3 mt-3">
            <div v-if="isPendingOpen" id="dataPending" class="card modern-card mt-4">
              <div class="card-body bg-additional rounded">
                <div class="d-flex justify-content-between">
                  <h5 class="fw-bold mb-2 text-white">Data Pending</h5>
                  <button @click="toggleExpandPending" class="btn-close"></button>
                </div>
                <EasyDataTable
                  :headers="headers_pending"
                  :items="bridePending"
                >
                  <template #item-action="{ id }">
                    <button
                      class="btn btn-secondary m-2"
                      @click="updateBride(id)"
                      style="font-size: small;"
                    >
                      <i class="fa fa-pen"></i>
                    </button>
                  </template>
                </EasyDataTable>
              </div>
            </div>
            <div class="card modern-card mt-4">
              <div class="datatable-responsive">
                <EasyDataTable
                  :headers="visibleHeaders"
                  :items="filteredCatin"
                  :searchable="true"
                  :pagination="true"
                >
                <!-- STATUS HB -->
                  <template #item-status_hb="{ status_hb }">
                    <span
                      class="badge px-3 py-2 text-white"
                      :class="{
                        'bg-danger': status_hb === 'Anemia Ringan' || status_hb === 'Anemia Sedang' || status_hb === 'Anemia Berat',
                        'bg-success': status_hb === 'Normal'
                      }"
                    >
                      {{ status_hb }}
                    </span>
                  </template>

                  <!-- STATUS GIZI -->
                  <template #item-status_gizi="{ status_gizi }">
                    <span
                      class="badge px-3 py-2 text-white"
                      :class="{
                        'bg-danger': status_gizi === 'KEK',
                        'bg-success': status_gizi === 'Normal'
                      }"
                    >
                      {{ status_gizi }}
                    </span>
                  </template>
                </EasyDataTable>
              </div>
            </div>
          </div>
        </div>
        <CopyRight class="mt-auto" />
      </div>
    </div>
  </div>

  <!-- Modal Import -->
  <div class="modal fade" id="modalImport" ref="modalImport" tabindex="-1">
    <div class="modal-dialog">
      <div
        class="modal-content"
        :style="{
          backgroundImage: background ? `url(${background})` : 'none',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          backgroundAttachment: 'fixed',
        }"
      >
        <div class="modal-header text-primary bg-light border-0 rounded-top-4">
          <h5 class="modal-title">Import File Pendampingan TPK</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning p-2">
            <ul>
              <li>Import data untuk kunjungan kehamilan oleh pendampingan TPK</li>
              <li>Pastikan data yang diimport, berformat xlxs</li>
              <li>Pastikan data sudah lengkap sebelum di import</li>
            </ul>
          </div>
          <input type="file" class="form-control" ref="csvFile" accept=".csv" />
        </div>
        <div class="modal-footer border-0 d-flex justify-content-between">
          <button class="btn btn-light-pill px-4" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i> Batal
          </button>
          <button class="btn btn-success rounded-pill px-4" @click="handleImport">
            <i class="bi bi-upload me-2"></i> Unggah
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Success -->
  <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content border-0 shadow-lg rounded-4"
        :style="{
          backgroundImage: background ? `url(${background})` : 'none',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          backgroundAttachment: 'fixed',
        }"
      >
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title">✅ Berhasil</h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body text-center">
          <p class="mb-0">Data Anak berhasil disimpan ke <strong>localStorage</strong>.</p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">
            OK
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Loader Overlay with Animated Progress -->
  <div
    v-if="isLoadingImport"
    class="position-fixed top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark bg-opacity-50"
    style="z-index: 2000"
  >
    <div class="w-50">
      <div class="progress" style="height: 1.8rem; border-radius: 1rem; overflow: hidden">
        <div
          class="progress-bar progress-bar-striped progress-bar-animated"
          role="progressbar"
          :style="{ width: importProgress + '%' }"
          :data-progress="progressLevel"
        >
          <span class="fw-bold">{{ animatedProgress }}%</span>
        </div>
      </div>
    </div>
    <p class="text-white mt-3">Mengimpor data... {{ currentRow }}/{{ totalRows }} baris</p>
  </div>

  <!-- Modal Error -->
  <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header bg-danger text-white rounded-top-4">
          <h5 class="modal-title">Error</h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body text-center">
          <p class="mb-0">{{ errorMessage || 'Terjadi kesalahan yang tidak diketahui.' }}</p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">
            OK
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import EasyDataTable from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'
import { Modal } from 'bootstrap'
import axios from 'axios'

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Bride',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, EasyDataTable },
  data() {
    return {
      isPendingOpen: false,
      isRiwayat:true,
      searchNIK: '',
      showForm: false,
      found: false,
      notFound: false,
      isLoading: true,
      isCollapsed: false,
      importTitle: 'Import File',
      showAlert: false,
      isLoadingImport: false,
      importProgress: 0,
      animatedProgress: 0,
      currentRow: 0,
      totalRows: 1, // default 1 agar tidak bagi 0
      catatan_list:[],
      penyakit_list:[],
      visibleColumns: [
        'nama_perempuan',
        'nama_pria',
        'tgl_rencana_menikah',
        'rencana_tinggal',
        'tgl_daftar',
        'bb',
        'tb',
        'status_gizi',
        'status_hb'
      ],
      form: {
        id:null,
        // ========== DATA CATIN PEREMPUAN ==========
        nama_perempuan: '',
        nik_perempuan: '',
        pekerjaan_perempuan: '',
        tgl_lahir_perempuan: '',
        usia_perempuan: null,
        hp_perempuan: '',

        // ========== DATA CATIN PRIA ==========
        nama_pria: '',
        nik_pria: '',
        pekerjaan_pria: '',
        tgl_lahir_pria: '',
        usia_pria: null,
        hp_pria: '',

        // ========== DATA PERNIKAHAN ==========
        tgl_rencana_menikah: '',
        rencana_tinggal: '',
        pernikahan_ke: '',
        rt:'',
        rw:'',

        // ========== DATA PENDAMPINGAN ==========
        dampingan_ke: '',
        tgl_pendampingan: '',
        tgl_pemeriksaan: '',
        bb: null,
        tb: null,
        lila: null,
        hb: null,
        imt:null,

        // ========== STATUS DAN KONDISI CATIN ==========
        status_hb: '', // "Anemia" | "Normal"
        status_gizi: '', // "KEK" | "Tidak KEK"
        catin_terpapar_rokok: '', // "ya" | "tidak"
        catin_ttd: '', // "ya" | "tidak"
        punya_riwayat_penyakit: '', // "ya" | "tidak"
        riwayat_penyakit: '',

        // ========== FASILITAS DAN EDUKASI ==========
        fasilitas_rujukan: '',
        edukasi: '',
        pmt: '',
      },
      bride: [],
      bridePending: [], // <--- tambahkan ini
      headers: [
        // --- Data Perempuan ---
        { text: 'Nama Perempuan', value: 'nama_perempuan' },
        { text: 'NIK Perempuan', value: 'nik_perempuan' },
        { text: 'Pekerjaan Perempuan', value: 'pekerjaan_perempuan' },
        { text: 'Tanggal Lahir Perempuan', value: 'tgl_lahir_perempuan' },
        { text: 'Usia Perempuan', value: 'usia_perempuan' },
        { text: 'HP Perempuan', value: 'hp_perempuan' },

        // --- Data Pria ---
        { text: 'Nama Pria', value: 'nama_pria' },
        { text: 'NIK Pria', value: 'nik_pria' },
        { text: 'Pekerjaan Pria', value: 'pekerjaan_pria' },
        { text: 'Tanggal Lahir Pria', value: 'tgl_lahir_pria' },
        { text: 'Usia Pria', value: 'usia_pria' },
        { text: 'HP Pria', value: 'hp_pria' },

        // --- Data Pernikahan ---
        { text: 'Tanggal Daftar', value: 'tgl_daftar' },
        { text: 'Tanggal Rencana Nikah', value: 'tgl_rencana_menikah' },
        { text: 'Rencana Tempat Tinggal', value: 'rencana_tinggal' },

        // --- Data Pendampingan ---
        { text: 'Tanggal Pemeriksaan', value: 'tgl_pemeriksaan' },
        { text: 'Tanggal Pendampingan', value: 'tgl_pendampingan' },
        { text: 'Dampingan Ke', value: 'dampingan_ke' },
        { text: 'Berat Badan (kg)', value: 'bb' },
        { text: 'Tinggi Badan (cm)', value: 'tb' },
        { text: 'Lingkar Lengan Atas (cm)', value: 'lila' },
        { text: 'Hemoglobin (g/dL)', value: 'hb' },
        { text: 'Status HB', value: 'status_hb' },
        { text: 'Status Gizi', value: 'status_gizi' },
        { text: 'Terpapar Rokok', value: 'catin_terpapar_rokok' },
        { text: 'TTD', value: 'catin_ttd' },
        { text: 'Punya Riwayat Penyakit', value: 'punya_riwayat_penyakit' },
        { text: 'Riwayat Penyakit', value: 'riwayat_penyakit' },
        { text: 'Fasilitas Rujukan', value: 'fasilitas_rujukan' },
        { text: 'Edukasi', value: 'edukasi' },
        { text: 'PMT', value: 'pmt' },

      ],
      headers_pending:[
        // --- Data Perempuan ---
        { text: 'Nama Perempuan', value: 'nama_perempuan' },

        // --- Data Pria ---
        { text: 'Nama Pria', value: 'nama_pria' },

        // --- Data Pernikahan ---
        { text: 'Tanggal Rencana Nikah', value: 'tgl_rencana_menikah' },
        { text: 'Rencana Tempat Tinggal', value: 'rencana_tinggal' },

        // --- Data Pendampingan ---
        { text: 'Tanggal Pendampingan', value: 'tgl_pendampingan' },
        { text: 'Dampingan Ke', value: 'dampingan_ke' },

        { text: 'Action', value: 'action' },
      ],
      advancedFilter_catin: {
        status_hb_list: [],
        status_gizi_list: [],
      },
      appliedFilter_catin: {}, // hasil filter aktif
    }
  },
  computed: {
    background() {
      try {
        const config = JSON.parse(localStorage.getItem('siteConfig'))
        return config?.background || null
      } catch {
        return null
      }
    },
    filteredCatin() {
      return this.bride.filter((item) => {
        // === Filter HB ===
        const matchHB =
          !this.appliedFilter_catin.status_hb_list?.length ||
          this.appliedFilter_catin.status_hb_list.includes(item.status_hb)

        // === Filter Gizi ===
        const matchGizi =
          !this.appliedFilter_catin.status_gizi_list?.length ||
          this.appliedFilter_catin.status_gizi_list.includes(item.status_gizi)

        // === Hasil Akhir ===
        return matchHB && matchGizi
      })
    },
    visibleHeaders() {
      return this.headers.filter((h) => this.visibleColumns.includes(h.value))
    },
    pendingCount() {
      return this.bridePending.length
    },
  },
  methods: {
    async updateData() {
      try {
        // 🔍 Debug payload sebelum dikirim
        console.log("Payload dikirim ke API:", this.form);

        // Kirim PUT request ke backend
        const res = await axios.put(
          `http://localhost:8000/api/bride/${this.form.id}`,
          this.form,
          {
            headers: {
              Accept: "application/json",
              Authorization: `Bearer ${localStorage.getItem("token")}`,
            },
          }
        );

        console.log("Respon update:", res.data);

        // ✅ Tampilkan modal sukses
        this.showSuccess('Data berhasil disimpan')

        // 🚪 Tutup form dan refresh data
        this.resetForm();
        this.showForm = false;
        this.isPendingOpen = false;

        // Refresh data list
        this.getPendingData();
        this.loadBride();

      } catch (err) {
        console.error("Gagal update data catin:", err);
        this.showError("Gagal memperbarui data. Coba lagi nanti.");
        //alert("Gagal memperbarui data. Coba lagi nanti.");
      }
    },
    async updateBride(id) {
      this.modalMode = "update"
      this.showForm = true

      try {
        const res = await axios.get(`http://localhost:8000/api/bride/${id}/pending`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        const data = res.data

        // 🔄 Mapping hasil response ke struktur form di frontend
        this.form = {
          id: data.id ?? null,

          // ========== DATA CATIN PEREMPUAN ==========
          nama_perempuan: data.nama_perempuan ?? '',
          nik_perempuan: data.nik_perempuan ?? '',
          pekerjaan_perempuan: data.pekerjaan_perempuan ?? '',
          tgl_lahir_perempuan: data.tgl_lahir_perempuan ?? '',
          usia_perempuan: data.usia_perempuan ?? null,
          hp_perempuan: data.hp_perempuan ?? '',

          // ========== DATA CATIN PRIA ==========
          nama_pria: data.nama_pria ?? '',
          nik_pria: data.nik_pria ?? '',
          pekerjaan_pria: data.pekerjaan_pria ?? '',
          tgl_lahir_pria: data.tgl_lahir_pria ?? '',
          usia_pria: data.usia_pria ?? null,
          hp_pria: data.hp_pria ?? '',

          // ========== DATA PERNIKAHAN ==========
          tgl_rencana_menikah: data.tgl_rencana_menikah ?? '',
          rencana_tinggal: data.rencana_tinggal ?? '',

          // ========== DATA PENDAMPINGAN ==========
          dampingan_ke: data.dampingan_ke ?? '',
          tgl_pendampingan: data.tgl_pendampingan ?? '',
          bb: data.bb ?? null,
          tb: data.tb ?? null,
          lila: data.lila ?? null,
          hb: data.hb ?? null,
          imt: data.imt ?? null,

          // ========== STATUS DAN KONDISI CATIN ==========
          status_hb: data.status_hb ?? '',
          status_gizi: data.status_gizi ?? '',
          catin_terpapar_rokok: data.catin_terpapar_rokok ?? '',
          //catin_ttd: data.catin_ttd ?? '',
          punya_riwayat_penyakit: data.punya_riwayat_penyakit ?? '',
          riwayat_penyakit: data.riwayat_penyakit ?? '',

          // ========== FASILITAS DAN EDUKASI ==========
          fasilitas_rujukan: data.fasilitas_rujukan ?? '',
          edukasi: data.edukasi ?? '',
          pmt: data.pmt ?? '',
        }

        // tampilkan form edit
        this.showForm = true
      } catch (err) {
        console.error("❌ Gagal load data bride pending:", err)
      }
    },
    async getPendingData() {
      try {
        const res = await axios.get("http://localhost:8000/api/bride/pending", {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        this.bridePending = res.data.map(item => {
          const catin = item.catin || {}
          const pasangan = catin.pasangan || {}
          const pend = (catin.pendampingan && catin.pendampingan[0]) || {}

          return {
            id: item.id,
            id_catin: item.id_catin,
            tgl_daftar: item.tgl_daftar,
            tgl_rencana_menikah: item.tgl_rencana_menikah,
            rencana_tinggal: item.rencana_tinggal,

            // --- Data Perempuan ---
            nama_perempuan: catin.peran === 'istri' ? catin.nama : pasangan.nama,
            nik_perempuan: catin.peran === 'istri' ? catin.nik : pasangan.nik,
            usia_perempuan: catin.peran === 'istri' ? catin.usia : pasangan.usia,
            pekerjaan_perempuan: catin.peran === 'istri' ? catin.pekerjaan : pasangan.pekerjaan,

            // --- Data Pria ---
            nama_pria: catin.peran === 'suami' ? catin.nama : pasangan.nama,
            nik_pria: catin.peran === 'suami' ? catin.nik : pasangan.nik,
            usia_pria: catin.peran === 'suami' ? catin.usia : pasangan.usia,
            pekerjaan_pria: catin.peran === 'suami' ? catin.pekerjaan : pasangan.pekerjaan,

            // --- Data Pendampingan ---
            tgl_pendampingan: pend.tgl_pendampingan || '-',
            dampingan_ke: pend.dampingan_ke || '-',

            // --- Untuk Action Button ---
            tipe: catin.peran,
          }
        })

      } catch (err) {
        this.showError('Error Ambil Data', err)
      }
    },
    hitungKondisiCatin() {
      const bb = parseFloat(this.form.bb)
      const tb = parseFloat(this.form.tb)
      const hb = parseFloat(this.form.hb)
      const lila = parseFloat(this.form.lila)

      // =====================
      // 1️⃣ Hitung IMT
      // =====================
      if (bb > 0 && tb > 0) {
        const tinggiMeter = tb / 100
        const imt = bb / (tinggiMeter * tinggiMeter)
        console.log('imt: '+imt);
        this.form.imt = imt.toFixed(1)
      } else {
        this.form.imt = null
      }

      // =====================
      // 2️⃣ Tentukan Status HB
      // =====================
      if (hb > 0) {
        if (hb < 8) this.form.status_hb = 'Anemia berat'
        else if (hb >= 8 && hb < 10) this.form.status_hb = 'Anemia sedang'
        else if (hb >= 10 && hb < 12) this.form.status_hb = 'Anemia ringan'
        else this.form.status_hb = 'Normal'
        console.log('hb: '+this.form.status_hb);
      } else {
        this.form.status_hb = null
      }

      // =====================
      // 3️⃣ Tentukan Status Gizi
      // =====================
      // Berdasarkan IMT dan LILA
      if (this.form.imt) {
        const imt = parseFloat(this.form.imt)
        if (imt < 18.5) this.form.status_gizi = 'Kekurangan gizi (Kurus)'
        else if (imt >= 18.5 && imt < 25) this.form.status_gizi = 'Normal'
        else if (imt >= 25 && imt < 30) this.form.status_gizi = 'Kelebihan berat badan'
        else this.form.status_gizi = 'Obesitas'
        console.log('gizi: '+this.form.status_gizi);
      } else if (lila > 0) {
        // fallback jika IMT tidak ada tapi ada LILA
        if (lila < 23.5) this.form.status_gizi = 'Kekurangan energi kronis (KEK)'
        else this.form.status_gizi = 'Normal'
        console.log('gizi: '+this.form.status_gizi);
      } else {
        this.form.status_gizi = null
      }
    },
    async checkDampinganKe() {
      if (!this.form.nik_perempuan || !this.form.nik_pria) return

      try {
        const res = await axios.get('http://localhost:8000/api/bride/check', {
          params: {
            nik_perempuan: this.form.nik_perempuan,
            nik_pria: this.form.nik_pria
          }
        })
        this.form.dampingan_ke = res.data.dampingan_ke
      } catch (err) {
        console.error('Gagal cek dampingan ke:', err)
        this.form.dampingan_ke = 1
      }
    },
    hitungUsia(jenis) {
      const today = new Date()
      let tglLahir = null

      if (jenis === 'perempuan' && this.form.tgl_lahir_perempuan) {
        tglLahir = new Date(this.form.tgl_lahir_perempuan)
      } else if (jenis === 'pria' && this.form.tgl_lahir_pria) {
        tglLahir = new Date(this.form.tgl_lahir_pria)
      }

      if (tglLahir) {
        let usia = today.getFullYear() - tglLahir.getFullYear()
        const m = today.getMonth() - tglLahir.getMonth()
        if (m < 0 || (m === 0 && today.getDate() < tglLahir.getDate())) {
          usia--
        }

        if (jenis === 'perempuan') {
          this.form.usia_perempuan = usia
        } else if (jenis === 'pria') {
          this.form.usia_pria = usia
        }
      }
    },
    resetForm() {
      this.form = {
        nama_perempuan: '',
        nik_perempuan: '',
        pekerjaan_perempuan: '',
        tgl_lahir_perempuan: '',
        usia_perempuan: null,
        hp_perempuan: '',
        nama_pria: '',
        nik_pria: '',
        pekerjaan_pria: '',
        tgl_lahir_pria: '',
        usia_pria: null,
        hp_pria: '',
        tgl_rencana_menikah: '',
        rencana_tinggal: '',
        tgl_pemeriksaan: '',
        tgl_pendampingan: '',
        bb: null,
        tb: null,
        lila: null,
        hb: null,
        status_hb: '',
        status_gizi: '',
        catin_terpapar_rokok: '',
        catin_ttd: '',
        punya_riwayat_penyakit: '',
        riwayat_penyakit: '',
        fasilitas_rujukan: '',
        edukasi: '',
        pmt: ''
      }
      this.showForm = false
    },
    async cariData() {
      this.notFound = false
      this.found = false

      try {
        const res = await axios.get(`http://localhost:8000/api/bride/search/${this.searchNIK}`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        const data = res.data

        // ========== DATA PENDAMPINGAN ==========
        const pend = data.catin?.pendampingan?.[0] || {} // <-- ambil elemen pertama atau objek kosong

        this.form = {
          id: data.id || null,
          nama_perempuan: data.catin?.peran === 'istri' ? data.catin?.nama : data.catin?.pasangan?.nama || '',
          nik_perempuan: data.catin?.peran === 'istri' ? data.catin?.nik : data.catin?.pasangan?.nik || '',
          pekerjaan_perempuan: data.catin?.peran === 'istri' ? data.catin?.pekerjaan : data.catin?.pasangan?.pekerjaan || '',
          tgl_lahir_perempuan: data.catin?.peran === 'istri' ? data.catin?.tgl_lahir : data.catin?.pasangan?.tgl_lahir || '',
          usia_perempuan: data.catin?.peran === 'istri' ? data.catin?.usia : data.catin?.pasangan?.usia || '',
          hp_perempuan: data.catin?.peran === 'istri' ? data.catin?.no_hp : data.catin?.pasangan?.no_hp || '',

          nama_pria: data.catin?.peran === 'suami' ? data.catin?.nama : data.catin?.pasangan?.nama || '',
          nik_pria: data.catin?.peran === 'suami' ? data.catin?.nik : data.catin?.pasangan?.nik || '',
          pekerjaan_pria: data.catin?.peran === 'suami' ? data.catin?.pekerjaan : data.catin?.pasangan?.pekerjaan || '',
          tgl_lahir_pria: data.catin?.peran === 'suami' ? data.catin?.tgl_lahir : data.catin?.pasangan?.tgl_lahir || '',
          usia_pria: data.catin?.peran === 'suami' ? data.catin?.usia : data.catin?.pasangan?.usia || '',
          hp_pria: data.catin?.peran === 'suami' ? data.catin?.no_hp : data.catin?.pasangan?.no_hp || '',

          tgl_rencana_menikah: data.tgl_rencana_menikah || '',
          rencana_tinggal: data.rencana_tinggal || '',

          // Pendampingan (ambil dari pend)
          tgl_pendampingan: pend.tgl_pendampingan || '',
          dampingan_ke: pend.dampingan_ke || '',
          bb: pend.bb || null,
          tb: pend.tb || null,
          lila: pend.lila || null,
          hb: pend.hb || null,
          imt:pend.imt || null,
          status_hb: pend.anemia || '', // Normal / Anemia
          status_gizi: pend.kek || '', // KEK / Tidak KEK
          catin_terpapar_rokok: pend.terpapar_rokok || '',
          catin_ttd: pend.catin_ttd || '',
          punya_riwayat_penyakit: pend.riwayat_penyakit || '',
          riwayat_penyakit: pend.ket_riwayat_penyakit || '',

          fasilitas_rujukan: pend.punya_jaminan || '',
          edukasi: pend.keluarga_teredukasi || '',
          pmt: pend.mendapatkan_bantuan || '',
        }

        this.modalMode = 'update'
        this.showForm = true
        this.found = true
      } catch (error) {
        // ✅ Tangani kalau backend kirim 404
        if (error.response && error.response.status === 404) {
          this.resetForm()
          this.modalMode = 'add'
          this.showForm = true
          this.notFound = true
        } else {
          console.error('Error tak terduga:', error)
        }
      }

      console.log('is found? ' + this.found)
    },
    async loadBride() {
      try {
        const res = await axios.get('http://localhost:8000/api/bride', {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        this.bride = res.data.map(item => {
        const pendamping = item.catin?.pendampingan?.[0] || {} // ambil pendampingan pertama

        return {
          id: item.id,
          tgl_daftar: item.tgl_daftar,
          tgl_rencana_menikah: item.tgl_rencana_menikah,
          rencana_tinggal: item.rencana_tinggal,

          // --- Data Catin Perempuan ---
          nama_perempuan: item.catin?.nama || '',
          nik_perempuan: item.catin?.nik || '',
          pekerjaan_perempuan: item.catin?.pekerjaan || '',
          tgl_lahir_perempuan: item.catin?.tgl_lahir || '',
          usia_perempuan: item.catin?.usia || '',
          hp_perempuan: item.catin?.no_hp || '',

          // --- Data Catin Pria ---
          nama_pria: item.catin?.pasangan?.nama || '',
          nik_pria: item.catin?.pasangan?.nik || '',
          pekerjaan_pria: item.catin?.pasangan?.pekerjaan || '',
          tgl_lahir_pria: item.catin?.pasangan?.tgl_lahir || '',
          usia_pria: item.catin?.pasangan?.usia || '',
          hp_pria: item.catin?.pasangan?.no_hp || '',

          // --- Data Pendampingan ---
          tgl_pemeriksaan: pendamping.tgl_pemeriksaan || '',
          tgl_pendampingan: pendamping.tgl_pendampingan || '',
          dampingan_ke: pendamping.dampingan_ke || '',
          bb: pendamping.bb || '',
          tb: pendamping.tb || '',
          lila: pendamping.lila || '',
          hb: pendamping.hb || '',
          imt: pendamping.imt || '',
          status_hb: pendamping.anemia || '',
          status_gizi: pendamping.kek || '',
          catin_terpapar_rokok: pendamping.catin_terpapar_rokok || '',
          catin_ttd: pendamping.catin_ttd || '',
          punya_riwayat_penyakit: pendamping.punya_riwayat_penyakit || '',
          riwayat_penyakit: pendamping.riwayat_penyakit || '',
          fasilitas_rujukan: pendamping.fasilitas_rujukan || '',
          edukasi: pendamping.edukasi || '',
          pmt: pendamping.pmt || '',
        }})

        //console.log('Data Flatten:', this.bride)
      } catch (e) {
        //console.error('Gagal ambil data:', e)
        this.showError('Error Ambil Data', e)
      }
    },
    showError(message) {
      this.errorMessage = message || 'Terjadi kesalahan.'
      // eslint-disable-next-line no-undef
      const modal = new bootstrap.Modal(document.getElementById('errorModal'))
      modal.show()
    },
    showSuccess(message){
      this.successMessage = message || 'Berhasil tersimpan.'
      // eslint-disable-next-line no-undef
      const modal = new bootstrap.Modal(document.getElementById('successModal'))
      modal.show()
    },
    toggleExpandForm() {
      this.modalMode = "add"
      this.showForm = !this.showForm
      if (!this.showForm) this.resetForm()
      console.log('modal mode: '+this.modalMode);

    },
    closeModal(id) {
      const el = document.getElementById(id)
      if (el) {
        const instance = Modal.getOrCreateInstance(el)
        instance.hide()
      }

      // jaga-jaga kalau backdrop masih nyangkut
      setTimeout(() => {
        document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove())
        document.body.classList.remove('modal-open')
        document.body.style.removeProperty('overflow')
        document.body.style.removeProperty('padding-right')
      }, 300) // delay biar nunggu animasi fade
    },
    updateProgressBar(percent, row, total) {
      this.importProgress = percent
      this.currentRow = row
      this.totalRows = total

      const start = this.animatedProgress
      const end = percent
      const step = (end - start) / 10
      let i = 0
      const interval = setInterval(() => {
        this.animatedProgress = Math.min(end, Math.round(start + step * i))
        i++
        if (this.animatedProgress >= end) clearInterval(interval)
      }, 30)
    },
    applyFilter_catin() {
      // copy advanced filter aktif saat tombol "Cari/Terapkan Filter"
      this.appliedFilter_catin = { ...this.advancedFilter_catin }
    },
    resetFilter_catin() {
      this.advancedFilter_catin = {
        status_hb_list: [],
        status_gizi_list: [],
      }

      this.appliedFilter_catin = {}
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    async saveData() {
      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0
      this.currentRow = 0
      this.totalRows = 1

      try {
        // Payload langsung dari struktur form
        const payload = {
          // Data catin perempuan
          nama_perempuan: this.form.nama_perempuan,
          nik_perempuan: this.form.nik_perempuan,
          pekerjaan_perempuan: this.form.pekerjaan_perempuan,
          tgl_lahir_perempuan: this.form.tgl_lahir_perempuan,
          usia_perempuan: this.form.usia_perempuan,
          hp_perempuan: this.form.hp_perempuan,

          // Data catin pria
          nama_pria: this.form.nama_pria,
          nik_pria: this.form.nik_pria,
          pekerjaan_pria: this.form.pekerjaan_pria,
          tgl_lahir_pria: this.form.tgl_lahir_pria,
          usia_pria: this.form.usia_pria,
          hp_pria: this.form.hp_pria,

          // Data pernikahan
          tgl_rencana_menikah: this.form.tgl_rencana_menikah,
          rencana_tinggal: this.form.rencana_tinggal,

          // Data pendampingan
          dampingan_ke: this.form.dampingan_ke,
          tgl_pendampingan: this.form.tgl_pendampingan,
          bb: this.form.bb,
          tb: this.form.tb,
          lila: this.form.lila,
          hb: this.form.hb,

          // Status & kondisi
          status_hb: this.form.status_hb,
          status_gizi: this.form.status_gizi,
          catin_terpapar_rokok: this.form.catin_terpapar_rokok,
          catin_ttd: this.form.catin_ttd,
          punya_riwayat_penyakit: this.form.punya_riwayat_penyakit,
          riwayat_penyakit: this.form.riwayat_penyakit,

          // Fasilitas & edukasi
          fasilitas_rujukan: this.form.fasilitas_rujukan,
          edukasi: this.form.edukasi,
          pmt: this.form.pmt,
        }

        // Tentukan mode (tambah / update)
        const url = this.form.id
          ? `http://localhost:8000/api/bride/${this.form.id}`
          : `http://localhost:8000/api/bride`
        const method = this.form.id ? 'put' : 'post'

        // Kirim ke backend
        await axios({
          method,
          url,
          data: payload,
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        // Refresh data
        await this.loadBride()
        this.resetForm()
        this.showSuccess('Data berhasil disimpan')
      } catch (error) {
        console.error('Gagal simpan data:', error)
        this.showError('Terjadi kesalahan saat menyimpan data')
      } finally {
        this.isLoadingImport = false
      }
    },
    openImport(title) {
      this.importTitle = title
      const el = this.$refs.modalImport
      Modal.getOrCreateInstance(el).show()
    },
    handleImport() {
      this.closeModal('modalImport')

      const fileInput = this.$refs.csvFile
      if (!fileInput || !fileInput.files.length) return

      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0

      const file = fileInput.files[0]
      const reader = new FileReader()

      reader.onload = (e) => {
        const text = e.target.result
        const rows = text
          .split('\n')
          .map((r) => r.trim())
          .filter((r) => r)
        const headers = rows[0].split(',').map((h) => h.trim())
        const total = rows.length - 1
        this.totalRows = total

        rows.slice(1).forEach((row, idx) => {
          const values = row.split(',').map((v) => v.trim())
          const obj = {}
          headers.forEach((h, i) => {
            obj[h] = values[i] || ''
          })

          // ===============================
          //  MAPPING DATA CSV → DATA CATIN
          // ===============================
          this.bride.push({
            id: obj.id || null,
            id_catin: obj.id_catin || null,
            // --- Data Pria ---
            nik_pria: obj.nik_pria || '',
            nama_pria: obj.nama_pria || '',
            tgl_lahir_pria: obj.tgl_lahir_pria || '',
            usia_pria: obj.usia_pria || '',
            pekerjaan_pria: obj.pekerjaan_pria || '',
            bb_pria: parseFloat(obj.bb_pria) || 0,
            tb_pria: parseFloat(obj.tb_pria) || 0,
            lila_pria: parseFloat(obj.lila_pria) || 0,
            hb_pria: obj.hb_pria || '',
            status_gizi_pria: obj.status_gizi_pria || '',

            // --- Data Perempuan ---
            nik_perempuan: obj.nik_perempuan || '',
            nama_perempuan: obj.nama_perempuan || '',
            tgl_lahir_perempuan: obj.tgl_lahir_perempuan || '',
            usia_perempuan: obj.usia_perempuan || '',
            pekerjaan_perempuan: obj.pekerjaan_perempuan || '',
            bb_perempuan: parseFloat(obj.bb_perempuan) || 0,
            tb_perempuan: parseFloat(obj.tb_perempuan) || 0,
            lila_perempuan: parseFloat(obj.lila_perempuan) || 0,
            hb_perempuan: obj.hb_perempuan || '',
            status_gizi_perempuan: obj.status_gizi_perempuan || '',

            // --- Data Pernikahan / Rencana ---
            tgl_daftar: obj.tgl_daftar || '',
            tgl_rencana_menikah: obj.tgl_rencana_menikah || '',
            rencana_tinggal: obj.rencana_tinggal || '',
            catatan: obj.catatan || '',
            fasilitas_rujukan: obj.fasilitas_rujukan || '',
            edukasi: obj.edukasi || '',
            pmt: obj.pmt || '',
          })

          // Update progress bar
          const percent = Math.round(((idx + 1) / total) * 100)
          this.updateProgressBar(percent, idx + 1, total)
        })

        // ===============================
        //  Tampilkan Modal Sukses
        // ===============================
        setTimeout(() => {
          this.isLoadingImport = false
          const el = document.getElementById('successModal')
          const instance = Modal.getOrCreateInstance(el)
          instance.show()
        }, 500)
      }

      reader.readAsText(file)
    },

  },
  async mounted() {
    this.isLoading = true
    try {
      await Promise.all([
        this.loadBride(),
        this.getPendingData()
      ])
    } catch (err) {
      console.error('Error loading data:', err)
    } finally {
      this.isLoading = false
    }
  },
  watch: {
    'form.bb': 'hitungKondisiCatin',
    'form.tb': 'hitungKondisiCatin',
    'form.hb': 'hitungKondisiCatin',
    'form.lila': 'hitungKondisiCatin',
    // eslint-disable-next-line no-unused-vars
    'form.nik_perempuan'(val) {
      this.checkDampinganKe()
    },
    // eslint-disable-next-line no-unused-vars
    'form.nik_pria'(val) {
      this.checkDampinganKe()
    }
  },
}
</script>

<style scoped>
.spinner-overlay {
  position: fixed;
  inset: 0;
  background: rgba(255, 255, 255, 0.8);
  z-index: 9999;
  backdrop-filter: blur(2px);
  transition: opacity 0.3s ease;
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
.datatable-responsive {
  width: 100%;
  overflow-x: auto; /* aktifkan scroll horizontal */
}

.datatable-responsive table {
  min-width: 300px; /* sesuaikan lebar minimal tabel */
}

.bride-wrapper {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  min-height: 100vh;
}
/* Gradient Banner */
.bride-banner {
  background: linear-gradient(90deg, var(--bs-primary), var(--bs-secondary));
  border-radius: 0 0 1rem 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.filter-wrapper {
  position: relative;
  z-index: 1050;
  margin-top: -30px !important;
  width: 97%;
}
/* Hilangkan garis pemisah antara sidebar dan content */
.flex-grow-1 {
  border-left: none !important;
  background-color: #f9f9fb;
}
.breadcrumb-item + .breadcrumb-item::before {
  color: rgba(255, 255, 255, 0.7);
}
/* Smooth Apple-like Modal */
.modern-modal {
  border-radius: 1.5rem;
  border: 1px solid #eaeaea;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  background: #fff;
  transition: all 0.3s ease-in-out;
}

/* Form lebih clean */
.form-control-modern,
.form-select.form-control-modern {
  border-radius: 0.75rem;
  border: 1px solid #ddd;
  padding: 0.6rem 1rem;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

.form-control-modern:focus {
  border-color: var(--bs-primary);
  box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.2);
}

/* Animasi modal lebih halus */
.modal.fade .modal-dialog {
  transform: translateY(20px);
  transition:
    transform 0.3s ease-out,
    opacity 0.3s ease-out;
}

.modal.fade.show .modal-dialog {
  transform: translateY(0);
  opacity: 1;
}
.modern-card {
  border-radius: 1rem;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
  border: none;
}
.table-wrapper {
  width: 100%;
  overflow-x: auto; /* ✅ Scroll horizontal */
  -webkit-overflow-scrolling: touch; /* smooth di mobile */
}

.table-modern td {
  max-width: 180px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

form h5 {
  position: relative;
  padding-bottom: 0.5rem;
  margin-bottom: 1rem;
  border-bottom: 2px solid var(--bs-secondary); /* default pakai secondary */
}
@media (max-width: 768px) {
  .table-modern {
    min-width: auto;
  }
}
</style>
