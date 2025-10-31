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
          <Welcome />

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
              <!-- === Filter Utama === -->
              <div class="col-md-3 position-relative">
                <label class="form-label">Underweight (BB/U)</label>
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    style="max-width: 100%;"
                  >
                    <span v-if="filters.bbu.length === 0" class="text-muted">Pilih Underweight</span>
                    <span v-else class="selected-text">{{ filters.bbu.join(', ') }}</span>
                  </button>

                  <ul class="dropdown-menu w-100 p-2" style="max-height: 260px; overflow-y: auto;">
                    <li
                      v-for="item in bbuOptions"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="'chk-' + item"
                        :value="item"
                        v-model="filters.bbu"
                      />
                      <label class="form-check-label w-100" :for="'chk-' + item">{{ item }}</label>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li class="d-flex justify-content-between px-2">
                      <button class="btn btn-sm btn-outline-primary fw-semibold" type="button" @click="selectAll_bbu">
                        Pilih Semua
                      </button>
                      <button class="btn btn-sm btn-outline-secondary fw-semibold" type="button" @click="clearAll_bbu">
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="col-md-3">
                <label class="form-label">Stunting (TB/U)</label>
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    style="max-width: 100%;"
                  >
                    <span v-if="filters.tbu.length === 0" class="text-muted">Pilih Stunting</span>
                    <span v-else class="selected-text">{{ filters.tbu.join(', ') }}</span>
                  </button>

                  <ul class="dropdown-menu w-100 p-2" style="max-height: 260px; overflow-y: auto;">
                    <li
                      v-for="item in tbuOptions"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="'chk-' + item"
                        :value="item"
                        v-model="filters.tbu"
                      />
                      <label class="form-check-label w-100" :for="'chk-' + item">{{ item }}</label>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li class="d-flex justify-content-between px-2">
                      <button class="btn btn-sm btn-outline-primary fw-semibold" type="button" @click="selectAll_tbu">
                        Pilih Semua
                      </button>
                      <button class="btn btn-sm btn-outline-secondary fw-semibold" type="button" @click="clearAll_tbu">
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="col-md-3">
                <label class="form-label">Wasting (BB/TB)</label>
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    style="max-width: 100%;"
                  >
                    <span v-if="filters.bbtb.length === 0" class="text-muted">Pilih Wasting</span>
                    <span v-else class="selected-text">{{ filters.bbtb.join(', ') }}</span>
                  </button>

                  <ul class="dropdown-menu w-100 p-2" style="max-height: 260px; overflow-y: auto;">
                    <li
                      v-for="item in bbtbOptions"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="'chk-' + item"
                        :value="item"
                        v-model="filters.bbtb"
                      />
                      <label class="form-check-label w-100" :for="'chk-' + item">{{ item }}</label>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li class="d-flex justify-content-between px-2">
                      <button class="btn btn-sm btn-outline-primary fw-semibold" type="button" @click="selectAll_bbtb">
                        Pilih Semua
                      </button>
                      <button class="btn btn-sm btn-outline-secondary fw-semibold" type="button" @click="clearAll_bbtb">
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="col-md-3">
                <label class="form-label">BB Stagnan</label>
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    style="max-width: 100%;"
                  >
                    <span v-if="filters.stagnan.length === 0" class="text-muted">Pilih BB Stagnan</span>
                    <span v-else class="selected-text">{{ filters.stagnan.join(', ') }}</span>
                  </button>

                  <ul class="dropdown-menu w-100 p-2" style="max-height: 260px; overflow-y: auto;">
                    <li
                      v-for="item in stagnanOptions"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="'chk-' + item"
                        :value="item"
                        v-model="filters.stagnan"
                      />
                      <label class="form-check-label w-100" :for="'chk-' + item">{{ item }}</label>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li class="d-flex justify-content-between px-2">
                      <button class="btn btn-sm btn-outline-primary fw-semibold" type="button" @click="selectAll_stagnan">
                        Pilih Semua
                      </button>
                      <button class="btn btn-sm btn-outline-secondary fw-semibold" type="button" @click="clearAll_stagnan">
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Tombol Expand/Collapse -->
              <div class="col-md-12 d-flex justify-content-end">
                <button
                  type="button"
                  class="btn btn-outline-secondary fw-semibold"
                  @click="showAdvanced = !showAdvanced"
                >
                  <i :class="showAdvanced ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
                  {{ showAdvanced ? 'Tutup Filter Lain' : 'Filter Lain' }}
                </button>
              </div>

              <!-- === Filter Lanjutan (Collapsible) === -->
              <transition name="fade">
                <div v-if="showAdvanced" class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label">Posyandu</label>
                    <select
                      v-model="filters.posyandu"
                      class="form-select text-muted"
                      @change="handlePosyanduChange"
                    >
                      <option value="">All</option>
                      <option v-for="item in posyanduList" :key="item.id" :value="item.nama_posyandu">
                        {{ item.nama_posyandu }}
                      </option>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">RW</label>
                    <select
                      v-model="filters.rw"
                      class="form-select text-muted"
                      :disabled="rwReadonly"
                    >
                      <option value="">All</option>
                      <option v-for="rw in rwList" :key="rw" :value="rw">{{ rw }}</option>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">RT</label>
                    <select
                      v-model="filters.rt"
                      class="form-select text-muted"
                      :disabled="rtReadonly"
                    >
                      <option value="">All</option>
                      <option v-for="rt in rtList" :key="rt" :value="rt">{{ rt }}</option>
                    </select>
                  </div>

                  <div class="col-md-4 position-relative">
                    <label class="form-label">Intervensi</label>
                    <div class="dropdown w-100">
                      <button
                        class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        style="max-width: 100%;"
                      >
                        <span v-if="filters.intervensi.length === 0" class="text-muted">Pilih Intervensi</span>
                        <span v-else class="selected-text">{{ filters.intervensi.join(', ') }}</span>
                      </button>

                      <ul class="dropdown-menu w-100 p-2" style="max-height: 260px; overflow-y: auto;">
                        <li
                          v-for="item in intervensiOptions"
                          :key="item"
                          class="dropdown-item d-flex align-items-center"
                        >
                          <input
                            type="checkbox"
                            class="form-check-input me-2"
                            :id="'chk-' + item"
                            :value="item"
                            v-model="filters.intervensi"
                          />
                          <label class="form-check-label w-100" :for="'chk-' + item">{{ item }}</label>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li class="d-flex justify-content-between px-2">
                          <button class="btn btn-sm btn-outline-primary fw-semibold" type="button" @click="selectAll_intervensi">
                            Pilih Semua
                          </button>
                          <button class="btn btn-sm btn-outline-secondary fw-semibold" type="button" @click="clearAll_intervensi">
                            Hapus Semua
                          </button>
                        </li>
                      </ul>
                    </div>
                  </div>


                  <div class="col-md-8 text-center">
                    <label class="form-label">Periode</label>
                    <div class="d-flex justify-content-between gap-2">
                      <select v-model="filters.periodeAwal" class="form-select text-muted">
                        <option value="">All</option>
                        <option v-for="periode in periodeOptions" :key="periode" :value="periode">
                          {{ periode }}
                        </option>
                      </select>
                      <select v-model="filters.periodeAkhir" class="form-select text-muted">
                        <option value="">All</option>
                        <option v-for="periode in periodeOptions" :key="periode" :value="periode">
                          {{ periode }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>
              </transition>

              <!-- === Tombol Aksi (Selalu Tampil) === -->
              <div class="col-md-12 d-flex justify-content-between mt-3">
                <button type="submit" class="btn btn-gradient fw-semibold">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>
                <button type="button" class="btn btn-secondary fw-semibold" @click="resetFilter">
                  <i class="bi bi-arrow-clockwise me-1"></i> Reset
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
              <div class="col-xl-10 col-sm-12">
                <div class="row justify-content-center">
                  <div
                    v-for="(item, index) in gizi"
                    :key="index"
                    class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-12 mb-3"
                  >
                    <div
                      class="card shadow-sm border-0 rounded-3 overflow-hidden"
                      :class="`border-start border-4 border-${item.color}`"
                    >
                      <div class="card-header">
                        <h6 class="fw-bold mb-1">{{ item.title }}</h6>
                      </div>
                      <div class="card-body py-3 d-flex justify-content-between align-items-center">
                        <h3 class="fw-bold mb-0" :class="`text-${item.color}`">{{ item.value }}</h3>
                        <p class="mb-0" :class="`text-${item.color}`">{{ item.percent }}</p>
                      </div>

                    </div>
                  </div>
                </div>
              </div>

              <!-- TOTAL ANAK -->
              <div class="col-xl-2 col-sm-12">
                <div class="card text-center shadow-sm border p-2 h-100 d-flex flex-column justify-content-center">
                  <h6 class="text-muted fw-bold">Total Anak Balita</h6>
                  <div class="flex-grow-1 d-flex flex-column justify-content-center">
                    <h1 class="fw-bold text-success mb-0">{{totalAnak}}</h1>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Table and detail Section -->
          <div class="container-fluid mt-4">
            <h5 class="fw-bold text-success mb-3">Data Anak</h5>
            <div class="row mt-4">
              <div :class="selectedAnak ? 'col-md-8 mb-3' : 'col-md-12 mb-3'">
                <div class="card bg-light px-2 py-5">
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center">
                      <thead class="table-light small">
                        <tr>
                          <th @click="sortBy('nama')" class="cursor-pointer align-middle text-center" rowspan="2">
                            Nama <SortIcon :field="'nama'" />
                          </th>
                          <th @click="sortBy('posyandu')" class="cursor-pointer align-middle text-center" rowspan="2">
                            Posyandu <SortIcon :field="'posyandu'" />
                          </th>
                          <th style="width:100px" @click="sortBy('usia')" class="cursor-pointer align-middle text-center" rowspan="2">
                            Usia (bln) <SortIcon :field="'usia'" />
                          </th>
                          <th style="width:60px" @click="sortBy('gender')" class="cursor-pointer align-middle text-center" rowspan="2">
                            JK <SortIcon :field="'gender'" />
                          </th>
                          <th @click="sortBy('tgl_ukur')" class="cursor-pointer align-middle text-center" rowspan="2">
                            Tgl Ukur Terakhir <SortIcon :field="'tgl_ukur'" />
                          </th>
                          <th @click="sortBy('intervensi')" class="cursor-pointer align-middle text-center" rowspan="2">
                            Intervensi <SortIcon :field="'intervensi'" />
                          </th>
                          <th colspan="3" class="text-center">Status</th>
                          <th @click="sortBy('rw')" class="cursor-pointer align-middle text-center" rowspan="2">
                            RW <SortIcon :field="'rw'" />
                          </th>
                          <th @click="sortBy('rt')" class="cursor-pointer align-middle text-center" rowspan="2">
                            RT <SortIcon :field="'rt'" />
                          </th>
                        </tr>
                        <tr>
                          <th @click="sortBy('tbu')" class="cursor-pointer text-center">TB/U <SortIcon :field="'tbu'" /></th>
                          <th @click="sortBy('bbu')" class="cursor-pointer text-center">BB/U <SortIcon :field="'bbu'" /></th>
                          <th @click="sortBy('bbtb')" class="cursor-pointer text-center">BB/TB <SortIcon :field="'bbtb'" /></th>
                        </tr>
                      </thead>

                      <tbody>
                        <tr v-for="anak in paginatedData" :key="anak.id" class="small">
                          <td class="text-start">
                            <a href="#" @click.prevent="showDetail(anak)" class="fw-semibold text-decoration-none text-primary">
                              {{ anak.nama }}
                            </a>
                          </td>
                          <td>{{ anak.posyandu }}</td>
                          <td>{{ anak.usia }}</td>
                          <td>{{ anak.gender }}</td>
                          <td>{{ anak.tgl_ukur }}</td>
                          <td>{{ anak.intervensi || '-' }}</td>
                          <td>
                            <span
                              :class="{
                                'badge px-3 py-2 bg-danger': anak.tbu === 'Severely Stunted',
                                'badge px-3 py-2 bg-warning text-dark': anak.tbu === 'Stunted',
                                'text-dark': anak.tbu === 'Normal'
                              }"
                            >
                              {{ anak.tbu }}
                            </span>
                          </td>
                          <td>
                            <span
                              :class="{
                                'badge px-3 py-2 bg-danger': anak.bbu === 'Severely Underweight',
                                'badge px-3 py-2 bg-warning text-dark': anak.bbu === 'Underweight',
                                'text-dark': anak.bbu === 'Normal'
                              }"
                            >
                              {{ anak.bbu }}
                            </span>
                          </td>
                          <td>
                            <span
                              :class="{
                                'badge px-3 py-2 bg-danger': anak.bbtb === 'Severely Wasted',
                                'badge px-3 py-2 bg-warning text-dark': anak.bbtb === 'Wasted',
                                'text-dark': anak.bbtb === 'Normal'
                              }"
                            >
                              {{ anak.bbtb }}
                            </span>
                          </td>
                          <td>{{ anak.rw }}</td>
                          <td>{{ anak.rt }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Pagination -->
                  <nav>
                    <ul class="pagination justify-content-center">
                      <li class="page-item" :class="{ disabled: currentPage === 1 }">
                        <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Prev</a>
                      </li>

                      <li
                        class="page-item"
                        v-for="page in totalPages"
                        :key="page"
                        :class="{ active: currentPage === page }"
                      >
                        <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                      </li>

                      <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                        <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
                      </li>
                    </ul>
                  </nav>
                </div>
              </div>

              <!-- DETAIL DATA -->
              <div class="col-md-4" v-if="selectedAnak">
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

                  <p class="text-muted mb-0 text-capitalize">{{ selectedAnak.alamat || 'Desa Wonosari, Kec. Bojong Gede' }}</p>
                  <p class="text-muted">{{ selectedAnak.posyandu || 'Posyandu Mawar' }}</p>

                  <!-- Badge Status Gizi -->
                  <div class="mb-3">
                    <span
                      class="badge px-3 py-2 fs-6"
                      :class="{
                        'bg-danger': ['Severely Wasted','Obesitas'].includes(selectedAnak.status_gizi),
                        'bg-warning text-dark': ['Wasted','Possible risk of Overweight','Overweight'].includes(selectedAnak.status_gizi),
                        'bg-success': selectedAnak.status_gizi === 'Normal'
                      }"
                    >
                      {{ selectedAnak.status_gizi }}
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
                        <tr
                          v-for="(r, i) in (selectedAnak.riwayat_penimbangan || []).slice(-3)"
                          :key="i"
                        >
                          <td>{{ r.tanggal }}</td>
                          <td>
                            <span
                              class="badge"
                              :class="{
                                'bg-danger': r.bbu === 'Severely Underweight',
                                'bg-warning text-dark': ['Risiko BB Lebih', 'Underweight'].includes(r.bbu),
                                'bg-success': r.bbu === 'Normal'
                              }"
                            >
                              {{ r.bbu }}
                            </span>
                          </td>
                          <td>
                            <span
                              class="badge"
                              :class="{
                                'bg-danger': r.tbu === 'Severely Stunted',
                                'bg-warning text-dark': r.tbu === 'Stunted',
                                'bg-success': r.tbu === 'Normal'
                              }"
                            >
                              {{ r.tbu }}
                            </span>
                          </td>
                          <td>
                            <span
                              class="badge"
                              :class="{
                                'bg-danger': r.bbtb === 'Severely Wasted',
                                'bg-warning text-dark': ['Wasted', 'Possible risk of Overweight', 'Overweight'].includes(r.bbtb),
                                'bg-success': r.bbtb === 'Normal'
                              }"
                            >
                              {{ r.bbtb }}
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

              <!-- Detail Riwayat Anak -->
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
                      <li class="nav-item" role="presentation">
                        <button
                          class="nav-link"
                          id="tab-tpk"
                          data-bs-toggle="tab"
                          data-bs-target="#tab-pane-tpk"
                          type="button"
                          role="tab"
                        >
                          <i class="bi bi-clipboard2-check me-1"></i> Pendampingan TPK
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
                                      'text-danger fw-bold': riwayat.bbtb === 'Stunting',
                                      'text-warning fw-bold': riwayat.bbtb === 'Underweight',
                                      'text-success fw-bold': riwayat.bbtb === 'Normal'
                                    }"
                                  >
                                    {{ riwayat.bbtb }}
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

                      <!-- Data TPK -->
                      <div class="tab-pane fade" id="tab-pane-tpk" role="tabpanel">
                        <div class="card bg-light border-0 shadow-sm p-3">
                          <h6 class="fw-bold mb-3 text-danger">Riwayat Intervensi</h6>
                          <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                              <thead class="table-secondary">
                                <tr>
                                  <th>Tanggal</th>
                                  <th>Petugas</th>
                                  <th>RT</th>
                                  <th>RW</th>
                                  <th>Berat Lahir</th>
                                  <th>Panjang Lahir</th>
                                  <th>Berat Badan</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr
                                  v-for="(item, i) in selectedAnak.riwayat_pendampingan"
                                  :key="'pendampingan-' + i"
                                >
                                  <td>{{ item.tgl_pendampingan }}</td>
                                  <td>{{ item.petugas }}</td>
                                  <td>{{ item.rt }}</td>
                                  <td>{{ item.rw }}</td>
                                  <td>{{ item.bb_lahir }}</td>
                                  <td>{{ item.pb_lahir }}</td>
                                  <td>{{ item.bb }}</td>
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
import Welcome from '@/components/Welcome.vue'
import axios from 'axios'
import { ref, computed } from 'vue'

// Simple sort icon component
const SortIcon = {
  props: ['field'],
  template: `<span v-if="$parent.sortKey === field">{{ $parent.sortDir === 'asc' ? '▲' : '▼' }}</span>`
}


// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Children',
  components: { NavbarAdmin, CopyRight, HeaderAdmin, SortIcon, Welcome },
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
      showAdvanced: false,
      totalAnak: 0,
      filters: {
        bbu:[],
        tbu:[],
        bbtb:[],
        stagnan:[],
        intervensi: [],
        posyandu: '',
        rw: '',
        rt: '',
        periodeAwal: '',
        periodeAkhir:''
      },
      posyanduList: [],
      rwList: [],
      rtList: [],
      periodeOptions: [],
      rwReadonly: true,
      rtReadonly: true,
      intervensiOptions: [
        "MBG",
        "KIE",
        "Bansos",
        "PMT",
        "Bantuan Lainnya",
        "Belum mendapatkan intervensi"
      ],
      bbuOptions: [
        "Severely Underweight",
        "Underweight",
        "Normal",
        "Risiko BB Lebih",
      ],
      tbuOptions: [
        "Severely Stunted",
        "Stunted",
        "Normal",
        "Tinggi",
      ],
      bbtbOptions: [
        "Severely Wasted",
        "Wasted",
        "Normal",
        "Possible risk of Overweight",
        "Overweight",
        "Obesitas"
      ],
      stagnanOptions: [
        "1 T",
        "2 T",
        "> 2 T",
      ],
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
      gizi:[],
      selectedAnak:'',
      children: [],
      filteredData: [],        // data hasil filter
    }
  },
  setup() {
    const searchQuery = ref('')
    const currentPage = ref(1)
    const perPage = ref(10)
    const sortKey = ref('')
    const sortDir = ref('asc')
    const filteredData = ref([])

    const applySearch = () => {
      const query = searchQuery.value.toLowerCase()
      filteredData.value = window.children.filter((c) =>
        Object.values(c).some((v) =>
          String(v).toLowerCase().includes(query)
        )
      )
      currentPage.value = 1
    }

    const sortBy = (key) => {
      if (sortKey.value === key) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
      } else {
        sortKey.value = key
        sortDir.value = 'asc'
      }
      filteredData.value.sort((a, b) => {
        if (a[key] < b[key]) return sortDir.value === 'asc' ? -1 : 1
        if (a[key] > b[key]) return sortDir.value === 'asc' ? 1 : -1
        return 0
      })
    }

    const totalPages = computed(() =>
      Math.ceil(filteredData.value.length / perPage.value)
    )

    const paginatedData = computed(() => {
      const start = (currentPage.value - 1) * perPage.value
      const end = start + perPage.value
      return filteredData.value.slice(start, end)
    })

    const changePage = (page) => {
      if (page < 1 || page > totalPages.value) return
      currentPage.value = page
    }

    return {
      searchQuery,
      // eslint-disable-next-line vue/no-dupe-keys
      filteredData,
      currentPage,
      perPage,
      sortKey,
      sortDir,
      totalPages,
      paginatedData,
      applySearch,
      sortBy,
      changePage
    }
  },
  methods: {
    async loadChildren() {
      try {
        const res = await axios.get(`${baseURL}/api/children`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        // API structure: { status, message, data_anak: [...] }
        const items = res.data.data_anak || []

        this.children = items.map((item) => {
          const kelahiran = item.kelahiran?.[0] || {}
          const keluarga = item.keluarga?.[0] || {}
          const pendamping = item.pendampingan?.[item.pendampingan.length - 1] || {}
          const posyandu = item.posyandu?.[item.posyandu.length - 1] || {}
          const intervensi = item.intervensi?.[item.intervensi.length - 1] // ambil intervensi terakhir

          return {
            // === Identitas Anak ===
            id: item.id,
            nama: item.nama || '-',
            nik: item.nik || '-',
            gender: item.jk || '-',
            provinsi: item.provinsi || '-',
            kota: item.kota || '-',
            kecamatan: item.kecamatan || '-',
            kelurahan: item.kelurahan || '-',
            rt: item.rt || '-',
            rw: item.rw || '-',

            // === Data Posyandu terakhir ===
            posyandu: posyandu.posyandu || '-',
            usia: posyandu.usia || '-',
            tgl_ukur: posyandu.tgl_ukur || '-',
            bbu: posyandu.bbu || '-',
            tbu: posyandu.tbu || '-',
            bbtb: posyandu.bbtb || '-',
            bb: posyandu.bb || '-',
            tb: posyandu.tb || '-',
            bb_naik: posyandu.bb_naik || false,

            // === Data Intervensi ===
            intervensi: intervensi ? intervensi.jenis : 'Belum dapat Intervensi',

            // === Tambahan opsional ===
            tmpt_dilahirkan: kelahiran.tmpt_dilahirkan || '-',
            tgl_lahir: kelahiran.tgl_lahir || '-',
            bb_lahir: kelahiran.bb_lahir || '-',
            pb_lahir: kelahiran.pb_lahir || '-',
            persalinan: kelahiran.persalinan || '-',
            nama_ayah: keluarga.nama_ayah || '-',
            nama_ibu: keluarga.nama_ibu || '-',
            pekerjaan_ayah: keluarga.pekerjaan_ayah || '-',
            pekerjaan_ibu: keluarga.pekerjaan_ibu || '-',
            usia_ayah: keluarga.usia_ayah || '-',
            usia_ibu: keluarga.usia_ibu || '-',
            anak_ke: keluarga.anak_ke || '-',
            kader_pendamping: pendamping.kader || '-',
            tgl_pendampingan: pendamping.tanggal || '-',

            // === Simpan data mentah (buat showDetail nanti) ===
            raw: {
              kelahiran: item.kelahiran || [],
              keluarga: item.keluarga || [],
              pendampingan: item.pendampingan || [],
              posyandu: item.posyandu || [],
              intervensi: item.intervensi || []
            }
          }

        })

        // Setelah data dimuat, langsung apply filter awal
        this.applyFilter()
        // 🔹 Hitung total anak dengan usia < 60 bulan
        this.totalAnak = this.children.filter((anak) => anak.usia < 60).length;

        // 🔹 Inisialisasi filteredData
        this.filteredData = [...this.children];

        // 🔹 Hitung status gizi awal
        this.hitungStatusGizi();
        this.generateListsFromChildren()
      } catch (e) {
        this.showError('Error Ambil Data Anak', e)
      }
    },
    hitungStatusGizi() {
      const dataAnak = this.filteredData.length ? this.filteredData : this.children;
      const total = dataAnak.length;

      const count = {
        Stunting: 0,
        Wasting: 0,
        Underweight: 0,
        Normal: 0,
        'BB Stagnan': 0,
        Overweight: 0,
      };

      dataAnak.forEach((anak) => {
        const { bbu, tbu, bbtb } = anak;

        if (tbu?.includes('Stunted')) count.Stunting++;
        if (bbtb?.includes('Wasted')) count.Wasting++;
        if (bbu?.includes('Underweight')) count.Underweight++;
        if (bbu === 'Normal' && tbu === 'Normal' && bbtb === 'Normal') count.Normal++;
        if (bbtb?.includes('Overweight')) count.Overweight++;

        // === cek stagnan (3 kali BB sama)
        const riwayat = anak.raw?.posyandu || [];
        if (riwayat.length >= 3) {
          const last3 = riwayat.slice(-3);
          const allEqual = last3.every((r) => r.bb === last3[0].bb);
          if (allEqual) count['BB Stagnan']++;
        }
      });

      this.gizi = Object.entries(count).map(([title, value]) => {
        const percent = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
        const colorMap = {
          Stunting: 'danger',
          Wasting: 'warning',
          Underweight: 'violet',
          Normal: 'success',
          'BB Stagnan': 'info',
          Overweight: 'dark',
        };
        return { title, value, percent, color: colorMap[title] };
      });
    },
    isStagnan(item) {
      const STATUS_DIBAWAH_NORMAL = [
        'Underweight', 'Severely Underweight',
        'Stunted', 'Severely Stunted',
        'Wasted', 'Severely Wasted'
      ]

      const penimbangan = item.riwayat_penimbangan
      if (!penimbangan || penimbangan.length < 2) return false

      // Ambil dua penimbangan terakhir
      const last = penimbangan[penimbangan.length - 1]
      const prev = penimbangan[penimbangan.length - 2]

      const bbSama = last.bb === prev.bb && STATUS_DIBAWAH_NORMAL.includes(last.bb)
      const tbSama = last.tb === prev.tb && STATUS_DIBAWAH_NORMAL.includes(last.tb)
      const bbtbSama = last.bbtb === prev.bbtb && STATUS_DIBAWAH_NORMAL.includes(last.bbtb)

      return bbSama || tbSama || bbtbSama
    },
    generateListsFromChildren() {
      // === Posyandu unik ===
      const posyanduSet = new Set(this.children.map(c => c.posyandu).filter(Boolean))
      this.posyanduList = Array.from(posyanduSet).map((nama, index) => ({
        id: index + 1,
        nama_posyandu: nama
      }))

      // === RW dan RT global (kalau gak pilih posyandu) ===
      const rwSet = new Set(this.children.map(c => c.rw).filter(Boolean))
      const rtSet = new Set(this.children.map(c => c.rt).filter(Boolean))
      this.rwList = Array.from(rwSet)
      this.rtList = Array.from(rtSet)

      // awalnya di-disable
      this.rwReadonly = true
      this.rtReadonly = true
    },

    handlePosyanduChange() {
      const pos = this.filters.posyandu

      if (pos) {
        const filteredChildren = this.children.filter(c => c.posyandu === pos)

        const rwSet = new Set(filteredChildren.map(c => c.rw).filter(Boolean))
        const rtSet = new Set(filteredChildren.map(c => c.rt).filter(Boolean))

        this.rwList = Array.from(rwSet)
        this.rtList = Array.from(rtSet)
        this.rwReadonly = false
        this.rtReadonly = false
      } else {
        // reset kalau posyandu dikosongin
        const rwSet = new Set(this.children.map(c => c.rw).filter(Boolean))
        const rtSet = new Set(this.children.map(c => c.rt).filter(Boolean))
        this.rwList = Array.from(rwSet)
        this.rtList = Array.from(rtSet)
        this.rwReadonly = true
        this.rtReadonly = true
        this.filters.rw = ''
        this.filters.rt = ''
      }
    },

    applyFilter() {
      this.filteredData = this.children.filter(item => {
        const f = this.filters

        // === 1. Filter Underweight (BBU)
        const bbuMatch = f.bbu.length === 0 || f.bbu.includes(item.bbu)

        // === 2. Filter Stunting (TBU)
        const tbuMatch = f.tbu.length === 0 || f.tbu.includes(item.tbu)

        // === 3. Filter Wasting (BBTB)
        const bbtbMatch = f.bbtb.length === 0 || f.bbtb.includes(item.bbtb)

        // === 4. Filter BB Stagnan
        const stagnanMatch =
        f.stagnan.length === 0 ||
        f.stagnan.some(opt => {
          if (opt === 'Ya') return this.isStagnan(item)
          if (opt === 'Tidak') return !this.isStagnan(item)
          return true
        })


        // === 5. Filter Posyandu
        const posyanduMatch =
          !f.posyandu || item.posyandu === f.posyandu || item.nama_posyandu === f.posyandu

        // === 6. Filter RW & RT
        const rwMatch = !f.rw || item.rw === f.rw
        const rtMatch = !f.rt || item.rt === f.rt

        // === 7. Filter Intervensi (multi)
        const intervensiMatch =
          f.intervensi.length === 0 ||
          f.intervensi.some(i => (item.intervensi || '').includes(i))

        // === 8. Filter Periode (antara dua periode)
        const periodeMatch = (() => {
          if (!f.periodeAwal && !f.periodeAkhir) return true

          const tgl = new Date(item.tgl_ukur)
          const periodeNum = tgl.getFullYear() * 100 + (tgl.getMonth() + 1)

          const awal = f.periodeAwal
            ? (() => {
                const [y, m] = f.periodeAwal.split('-')
                return parseInt(y) * 100 + parseInt(m)
              })()
            : -Infinity

          const akhir = f.periodeAkhir
            ? (() => {
                const [y, m] = f.periodeAkhir.split('-')
                return parseInt(y) * 100 + parseInt(m)
              })()
            : Infinity

          return periodeNum >= awal && periodeNum <= akhir
        })()


        return (
          bbuMatch &&
          tbuMatch &&
          bbtbMatch &&
          stagnanMatch &&
          posyanduMatch &&
          rwMatch &&
          rtMatch &&
          intervensiMatch &&
          periodeMatch
        )
      })
    },

    resetFilter() {
      this.filters = {
        bbu: [],
        tbu: [],
        bbtb: [],
        stagnan: [],
        intervensi: [],
        posyandu: '',
        rw: '',
        rt: '',
        periodeAwal: '',
        periodeAkhir: ''
      }

      this.rwList = []
      this.rtList = []
      this.rwReadonly = true
      this.rtReadonly = true

      this.filteredData = [...this.children]
    },
    selectAll_bbu() {
      this.filters.bbu = [...this.bbuOptions]
    },
    selectAll_tbu() {
      this.filters.tbu = [...this.tbuOptions]
    },
    selectAll_bbtb() {
      this.filters.bbtb = [...this.bbtbOptions]
    },
    selectAll_stagnan() {
      this.filters.stagnan = [...this.stagnanOptions]
    },
    selectAll_intervensi() {
      this.filters.intervensi = [...this.intervensiOptions]
    },

    clearAll_bbu() {
      this.filters.bbu = []
    },
    clearAll_tbu() {
      this.filters.tbu = []
    },
    clearAll_bbtb() {
      this.filters.bbtb = []
    },
    clearAll_stagnan() {
      this.filters.stagnan = []
    },
    clearAll_intervensi() {
      this.filters.intervensi = []
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

        const wilayah = res.data
        this.kelurahan = wilayah.kelurahan || 'Tidak diketahui'
        this.id_wilayah = wilayah.id_wilayah // pastikan backend kirim ini

        // Setelah dapet id_wilayah, langsung fetch posyandu
        //await this.fetchPosyanduByWilayah(this.id_wilayah)
      } catch (error) {
        console.error('Gagal ambil data wilayah user:', error)
        this.kelurahan = '-'
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
      ////console.log('Klik props:', props)

      const raw = props.raw || {}

      const posyanduList = Array.isArray(raw.posyandu) ? raw.posyandu : []
      const intervensiList = Array.isArray(raw.intervensi) ? raw.intervensi : []
      const pendampinganList = Array.isArray(raw.pendampingan) ? raw.pendampingan : []
      const kelahiranList = Array.isArray(raw.kelahiran) ? raw.kelahiran : []
      const keluargaList = Array.isArray(raw.keluarga) ? raw.keluarga : []

      const lastPosyandu = posyanduList.length
        ? posyanduList.sort((a, b) => new Date(a.tgl_ukur) - new Date(b.tgl_ukur)).slice(-1)[0]
        : {}

      this.selectedAnak = {
        // --- Identitas Anak ---
        id: props.id,
        nik: props.nik || '-',
        nama: props.nama || '-',
        gender: props.gender || props.jk || '-',
        usia: lastPosyandu.usia || '-',
        alamat: `${props.kelurahan || '-'}, RT ${props.rt || '-'} / RW ${props.rw || '-'}`,
        desa: props.kelurahan || '-',
        kecamatan: props.kecamatan || '-',
        provinsi: props.provinsi || '-',
        kota: props.kota || '-',
        status_gizi:props.bbtb || '-',

        // --- Orang Tua (keluarga[0]) ---
        nama_ayah: keluargaList[0]?.nama_ayah || '-',
        nik_ayah: keluargaList[0]?.nik_ayah || '-',
        nama_ibu: keluargaList[0]?.nama_ibu || '-',
        nik_ibu: keluargaList[0]?.nik_ibu || '-',
        no_telp: keluargaList[0]?.no_telp || '-',

        // --- Data Kelahiran ---
        kelahiran: kelahiranList.map(k => ({
          no_kia: k.no_kia || '-',
          tmpt_dilahirkan: k.tmpt_dilahirkan || '-',
          tgl_lahir: k.tgl_lahir || '-',
          bb: k.bb_lahir || '-',
          pb: k.pb_lahir || '-',
          jenis: k.persalinan || '-',
        })),

        // --- Riwayat Penimbangan (Posyandu) ---
        riwayat_penimbangan: posyanduList.map(p => ({
          tanggal: p.tgl_ukur || '-',
          bb: p.bb || '-',
          tb: p.tb || '-',
          bbu: p.bbu || '-',
          tbu: p.tbu || '-',
          bbtb: p.bbtb || '-',
        })),

         // ambil status terakhir
        status_terakhir: {
          bbu: lastPosyandu.bbu || '-',
          tbu: lastPosyandu.tbu || '-',
          bbtb: lastPosyandu.bbtb || '-',
        },

        // --- Riwayat Intervensi ---
        riwayat_intervensi: intervensiList.map(i => ({
          tanggal: i.tanggal || '-',
          kader: i.kader || '-',
          intervensi: i.jenis || '-',
        })),

        // --- Riwayat Pendampingan (TPK) ---
        riwayat_pendampingan: pendampinganList.map(p => ({
          tgl_pendampingan: p.tanggal || '-',
          petugas: p.kader || '-',
          usia: p.usia || '-',
          rt: props.rt || '-',
          rw: props.rw || '-',
          bb_lahir: kelahiranList[0]?.bb_lahir || '-',
          pb_lahir: kelahiranList[0]?.pb_lahir || '-',
          bb: p.bb || '-'
        })),

      }

      //console.log('selectedAnak detail:', this.selectedAnak)
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
      await this.getWilayahUser()
      await this.loadConfigWithCache()
      this.generatePeriodeOptions()

      this.hitungStatusGizi()
      // 🔥 Load children dulu
      await this.loadChildren()

      // Ambil query param
      const { tipe, status } = this.$route.query
      //console.log('data dari dashboard:', tipe, status)

      // Copy semua children
      this.filteredData = [...this.children]

      // Terapkan filter dari chart
      if (tipe && status) {
        this.filters.bbu = []
        this.filters.tbu = []
        this.filters.bbtb = []

        if (tipe === 'BB/U') this.filters.bbu = [status]
        else if (tipe === 'TB/U') this.filters.tbu = [status]
        else if (tipe === 'BB/TB') this.filters.bbtb = [status]

        // Pastikan dropdown reactive ter-update sebelum applyFilter
        await this.$nextTick()

        if (typeof this.applyFilter === 'function') {
          this.applyFilter()
        }
      }


      this.handleResize()
      window.addEventListener('resize', this.handleResize)
    } catch (err) {
      console.error('Error mounted:', err)
    } finally {
      this.isLoading = false
    }
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
  },
  watch: {
    'filters.posyandu'(val) {
      this.handlePosyanduChange(val)
    }
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
