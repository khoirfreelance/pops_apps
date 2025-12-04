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
    <HeaderAdmin />

    <div
      class="content flex-grow-1 d-flex flex-column flex-md-row"
      :class="{
        'sidebar-collapsed': isCollapsed,
        'sidebar-expanded': !isCollapsed
      }"
    >
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar"   />

      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <!-- Content -->
        <div class="py-4 container-fluid" >

          <!-- Welcome Card -->
          <Welcome />

          <!-- Nav Tab-->
          <div class="container-fluid my-2 d-flex justify-content-center">
            <ul
              class="nav nav-pills d-flex flex-wrap justify-content-center gap-2 w-100"
              id="myTab"
              role="tablist"
              style="max-width: 800px;"
            >
              <li class="nav-item flex-fill text-center" role="presentation">
                <button
                  class="nav-link active w-100 text-truncate"
                  id="anak-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#anak-tab-pane"
                  type="button"
                  role="tab"
                  aria-controls="anak-tab-pane"
                  aria-selected="true"
                  @click="menu('anak')"
                >
                  Gizi Anak
                </button>
              </li>

              <li class="nav-item flex-fill text-center" role="presentation">
                <button
                  class="nav-link w-100 text-truncate"
                  id="bumil-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#bumil-tab-pane"
                  type="button"
                  role="tab"
                  aria-controls="bumil-tab-pane"
                  aria-selected="false"
                  @click="menu('bumil')"
                >
                  Ibu Hamil
                </button>
              </li>

              <li class="nav-item flex-fill text-center" role="presentation">
                <button
                  class="nav-link w-100 text-truncate"
                  id="catin-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#catin-tab-pane"
                  type="button"
                  role="tab"
                  aria-controls="catin-tab-pane"
                  aria-selected="false"
                  @click="menu('catin')"
                >
                  Calon Pengantin
                </button>
              </li>
            </ul>
          </div>

          <!-- Panel tab-->
          <div class="tab-content" id="myTabContent">
            <!-- Import Anak -->
            <div v-if="formOpen" class="card p-3 my-3">
              <div class="d-flex justify-content-between">
                <h3>Form Gizi Anak</h3>
                <button class="btn btn-outline-danger" @click="formOpen=!formOpen">
                  X
                </button>
              </div>
              <div class="row g-2">
                <div class="col-md-4">
                  <label>NIK</label>
                  <input type="text" class="form-control" v-model="form.nik" readonly>
                </div>

                <div class="col-md-4">
                  <label>Nama Anak</label>
                  <input type="text" class="form-control" v-model="form.nama_anak" :readonly="form.mode === 'input'">
                </div>

                <div v-if="form.mode === 'update'" class="col-md-4">
                  <label>Nama Orang Tua</label>
                  <input type="text" class="form-control" v-model="form.nama_ortu">
                </div>

                <div v-if="form.mode === 'input'" class="col-md-4">
                  <label>Tanggal Pengukuran</label>
                  <input type="date" class="form-control" v-model="form.tgl_pengukuran">
                </div>

                <div class="col-md-4">
                  <label>Berat Badan (kg)</label>
                  <input type="text" class="form-control" v-model="form.bb">
                </div>

                <div class="col-md-4">
                  <label>Tinggi Badan (cm)</label>
                  <input type="text" class="form-control" v-model="form.tb">
                </div>

                <div class="col-md-4">
                  <label>Lingkar Kepala</label>
                  <input type="text" class="form-control" v-model="form.lika">
                </div>
                <input type="hidden" v-model="form.tgl_lahir">
                <input type="hidden" v-model="form.gender">

                <div class="col-12">
                  <button class="btn btn-primary mt-3" @click="submitUpdate">
                    <i class="bi bi-pencil-square"></i> {{ form.mode == 'update'? 'Ubah Data' : 'Rekam Baru'}}
                  </button>
                  <button class="btn btn-secondary mt-3 ms-2" @click="resetForm">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                  </button>
                </div>
              </div>
            </div>

            <div v-if="isUploadOpen" class="card p-3 my-3">
              <div class="d-flex justify-content-between align-item-center">
                <h5>Upload Data</h5>
                <button
                  @click="isUploadOpen = !isUploadOpen"
                  class="btn btn-sm btn-outline-danger mb-2"
                >
                X
                </button>
              </div>

              <div class="row g-2">
                <div class="alert alert-success">
                  <ul>
                    <li>Pastikan data yang diimport, berformat csv</li>
                    <li>Pastikan data sudah lengkap sebelum di import</li>
                    <li>
                      Silahkan unduh contoh dengan klik
                      <a href="/example_kunjungan_posyandu.csv">Example.csv</a>
                    </li>
                  </ul>
                </div>

                <input
                  ref="fileInput"
                  type="file"
                  accept=".csv,text/csv"
                  class="form-control"
                  @change="handleFileChange($event)"
                />

                <!-- Preview / status -->
                <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <div v-if="filePreviewTable.length" class="mt-3">
                    <p class="fw-bold mb-1 text-danger">
                      *Preview (2 baris pertama):
                    </p>
                    <div class="table-responsive">
                      <table class="table table-bordered table-sm small border-danger">
                        <thead>
                          <tr>
                            <th v-for="(col, index) in filePreviewTable[0]" :key="'h'+index" width="120" class="text-danger">
                              {{ col }}
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(row, rIndex) in filePreviewTable.slice(1)" :key="'r'+rIndex">
                            <td v-for="(col, cIndex) in row" :key="'c'+cIndex" class="text-danger">
                              {{ col }}
                            </td>
                          </tr>
                          <tr v-for="(row, rIndex) in filePreviewTable.slice(2)" :key="'r'+rIndex">
                            <td v-for="(col, cIndex) in row" :key="'c'+cIndex" class="text-danger">
                              ...
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                  </div>

                  <div v-else class="text-muted small">Belum ada file dipilih</div>
                </div>

                <div v-if="fileError" class="mt-2 text-danger small">
                  {{ fileError }}
                </div>

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
                    @click="uploadCSV"
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
            </div>

            <div class="tab-pane fade show active" id="anak-tab-pane" role="tabpanel" tabindex="0">
              <div class="card shadow-sm">
                <div class="card-body">
                  <!-- Search + Button -->
                  <div class="d-flex align-items-center justify-content-end gap-2">

                    <input
                      type="text"
                      class="form-control form-control-sm"
                      style="width: 220px;"
                      placeholder="Ketik NIK atau Nama"
                      v-model="searchQuery_kunAn"
                    >

                    <button
                      class="btn btn-primary btn-sm"
                      type="button"
                      @click="isUploadOpen = !isUploadOpen"
                    >
                      <i class="bi bi-filetype-csv me-1"></i> Import Kunjungan
                    </button>
                    <button
                      class="btn btn-success btn-sm"
                      type="button"
                      @click="isUploadOpen = !isUploadOpen"
                    >
                      <i class="bi bi-filetype-csv me-1"></i> Import Pendampingan
                    </button>
                    <button
                      class="btn btn-outline-primary btn-sm"
                      type="button"
                      @click="isUploadOpen = !isUploadOpen"
                    >
                      <i class="bi bi-filetype-csv me-1"></i> Import Intervensi
                    </button>
                  </div>
                  <!-- Table -->
                  <div class="mt-3">

                    <!-- Search + Row Per Page -->
                    <easy-data-table
                      :headers="headers_kunAn"
                      :items="items_kunAn"
                      :sortable="true"
                      :search-value="searchQuery_kunAn"
                      :rows-per-page="perPage"
                      :rows-items="perPageOptions"
                      :rows-per-page-text="'Rows per page'"
                      header-text-direction="center"
                      table-class-name="my-custom-table"
                      header-class-name="my-custom-header"
                      show-index
                      alternating
                      border-cell
                    >

                      <!-- ACTION BUTTONS -->
                      <template
                        #item-action="items"
                      >
                        <div class="action-wrapper d-flex gap-1 m-1 text-center">
                          <button @click="inputItem(items)" class="btn btn-primary" data-bs-toggle="tooltip" title="Tambah">
                            <i class="bi bi-plus-square"></i>
                          </button>
                          <button @click="editItem(items)" class="btn btn-secondary" data-bs-toggle="tooltip" title="Ubah">
                            <i class="bi bi-pencil-square"></i>
                          </button>
                          <button @click="delItem(items)" class="btn btn-danger" data-bs-toggle="tooltip" title="Hapus">
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </template>

                    </easy-data-table>
                  </div>

                </div>
              </div>
            </div>

            <!-- Import Bumil -->
            <div class="tab-pane fade" id="bumil-tab-pane" role="tabpanel" tabindex="0">
              <div v-if="formOpen_bumil" class="card p-3 my-3">
                <div class="d-flex justify-content-between">
                  <h3>Form Ibu Hamil</h3>
                  <button class="btn btn-outline-danger" @click="formOpen_bumil=!formOpen_bumil">
                    X
                  </button>
                </div>
                <div class="row g-2">
                  <div class="col-md-4">
                    <label>NIK</label>
                    <input type="text" class="form-control" v-model="form_bumil.nik" readonly>
                  </div>

                  <div class="col-md-4">
                    <label>Nama Anak</label>
                    <input type="text" class="form-control" v-model="form_bumil.nama_anak" :readonly="form.mode === 'input'">
                  </div>

                  <div v-if="form.mode === 'update'" class="col-md-4">
                    <label>Nama Orang Tua</label>
                    <input type="text" class="form-control" v-model="form_bumil.nama_ortu">
                  </div>

                  <div v-if="form.mode === 'input'" class="col-md-4">
                    <label>Tanggal Pengukuran</label>
                    <input type="date" class="form-control" v-model="form_bumil.tgl_pengukuran">
                  </div>

                  <div class="col-md-4">
                    <label>Berat Badan (kg)</label>
                    <input type="text" class="form-control" v-model="form_bumil.bb">
                  </div>

                  <div class="col-md-4">
                    <label>Tinggi Badan (cm)</label>
                    <input type="text" class="form-control" v-model="form_bumil.tb">
                  </div>

                  <div class="col-md-4">
                    <label>Lingkar Kepala</label>
                    <input type="text" class="form-control" v-model="form_bumil.lika">
                  </div>
                  <input type="hidden" v-model="form_bumil.tgl_lahir">
                  <input type="hidden" v-model="form_bumil.gender">

                  <div class="col-12">
                    <button class="btn btn-primary mt-3" @click="submitUpdate">
                      <i class="bi bi-pencil-square"></i> {{ form_bumil.mode == 'update'? 'Ubah Data' : 'Rekam Baru'}}
                    </button>
                    <button class="btn btn-secondary mt-3 ms-2" @click="resetForm">
                      <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                  </div>
                </div>
              </div>

              <div v-if="isUploadOpen_bumil" class="card p-3 my-3">
                <div class="d-flex justify-content-between align-item-center">
                  <h5>Upload Data</h5>
                  <button
                    @click="isUploadOpen_bumil = !isUploadOpen_bumil"
                    class="btn btn-sm btn-outline-danger mb-2"
                  >
                  X
                  </button>
                </div>

                <div class="row g-2">
                  <div class="alert alert-success">
                    <ul>
                      <li>Pastikan data yang diimport, berformat csv</li>
                      <li>Pastikan data sudah lengkap sebelum di import</li>
                      <li>
                        Silahkan unduh contoh dengan klik
                        <a href="/example_kunjungan_posyandu.csv">Example.csv</a>
                      </li>
                    </ul>
                  </div>

                  <input
                    ref="fileInput"
                    type="file"
                    accept=".csv,text/csv"
                    class="form-control"
                    @change="handleFileChange($event)"
                  />

                  <!-- Preview / status -->
                  <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div v-if="filePreviewTable_bumil.length" class="mt-3">
                      <p class="fw-bold mb-1 text-danger">
                        *Preview (2 baris pertama):
                      </p>
                      <div class="table-responsive">
                        <table class="table table-bordered table-sm small border-danger">
                          <thead>
                            <tr>
                              <th v-for="(col, index) in filePreviewTable_bumil[0]" :key="'h'+index" width="120" class="text-danger">
                                {{ col }}
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(row, rIndex) in filePreviewTable_bumil.slice(1)" :key="'r'+rIndex">
                              <td v-for="(col, cIndex) in row" :key="'c'+cIndex" class="text-danger">
                                {{ col }}
                              </td>
                            </tr>
                            <tr v-for="(row, rIndex) in filePreviewTable_bumil.slice(2)" :key="'r'+rIndex">
                              <td v-for="(col, cIndex) in row" :key="'c'+cIndex" class="text-danger">
                                ...
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                    </div>

                    <div v-else class="text-muted small">Belum ada file dipilih</div>
                  </div>

                  <div v-if="fileError" class="mt-2 text-danger small">
                    {{ fileError }}
                  </div>

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
                      @click="uploadCSV"
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
              </div>

              <div class="card shadow-sm">
                <div class="card-body">
                  <!-- Search + Button -->
                  <div class="d-flex align-items-center justify-content-end gap-2">

                    <input
                      type="text"
                      class="form-control form-control-sm"
                      style="width: 220px;"
                      placeholder="Ketik NIK atau Nama"
                      v-model="searchQuery_bumil"
                    >

                    <button
                      class="btn btn-primary btn-sm"
                      type="button"
                      @click="isUploadOpen_bumil = !isUploadOpen_bumil"
                    >
                      <i class="bi bi-filetype-csv me-1"></i> Import Pendampingan
                    </button>
                    <button
                      class="btn btn-outline-primary btn-sm"
                      type="button"
                      @click="isUploadOpen_bumil = !isUploadOpen_bumil"
                    >
                      <i class="bi bi-filetype-csv me-1"></i> Import Intervensi
                    </button>
                  </div>
                  <!-- Table -->
                  <div class="mt-3">

                    <!-- Search + Row Per Page -->
                    <!-- <easy-data-table
                      :headers="headers_bumil"
                      :items="items_bumil"
                      :sortable="true"
                      :search-value="searchQuery_bumil"
                      :rows-per-page="perPage"
                      :rows-items="perPageOptions"
                      :rows-per-page-text="'Rows per page'"
                      header-text-direction="center"
                      table-class-name="my-custom-table"
                      header-class-name="my-custom-header"
                      show-index
                      alternating
                      border-cell
                    > -->

                      <!-- ACTION BUTTONS -->
                      <!-- <template
                        #item-action="items"
                      >
                        <div class="action-wrapper d-flex gap-1 m-1 text-center">
                          <button @click="inputItem(items)" class="btn btn-primary" data-bs-toggle="tooltip" title="Tambah">
                            <i class="bi bi-plus-square"></i>
                          </button>
                          <button @click="editItem(items)" class="btn btn-secondary" data-bs-toggle="tooltip" title="Ubah">
                            <i class="bi bi-pencil-square"></i>
                          </button>
                          <button @click="delItem(items)" class="btn btn-danger" data-bs-toggle="tooltip" title="Hapus">
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </template> -->

                    <!-- </easy-data-table> -->
                  </div>

                </div>
              </div>
            </div>
            <!-- Import Catin -->
            <div class="tab-pane fade" id="catin-tab-pane" role="tabpanel" tabindex="0">
              CATIN
            </div>
          </div>

        </div>
        <CopyRight class="mt-auto" />
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

  <!-- Modal Success -->
  <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title">Berhasil</h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body text-center">
          <h5 class="mb-0">{{ successMessage || 'Konfigurasi berhasil disimpan.' }}</h5>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">
            OK
          </button>
        </div>
      </div>
    </div>
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
          <h5 class="mb-0">{{ errorMessage || 'Terjadi kesalahan yang tidak diketahui.' }}</h5>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">
            OK
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Warning -->
  <div class="modal fade" id="warningModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header bg-secondary rounded-top-4">
          <h5 class="modal-title">Konfirmasi</h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body text-center">
          <h5 class="mb-0">{{ confirmMessage || 'Apakah anda yakin?' }}</h5>
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
import axios from 'axios'
import Welcome from '@/components/Welcome.vue'
import EasyDataTable from 'vue3-easy-data-table'
import { Modal } from 'bootstrap'
import 'vue3-easy-data-table/dist/style.css'

// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;
// inisialisasi DataTables agar pakai styling Bootstrap 5

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Import',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, Welcome, EasyDataTable, },
  data() {
    return {
      isLoadingImport: false,
      importProgress: 0,
      animatedProgress: 0,
      currentRow: 0,
      totalRows: 1,
      form: {
        mode:"",
        tgl_pengukuran:"",
        nik: "",
        nama_anak: "",
        nama_ortu: "",
        bb: "",
        tb: "",
        lika:"",
        unit_posyandu: "",
        gender: "",
        tgl_lahir: "",
      },
      sortKey: '',
      sortOrder: 'asc',

      headers_bumil: [
        { text: "Nama", key: "nama", sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: "Kehamilan ke", key: "ke", sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: 'Hb', value: 'action', width: 120, align: "center", class: "align-middle text-center cursor-pointer" },
        { text: 'Lingkar Lengan', value: 'action', width: 120, align: "center", class: "align-middle text-center cursor-pointer" },
        { text: "Usia (Tahun)", key: "usia", sortable: true, class: "align-middle text-center cursor-pointer", width: "100px" },
        { text: "HPL", key: "hpl", sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: 'Action', value: 'action', width: 120, align: "center", class: "col-action" },
      ],
      headers_kunAn: [
        { text: 'NIK', value: 'nik', sortable: true },
        { text: 'Nama Anak', value: 'nama_anak', sortable: true },
        { text: 'Nama Orang Tua', value: 'nama_ortu', sortable: true },
        { text: 'Jenis Kelamin', value: 'gender', sortable: true },
        { text: 'Tanggal Lahir', value: 'tgl_lahir', sortable: true },
        { text: 'BB', value: 'bb', sortable: true },
        { text: 'TB', value: 'tb', sortable: true },
        { text: 'Unit Posyandu', value: 'unit_posyandu', sortable: true },
        { text: 'Action', value: 'action', width: 120, align: "center", class: "col-action" },
      ],
      formOpen : false,
      searchQuery_kunAn:"",
      searchQuery_bumil: "",
      currentPage: 1,
      perPage: 10,
      perPageOptions: [5, 10, 25, 50],
      isUploadOpen: false,
      tableOptions: {
        responsive: true,
        pageLength: 10,
        destroy: true, // penting agar bisa di-refresh
      },
      aktifitas_anak:'kunjungan',
      isLoading: true,
      isCollapsed: false,
      username: '',
      today: '',
      thisMonth: '',
      kelurahan: '',
      windowWidth: window.innerWidth,
      configCacheKey: 'site_config_cache',
      activeMenu: 'anak', // default tampilan awal
      dataLoad:[],
      file: null,
      fileName: '',
      fileSize: 0,
      filePreviewLines: '',
      fileError: '',
      uploading: false,
      uploadProgress: 0,
      // config
      ACCEPTED_EXT: ['csv'],
      ACCEPTED_MIME: ['text/csv', 'application/vnd.ms-excel', 'text/plain'],
      MAX_FILE_SIZE: 5 * 1024 * 1024, // 5 MB
      filePreviewTable: []
    }
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
  computed: {
    filteredData() {
      //console.log('filter data: ', this.paginatedData[1]);

      if (!this.paginatedData[1]) {
        return []
      }

      if (Array.isArray(this.paginatedData[1])) {
        console.warn("dataLoad is ARRAY, expected OBJECT mapping NIK → Data!")
      }

      const arr = Object.values(this.paginatedData[1])
      const q = this.searchQuery.toLowerCase()
      const result = arr.filter(item => {
        const match =
          item.nama_perempuan?.toLowerCase().includes(q) ||
          item.nama_laki?.toLowerCase().includes(q) ||
          item.nik_perempuan?.includes(q) ||
          item.nik_laki?.includes(q)

        return match
      })
      return result
    },

    filteredBumil() {
      if (!this.dataLoad) return [];
      console.log('bumil', this.dataLoad);

      const arr = Array.isArray(this.dataLoad)
        ? this.dataLoad
        : Object.values(this.dataLoad);

      const q = this.searchQuery_bumil?.toLowerCase() ?? '';

      return arr.filter(item => {
        return (
          item.nama_ibu?.toLowerCase().includes(q) ||
          item.nik_ibu?.includes(q)
        );
      });
    },
    items_bumil(){
      return this.filteredBumil.map((item) => ({
        nik: item.nik_ibu ?? "-",
        nama_ibu: item.nama_ibu ?? "-",
        kehamilan_ke: item.kehamilan_ke ?? "-",
        hb: item.hb ?? "-",
        lila: item.lila ?? "-",
        usia: item.usia ?? "-",
        hpl: item.hpl ?? "-",
        // 🔥 INI YANG PENTING
        action: { ...item }
      }));
    },

    filteredAnak() {
      if (!this.dataLoad) return [];

      const arr = Array.isArray(this.dataLoad)
        ? this.dataLoad
        : Object.values(this.dataLoad);

      const q = this.searchQuery_kunAn?.toLowerCase() ?? "";

      return arr.filter(item => {
        return (
          item.nama?.toLowerCase().includes(q) ||
          item.nik?.toString().includes(q)
        );
      });
    },
    items_kunAn() {
      return this.filteredAnak.map((item) => ({
        nik: item.nik ?? "-",
        nama_anak: item.nama ?? "-",
        nama_ortu: item.keluarga?.[0]?.nama_ortu ?? "-",
        gender: item.jk === "L" ? "Laki-laki" : "Perempuan",
        tgl_lahir: this.formatDate(item.kelahiran?.[0]?.tgl_lahir),
        bb: item.posyandu?.[0]?.bb ?? "-",
        tb: item.posyandu?.[0]?.tb ?? "-",
        unit_posyandu: item.posyandu?.[0]?.posyandu ?? "-",
        // 🔥 INI YANG PENTING
        action: { ...item }
      }));
    }
  },
  methods: {
    resetForm(){
      this.formOpen = false;
      this.form = {
        ...this.form,
        mode:"",
        nik: "",
        nama_anak: "",
        nama_ortu:  "",
        bb: "",
        tb: "",
        unit_posyandu: "",
        gender: "",
        tgl_lahir: ""
      };
    },
    confirmModal(message) {
      this.confirmMessage = message || 'Apakah anda yakin?'

      return new Promise((resolve) => {
        const modalEl = document.getElementById('warningModal')
        // eslint-disable-next-line no-undef
        const modal = new bootstrap.Modal(modalEl)

        // ketika tombol OK ditekan → resolve(true)
        const okBtn = modalEl.querySelector('.btn-success')
        const handler = () => {
          resolve(true)
          okBtn.removeEventListener('click', handler)
        }
        okBtn.addEventListener('click', handler)

        modal.show()
      })
    },
    /* showConfirm(message) {
      this.confirmMessage = message || 'Apakah anda yakin?'
      // eslint-disable-next-line no-undef
      const modal = new bootstrap.Modal(document.getElementById('warningModal'))
      modal.show()
    }, */
    showSuccess(message) {
      this.successMessage = message || 'Berhasil tersimpan.'
      // eslint-disable-next-line no-undef
      const modal = new bootstrap.Modal(document.getElementById('successModal'))
      modal.show()
    },
    showError(message) {
      this.errorMessage = message || 'Terjadi kesalahan.'
      // eslint-disable-next-line no-undef
      const modal = new bootstrap.Modal(document.getElementById('errorModal'))
      modal.show()
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
    // Prefill form
    editItem(item) {
      this.formOpen = true;

      this.form = {
        ...this.form,
        mode:'update',
        nik: item.nik ?? "",
        nama_anak: item.nama_anak ?? "",
        nama_ortu: item.nama_ortu ?? "",
        bb: item.bb ?? "",
        tb: item.tb ?? "",
        unit_posyandu: item.unit_posyandu ?? "",
        gender: item.gender ?? "",
        tgl_lahir: item.tgl_lahir ?? ""
      };
    },
    inputItem(item) {
      this.formOpen = true;

      this.form = {
        ...this.form,
        mode:'input',
        nik: item.nik ?? "",
        nama_anak: item.nama_anak ?? "",
        nama_ortu: item.nama_ortu ?? "",
        //bb: item.bb ?? "",
        //tb: item.tb ?? "",
        unit_posyandu: item.unit_posyandu ?? "",
        gender: item.gender ?? "",
        tgl_lahir: item.tgl_lahir ?? ""
      };
    },
    async submitUpdate() {
      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0

      try {
        // MODE UPDATE → PUT
        if (this.form.mode === 'update') {
          const res = await axios.put(
            `${baseURL}/api/children/${this.form.nik}`,
            this.form,
            {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: `Bearer ${localStorage.getItem('token')}`,
              }
            }
          )

          await this.loadData()
          this.resetForm()
          this.showSuccess(res.data.message)
          setTimeout(() => (this.showSuccess(res.data.message)), 3000)
        }
        // MODE INPUT → POST
        else {
          const res = await axios.post(
            `${baseURL}/api/children`,
            this.form,
            {
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: `Bearer ${localStorage.getItem('token')}`,
              }
            }
          )

          await this.loadData()
          this.resetForm()
          this.showSuccess(res.data.message)
          setTimeout(() => (this.showSuccess(res.data.message)), 3000)
        }
      } catch (e) {
        //console.error(e)
        this.showError(e)
        //alert("Gagal menyimpan data")
      }finally{
        this.isLoadingImport = false
      }
    },
    // Delete via backend
    async delItem(item) {
      const nik = item.nik
      // Pakai modal, bukan confirm()
      const confirmed = await this.confirmModal("Yakin ingin menghapus data ini?")
      if (!confirmed) return

      try {
        const res = await axios.delete(`${baseURL}/api/children/${nik}`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          }
        })
        this.showSuccess(res.message || "Data berhasil dihapus!")
        //alert("Data berhasil dihapus!")
      } catch (e) {
        //console.error(e)
        this.showError(e)
      }
    },
    //Mandatory
    /* goToPage(p) {
      if (p !== "...") {
        this.currentPage = p;
      }
    }, */
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    menu(type) {
      this.activeMenu = type
      this.loadData();
    },
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

        const data = res.data
        this.kelurahan = data.kelurahan
        // Setelah dapet id_wilayah, langsung fetch posyandu
        //await this.fetchPosyanduByWilayah(this.id_wilayah)
      } catch (error) {
        console.error('Gagal ambil data wilayah user:', error)
        //this.kelurahan = '-'
      }
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

        let lines = text.split(/\r?\n/).filter(Boolean).slice(0, 3)

        if (!lines.length) {
          this.filePreviewTable = []
          return
        }

        // DETECT delimiter (comma atau semicolon)
        const delimiter = lines[0].includes(';') ? ';' : ','

        const tableData = lines.map(line => line.split(delimiter))

        this.filePreviewTable = tableData
        console.log("Preview:", tableData) // debug
      }
      reader.readAsText(file.slice(0, 2000))
    },
    async uploadCSV() {
      //console.log(this.aktifitas_anak);

      if (!this.file) {
        this.fileError = 'Tidak ada file untuk di-upload.'
        return
      }

      // 🧭 Tentukan endpoint sesuai menu aktif
      let UPLOAD_URL = ''
      if (this.activeMenu === 'anak') {
        UPLOAD_URL = `${baseURL}/api/children/${
          this.aktifitas_anak === 'kunjungan'
            ? 'import_kunjungan'
            : 'import_pendampingan'
        }`
      } else if (this.activeMenu === 'bumil') {
        UPLOAD_URL = `${baseURL}/api/pregnancy/import`
      } else if (this.activeMenu === 'catin') {
        UPLOAD_URL = `${baseURL}/api/bride/import`
      } else {
        this.fileError = 'Menu tidak dikenal. Pastikan kamu memilih menu yang benar.'
        return
      }

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
              this.uploadProgress = Math.round(
                (progressEvent.loaded * 100) / progressEvent.total
              )
            }
          }
        })

        // ✅ Respons sukses
        console.log('Upload berhasil:', res.data)
        if (this.$bvToast) {
          this.$bvToast.toast('Upload CSV berhasil diproses.', {
            variant: 'success',
            solid: true
          })
        }

        // 🔄 Reset input file setelah upload
        this.removeFile()
      } catch (err) {
        console.error('Upload error:', err)
        this.fileError =
          (err.response && err.response.data && err.response.data.message) ||
          'Gagal upload file. Periksa format CSV atau koneksi server.'
      } finally {
        this.uploading = false
        this.uploadProgress = 0
      }
    },
    /* triggerFileDialog() {
      this.$refs.fileInput.click()
    },
    handleFileChange(e) {
      const file = e.target.files[0]
      this.loadFilePreview(file)
    },
    handleDrop(e) {
      const file = e.dataTransfer.files[0]
      this.loadFilePreview(file)
      this.isDataDrag = false
    },
    onDragOver() {
      this.isDataDrag = true
    },
    onDragLeave() {
      this.isDataDrag = false
    }, */
    removeFile() {
      this.file = null
      this.fileName = ''
      this.fileSize = 0
      this.filePreviewTable = ''
      this.$refs.fileInput.value = ''
    },
    humanFileSize(size) {
      const i = Math.floor(Math.log(size) / Math.log(1024))
      return (
        (size / Math.pow(1024, i)).toFixed(2) * 1 +
        ' ' +
        ['B', 'kB', 'MB', 'GB', 'TB'][i]
      )
    },
    async loadFilePreview(file) {
      if (!file) return
      if (!file.name.endsWith('.csv')) {
        this.fileError = 'Hanya file CSV yang diperbolehkan.'
        return
      }

      this.file = file
      this.fileName = file.name
      this.fileSize = file.size
      this.fileError = ''

      const text = await file.text()

      // Ambil 3 baris pertama
      const rawLines = text.split(/\r?\n/).filter(Boolean).slice(0, 3)

      if (rawLines.length === 0) {
        this.filePreviewTable = []
        return
      }

      // Deteksi delimiter otomatis
      const delimiter = rawLines[0].includes(';') ? ';' : ','

      // Parse CSV
      let table = rawLines.map(line => line.split(delimiter))

      // === CROP KOLOM ===
      table = table.map(row => {
        if (row.length > 6) {
          return [...row.slice(0, 10), '...'] // max 5 + "..."
        }
        return row
      })

      this.filePreviewTable = table
    },
    formatDate(dateString) {
      if (!dateString) return '-'
      const date = new Date(dateString)
      const day = String(date.getDate()).padStart(2, '0')
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const year = date.getFullYear()
      return `${day}-${month}-${year}`
    },
    async loadData() {
      try {
        let res = null;

        const headers = {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        };

        var payload;
        switch (this.activeMenu) {
          case 'anak':
            res = await axios.get(`${baseURL}/api/children`, { headers });
            payload = res.data.data_anak ?? {};
            this.dataLoad = Array.isArray(payload) ? payload : Object.values(payload);
            break;
          case 'bumil':
            res = await axios.get(`${baseURL}/api/pregnancy`, { headers });
            payload = res.data?.data || [];
            this.dataLoad = Array.isArray(payload) ? payload : Object.values(payload);
            break;
          case 'catin':
            res = await axios.get(`${baseURL}/api/bride`, { headers });
            payload = res.data ?? {};
            this.dataLoad = Array.isArray(payload) ? payload : Object.values(payload);
            break;
          default:
            return;
        }
        //console.log(this.dataLoad);

      } catch (e) {
        console.error('Gagal ambil data:', e);
        //this.paginatedData = [];
      }
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
  },
  async mounted() {
    this.isLoading = true
    try {
      await Promise.all([
        this.loadConfigWithCache(),
        this.loadData(),
        this.getWilayahUser(),
        this.handleResize(),

        window.addEventListener('resize', this.handleResize)
      ])
    } catch (err) {
      console.error('Error loading data:', err)
    } finally {
      this.isLoading = false
    }
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
  },
}
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.25s ease;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}
.action-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
}

.my-custom-table {
   --easy-table-header-background-color: #b4dbc6;
}

.my-custom-table .col-action {
  text-align: center !important;
}

.my-custom-table td.col-action {
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
}

</style>
