<template>
  <div class="wrapper">
    <!-- 🔄 Spinner Overlay -->
    <transition name="fade">
      <div v-if="isLoading" class="spinner-overlay d-flex justify-content-center align-items-center">
        <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </transition>

    <!-- Header -->
    <HeaderAdmin />

    <div class="content flex-grow-1 d-flex flex-column flex-md-row" :class="{
      'sidebar-collapsed': isCollapsed,
      'sidebar-expanded': !isCollapsed
    }">
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <!-- Content -->
        <div class="py-4 container-fluid">

          <!-- Welcome Card -->
          <Welcome />

          <!-- Nav Tab-->
          <div class="container-fluid my-2 d-flex justify-content-center">
            <ul class="nav nav-pills d-flex flex-wrap justify-content-center gap-2 w-100" id="myTab" role="tablist"
              style="max-width: 800px;">
              <li class="nav-item flex-fill text-center" role="presentation">
                <button class="nav-link active w-100 text-truncate" id="anak-tab" data-bs-toggle="tab"
                  data-bs-target="#anak-tab-pane" type="button" role="tab" aria-controls="anak-tab-pane"
                  aria-selected="true" @click="menu('anak')">
                  Gizi Anak
                </button>
              </li>

              <li class="nav-item flex-fill text-center" role="presentation">
                <button class="nav-link w-100 text-truncate" id="bumil-tab" data-bs-toggle="tab"
                  data-bs-target="#bumil-tab-pane" type="button" role="tab" aria-controls="bumil-tab-pane"
                  aria-selected="false" @click="menu('bumil')">
                  Ibu Hamil
                </button>
              </li>

              <li class="nav-item flex-fill text-center" role="presentation">
                <button class="nav-link w-100 text-truncate" id="catin-tab" data-bs-toggle="tab"
                  data-bs-target="#catin-tab-pane" type="button" role="tab" aria-controls="catin-tab-pane"
                  aria-selected="false" @click="menu('catin')">
                  Calon Pengantin
                </button>
              </li>
            </ul>
          </div>

          <div class="tab-content" id="myTabContent">
            <!-- Import Anak -->
            <div class="tab-pane fade show active" id="anak-tab-pane" role="tabpanel" tabindex="0">
              <div v-if="formOpen" class="card p-3 my-3">
                <div class="d-flex justify-content-between">
                  <h3>Form Gizi Anak</h3>
                  <button class="btn btn-outline-danger" @click="formOpen = !formOpen">
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
                      <i class="bi bi-pencil-square"></i> {{ form.mode == 'update' ? 'Ubah Data' : 'Rekam Baru' }}
                    </button>
                    <button class="btn btn-secondary mt-3 ms-2" @click="resetForm">
                      <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                  </div>
                </div>
              </div>

              <div v-if="isUploadOpen" class="card p-3 my-3">
                <div class="d-flex justify-content-between align-item-center">
                  <h5>Kelola Data</h5>
                  <button @click="isUploadOpen = !isUploadOpen" class="btn btn-sm btn-outline-danger mb-2">
                    X
                  </button>
                </div>

                <div class="row g-2">
                  <div class="alert alert-success">
                    <ul>
                      <li>Pastikan data yang diimport, berformat csv, xls, atau xlsx</li>
                      <li>Pastikan data sudah lengkap sebelum di import</li>
                      <li>
                        Silahkan unduh contoh dengan klik
                        <a :href="exampleFile">Example.csv</a>
                      </li>
                    </ul>
                  </div>

                  <input ref="fileInput" type="file" accept=".csv,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" class="form-control"
                    @change="handleFileChange($event)" />

                  <!-- Preview / status -->
                  <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div v-if="filePreviewTable.length" class="mt-3">
                      <p class="fw-bold mb-1 text-danger">
                        *Preview:
                      </p>
                      <div class="table-responsive">
                        <table class="table table-bordered table-sm small border-danger">
                          <thead>
                            <tr>
                              <th v-for="(col, index) in filePreviewTable[0]" :key="'h' + index" width="120"
                                class="text-danger">
                                {{ col }}
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(row, rIndex) in filePreviewTable.slice(1)" :key="'r' + rIndex">
                              <td v-for="(col, cIndex) in row" :key="'c' + cIndex" class="text-danger">
                                {{ col }}
                              </td>
                            </tr>
                            <tr v-for="(row, rIndex) in filePreviewTable.slice(2)" :key="'r' + rIndex">
                              <td v-for="(col, cIndex) in row" :key="'c' + cIndex" class="text-danger">
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
                    <button v-if="file && !uploading" class="btn btn-outline-danger btn-sm" @click="removeFile"
                      type="button">
                      <i class="bi bi-trash me-1"></i> Hapus
                    </button>

                    <button v-if="file && !uploading" class="btn btn-success btn-sm" @click="uploadCSV" type="button">
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

                    <input type="text" class="form-control form-control-sm" style="width: 220px;"
                      placeholder="Ketik NIK atau Nama" v-model="searchQuery_kunAn">

                    <button class="btn btn-primary btn-sm" type="button"
                      @click="isUploadOpen = !isUploadOpen; aktifitas = 'kunjungan'">
                      <i class="bi bi-filetype-csv me-1"></i> Import Kunjungan
                    </button>
                    <button class="btn btn-success btn-sm" type="button"
                      @click="isUploadOpen = !isUploadOpen; aktifitas = 'pendampingan_anak'">
                      <i class="bi bi-filetype-csv me-1"></i> Import Pendampingan
                    </button>
                    <button class="btn btn-outline-primary btn-sm" type="button"
                      @click="isUploadOpen = !isUploadOpen; aktifitas = 'intervensi_anak'">
                      <i class="bi bi-filetype-csv me-1"></i> Import Intervensi
                    </button>
                  </div>
                  <!-- Table -->
                  <div class="mt-3">

                    <!-- Search + Row Per Page -->
                    <easy-data-table :headers="headers_kunAn" :items="items_kunAn" :sortable="true"
                      :search-value="searchQuery_kunAn" :rows-per-page="perPage" :rows-items="perPageOptions"
                      :rows-per-page-text="'Rows per page'" header-text-direction="center"
                      table-class-name="my-custom-table" header-class-name="my-custom-header" show-index alternating
                      border-cell>

                      <!-- ACTION BUTTONS -->
                      <template #item-action="items">
                        <div class="action-wrapper d-flex gap-1 m-1 text-center">
                          <button @click="inputItem(items)" class="btn btn-primary" data-bs-toggle="tooltip"
                            title="Tambah">
                            <i class="bi bi-plus-square"></i>
                          </button>
                          <button @click="editItem(items)" class="btn btn-secondary" data-bs-toggle="tooltip"
                            title="Ubah">
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
                  <button class="btn btn-outline-danger" @click="formOpen_bumil = !formOpen_bumil">
                    X
                  </button>
                </div>
                <div class="row g-2">
                  <div class="col-md-4">
                    <label>NIK</label>
                    <input type="text" class="form-control" v-model="form_bumil.nik_ibu" readonly>
                  </div>

                  <div class="col-md-4">
                    <label>Nama Ibu</label>
                    <input type="text" class="form-control" v-model="form_bumil.nama_ibu"
                      :readonly="form.mode === 'input'">
                  </div>

                  <!-- <div v-if="form.mode === 'update'" class="col-md-4">
                    <label>Nama Suami</label>
                    <input type="text" class="form-control" v-model="form_bumil.nama_suami">
                  </div> -->

                  <div v-if="form.mode === 'input'" class="col-md-4">
                    <label>Tanggal Pendampingan</label>
                    <input type="date" class="form-control" v-model="form_bumil.tanggal_pendampingan">
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
                    <label>Lingkar Lengan</label>
                    <input type="text" class="form-control" v-model="form_bumil.lila">
                  </div>

                  <div class="col-md-4">
                    <label>Hb</label>
                    <input type="text" class="form-control" v-model="form_bumil.hb">
                  </div>

                  <div class="col-12">
                    <button class="btn btn-primary mt-3" @click="submitUpdate">
                      <i class="bi bi-pencil-square"></i> {{ form_bumil.mode == 'update' ? 'Ubah Data' : 'Rekam Baru' }}
                    </button>
                    <button class="btn btn-secondary mt-3 ms-2" @click="resetForm">
                      <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                  </div>
                </div>
              </div>

              <div v-if="isUploadOpen_bumil" class="card p-3 my-3">
                <div class="d-flex justify-content-between align-item-center">
                  <h5>Kelola Data</h5>
                  <button @click="isUploadOpen_bumil = !isUploadOpen_bumil" class="btn btn-sm btn-outline-danger mb-2">
                    X
                  </button>
                </div>

                <div class="row g-2">
                  <div class="alert alert-success">
                    <ul>
                      <li>Pastikan data yang diimport, berformat csv, xls, atau xlsx</li>
                      <li>Pastikan data sudah lengkap sebelum di import</li>
                      <li>
                        Silahkan unduh contoh dengan klik
                        <a :href="exampleFile">Example.csv</a>
                      </li>
                    </ul>
                  </div>

                  <input ref="fileInput" type="file"  accept=".csv,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" class="form-control"
                    @change="handleFileChange($event)" />

                  <!-- Preview / status -->
                  <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div v-if="filePreviewTable_bumil.length" class="mt-3">
                      <p class="fw-bold mb-1 text-danger">
                        *Preview:
                      </p>
                      <div class="table-responsive">
                        <table class="table table-bordered table-sm small border-danger">
                          <thead>
                            <tr>
                              <th v-for="(col, index) in filePreviewTable_bumil[0]" :key="'h' + index" width="120"
                                class="text-danger">
                                {{ col }}
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(row, rIndex) in filePreviewTable_bumil.slice(1)" :key="'r' + rIndex">
                              <td v-for="(col, cIndex) in row" :key="'c' + cIndex" class="text-danger">
                                {{ col }}
                              </td>
                            </tr>
                            <tr v-for="(row, rIndex) in filePreviewTable_bumil.slice(2)" :key="'r' + rIndex">
                              <td v-for="(col, cIndex) in row" :key="'c' + cIndex" class="text-danger">
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
                    <button v-if="file && !uploading" class="btn btn-outline-danger btn-sm" @click="removeFile"
                      type="button">
                      <i class="bi bi-trash me-1"></i> Hapus
                    </button>

                    <button v-if="file && !uploading" class="btn btn-success btn-sm" @click="uploadCSV" type="button">
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

                    <input type="text" class="form-control form-control-sm" style="width: 220px;"
                      placeholder="Ketik NIK atau Nama" v-model="searchQuery_bumil">

                    <button class="btn btn-primary btn-sm" type="button"
                      @click="isUploadOpen_bumil = !isUploadOpen_bumil; aktifitas = 'pendampingan_bumil'">
                      <i class="bi bi-filetype-csv me-1"></i> Import Pendampingan
                    </button>
                    <button class="btn btn-outline-primary btn-sm" type="button"
                      @click="isUploadOpen_bumil = !isUploadOpen_bumil; aktifitas = 'intervensi_bumil'">
                      <i class="bi bi-filetype-csv me-1"></i> Import Intervensi
                    </button>
                  </div>
                  <!-- Table -->
                  <div class="mt-3">

                    <!-- Search + Row Per Page -->
                    <easy-data-table :headers="headers_bumil" :items="items_bumil" :sortable="true"
                      :search-value="searchQuery_bumil" :rows-per-page="perPage" :rows-items="perPageOptions"
                      :rows-per-page-text="'Rows per page'" header-text-direction="center"
                      table-class-name="my-custom-table" header-class-name="my-custom-header" show-index alternating
                      border-cell>

                      <template #item-action="items">
                        <div class="action-wrapper d-flex gap-1 m-1 text-center">
                          <button @click="inputItem(items)" class="btn btn-primary" data-bs-toggle="tooltip"
                            title="Tambah">
                            <i class="bi bi-plus-square"></i>
                          </button>
                          <button @click="editItem(items)" class="btn btn-secondary" data-bs-toggle="tooltip"
                            title="Ubah">
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

            <!-- Import Catin -->
            <div class="tab-pane fade" id="catin-tab-pane" role="tabpanel" tabindex="0">
              <div v-if="formOpen_catin" class="card p-3 my-3">
                <div class="d-flex justify-content-between">
                  <h3>Form Calon Pengantin</h3>
                  <button class="btn btn-outline-danger" @click="formOpen_catin = !formOpen_catin">
                    X
                  </button>
                </div>
                <div class="row g-2">
                  <div class="col-md-4">
                    <label>NIK</label>
                    <input type="text" class="form-control" v-model="form_catin.nik" readonly>
                  </div>

                  <div class="col-md-4">
                    <label>Nama Calon Perempuan</label>
                    <input type="text" class="form-control" v-model="form_catin.nama_perempuan"
                      :readonly="form.mode === 'input'">
                  </div>

                  <div v-if="form.mode === 'update'" class="col-md-4">
                    <label>Nama Pasangan</label>
                    <input type="text" class="form-control" v-model="form_catin.nama_laki">
                  </div>

                  <div v-if="form.mode === 'input'" class="col-md-4">
                    <label>Tanggal Pendampingan</label>
                    <input type="date" class="form-control" v-model="form_catin.tanggal_pendampingan">
                  </div>

                  <div class="col-md-3">
                    <label>Berat Badan (kg)</label>
                    <input type="text" class="form-control" v-model="form_catin.berat_perempuan">
                  </div>

                  <div class="col-md-3">
                    <label>Tinggi Badan (cm)</label>
                    <input type="text" class="form-control" v-model="form_catin.tinggi_perempuan">
                  </div>

                  <div class="col-md-3">
                    <label>Hb</label>
                    <input type="text" class="form-control" v-model="form_catin.kadar_hb">
                  </div>

                  <div class="col-md-3">
                    <label>Lingkar Lengan</label>
                    <input type="text" class="form-control" v-model="form_catin.lila_perempuan">
                  </div>

                  <div class="col-12">
                    <button class="btn btn-primary mt-3" @click="submitUpdate">
                      <i class="bi bi-pencil-square"></i> {{ form_catin.mode == 'update' ? 'Ubah Data' : 'Rekam Baru' }}
                    </button>
                    <button class="btn btn-secondary mt-3 ms-2" @click="resetForm">
                      <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                  </div>
                </div>
              </div>

              <div v-if="isUploadOpen_catin" class="card p-3 my-3">
                <div class="d-flex justify-content-between align-item-center">
                  <h5>Kelola Data</h5>
                  <button @click="isUploadOpen_catin = !isUploadOpen_catin" class="btn btn-sm btn-outline-danger mb-2">
                    X
                  </button>
                </div>

                <div class="row g-2">
                  <div class="alert alert-success">
                    <ul>
                      <li>Pastikan data yang diimport, berformat csv, xls, atau xlsx</li>
                      <li>Pastikan data sudah lengkap sebelum di import</li>
                      <li>
                        Silahkan unduh contoh dengan klik
                        <a :href="exampleFile">Example.csv</a>
                      </li>
                    </ul>
                  </div>

                  <input ref="fileInput" type="file" accept=".csv,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" class="form-control"
                    @change="handleFileChange($event)" />

                  <!-- Preview / status -->
                  <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div v-if="filePreviewTable_catin.length" class="mt-3">
                      <p class="fw-bold mb-1 text-danger">
                        *Preview:
                      </p>
                      <div class="table-responsive">
                        <table class="table table-bordered table-sm border-danger" style="font-size: 80%;">
                          <thead>
                            <tr>
                              <th v-for="(col, index) in PreviewTable_catin" :key="'h' + index" width="120"
                                class="text-danger">
                                {{ col }}
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(row, rIndex) in filePreviewTable_catin.slice(0)" :key="'r' + rIndex">
                              <td v-for="(col, cIndex) in row" :key="'c' + cIndex" class="text-danger">
                                {{ col }}
                              </td>
                            </tr>
                            <tr v-for="(row, rIndex) in filePreviewTable_catin.slice(1)" :key="'r' + rIndex">
                              <td v-for="(col, cIndex) in row" :key="'c' + cIndex" class="text-danger">
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
                    <button v-if="file && !uploading" class="btn btn-outline-danger btn-sm" @click="removeFile"
                      type="button">
                      <i class="bi bi-trash me-1"></i> Hapus
                    </button>

                    <button v-if="file && !uploading" class="btn btn-success btn-sm" @click="uploadCSV" type="button">
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
                    <input type="text" class="form-control form-control-sm" style="width: 220px;"
                      placeholder="Ketik NIK atau Nama" v-model="searchQuery_catin">

                    <button class="btn btn-primary btn-sm" type="button"
                      @click="isUploadOpen_catin = !isUploadOpen_catin; aktifitas = 'pendampingan_catin'">
                      <i class="bi bi-filetype-csv me-1"></i> Import Pendampingan
                    </button>
                  </div>
                  <!-- Table -->
                  <div class="mt-3">

                    <!-- Search + Row Per Page -->
                    <easy-data-table :headers="headers_catin" :items="items_catin" :sortable="true"
                      :search-value="searchQuery_catin" :rows-per-page="perPage" :rows-items="perPageOptions"
                      :rows-per-page-text="'Rows per page'" header-text-direction="center"
                      table-class-name="my-custom-table" header-class-name="my-custom-header" show-index alternating
                      border-cell>

                      <template #item-action="items">
                        <div class="action-wrapper d-flex gap-1 m-1 text-center">
                          <button @click="inputItem(items)" class="btn btn-primary" data-bs-toggle="tooltip"
                            title="Tambah">
                            <i class="bi bi-plus-square"></i>
                          </button>
                          <button @click="editItem(items)" class="btn btn-secondary" data-bs-toggle="tooltip"
                            title="Ubah">
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
          </div>

        </div>
        <CopyRight class="mt-auto" />
      </div>
    </div>
  </div>

  <!-- Loader Overlay with Animated Progress -->
  <div v-if="isLoadingImport"
    class="position-fixed top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark bg-opacity-50"
    style="z-index: 2000">
    <div class="w-50">
      <div class="progress" style="height: 1.8rem; border-radius: 1rem; overflow: hidden">
        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
          :style="{ width: importProgress + '%' }" :data-progress="progressLevel">
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
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
    <p class="text-white mt-3">Menyimpan data... {{ currentRow }}/{{ totalRows }} baris</p>
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
import * as XLSX from 'xlsx'

// PORT backend kamu
const API_PORT = 8001;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8001"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;
// inisialisasi DataTables agar pakai styling Bootstrap 5

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Import',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, Welcome, EasyDataTable, },
  data() {
    return {
      formOpen_bumil: '',
      formOpen_catin: '',
      isLoadingImport: false,
      importProgress: 0,
      animatedProgress: 0,
      currentRow: 0,
      totalRows: 1,
      form: {
        mode: "",
        tgl_pengukuran: "",
        nik: "",
        nama_anak: "",
        nama_ortu: "",
        bb: "",
        tb: "",
        lika: "",
        unit_posyandu: "",
        gender: "",
        tgl_lahir: "",
      },
      form_bumil: {
        mode: "",
        tanggal_pendampingan: "",
        nik_ibu: "",
        nama_ibu: "",
        nama_suami: "",
        hb: "",
        bb: "",
        tb: "",
        lila: "",
      },
      form_catin: {
        mode: "",
        tanggal_pendampingan: "",
        nik: "",
        kadar_hb: "",
        nama_perempuan: "",
        nama_laki: "",
        berat_perempuan: "",
        tinggi_perempuan: "",
        lika_perempuan: "",
      },
      sortKey: '',
      sortOrder: 'asc',
      headers_catin: [
        {
          text: 'NIK Perempuan',
          value: 'nik',
          sortable: true,
          align: 'center',
        },
        {
          text: 'Nama Perempuan',
          value: 'nama_perempuan',
          sortable: true,
          align: 'center',
        },
        {
          text: 'Nama Laki-laki',
          value: 'nama_laki',
          sortable: true,
          align: 'center',
        },
        {
          text: 'Usia Perempuan',
          value: 'usia_perempuan',
          sortable: true,
          align: 'center',
        },
        {
          text: 'BB Perempuan',
          value: 'bb',
          sortable: true,
          align: 'center',
        },
        {
          text: 'TB Perempuan',
          value: 'tb',
          sortable: true,
          align: 'center',
        },
        {
          text: 'Hb Perempuan',
          value: 'hb_perempuan',
          sortable: true,
          align: 'center',
        },
        {
          text: 'Lila Perempuan',
          value: 'lila_perempuan',
          sortable: true,
          align: 'center',
        },
        {
          text: 'Tanggal Menikah',
          value: 'tanggal_menikah',
          sortable: true,
          align: 'center',
        },
        { text: 'Action', value: 'action', width: 120, align: "center", class: "col-action" },
      ],
      headers_bumil: [
        { text: "NIK", value: "nik_ibu", sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: "Nama", value: "nama_ibu", sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: "Kehamilan ke", value: "kehamilan_ke", sortable: true, width: 130, class: "align-middle text-center cursor-pointer" },
        { text: "Hb", value: "kadar_hb", sortable: true, align: "center", class: "align-middle text-center cursor-pointer" },
        { text: "Lila", value: "lila", sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: "Usia (Thn)", value: "usia_ibu", sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: "Tgl Pendampingan", value: "tgl_pendampingan", sortable: true, width: 130, class: "align-middle text-center cursor-pointer" },
        { text: "Action", value: "action", width: 120, align: "center", class: "col-action" },
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
      formOpen: false,
      searchQuery_kunAn: "",
      searchQuery_bumil: "",
      searchQuery_catin: "",
      currentPage: 1,
      perPage: 10,
      perPageOptions: [5, 10, 25, 50],
      isUploadOpen: false,
      isUploadOpen_bumil: false,
      isUploadOpen_catin: false,
      tableOptions: {
        responsive: true,
        pageLength: 10,
        destroy: true, // penting agar bisa di-refresh
      },
      aktifitas: 'kunjungan',
      isLoading: true,
      isCollapsed: false,
      username: '',
      today: '',
      thisMonth: '',
      kelurahan: '',
      windowWidth: window.innerWidth,
      configCacheKey: 'site_config_cache',
      activeMenu: 'anak', // default tampilan awal
      dataLoad: [],
      file: null,
      fileName: '',
      fileSize: 0,
      filePreviewLines: '',
      fileError: '',
      uploading: false,
      uploadProgress: 0,
      successMessage:'',
      errorMessage:'',
      confirmMessage:'',
      // config
      ACCEPTED_EXT: ['csv', 'xlsx', 'xls'],
      ACCEPTED_MIME: [
        'text/csv',
        'text/plain',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      ],
      MAX_FILE_SIZE: 50 * 1024 * 1024, // 5 MB
      filePreviewTable: [],
      filePreviewTable_catin: [],
      filePreviewTable_bumil: []
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
    exampleFile() {
      switch (this.aktifitas) {
        case "kunjungan":
          return "/example_kunjungan_posyandu.csv";
        case "pendampingan_anak":
          return "/example_pendampingan_anak.csv";
        case "intervensi_anak":
          return "/example_intervensi_anak.csv";
        case "pendampingan_bumil":
          return "/example_bumil.csv";
        case "intervensi_bumil":
          return "/example_intervensi_bumil.csv";
        case "pendampingan_catin":
          return "/example_catin.csv";
        default:
          return "#";
      }
    },

    // ==================== ANAK ====================
    filteredAnak() {
      if (!this.dataLoad) return [];

      const arr = Array.isArray(this.dataLoad)
        ? this.dataLoad
        : Object.values(this.dataLoad);

      const q = this.searchQuery_kunAn?.toLowerCase()?.trim() ?? "";

      return arr.filter(item => {
        const nama = item.nama?.toLowerCase() ?? "";
        return nama.includes(q) || item.nik?.toString().includes(q);
      });
    },

    items_kunAn() {
      const latestByNik = {}

      this.filteredAnak.forEach(item => {
        const nik = item.nik ?? "-"
        const currentTgl = item.posyandu?.[0]?.tgl_ukur
          ? new Date(item.posyandu[0].tgl_ukur)
          : null

        // kalau NIK belum ada → simpan
        if (!latestByNik[nik]) {
          latestByNik[nik] = item
          return
        }

        // bandingkan dengan data yg sudah ada
        const savedTgl = latestByNik[nik].posyandu?.[0]?.tgl_ukur
          ? new Date(latestByNik[nik].posyandu[0].tgl_ukur)
          : null

        // ambil yang TERBARU
        if (currentTgl && (!savedTgl || currentTgl > savedTgl)) {
          latestByNik[nik] = item
        }
      })

      return Object.values(latestByNik)
        .sort((a, b) => a.id - b.id) // optional: urut id
        .map(item => ({
          id: item.id,
          nik: item.nik ?? "-",
          nama_anak: item.nama ?? "-",
          nama_ortu: item.keluarga?.[0]?.nama_ortu ?? "-",
          gender: item.jk === "L" ? "Laki-laki" : "Perempuan",
          tgl_lahir: this.formatDate(item.kelahiran?.[0]?.tgl_lahir),
          bb: item.posyandu?.[0]?.bb ?? "-",
          tb: item.posyandu?.[0]?.tb ?? "-",
          unit_posyandu: item.posyandu?.[0]?.posyandu ?? "-",
          action: { ...item }
        }))
    },

    // ==================== BUMIL ====================
    filteredBumil() {
      if (!this.dataLoad) return [];
      //console.log('bumil:', this.dataLoad);

      const arr = Array.isArray(this.dataLoad)
        ? this.dataLoad
        : Object.values(this.dataLoad);

      const q = this.searchQuery_bumil?.toLowerCase()?.trim() ?? "";
      return arr.filter(item => {
        const nama = item.nama_ibu?.toLowerCase() ?? "";
        return nama.includes(q) || item.nik_ibu?.toString().includes(q);
      });
    },

    items_bumil() {
      const latestByNik = {}

      this.filteredBumil.forEach(item => {
        const nik = item.nik_ibu ?? "-"
        const currentTgl = item.riwayat_pemeriksaan?.[0]?.tanggal_pemeriksaan
          ? new Date(item.riwayat_pemeriksaan[0].tanggal_pemeriksaan)
          : null

        // kalau belum ada → simpan
        if (!latestByNik[nik]) {
          latestByNik[nik] = item
          return
        }

        const savedTgl = latestByNik[nik].riwayat_pemeriksaan?.[0]?.tanggal_pemeriksaan
          ? new Date(latestByNik[nik].riwayat_pemeriksaan[0].tanggal_pemeriksaan)
          : null

        // ambil pemeriksaan TERBARU
        if (currentTgl && (!savedTgl || currentTgl > savedTgl)) {
          latestByNik[nik] = item
        }
      })

      return Object.values(latestByNik).map(item => ({
        nik_ibu: item.nik_ibu ?? "-",
        nama_ibu: item.nama_ibu ?? "-",
        nama_ortu: item.keluarga?.[0]?.nama_ortu ?? "-",
        kehamilan_ke: item.kehamilan_ke ?? "-",
        kadar_hb: item.riwayat_pemeriksaan?.[0]?.kadar_hb ?? "-",
        lila: item.riwayat_pemeriksaan?.[0]?.lila ?? "-",
        usia_ibu: item.usia_ibu ?? "-",
        bb: item.riwayat_pemeriksaan?.[0]?.berat_badan ?? "-",
        tb: item.riwayat_pemeriksaan?.[0]?.tinggi_badan ?? "-",
        tgl_pendampingan: this.formatDate(item.tanggal_pendampingan) ?? "-",
        jml_anak: item.jumlah_anak ?? "-",
        action: { ...item }
      }))
    },

    // ==================== CATIN ====================
    filteredCatin() {
      if (!this.dataLoad[1]) return [];

      let arr = Array.isArray(this.dataLoad[1])
        ? this.dataLoad[1]
        : Object.values(this.dataLoad[1]);

      // 🔒 FILTER WAJIB: buang null / undefined / bukan object
      arr = arr.filter(item => item && typeof item === "object");

      if (this.searchQuery_catin) {
        const q = this.searchQuery_catin.toLowerCase().trim();

        return arr.filter(item => {
          const namaP = item.nama_perempuan?.toLowerCase() ?? "";
          const namaL = item.nama_laki?.toLowerCase() ?? "";
          const nikP = item.nik_perempuan ?? "";
          const nikL = item.nik_laki ?? "";

          return (
            namaP.includes(q) ||
            namaL.includes(q) ||
            nikP.includes(q) ||
            nikL.includes(q)
          );
        });
      }

      return arr;
    },

    items_catin() {
      const unique = Object.values(
        this.filteredCatin.reduce((acc, item) => {
          const nik = item.nik_perempuan ?? "-"
          const curr = item.riwayat_pemeriksaan?.[0]?.tanggal_pemeriksaan
          const prev = acc[nik]?.riwayat_pemeriksaan?.[0]?.tanggal_pemeriksaan

          if (
            !acc[nik] ||
            (curr && (!prev || new Date(curr) > new Date(prev)))
          ) {
            acc[nik] = item
          }

          return acc
        }, {})
      )

      return unique.map(item => {
        const pemeriksaan = item.riwayat_pemeriksaan?.[0] ?? null

        return {
          nik: item.nik_perempuan ?? "-",
          nama_perempuan: item.nama_perempuan ?? "-",
          nama_laki: item.nama_laki ?? "-",
          usia_perempuan: item.usia_perempuan ?? "-",
          bb: pemeriksaan?.berat_perempuan ?? "-",
          tb: pemeriksaan?.tinggi_perempuan ?? "-",
          hb_perempuan: pemeriksaan?.hb_perempuan ?? "-",
          lila_perempuan: pemeriksaan?.lila_perempuan ?? "-",
          tanggal_menikah: this.formatDate(item.tgl_pernikahan) ?? "-",
          action: { ...item }
        }
      })
    }


  },
  methods: {
    handleFileChange(e) {
      const file = e.target.files[0]
      this.loadFilePreview(file)
    },
    resetForm() {
      this.formOpen = false;
      this.form = {
        ...this.form,
        mode: "",
        nik: "",
        nama_anak: "",
        nama_ortu: "",
        bb: "",
        tb: "",
        unit_posyandu: "",
        gender: "",
        tgl_lahir: ""
      };
      this.formOpen_bumil = false;
      this.form_bumil = {
        ...this.form_bumil
      };
      this.formOpen_catin = false;
      this.form_catin = {
        ...this.form_catin
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
    showConfirm(message) {
      this.confirmMessage = message || 'Apakah anda yakin?'
      // eslint-disable-next-line no-undef
      const modal = new bootstrap.Modal(document.getElementById('warningModal'))
      modal.show()
    },
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
      switch (this.activeMenu) {
        case 'anak':
          this.formOpen = true;
          this.form = {
            ...this.form,
            mode: 'update',
            nik: item.nik ?? "",
            nama_anak: item.nama_anak ?? "",
            nama_ortu: item.nama_ortu ?? "",
            bb: item.bb ?? "",
            tb: item.tb ?? "",
            unit_posyandu: item.unit_posyandu ?? "",
            gender: item.gender ?? "",
            tgl_lahir: item.tgl_lahir ?? ""
          };
          break;
        case 'bumil':
          this.formOpen_bumil = true;
          this.form_bumil = {
            ...this.form_bumil,
            mode: 'input',
            nik_ibu: item.nik_ibu ?? "",
            nama_ibu: item.nama_ibu ?? "",
            nama_suami: item.nama_suami ?? "",
            bb: item.bb ?? "",
            tb: item.tb ?? "",
            lila: item.lila ?? "",
            hb: item.kadar_hb ?? "",
          };
          break;

        case 'catin':

          this.formOpen_catin = true;
          this.form_catin = {
            ...this.form_catin,
            mode: 'input',
            nik: item.nik ?? "",
            tanggal_pendampingan: item.tanggal_pendampingan ?? "",
            nama_perempuan: item.nama_perempuan ?? "",
            nama_laki: item.nama_laki ?? "",
            kadar_hb: item.hb_perempuan ?? "",
            berat_perempuan: item.bb ?? "",
            tinggi_perempuan: item.tb ?? "",
            lila_perempuan: item.lila_perempuan ?? "",
          };
          break;
        default:
          return;
      }
    },
    inputItem(item) {
      switch (this.activeMenu) {
        case 'anak':
          this.formOpen = true;
          this.form = {
            ...this.form,
            mode: 'input',
            nik: item.nik ?? "",
            nama_anak: item.nama_anak ?? "",
            nama_ortu: item.nama_ortu ?? "",
            //bb: item.bb ?? "",
            //tb: item.tb ?? "",
            unit_posyandu: item.unit_posyandu ?? "",
            gender: item.gender ?? "",
            tgl_lahir: item.tgl_lahir ?? ""
          };
          break;
        case 'bumil':
          this.formOpen_bumil = true;
          this.form_bumil = {
            ...this.form_bumil,
            mode: 'input',
            nik_ibu: item.nik_ibu ?? "",
            nama_ibu: item.nama_ibu ?? "",
            nama_suami: item.nama_suami ?? ""
          };
          break;

        case 'catin':
          this.formOpen_catin = true;
          this.form_catin = {
            ...this.form_catin,
            mode: 'input',
            nik: item.nik ?? "",
            tanggal_pendampingan: item.tanggal_pendampingan ?? "",
            nama_perempuan: item.nama_perempuan ?? "",
            nama_laki: item.nama_laki ?? "",
            kadar_hb: item.kadar_hb ?? "",
          };
          break;
        default:
          return;
      }
    },
    async submitUpdate() {
      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0

      try {
        switch (this.activeMenu) {
          case 'anak':
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
            break;
          case 'bumil':
            // MODE UPDATE → PUT
            if (this.form_bumil.mode === 'update') {
              const res = await axios.put(
                `${baseURL}/api/pregnancy/${this.form_bumil.nik}`,
                this.form_bumil,
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
                `${baseURL}/api/pregnancy`,
                this.form_bumil,
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
            break;
          case 'catin':
            // MODE UPDATE → PUT
            if (this.form_catin.mode === 'update') {
              const res = await axios.put(
                `${baseURL}/api/bride/${this.form_catin.nik_perempuan}`,
                this.form_catin,
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
                `${baseURL}/api/pregnancy`,
                this.form_catin,
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
            break;
          default:
            break;
        }

      } catch (e) {
        //console.error(e)
        this.showError(e)
        //alert("Gagal menyimpan data")
      } finally {
        this.isLoadingImport = false
      }
    },
    // Delete via backend
    async delItem(item) {
      const nik = item.nik
      const confirmed = await this.confirmModal("Yakin ingin menghapus data ini?")
      if (!confirmed) return

      let res = null // <<< ini penting

      try {
        switch (this.activeMenu) {
          case 'anak':
            res = await axios.delete(`${baseURL}/api/children/${nik}`, {
              headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${localStorage.getItem('token')}`,
              }
            })
            break;

          case 'bumil':
            res = await axios.delete(`${baseURL}/api/pregnancy/${nik}`, {
              headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${localStorage.getItem('token')}`,
              }
            })
            break;

          case 'catin':
            res = await axios.delete(`${baseURL}/api/bride/${nik}`, {
              headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${localStorage.getItem('token')}`,
              }
            })
            break;
        }

        this.showSuccess(res?.data?.message || "Data berhasil dihapus!")

      } catch (e) {
        this.showError(e)
      }
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    async menu(type) {
      this.isLoading = true
      try {
        await Promise.all([
          this.activeMenu = type,
          this.loadData()
        ])
      } catch (err) {
        console.error('Error loading data:', err)
      } finally {
        this.isLoading = false
      }
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
      } catch (error) {
        console.warn('Gagal load config:', error)
        this.logoLoaded = false
      }
    },
    /* async getWilayahUser() {
      const res = await axios.get(`${baseURL}/api/user/region`, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      })

      const wilayah = res.data

      this.filters.kelurahan_id    = wilayah.id_wilayah
      this.filters.provinsi = wilayah.provinsi
      this.filters.kota = wilayah.kota
      this.filters.kecamatan = wilayah.kecamatan
      this.filters.kelurahan = wilayah.kelurahan

      this.listKelurahan = [
        {
          label: 'Kelurahan',
          options: [
            {
              id: wilayah.id_wilayah,
              label: wilayah.kelurahan,
            },
          ],
        },
      ]
    }, */
    /* async getWilayahUser() {
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
    }, */
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
      console.log('ini filenya:', file);

      const nameParts = (file.name || '').split('.')
      const ext = nameParts.length > 1 ? nameParts.pop().toLowerCase() : ''
      if (!this.ACCEPTED_EXT.includes(ext)) {
        return { valid: false, message: 'Format file tidak didukung. Hanya .csv, .xlxs atau .xls yang diperbolehkan.' }
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
      // 1️⃣ Cek file ada atau tidak
      if (!this.file) {
        this.showError('Tidak ada file untuk di-upload.')
        return
      }

      // 2️⃣ Validasi file (OPSI 1)
      const validation = this.validateFile(this.file)
      if (!validation.valid) {
        this.showError(validation.message)
        return
      }

      // 3️⃣ Tentukan endpoint sesuai menu aktif
      let UPLOAD_URL
      switch (this.activeMenu) {
        case 'anak':
          switch (this.aktifitas) {
            case 'kunjungan':
              UPLOAD_URL = `${baseURL}/api/children/import_kunjungan`
              break
            case 'pendampingan':
              UPLOAD_URL = `${baseURL}/api/children/import_pendampingan`
              break
            case 'intervensi_anak':
              UPLOAD_URL = `${baseURL}/api/children/import_intervensi`
              break
          }
          break

        case 'bumil':
          switch (this.aktifitas) {
            case 'pendampingan_bumil':
              UPLOAD_URL = `${baseURL}/api/pregnancy/import`
              break
            case 'intervensi_bumil':
              UPLOAD_URL = `${baseURL}/api/pregnancy/import_intervensi`
              break
          }
          break

        case 'catin':
          UPLOAD_URL = `${baseURL}/api/bride/import`
          break
      }

      if (!UPLOAD_URL) {
        this.showError('Endpoint upload tidak ditemukan.')
        return
      }

      const formData = new FormData()
      formData.append('file', this.file)

      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0

      try {

        //this.uploading = true
        //this.uploadProgress = 0

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

        // ✅ sukses
        this.showSuccess(res.data.message || 'Data berhasil diimport!')
        this.formOpen = false
        this.formOpen_bumil = false
        this.formOpen_catin = false

        this.removeFile()
        setTimeout(() => (this.showAlert = false), 3000)
      } catch (err) {
        console.error('Upload error:', err)

        const message =
          err.response?.data?.message ||
          'Gagal upload file. Periksa format CSV atau koneksi server.'

        this.showError(message)
      } finally {
        this.isLoadingImport = false
        /* this.uploading = false
        this.uploadProgress = 0 */
      }
    },
    removeFile() {
      this.file = null
      this.fileName = ''
      this.fileSize = 0
      this.filePreviewTable = ''
      this.filePreviewTable_catin = ''
      this.filePreviewTable_bumil = ''
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
    /* async loadFilePreview(file) {
      if (!file) return

      const allowed = ['.csv', '.xlsx', '.xls']

      if (!allowed.some(ext => file.name.toLowerCase().endsWith(ext))) {
        this.fileError = 'Hanya file CSV atau Excel (XLS/XLSX) yang diperbolehkan.'
        return
      }

      this.file = file
      this.fileName = file.name
      this.fileSize = file.size
      this.fileError = ''

      const text = await file.text()

      // Ambil 3 baris pertama
      const rawLines = text.split(/\r?\n/).filter(Boolean).slice(0, 3)

      switch (this.activeMenu) {
        case 'anak':
          if (rawLines.length === 0) {
            this.filePreviewTable = []
            return
          }

          // Deteksi delimiter otomatis
          // eslint-disable-next-line no-case-declarations
          const delimiter = rawLines[0].includes(';') ? ';' : ','

          // Parse CSV
          // eslint-disable-next-line no-case-declarations
          let table = rawLines.map(line => line.split(delimiter))

          // === CROP KOLOM ===
          table = table.map(row => {
            if (row.length > 6) {
              return [...row.slice(0, 10), '...'] // max 5 + "..."
            }
            return row
          })

          this.filePreviewTable = table
          break;
        case 'bumil':
          if (rawLines.length === 0) {
            this.filePreviewTable_bumil = []
            return
          }

          // Deteksi delimiter otomatis
          // eslint-disable-next-line no-case-declarations
          const delimiter_bumil = rawLines[0].includes(';') ? ';' : ','

          // Parse CSV
          // eslint-disable-next-line no-case-declarations
          let table_bumil = rawLines.map(line => line.split(delimiter_bumil))

          // === CROP KOLOM ===
          table_bumil = table_bumil.map(row => {
            if (row.length > 6) {
              return [...row.slice(0, 10), '...'] // max 5 + "..."
            }
            return row
          })

          this.filePreviewTable_bumil = table_bumil
          break;
        case 'catin':
          if (rawLines.length === 0) {
            this.filePreviewTable_catin = []
            return
          }

          // Deteksi delimiter otomatis
          // eslint-disable-next-line no-case-declarations
          const delimiter_catin = rawLines[0].includes(';') ? ';' : ','

          // Parse CSV
          // eslint-disable-next-line no-case-declarations
          let table_catin = rawLines.map(line => line.split(delimiter_catin))

          // === CROP KOLOM ===
          table_catin = table_catin.map(row => {
            if (row.length > 6) {
              return [...row.slice(0, 10), '...'] // max 5 + "..."
            }
            return row
          })

          this.filePreviewTable_catin = table_catin
          break;
        default:
          return;
      }

    } */
    async loadFilePreview(file) {
      if (!file) return

      const ext = file.name.split('.').pop().toLowerCase()

      if (!['csv', 'xlsx', 'xls'].includes(ext)) {
        this.fileError = 'Hanya file CSV atau Excel (XLS/XLSX) yang diperbolehkan.'
        return
      }

      this.file = file
      this.fileName = file.name
      this.fileSize = file.size
      this.fileError = ''

      // === CSV ===
      if (ext === 'csv') {
        const text = await file.text()

        const rawLines = text
          .split(/\r?\n/)
          .filter(Boolean)
          .slice(0, 4)

        if (!rawLines.length) {
          this.filePreviewTable = []
          return
        }

        const delimiter = rawLines[0].includes(';') ? ';' : ','

        let table = rawLines.map(line => line.split(delimiter))

        table = table.map(row =>
          row.length > 10 ? [...row.slice(0, 10), '...'] : row
        )

        this.filePreviewTable = table
        return
      }

      // === EXCEL ===
      if (ext === 'xlsx' || ext === 'xls') {
        this.previewExcel(file)
      }
    },
    previewExcel(file) {
      const reader = new FileReader()

      reader.onload = (e) => {
        const data = new Uint8Array(e.target.result)
        const workbook = XLSX.read(data, { type: 'array' })

        const sheetName = workbook.SheetNames[0]
        const worksheet = workbook.Sheets[sheetName]

        const rows = XLSX.utils.sheet_to_json(worksheet, {
          header: 1,
          blankrows: false
        })

        this.filePreviewTable = rows.slice(0, 4)
      }

      reader.readAsArrayBuffer(file)
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
        console.log(this.dataLoad);

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
        //this.getWilayahUser(),
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
.table-scroll-x {
  overflow-x: auto !important;
  overflow-y: hidden !important;
  width: 100% !important;
  display: block !important;
}

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
