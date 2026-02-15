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
              <div v-if="formOpen" class="card p-3 my-3" id="form_anak">
                <div class="d-flex justify-content-between">
                  <h3 class="text-primary">Form Gizi Anak</h3>
                  <button class="btn btn-outline-danger" @click="formOpen = !formOpen">
                    X
                  </button>
                </div>
                <div class="row g-2">
                  <div class="col-md-4">
                    <label>NIK</label>
                    <input type="text" class="form-control" v-model="form.nik" :readonly="form.mode === 'input'">
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

                  <div class="col-md-4" v-if="form.mode === 'input'">
                    <label>Berat Badan (kg)</label>
                    <input type="text" class="form-control" v-model="form.bb">
                  </div>

                  <div class="col-md-4" v-if="form.mode === 'input'">
                    <label>Tinggi Badan (cm)</label>
                    <input type="text" class="form-control" v-model="form.tb">
                  </div>

                  <!-- <div class="col-md-4">
                    <label>Lingkar Kepala</label>
                    <input type="text" class="form-control" v-model="form.lika">
                  </div>

                  <div v-if="form.mode === 'update' && activeMenu === 'anak'" class="col-md-3">
                    <label>NO KK</label>
                    <input type="text" class="form-control" v-model="form.no_kk">
                  </div>

                  <div v-if="form.mode === 'update' && activeMenu === 'anak'" class="col-md-3">
                    <label>NIK Wali</label>
                    <input type="text" class="form-control" v-model="form.nik_ortu">
                  </div>

                  <div v-if="form.mode === 'update' && activeMenu === 'anak'" class="col-md-3">
                    <label>Peran Wali</label>
                    <input type="text" class="form-control" v-model="form.peran">
                  </div> -->

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
                  <h5>Kelola Data {{ aktifitas }}</h5>
                  <button @click="isUploadOpen = !isUploadOpen" class="btn btn-sm btn-outline-danger mb-2">
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
                        <a :href="exampleFile">Example</a>
                      </li>
                    </ul>
                  </div>

                  <input ref="fileInput" type="file" accept=".csv,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" class="form-control"
                    @change="handleFileChange($event)" />

                  <!-- Preview / status -->
                  <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div v-if="filePreviewTable.length" class="mt-3">
                      <p class="fw-bold mb-1 text-success">
                        *Preview:
                      </p>
                      <div class="table-responsive">
                        <table class="table table-bordered table-sm small border-success">
                          <thead>
                            <tr>
                              <th v-for="(col, index) in filePreviewTable[0]" :key="'h' + index" width="120"
                                class="text-success">
                                {{ col }}
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(row, rIndex) in filePreviewTable.slice(1)" :key="'r' + rIndex">
                              <td v-for="(col, cIndex) in row" :key="'c' + cIndex" class="text-success">
                                {{ col }}
                              </td>
                            </tr>
                            <tr v-for="(row, rIndex) in filePreviewTable.slice(2)" :key="'r' + rIndex">
                              <td v-for="(col, cIndex) in row" :key="'c' + cIndex" class="text-success">
                                ...
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                    </div>

                    <div v-else class="text-muted small">Belum ada file dipilih</div>
                  </div>

                  <div v-if="fileError" class="mt-2 text-success small">
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
                  <!-- Search + Action -->
                  <div class="row g-2 align-items-end">

                    <!-- Filter -->
                    <div class="col-lg-4 col-12">
                      <div class="d-flex flex-wrap gap-2">
                        <select
                          class="form-select form-select-sm" style="width: 30%;"
                          v-if="role === 'Super Admin' "
                          v-model="filters.kelurahan"
                          @change="handleRegionChange"
                        >
                          <option value="">Pilih Desa</option>
                          <option v-for="desa in listKelurahan"
                                  :key="desa.id"
                                  :value="desa.kelurahan">
                            {{ desa.label }}
                          </option>
                        </select>

                        <select v-model="this.periode" class="form-select form-select-sm w-auto">
                          <option value="">Pilih Periode</option>
                          <option v-for="p in periodeOptions" :key="p.value" :value="p.value">
                            {{ p.label }}
                          </option>
                        </select>

                        <button @click="applyFilter()" class="btn btn-gradient btn-sm">
                          Terapkan
                        </button>
                        <!-- <select v-model="searchDate_kunAn"
                                class="form-select form-select-sm w-auto">
                          <option value="">Pilih Periode</option>
                          <option value="01">Januari</option>
                          <option value="02">Februari</option>
                          <option value="03">Maret</option>
                          <option value="04">April</option>
                          <option value="05">Mei</option>
                          <option value="06">Juni</option>
                          <option value="07">Juli</option>
                          <option value="08">Agustus</option>
                          <option value="09">September</option>
                          <option value="10">Oktober</option>
                          <option value="11">November</option>
                          <option value="12">Desember</option>
                        </select>

                        <select v-model="searchYear_kunAn"
                                class="form-select form-select-sm w-auto">
                          <option value="">Pilih Tahun</option>
                          <option v-for="year in yearOptions"
                                  :key="year"
                                  :value="year">
                            {{ year }}
                          </option>
                        </select> -->

                      </div>
                    </div>

                    <!-- Search + Button -->
                    <div class="col-lg-8 col-12">
                      <div class="d-flex flex-wrap gap-2 justify-content-lg-end">

                        <input type="text"
                              class="form-control form-control-sm w-auto"
                              placeholder="Ketik NIK atau Nama"
                              v-model="searchQuery_kunAn">

                        <button :class="aktifitas === 'Kunjungan Posyandu'? 'btn btn-primary btn-sm' : 'btn btn-outline-primary btn-sm'" type="button"
                          @click="isUploadOpen = !isUploadOpen; aktifitas = 'Kunjungan Posyandu'">
                          <i class="bi bi-filetype-csv me-1"></i> Import Kunjungan
                        </button>
                        <button :class="aktifitas === 'Pendampingan Anak'? 'btn btn-primary btn-sm' : 'btn btn-outline-primary btn-sm'" type="button"
                          @click="isUploadOpen = !isUploadOpen; aktifitas = 'Pendampingan Anak'">
                          <i class="bi bi-filetype-csv me-1"></i> Import Pendampingan
                        </button>
                        <button :class="aktifitas === 'Intervensi Anak'? 'btn btn-primary btn-sm' : 'btn btn-outline-primary btn-sm'" type="button"
                          @click="isUploadOpen = !isUploadOpen; aktifitas = 'Intervensi Anak'">
                          <i class="bi bi-filetype-csv me-1"></i> Import Intervensi
                        </button>

                      </div>
                    </div>

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
                       alternating
                      border-cell
                    >

                      <template #header-select>
                        <input
                          type="checkbox"
                          v-model="selectAll_anak"
                          @change="toggleSelectAll"
                        />
                      </template>

                      <template #item-select="{ nik }">
                        <div class="text-center">
                          <input
                            type="checkbox"
                            :value="nik"
                            v-model="selectedIds_anak"
                            @change="syncSelectAll"
                          />
                        </div>
                      </template>
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

                    <select
                      v-model="bulkAnak"
                      :disabled="!selectedIds_anak.length"
                      class="form-select form-select-sm w-auto"
                      @change="handleBulkAction"
                    >
                      <option value="">-- Pilih Aksi --</option>
                      <option value="kunjungan_anak">
                        Hapus Data Kunjungan Anak ({{ selectedIds_anak.length }})
                      </option>
                      <option value="pendampingan_anak">
                        Hapus Data Pendampingan Anak ({{ selectedIds_anak.length }})
                      </option>
                      <option value="intervensi_anak">
                        Hapus Data Intervensi Anak ({{ selectedIds_anak.length }})
                      </option>
                      <option value="data_anak">
                        Hapus Data Anak ({{ selectedIds_anak.length }})
                      </option>
                    </select>

                    <!-- <button
                      class="btn btn-danger btn-sm mt-2"
                      :disabled="!selectedIds_anak.length"
                      @click="bulkDelete"
                    >
                      <i class="bi bi-trash"></i>
                      Hapus ({{ selectedIds_anak.length }})
                    </button> -->
                  </div>

                </div>
              </div>
            </div>

            <!-- Import Bumil -->
            <div class="tab-pane fade" id="bumil-tab-pane" role="tabpanel" tabindex="0">
              <div v-if="formOpen_bumil" id="form_bumil" class="card p-3 my-3">
                <div class="d-flex justify-content-between">
                  <h3>Form Ibu Hamil</h3>
                  <button class="btn btn-outline-danger" @click="formOpen_bumil = !formOpen_bumil">
                    X
                  </button>
                </div>
                <div class="row g-2">
                  <div :class="form_bumil.mode === 'update' ? 'col-md-6' : 'col-md-4'">
                    <label>NIK</label>
                    <input type="text" class="form-control" v-model="form_bumil.nik_ibu" :readonly="form_bumil.mode === 'input'">
                  </div>

                  <div :class="form_bumil.mode === 'update' ? 'col-md-6' : 'col-md-4'">
                    <label>Nama Ibu</label>
                    <input type="text" class="form-control" v-model="form_bumil.nama_ibu" :readonly="form_bumil.mode === 'input'">
                  </div>

                  <div :class="form_bumil.mode === 'update' ? 'col-md-6' : 'col-md-4'">
                    <label>NIK Suami</label>
                    <input type="text" class="form-control" v-model="form_bumil.nik_suami" :readonly="form_bumil.mode === 'input'">
                  </div>

                  <div :class="form_bumil.mode === 'update' ? 'col-md-6' : 'col-md-4'">
                    <label>Nama Suami</label>
                    <input type="text" class="form-control" v-model="form_bumil.nama_suami" :readonly="form_bumil.mode === 'input'">
                  </div>

                  <div v-if="form_bumil.mode === 'input'" class="col-md-4">
                    <label>Tanggal Pendampingan</label>
                    <input type="date" class="form-control" v-model="form_bumil.tanggal_pendampingan">
                  </div>

                  <div class="col-md-4" v-if="form_bumil.mode === 'input'">
                    <label>Berat Badan (kg)</label>
                    <input type="text" class="form-control" v-model="form_bumil.bb">
                  </div>

                  <div class="col-md-4" v-if="form_bumil.mode === 'input'">
                    <label>Tinggi Badan (cm)</label>
                    <input type="text" class="form-control" v-model="form_bumil.tb">
                  </div>

                  <div class="col-md-4" v-if="form_bumil.mode === 'input'">
                    <label>Lingkar Lengan</label>
                    <input type="text" class="form-control" v-model="form_bumil.lila">
                  </div>

                  <div class="col-md-4" v-if="form_bumil.mode === 'input'">
                    <label>Hb</label>
                    <input type="text" class="form-control" v-model="form_bumil.hb">
                  </div>
                  <input type="hidden" v-model="form_bumil.jml_anak">
                  <input type="hidden" v-model="form_bumil.usia_ibu">
                  <input type="hidden" v-model="form_bumil.usia_kehamilan">
                  <input type="hidden" v-model="form_bumil.hpl">

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
                  <h5>Kelola Data {{ aktifitas }}</h5>
                  <button @click="isUploadOpen_bumil = !isUploadOpen_bumil" class="btn btn-sm btn-outline-danger mb-2">
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
                        <a :href="exampleFile">Example</a>
                      </li>
                    </ul>
                  </div>

                  <input ref="fileInput" type="file"  accept=".csv,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" class="form-control"
                    @change="handleFileChange($event)" />

                  <!-- Preview / status -->
                  <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div v-if="filePreviewTable_bumil.length" class="mt-3">
                      <p class="fw-bold mb-1 text-success">
                        *Preview:
                      </p>
                      <div class="table-responsive">
                        <table class="table table-bordered table-sm small border-success">
                          <thead>
                            <tr>
                              <th v-for="(col, index) in filePreviewTable_bumil[0]" :key="'h' + index" width="120"
                                class="text-success">
                                {{ col }}
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(row, rIndex) in filePreviewTable_bumil.slice(1)" :key="'r' + rIndex">
                              <td v-for="(col, cIndex) in row" :key="'c' + cIndex" class="text-success">
                                {{ col }}
                              </td>
                            </tr>
                            <tr v-for="(row, rIndex) in filePreviewTable_bumil.slice(2)" :key="'r' + rIndex">
                              <td v-for="(col, cIndex) in row" :key="'c' + cIndex" class="text-success">
                                ...
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                    </div>

                    <div v-else class="text-muted small">Belum ada file dipilih</div>
                  </div>

                  <div v-if="fileError" class="mt-2 text-success small">
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
                  <div class="row g-2 align-items-end">

                    <!-- Filter -->
                    <div class="col-lg-6 col-12">
                      <div class="d-flex flex-wrap gap-2">

                        <select
                          class="form-select form-select-sm w-auto"
                          v-if="role === 'Super Admin' "
                          v-model="filters.kelurahan"
                          @change="handleRegionChange"
                        >
                          <option value="">Pilih Desa</option>
                          <option v-for="desa in listKelurahan"
                                  :key="desa.id"
                                  :value="desa.kelurahan">
                            {{ desa.label }}
                          </option>
                        </select>

                        <select v-model="this.periode" class="form-select form-select-sm w-auto">
                          <option value="">Pilih Periode</option>
                          <option v-for="p in periodeOptions" :key="p.value" :value="p.value">
                            {{ p.label }}
                          </option>
                        </select>

                        <button @click="applyFilter()" class="btn btn-gradient btn-sm">
                          Terapkan
                        </button>
                        <!-- <select v-if="role === 'Super Admin' " v-model="selectDesa_bumil"
                                class="form-select form-select-sm w-auto">
                          <option value="">Pilih Desa</option>
                          <option v-for="desa in listKelurahan"
                                  :key="desa.id"
                                  :value="desa.kelurahan">
                            {{ desa.label }}
                          </option>
                        </select>

                        <select v-model="searchDate_bumil"
                                class="form-select form-select-sm w-auto">
                          <option value="">Pilih Periode</option>
                          <option value="01">Januari</option>
                          <option value="02">Februari</option>
                          <option value="03">Maret</option>
                          <option value="04">April</option>
                          <option value="05">Mei</option>
                          <option value="06">Juni</option>
                          <option value="07">Juli</option>
                          <option value="08">Agustus</option>
                          <option value="09">September</option>
                          <option value="10">Oktober</option>
                          <option value="11">November</option>
                          <option value="12">Desember</option>
                        </select>

                        <select v-model="searchYear_bumil"
                                class="form-select form-select-sm w-auto">
                          <option value="">Pilih Tahun</option>
                          <option v-for="year in yearOptions"
                                  :key="year"
                                  :value="year">
                            {{ year }}
                          </option>
                        </select> -->

                      </div>
                    </div>

                    <!-- Search + Button -->
                    <div class="col-lg-6 col-12">
                      <div class="d-flex flex-wrap gap-2 justify-content-lg-end">

                        <input type="text" class="form-control form-control-sm" style="width: 220px;" placeholder="Ketik NIK atau Nama" v-model="searchQuery_bumil">

                        <button :class="aktifitas === 'Pendampingan Bumil'?'btn btn-primary btn-sm':'btn btn-outline-primary btn-sm'" type="button"
                          @click="isUploadOpen_bumil = !isUploadOpen_bumil; aktifitas = 'Pendampingan Bumil'">
                          <i class="bi bi-filetype-csv me-1"></i> Import Pendampingan
                        </button>
                        <button :class="aktifitas === 'Intervensi Bumil'?'btn btn-primary btn-sm':'btn btn-outline-primary btn-sm'" type="button"
                          @click="isUploadOpen_bumil = !isUploadOpen_bumil; aktifitas = 'Intervensi Bumil'">
                          <i class="bi bi-filetype-csv me-1"></i> Import Intervensi
                        </button>

                      </div>
                    </div>

                  </div>
                  <!-- Table -->
                  <div class="mt-3">

                    <!-- Search + Row Per Page -->
                    <easy-data-table :headers="headers_bumil" :items="items_bumil" :sortable="true"
                      :search-value="searchQuery_bumil" :rows-per-page="perPage" :rows-items="perPageOptions"
                      :rows-per-page-text="'Rows per page'" header-text-direction="center"
                      table-class-name="my-custom-table" header-class-name="my-custom-header"  alternating
                      border-cell>

                      <template #header-select>
                        <input
                          type="checkbox"
                          v-model="selectAll_bumil"
                          @change="toggleSelectAll"
                        />
                      </template>

                      <template #item-select="{ nik_ibu }">
                        <div class="text-center">
                          <input
                            type="checkbox"
                            :value="nik_ibu"
                            v-model="selectedIds_bumil"
                            @change="syncSelectAll"
                          />
                        </div>
                      </template>

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

                    <button
                        class="btn btn-danger btn-sm mt-2"
                        :disabled="!selectedIds_bumil.length"
                        @click="bulkDelete"
                      >
                        <i class="bi bi-trash"></i>
                        Hapus ({{ selectedIds_bumil.length }})
                      </button>
                  </div>

                </div>
              </div>
            </div>

            <!-- Import Catin -->
            <div class="tab-pane fade" id="catin-tab-pane" role="tabpanel" tabindex="0">
              <div v-if="formOpen_catin" class="card p-3 my-3" id="form_catin">
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
                      :readonly="form_catin.mode === 'input'">
                  </div>

                  <div class="col-md-4">
                    <label>Usia Calon Perempuan</label>
                    <input type="number" class="form-control" v-model="form_catin.usia_perempuan">
                  </div>

                  <div class="col-md-4">
                    <label>NIK Pasangan</label>
                    <input type="text" class="form-control" v-model="form_catin.nik_laki" :readonly="form_catin.mode === 'input'">
                  </div>

                  <div class="col-md-4">
                    <label>Nama Pasangan</label>
                    <input type="text" class="form-control" v-model="form_catin.nama_laki" :readonly="form_catin.mode === 'input'">
                  </div>

                  <div class="col-md-4">
                    <label>Usia Pasangan</label>
                    <input type="number" class="form-control" v-model="form_catin.usia_laki">
                  </div>

                  <div v-if="form_catin.mode === 'update'" class="col-md-12">
                    <label>Rencana Tanggal Menikah</label>
                    <input type="date" class="form-control" v-model="form_catin.tanggal_menikah">
                  </div>

                  <div v-if="form_catin.mode === 'input'" class="col-md-12">
                    <label>Tanggal Pendampingan</label>
                    <input type="date" class="form-control" v-model="form_catin.tanggal_pendampingan">
                  </div>

                  <div v-if="form_catin.mode === 'input'"  class="col-md-4">
                    <label>Berat Badan (kg)</label>
                    <input type="text" class="form-control" v-model="form_catin.berat_perempuan">
                  </div>

                  <div v-if="form_catin.mode === 'input'"  class="col-md-4">
                    <label>Tinggi Badan (cm)</label>
                    <input type="text" class="form-control" v-model="form_catin.tinggi_perempuan">
                  </div>

                  <div v-if="form_catin.mode === 'input'"  class="col-md-4">
                    <label>Hb</label>
                    <input type="text" class="form-control" v-model="form_catin.kadar_hb">
                  </div>

                  <div v-if="form.mode === 'input'"  class="col-md-4">
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
                  <h5>Kelola Data {{ aktifitas }}</h5>
                  <button @click="isUploadOpen_catin = !isUploadOpen_catin" class="btn btn-sm btn-outline-danger mb-2">
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
                        <a :href="exampleFile">Example</a>
                      </li>
                    </ul>
                  </div>

                  <input ref="fileInput" type="file" accept=".csv,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" class="form-control"
                    @change="handleFileChange($event)" />

                  <!-- Preview / status -->
                  <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div v-if="filePreviewTable_catin.length" class="mt-3">
                      <p class="fw-bold mb-1 text-success">
                        *Preview:
                      </p>
                      <div class="table-responsive">
                        <table class="table table-bordered table-sm border-success" style="font-size: 80%;">
                          <thead>
                            <tr>
                              <th v-for="(col, index) in PreviewTable_catin" :key="'h' + index" width="120"
                                class="text-success">
                                {{ col }}
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(row, rIndex) in filePreviewTable_catin.slice(0)" :key="'r' + rIndex">
                              <td v-for="(col, cIndex) in row" :key="'c' + cIndex" class="text-success">
                                {{ col }}
                              </td>
                            </tr>
                            <tr v-for="(row, rIndex) in filePreviewTable_catin.slice(1)" :key="'r' + rIndex">
                              <td v-for="(col, cIndex) in row" :key="'c' + cIndex" class="text-success">
                                ...
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                    </div>

                    <div v-else class="text-muted small">Belum ada file dipilih</div>
                  </div>

                  <div v-if="fileError" class="mt-2 text-success small">
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
                  <div class="row g-2 align-items-end">

                    <!-- Filter -->
                    <div class="col-lg-6 col-12">
                      <div class="d-flex flex-wrap gap-2">

                        <select
                          class="form-select form-select-sm w-auto"
                          v-if="role === 'Super Admin' "
                          v-model="filters.kelurahan"
                          @change="handleRegionChange"
                        >
                          <option value="">Pilih Desa</option>
                          <option v-for="desa in listKelurahan"
                                  :key="desa.id"
                                  :value="desa.kelurahan">
                            {{ desa.label }}
                          </option>
                        </select>

                        <select v-model="this.periode" class="form-select form-select-sm w-auto">
                          <option value="">Pilih Periode</option>
                          <option v-for="p in periodeOptions" :key="p.value" :value="p.value">
                            {{ p.label }}
                          </option>
                        </select>

                        <button @click="applyFilter()" class="btn btn-gradient btn-sm">
                          Terapkan
                        </button>
                        <!-- <select v-if="role === 'Super Admin'" v-model="selectDesa_catin"
                                class="form-select form-select-sm w-auto">
                          <option value="">Pilih Desa</option>
                          <option v-for="desa in listKelurahan"
                                  :key="desa.id"
                                  :value="desa.kelurahan">
                            {{ desa.label }}
                          </option>
                        </select>

                        <select v-model="searchDate_catin"
                                class="form-select form-select-sm w-auto">
                          <option value="">Pilih Periode</option>
                          <option value="01">Januari</option>
                          <option value="02">Februari</option>
                          <option value="03">Maret</option>
                          <option value="04">April</option>
                          <option value="05">Mei</option>
                          <option value="06">Juni</option>
                          <option value="07">Juli</option>
                          <option value="08">Agustus</option>
                          <option value="09">September</option>
                          <option value="10">Oktober</option>
                          <option value="11">November</option>
                          <option value="12">Desember</option>
                        </select>

                        <select v-model="searchYear_catin"
                                class="form-select form-select-sm w-auto">
                          <option value="">Pilih Tahun</option>
                          <option v-for="year in yearOptions"
                                  :key="year"
                                  :value="year">
                            {{ year }}
                          </option>
                        </select> -->

                      </div>
                    </div>

                    <!-- Search + Button -->
                    <div class="col-lg-6 col-12">
                      <div class="d-flex flex-wrap gap-2 justify-content-lg-end">

                        <input type="text" class="form-control form-control-sm" style="width: 220px;"
                          placeholder="Ketik NIK atau Nama" v-model="searchQuery_catin">

                        <button :class="aktifitas === 'Pendampingan Catin'?'btn btn-primary btn-sm' : 'btn btn-outline-primary btn-sm'" type="button"
                          @click="isUploadOpen_catin = !isUploadOpen_catin; aktifitas = 'Pendampingan Catin'">
                          <i class="bi bi-filetype-csv me-1"></i> Import Pendampingan
                        </button>

                      </div>
                    </div>

                  </div>

                  <!-- Table -->
                  <div class="mt-3">

                    <!-- Search + Row Per Page -->
                    <easy-data-table :headers="headers_catin" :items="items_catin" :sortable="true"
                      :search-value="searchQuery_catin" :rows-per-page="perPage" :rows-items="perPageOptions"
                      :rows-per-page-text="'Rows per page'" header-text-direction="center"
                      table-class-name="my-custom-table" header-class-name="my-custom-header"  alternating
                      border-cell>

                      <template #header-select>
                        <input
                          type="checkbox"
                          v-model="selectAll_catin"
                          @change="toggleSelectAll"
                        />
                      </template>

                      <template #item-select="{ nik }">
                        <div class="text-center">
                          <input
                            type="checkbox"
                            :value="nik"
                            v-model="selectedIds_catin"
                            @change="syncSelectAll"
                          />
                        </div>
                      </template>

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

                    <button
                        class="btn btn-danger btn-sm mt-2"
                        :disabled="!selectedIds_catin.length"
                        @click="bulkDelete"
                      >
                        <i class="bi bi-trash"></i>
                        Hapus ({{ selectedIds_catin.length }})
                      </button>
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
    <p class="text-white mt-3">Sedang {{transaksi}} data...</p>
  </div>
</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import axios from 'axios'
import Welcome from '@/components/Welcome.vue'
import EasyDataTable from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'
import * as XLSX from 'xlsx'
import Swal from 'sweetalert2'

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
      bulkAnak:'',
      bulkBumil:'',
      bulkCatin:'',
      periodeOptions: [],
      listKelurahan:[],
      role:'',
      selectedIds_anak: [],      // ← ID terpilih
      selectAll_anak: false,     // ← checkbox header
      selectedIds_bumil: [],      // ← ID terpilih
      selectAll_bumil: false,     // ← checkbox header
      selectedIds_catin: [],      // ← ID terpilih
      selectAll_catin: false,     // ← checkbox header
      transaksi:'',
      progressLevel:null,
      formOpen_bumil: '',
      formOpen_catin: '',
      isLoadingImport: false,
      importProgress: 0,
      animatedProgress: 0,
      currentRow: 0,
      totalRows: 1,
      form: {
        isEmptyNIK:"",
        id:"",
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
        no_kk:"",
        peran: "",
        nik_ortu: "",
      },
      form_bumil: {
        isEmptyNIK:"",
        id:"",
        mode: "",
        tanggal_pendampingan: "",
        nik_ibu: "",
        nik_suami: "",
        nama_ibu: "",
        nama_suami: "",
        hb: "",
        bb: "",
        tb: "",
        lila: "",
        jml_anak: "",
        usia_ibu: "",
        usia_kehamilan: "",
      },
      form_catin: {
        isEmptyNIK:"",
        id:"",
        mode: "",
        tanggal_pendampingan: "",
        nik: "",
        nik_laki:"",
        usia_perempuan:"",
        usia_laki:"",
        kadar_hb: "",
        nama_perempuan: "",
        nama_laki: "",
        berat_perempuan: "",
        tinggi_perempuan: "",
        lika_perempuan: "",
        tanggal_menikah:"",
      },
      sortKey: '',
      sortOrder: 'asc',
      headers_catin: [
        { text: '', value: 'select', width: 50 },
        {
          text: 'NIK Perempuan',
          value: 'nik',
          sortable: true,
          align: 'center',
        },
        {
          text: 'Tanggal Pendampingan',
          value: 'tanggal_pendampingan',
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
        {
          text: 'Desa',
          value: 'desa',
          sortable: true,
          align: 'center',
        },
        { text: 'Action', value: 'action', width: 120, align: "center", class: "col-action" },
      ],
      headers_bumil: [
        { text: '', value: 'select', width: 50 },
        { text: "NIK", value: "nik_ibu", sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: "Nama", value: "nama_ibu", width: 130, sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: "Suami", value: "nama_suami", width: 130, sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: "Kehamilan ke", value: "kehamilan_ke", sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: "Hb", value: "kadar_hb", sortable: true, align: "center", class: "align-middle text-center cursor-pointer" },
        { text: "Lila", value: "lila", sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: "Usia (Thn)", value: "usia_ibu", sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: "HPL", value: "hpl", sortable: true, width:100 ,class: "align-middle text-center cursor-pointer" },
        { text: "Tgl Pendampingan", value: "tgl_pendampingan", sortable: true, width: 130, class: "align-middle text-center cursor-pointer" },
        { text: "Tgl Intervensi", value: "intervensi", sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: "Desa", value: "desa", sortable: true, class: "align-middle text-center cursor-pointer" },
        { text: "Action", value: "action", width: 120, align: "center", class: "col-action" },
      ],
      headers_kunAn: [
        { text: '', value: 'select', width: 50 },
        { text: 'NIK', value: 'nik', sortable: true },
        { text: 'Nama', value: 'nama_anak', sortable: true },
        { text: 'Orang Tua', value: 'nama_ortu', sortable: true },
        { text: 'JK', value: 'gender', sortable: true },
        { text: 'Tgl Lahir', value: 'tgl_lahir', sortable: true },
        { text: 'BB', value: 'bb', sortable: true },
        { text: 'TB', value: 'tb', sortable: true },
        { text: 'Tgl Pengukuran', value: 'tgl_pengukuran', sortable: true },
        { text: 'Posyandu', value: 'unit_posyandu', sortable: true },
        { text: 'Tgl Pendampingan', value: 'pendampingan', sortable: true },
        { text: 'Tgl Intervensi', value: 'intervensi', sortable: true },
        { text: 'Desa', value: 'desa', width: 120, sortable: true },
        { text: 'Action', value: 'action', width: 120, align: "center", class: "col-action" },
      ],
      formOpen: false,
      yearOptions: [],
      searchQuery_kunAn: "",
      selectDesa_kunAn: "",
      searchDate_kunAn: "",
      searchYear_kunAn: "",
      searchQuery_bumil: "",
      selectDesa_bumil: "",
      searchDate_bumil: "",
      searchYear_bumil: "",
      searchQuery_catin: "",
      selectDesa_catin: "",
      searchDate_catin: "",
      searchYear_catin: "",
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
      aktifitas: 'Kunjungan Posyandu',
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
      PreviewTable_catin:[],
      filePreviewTable: [],
      filePreviewTable_catin: [],
      filePreviewTable_bumil: [],
      periode:'',
      filters: {
        provinsi: '',
        kota: '',
        kecamatan: '',
        kelurahan: '',
        idWilayah: '',
        periodeAwal: '',
        periodeAkhir: '',
      },
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
    /* role() {
      return localStorage.getItem('role')
    },
    isAdmin() {
      return this.role === 'Super Admin'
    }, */
    exampleFile() {
      switch (this.aktifitas) {
        case "Kunjungan Posyandu":
          return "/example_kunjungan_posyandu.xlsx";
        case "Pendampingan Anak":
          return "/example_pendampingan_anak.xlsx";
        case "Intervensi Anak":
          return "/example_intervensi_anak.xlsx";
        case "Pendampingan Bumil":
          return "/example_pendampingan_bumil.xlsx";
        case "Intervensi Bumil":
          return "/example_intervensi_bumil.xlsx";
        case "Pendampingan Catin":
          return "/example_pendampingan_catin.xlsx";
        default:
          return "#";
      }
    },

    // ==================== ANAK ====================
    filteredAnak() {
      if (!this.dataLoad) return [];
      //console.log('anak:',this.dataLoad);

      const arr = Array.isArray(this.dataLoad)
        ? this.dataLoad
        : Object.values(this.dataLoad);

      const q = this.searchQuery_kunAn?.toLowerCase()?.trim() ?? "";
      const a = this.selectDesa_kunAn?.toLowerCase()?.trim() ?? "";
      const m = this.searchDate_kunAn ?? "";
      const y  = this.searchYear_kunAn ?? "";

      return arr.filter(item => {
        /** 🔎 SEARCH (nama / nik) */
        const nama = item.nama?.toLowerCase() ?? "";
        const nik  = item.nik?.toString() ?? "";

        const matchSearch = !q || nama.includes(q) || nik.includes(q);

        /** 🏘️ DESA */
        const desa = item.kelurahan?.toLowerCase().trim() ?? "";
        const matchDesa = !a || desa === a;

        /** 🏘️ Tanggal */
        let matchDate = true;

        if (m || y) {
          if (!Array.isArray(item.posyandu) || !item.posyandu.length) {
            return false;
          }

          const latestPosyandu = [...item.posyandu].sort(
            (a, b) => new Date(b.tgl_ukur) - new Date(a.tgl_ukur)
          )[0];

          const tgl = latestPosyandu?.tgl_ukur;
          if (!tgl) return false;

          const [year, month] = tgl.split("-");

          if (m && month != m) matchDate = false;
          if (y && year != y) matchDate = false;
        }

        return matchSearch && matchDesa && matchDate;
      });
    },

    items_kunAn() {
      const latestByNik = {}

      this.filteredAnak.forEach(item => {
        const nik = item.nik ?? "-"

        // 🔑 ambil posyandu TERBARU dari item ini
        const latestPosyandu = Array.isArray(item.posyandu) && item.posyandu.length
          ? [...item.posyandu].sort(
              (a, b) => new Date(b.tgl_ukur) - new Date(a.tgl_ukur)
            )[0]
          : null

        const currentTgl = latestPosyandu?.tgl_ukur
          ? new Date(latestPosyandu.tgl_ukur)
          : null

        // kalau NIK belum ada → simpan
        if (!latestByNik[nik]) {
          latestByNik[nik] = { ...item, latestPosyandu }
          return
        }

        // bandingkan dengan data tersimpan
        const savedTgl = latestByNik[nik].latestPosyandu?.tgl_ukur
          ? new Date(latestByNik[nik].latestPosyandu.tgl_ukur)
          : null

        // ambil yang TERBARU
        if (currentTgl && (!savedTgl || currentTgl > savedTgl)) {
          latestByNik[nik] = { ...item, latestPosyandu }
        }
      })

      return Object.values(latestByNik)
        .sort((a, b) => a.id - b.id)
        .map(item => ({
          id: item.id,
          nik: item.nik ?? "-",
          nama_anak: item.nama ?? "-",
          nama_ortu: item.keluarga?.[0]?.nama_ortu ?? "-",
          gender: item.jk === "L" ? "Laki-laki" : "Perempuan",
          tgl_lahir: this.formatDate(item.kelahiran?.[0]?.tgl_lahir),

          // ✅ PAKAI DATA POSYANDU TERBARU
          bb: item.latestPosyandu?.bb ?? "-",
          tb: item.latestPosyandu?.tb ?? "-",
          tgl_pengukuran: item.latestPosyandu?.tgl_ukur ?? "-",
          unit_posyandu: item.latestPosyandu?.posyandu ?? "-",

          intervensi: item.intervensi?.[0]?.tgl_intervensi ?? "-",
          pendampingan: item.pendampingan?.[0]?.tanggal_pendampingan ?? "-",
          desa: item.kelurahan ?? "-",
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
      const a = this.selectDesa_bumil?.toLowerCase()?.trim() ?? "";
      const m = this.searchDate_bumil ?? "";
      const y  = this.searchYear_bumil ?? "";

      return arr.filter(item => {
        /** 🔎 SEARCH (nama / nik) */
        const nama = item.nama_ibu?.toLowerCase() ?? "";
        const nik  = item.nik_ibu?.toString() ?? "";

        const matchSearch = !q || nama.includes(q) || nik.includes(q);

        /** 🏘️ DESA */
        const desa = item.kelurahan?.toLowerCase().trim() ?? "";
        const matchDesa = !a || desa === a;

        /** 📅 TANGGAL */
        let matchDate = true;

        if (m || y) {
          const tgl = item.tanggal_pendampingan;
          if (!tgl) return false;

          const [year, month] = tgl.split("-");

          if (m && month !== m) matchDate = false;
          if (y && year != y) matchDate = false;

          console.log({ tgl, m, y, month, year, matchDate });
        }

        return matchSearch && matchDesa && matchDate;
      });

    },

    items_bumil() {
      const latestByNik = {}
      //console.log(item);

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
          console.log('latest',latestByNik[nik]);
        }

      })

      return Object.values(latestByNik).map(item => ({
        nik_ibu: item.nik_ibu ?? "-",
        nama_ibu: item.nama_ibu ?? "-",
        nik_suami: item.nik_suami ?? "-",
        nama_suami: item.nama_suami ?? "-",
        hpl: this.formatDate(item.hpl) ?? "-",
        kehamilan_ke: item.kehamilan_ke ?? "-",
        kadar_hb: item.riwayat_pemeriksaan?.kadar_hb ?? "-",
        lila: item.riwayat_pemeriksaan?.lila ?? "-",
        usia_ibu: item.usia_ibu ?? "-",
        usia_kehamilan: item.riwayat_pemeriksaan?.[0]?.usia_kehamilan_minggu ?? "-",
        bb: item.riwayat_pemeriksaan?.[0]?.berat_badan ?? "-",
        tb: item.riwayat_pemeriksaan?.[0]?.tinggi_badan ?? "-",
        tgl_pendampingan: this.formatDate(item.tanggal_pendampingan) ?? "-",
        jml_anak: item.jumlah_anak ?? 0,
        desa: item.kelurahan,
        intervensi: item.intervensi?.[0]?.tanggal ?? "-",
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

      //console.log('data:',arr[0].tgl_kunjungan);
      const q = this.searchQuery_catin.toLowerCase().trim();
      const a = this.selectDesa_catin?.toLowerCase()?.trim() ?? "";
      const m = this.searchDate_catin ?? "";
      const y  = this.searchYear_catin ?? "";

      return arr.filter(item => {
        const namaP = item.nama_perempuan?.toLowerCase() ?? "";
        const namaL = item.nama_laki?.toLowerCase() ?? "";
        const nikP = item.nik_perempuan ?? "";
        const nikL = item.nik_laki ?? "";

        const matchSearch = !q || namaP.includes(q) || namaL.includes(q) || nikP.includes(q) ||nikL.includes(q);

        /** 🏘️ DESA */
        const desa = item.kelurahan?.toLowerCase().trim() ?? "";
        const matchDesa = !a || desa === a;

        /** 🏘️ Tanggal */
        let matchDate = true;

        if (m || y) {
          if (!item.tgl_kunjungan) return false;

          const [year, monthRaw] = item.tgl_kunjungan.split("-");
          const month = monthRaw.padStart(2, "0");

          if (m && month !== m) matchDate = false;
          if (y && year != y) matchDate = false;
        }

        return matchSearch && matchDesa && matchDate;
      });

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
        const pendampingan = item.riwayat_pendampingan?.[0] ?? null

        return {
          nik: item.nik_perempuan ?? "-",
          nama_perempuan: item.nama_perempuan ?? "-",
          nama_laki: item.nama_laki ?? "-",
          usia_perempuan: item.usia_perempuan ?? "-",
          bb: pemeriksaan?.berat_perempuan ?? "-",
          tb: pemeriksaan?.tinggi_perempuan ?? "-",
          hb_perempuan: pemeriksaan?.hb_perempuan ?? "-",
          lila_perempuan: pemeriksaan?.lila_perempuan ?? "-",
          desa: item.kelurahan ?? "-",

          // 🔑 RAW DATE (buat filter)
          tanggal_pendampingan_raw: pendampingan?.tanggal_pendampingan ?? null,

          // 🎨 DISPLAY DATE (buat tabel)
          tanggal_pendampingan: this.formatDate(pendampingan?.tanggal_pendampingan) ?? "-",

          tanggal_menikah: this.formatDate(item.tgl_pernikahan) ?? "-",
          action: { ...item }
        }
      })
    }

  },
  methods: {
    setPeriodeRange() {
      if (!this.periode) {
        this.filters.periodeAwal = ''
        this.filters.periodeAkhir = ''
        return
      }

      const bulan = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
      ]

      const [year, month] = this.periode.split('-')
      const namaBulan = bulan[parseInt(month) - 1]

      const formatted = `${namaBulan} ${year}`

      this.filters.periodeAwal = formatted
      this.filters.periodeAkhir = formatted
    },
    async applyFilter() {
      this.isLoading = true
      try {

        // 🔥 generate periode range dulu
        this.setPeriodeRange()

        await this.loadData()
      } finally {
        this.isLoading = false
      }
    },
    generatePeriodeOptions() {
      const bulan = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
      ]

      const now = new Date()
      const result = []

      for (let i = 0; i < 12; i++) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
        const value = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}` // ✅ pakai tanda "-"
        const label = `${bulan[d.getMonth()]} ${d.getFullYear()}`
        result.push({ label, value })
      }

      this.periodeOptions = result
      //console.log('Periode options:', this.periodeOptions)
    },
    async getWilayahUser() {
      try {
        const res = await axios.get(`${baseURL}/api/user/region`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })
        const wilayah = res.data
        this.filters.idWilayah = wilayah.id_wilayah
        this.filters.provinsi = wilayah.provinsi
        this.filters.kota = wilayah.kota
        this.filters.kecamatan = wilayah.kecamatan
        this.filters.kelurahan = wilayah.kelurahan
        //console.log('✅ getWilayahUser ->', this.filters)
      } catch (e) {
        console.error('❌ getWilayahUser error:', e)
        this.kelurahan = '-'
      }
    },
    async loadRegion() {
      const res = await axios.get(
        `${baseURL}/api/region`,
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        }
      )

      this.listKelurahan = res.data.data
      .flatMap(item => item.options || [])
      .map(opt => ({
        id: opt.id,
        kelurahan: opt.kelurahan,
        label: opt.label,
        kecamatan: opt.kecamatan,
        kota: opt.kota,
        provinsi: opt.provinsi,
      }));
    },
    toggleSelectAll() {
      //console.log(this.items);
      switch (this.activeMenu) {
          case 'anak':
            if (this.selectAll_anak) {
              this.selectedIds_anak = this.items_kunAn.map(i => i.nik)
            } else {
              this.selectedIds_anak = []
            }
            break;
          case 'bumil':
            if (this.selectAll_bumil) {
              this.selectedIds_bumil = this.items_bumil.map(i => i.nik_ibu)
            } else {
              this.selectedIds_bumil = []
            }
            break;
          case 'catin':
              if (this.selectAll_catin) {
                this.selectedIds_catin = this.items_catin.map(i => i.nik)
              } else {
                this.selectedIds_catin = []
              }
              break;
          default:
            break;
      }
    },
    syncSelectAll() {
      switch (this.activeMenu) {
          case 'anak':
            this.selectAll_anak =
            this.selectedIds_anak.length === this.items_kunAn.length &&
            this.items_kunAn.length > 0
            break;
          case 'bumil':
            this.selectAll_bumil =
            this.selectedIds_bumil.length === this.items_bumil.length &&
            this.items_bumil.length > 0
            break;
          case 'catin':
            this.selectAll_catin =
            this.selectedIds_catin.length === this.items_catin.length &&
            this.items_catin.length > 0
            break;
          default:
            break;
      }
    },
    async handleBulkAction() {
      if (!this.bulkAnak) return

      await this.bulkDelete()

      // reset select biar gak auto trigger lagi
      this.bulkAnak = ''
    },
    async bulkDelete() {
      let url = ''
      let ids = []
      let extraPayload = {}

      switch (this.activeMenu) {
        case 'anak':
          if (!this.selectedIds_anak.length) return
          url = 'children'
          ids = this.selectedIds_anak
          extraPayload.bulk_type = this.bulkAnak
          extraPayload.filters = this.filters
          break

        case 'bumil':
          if (!this.selectedIds_bumil.length) return
          url = 'pregnancy'
          ids = this.selectedIds_bumil
          break

        case 'catin':
          if (!this.selectedIds_catin.length) return
          url = 'bride'
          ids = this.selectedIds_catin
          break

        default:
          return
      }

      const length = ids.length

      const confirm = await Swal.fire({
        title: 'Konfirmasi',
        html: `Yakin ingin menghapus <b>${length}</b> data ${this.activeMenu}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
          confirmButton: 'btn btn-danger mx-1',
          cancelButton: 'btn btn-secondary mx-1'
        }
      })

      if (!confirm.isConfirmed) return

      try {
        this.isLoadingImport = true
        this.animatedProgress = 10
        this.progressLevel = 10
        this.importProgress = 10

        await axios.post(`${baseURL}/api/${url}/bulk-delete`, {
          ids,
          ...extraPayload
        }, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        // Reset selection
        this.selectedIds_anak = []
        this.selectedIds_bumil = []
        this.selectedIds_catin = []
        this.selectAll_anak = false
        this.selectAll_bumil = false
        this.selectAll_catin = false

        this.animatedProgress = 70
        this.progressLevel = 70
        this.importProgress = 70

        await this.loadData()

        this.importProgress = 100

        await new Promise(resolve => {
          const interval = setInterval(() => {
            if (this.animatedProgress >= 100) {
              clearInterval(interval)
              resolve()
            } else {
              this.animatedProgress += 5
            }
          }, 30)
        })

        Swal.fire({
          icon: 'success',
          html: `Berhasil menghapus <b>${length}</b> data ${this.activeMenu}`,
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-primary'
          }
        })

      } catch (e) {
        Swal.fire({
          title: 'Error',
          html: e.response?.data?.error || 'Terjadi kesalahan saat menghapus data',
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger'
          }
        })
      } finally {
        this.isLoadingImport = false
      }
    },
    formatDateToInput(date) {
      if (!date) return null

      // kalau sudah yyyy-mm-dd, langsung pakai
      if (/^\d{4}-\d{2}-\d{2}$/.test(date)) {
        return date
      }

      // asumsi dd-mm-yyyy
      const [day, month, year] = date.split('-')
      return `${year}-${month}-${day}`
    },
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
    capitalizeName(name) {
      return (name || '')
        .toLowerCase()
        .replace(/\b\w/g, c => c.toUpperCase())
    },
    scrollToForm() {
      this.$nextTick(() => {
        let formId = null

        switch (this.activeMenu) {
          case 'anak':
            formId = 'form_anak'
            break
          case 'bumil':
            formId = 'form_bumil'
            break
          case 'catin':
            formId = 'form_catin'
            break
        }

        if (formId) {
          const el = document.getElementById(formId)
          if (el) {
            el.scrollIntoView({
              behavior: 'smooth',
              block: 'center'
            })
          }
        }
      })
    },
    // Prefill form
    editItem(item) {
      switch (this.activeMenu) {
        case 'anak':
          this.formOpen = true;
          this.form = {
            ...this.form,
            mode: 'update',
            id: item.id,
            isEmptyNIK: item.no_kk ? false : true,
            no_kk:item.no_kk,
            peran: item.peran,
            nik_ortu: item.nik_ortu,
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
            mode: 'update',
            id: item.id,
            nik_ibu: item.nik_ibu ?? "",
            nama_ibu: item.nama_ibu ?? "",
            nik_suami: item.nik_suami ?? "",
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
            mode: 'update',
            id: item.id,
            nik: item.nik ?? null,
            nik_laki:item.nik_laki ?? null,
            tanggal_menikah: this.formatDateToInput(item.tanggal_menikah) ?? null,
            nama_perempuan: item.nama_perempuan ?? null,
            nama_laki: item.nama_laki ?? null,
            usia_perempuan: item.usia_perempuan ?? null,
            usia_laki: item.usia_laki ?? null,
          };
          break;
        default:
          return;
      }
      this.scrollToForm()
    },
    hitungUsiaKehamilanSaatIni(tglPendampingan, usiaKehamilanAwal) {
      if (!tglPendampingan || !usiaKehamilanAwal) return usiaKehamilanAwal

      // format "DD-MM-YYYY" → Date
      const [day, month, year] = tglPendampingan.split('-')
      const tglPendampinganDate = new Date(year, month - 1, day)

      const today = new Date()

      const diffMs = today - tglPendampinganDate
      const diffHari = Math.floor(diffMs / (1000 * 60 * 60 * 24))
      const diffMinggu = Math.floor(diffHari / 7)

      return usiaKehamilanAwal + diffMinggu
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
        case 'bumil':{

          const usiaKehamilanSaatIni = this.hitungUsiaKehamilanSaatIni(
            item.tgl_pendampingan,
            item.usia_kehamilan
          )

          this.formOpen_bumil = true;
          this.form_bumil = {
            ...this.form_bumil,
            mode: 'input',
            nik_ibu: item.nik_ibu ?? "",
            nama_ibu: item.nama_ibu ?? "",
            nik_suami: item.nik_suami ?? "",
            nama_suami: item.nama_suami ?? "",
            usia_kehamilan: item.usia_kehamilan ?? 0,
            usia_kehamilan_saat_ini: usiaKehamilanSaatIni,
            // ⬇️ PENTING: kosongkan input pemeriksaan
            bb: "",
            tb: "",
            lila: "",
            hb: "",

            // optional kalau ada
            tanggal_pendampingan: ""
          };
          break;
        }
        case 'catin':
          this.formOpen_catin = true;
          this.form_catin = {
            ...this.form_catin,
            mode: 'input',
            nik: item.nik ?? "",
            nik_laki: item.nik_laki ?? "",
            tanggal_pendampingan: "",
            usia_perempuan: item.usia_perempuan ?? null,
            usia_laki: item.usia_laki ?? null,
            nama_perempuan: item.nama_perempuan ?? null,
            nama_laki: item.nama_laki ?? null,
            kadar_hb: "",
            berat_perempuan: "",
            tinggi_perempuan: "",
            lika_perempuan: "",
          };
          break;
        default:
          return;
      }
      this.scrollToForm()
    },
    async submitUpdate() {
      this.transaksi = 'menyimpan'
      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0

      await this.$nextTick()
      this.importProgress = 10
      this.animatedProgress = 10

      let nama = this.form.nama_anak || this.form_bumil.nama_ibu || this.form_catin.nama_perempuan
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

              this.importProgress = 70
              this.animatedProgress = 70
              await this.loadData()
              this.importProgress = 90
              this.animatedProgress = 90
              this.resetForm()
              this.importProgress = 100

              // animasi ke 100
              await new Promise(resolve => {
                const interval = setInterval(() => {
                  if (this.animatedProgress >= 100) {
                    clearInterval(interval)
                    resolve()
                  } else {
                    this.animatedProgress += 5
                  }
                }, 30)
              })

              // matikan loading
              this.isLoadingImport = false
              console.log(res.data.message);
              Swal.fire({
                icon: 'success',
                html: `Data <b>${this.capitalizeName(nama)}</b> berhasil diperbarui`,
                buttonsStyling: false,
                customClass: {
                  confirmButton: 'btn btn-primary mx-1',
                  cancelButton: 'btn btn-outline-secondary mx-1'
                }
              })

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

              this.importProgress = 70
              this.animatedProgress = 70
              await this.loadData()
              this.importProgress = 90
              this.animatedProgress = 90
              this.resetForm()
              this.importProgress = 100

              // animasi ke 100
              await new Promise(resolve => {
                const interval = setInterval(() => {
                  if (this.animatedProgress >= 100) {
                    clearInterval(interval)
                    resolve()
                  } else {
                    this.animatedProgress += 5
                  }
                }, 30)
              })

              // matikan loading
              this.isLoadingImport = false

              console.log(res.data.message);
              Swal.fire({
                icon: 'success',
                html: `Data pengukuran <b>${this.capitalizeName(nama)}</b> berhasil ditambahkan`,
                buttonsStyling: false,
                customClass: {
                  confirmButton: 'btn btn-primary mx-1',
                  cancelButton: 'btn btn-outline-secondary mx-1'
                }
              })

            }
            break;
          case 'bumil':
            // MODE UPDATE → PUT
            if (this.form_bumil.mode === 'update') {
              const res = await axios.put(
                `${baseURL}/api/pregnancy/${this.form_bumil.nik_ibu}`,
                this.form_bumil,
                {
                  headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    Authorization: `Bearer ${localStorage.getItem('token')}`,
                  }
                }
              )

              this.importProgress = 70
              this.animatedProgress = 70
              await this.loadData()
              this.importProgress = 90
              this.animatedProgress = 90
              this.resetForm()
              this.importProgress = 100

              // animasi ke 100
              await new Promise(resolve => {
                const interval = setInterval(() => {
                  if (this.animatedProgress >= 100) {
                    clearInterval(interval)
                    resolve()
                  } else {
                    this.animatedProgress += 5
                  }
                }, 30)
              })

              // matikan loading
              this.isLoadingImport = false

              console.log(res.data.message);
              console.log(nama);

              Swal.fire({
                icon: 'success',
                html: `Data <b>${this.capitalizeName(nama)}</b> berhasil diperbarui`,
                buttonsStyling: false,
                customClass: {
                  confirmButton: 'btn btn-primary mx-1',
                  cancelButton: 'btn btn-outline-secondary mx-1'
                }
              })

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

              this.importProgress = 70
              this.animatedProgress = 70
              await this.loadData()
              this.importProgress = 90
              this.animatedProgress = 90
              this.resetForm()
              this.importProgress = 100

              // animasi ke 100
              await new Promise(resolve => {
                const interval = setInterval(() => {
                  if (this.animatedProgress >= 100) {
                    clearInterval(interval)
                    resolve()
                  } else {
                    this.animatedProgress += 5
                  }
                }, 30)
              })

              // matikan loading
              this.isLoadingImport = false
              console.log(res.data.message);
              Swal.fire({
                icon: 'success',
                html: `Data pendampingan <b>${this.capitalizeName(nama)}</b> berhasil ditambahkan`,
                buttonsStyling: false,
                customClass: {
                  confirmButton: 'btn btn-primary mx-1',
                  cancelButton: 'btn btn-outline-secondary mx-1'
                }
              })

            }
            break;
          case 'catin':
            // MODE UPDATE → PUT
            if (this.form_catin.mode === 'update') {
              const res = await axios.put(
                `${baseURL}/api/bride/${this.form_catin.nik}`,
                this.form_catin,
                {
                  headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    Authorization: `Bearer ${localStorage.getItem('token')}`,
                  }
                }
              )

              this.importProgress = 70
              this.animatedProgress = 70
              await this.loadData()
              this.importProgress = 90
              this.animatedProgress = 90
              this.resetForm()
              this.importProgress = 100

              // animasi ke 100
              await new Promise(resolve => {
                const interval = setInterval(() => {
                  if (this.animatedProgress >= 100) {
                    clearInterval(interval)
                    resolve()
                  } else {
                    this.animatedProgress += 5
                  }
                }, 30)
              })

              // matikan loading
              this.isLoadingImport = false
              const msg = res.data.message;
              Swal.fire({
                icon: 'success',
                html: msg ?? `Data <b>${this.capitalizeName(nama)}</b> berhasil diperbarui`,
                buttonsStyling: false,
                customClass: {
                  confirmButton: 'btn btn-primary mx-1',
                  cancelButton: 'btn btn-outline-secondary mx-1'
                }
              })

            }
            // MODE INPUT → POST
            else {
              const res = await axios.post(
                `${baseURL}/api/bride`,
                this.form_catin,
                {
                  headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    Authorization: `Bearer ${localStorage.getItem('token')}`,
                  }
                }
              )

              this.importProgress = 70
              this.animatedProgress = 70
              await this.loadData()
              this.importProgress = 90
              this.animatedProgress = 90
              this.resetForm()
              this.importProgress = 100

              // animasi ke 100
              await new Promise(resolve => {
                const interval = setInterval(() => {
                  if (this.animatedProgress >= 100) {
                    clearInterval(interval)
                    resolve()
                  } else {
                    this.animatedProgress += 5
                  }
                }, 30)
              })

              // matikan loading
              this.isLoadingImport = false
              console.log(res.data.message);
              Swal.fire({
                icon: 'success',
                html: `Data pengukuran <b>${this.capitalizeName(nama)}</b> berhasil ditambahkan`,
                buttonsStyling: false,
                customClass: {
                  confirmButton: 'btn btn-primary mx-1',
                  cancelButton: 'btn btn-outline-secondary mx-1'
                }
              })

            }
            break;
          default:
            break;
        }

      } catch (e) {
        this.importProgress = 0
        this.animatedProgress = 0
        this.isLoadingImport = false
        //console.error(e)
        Swal.fire({
          title: 'Error',
          html: e.data?.error || 'Terjadi kesalahan input data',
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })

      }
    },
    // Delete via backend
    async delItem(item) {
      this.transaksi = 'menghapus'
      //console.log('isi item:',item);
      const nama = item.nama_anak || item.nama_perempuan || item.nama_ibu
      let nik = null

      const result = await Swal.fire({
        title: 'Konfirmasi',
        html: `Yakin ingin menghapus data <strong>${this.capitalizeName(nama)}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
          confirmButton: 'btn btn-danger mx-1',
          cancelButton: 'btn btn-secondary mx-1'
        }
      })

      if (!result.isConfirmed) return


      this.isLoadingImport = true
      this.importProgress = 10
      this.animatedProgress = 10
      try {
        switch (this.activeMenu) {
          case 'anak':
            nik = item.nik
            await axios.delete(`${baseURL}/api/children/${nik}`, {
              headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${localStorage.getItem('token')}`,
              }
            })
            break;

          case 'bumil':
            nik = item.nik_ibu
            await axios.delete(`${baseURL}/api/pregnancy/${nik}`, {
              headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${localStorage.getItem('token')}`,
              }
            })
            break;

          case 'catin':
            nik = item.nik
            await axios.delete(`${baseURL}/api/bride/${nik}`, {
              headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${localStorage.getItem('token')}`,
              }
            })
            break;
        }

        this.importProgress = 70
        this.animatedProgress = 70
        await this.loadData()
        this.importProgress = 100

        // animasi ke 100
        await new Promise(resolve => {
          const interval = setInterval(() => {
            if (this.animatedProgress >= 100) {
              clearInterval(interval)
              resolve()
            } else {
              this.animatedProgress += 5
            }
          }, 30)
        })

        // matikan loading
        this.isLoadingImport = false
        //console.log(res.data.message);
        Swal.fire({
          icon: 'success',
          html: `Data <b>${this.capitalizeName(nama)}</b> berhasil dihapus!`,
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-primary mx-1',
            cancelButton: 'btn btn-outline-secondary mx-1'
          }
        })
      } catch (e) {
        this.importProgress = 0
        this.animatedProgress = 0
        this.isLoadingImport = false
        //console.error(e)
        Swal.fire({
          title: 'Error',
          text: e.data?.message || 'Terjadi kesalahan input data',
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })
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

      const nameParts = (file.name || '').split('.')
      const ext = nameParts.length > 1 ? nameParts.pop().toLowerCase() : ''
      if (!this.ACCEPTED_EXT.includes(ext)) {
        return { valid: false, message: 'Format file tidak didukung Hanya .csv' }
      }

      // mime (beberapa browser pakai text/plain)
      //if (this.ACCEPTED_MIME.length && !this.ACCEPTED_MIME.includes(file.type) && file.type !== '') {
        // dimungkinkan file.type kosong di beberapa OS, jadi jangan terlalu strict
        //return { valid: false, message: 'Tipe file tidak valid (MIME mismatch).' }
      //}

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
        Swal.fire({
          title: 'Error',
          text: 'Tidak ada file untuk di-upload.',
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })
        return
      }

      // 2️⃣ Validasi file (OPSI 1)
      const validation = this.validateFile(this.file)
      if (!validation.valid) {
        Swal.fire({
          title: 'Error',
          text: validation.message,
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })
        return
      }

      // 3️⃣ Tentukan endpoint sesuai menu aktif
      let UPLOAD_URL
      console.log('active: ',this.activeMenu,'activity: ',this.aktifitas);

      switch (this.activeMenu) {
        case 'anak':
          switch (this.aktifitas) {
            case 'Kunjungan Posyandu':
              UPLOAD_URL = `${baseURL}/api/children/import_kunjungan`
              break
            case 'Pendampingan Anak':
              UPLOAD_URL = `${baseURL}/api/children/import_pendampingan`
              break
            case 'Intervensi Anak':
              UPLOAD_URL = `${baseURL}/api/children/import_intervensi`
              break
          }
          break

        case 'bumil':
          switch (this.aktifitas) {
            case 'Pendampingan Bumil':
              UPLOAD_URL = `${baseURL}/api/pregnancy/import`
              break
            case 'Intervensi Bumil':
              UPLOAD_URL = `${baseURL}/api/pregnancy/import_intervensi`
              break
          }
          break

        case 'catin':
          UPLOAD_URL = `${baseURL}/api/bride/import`
          break
      }

      if (!UPLOAD_URL) {
        Swal.fire({
          title: 'Error',
          text: 'Endpoint upload tidak ditemukan.',UPLOAD_URL,
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })
        return
      }

      const formData = new FormData()
      formData.append('file', this.file)

      this.transaksi = 'mengunggah'
      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0

      await this.$nextTick()
      this.importProgress = 10
      this.animatedProgress = 10

      try {
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
        // 🔥 pastikan progress berhenti
        this.uploadProgress = 100

        this.importProgress = 50
        this.animatedProgress = 50
        this.formOpen = false
        this.formOpen_bumil = false
        this.formOpen_catin = false
        this.isUploadOpen = !this.isUploadOpen
        this.isUploadOpen_bumil = !this.isUploadOpen_bumil
        this.isUploadOpen_catin = !this.isUploadOpen_catin
        this.removeFile()

        this.importProgress = 70
        this.animatedProgress = 70
        await this.loadData()

        this.importProgress = 100
        // animasi ke 100
        await new Promise(resolve => {
          const interval = setInterval(() => {
            if (this.animatedProgress >= 100) {
              clearInterval(interval)
              resolve()
            } else {
              this.animatedProgress += 5
            }
          }, 30)
        })

        // matikan loading
        this.isLoadingImport = false
        // ✅ sukses
        console.log(res.data.message);
        Swal.fire({
          icon: 'success',
          text: res.data.message || 'Data berhasil diimport!',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-primary mx-1',
            cancelButton: 'btn btn-outline-secondary mx-1'
          }
        })


      } catch (err) {
        const detail = err.response?.data?.detail
        console.log(err);

        const message =
          detail ||
          err.response?.data?.message ||
          'Format CSV tidak valid'

        Swal.fire({
          title: 'Error',
          html: message,
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })
        console.error('Upload error:', err.response?.data)
      } finally {
        this.isLoadingImport = false

        // 🛑 reset total state progress
        this.uploadProgress = 0
        this.importProgress = 0
        this.animatedProgress = 0

        // jika pakai interval / RAF
        if (this.progressTimer) {
          clearInterval(this.progressTimer)
          this.progressTimer = null
        }
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
    async loadFilePreview(file) {
      if (!file) return

      const ext = file.name.split('.').pop().toLowerCase()

      //if (!['csv', 'xlsx', 'xls'].includes(ext)) {
      if (!['csv'].includes(ext)) {
        Swal.fire({
          title: 'Error',
          html: 'Periksa format file. Pastikan berformat CSV',
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })
        //this.fileError = 'Hanya file CSV atau Excel (XLS/XLSX) yang diperbolehkan.'
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
          this.filePreviewTable_bumil = []
          this.filePreviewTable_catin = []
          return
        }

        const delimiter = rawLines[0].includes(';') ? ';' : ','

        let table = rawLines.map(line => line.split(delimiter))

        table = table.map(row =>
          row.length > 10 ? [...row.slice(0, 10), '...'] : row
        )

        switch (this.activeMenu) {
          case 'anak':
            this.filePreviewTable = table
            break;
          case 'bumil':
            this.filePreviewTable_bumil = table
            break;
          case 'catin':
            this.filePreviewTable_catin = table
            break;

          default:
            break;
        }
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
            res = await axios.get(`${baseURL}/api/children`, { headers,params: this.filters });
            payload = res.data.data_anak ?? {};
            this.dataLoad = Array.isArray(payload) ? payload : Object.values(payload);
            break;
          case 'bumil':
            res = await axios.get(`${baseURL}/api/pregnancy`, { headers,params: this.filters });
            payload = res.data?.data || [];
            this.dataLoad = Array.isArray(payload) ? payload : Object.values(payload);
            break;

          case 'catin':
            res = await axios.get(`${baseURL}/api/bride`, { headers,params: this.filters });
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
      this.role = localStorage.getItem('role')

      const currentYear = new Date().getFullYear()
      this.yearOptions = Array.from({ length: 5 }, (_, i) => currentYear - i)

      await this.loadConfigWithCache()

      if (this.role === "Super Admin") {
        await this.loadRegion()
      } else {
        await this.getWilayahUser() // ⬅️ ini WAJIB selesai dulu
      }

      this.generatePeriodeOptions()
      await this.loadData() // ⬅️ baru load data setelah filter siap

      this.handleResize()
      window.addEventListener('resize', this.handleResize)

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
.table-responsive {
  overflow-x: auto;
  scrollbar-width: thick; /* Firefox */
}

/* WebKit */
.table-responsive::-webkit-scrollbar {
  height: 50px !important; /* INI ukuran scrollbar horizontal */
}

.table-responsive::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.table-responsive::-webkit-scrollbar-thumb {
  background: #999;
  border-radius: 10px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
  background: #666;
}


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
