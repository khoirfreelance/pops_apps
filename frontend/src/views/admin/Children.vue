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
            <div class="bg-primary text-white py-1 px-4 d-inline-block rounded-top">
              <h5 class="mb-0">
                Laporan Status Gizi dan Stagnansi <span class="text-capitalize fw-bold">{{ kelurahan }}</span> Bulan <span class="fw-bold">{{ thisMonth }}</span>
              </h5>
            </div>
          </div>

          <!-- Filter Form -->
          <div class="bg-light border rounded-bottom shadow-sm px-4 py-3 mt-0">
            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <!-- === Filter Utama === -->
              <div class="col-md-2 position-relative">
                <label class="form-label text-primary">Filter Status Gizi:</label>
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                  >
                    <span v-if="filters.bbu.length === 0" class="text-muted">Pilih Underweight</span>
                    <span v-else class="selected-text">{{ underDisplayText }}</span>
                  </button>

                  <ul class="dropdown-menu w-100" style="max-height: 260px; overflow-y: auto;">
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

              <div class="col-md-2">
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                  >
                    <span v-if="filters.tbu.length === 0" class="text-muted">Pilih Stunting</span>
                    <span v-else class="selected-text">{{ StuntingDisplayText }}</span>
                  </button>

                  <ul class="dropdown-menu w-100" style="max-height: 260px; overflow-y: auto;">
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

              <div class="col-md-2">
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                  >
                    <span v-if="filters.bbtb.length === 0" class="text-muted">Pilih Wasting</span>
                    <span v-else class="selected-text">{{ WastingDisplayText }}</span>
                  </button>

                  <ul class="dropdown-menu w-100" style="max-height: 260px; overflow-y: auto;">
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
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                  >
                    <span v-if="filters.stagnan.length === 0" class="text-muted">Pilih BB Stagnan</span>
                    <span v-else class="selected-text">{{ stagnanDisplayText }}</span>
                  </button>

                  <ul class="dropdown-menu w-100" style="max-height: 260px; overflow-y: auto;">
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

              <div class="col-md-3 position-relative">
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                  >
                    <span v-if="filters.intervensi.length === 0" class="text-muted">Pilih Intervensi</span>
                    <span v-else class="selected-text">{{ intervesiDisplayText }}</span>
                  </button>

                  <ul class="dropdown-menu w-100" style="max-height: 260px; overflow-y: auto;">
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

              <div class="col-md-2">
                <label class="form-label text-primary">Filter Lokasi</label>
                <select
                  v-model="filters.posyandu"
                  class="form-select text-muted"
                  @change="handlePosyanduChange"
                >
                  <option value="">Pilih Posyandu</option>
                  <option v-for="item in posyanduList" :key="item.id" :value="item.nama_posyandu">
                    {{ item.nama_posyandu }}
                  </option>
                </select>
              </div>

              <div class="col-md-2">
                <select v-model="filters.rw" class="form-select text-muted" :disabled="rwReadonly">
                  <option value="">Pilih RW</option>
                  <option v-for="rw in rwList" :key="rw" :value="rw">{{ rw }}</option>
                </select>
              </div>

              <div class="col-md-2">
                <select v-model="filters.rt" class="form-select text-muted" :disabled="rtReadonly">
                  <option value="">Pilih RT</option>
                  <option v-for="rt in rtList" :key="rt" :value="rt">{{ rt }}</option>
                </select>
              </div>

              <div class="col-md-6 text-center">
                <label class="form-label text-primary">Pilih Periode:</label>
                <div class="d-flex justify-content-between gap-2">
                  <select v-model="filters.periodeAwal" class="form-select text-muted">
                    <option value="">Awal</option>
                    <option v-for="periode in periodeOptions" :key="periode" :value="periode">
                      {{ periode }}
                    </option>
                  </select>
                  <select v-model="filters.periodeAkhir" class="form-select text-muted">
                    <option value="">Akhir</option>
                    <option v-for="periode in periodeOptions" :key="periode" :value="periode">
                      {{ periode }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- === Tombol Aksi === -->
              <div class="col-md-12 d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-gradient fw-semibold me-2">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>
                <button type="button" class="btn btn-secondary fw-semibold" @click="resetFilter">
                  <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
              </div>
            </form>
          </div>

          <div class="text-center mt-3">
            <h5 class="fw-bold text-success mb-3">Ringkasan Statistik</h5>
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
              <div :class="selectedAnak ? 'col-md-8' : 'col-md-12'">
                <div class="card bg-light p-2">
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center">
                      <thead class="table-success small">
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
                        v-for="page in visiblePages"
                        :key="page"
                        :class="{ active: currentPage === page, disabled: page === '...' }"
                      >
                        <a
                          v-if="page !== '...'"
                          class="page-link"
                          href="#"
                          @click.prevent="changePage(page)"
                        >{{ page }}</a>
                        <span v-else class="page-link">...</span>
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
                      class="badge px-3 py-2 small"
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
                    <p class="text-white mb-0 small">
                      NIK: {{ selectedAnak.nik }} •
                      <span class="text-capitalize">
                        {{ selectedAnak.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                      </span>
                      Usia: {{ selectedAnak.usia }} bln
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
                          <div class="table-responsive mb-4">
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
                                <tr v-for="(riwayat, i) in selectedAnak.riwayat_penimbangan" :key="'penimbangan-' + i">
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

                          <div class="row g-3">
                            <!-- Chart BB/U -->
                            <div class="col-md-6 col-12">
                              <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                  <h6 class="mb-2">BB/U (0–60 bln)</h6>
                                  <div style="height: 200px;">
                                    <canvas ref="chartBB"></canvas>
                                  </div>

                                </div>
                              </div>
                            </div>

                            <!-- Chart TB/U -->
                            <div class="col-md-6 col-12">
                              <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                  <h6 class="mb-2">TB/U (0–60 bln)</h6>
                                  <div style="height: 200px;">
                                    <canvas ref="chartTB"></canvas>
                                  </div>
                                </div>
                              </div>
                            </div>

                            <!-- Chart BB/TB -->
                            <!-- <div class="col-md-4 col-12">
                              <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                  <h6 class="mb-2">BB/TB</h6>
                                  <div style="height: 200px;">
                                    <canvas ref="chartBBTB"></canvas>
                                  </div>
                                  <div class="small text-muted mt-2">
                                    Catatan: kurva via interpolasi linear antar titik acuan WHO untuk demo.
                                  </div>
                                </div>
                              </div>
                            </div> -->
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
import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  Title,
  CategoryScale,
  Legend,
  Tooltip,
  Filler
} from 'chart.js'

Chart.register(
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  Title,
  CategoryScale,
  Legend,
  Tooltip,
  Filler
)

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
      chartBBTB: null,
      chartBB: null,
      chartTB: null,
      ageMonths: 24,
      currentWeight: 12,
      currentHeight: 87,
      gender: 'male',

      kmsColors: {
        top: '#F2D803',
        midTop: '#84BA24',
        mid: '#2CA339',
        midMed: '#2DA83C',
        midBottom: '#80B626',
        bottom: '#DCBF1E',
      },
      wfaBoys: [
        { m: 0, median: 3.3, sd: 0.3 }, { m: 1, median: 4.5, sd: 0.35 },
        { m: 2, median: 5.6, sd: 0.4 }, { m: 3, median: 6.4, sd: 0.45 },
        { m: 4, median: 7.0, sd: 0.5 }, { m: 5, median: 7.5, sd: 0.55 },
        { m: 6, median: 7.9, sd: 0.6 }, { m: 12, median: 9.6, sd: 0.8 },
        { m: 24, median: 12.2, sd: 1.1 }, { m: 36, median: 14.3, sd: 1.4 },
        { m: 48, median: 16.3, sd: 1.6 }, { m: 60, median: 18.3, sd: 1.8 }
      ],
      wfaGirls: [
        { m: 0, median: 3.2, sd: 0.3 }, { m: 1, median: 4.2, sd: 0.35 },
        { m: 2, median: 5.1, sd: 0.4 }, { m: 3, median: 5.8, sd: 0.45 },
        { m: 4, median: 6.4, sd: 0.5 }, { m: 5, median: 6.9, sd: 0.55 },
        { m: 6, median: 7.3, sd: 0.6 }, { m: 12, median: 8.9, sd: 0.8 },
        { m: 24, median: 11.5, sd: 1.1 }, { m: 36, median: 13.9, sd: 1.4 },
        { m: 48, median: 16.0, sd: 1.6 }, { m: 60, median: 18.2, sd: 1.8 }
      ],
      hfaBoys: [
        { m: 0, median: 49.9, sd: 1.9 }, { m: 1, median: 54.7, sd: 2.0 },
        { m: 2, median: 58.4, sd: 2.1 }, { m: 3, median: 61.4, sd: 2.2 },
        { m: 4, median: 63.9, sd: 2.3 }, { m: 5, median: 65.9, sd: 2.4 },
        { m: 6, median: 67.6, sd: 2.5 }, { m: 12, median: 75.7, sd: 2.9 },
        { m: 24, median: 87.8, sd: 3.2 }, { m: 36, median: 96.1, sd: 3.4 },
        { m: 48, median: 103.3, sd: 3.6 }, { m: 60, median: 110.0, sd: 3.8 }
      ],
      hfaGirls: [
        { m: 0, median: 49.1, sd: 1.9 }, { m: 1, median: 53.7, sd: 2.0 },
        { m: 2, median: 57.1, sd: 2.1 }, { m: 3, median: 59.8, sd: 2.2 },
        { m: 4, median: 62.1, sd: 2.3 }, { m: 5, median: 64.0, sd: 2.4 },
        { m: 6, median: 65.7, sd: 2.5 }, { m: 12, median: 74.0, sd: 2.8 },
        { m: 24, median: 86.4, sd: 3.2 }, { m: 36, median: 95.1, sd: 3.4 },
        { m: 48, median: 102.7, sd: 3.6 }, { m: 60, median: 109.4, sd: 3.8 }
      ],

      /* Wajib ada */
      file: null,
      fileName: '',
      fileSize: 0,
      filePreviewLines: '',
      fileError: '',
      uploading: false,
      uploadProgress: 0,
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
        Object.values(c).some((v) => String(v).toLowerCase().includes(query))
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

    const visiblePages = computed(() => {
      const pages = []
      const total = totalPages.value
      const current = currentPage.value

      if (total <= 4) {
        for (let i = 1; i <= total; i++) pages.push(i)
      } else if (current <= 2) {
        // Halaman awal
        pages.push(1, 2, 3, '...', total)
      } else if (current >= total - 1) {
        // Halaman akhir
        pages.push(total - 2, total - 1, total)
      } else {
        // Tengah
        pages.push(current - 1, current, current + 1, '...', total)
      }

      return pages
    })


    const changePage = (page) => {
      if (page < 1 || page > totalPages.value || page === '...') return
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
      changePage,
      visiblePages
    }
  },
  methods: {
    downloadRiwayat() {
      if (!this.selectedAnak) {
        alert('Silakan pilih anak terlebih dahulu.')
        return
      }

      // Data utama anak
      const anak = this.selectedAnak
      let csvContent = `Data Anak\n`
      csvContent += `Nama,${anak.nama}\n`
      csvContent += `NIK,${anak.nik}\n`
      csvContent += `Jenis Kelamin,${anak.gender === 'L' ? 'Laki-laki' : anak.gender === 'P' ? 'Perempuan' : anak.gender}\n`
      csvContent += `Usia (${anak.usia} bulan),${anak.usia}\n`
      csvContent += `Tgl Ukur Terakhir,${anak.tgl_ukur}\n`
      csvContent += `Status Gizi,${anak.status_gizi}\n`
      csvContent += `Intervensi,${anak.intervensi}\n\n`

      // Riwayat penimbangan (jika ada)
      if (anak.riwayat_penimbangan && anak.riwayat_penimbangan.length) {
        csvContent += `Riwayat Penimbangan\nTanggal,Status BB/U,Status TB/U,Status BB/TB\n`
        anak.riwayat_penimbangan.forEach(r => {
          csvContent += `${r.tanggal},${r.bb},${r.tb},${r.bb_tb}\n`
        })
        csvContent += `\n`
      }

      // Riwayat intervensi/bantuan (jika ada)
      if (anak.riwayat_intervensi && anak.riwayat_intervensi.length) {
        csvContent += `Riwayat Intervensi / Bantuan\nTanggal,Kader,Intervensi\n`
        anak.riwayat_intervensi.forEach(r => {
          csvContent += `${r.tanggal},${r.kader},${r.intervensi}\n`
        })
        csvContent += `\n`
      }

      // Konversi ke blob dan download
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `Riwayat_${anak.nama.replace(/\s+/g, '_')}.csv`)
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    },
    async loadChildren() {
      try {
        const res = await axios.get(`${baseURL}/api/children`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
          params: this.filters, // 🔹 kirim semua filter ke backend
        });

        const items = res.data.data_anak || [];

        // 🔹 Ambil semua nama posyandu unik
        const allPosyandu = items
          .flatMap(item => item.posyandu?.map(p => p.posyandu).filter(Boolean) || [])
        const uniquePosyandu = [...new Set(allPosyandu)];

        this.posyanduList = uniquePosyandu.map((nama, idx) => ({
          id: idx + 1,
          nama_posyandu: nama,
        }));

        // 🔹 Format data anak
        this.children = items.map((item) => {
          const kelahiran = item.kelahiran?.[0] || {};
          const keluarga = item.keluarga?.[0] || {};
          const pendamping = item.pendampingan?.at(-1) || {};
          const posyandu = item.posyandu?.at(-1) || {};
          const intervensi = item.intervensi?.at(-1);

          return {
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
            posyandu: posyandu.posyandu || '-',
            usia: posyandu.usia || '-',
            tgl_ukur: posyandu.tgl_ukur || '-',
            bbu: posyandu.bbu || '-',
            tbu: posyandu.tbu || '-',
            bbtb: posyandu.bbtb || '-',
            bb: posyandu.bb || '-',
            tb: posyandu.tb || '-',
            bb_naik: posyandu.bb_naik || false,
            intervensi: intervensi ? intervensi.jenis : 'Belum mendapatkan intervensi',
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
            raw: {
              kelahiran: item.kelahiran || [],
              keluarga: item.keluarga || [],
              pendampingan: item.pendampingan || [],
              posyandu: item.posyandu || [],
              intervensi: item.intervensi || []
            }
          }
        });

        // ✅ simpan hasilnya
        this.filteredData = [...this.children];

        // 🔹 (opsional) sekalian update ringkasan status gizi
        await this.hitungStatusGizi();

      } catch (e) {
        this.showError('Error Ambil Data Anak', e);
      }
    },
    async applyFilter() {
      this.isLoading = true
      try {
        await this.loadChildren(),
        await this.hitungStatusGizi()
      }catch(e){
        console.error(e);
      }finally{
        this.isLoading = false
      }
    },
    async hitungStatusGizi() {
      try {
        const params = {
          posyandu: this.filters.posyandu|| '',
          rw: this.filters.rw|| '',
          rt: this.filters.rt|| '',
          usia: this.filters.usia|| '',
          bbu: this.filters.bbu|| '',
          tbu: this.filters.tbu|| '',
          bbtb: this.filters.bbtb|| '',
          intervensi: this.filters.intervensi || [],
          periodeAwal: this.filters.periodeAwal || '',
          periodeAkhir: this.filters.periodeAkhir || '',
        };

        const res = await axios.get(`${baseURL}/api/children/status`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          params,
        });

        const { total, counts, kelurahan } = res.data;

        // ✅ Bind langsung ke data komponen
        this.gizi = counts || [];
        this.totalAnak = total || 0;
        this.kelurahan = kelurahan || '-';

      } catch (error) {
        console.error('❌ hitungStatusGizi error:', error);
      }
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
    async resetFilter() {
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
      await this.loadChildren()
      await this.hitungStatusGizi()
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

      this.renderKMSChart();
      //console.log('selectedAnak detail:', this.selectedAnak)
    },
    // KMS Chart (BB/U & TB/U)
    renderKMSChart() {
      if (!this.selectedAnak?.riwayat_penimbangan?.length) return;

      this.$nextTick(() => {
        const riwayat = this.selectedAnak.riwayat_penimbangan.sort((a, b) => a.tanggal.localeCompare(b.tanggal));
        if (!riwayat.length) return;

        const gender = this.selectedAnak.gender;
        const lastRecord = riwayat[riwayat.length - 1];
        const usiaAnak = Number(this.selectedAnak.usia);
        const bbAnak = Number(lastRecord.bb);
        const tbAnak = Number(lastRecord.tb);

        const wfa = gender === 'L' ? this.wfaBoys : this.wfaGirls;
        const hfa = gender === 'L' ? this.hfaBoys : this.hfaGirls;
        const C = this.kmsColors;

        // === Utility warna → RGBA ===
        this.hexA = (hex, alpha) => {
          const bigint = parseInt(hex.replace('#', ''), 16);
          const r = (bigint >> 16) & 255;
          const g = (bigint >> 8) & 255;
          const b = bigint & 255;
          return `rgba(${r},${g},${b},${alpha})`;
        };

        // === Ekspansi WHO dataset ===
        const expandWHO = (arr) => ({
          usia: arr.map(d => d.m),
          median: arr.map(d => d.median),
          sd1: arr.map(d => d.median + 1 * d.sd),
          sd2: arr.map(d => d.median + 2 * d.sd),
          sd3: arr.map(d => d.median + 3 * d.sd),
          sd_1: arr.map(d => d.median - 1 * d.sd),
          sd_2: arr.map(d => d.median - 2 * d.sd),
          sd_3: arr.map(d => d.median - 3 * d.sd),
        });

        const bbData = expandWHO(wfa);
        const tbData = expandWHO(hfa);

        // === Kurva area (dengan warna) ===
        const makeDatasets = (D, C) => [
          {
            label: '-3SD',
            data: D.sd_3,
            borderColor: C.bottom,
            backgroundColor: this.hexA(C.bottom, 0.2),
            fill: '+1',
            tension: 0.2,
            pointRadius: 0,
            order: 1,
          },
          {
            label: '-2SD',
            data: D.sd_2,
            borderColor: C.midBottom,
            backgroundColor: this.hexA(C.midBottom, 0.2),
            fill: '+1',
            tension: 0.2,
            pointRadius: 0,
            order: 2,
          },
          {
            label: '-1SD',
            data: D.sd_1,
            borderColor: C.mid,
            backgroundColor: this.hexA(C.mid, 0.2),
            fill: '+1',
            tension: 0.2,
            pointRadius: 0,
            order: 3,
          },
          {
            label: 'Median',
            data: D.median,
            borderColor: C.midMed,
            backgroundColor: this.hexA(C.midMed, 0.15),
            fill: '+1',
            tension: 0.2,
            pointRadius: 0,
            order: 4,
          },
          {
            label: '+1SD',
            data: D.sd1,
            borderColor: C.midTop,
            backgroundColor: this.hexA(C.midTop, 0.15),
            fill: '+1',
            tension: 0.2,
            pointRadius: 0,
            order: 5,
          },
          {
            label: '+2SD',
            data: D.sd2,
            borderColor: C.top,
            backgroundColor: this.hexA(C.top, 0.15),
            fill: '+1',
            tension: 0.2,
            pointRadius: 0,
            order: 6,
          },
          {
            label: '+3SD',
            data: D.sd3,
            borderColor: C.top,
            backgroundColor: this.hexA(C.top, 0.1),
            fill: false,
            tension: 0.2,
            pointRadius: 0,
            order: 7,
          },
        ];

        // === Titik anak ===
        // eslint-disable-next-line no-unused-vars
        const makePoint = (D, usiaAnak, nilai, labelY) => {
          // Cari usia terdekat
          let nearestIndex = 0;
          let minDiff = Infinity;
          D.usia.forEach((u, i) => {
            const diff = Math.abs(u - usiaAnak);
            if (diff < minDiff) {
              minDiff = diff;
              nearestIndex = i;
            }
          });

          // Data titik tunggal
          const pointData = Array(D.usia.length).fill(null);
          pointData[nearestIndex] = nilai;

          return {
            label: 'Anak',
            data: pointData,
            borderColor: '#fff',
            backgroundColor: gender === 'L' ? '#007bff' : '#ff4b5c',
            pointRadius: 8,
            borderWidth: 2,
            pointStyle: 'circle',
            showLine: false,
            clip: false, // penting biar tidak terpotong oleh area
            order: 9999, // paling depan
            z: 9999,
          };
        };

        // === Chart Builder ===
        const buildChart = (ctx, dataObj, labelY, nilaiAnak, usiaAnak, minY, maxY) => {
          return new Chart(ctx, {
            type: 'line',
            data: {
              labels: dataObj.usia,
              datasets: [
                ...makeDatasets(dataObj, C),
                makePoint(dataObj, usiaAnak, nilaiAnak, labelY),
              ],
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: { display: false },
                datalabels: { display: false },
                tooltip: {
                  backgroundColor: 'rgba(0,0,0,0.8)',
                  titleFont: { weight: 'bold', size: 13 },
                  bodyFont: { size: 12 },
                  padding: 8,
                  displayColors: false,
                  callbacks: {
                    label: () => `Umur: ${usiaAnak} bln, ${labelY}: ${nilaiAnak}`,
                  },
                },
              },
              interaction: {
                mode: 'nearest',
                intersect: false,
              },
              elements: {
                line: {
                  borderWidth: 2,
                },
                point: {
                  radius: 0,
                },
              },
              scales: {
                x: {
                  title: { display: true, text: 'Umur (bulan)' },
                  min: 0,
                  max: 60,
                },
                y: {
                  title: { display: true, text: labelY },
                  min: minY,
                  max: maxY,
                },
              },
            },
          });
        };

        // === BB/U Chart ===
        const ctxBB = this.$refs.chartBB?.getContext('2d');
        if (ctxBB) {
          if (this.chartBB) this.chartBB.destroy();
          this.chartBB = buildChart(ctxBB, bbData, 'Berat Badan (kg)', bbAnak, usiaAnak, 0, 25);
        }

        // === TB/U Chart ===
        const ctxTB = this.$refs.chartTB?.getContext('2d');
        if (ctxTB) {
          if (this.chartTB) this.chartTB.destroy();
          this.chartTB = buildChart(ctxTB, tbData, 'Tinggi Badan (cm)', tbAnak, usiaAnak, 45, 120);
        }
      });
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
  computed:{
    underDisplayText() {
      const total = this.filters.bbu.length;
      const allSelected = total === this.bbuOptions.length;

      if (allSelected) return 'All';
      if (total === 1) return this.filters.bbu[0];
      return `${total} Selected`;
    },
    StuntingDisplayText() {
      const total = this.filters.tbu.length;
      const allSelected = total === this.tbuOptions.length;

      if (allSelected) return 'All';
      if (total === 1) return this.filters.tbu[0];
      return `${total} Selected`;
    },
    WastingDisplayText() {
      const total = this.filters.bbtb.length;
      const allSelected = total === this.bbtbOptions.length;

      if (allSelected) return 'All';
      if (total === 1) return this.filters.bbtb[0];
      return `${total} Selected`;
    },
    stagnanDisplayText() {
      const total = this.filters.stagnan.length;
      const allSelected = total === this.stagnanOptions.length;

      if (allSelected) return 'All';
      if (total === 1) return this.filters.stagnan[0];
      return `${total} Selected`;
    },
    intervesiDisplayText() {
      const total = this.filters.intervensi.length;
      const allSelected = total === this.intervensiOptions.length;

      if (allSelected) return 'All';
      if (total === 1) return this.filters.intervensi[0];
      return `${total} Selected`;
    },
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
      //const { tipe, status } = this.$route.query
      //console.log('data dari dashboard:', tipe, status)

      // Copy semua children
      this.filteredData = [...this.children]

      // Terapkan filter dari chart
      /* if (tipe && status) {
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
      } */


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
    },
  },
}
</script>

<style scoped>
  .dropdown-menu .form-check-label {
    white-space: normal !important;
    word-break: break-word;
  }

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
