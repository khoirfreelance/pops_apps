<template>
  <div class="wrapper">
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
    <HeaderAdmin/>

    <div
      class="content flex-grow-1 d-flex flex-column flex-md-row"
      :class="{
        'sidebar-collapsed': isCollapsed,
        'sidebar-expanded': !isCollapsed
      }"
    >

      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <!-- Content -->
        <div class="py-4 container-fluid" >

          <!-- Welcome Card -->
          <div class="card welcome-card shadow-sm mb-4 border-0">
            <div class="card-body d-flex flex-column flex-md-row align-items-start py-0 justify-content-between">
              <!-- Kiri: Teks Welcome -->
              <div class="text-start">
                <h3>
                  <span class="fw-normal fs-6">Selamat datang,</span> <br />
                  {{ username }}
                </h3>
                <img
                  v-if="logoLoaded"
                  :src="logoSrc"
                  alt="Logo"
                  height="50"
                  class="mt-4"
                  @error="logoLoaded = false"
                />
                <!-- jika gagal load logo, tampilkan kelurahan -->
                <span
                  v-else
                  class="text-muted fw-bold fs-5 mt-4"
                >
                  {{ kelurahan || 'Wilayah' }}
                </span>
                <p class="small d-flex align-items-center mt-1">
                  Data terakhir diperbarui pada &nbsp;<strong>{{ today }}</strong>
                </p>
              </div>

              <!-- Kanan: Gambar -->
              <div class="mt-3 mt-md-0">
                <img
                  src="/banner.png"
                  alt="Welcome"
                  class="img-fluid welcome-img"
                  style="max-width: 280px"
                />
              </div>
            </div>
          </div>

          <!-- Judul Laporan -->
          <div class="text-center mt-4">
            <div class="bg-primary text-white py-2 px-4 d-inline-block rounded-top">
              <h5 class="mb-0">
                Laporan Status Gizi dan Stagnansi <span class="text-capitalize fw-bold">{{ kelurahan }}</span> Bulan <span class="fw-bold">{{ thisMonth }}</span>
              </h5>
            </div>
          </div>

          <!-- Filter Form -->
          <div class="bg-light border rounded-bottom shadow-sm px-4 py-3 mt-0">
            <div class="mb-2 fw-bold text-primary">Filter:</div>

            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <!-- Kelurahan/Desa -->
              <div class="col-md-2">
                <label class="form-label">Kelurahan/Desa</label>
                <select v-model="filters.kelurahan" class="form-select text-muted" disabled>
                  <option :value="kelurahan">{{ kelurahan }}</option>
                </select>
              </div>

              <!-- Posyandu -->
              <div class="col-md-2">
                <label class="form-label">Posyandu</label>
                <select v-model="filters.posyandu" class="form-select text-muted">
                  <option value="">All</option>
                  <option v-for="item in posyanduList" :key="item.id" :value="item.id">
                    {{ item.nama_posyandu}}
                  </option>
                </select>
              </div>

              <!-- RW -->
              <div class="col-md-2">
                <label class="form-label">RW</label>
                <select v-model="filters.rw" class="form-select text-muted" :disabled="rwReadonly">
                  <option value="">All</option>
                  <option v-for="rw in rwList" :key="rw" :value="rw">{{ rw }}</option>
                </select>
              </div>

              <!-- RT -->
              <div class="col-md-2">
                <label class="form-label">RT</label>
                <select v-model="filters.rt" class="form-select text-muted" :disabled="rtReadonly">
                  <option value="">All</option>
                  <option v-for="rt in rtList" :key="rt" :value="rt">{{ rt }}</option>
                </select>
              </div>

              <!-- Periode -->
              <div class="col-md-2">
                <label class="form-label">Periode</label>
                <select v-model="filters.periode" class="form-select text-muted">
                  <option value="">All</option>
                  <option v-for="periode in periodeOptions" :key="periode" :value="periode">
                    {{ periode }}
                  </option>
                </select>
              </div>

              <!-- Tombol Cari -->
              <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-gradient fw-semibold">
                  <i class="bi bi-search me-1"></i> Cari
                </button>
              </div>
            </form>
          </div>

          <!-- Button Group -->
          <div class="container-fluid mt-4">
            <!-- Expand/Collapse Button -->
            <div class="d-flex justify-content-between">
              <h5 class="fw-bold text-success mb-3">Ringkasan Statistik</h5>
              <div class="mb-3">
                <button
                  type="button"
                  class="btn btn-outline-primary mx-3"
                  @click="toggleExpandForm"
                >
                  <i :class="isFormOpen ? 'bi bi-dash-square' : 'bi bi-plus-square'"></i>
                  {{ isFormOpen ? 'Tutup Form' : 'Unggah Data' }}
                </button>
              </div>
            </div>

            <!-- Collapsible Form -->
            <div class="card shadow-sm" v-if="isFormOpen">
              <div class="card-body">
                <label class="form-label fw-semibold">Upload Data Anak (CSV)</label>
                <div
                  class="dropzone-full position-relative p-4 rounded-3 border text-center"
                  :class="{
                    'border-primary bg-light': isDataDrag,
                    'border-danger': fileError
                  }"
                  @click="triggerFileDialog"
                  @dragover.prevent="onDragOver"
                  @dragleave.prevent="onDragLeave"
                  @drop.prevent="handleDrop($event)"
                  role="button"
                  tabindex="0"
                  @keydown.enter.prevent="triggerFileDialog"
                  @keydown.space.prevent="triggerFileDialog"
                >
                  <i class="bi bi-cloud-upload fs-1 text-primary"></i>
                  <p class="mb-1 fw-medium">Drag & drop file CSV di sini</p>
                  <small class="text-muted">atau klik untuk pilih file</small>

                  <!-- Invisible input (terikat ke parent relatif) -->
                  <input
                    ref="fileInput"
                    type="file"
                    accept=".csv,text/csv"
                    class="position-absolute w-100 h-100 top-0 start-0 opacity-0"
                    @change="handleFileChange($event)"
                  />
                </div>

                <!-- Preview / status -->
                <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <div v-if="file">
                    <div><strong>Nama:</strong> {{ fileName }}</div>
                    <div><strong>Ukuran:</strong> {{ humanFileSize(fileSize) }}</div>
                    <div v-if="filePreviewLines" class="text-muted small">Contoh baris pertama: <code>{{ filePreviewLines }}</code></div>
                  </div>

                  <div v-else class="text-muted small">Belum ada file dipilih</div>

                  <div class="d-flex gap-2">
                    <button
                      v-if="file && !uploading"
                      class="btn btn-outline-danger btn-sm"
                      @click="removeFile"
                      type="button"
                    >
                      <i class="bi bi-trash me-1"></i> Hapus
                    </button>

                    <button
                      v-if="file && !uploading"
                      class="btn btn-success btn-sm"
                      @click="uploadFile"
                      type="button"
                    >
                      <i class="bi bi-upload me-1"></i> Upload
                    </button>

                    <div v-if="uploading" class="d-flex align-items-center gap-2">
                      <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                      <small class="text-muted">Mengunggah... {{ uploadProgress }}%</small>
                    </div>
                  </div>
                </div>

                <!-- Error message -->
                <div v-if="fileError" class="mt-2 text-danger small">
                  {{ fileError }}
                </div>
              </div>
            </div>

          </div>

          <!-- Ringkasan Statistik-->
          <div class="container-fluid my-4">

            <div class="row">
              <div class="col-md-10 col-sm-12">
                <div class="row justify-content-center">
                  <div
                    v-for="(item, index) in gizi"
                    :key="index"
                    class="col-lg-2 col-sm-6 col-12 mb-3"
                  >
                    <div
                      class="card shadow-sm border-0 rounded-3 overflow-hidden"
                      :class="`border-start border-4 border-${item.color}`"
                    >
                      <div class="card-body py-3 d-flex justify-content-between">
                        <div>
                          <h6 class="fw-bold mb-1">{{ item.title }}</h6>
                          <p class="mb-2 small" :class="`text-${item.color}`">{{ item.percent }}</p>
                        </div>
                        <h3 class="fw-bold mb-0" :class="`text-${item.color}`">{{ item.value }}</h3>
                      </div>

                      <!-- SVG LINE CHART -->
                      <div class="card-footer bg-transparent border-0 pt-0">
                        <svg
                          viewBox="0 0 100 30"
                          class="svg-line"
                          preserveAspectRatio="none"
                          :class="`stroke-${item.color}`"
                        >
                          <polyline
                            fill="none"
                            stroke-width="2"
                            stroke-linecap="round"
                            points="0,20 15,15 30,18 45,10 60,12 75,8 90,15 100,10"
                          />
                        </svg>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- TOTAL ANAK -->
              <div class="col-md-2 col-sm-12">
                <div class="card text-center shadow-sm border-0 h-100 d-flex flex-column justify-content-center">
                  <h6 class="text-muted fw-bold">Total Anak Balita</h6>
                  <div class="flex-grow-1 d-flex flex-column justify-content-center">
                    <h1 class="fw-bold text-success mb-0">111</h1>
                  </div>
                  <i class="bi bi-people fs-3 text-secondary"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Table and detail Section -->
          <div class="container-fluid mt-4">
            <h5 class="fw-bold text-success mb-3">Data Anak</h5>
            <div class="row mt-4">
              <div :class="selectedAnak ? 'col-md-8' : 'col-md-12'">
                <div class="card bg-light px-2 py-5">
                  <div class="table-responsive">
                    <EasyDataTable
                      :headers="headers"
                      :items="kunjungan_posyandu"
                      table-class="table table-striped align-middle text-center"
                      header-text-direction="center"
                      body-text-direction="center"
                    >

                      <template #item-nama="props">
                        <a
                          href="#"
                          @click.prevent="showDetail(props)"
                          class="fw-semibold text-decoration-none text-primary text-start"
                        >
                          {{ props.nama }}
                        </a>
                      </template>

                      <!-- INTERVENSI -->
                      <template #item-intervensi="{ intervensi }">
                        <span>{{ intervensi || '-' }}</span>
                      </template>

                      <!-- STATUS GIZI -->
                      <template #item-tbu="{ tbu }">
                        <span
                          :class="{
                            'badge px-3 py-2 bg-danger': tbu === 'Severely Stunted',
                            'badge px-3 py-2 bg-warning text-dark': tbu === 'Stunted',
                            'text-dark': tbu === 'Normal',
                          }"
                        >
                          {{ tbu }}
                        </span>
                      </template>
                    </EasyDataTable>
                  </div>
                </div>
              </div>

              <!-- DETAIL DATA -->
              <div class="col-md-4 mt-3" v-if="selectedAnak">
                <div v-if="selectedAnak" class="card shadow-sm p-4 text-center small position-relative">

                  <!-- Tombol Close -->
                  <button
                    type="button"
                    class="btn-close position-absolute top-0 end-0 m-3"
                    aria-label="Close"
                    @click="selectedAnak = null"
                  ></button>

                  <!-- Nama dan Identitas -->
                  <h5 class="fw-bold text-dark mb-1">{{ selectedAnak.nama }}</h5>
                  <p class="text-muted mb-0">
                    {{ selectedAnak.gender === 'L' ? 'Laki-laki' : selectedAnak.gender === 'P' ? 'Perempuan' : selectedAnak.gender }}
                  </p>

                  <p class="text-muted mb-0">{{ selectedAnak.alamat || 'Desa Wonosari, Kec. Bojong Gede' }}</p>
                  <p class="text-muted">{{ selectedAnak.posyandu || 'Posyandu Mawar' }}</p>

                  <!-- Badge Status Gizi -->
                  <div class="mb-3">
                    <span
                      class="badge px-3 py-2 fs-6"
                      :class="{
                        'bg-danger': selectedAnak.status_gizi === 'Stunting',
                        'bg-warning text-dark': ['Wasting', 'Underweight'].includes(selectedAnak.status_gizi),
                        'bg-success': selectedAnak.status_gizi === 'Normal'
                      }"
                    >
                      {{ selectedAnak.status_gizi }} {{ selectedAnak.status_gizi_kategori || 'BB/U' }}
                    </span>
                  </div>

                  <!-- Riwayat Penimbangan -->
                  <h6 class="fw-bold text-start text-secondary mt-2">Riwayat Penimbangan</h6>
                  <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center">
                      <thead class="table-light">
                        <tr>
                          <th>Tanggal</th>
                          <th>Status BB/U</th>
                          <th>Status TB/U</th>
                          <th>Status BB/TB</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(r, i) in selectedAnak.riwayat_penimbangan || []" :key="i">
                          <td>{{ r.tanggal }}</td>
                          <td>{{ r.bb }}</td>
                          <td>{{ r.tb }}</td>
                          <td>
                            <span
                              class="badge"
                              :class="{
                                'bg-danger': r.bb_tb === 'Stunting',
                                'bg-warning text-dark': ['Wasting', 'Underweight'].includes(r.bb_tb),
                                'bg-success': r.bb_tb === 'Normal'
                              }"
                            >
                              {{ r.bb_tb }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Riwayat Intervensi -->
                  <h6 class="fw-bold text-start text-secondary mt-3">Riwayat Intervensi / Bantuan</h6>
                  <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center">
                      <thead class="table-light">
                        <tr>
                          <th>Tanggal</th>
                          <th>Kader</th>
                          <th>Intervensi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(i, idx) in selectedAnak.riwayat_intervensi || []" :key="idx">
                          <td>{{ i.tanggal }}</td>
                          <td>{{ i.kader }}</td>
                          <td>{{ i.intervensi }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Tombol Download -->
                  <button
                    class="btn btn-gradient rounded-pill px-4 mt-2 fw-semibold"
                    @click="downloadRiwayat"
                  >
                    Download Riwayat
                  </button>
                </div>
              </div>

              <!-- Detail Anak -->
              <div class="col-md-12 mt-4" v-if="selectedAnak">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden position-relative">
                  <!-- Tombol Close -->
                  <button
                    class="btn-close position-absolute top-0 end-0 m-3"
                    aria-label="Close"
                    @click="selectedAnak = null"
                  ></button>

                  <!-- Header -->
                  <div class="bg-primary text-white p-4 text-center rounded-top">
                    <h5 class="fw-bold mb-0">{{ selectedAnak.nama }}</h5>
                    <p class="mb-0 small">
                      NIK: {{ selectedAnak.nik }} •
                      <span class="text-capitalize">
                        {{ selectedAnak.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                      </span>
                      • Usia: {{ selectedAnak.usia }} bln
                    </p>
                  </div>

                  <!-- Tabs -->
                  <div class="p-3">
                    <ul
                      class="nav nav-pills justify-content-center mb-4 flex-wrap gap-2"
                      id="anakDetailTab"
                      role="tablist"
                    >
                      <li class="nav-item" role="presentation">
                        <button
                          class="nav-link active"
                          id="tab-profile-anak"
                          data-bs-toggle="tab"
                          data-bs-target="#tab-pane-profile-anak"
                          type="button"
                          role="tab"
                        >
                          <i class="bi bi-person-badge me-1"></i> Profile Anak
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button
                          class="nav-link"
                          id="tab-kelahiran"
                          data-bs-toggle="tab"
                          data-bs-target="#tab-pane-kelahiran"
                          type="button"
                          role="tab"
                        >
                          <i class="bi bi-clipboard-heart me-1"></i> Data Kelahiran
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button
                          class="nav-link"
                          id="tab-kunjungan"
                          data-bs-toggle="tab"
                          data-bs-target="#tab-pane-kunjungan"
                          type="button"
                          role="tab"
                        >
                          <i class="bi bi-hospital me-1"></i> Kunjungan Posyandu
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button
                          class="nav-link"
                          id="tab-intervensi"
                          data-bs-toggle="tab"
                          data-bs-target="#tab-pane-intervensi"
                          type="button"
                          role="tab"
                        >
                          <i class="bi bi-activity me-1"></i> Intervensi
                        </button>
                      </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="anakDetailTabContent">
                      <!-- Profile Anak -->
                      <div
                        class="tab-pane fade show active"
                        id="tab-pane-profile-anak"
                        role="tabpanel"
                      >
                        <div class="row g-3">
                          <div class="col-md-6">
                            <div class="card bg-light border-0 shadow-sm p-3 h-100">
                              <h6 class="fw-bold mb-3 text-danger">Identitas Anak</h6>
                              <p class="mb-1"><strong>Nama:</strong> {{ selectedAnak.nama }}</p>
                              <p class="mb-1"><strong>NIK:</strong> {{ selectedAnak.nik }}</p>
                              <p class="mb-1">
                                <strong>Jenis Kelamin:</strong>
                                {{ selectedAnak.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                              </p>
                              <p class="mb-1"><strong>Usia:</strong> {{ selectedAnak.usia }} bulan</p>
                              <p class="mb-1"><strong>Alamat:</strong> {{ selectedAnak.alamat }}</p>
                              <p class="mb-1">
                                <strong>Desa/Kecamatan:</strong>
                                {{ selectedAnak.desa }}, {{ selectedAnak.kecamatan }}
                              </p>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="card bg-light border-0 shadow-sm p-3 h-100">
                              <h6 class="fw-bold mb-3 text-danger">Orang Tua</h6>
                              <p class="mb-1"><strong>Ayah:</strong> {{ selectedAnak.nama_ayah }}</p>
                              <p class="mb-1"><strong>NIK Ayah:</strong> {{ selectedAnak.nik_ayah }}</p>
                              <p class="mb-1"><strong>Ibu:</strong> {{ selectedAnak.nama_ibu }}</p>
                              <p class="mb-1"><strong>NIK Ibu:</strong> {{ selectedAnak.nik_ibu }}</p>
                              <p class="mb-1"><strong>No. Telp:</strong> {{ selectedAnak.no_telp }}</p>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Data Kelahiran -->
                      <div class="tab-pane fade" id="tab-pane-kelahiran" role="tabpanel">
                        <div class="card bg-light border-0 shadow-sm p-3">
                          <h6 class="fw-bold mb-3 text-danger">Data Kelahiran</h6>
                          <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                              <thead>
                                <tr>
                                  <th>No. KIA</th>
                                  <th>Tempat Lahir</th>
                                  <th>Tanggal Lahir</th>
                                  <th>Berat (kg)</th>
                                  <th>Panjang (cm)</th>
                                  <th>Jenis Persalinan</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr
                                  v-for="(item, i) in selectedAnak.kelahiran"
                                  :key="'kelahiran-' + i"
                                >
                                  <td>{{ item.no_kia }}</td>
                                  <td>{{ item.tmpt_dilahirkan }}</td>
                                  <td>{{ item.tgl_lahir }}</td>
                                  <td>{{ item.bb }}</td>
                                  <td>{{ item.pb }}</td>
                                  <td class="text-capitalize">{{ item.jenis }}</td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>

                      <!-- Data Kunjungan Posyandu -->
                      <div class="tab-pane fade" id="tab-pane-kunjungan" role="tabpanel">
                        <div class="card bg-light border-0 shadow-sm p-3">
                          <h6 class="fw-bold mb-3 text-danger">Riwayat Penimbangan</h6>
                          <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                              <thead class="table-secondary">
                                <tr>
                                  <th>Tanggal</th>
                                  <th>BB</th>
                                  <th>TB</th>
                                  <th>Status BB/TB</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr
                                  v-for="(riwayat, i) in selectedAnak.riwayat_penimbangan"
                                  :key="'penimbangan-' + i"
                                >
                                  <td>{{ riwayat.tanggal }}</td>
                                  <td>{{ riwayat.bb }}</td>
                                  <td>{{ riwayat.tb }}</td>
                                  <td
                                    :class="{
                                      'text-danger fw-bold': riwayat.bb_tb === 'Stunting',
                                      'text-warning fw-bold': riwayat.bb_tb === 'Underweight',
                                      'text-success fw-bold': riwayat.bb_tb === 'Normal'
                                    }"
                                  >
                                    {{ riwayat.bb_tb }}
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>

                      <!-- Data Intervensi -->
                      <div class="tab-pane fade" id="tab-pane-intervensi" role="tabpanel">
                        <div class="card bg-light border-0 shadow-sm p-3">
                          <h6 class="fw-bold mb-3 text-danger">Riwayat Intervensi</h6>
                          <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                              <thead class="table-secondary">
                                <tr>
                                  <th>Tanggal</th>
                                  <th>Kader</th>
                                  <th>Jenis Intervensi</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr
                                  v-for="(item, i) in selectedAnak.riwayat_intervensi"
                                  :key="'intervensi-' + i"
                                >
                                  <td>{{ item.tanggal }}</td>
                                  <td>{{ item.kader }}</td>
                                  <td>{{ item.intervensi }}</td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <CopyRight />
  </div>
</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import EasyDataTable from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'
import axios from 'axios'

// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Children',
  components: { NavbarAdmin, CopyRight, HeaderAdmin, EasyDataTable },
  data() {
    return {
      /* Wajib ada */
      configCacheKey: 'site_config_cache',
      isLoading: true,
      isCollapsed: false,
      username: '',
      today: '',
      thisMonth:'',
      kelurahan: '',
      logoSrc: null,
      logoLoaded: true,
      totalKasus: 37,
      windowWidth: window.innerWidth,
      isFormOpen:false,
      isDataDrag:false,
      filters: {
        bbu:'',
        tbu:'',
        bbtb:'',
        stagnan:'',
        intervensi: '',
        kelurahan: '',
        posyandu: '',
        rw: '',
        rt: '',
        periode: '',
      },
      posyanduList: [],
      rwList: [],
      rtList: [],
      periodeOptions: [],
      rwReadonly: true,
      rtReadonly: true,

      // upload file
      file: null,
      fileName: '',
      fileSize: 0,
      fileError: '',
      filePreviewLines: '', // (opsional) capture beberapa char/baris pertama dari CSV
      uploading: false,
      uploadProgress: 0,
      // config
      ACCEPTED_EXT: ['csv'],
      ACCEPTED_MIME: ['text/csv', 'application/vnd.ms-excel', 'text/plain'],
      MAX_FILE_SIZE: 5 * 1024 * 1024, // 5 MB

      /* Just for som pages */
      gizi:[
        { title: "Stunting", value: 1, percent: "0%", color: "danger" },
        { title: "Wasting", value: 1, percent: "0%", color: "warning" },
        { title: "Underweight", value: 1, percent: "0%", color: "violet" },
        { title: "Normal", value: 1, percent: "0%", color: "success" },
        { title: "BB Stagnan", value: 1, percent: "0%", color: "info" },
        { title: "Overweight", value: 1, percent: "0%", color: "dark" },
      ],
      headers: [
        { text: 'Nama', value: 'nama' },
        { text: 'Posyandu', value: 'posyandu' },
        { text: 'Usia (bln)', value: 'usia' },
        { text: 'JK', value: 'gender', width: 60 },
        { text: 'Tgl Ukur Terakhir', value: 'tgl_ukur' },
        { text: 'Intervensi', value: 'intervensi' },
        { text: 'TB/U', value: 'tbu' },
        { text: 'BB/U', value: 'bbu' },
        { text: 'BB/TB', value: 'bbtb' }
      ],
      selectedAnak:'',
      kunjungan_posyandu: [
        {
          id:1,
          posyandu: 'Posyandu Mawar',
          status_gizi_kategori: 'BB/U',
          profile_anak:[
            {
              no_kk:'3127890384059987',
              nik_ayah:'1870965231876523',
              nama_ayah: 'Suhartanto',
              nik_ibu:'3127093421874560',
              nama_ibu: 'Fiska Bisatika'
            }
          ],
          kelahiran:[
            {
              no_kia: '1893456723456123',
              tmpt_dilahirkan:'Jakarta',
              tgl_lahir:'2024-03-22',
              bb:'3',
              pb:'50',
              anak_ke:'1',
              usia_ibu:'23',
              jarak:'-',
              jenis:'normal',
            }
          ],
          riwayat_penimbangan: [
            { tanggal: '22/05/2025', bb: '0.35', tb: '0.85', bb_tb: 'Stunting' },
            { tanggal: '18/06/2025', bb: '0.22', tb: '0.89', bb_tb: 'Stunting' },
            { tanggal: '20/07/2025', bb: '0.25', tb: '0.92', bb_tb: 'Stunting' },
          ],
          riwayat_intervensi: [
            { tanggal: '22/05/2025', kader: 'Siti R.', intervensi: 'PMT' },
            { tanggal: '18/06/2025', kader: 'Siti R.', intervensi: 'PMT' },
            { tanggal: '20/07/2025', kader: 'Siti R.', intervensi: 'PMT' },
          ],
          no_telp: '087838894555',
          alamat: 'Jalan Mewah',
          kecamatan: 'Bojong Gede',
          desa: 'Cimanggis',
          no_kia: '3403012605200002',
          nik_ayah:'1870965231876523',
          nama_ayah: 'Suhartanto',
          nik_ibu:'3127093421874560',
          nama_ibu: 'Fiska Bisatika',
          rw: '03',
          rt: '04',
          tgl_ukur:'2025-01-20',
          nama: 'Aluna Daneen Azqiara',
          nik: '3403012012930002',
          usia: 24,
          gender: 'P',
          intervensi: 'PMT',
          bbtb: 'Stunting',
          bbu: 3.3,
          tbu: 2.3
        },
        {
          id:2,
          posyandu: 'Posyandu Mawar',
          status_gizi_kategori: 'BB/U',
          profile_anak:[
            {
              no_kk:'3127890384059987',
              nik_ayah:'3321965231876523',
              nama_ayah: 'Dani',
              nama_ibu: 'Dini',
              nik_ibu:'3457093421874560',
            }
          ],
          kelahiran:[
            {
              no_kia: '1893456723456123',
              tmpt_dilahirkan:'Jakarta',
              tgl_lahir:'2024-03-22',
              bb:'3',
              pb:'50',
              anak_ke:'1',
              usia_ibu:'30',
              jarak:'-',
              jenis:'normal',
            }
          ],
          riwayat_penimbangan: [
            { tanggal: '22/05/2025', bb: '0.35', tb: '0.85', bb_tb: 'Stunting' },
            { tanggal: '18/06/2025', bb: '0.22', tb: '0.89', bb_tb: 'Stunting' },
            { tanggal: '20/07/2025', bb: '0.25', tb: '0.92', bb_tb: 'Stunting' },
          ],
          riwayat_intervensi: [
            { tanggal: '22/05/2025', kader: 'Siti R.', intervensi: 'PMT' },
            { tanggal: '18/06/2025', kader: 'Siti R.', intervensi: 'PMT' },
            { tanggal: '20/07/2025', kader: 'Siti R.', intervensi: 'PMT' },
          ],
          tgl_lahir: '20-01-2021',
          no_telp: '087838894555',
          alamat: 'Jalan Mewah',
          kecamatan: 'Bojong Gede',
          desa: 'Cimanggis',
          no_kia: '3403012605200002',
          nama_ayah: 'Dani',
          nama_ibu: 'Dini',
          rw: '03',
          rt: '04',
          tgl_ukur:'2025-01-20',
          nama: 'Arkhansa Raffasya Pamulat',
          nik: '3403010508980002',
          usia: 26,
          gender: 'L',
          intervensi: 'PMT',
          bbtb: 'Stunting',
          bb: 1.5,
          tb: 1.5,
          bb_tb: 1.5
        },
        {
          id:3,
          posyandu: 'Posyandu Mawar',
          status_gizi_kategori: 'BB/U',
          profile_anak:[
            {
              no_kk:'3127890384059987',
              nik_ayah:'332196037476523',
              nama_ayah: 'Suhartanto',
          nama_ibu: 'Fiska Bisatika',
              nik_ibu:'3123093421874560',
            }
          ],
          kelahiran:[
            {
              no_kia: '1871456723456123',
              tmpt_dilahirkan:'Jakarta',
              tgl_lahir:'2024-03-22',
              bb:'3',
              pb:'50',
              anak_ke:'1',
              usia_ibu:'30',
              jarak:'-',
              jenis:'normal',
            }
          ],
          riwayat_penimbangan: [
            { tanggal: '22/05/2025', bb: '0.35', tb: '0.85', bb_tb: 'Stunting' },
            { tanggal: '18/06/2025', bb: '1.22', tb: '1.89', bb_tb: 'Wasting' },
            { tanggal: '20/07/2025', bb: '2.3', tb: '2.3', bb_tb: 'Wasting' },
          ],
          riwayat_intervensi: [
            { tanggal: '22/05/2025', kader: 'Siti R.', intervensi: 'PMT' },
            { tanggal: '18/06/2025', kader: 'Siti R.', intervensi: 'PMT' },
            { tanggal: '20/07/2025', kader: 'Siti R.', intervensi: 'PMT' },
          ],
          tgl_lahir: '20-01-2021',
          no_telp: '087838894555',
          alamat: 'Jalan Mewah',
          kecamatan: 'Bojong Gede',
          desa: 'Cimanggis',
          no_kia: '3403012605200002',
          nama_ayah: 'Suhartanto',
          nama_ibu: 'Fiska Bisatika',
          rw: '03',
          rt: '04',
          tgl_ukur:'2025-01-20',
          nama: 'Askara Gedhe Manah Sinatrya',
          nik: '3403011105950001',
          usia: 20,
          gender: 'L',
          intervensi: 'BLT',
          bbtb: 'Wasting',
          bb: 2.3,
          tb: 2.3,
          bb_tb: 2.3
        },
        {
          id:4,
          posyandu: 'Posyandu Mawar',
          status_gizi_kategori: 'BB/U',
          profile_anak:[
            {
              no_kk:'3127890384059987',
              nik_ayah:'332196037476523',
              nama_ayah: 'Suhendra',
              nama_ibu: 'Milanti',
              nik_ibu:'3123093421874560',
            }
          ],
          kelahiran:[
            {
              no_kia: '3321456723456123',
              tmpt_dilahirkan:'Jakarta',
              tgl_lahir:'2024-03-22',
              bb:'3',
              pb:'50',
              anak_ke:'1',
              usia_ibu:'30',
              jarak:'-',
              jenis:'normal',
            }
          ],
          riwayat_penimbangan: [
            { tanggal: '22/05/2025', bb: '0.35', tb: '0.85', bb_tb: 'Stunting' },
            { tanggal: '18/06/2025', bb: '0.22', tb: '0.89', bb_tb: 'Stunting' },
            { tanggal: '20/07/2025', bb: '1.8', tb: '0.92', bb_tb: 'Underweight' },
          ],
          riwayat_intervensi: [
            { tanggal: '22/05/2025', kader: 'Siti R.', intervensi: 'PMT' },
            { tanggal: '18/06/2025', kader: 'Siti R.', intervensi: 'PMT' },
            { tanggal: '20/07/2025', kader: 'Siti R.', intervensi: 'PMT' },
          ],
          tgl_lahir: '20-01-2021',
          no_telp: '087838894555',
          alamat: 'Jalan Mewah',
          kecamatan: 'Bojong Gede',
          desa: 'Cimanggis',
          no_kia: '3403012605200002',
          nama_ayah: 'Suhendra',
          nama_ibu: 'Milanti',
          rw: '03',
          rt: '04',
          tgl_ukur:'2025-01-20',
          nama: 'Azka Maulana Fadil',
          nik: '3403011212980002',
          usia: 23,
          gender: 'L',
          intervensi: 'PKH',
          bbtb: 'Underweight',
          bb: 1.8,
          tb: 1.8,
          bb_tb: 1.8
        },
        {
          id:5,
          posyandu: 'Posyandu Mawar',
          status_gizi_kategori: 'BB/U',
           profile_anak:[
            {
              no_kk:'3127890384059987',
              nik_ayah:'332196037476523',
              nama_ayah: 'Hendra',
              nama_ibu: 'Manah',
              nik_ibu:'3123093421874560',
            }
          ],
          kelahiran:[
            {
              no_kia: '3321456723456123',
              tmpt_dilahirkan:'Jakarta',
              tgl_lahir:'2024-03-22',
              bb:'3',
              pb:'50',
              anak_ke:'1',
              usia_ibu:'30',
              jarak:'-',
              jenis:'normal',
            }
          ],
          riwayat_penimbangan: [
            { tanggal: '22/05/2025', bb: '0.35', tb: '0.85', bb_tb: 'Stunting' },
            { tanggal: '18/06/2025', bb: '0.22', tb: '0.89', bb_tb: 'Stunting' },
            { tanggal: '20/07/2025', bb: '0.25', tb: '0.92', bb_tb: 'Stunting' },
          ],
          riwayat_intervensi: [
            { tanggal: '22/05/2025', kader: 'Siti R.', intervensi: 'PMT' },
            { tanggal: '18/06/2025', kader: 'Siti R.', intervensi: 'PMT' },
            { tanggal: '20/07/2025', kader: 'Siti R.', intervensi: 'PMT' },
          ],
          tgl_lahir: '20-01-2021',
          no_telp: '087838894555',
          alamat: 'Jalan Mewah',
          kecamatan: 'Bojong Gede',
          desa: 'Cimanggis',
          no_kia: '3403012605200002',
          nama_ayah: 'Hendra',
          nama_ibu: 'Manah',
          rw: '03',
          rt: '04',
          tgl_ukur:'2025-01-20', nama: 'Irshad Ghani Arvarizi', nik: '3403012507930001', usia: 28, gender: 'L', intervensi: '-', bbtb: 'Normal', bb: 6.5, tb: 6.5, bb_tb: 6.5 },
        {
          id:6,
          posyandu: 'Posyandu Mawar',
          status_gizi_kategori: 'BB/U',
           profile_anak:[
            {
              no_kk:'3127890384059987',
              nik_ayah:'332196037476523',
              nama_ayah: 'Syafi',
              nama_ibu: 'Syiffa',
              nik_ibu:'3123093421874560',
            }
          ],
          kelahiran:[
            {
              no_kia: '3321456723456123',
              tmpt_dilahirkan:'Jakarta',
              tgl_lahir:'2024-03-22',
              bb:'3',
              pb:'50',
              anak_ke:'1',
              usia_ibu:'30',
              jarak:'-',
              jenis:'normal',
            }
          ],
          riwayat_penimbangan: [
            { tanggal: '22/05/2025', bb: '0.35', tb: '0.85', bb_tb: 'Stunting' },
            { tanggal: '18/06/2025', bb: '0.22', tb: '0.89', bb_tb: 'Stunting' },
            { tanggal: '20/07/2025', bb: '0.25', tb: '0.92', bb_tb: 'Stunting' },
          ],
          riwayat_intervensi: [
            { tanggal: '22/05/2025', kader: 'Siti R.', intervensi: 'PMT' },
            { tanggal: '18/06/2025', kader: 'Siti R.', intervensi: 'PMT' },
            { tanggal: '20/07/2025', kader: 'Siti R.', intervensi: 'PMT' },
          ],
          tgl_lahir: '20-01-2021',
          no_telp: '087838894555',
          alamat: 'Jalan Mewah',
          kecamatan: 'Bojong Gede',
          desa: 'Cimanggis',
          no_kia: '3403012605200002',
          nama_ayah: 'Syafi',
          nama_ibu: 'Syiffa',
          rw: '03',
          rt: '04',
          tgl_ukur:'2025-01-20', nama: 'Syiffa Azahra', nik: '3403012806910002', usia: 24, gender: 'P', intervensi: '-', bbtb: 'Normal', bb: 5.5, tb: 5.5, bb_tb: 5.5 },
      ],
    }
  },
  methods: {
    async loadConfigWithCache() {
      try {
        // cek di localStorage
        const cached = localStorage.getItem(this.configCacheKey)
        if (cached) {
          const parsed = JSON.parse(cached)
          this.logoSrc = parsed.logo || null
          return
        }
         // kalau belum ada cache, fetch dari API
        const res = await axios.get(`${baseURL}/api/config`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        const data = res.data?.data
        if (data) {
          this.logoSrc = data.logo || null
          // simpan di localStorage untuk load cepat di page berikutnya
          localStorage.setItem(this.configCacheKey, JSON.stringify(data))
        }
      }catch (error) {
        console.warn('Gagal load config:', error)
        this.logoLoaded = false
      }
    },
    async getWilayahUser() {
      try {
        const res = await axios.get(`${baseURL}/api/user/region`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        const wilayah = res.data
        this.kelurahan = wilayah.kelurahan || 'Tidak diketahui'
        this.id_wilayah = wilayah.id_wilayah // pastikan backend kirim ini

        // Setelah dapet id_wilayah, langsung fetch posyandu
        await this.fetchPosyanduByWilayah(this.id_wilayah)
      } catch (error) {
        console.error('Gagal ambil data wilayah user:', error)
        this.kelurahan = '-'
      }
    },
    async fetchPosyanduByWilayah(id_wilayah) {
      try {
        const res = await axios.get(`${baseURL}/api/posyandu/${id_wilayah}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })
        this.posyanduList = res.data
      } catch (err) {
        console.error('Gagal ambil data posyandu:', err)
      }
    },
    handlePosyanduChange() {
      console.log('Posyandu dipilih:', this.filters.posyandu)
      if (this.filters.posyandu) {
        this.rtReadonly = false
        this.rwReadonly = false
        this.fetchRwRtByPosyandu(this.filters.posyandu)
      } else {
        this.rtReadonly = true
        this.rwReadonly = true
        this.rwList = []
        this.rtList = []
      }
    },
    async fetchRwRtByPosyandu(idPosyandu) {
      try {
        const res = await axios.get(`${baseURL}/api/posyandu/${idPosyandu}/wilayah`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })
        this.rwList = res.data.rw || []
        this.rtList = res.data.rt || []
        console.log('data', res.data);

      } catch (err) {
        console.error('Gagal ambil RW/RT:', err)
      }
    },
    generatePeriodeOptions() {
      const bulan = [
        'Januari', 'Februari', 'Maret', 'April',
        'Mei', 'Juni', 'Juli', 'Agustus',
        'September', 'Oktober', 'November', 'Desember'
      ]

      const now = new Date()
      const result = []

      for (let i = 0; i < 12; i++) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
        const label = `${bulan[d.getMonth()]} ${d.getFullYear()}`
        result.push(label)
      }

      this.periodeOptions = result
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    getTodayDate() {
      const hari = [
        'Minggu', 'Senin', 'Selasa', 'Rabu',
        'Kamis', 'Jumat', 'Sabtu'
      ]
      const bulan = [
        'Januari', 'Februari', 'Maret', 'April',
        'Mei', 'Juni', 'Juli', 'Agustus',
        'September', 'Oktober', 'November', 'Desember'
      ]
      const now = new Date()
      return `${hari[now.getDay()]}, ${now.getDate()} ${bulan[now.getMonth()]} ${now.getFullYear()}`
    },
    getThisMonth() {
      const bulan = [
        'Januari', 'Februari', 'Maret', 'April',
        'Mei', 'Juni', 'Juli', 'Agustus',
        'September', 'Oktober', 'November', 'Desember'
      ]

      const now = new Date()
      let monthIndex = now.getMonth() - 1
      let year = now.getFullYear()

      // kalau sekarang Januari (0), berarti mundur ke Desember tahun sebelumnya
      if (monthIndex < 0) {
        monthIndex = 11
        year -= 1
      }

      return `${bulan[monthIndex]} ${year}`
    },
    handleResize() {
      this.windowWidth = window.innerWidth
      if (this.windowWidth < 992) {
        this.isCollapsed = true // auto collapse di tablet/mobile
      } else {
        this.isCollapsed = false // normal lagi di desktop
      }
    },
    toggleExpandForm() {
      this.isFormOpen = !this.isFormOpen
    },
    showDetail(props) {
      console.log('Klik props:', props)
      this.selectedAnak = props
    },
    applyFilter() {
      console.log('Filter diterapkan:', this.filters)
      // nanti bisa dipakai buat fetch data laporan
    },
    triggerFileDialog() {
      if (this.$refs.fileInput) {
        this.$refs.fileInput.click()
      }
    },

    onDragOver(e) {
      this.isDataDrag = true
      console.log(e);
    },
    onDragLeave(e) {
      this.isDataDrag = false
      console.log(e);
    },

    handleFileChange(e) {
      const file = e.target.files && e.target.files[0]
      if (!file) return
      this.setFile(file)
      // reset input value biar bisa pilih file sama lagi kalau ingin
      e.target.value = ''
    },

    handleDrop(e) {
      this.isDataDrag = false
      const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]
      if (!file) return
      this.setFile(file)
    },

    setFile(file) {
      this.fileError = ''
      // validasi
      const valid = this.validateFile(file)
      if (!valid.valid) {
        this.file = null
        this.fileName = ''
        this.fileSize = 0
        this.filePreviewLines = ''
        this.fileError = valid.message
        return
      }

      this.file = file
      this.fileName = file.name
      this.fileSize = file.size
      this.fileError = ''

      // baca beberapa byte pertama untuk preview (opsional)
      this.previewFileContent(file)
    },

    validateFile(file) {
      // ext
      const nameParts = (file.name || '').split('.')
      const ext = nameParts.length > 1 ? nameParts.pop().toLowerCase() : ''
      if (!this.ACCEPTED_EXT.includes(ext)) {
        return { valid: false, message: 'Format file tidak didukung. Hanya .csv yang diperbolehkan.' }
      }

      // mime (beberapa browser pakai text/plain)
      if (this.ACCEPTED_MIME.length && !this.ACCEPTED_MIME.includes(file.type) && file.type !== '') {
        // dimungkinkan file.type kosong di beberapa OS, jadi jangan terlalu strict
        return { valid: false, message: 'Tipe file tidak valid (MIME mismatch).' }
      }

      if (file.size > this.MAX_FILE_SIZE) {
        return { valid: false, message: `Ukuran file terlalu besar. Maks ${this.humanFileSize(this.MAX_FILE_SIZE)}.` }
      }

      return { valid: true }
    },

    previewFileContent(file) {
      const reader = new FileReader()
      reader.onload = (ev) => {
        const text = (ev.target.result || '').toString()
        // ambil 1-2 baris pertama untuk preview, sanitasi
        const lines = text.split(/\r?\n/).filter(Boolean)
        this.filePreviewLines = lines.length ? lines.slice(0,2).join(' | ') : ''
      }
      // baca sebagian saja untuk efisiensi (readAsText membaca seluruh file — acceptable untuk CSV kecil)
      reader.readAsText(file.slice(0, 2000))
    },

    removeFile() {
      this.file = null
      this.fileName = ''
      this.fileSize = 0
      this.filePreviewLines = ''
      this.fileError = ''
    },

    humanFileSize(size) {
      if (!size) return '0 B'
      const i = Math.floor(Math.log(size) / Math.log(1024))
      const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
      return (size / Math.pow(1024, i)).toFixed(i ? 1 : 0) + ' ' + sizes[i]
    },

    async uploadFile() {
      if (!this.file) {
        this.fileError = 'Tidak ada file untuk di-upload.'
        return
      }

      // Ubah URL ini sesuai backend-mu
      const UPLOAD_URL = '/api/uploads/csv' // <-- sesuaikan

      const formData = new FormData()
      formData.append('file', this.file)

      try {
        this.uploading = true
        this.uploadProgress = 0
        this.fileError = ''

        const res = await axios.post(UPLOAD_URL, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          },
          onUploadProgress: (progressEvent) => {
            if (progressEvent.lengthComputable) {
              this.uploadProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total)
            }
          }
        })

        // sukses — tangani response sesuai API
        this.$bvToast && this.$bvToast.toast
          ? this.$bvToast.toast('Upload berhasil', { variant: 'success' })
          : console.log('Upload berhasil', res.data)

        // reset file atau lakukan apa yg dibutuhkan
        this.removeFile()
      } catch (err) {
        console.error('Upload error', err)
        this.fileError = (err.response && err.response.data && err.response.data.message) || 'Gagal upload file.'
      } finally {
        this.uploading = false
        this.uploadProgress = 0
      }
    },

    goToDetail(id) {
      // misal arahkan ke route detail anak
      this.$router.push({ name: 'AnakDetail', params: { id } })
    },
  },
  created() {
    const storedEmail = localStorage.getItem('userEmail')
    if (storedEmail) {
      let namePart = storedEmail.split('@')[0]
      namePart = namePart.replace(/[._]/g, ' ')
      this.username = namePart
        .split(' ')
        .map(w => w.charAt(0).toUpperCase() + w.slice(1))
        .join(' ')
    } else {
      this.username = 'User'
    }
    this.today = this.getTodayDate()
    this.thisMonth = this.getThisMonth()
  },
  async mounted() {
    this.isLoading = true

    try {
      // Ambil data dulu
      await this.getWilayahUser()
      this.generatePeriodeOptions()
      this.filters.kelurahan = this.kelurahan
      await this.loadConfigWithCache()
      this.handleResize()
      window.addEventListener('resize', this.handleResize)
    } catch (err) {
      console.error('Error loading data:', err)
    } finally {
      // Kasih sedikit delay biar animasi fade spinner kelihatan
      setTimeout(() => {
        this.isLoading = false
      }, 300)
    }
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
  },
}
</script>

<style scoped>
  .collapse-enter-active,
  .collapse-leave-active {
    transition: all 0.3s ease;
    overflow: hidden;
  }

  .collapse-enter-from,
  .collapse-leave-to {
    max-height: 0;
    opacity: 0;
  }

  .collapse-enter-to,
  .collapse-leave-from {
    max-height: 1000px; /* cukup besar agar muat konten */
    opacity: 1;
  }
  /* Timeline Style */
  .timeline li {
    position: relative;
    padding-left: 20px;
    margin-bottom: 10px;
    font-size: 0.9rem;
  }
  .timeline .dot {
    position: absolute;
    left: 0;
    top: 4px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
  }

  .table-modern {
    border: none !important;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  }

  /* Header */
  .table-modern th {
    background-color: var(--bs-primary) !important; /* primary */
    color: #fff !important;
    font-weight: 600;
    padding: 0.75rem;
    text-align: left;
  }

  /* Cell */
  .table-modern td {
    vertical-align: middle;
    padding: 0.65rem 0.75rem;
    border-bottom: 1px solid #f1f1f1;
  }

  /* Row hover */
  .table-modern tr:hover {
    background-color: rgba(13, 110, 253, 0.08) !important;
    transition: background 0.2s ease-in-out;
  }

  /* Pagination & footer */
  .table-modern .pagination {
    margin-top: 1rem;
  }

  .table-modern .pagination .page-link {
    border-radius: 0.5rem;
    color: var(--bs-primary);
  }

  .table-modern .pagination .active .page-link {
    background-color: #6c757d; /* secondary */
    border-color: #6c757d;
    color: #fff;
  }

  .progress-bar {
    transition: width 0.4s ease-in-out;
  }
  .progress-bar[data-progress='low'] {
    background-color: var(--bs-primary); /* biru awal */
  }
  .progress-bar[data-progress='mid'] {
    background-color: #ffc107; /* kuning tengah */
  }
  .progress-bar[data-progress='high'] {
    background-color: #198754; /* hijau akhir */
  }
  /* di <style scoped> */
  .my-striped tr:nth-child(even) {
    background-color: #f8f9fa !important;
  }
  .my-striped tr:nth-child(odd) {
    background-color: #ffffff !important;
  }
  .dropzone-full {
    cursor: pointer;
    transition: background-color .15s ease, border-color .15s ease;
    min-height: 120px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }

  .dropzone-full.border-primary {
    border-width: 2px !important;
  }

  .dropzone-full .bi {
    opacity: 0.9;
  }

  /* state ketika drag */
  .dropzone-full.bg-light {
    background-color: #f8fafc !important;
  }

  /* optional: fokus keyboard */
  .dropzone-full:focus {
    outline: none;
    box-shadow: 0 0 0 4px rgba(13,110,253,0.12);
  }

  .border-violet { border-color: #4f0891 !important; } /* Overweight - Hitam */
  .text-violet {color: #4f0891 !important;}

  /* === Warna garis sesuai item.color === */
  .stroke-danger polyline {
    stroke: #dc3545;
  }
  .stroke-warning polyline {
    stroke: #ffc107;
  }
  .stroke-success polyline {
    stroke: #198754;
  }
  .stroke-info polyline {
    stroke: #0dcaf0;
  }
  .stroke-dark polyline {
    stroke: #343a40;
  }
  .stroke-violet polyline {
    stroke: #4f0891;
  }
  /* Hover efek */
  .card:hover .svg-line polyline {
    opacity: 1;
    stroke-width: 3;
    transition: all 0.3s ease;
  }
</style>
