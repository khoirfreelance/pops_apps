<template>
  <div class="wrapper">
    <!-- 🔄 Spinner Overlay -->
    <transition name="fade">
      <div
        v-if="isLoading"
        class="spinner-overlay d-flex justify-content-center align-items-center"
      >
        <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem">
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
        'sidebar-expanded': !isCollapsed,
      }"
    >
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

      <!-- Main Content -->
      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <div class="py-4 container-fluid">
          <!-- Welcome Card -->
          <Welcome />

          <!-- Judul Laporan -->
          <div class="text-center mt-4">
            <div class="bg-additional text-white py-1 px-4 d-inline-block rounded-top">
              <div class="title mb-0 text-capitalize fw-bold" style="font-size: 23px">
                Laporan Status Kesehatan Ibu Hamil Desa {{ kelurahan }} Periode {{ thisMonth }}
              </div>
            </div>
          </div>

          <!-- Filter Form -->
          <div class="bg-light border rounded-bottom shadow-sm px-4 py-3 d-none d-md-block">
            <div class="row g-3 align-items-end">
              <!-- Dropdown Fiter Dynamic -->
              <div
                v-for="(filter, key) in filterOptions"
                :key="key"
                class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 position-relative"
              >
                <label v-if="filter.filter" class="text-primary">
                  {{ filter.filter }}
                </label>

                <div class="dropdown w-100">
                  <button
                    class="form-select text-start d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                    data-bs-display="static"
                  >
                    <span class="truncate-span text-muted" :title="getFilterDisplayText(key)">
                      {{ getFilterDisplayText(key) }}
                    </span>
                  </button>

                  <ul class="dropdown-menu w-100 p-2" style="max-height: 260px; overflow-y: auto">
                    <li
                      v-for="item in filter.options"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="`${key}-${item}`"
                        :value="item"
                        v-model="filters[key]"
                      />
                      <label class="form-check-label w-100 text-truncate" :for="`${key}-${item}`">{{
                        item
                      }}</label>
                    </li>

                    <li>
                      <hr class="dropdown-divider" />
                    </li>

                    <li class="d-flex justify-content-between px-2">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-primary fw-semibold"
                        @click="selectAll(key)"
                      >
                        Pilih Semua
                      </button>
                      <button
                        type="button"
                        class="btn btn-sm small btn-outline-secondary fw-semibold"
                        @click="clearAll(key)"
                      >
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Desa -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-6">
                <label class="text-primary">Desa:</label>
                <select v-model="kelurahan" class="form-select text-muted" disabled>
                  <option :value="kelurahan">{{ kelurahan }}</option>
                </select>
              </div>

              <!-- Posyandu -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-2 col-xl-2">
                <label class="text-primary">Filter Lokasi:</label>
                <select
                  v-model="filters.posyandu"
                  class="form-select text-muted"
                  @change="handlePosyanduChange"
                >
                  <option class="text-muted" value="">Pilih Posyandu</option>
                  <option v-for="item in posyanduList" :key="item.id" :value="item.nama_posyandu">
                    {{ item.nama_posyandu }}
                  </option>
                </select>
              </div>

              <!-- RW -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-2 col-xl-2">
                <select v-model="filters.rw" class="form-select text-muted" :disabled="rwReadonly">
                  <option class="text-muted" value="">Pilih RW</option>
                  <option v-for="rw in rwList" :key="rw" :value="rw">{{ rw }}</option>
                </select>
              </div>

              <!-- RT -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-2 col-xl-2">
                <select v-model="filters.rt" class="form-select text-muted" :disabled="rtReadonly">
                  <option class="text-muted" value="">Pilih RT</option>
                  <option v-for="rt in rtList" :key="rt" :value="rt">{{ rt }}</option>
                </select>
              </div>

              <!-- Periode -->
              <div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6">
                <label class="text-primary d-block">Filter Periode:</label>

                <div class="d-flex gap-2">
                  <select v-model="filters.periodeAwal" class="form-select text-muted w-100">
                    <option value="">Awal</option>
                    <option v-for="p in periodeOptions" :key="p" :value="p">{{ p }}</option>
                  </select>

                  <select v-model="filters.periodeAkhir" class="form-select text-muted w-100">
                    <option value="">Akhir</option>
                    <option v-for="p in periodeOptions" :key="p" :value="p">{{ p }}</option>
                  </select>
                </div>
              </div>

              <!-- Tombol -->
              <div class="col-12 d-flex justify-content-end mt-3">
                <button class="btn btn-gradient fw-semibold me-2" @click="applyFilter">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>
                <button class="btn btn-secondary fw-semibold" @click="resetFilter">
                  <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
              </div>
            </div>
          </div>

          <!-- Mobile Filter -->
          <!-- Floating Button -->
          <button
            class="btn btn-primary filter-float-btn rounded-pill shadow-lg px-4 py-2"
            @click="mobileFilterOpen = true"
          >
            <i class="bi bi-funnel"></i> Filter
          </button>

          <div class="filter-mobile-panel d-md-none" :class="{ open: mobileFilterOpen }">
            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="fw-bold">Filter</h5>
              <button class="btn btn-light" @click="mobileFilterOpen = false">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>

            <!-- === FORM FILTER MOBILE === -->
            <div class="row g-3 align-items-end">
              <!-- Dropdown Fiter Dynamic -->
              <div
                v-for="(filter, key) in filterOptions"
                :key="key"
                class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 position-relative"
              >
                <label v-if="filter.filter" class="text-primary">
                  {{ filter.filter }}
                </label>

                <div class="dropdown w-100">
                  <button
                    class="form-select text-start d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                    data-bs-display="static"
                  >
                    <span class="truncate-span text-muted" :title="getFilterDisplayText(key)">
                      {{ getFilterDisplayText(key) }}
                    </span>
                  </button>

                  <ul class="dropdown-menu w-100 p-2" style="max-height: 260px; overflow-y: auto">
                    <li
                      v-for="item in filter.options"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="`${key}-${item}`"
                        :value="item"
                        v-model="filters[key]"
                      />
                      <label class="form-check-label w-100 text-truncate" :for="`${key}-${item}`">{{
                        item
                      }}</label>
                    </li>

                    <li>
                      <hr class="dropdown-divider" />
                    </li>

                    <li class="d-flex justify-content-between px-2">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-primary fw-semibold"
                        @click="selectAll(key)"
                      >
                        Pilih Semua
                      </button>
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary fw-semibold"
                        @click="clearAll(key)"
                      >
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Desa -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-6">
                <label class="text-primary">Desa:</label>
                <select v-model="kelurahan" class="form-select text-muted" disabled>
                  <option :value="kelurahan">{{ kelurahan }}</option>
                </select>
              </div>

              <!-- Posyandu -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-2 col-xl-2">
                <label class="text-primary">Filter Lokasi:</label>
                <select
                  v-model="filters.posyandu"
                  class="form-select text-muted"
                  @change="handlePosyanduChange"
                >
                  <option class="text-muted" value="">Pilih Posyandu</option>
                  <option v-for="item in posyanduList" :key="item.id" :value="item.nama_posyandu">
                    {{ item.nama_posyandu }}
                  </option>
                </select>
              </div>

              <!-- RW -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-2 col-xl-2">
                <select v-model="filters.rw" class="form-select text-muted" :disabled="rwReadonly">
                  <option class="text-muted" value="">Pilih RW</option>
                  <option v-for="rw in rwList" :key="rw" :value="rw">{{ rw }}</option>
                </select>
              </div>

              <!-- RT -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-2 col-xl-2">
                <select v-model="filters.rt" class="form-select text-muted" :disabled="rtReadonly">
                  <option class="text-muted" value="">Pilih RT</option>
                  <option v-for="rt in rtList" :key="rt" :value="rt">{{ rt }}</option>
                </select>
              </div>

              <!-- Periode -->
              <div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6">
                <label class="text-primary d-block">Filter Periode:</label>

                <div class="d-flex gap-2">
                  <select v-model="filters.periodeAwal" class="form-select text-muted w-100">
                    <option value="">Awal</option>
                    <option v-for="p in periodeOptions" :key="p" :value="p">{{ p }}</option>
                  </select>

                  <select v-model="filters.periodeAkhir" class="form-select text-muted w-100">
                    <option value="">Akhir</option>
                    <option v-for="p in periodeOptions" :key="p" :value="p">{{ p }}</option>
                  </select>
                </div>
              </div>

              <!-- Tombol -->
              <div class="col-12 d-flex justify-content-end mt-3">
                <button class="btn btn-gradient fw-semibold me-2" @click="applyFilter">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>
                <button class="btn btn-secondary fw-semibold" @click="resetFilter">
                  <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
              </div>
            </div>
          </div>

          <!-- Ringkasan Statistik -->
          <div class="text-center mt-3">
            <div class="ringkasan-header text-success mb-3">Ringkasan Statistik</div>
          </div>

          <div class="container-fluid my-4">
            <div class="row">
              <div class="row justify-content-center g-0">
                <div
                  v-for="(item, index) in kesehatan"
                  :key="index"
                  class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mb-3 no-col-padding"
                >
                  <div
                    class="card border-0 rounded-2 overflow-hidden custom-card-size shadow"
                    :class="`border-start border-4 border-${item.color}`"
                  >
                    <div class="card-body position-relative">
                      <h5
                        class="fw-bold mb-1"
                        :class="{
                          'text-center w-100': item.title === 'Total Bumil',
                          'text-start': item.title !== 'Total Bumil',
                        }"
                      >
                        {{ item.title }}
                      </h5>
                      <h3
                        class="fw-bold mb-0 custom-value1"
                        :class="[
                          `text-${item.color}`,
                          item.title === 'Total Bumil' ? 'text-center w-100' : 'text-start',
                        ]"
                      >
                        {{ item.value }}
                      </h3>
                      <p
                        class="position-absolute bottom-0 end-0 mb-1 me-2 small custom-percent1"
                        :class="`text-${item.color}`"
                      >
                        {{ item.percent }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Table and detail Section -->
          <div class="container-fluid mt-4">
            <h5 class="table-name text-success">Data Ibu Hamil</h5>
            <div class="row mt-4">
              <div :class="selectedBumil ? 'col-md-8' : 'col-md-12'">
                <div class="card bg-light p-2">
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center">
                      <thead class="table-success small">
                        <tr>
                          <th
                            @click="sortBy('nama')"
                            class="cursor-pointer align-middle text-center"
                          >
                            Nama
                            <SortIcon :field="'nama'" />
                          </th>
                          <th
                            @click="sortBy('anemia')"
                            class="cursor-pointer align-middle text-center"
                          >
                            Anemia
                            <SortIcon :field="'anemia'" />
                          </th>
                          <th
                            style="width: 100px"
                            @click="sortBy('berisiko')"
                            class="cursor-pointer align-middle text-center"
                          >
                            Kehamilan Berisiko
                            <SortIcon :field="'berisiko'" />
                          </th>
                          <th
                            style="width: 60px"
                            @click="sortBy('kek')"
                            class="cursor-pointer align-middle text-center"
                          >
                            KEK
                            <SortIcon :field="'kek'" />
                          </th>
                          <th
                            @click="sortBy('intervensi')"
                            class="cursor-pointer align-middle text-center"
                          >
                            Intervensi
                            <SortIcon :field="'intervensi'" />
                          </th>
                          <th @click="sortBy('rw')" class="cursor-pointer align-middle text-center">
                            RW
                            <SortIcon :field="'rw'" />
                          </th>
                          <th @click="sortBy('rt')" class="cursor-pointer align-middle text-center">
                            RT
                            <SortIcon :field="'rt'" />
                          </th>
                          <th
                            style="width: 100px"
                            @click="sortBy('usia')"
                            class="cursor-pointer align-middle text-center"
                          >
                            Usia (Tahun)
                            <SortIcon :field="'usia'" />
                          </th>
                          <th
                            style="width: 100px"
                            @click="sortBy('tanggal_pemeriksaan_terakhir')"
                            class="cursor-pointer align-middle text-center"
                          >
                            Tanggal Pemeriksaan
                            <SortIcon :field="'tanggal_pemeriksaan_terakhir'" />
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="bumil in paginatedData" :key="bumil.id" class="small">
                          <td class="text-start">
                            <a
                              href="#"
                              @click.prevent="showDetail(bumil)"
                              class="fw-semibold text-decoration-none text-primary"
                            >
                              <u>{{ bumil.nama }}</u>
                            </a>
                          </td>
                          <td>
                            <span
                              v-if="bumil.anemia === 'Anemia'"
                              class="badge bg-danger text-white"
                              >Ya</span
                            >
                            <span v-else>{{ bumil.anemia }}</span>
                          </td>
                          <td>
                            <span
                              v-if="bumil.risiko === 'Berisiko'"
                              class="badge bg-danger text-white"
                              >Ya</span
                            >
                            <span v-else>{{ bumil.risiko }}</span>
                          </td>
                          <td>
                            <span
                              v-if="bumil.kek === 'Ya' || bumil.kek === 'KEK'"
                              class="badge bg-danger text-white"
                              >Ya</span
                            >
                            <span v-else>{{ bumil.kek }}</span>
                          </td>
                          <td>
                            <span
                              v-if="bumil.intervensi === 'Ya'"
                              class="badge bg-danger text-white"
                              >Ya</span
                            >
                            <span v-else>{{ bumil.intervensi }}</span>
                          </td>
                          <td>{{ bumil.rw }}</td>
                          <td>{{ bumil.rt }}</td>
                          <td>{{ bumil.usia }}</td>
                          <td>
                            {{
                              new Date(bumil.tanggal_pemeriksaan_terakhir)
                                .toISOString()
                                .slice(0, 10)
                            }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <nav>
                    <ul id="responsive-pagination" class="pagination justify-content-center">
                      <!-- Prev -->
                      <li class="page-item" :class="{ disabled: currentPage === 1 }">
                        <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">
                          &laquo;
                        </a>
                      </li>

                      <!-- Nomor halaman -->
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
                        >
                          {{ page }}
                        </a>
                        <span v-else class="page-link">...</span>
                      </li>

                      <!-- Next -->
                      <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                        <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">
                          &raquo;
                        </a>
                      </li>
                    </ul>
                  </nav>
                </div>
              </div>

              <!-- DETAIL DATA -->
              <div class="col-md-4" v-if="selectedBumil">
                <div
                  v-if="selectedBumil"
                  class="card shadow-sm p-4 text-center small position-relative"
                >
                  <!-- Tombol Close -->
                  <button
                    type="button"
                    class="btn-close position-absolute top-0 end-0 m-3"
                    aria-label="Close"
                    @click="selectedBumil = null"
                  ></button>

                  <!-- Nama dan Identitas -->
                  <h5 class="fw-bold text-dark mb-1">{{ selectedBumil.nama }}</h5>
                  <p class="text-muted mb-0 text-capitalize">
                    {{ selectedBumil.kelurahan || '-' }}
                  </p>
                  <p class="text-muted">{{ selectedBumil.kecamatan || '-' }}</p>

                  <!-- Badge Status Gizi -->
                  <div class="mb-3">
                    <span
                      class="badge px-3 py-2 small"
                      :class="{
                        'bg-danger text-dark': ['Kehamilan Berisiko'].includes(
                          selectedBumil.status_gizi,
                        ),
                        'bg-warning text-dark': ['KEK'].includes(selectedBumil.status_gizi),
                        'bg-success': selectedBumil.status_gizi === 'Normal',
                      }"
                    >
                      {{ selectedBumil.status_gizi }}
                    </span>
                  </div>

                  <!-- Riwayat Penimbangan -->
                  <h6 class="fw-bold text-start text-secondary mt-2">Riwayat Pemeriksaan</h6>
                  <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center">
                      <thead class="table-light">
                        <tr>
                          <th rowspan="2">Tanggal</th>
                          <th colspan="3">Status</th>
                        </tr>
                        <tr>
                          <th>Anemia</th>
                          <th>KEK</th>
                          <th>Risiko</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="(r, i) in (selectedBumil.riwayat_pemeriksaan || []).slice(-3)"
                          :key="i"
                        >
                          <td>{{ r.tanggal }}</td>
                          <td>
                            <span
                              class="badge"
                              :class="r.anemia === 'Ya' ? 'bg-danger' : 'text-dark'"
                            >
                              {{ r.anemia }}
                            </span>
                          </td>
                          <td>
                            <span
                              class="badge"
                              :class="r.kek === 'KEK' ? 'bg-danger' : 'text-dark'"
                            >
                              {{ r.kek }}
                            </span>
                          </td>
                          <td>
                            <span
                              class="badge"
                              :class="r.risiko === 'Tinggi' ? 'bg-danger' : 'text-dark'"
                            >
                              {{ r.risiko }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Riwayat Intervensi -->
                  <h6 class="fw-bold text-start text-secondary mt-3">
                    Riwayat Intervensi / Bantuan
                  </h6>
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
                        <tr v-for="(i, idx) in selectedBumil.riwayat_intervensi || []" :key="idx">
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
              <div class="col-md-12 mt-4" v-if="selectedBumil">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden position-relative">
                  <!-- Tombol Close -->
                  <button
                    class="btn-close position-absolute top-0 end-0 m-3"
                    aria-label="Close"
                    @click="selectedBumil = null"
                  ></button>

                  <!-- Header -->
                  <div class="card shadow rounded">
                    <!-- HEADER CARD -->
                    <div class="card-header bg-danger text-white text-center rounded-top py-3">
                      <h5 class="fw-bold mb-0 text-white">
                        {{ selectedBumil.nama }}
                      </h5>
                      <p class="mb-0 small text-white">
                        {{ selectedBumil.usia }} Tahun - {{ selectedBumil.risiko }}
                      </p>
                    </div>

                    <!-- BODY CARD -->
                    <div class="card-body">
                      <!-- Isi Profil Ibu Hamil -->
                    </div>
                  </div>

                  <!-- Tabs -->
                  <div class="p-3">
                    <ul
                      class="nav nav-pills justify-content-center mb-4 flex-wrap gap-2"
                      id="bumilDetailTab"
                      role="tablist"
                    >
                      <li class="nav-item" role="presentation">
                        <button
                          class="nav-link active"
                          id="tab-profile-bumil"
                          data-bs-toggle="tab"
                          data-bs-target="#tab-pane-profile-bumil"
                          type="button"
                          role="tab"
                        >
                          <i class="bi bi-person-badge me-1"></i> Profil Ibu Hamil
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button
                          class="nav-link"
                          id="tab-kehamilan"
                          data-bs-toggle="tab"
                          data-bs-target="#tab-pane-kehamilan"
                          type="button"
                          role="tab"
                        >
                          <i class="bi bi-clipboard-heart me-1"></i> Data Kehamilan
                        </button>
                      </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="bumilDetailTabContent">
                      <!-- Profile Anak -->
                      <div
                        class="tab-pane fade show active"
                        id="tab-pane-profile-bumil"
                        role="tabpanel"
                      >
                        <div class="row g-3">
                          <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100">
                              <h6 class="fw-bold mb-3 text-danger">Identitas Ibu Hamil</h6>
                              <table class="table table-borderless mb-0">
                                <tbody>
                                  <tr>
                                    <td class="fw-semibold" style="width: 120px">Nama</td>
                                    <td>:</td>
                                    <td>{{ selectedBumil.nama }}</td>
                                  </tr>
                                  <tr>
                                    <td class="fw-semibold">Usia</td>
                                    <td>:</td>
                                    <td>{{ selectedBumil.usia }} Tahun</td>
                                  </tr>
                                  <tr>
                                    <td class="fw-semibold">Nama Suami</td>
                                    <td>:</td>
                                    <td>{{ selectedBumil.nama_suami }}</td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100">
                              <h6 class="fw-bold mb-3 text-danger">Alamat</h6>
                              <table class="table table-borderless mb-0">
                                <tbody>
                                  <tr>
                                    <td class="fw-semibold" style="width: 120px">Alamat</td>
                                    <td>:</td>
                                    <td>
                                      {{ selectedBumil.provinsi }}, {{ selectedBumil.kota }},
                                      {{ selectedBumil.kecamatan }}
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="fw-semibold">Desa</td>
                                    <td>:</td>
                                    <td>{{ selectedBumil.kelurahan }}</td>
                                  </tr>
                                  <tr>
                                    <td class="fw-semibold">RW</td>
                                    <td>:</td>
                                    <td>0{{ selectedBumil.rw }}</td>
                                  </tr>
                                  <tr>
                                    <td class="fw-semibold">RT</td>
                                    <td>:</td>
                                    <td>0{{ selectedBumil.rt }}</td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Data Kelahiran -->
                      <div class="tab-pane fade" id="tab-pane-kehamilan" role="tabpanel">
                        <div class="card bg-light border-0 shadow-sm p-3">
                          <h6 class="fw-bold mb-3 text-danger">Data Kehamilan</h6>
                          <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                              <thead>
                                <tr class="small text-center align-middle">
                                  <th style="width: 150px">Tanggal</th>
                                  <th>Kehamilan ke</th>
                                  <th>Risiko</th>
                                  <th>TB <span class="fw-normal">(cm)</span></th>
                                  <th>BB <span class="fw-normal">(kg)</span></th>
                                  <th>Lila <span class="fw-normal">(cm)</span></th>
                                  <th>KEK</th>
                                  <th>Hb</th>
                                  <th>Anemia</th>
                                  <th>Terpapar Asap Rokok</th>
                                  <th>Mendapat Bantuan Sosial</th>
                                  <th>Jamban Sehat</th>
                                  <th>Sumber Air Bersih</th>
                                  <th>Keluhan</th>
                                  <th>Intervensi</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr
                                  v-for="(item, i) in selectedBumil.kehamilan"
                                  :key="'kehamilan-' + i"
                                  class="small text-center"
                                >
                                  <td>{{ item.tgl_pendampingan }}</td>
                                  <td>{{ item.kehamilan_ke }}</td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="item.risiko === 'Tinggi' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.risiko || '-' }}
                                    </span>
                                  </td>
                                  <td>{{ item.tb }}</td>
                                  <td>{{ item.bb }}</td>
                                  <td>{{ item.lila }}</td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="item.kek === 'KEK' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.kek || '-' }}
                                    </span>
                                  </td>
                                  <td>{{ item.hb }}</td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="item.anemia === 'Ya' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.anemia || '-' }}
                                    </span>
                                  </td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="item.asap_rokok === '1' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.asap_rokok === '0' ? 'Ya' : 'Tidak' }}
                                    </span>
                                  </td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="
                                        item.bantuan_sosial === '1' ? 'bg-danger' : 'text-dark'
                                      "
                                    >
                                      {{ item.bantuan_sosial === '0' ? 'Ya' : 'Tidak' }}
                                    </span>
                                  </td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="item.jamban_sehat === '0' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.jamban_sehat === '0' ? 'Tidak' : 'Ya' }}
                                    </span>
                                  </td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="
                                        item.sumber_air_bersih === '0' ? 'bg-danger' : 'text-dark'
                                      "
                                    >
                                      {{ item.sumber_air_bersih === '0' ? 'Tidak' : 'Ya' }}
                                    </span>
                                  </td>
                                  <td>{{ item.keluhan }}</td>
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
        <CopyRight class="mt-auto" />
      </div>
    </div>
  </div>
</template>

<script>
import * as XLSX from 'xlsx'
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import axios from 'axios'
import { ref, computed } from 'vue'
import Welcome from '@/components/Welcome.vue'

const API_PORT = 8000
const { protocol, hostname } = window.location
const baseURL = `${protocol}//${hostname}:${API_PORT}`

const monthNames = {
  Januari: '01',
  Februari: '02',
  Maret: '03',
  April: '04',
  Mei: '05',
  Juni: '06',
  Juli: '07',
  Agustus: '08',
  September: '09',
  Oktober: '10',
  November: '11',
  Desember: '12',
}

function formatYearMonth(input) {
  if (!input) return ''
  const [monthName, year] = input.split(' ')
  const month = monthNames[monthName]
  return month && year ? `${year}-${month}` : ''
}

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Pregnancy',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, Welcome },
  data() {
    return {
      isLoading: true,
      isCollapsed: false,
      username: '',
      today: '',
      thisMonth: '',
      kelurahan: '',
      windowWidth: window.innerWidth,
      configCacheKey: 'site_config_cache',
      kesehatan: [],
      bumil: [],
      filteredBumil: [],
      totalBumil: 0,
      selectedBumil: null,
      posyanduList: [],
      rwList: [],
      rtList: [],
      rwReadonly: true,
      rtReadonly: true,
      filters: {
        kelurahan: '',
        posyandu: '',
        rt: '',
        rw: '',
        status: [],
        usia: [],
        intervensi: [],
        periodeAwal: '',
        periodeAkhir: '',
      },
      periodeOptions: [],
      mobileFilterOpen: false,
    }
  },
  setup() {
    const bumil = ref([])
    const searchQuery = ref('')
    const currentPage = ref(1)
    const perPage = ref(10)
    const sortKey = ref('')
    const sortDir = ref('asc')
    const filteredData = ref([])

    const applySearch = () => {
      const query = searchQuery.value.toLowerCase()
      filteredData.value = bumil.value.filter((c) =>
        Object.values(c).some((v) => String(v).toLowerCase().includes(query)),
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

    const totalPages = computed(() => Math.ceil(filteredData.value.length / perPage.value))

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
      visiblePages,
    }
  },
  computed: {
    filterOptions() {
      return {
        status: {
          label: 'Status',
          placeholder: 'Pilih Status',
          options: ['KEK', 'Anemia', 'Berisiko'],
          filter: 'Filter status ibu hamil:',
        },
        intervensi: {
          label: 'Intervensi',
          placeholder: 'Pilih Intervensi',
          options: [
            'MBG',
            'KIE',
            'Bansos',
            'PMT',
            'Bantuan Lainnya',
            'Belum mendapatkan intervensi',
          ],
          filter: '',
        },
        usia: {
          label: 'Usia (Tahun)',
          placeholder: 'Pilih Usia',
          options: ['<20', '20-30', '30-40', '>40'],
          filter: '',
        },
      }
    },
    intervesiDisplayText() {
      const total = this.filters.intervensi.length
      const allSelected = total === this.intervensiOptions.length

      if (allSelected) return 'All'
      if (total === 1) return this.filters.intervensi[0]
      return `${total} Selected`
    },
  },
  methods: {
    downloadRiwayat() {
      if (!this.selectedBumil) {
        alert('Silakan pilih ibu hamil terlebih dahulu.')
        return
      }

      const ibu = this.selectedBumil

      // Buat workbook dan sheet
      const wb = XLSX.utils.book_new()
      const ws_data = []

      // ============================
      // 1. DATA IBU HAMIL
      // ============================
      ws_data.push(['DATA IBU HAMIL'])
      ws_data.push(['Nama', ibu.nama || '-'])
      ws_data.push(['Usia (Tahun)', ibu.usia || '-'])
      ws_data.push(['Nama Suami', ibu.nama_suami || '-'])
      ws_data.push([
        'Alamat',
        `${ibu.provinsi || '-'}, ${ibu.kota || '-'}, ${ibu.kecamatan || '-'}`,
      ])
      ws_data.push(['Desa/Kelurahan', ibu.kelurahan || '-'])
      ws_data.push(['RW', ibu.rw || '-'])
      ws_data.push(['RT', ibu.rt || '-'])
      ws_data.push(['Status Risiko', ibu.risiko || '-'])
      ws_data.push(['']) // spacer

      // ============================
      // 2. RIWAYAT PEMERIKSAAN
      // ============================
      ws_data.push(['RIWAYAT PEMERIKSAAN'])
      ws_data.push(['Tanggal', 'Anemia', 'KEK', 'Risiko'])
      ;(ibu.riwayat_pemeriksaan || []).forEach((r) => {
        ws_data.push([r.tanggal || '-', r.anemia || '-', r.kek || '-', r.risiko || '-'])
      })

      ws_data.push(['']) // spacer

      // ============================
      // 3. RIWAYAT INTERVENSI
      // ============================
      ws_data.push(['RIWAYAT INTERVENSI'])
      ws_data.push(['Tanggal', 'Kader', 'Intervensi'])
      ;(ibu.riwayat_intervensi || []).forEach((i) => {
        ws_data.push([i.tanggal || '-', i.kader || '-', i.intervensi || '-'])
      })

      ws_data.push(['']) // spacer

      // ============================
      // 4. DATA KEHAMILAN DETAIL
      // ============================
      ws_data.push(['DATA KEHAMILAN DETAIL'])
      ws_data.push([
        'Tanggal',
        'Kehamilan ke',
        'Risiko',
        'TB (cm)',
        'BB (kg)',
        'LILA (cm)',
        'KEK',
        'Hb',
        'Anemia',
        'Asap Rokok',
        'Bansos',
        'Jamban Sehat',
        'Sumber Air Bersih',
        'Keluhan',
        'Intervensi',
      ])
      ;(ibu.kehamilan || []).forEach((k) => {
        ws_data.push([
          k.tgl_pendampingan || '-',
          k.kehamilan_ke || '-',
          k.risiko || '-',
          k.tb || '-',
          k.bb || '-',
          k.lila || '-',
          k.kek || '-',
          k.hb || '-',
          k.anemia || '-',
          k.asap_rokok === '1' ? 'Ya' : k.asap_rokok === '0' ? 'Tidak' : '-',
          k.bantuan_sosial === '1' ? 'Ya' : k.bantuan_sosial === '0' ? 'Tidak' : '-',
          k.jamban_sehat === '1' ? 'Ya' : k.jamban_sehat === '0' ? 'Tidak' : '-',
          k.sumber_air_bersih === '1' ? 'Ya' : k.sumber_air_bersih === '0' ? 'Tidak' : '-',
          k.keluhan || '-',
          k.intervensi || '-',
        ])
      })

      // ============= SIMPAN EXCEL =============
      const ws = XLSX.utils.aoa_to_sheet(ws_data)
      XLSX.utils.book_append_sheet(wb, ws, 'Riwayat')

      XLSX.writeFile(wb, `Riwayat_IbuHamil_${ibu.nama}.xlsx`)
    },
    async loadPregnancy() {
      try {
        const res = await axios.get(`${baseURL}/api/pregnancy`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          params: this.filters,
        })

        const data = res.data?.data || []

        // ✅ Ambil semua nama posyandu dari seluruh riwayat_pemeriksaan
        const allPosyandu = data.flatMap((ibu) =>
          (ibu.riwayat_pemeriksaan || []).map((r) => r.posyandu).filter(Boolean),
        )

        // ✅ Buat list unik
        const uniquePosyandu = [...new Set(allPosyandu)]
        this.posyanduList = uniquePosyandu.map((nama, idx) => ({
          id: idx + 1,
          nama_posyandu: nama,
        }))

        // ✅ Format data bumil
        this.bumil = data.map((item) => {
          const lastCheck = item.riwayat_pemeriksaan?.at(-1) // pemeriksaan terakhir
          return {
            id: item.nik_ibu,
            nama: item.nama_ibu || '-',
            usia: item.usia_ibu || '-',
            nama_suami: item.nama_suami || '-',
            risiko: item.status_risiko_usia || '-',
            rw: item.rw || '-',
            rt: item.rt || '-',
            // ambil nilai terakhir dari pemeriksaan
            tanggal_pemeriksaan_terakhir: lastCheck?.tanggal_pemeriksaan_terakhir || '-',
            berat_badan: lastCheck?.berat_badan || '-',
            tinggi_badan: lastCheck?.tinggi_badan || '-',
            imt: lastCheck?.imt || '-',
            kadar_hb: lastCheck?.kadar_hb || '-',
            lila: lastCheck?.lila || '-',
            anemia: lastCheck?.status_gizi_hb || '-',
            kek: lastCheck?.status_gizi_lila || '-',
            nama_posyandu: lastCheck?.posyandu || '-',
            intervensi: item.intervensi?.kategori || '-',
          }
        })

        //console.log(this.bumil);

        // ✅ Set filtered dan total
        this.filteredData = [...this.bumil]
        this.totalBumil = this.bumil.length
      } catch (e) {
        console.error('Gagal ambil data pregnancy:', e)
        this.bumil = []
        this.filteredData = []
        this.posyanduList = []
      }
    },
    async showDetail(bumil) {
      try {
        this.isLoading = true
        //console.log('detail:', bumil);

        const nik = bumil.id

        const res = await axios.get(`${baseURL}/api/pregnancy/${nik}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })
        const data = res.data
        // Ambil riwayat kehamilan
        const riwayatKehamilan = data.kehamilan || []

        // Ambil record terakhir
        const lastKehamilan = riwayatKehamilan.length
          ? riwayatKehamilan[riwayatKehamilan.length - 1]
          : {}

        this.selectedBumil = {
          ...data.ibu,
          riwayat_pemeriksaan: data.riwayat_pemeriksaan || [],
          riwayat_intervensi: data.riwayat_intervensi || [],
          kehamilan: data.kehamilan || [],
          risiko: lastKehamilan.risiko || '-', // ← ini tambahan
        }

        // optional console debug
        //console.log('selectedBumil:', this.selectedBumil);
      } catch (error) {
        console.error('Gagal load detail bumil:', error)
        this.selectedBumil = null
      } finally {
        this.isLoading = false
      }
    },
    getFilterDisplayText(key) {
      const selected = this.filters[key] || []
      const options = this.filterOptions[key]?.options || []
      const total = selected.length

      if (total === 0) return this.filterOptions[key].placeholder
      if (total === options.length) return 'Semua'
      if (total === 1) return selected[0]
      return `${total} Dipilih`
    },
    handleResize() {
      this.windowWidth = window.innerWidth
      this.isCollapsed = this.windowWidth < 992
    },
    selectAll(key) {
      this.filters[key] = [...this.filterOptions[key].options]
    },
    clearAll(key) {
      this.filters[key] = []
    },
    async applyFilter() {
      ;(await this.loadPregnancy(), await this.hitungStatusKesehatan())
    },
    async resetFilter() {
      Object.keys(this.filters).forEach((k) => {
        if (Array.isArray(this.filters[k])) this.filters[k] = []
        else this.filters[k] = ''
      })
      ;(await this.loadPregnancy(), await this.hitungStatusKesehatan())
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    getTodayDate() {
      const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
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
      return `${hari[now.getDay()]}, ${now.getDate()} ${bulan[now.getMonth()]} ${now.getFullYear()}`
    },
    getThisMonth() {
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
      return `${bulan[now.getMonth()]} ${now.getFullYear()}`
    },
    async getWilayahUser() {
      try {
        const res = await axios.get(`${baseURL}/api/user/region`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })
        const wilayah = res.data
        //console.log('✅ getWilayahUser ->', wilayah)
        this.kelurahan = wilayah.kelurahan || '-'
        this.filters.kelurahan = this.kelurahan
      } catch (e) {
        console.error('❌ getWilayahUser error:', e)
        this.kelurahan = '-'
      }
    },
    handlePosyanduChange() {
      const pos = this.filters.posyandu

      if (!pos) {
        this.rwList = []
        this.rtList = []
        this.rwReadonly = true
        this.rtReadonly = true
        this.filteredBumil = [...this.bumil] // tampilkan semua
        return
      }

      // ✅ Gunakan field yang benar
      const filteredBumil = this.bumil.filter((b) => b.posyandu === pos)

      // 🔹 Ambil daftar RW & RT unik
      const rwSet = new Set(filteredBumil.map((b) => b.rw).filter(Boolean))
      const rtSet = new Set(filteredBumil.map((b) => b.rt).filter(Boolean))

      this.rwList = Array.from(rwSet)
      this.rtList = Array.from(rtSet)

      // 🔹 Aktifkan dropdown RW & RT
      this.rwReadonly = false
      this.rtReadonly = false

      // 🔹 Update data tabel
      this.filteredBumil = filteredBumil
    },
    handleRwChange() {
      const pos = this.filters.posyandu
      const rw = this.filters.rw

      if (!rw) {
        this.rtList = []
        this.rtReadonly = true
        return
      }

      const filteredBumil = this.bumil.filter((c) => c.posyandu === pos && c.rw === rw)

      const rtSet = new Set(filteredBumil.map((c) => c.rt).filter(Boolean))
      this.rtList = Array.from(rtSet)
      this.rtReadonly = false
    },
    async hitungStatusKesehatan() {
      try {
        const params = {
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          usia: this.filters.usia || [], // tambahan
          intervensi: this.filters.intervensi || [], // tambahan
          periodeAwal: formatYearMonth(this.filters.periodeAwal),
          periodeAkhir: formatYearMonth(this.filters.periodeAkhir),
        }

        // Status bisa multiple (checkbox)
        if (this.filters.status && this.filters.status.length > 0) {
          params.status = this.filters.status
        }

        const res = await axios.get(`${baseURL}/api/pregnancy/status`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          params,
        })

        const data = res.data
        const total = data.total || 0

        //this.totalBumil = total;
        this.kesehatan = data.counts.map((item) => ({
          title: item.title,
          value: item.value,
          percent:
            item.title != 'Total Bumil'
              ? total > 0
                ? ((item.value / total) * 100).toFixed(1) + '%'
                : '0%'
              : '',
          color: item.color,
        }))
      } catch (e) {
        console.error('❌ hitungStatusKesehatan error:', e)
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
        const label = `${bulan[d.getMonth()]} ${d.getFullYear()}`
        result.push(label)
      }

      this.periodeOptions = result
    },
  },
  created() {
    const storedEmail = localStorage.getItem('userEmail')
    this.username = storedEmail
      ? storedEmail
          .split('@')[0]
          .replace(/[._]/g, ' ')
          .split(' ')
          .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
          .join(' ')
      : 'User'

    this.today = this.getTodayDate()
    this.thisMonth = this.getThisMonth()
  },
  async mounted() {
    this.isLoading = true
    try {
      await this.getWilayahUser()
      await this.loadPregnancy() // kasih await juga kalau ini async
      await this.hitungStatusKesehatan()
      this.generatePeriodeOptions()
      this.handleResize()
      window.addEventListener('resize', this.handleResize)
      this.filteredBumil = this.bumil
    } catch (err) {
      console.error('Error loading data:', err)
    } finally {
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
* {
  font-size: 16px;
}

* {
  word-wrap: break-word;
  white-space: normal;
}

.filter-wrapper {
  position: relative;
  /* biar ikut alur layout */
  z-index: 0;
  /* pastikan di bawah sidebar */
  margin-top: -30px !important;
  width: 97%;
  border-radius: 0.75rem;
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

/* Header */
.table-modern th {
  background-color: var(--bs-primary) !important;
  /* primary */
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
  background-color: #6c757d;
  /* secondary */
  border-color: #6c757d;
  color: #fff;
}

/* BEGIN Judul */
/* Font Title */
@import url('https://fonts.googleapis.com/css2?family=Neuton:wght@400;700&display=swap');

/* Judul besar */
.title-kesehatan {
  font-family: 'Neuton', serif;
  font-weight: 700;
  font-size: 20px;
  text-align: center;
  margin: 0;
  line-height: 1.3;
}

/* Span di dalam judul — ukuran lebih kecil */
.title-kesehatan span {
  font-size: 20px;
  font-weight: 700;
}

/* Container header hijau — jangan full lebar card */
.report-title-container {
  width: 100%;
  display: flex;
  justify-content: center;
}

/* Inner untuk header hijau — beri max-width biar tidak full */
.report-title-inner {
  background-color: var(--bs-additional, #6ea58a);
  width: 80%;
  padding: 10px;
  border-radius: 6px;
  display: flex;
  justify-content: center;
}

/* END Judul */

/* Import Google Font */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

/* Hilangkan padding kolom bootstrap */
.no-col-padding {
  padding-left: 0 !important;
  padding-right: 0 !important;
}

/* Ukuran card */
.custom-card-size {
  height: 109px !important;
  width: 98% !important;
  margin-left: 0 !important;
  margin-right: 0 !important;
}

/* Padding card */
.custom-card-padding {
  padding: 5px !important;
}

/* VALUE */
.custom-value1 {
  font-family: 'Poppins', sans-serif !important;
  font-size: 20px !important;
  font-weight: 700 !important;
}

/* PERCENT */
.custom-percent1 {
  font-family: 'Poppins', sans-serif !important;
  font-size: 12px !important;
  font-weight: 600 !important;
}

.dropdown-menu .form-check-label {
  white-space: normal !important;
  word-break: break-word;
}

/* Default pagination */
#responsive-pagination .page-link {
  font-size: 11px; /* ukuran font default */
  padding: 0.2rem 0.5rem; /* padding sama untuk semua */
  min-width: 2rem; /* agar semua box sama lebar */
  text-align: center; /* center text/simbol */
  display: flex; /* untuk center vertical + horizontal */
  align-items: center;
  justify-content: center;
  border-radius: 0.25rem; /* opsional, biar rapi */
}

.filter-float-btn {
  position: fixed;
  bottom: 15px;
  left: 15px;
  z-index: 2000;
  display: none;
}

.ringkasan-header,
.table-name {
  font-family: 'Neuton', serif;
  font-weight: bold;
  font-size: 24px;
}

@media (max-width: 768px) {
  .filter-float-btn {
    display: block;
  }

  /* Panel filter mobile */
  .filter-mobile-panel {
    position: fixed;
    left: 0;
    right: 0;
    bottom: -100%;
    height: 85%;
    background: #fff;
    border-radius: 16px 16px 0 0;
    box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.15);
    z-index: 2001;
    padding: 15px;
    overflow-y: auto;
    transition: bottom 0.35s ease;
  }

  .filter-mobile-panel.open {
    bottom: 0;
  }
}

/* Responsive untuk layar kecil */
@media (max-width: 767.98px) {
  #responsive-pagination .page-link {
    font-size: 0.8rem;
    padding: 0.2rem 0.5rem;
  }

  #responsive-pagination .page-item {
    margin: 0 0px; /* margin antar item dikurangi */
  }
}

/* Active / Disabled styling */
#responsive-pagination .page-item.active .page-link {
  background-color: #0d6efd;
  color: white;
  border-color: #0d6efd;
}

.selected-text,
.form-select.text-truncate {
  display: block;
  width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.truncate-span {
  display: inline-block;
  flex: 1;
  min-width: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: default; /* atau hapus baris ini */
}

#responsive-pagination .page-item.disabled .page-link {
  pointer-events: none;
  opacity: 0.5;
}
</style>
