<!-- eslint-disable vue/valid-v-slot -->
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
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar"/>

      <!-- Main Content -->
      <div class="flex-grow-1 d-flex flex-column">

        <!-- Content -->
        <div class="py-4 container-fluid" >

          <!-- Welcome Card -->
          <Welcome />

          <!-- Filter -->
          <!-- <div class="filter-wrapper bg-light rounded shadow-sm p-3 mt-3 container-fluid">
            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <div class="col-md-6">
                <label for="nik" class="form-label">NIK</label>
                <input
                  type="text"
                  v-model="filter.nik_kepala"
                  id="nik_kepala"
                  class="form-control"
                  placeholder="Cari berdasarkan NIK Kepala Keluarga"
                />
              </div>
              <div class="col-md-6">
                <label for="no_kk" class="form-label">No. Kartu Keluarga</label>
                <input
                  type="text"
                  v-model="filter.no_kk"
                  id="no_kk"
                  class="form-control"
                  placeholder="Cari berdasarkan No. Kartu Keluarga"
                />
              </div>

              <div v-if="isFilterOpen" class="row g-3 align-items-end mt-2">
                <div class="col-md-6">
                  <label for="kepala" class="form-label">Nama Kepala Keluarga</label>
                  <input
                    type="text"
                    v-model="advancedFilter.nama_kepala"
                    id="kepala"
                    class="form-control"
                  />
                </div>

                <div class="col-md-3">
                  <label for="rt" class="form-label">RT</label>
                  <input type="number" v-model="advancedFilter.rt" id="rt" class="form-control" />
                </div>

                <div class="col-md-3">
                  <label for="rw" class="form-label">RW</label>
                  <input type="number" v-model="advancedFilter.rw" id="rw" class="form-control" />
                </div>

                <div class="col-md-12 d-flex justify-content-end mt-3">
                  <button type="button" class="btn btn-secondary" @click="resetFilter">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                  </button>
                </div>
              </div>
            </form>

            <div class="text-end mt-2">
              <button type="button" class="btn btn-outline-secondary btn-sm" @click="toggleExpand">
                <i :class="isFilterOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
                {{ isFilterOpen ? 'Tutup Filter Lain' : 'Filter Lain' }}
              </button>
            </div>
          </div> -->

          <div v-if="isUploadOpen" class="card p-3 my-3">
            <div class="d-flex justify-content-between align-item-center">
              <h5>Import File Data Keluarga</h5>
              <button @click="isUploadOpen = !isUploadOpen" class="btn btn-sm btn-outline-danger mb-2">
                X
              </button>
            </div>

            <div class="row g-2">
              <div class="alert alert-success">
                 <ul>
                  <li>Import data untuk data Keluarga</li>
                  <li>Pastikan data yang diimport, berformat csv</li>
                  <li>Pastikan data sudah lengkap sebelum di import</li>
                  <li>Silahkan unduh contoh dengan klik <a href="/example_keluarga.xlsx" download="keluarga.csv">Example</a></li>
                </ul>
              </div>

              <input
                ref="csvFile"
                type="file"
                accept=".csv,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                class="form-control"
                @change="handleFileChange($event)"
              />

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

                <button v-if="file && !uploading" class="btn btn-success btn-sm" @click="handleImport" type="button">
                  <i class="bi bi-upload me-1"></i> Upload
                </button>

                <div v-if="uploading" class="d-flex align-items-center gap-2">
                  <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                  <small class="text-muted">Mengunggah... {{ uploadProgress }}%</small>
                </div>
              </div>
            </div>
          </div>

          <!-- Collapsible Form -->
          <div v-if="isFormOpen" id="formData" class="card shadow-sm my-3">
            <div class="card-body">
              <form class="row g-4">
                <!-- No KK -->
                <div class="col-md-12">
                  <label class="form-label small fw-semibold text-secondary">No KK</label>
                  <input
                    type="text"
                    class="form-control shadow-sm"
                    v-model="form.no_kk"
                    @input="form.no_kk = form.no_kk.replace(/\D/g, '')"
                    maxlength="16"
                    @blur="checkNoKK"
                  />
                </div>

                <!-- Alamat -->
                <div class="col-md-12">
                  <label class="form-label small fw-semibold text-secondary">Alamat</label>
                  <textarea
                    class="form-control shadow-sm"
                    rows="2"
                    v-model="form.alamat"
                    :readonly="isKKChecked"
                  ></textarea>
                </div>

                <!-- Provinsi -->
                <div class="col-md-4">
                  <label class="form-label small fw-semibold text-secondary">Provinsi</label>
                  <template v-if="form.provinsi === '__new__'">
                    <input
                      type="text"
                      class="form-control shadow-sm"
                      v-model="form.provinsi_new"
                      placeholder="Tambah provinsi baru"
                    />
                  </template>
                  <template v-else>
                    <select
                      class="form-select shadow-sm"
                      v-model="form.provinsi"
                      @change="loadKota"
                      :readonly="isKKChecked"
                    >
                      <option value="">Pilih</option>
                      <option v-for="item in provinsiList" :key="item.nama" :value="item.nama">
                        {{ item.nama }}
                      </option>
                      <option value="__new__">+ Tambah baru</option>
                    </select>
                  </template>
                </div>

                <!-- Kota -->
                <div class="col-md-4">
                  <label class="form-label small fw-semibold text-secondary">Kota</label>
                  <template v-if="form.kota === '__new__'">
                    <input
                      type="text"
                      class="form-control shadow-sm"
                      v-model="form.kota_new"
                      placeholder="Tambah kota baru"
                    />
                  </template>
                  <template v-else>
                    <select
                      class="form-select shadow-sm"
                      v-model="form.kota"
                      @change="loadKecamatan"
                      :disabled="isKKChecked || KotaReadonly"
                    >
                      <option value="">Pilih</option>
                      <option v-for="item in kotaList" :key="item.nama" :value="item.nama">
                        {{ item.nama }}
                      </option>
                      <option value="__new__">+ Tambah baru</option>
                    </select>
                  </template>
                </div>

                <!-- Kecamatan -->
                <div class="col-md-4">
                  <label class="form-label small fw-semibold text-secondary">Kecamatan</label>
                  <template v-if="form.kecamatan === '__new__'">
                    <input
                      type="text"
                      class="form-control shadow-sm"
                      v-model="form.kecamatan_new"
                      placeholder="Tambah kecamatan baru"
                    />
                  </template>
                  <template v-else>
                    <select
                      class="form-select shadow-sm"
                      v-model="form.kecamatan"
                      @change="loadKelurahan"
                      :disabled="isKKChecked || KecReadonly"
                    >
                      <option value="">Pilih</option>
                      <option v-for="item in kecamatanList" :key="item.nama" :value="item.nama">
                        {{ item.nama }}
                      </option>
                      <option value="__new__">+ Tambah baru</option>
                    </select>
                  </template>
                </div>

                <!-- Kelurahan -->
                <div class="col-md-4">
                  <label class="form-label small fw-semibold text-secondary">Kelurahan</label>
                  <template v-if="form.kelurahan === '__new__'">
                    <input
                      type="text"
                      class="form-control shadow-sm"
                      v-model="form.kelurahan_new"
                      placeholder="Tambah kelurahan baru"
                    />
                  </template>
                  <template v-else>
                    <select
                      class="form-select shadow-sm"
                      v-model="form.kelurahan"
                      :disabled="isKKChecked || KelReadonly"
                    >
                      <option value="">Pilih</option>
                      <option v-for="item in kelurahanList" :key="item.nama" :value="item.nama">
                        {{ item.nama }}
                      </option>
                      <option value="__new__">+ Tambah baru</option>
                    </select>
                  </template>
                </div>

                <!-- RT & RW -->
                <div class="col-md-4">
                  <label class="form-label small fw-semibold text-secondary">RT</label>
                  <input
                    type="number"
                    min="0"
                    class="form-control shadow-sm"
                    v-model="form.rt"
                    :readonly="isKKChecked"
                  />
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-semibold text-secondary">RW</label>
                  <input
                    type="number"
                    min="0"
                    class="form-control shadow-sm"
                    v-model="form.rw"
                    :readonly="isKKChecked"
                  />
                </div>

                <!-- Nama -->
                <div class="col-md-12">
                  <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                  <input type="text" class="form-control shadow-sm" v-model="form.nama" />
                </div>

                <!-- NIK -->
                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-secondary">NIK</label>
                  <input
                    type="text"
                    class="form-control shadow-sm"
                    v-model="form.nik"
                    maxlength="16"
                    @input="form.nik = form.nik.replace(/\D/g, '')"
                  />
                </div>

                <!-- Gender -->
                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-secondary">Jenis Kelamin</label>
                  <select class="form-select shadow-sm" v-model="form.gender">
                    <option value="">L/P</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                  </select>
                </div>

                <!-- Tempat & Tanggal Lahir -->
                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-secondary">Tempat Lahir</label>
                  <input type="text" class="form-control shadow-sm" v-model="form.tempat_lahir" />
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-secondary">Tanggal Lahir</label>
                  <input type="date" class="form-control shadow-sm" v-model="form.tgl_lahir" />
                </div>

                <!-- Pendidikan -->
                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-secondary">Pendidikan</label>
                  <select class="form-select shadow-sm" v-model="form.pendidikan">
                    <option value="">Pendidikan</option>
                    <option value="TK">TK</option>
                    <option value="SD">SD</option>
                    <option value="SMP">SMP</option>
                    <option value="SMA/SMK">SMA/sederajat</option>
                    <option value="SARJANA">Sarjana</option>
                    <option value="MAGISTER">Magister</option>
                    <option value="DOKTORAL">Doktoral</option>
                    <option value="PROFESSOR">Professor</option>
                  </select>
                </div>

                <!-- Pekerjaan -->
                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-secondary">Pekerjaan</label>
                  <input type="text" class="form-control shadow-sm" v-model="form.pekerjaan" />
                </div>

                <!-- Status Hubungan -->
                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-secondary">Status Hubungan</label>
                  <select class="form-select shadow-sm" v-model="form.status_hubungan">
                    <option value="">Pilih</option>
                    <option value="Kepala Keluarga">Kepala Keluarga</option>
                    <option value="Istri">Istri</option>
                    <option value="Anak">Anak</option>
                    <option value="Famili Lain">Famili Lain</option>
                  </select>
                </div>

                <!-- Agama -->
                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-secondary">Agama</label>
                  <select class="form-select shadow-sm" v-model="form.agama">
                    <option value="">Pilih</option>
                    <option value="Islam">Islam</option>
                    <option value="Kristen">Kristen</option>
                    <option value="Katolik">Katolik</option>
                    <option value="Hindu">Hindu</option>
                    <option value="Budha">Budha</option>
                    <option value="Konghucu">Konghucu</option>
                  </select>
                </div>

                <!-- Status Perkawinan -->
                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-secondary">Status Perkawinan</label>
                  <select class="form-select shadow-sm" v-model="form.status_perkawinan">
                    <option value="">Pilih</option>
                    <option value="Belum Kawin">Belum Kawin</option>
                    <option value="Kawin">Kawin</option>
                    <option value="Cerai Hidup">Cerai Hidup</option>
                    <option value="Cerai Mati">Cerai Mati</option>
                  </select>
                </div>

                <!-- Kewarganegaraan -->
                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-secondary">Kewarganegaraan</label>
                  <input
                    type="text"
                    class="form-control shadow-sm"
                    v-model="form.kewarganegaraan"
                    placeholder="Contoh: WNI"
                  />
                </div>
              </form>
            </div>

            <!-- Actions -->
            <div class="card-footer bg-white d-flex justify-content-between">
              <button
                class="btn btn-light border rounded-pill px-4"
                @click="resetForm"
              >
                <i class="bi bi-x-circle me-2"></i> Batal
              </button>
              <button
                class="btn btn-success rounded-pill px-4"
                @click="modalMode === 'add' ? saveData() : updateData()"
              >
                <i class="bi bi-save me-2"></i> {{ modalMode === 'add' ? 'Simpan' : 'Ubah' }}
              </button>
            </div>
          </div>

          <div class="card shadow-sm">
            <div class="card-body">
              <!-- Search + Button -->
              <div class="d-flex align-items-center justify-content-end gap-2">

                <input
                  type="text"
                  v-model="filter.nik_kepala"
                  id="nik_kepala"
                  class="form-control w-25"
                  placeholder="Cari NIK atau No. KK"
                />
                <!-- <input type="text" class="form-control form-control-sm" style="width: 220px;"
                  placeholder="Ketik NIK atau Nama" v-model="searchQuery_kunAn"> -->

                <button
                  type="button"
                  class="btn btn-primary mx-2"
                  @click="toggleExpandForm"
                >
                  <i :class="isFormOpen ? 'bi bi-dash-square' : 'bi bi-plus-square'"></i>
                  {{ isFormOpen ? 'Tutup Form' : 'Tambah Data' }}
                </button>
                <button class="btn btn-outline-success" @click="isUploadOpen = !isUploadOpen">
                  <i class="bi bi-filetype-csv"></i> Import Data Keluarga
                </button>
              </div>
              <!-- Table -->
              <div class="mt-3">

                <!-- Search + Row Per Page -->
                <easy-data-table
                  :headers="headers"
                  :items="filteredFamily"
                  :sortable="true"
                  :search-value="searchQuery"
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
                  <template #item-action="items">
                    <div class="action-wrapper d-flex gap-1 m-1 text-center">
                      <button
                        class="btn btn-primary m-2"
                        @click="openFamilyModal(id)"
                        style="font-size: small;"
                      >
                        <i class="fa fa-eye"></i>
                      </button>
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

          <!-- Table -->
          <!-- <div class="container-fluid"> -->
            <!-- <div v-if="isPendingOpen" id="dataPending" class="card modern-card mt-4">
              <div class="card-body bg-additional rounded">
                <div class="d-flex justify-content-between">
                  <h5 class="fw-bold mb-2 text-white">Data Pending</h5>
                  <button @click="toggleExpandPending" class="btn-close"></button>
                </div>

                <EasyDataTable
                  :headers="headers_pending"
                  :items="familyPending"
                >
                  <template #item-action="{ id,tipe }">
                    <button
                      class="btn btn-secondary m-2"
                      @click="updateFamily(id,tipe)"
                      style="font-size: small;"
                    >
                      <i class="fa fa-pen"></i>
                    </button>
                  </template>
                </EasyDataTable>
              </div>
            </div> -->
            <!-- <div class="card modern-card mt-4">
              <div class="card-body">
                <div class="table-responsive">
                  <h5 class="fw-bold mb-2">Data Keluarga</h5>
                  <EasyDataTable
                    :headers="headers"
                    :items="filteredFamily"
                  >
                  <template #item-action="{ id }">
                    <button
                      class="btn btn-primary m-2"
                      @click="openFamilyModal(id)"
                      style="font-size: small;"
                    >
                      <i class="fa fa-eye"></i>
                    </button>
                  </template>
                  </EasyDataTable>
                </div>
              </div>
            </div> -->
          <!-- </div> -->
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
      >
        <div class="modal-header text-primary bg-light border-0 rounded-top-4">
          <h5 class="modal-title">Import File Data Keluarga</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning p-2">
            <ul>
              <li>Import data untuk data Keluarga</li>
              <li>Pastikan data yang diimport, berformat csv</li>
              <li>Pastikan data sudah lengkap sebelum di import</li>
              <li>Silahkan unduh contoh dengan klik <a href="/example_keluarga.xlsx" download="keluarga.csv">Example</a></li>
            </ul>
          </div>

        </div>
        <div class="modal-footer border-0 d-flex justify-content-between">
          <button class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">
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
      >
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
          <p class="mb-0">Data keluarga berhasil disimpan.</p>
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
      <div
        class="modal-content border-0 shadow-lg rounded-4"
      >
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
          <p class="mb-0">Data keluarga berhasil disimpan.</p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">
            OK
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal KK -->
  <div class="modal fade ios-modal" id="kkModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content rounded-3 shadow-lg border-0">
        <!-- Header -->
        <div class="modal-header fw-semibold border-0 bg-light sticky-top rounded-top-3 text-center">
          <button
            class="btn btn-secondary btn-close"
            @click="closeModal('kkModal')"
          >
          </button>
        </div>

        <!-- Body -->
        <div class="modal-body px-4 py-3">
          <h2 class="mb-0 fw-bold text-center">
            DETAIL KELUARGA
          </h2>
          <p class="mt-0 mb-3 text-muted text-center">
            No. {{ selectedFamily?.no_kk }}
          </p>
          <div class="row mt-4">
            <div class="col-6">
              <div class="d-flex">
                <span class="fw-bold me-2" style="min-width: 90px;">Alamat</span>
                <span>: {{ selectedFamily?.alamat }}</span>
              </div>
              <div class="d-flex">
                <span class="fw-bold me-2" style="min-width: 90px;">RT/RW</span>
                <span>: {{ selectedFamily?.rt }}/{{ selectedFamily?.rw }}</span>
              </div>
              <div class="d-flex">
                <span class="fw-bold me-2" style="min-width: 90px;">Kelurahan</span>
                <span>: {{ selectedFamily?.wilayah?.kelurahan }}</span>
              </div>
            </div>

            <div class="col-6">
              <div class="d-flex">
                <span class="fw-bold me-2" style="min-width: 90px;">Kecamatan</span>
                <span>: {{ selectedFamily?.wilayah?.kecamatan }}</span>
              </div>
              <div class="d-flex">
                <span class="fw-bold me-2" style="min-width: 90px;">Kot/Kab</span>
                <span>: {{ selectedFamily?.wilayah?.kota }}</span>
              </div>
              <div class="d-flex">
                <span class="fw-bold me-2" style="min-width: 90px;">Provinsi</span>
                <span>: {{ selectedFamily?.wilayah?.provinsi }}</span>
              </div>
            </div>
          </div>

          <div class="row mt-2">
            <div class="table-responsive mt-3">
              <table class="table table-sm align-middle border">
                <thead class="table-light text-center">
                  <tr>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>TTL</th>
                    <th>Jenis Kelamin</th>
                    <th>Pendidikan</th>
                    <th>Pekerjaan</th>
                    <th>Status Hubungan</th>
                  </tr>
                </thead>
                <tbody class="small">
                  <tr v-for="a in selectedFamily?.anggota" :key="a.id">
                    <td>{{ a.nik }}</td>
                    <td>{{ a.nama }}</td>
                    <td>{{ a.tempat_lahir }}, {{ new Date(a.tanggal_lahir).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) }}</td>
                    <td class="text-center">{{ a.jenis_kelamin }}</td>
                    <td>{{ a.pendidikan }}</td>
                    <td>{{ a.pekerjaan }}</td>
                    <td>{{ a.status_hubungan }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
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
import EasyDataTable from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'
import { Modal } from 'bootstrap'
import axios from 'axios'
import Welcome from '@/components/Welcome.vue'
import Swal from 'sweetalert2'

// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Family',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, EasyDataTable, Welcome },
  data() {
    return {
      isUploadOpen: false,
      file: null,
      fileName: '',
      fileSize: 0,
      filePreviewLines: '',
      fileError: '',
      uploading: false,
      uploadProgress: 0,
      filePreviewTable: [],
      currentPage: 1,
      perPage: 10,
      perPageOptions: [5, 10, 25, 50],
      searchQuery:"",
      progressLevel:0,
      configCacheKey: 'site_config_cache',
      // required
      ACCEPTED_EXT: ['csv'],
      MAX_FILE_SIZE: 5 * 1024 * 1024, // 5MB
      isLoading: true,
      isCollapsed: false,
      username: '',
      today: '',
      thisMonth:'',
      kelurahan: '',
      logoSrc: '/cipayung.png',
      logoLoaded: true,
      windowWidth: window.innerWidth,
      // -------------------
      KotaReadonly: true,
      KecReadonly: true,
      KelReadonly: true,
      modalMode: "add",
      selectedFamily: null,
      isKKChecked: false,
      isFilterOpen: false,
      isFormOpen: false,
      isPendingOpen: false,
      showAlert: false,
      isLoadingImport: false,
      importProgress: 0,
      animatedProgress: 0,
      currentRow: 0,
      totalRows: 1,
      provinsiList: [],
      kotaList: [],
      kecamatanList: [],
      kelurahanList: [],
      form: {
        id: null,
        nik: '',
        no_kk: '',
        nama: '',
        alamat: '',
        rt: '',
        rw: '',
        kelurahan: '',
        kecamatan: '',
        kota: '',
        provinsi: '',
        kelurahan_new: '',
        kecamatan_new: '',
        kota_new: '',
        provinsi_new: '',
        tempat_lahir: '',
        gender: '',
        tgl_lahir: '',
        pendidikan: '',
        pekerjaan: '',
        status_hubungan: '',
        agama: '',
        status_perkawinan: '',
        kewarganegaraan: ''
      },
      family: [],
      familyPending: [],
      headers: [
        { text: 'ID', value: 'id' },
        { text: 'No KK', value: 'no_kk' },
        { text: 'NIK', value: 'nik_kepala' },
        { text: 'Kepala Keluarga', value: 'nama_kepala' },
        { text: 'RT', value: 'rt' },
        { text: 'RW', value: 'rw' },
        { text: 'Tempat, Tanggal Lahir', value: 'tgl_lahir' },
        { text: 'Pendidikan', value: 'pendidikan' },
        { text: 'Jumlah Anggota', value: 'jml_anggota' },
        { text: 'Action', value: 'action' },
      ],
      /*headers_pending: [
        { text: 'ID', value: 'id' },
        { text: 'No KK', value: 'no_kk' },
        { text: 'NIK', value: 'nik' },
        { text: 'Nama', value: 'nama' },
        { text: 'RT', value: 'rt' },
        { text: 'RW', value: 'rw' },
        { text: 'Tempat, Tanggal Lahir', value: 'tgl_lahir' },
        { text: 'Pendidikan', value: 'pendidikan' },
        { text: 'Tipe Pending', value: 'tipe' },
        { text: 'Action', value: 'action' },
      ],*/
      filter: {
        nik_kepala: '',
        no_kk: '',
      },
      advancedFilter: {
        nama_kepala: '',
        rt: '',
        rw: '',
      },
      appliedFilter: {},
    }
  },
  computed: {
    filteredFamily() {
      if (!this.family) return [];

      const arr = Array.isArray(this.family)
        ? this.family
        : Object.values(this.family);

      const q = (this.searchQuery ?? "").toString().toLowerCase().trim();

      return arr.filter(item => {
        const nik = (item.nik_kepala ?? "").toString().toLowerCase();
        const no_kk = (item.no_kk ?? "").toString().toLowerCase();

        return nik.includes(q) || no_kk.includes(q);
      });
    },
    pendingCount() {
      return this.familyPending.length
    },
  },
  methods: {
    handleFileChange(e) {
      const file = e.target.files[0]
      this.loadFilePreview(file)
    },
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
    /* async getWilayahUser() {
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
        //await this.fetchPosyanduByWilayah(this.id_wilayah)
      } catch (error) {
        console.error('Gagal ambil data wilayah user:', error)
        this.kelurahan = '-'
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
    toggleExpand() {
      this.isFilterOpen = !this.isFilterOpen
    },
    toggleExpandForm() {
      this.isFormOpen = !this.isFormOpen
    },
    /* toggleExpandPending() {
      this.isPendingOpen = !this.isPendingOpen
    }, */
    async checkNoKK() {
      if (!this.form.no_kk) {
        this.isKKChecked = false;
        return;
      }

      try {
        const res = await axios.get(`${baseURL}/api/family/check`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          },
          params: { no_kk: this.form.no_kk },
        });

        if (res.data.exists) {
          const data = res.data.keluarga;

          // isi alamat dan rt/rw dulu
          this.form.alamat = data.alamat;
          this.form.rt = data.rt;
          this.form.rw = data.rw;

          // provinsi
          if (data.provinsi) {
            this.form.provinsi = data.provinsi;
            await this.loadKota();
          }

          // kota
          if (data.kota) {
            this.form.kota = data.kota;
            await this.loadKecamatan();
          }

          // kecamatan
          if (data.kecamatan) {
            this.form.kecamatan = data.kecamatan;
            await this.loadKelurahan();
          }

          // kelurahan
          if (data.kelurahan) {
            this.form.kelurahan = data.kelurahan;
          }

          this.isKKChecked = true;
        } else {
          this.isKKChecked = false;
        }
      } catch (e) {
        console.error("Error check KK:", e);
        this.isKKChecked = false;
      }
    },
    async loadProvinsi() {
      try {
        const res = await axios.get(`${baseURL}/api/region/provinsi`);
        //console.log("Provinsi API response:", res.data); // debug

        // isi list dari API
        this.provinsiList = res.data;

      } catch (err) {
        console.error("Error load provinsi:", err);
      }
    },
    async loadKota() {
      this.KotaReadonly = false
      if (this.form.provinsi && this.form.provinsi !== "__new__") {
        const res = await axios.get(`${baseURL}/api/region/kota`, {
          params: { provinsi: this.form.provinsi }
        });
        this.kotaList = res.data;
        this.kecamatanList = [];
        this.kelurahanList = [];
        this.form.kota = "";
        this.form.kecamatan = "";
        this.form.kelurahan = "";
      }
    },
    async loadKecamatan() {
      this.KecReadonly = false
      if (this.form.kota && this.form.kota !== "__new__") {
        const res = await axios.get(`${baseURL}/api/region/kecamatan`, {
          params: { provinsi: this.form.provinsi, kota: this.form.kota }
        });
        this.kecamatanList = res.data;
        this.kelurahanList = [];
        this.form.kecamatan = "";
        this.form.kelurahan = "";
      }
    },
    async loadKelurahan() {
      this.KelReadonly = false
      if (this.form.kecamatan && this.form.kecamatan !== "__new__") {
        const res = await axios.get(`${baseURL}/api/region/kelurahan`, {
          params: {
            provinsi: this.form.provinsi,
            kota: this.form.kota,
            kecamatan: this.form.kecamatan
          }
        });
        this.kelurahanList = res.data;
        this.form.kelurahan = "";
      }
    },
    async loadFamily() {
      try {
        const res = await axios.get(`${baseURL}/api/family`,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        this.family = res.data
        console.log('family:',this.family);
      } catch (e) {
        console.error('Gagal ambil data:', e)
      }
    },
    closeModal(id) {
      const el = document.getElementById(id)
      if (el) {
        const instance = Modal.getOrCreateInstance(el)
        instance.hide()
      }
      setTimeout(() => {
        document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove())
        document.body.classList.remove('modal-open')
        document.body.style.removeProperty('overflow')
        document.body.style.removeProperty('padding-right')
      }, 300)
    },
    resetForm() {
      this.form = {
        id: null,
        no_kk: "",
        alamat: "",
        provinsi: "",
        kota: "",
        kecamatan: "",
        kelurahan: "",
        rt: "",
        rw: "",
        nama: "",
        nik: "",
        gender: "",
        tempat_lahir: "",
        tgl_lahir: "",
        pendidikan: "",
        pekerjaan: "",
        status_hubungan: "",
        agama: "",
        status_perkawinan: "",
        kewarganegaraan: "",
      },
      this.isFormOpen = false
    },
    openTambah() {
      this.modalMode = "add"
      this.form = {} // reset form
      this.isFormOpen = true
    },
    async updateFamily(id,tipe) {
      this.modalMode = "update";
      this.isFormOpen = true
      try {
        const res = await axios.get(`${baseURL}/api/family/${id}/pending?tipe=${tipe}`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        const data = res.data;

        // mapping ke form sesuai struktur
        this.form = {
          tipe: data.tipe,
          id: data.id,
          no_kk: data.no_kk,
          alamat: data.alamat,
          provinsi: data.provinsi,
          kota: data.kota,
          kecamatan: data.kecamatan,
          kelurahan: data.kelurahan,
          rt: data.rt,
          rw: data.rw,
          // ambil anggota pertama (atau filter kepala keluarga)
          ...(
            data.anggota && data.anggota.length > 0
              ? {
                  anggota_id: data.anggota[0].id,
                  nik: data.anggota[0].nik,
                  nama: data.anggota[0].nama,
                  tempat_lahir: data.anggota[0].tempat_lahir,
                  tgl_lahir: data.anggota[0].tanggal_lahir,
                  gender: data.anggota[0].jenis_kelamin,
                  pendidikan: data.anggota[0].pendidikan,
                  pekerjaan: data.anggota[0].pekerjaan,
                  status_hubungan: data.anggota[0].status_hubungan,
                  agama: data.anggota[0].agama,
                  status_perkawinan: data.anggota[0].status_perkawinan,
                  kewarganegaraan: data.anggota[0].kewarganegaraan,
                }
              : {}
          )
        };

        // pastikan listnya ke-load dulu sebelum set value
        await this.loadKota()
        this.form.kota = data.kota

        await this.loadKecamatan()
        this.form.kecamatan = data.kecamatan

        await this.loadKelurahan()
        this.form.kelurahan = data.kelurahan

        this.isFormOpen = true // 👈 bukan modal.show()
      } catch (err) {
        console.error("Gagal load data keluarga:", err);
      }
    },
    async updateData() {
      try {
       console.log("Payload sebelum dikirim:", this.form) // 👈 cek dulu isi form

        await axios.put(
          `${baseURL}/api/family/${this.form.id}`,
          this.form, // data body
          {
            headers: {
              Accept: 'application/json',
              Authorization: `Bearer ${localStorage.getItem('token')}`
            }
          }
        )

        //alert("Data berhasil diupdate")
        this.isFormOpen = false
        this.isPendingOpen = false
        //this.getPendingData()
        this.loadFamily()
        const modal = new Modal(document.getElementById("successModal"))
        modal.show()
      } catch (err) {
        console.error("Gagal update:", err)
      }
    },
    async saveData() {

      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0

      try {
        this.form.provinsi = this.form.provinsi === "__new__" ? this.form.provinsi_new : this.form.provinsi;
        this.form.kota = this.form.kota === "__new__" ? this.form.kota_new : this.form.kota;
        this.form.kecamatan = this.form.kecamatan === "__new__" ? this.form.kecamatan_new : this.form.kecamatan;
        this.form.kelurahan= this.form.kelurahan === "__new__" ? this.form.kelurahan_new : this.form.kelurahan;

        //console.log("Payload sebelum dikirim:", this.form) // 👈 cek dulu isi form

        // simpan ke backend
        await axios.post(`${baseURL}/api/family`, this.form,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        // refresh table
        //await this.getPendingData()
        await this.loadFamily()

        // reset form
        this.form = {
          nik: '',
          no_kk: '',
          kepala: '',
          alamat: '',
          rt: '',
          rw: '',
          kelurahan: '',
          kecamatan: '',
          kota: '',
          tempat_lahir: '',
          gender: '',
          tgl_lahir: '',
          pendidikan: '',
          pekerjaan: '',
        }

        this.isFormOpen = false
        setTimeout(() => (this.showAlert = false), 3000)
      } catch (e) {
        console.error('Gagal simpan data:', e)
      } finally {
        this.isLoadingImport = false
      }
    },
    applyFilter() {
      this.appliedFilter = { ...this.advancedFilter }
    },
    resetFilter() {
      this.filter = { nik_kepala: '', no_kk: '' }
      this.advancedFilter = { nama_kepala: '', rt: '', rw: '' }
      this.appliedFilter = {}
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    async openFamilyModal(id) {
      try {
        // panggil API detail by id
        const res = await axios.get(`${baseURL}/api/family/detail/${id}`,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        this.selectedFamily = res.data
        //console.log(res.data);

        // buka modal
        const modalEl = document.getElementById('kkModal')
        const instance = Modal.getOrCreateInstance(modalEl)
        instance.show()
      } catch (e) {
        console.error("Gagal ambil detail keluarga:", e)
      }
    },
    validateFile(file) {
      if (!file) {
        return { valid: false, message: 'File tidak ditemukan.' }
      }

      const nameParts = (file.name || '').split('.')
      const ext = nameParts.length > 1 ? nameParts.pop().toLowerCase() : ''

      if (!this.ACCEPTED_EXT.includes(ext)) {
        return { valid: false, message: 'Format file tidak didukung. Hanya .csv' }
      }

      if (file.size > this.MAX_FILE_SIZE) {
        return {
          valid: false,
          message: `Ukuran file terlalu besar. Maks ${this.humanFileSize(this.MAX_FILE_SIZE)}.`
        }
      }

      return { valid: true }
    },
    /* async uploadCSV() {
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
        //this.showSuccess(res.data.message || 'Data berhasil diimport!')

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
    }, */
    async handleImport() {
      const file = this.$refs.csvFile.files[0];
      if (!file) {
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
        return;
      }

      // 2️⃣ Validasi file (OPSI 1)
      const validation = this.validateFile(file)
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

      const formData = new FormData();
      formData.append("file", file);

      this.isLoadingImport = true;
      this.importProgress = 0;
      this.animatedProgress = 0;

      try {
        const res = await axios.post(`${baseURL}/api/family/import`, formData, {
          headers: {
            "Content-Type": "multipart/form-data",
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          },
          onUploadProgress: (progressEvent) => {
            if (progressEvent.total) {
              this.importProgress = Math.round(
                (progressEvent.loaded * 100) / progressEvent.total
              );
              this.animatedProgress = this.importProgress;
            }
          },
        });

        this.closeModal("modalImport");
        //this.importProgress = 50
        this.animatedProgress = 50
        this.animatedProgress = 70
        await this.loadFamily();

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

        // refresh data keluarga

        //await this.getPendingData();

        //this.closeModal("modalImport");
        //this.showAlert = true;
        //setTimeout(() => (this.showAlert = false), 3000);
      } catch (err) {
        this.closeModal("modalImport");
        this.isLoadingImport = false

        const detail = err.response?.data?.detail
        console.log(err);

        const message =
          detail ||
          err.response?.data?.message ||
          'Gagal mengunggah data keluarga'

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
        console.error("Gagal import:", err.response?.data || err);
        //alert("Gagal import data!");
      } finally {
        this.isLoadingImport = false;
      }
    },
    /* async getPendingData() {
      try {
        const res = await axios.get(`${baseURL}/api/family/pending`,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        this.familyPending = res.data
        //console.log("Pending data:", this.familyPending)
      } catch (err) {
        console.error("Gagal fetch pending data:", err)
      }
    }, */
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
      await Promise.all([
        this.loadConfigWithCache(),
        this.loadFamily(),
        this.loadProvinsi(),
        //this.getPendingData(),
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
/* Gradient Banner */
.family-banner {
  background: linear-gradient(90deg, var(--bs-primary), var(--bs-secondary));
  border-radius: 0 0 1rem 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.filter-wrapper {
  position: relative; /* biar ikut alur layout */
  z-index: 0; /* pastikan di bawah sidebar */
  border-radius: 0.75rem;
}
/* Hilangkan garis pemisah antara sidebar dan content */
.flex-grow-1 {
  border-left: none !important;
  background-color: #f9f9fb;
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

</style>
