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

      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <!-- Content -->
        <div class="py-4 container-fluid">
          <!-- Welcome Card -->
          <Welcome />

          <!-- Judul Laporan -->
          <div class="text-center mt-4">
            <div class="bg-additional text-white py-1 px-4 d-inline-block rounded-top">
              <div class="title mb-0 text-capitalize fw-bold" style="font-size: 23px">
                Laporan Status Kesehatan Calon Pengantin {{ kelurahan }} periode {{ periodeTitle }}
              </div>
            </div>
          </div>

          <!-- Filter Form -->
          <div class="bg-light border rounded-bottom shadow-sm px-4 py-3 d-none d-md-block">
            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <div
                v-for="(filter, key) in filterOptions"
                :key="key"
                class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12 position-relative"
              >
                <label v-if="filter.filter" class="text-primary">
                  {{ filter.filter }}
                </label>
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                  >
                    <span class="text-muted">{{ getFilterDisplayText(key) }}</span>
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
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex justify-content-between px-2">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-primary fw-semibold small"
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

              <!-- Kelurahan/Desa -->
              <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12">
                <label class="form-label fs-md-1 text-primary">Kel/Desa</label>
                <select
                  class="form-select"
                  v-if="isAdmin"
                  v-model="filters.kelurahan_id"
                  @change="handleRegionChange"
                >
                  <option value="">Pilih Kel/Desa</option>

                  <optgroup
                    v-for="group in listKelurahan"
                    :key="group.label"
                    :label="group.label"
                  >
                    <option
                      v-for="opt in group.options"
                      :key="opt.id"
                      :value="opt.id"
                    >
                      {{ opt.label }}
                    </option>
                  </optgroup>
                </select>

                <select v-else v-model="filters.kelurahan" class="form-select text-muted small uniform-input" disabled>
                  <option :value="filters.kelurahan" class="small">{{ filters.kelurahan }}</option>
                </select>

              </div>
              <!-- <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12">
                <label for="filter" class="text-primary">Filter Lokasi:</label>
                <select v-model="kelurahan" class="form-select text-muted" disabled>
                  <option :value="kelurahan">{{ this.filters.kelurahan }}</option>
                </select>
              </div> -->

              <!-- Posyandu -->
              <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12">
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
              <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-6">
                <select v-model="filters.rw" class="form-select text-muted" :disabled="rwReadonly">
                  <option class="text-muted" value="">Pilih RW</option>
                  <option v-for="rw in rwList" :key="rw" :value="rw">{{ rw }}</option>
                </select>
              </div>

              <!-- RT -->
              <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-6">
                <select v-model="filters.rt" class="form-select text-muted" :disabled="rtReadonly">
                  <option class="text-muted" value="">Pilih RT</option>
                  <option v-for="rt in rtList" :key="rt" :value="rt">{{ rt }}</option>
                </select>
              </div>

              <!-- Periode -->
              <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 text-center">
                <label for="filter" class="text-primary"> Filter Periode:</label>
                <div class="d-flex justify-content-between gap-2">
                  <select v-model="filters.periodeAwal" class="form-select text-muted">
                    <option value=" ">Awal</option>
                    <option v-for="p in periodeOptions" :key="p" :value="p">{{ p }}</option>
                  </select>
                  <select v-model="filters.periodeAkhir" class="form-select text-muted">
                    <option value=" ">Akhir</option>
                    <option v-for="p in periodeOptions" :key="p" :value="p">{{ p }}</option>
                  </select>
                </div>
              </div>

              <!-- Terapkan -->
              <div class="d-flex justify-content-end gap-3">
                <button type="submit" class="btn btn-gradient fw-semibold">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>

                <button type="button" class="btn btn-secondary fw-semibold" @click="resetFilter">
                  <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
              </div>
            </form>
          </div>

          <!-- Mobile Filter -->
          <!-- Floating Button -->
          <button
            class="btn btn-primary filter-float-btn rounded-pill shadow-lg px-4 py-2"
            @click="mobileFilterOpen = true"
          >
            <i class="bi bi-funnel"></i> Filter
          </button>

          <!-- FILTER MOBILE SLIDE PANEL -->
          <div class="filter-mobile-panel d-md-none" :class="{ open: mobileFilterOpen }">
            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="fw-bold">Filter</h5>
              <button class="btn btn-light" @click="mobileFilterOpen = false">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>

            <!-- === FORM FILTER MOBILE === -->
            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <div
                v-for="(filter, key) in filterOptions"
                :key="key"
                class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12 position-relative"
              >
                <label v-if="filter.filter" class="text-primary">
                  {{ filter.filter }}
                </label>
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                    data-bs-display="static"
                  >
                    <span class="text-muted">{{ getFilterDisplayText(key) }}</span>
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
                      <label class="form-check-label w-100" :for="`${key}-${item}`">{{
                        item
                      }}</label>
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex justify-content-between px-2">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-primary fw-semibold small"
                        data-bs-display="static"
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

              <!-- Kelurahan/Desa -->
              <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12">
                <label for="filter" class="text-primary">Filter Lokasi:</label>
                <select v-model="kelurahan" class="form-select text-muted" disabled>
                  <option :value="kelurahan">{{ kelurahan }}</option>
                </select>
              </div>

              <!-- Posyandu -->
              <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12">
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
              <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-6">
                <select v-model="filters.rw" class="form-select text-muted" :disabled="rwReadonly">
                  <option class="text-muted" value="">Pilih RW</option>
                  <option v-for="rw in rwList" :key="rw" :value="rw">{{ rw }}</option>
                </select>
              </div>

              <!-- RT -->
              <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-6">
                <select v-model="filters.rt" class="form-select text-muted" :disabled="rtReadonly">
                  <option class="text-muted" value="">Pilih RT</option>
                  <option v-for="rt in rtList" :key="rt" :value="rt">{{ rt }}</option>
                </select>
              </div>

              <!-- Periode -->
              <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 text-center">
                <label for="filter" class="text-primary"> Filter Periode:</label>
                <div class="d-flex justify-content-between gap-2">
                  <select v-model="filters.periodeAwal" class="form-select text-muted">
                    <option value="">Awal</option>
                    <option v-for="p in periodeOptions" :key="p" :value="p">{{ p }}</option>
                  </select>
                  <select v-model="filters.periodeAkhir" class="form-select text-muted">
                    <option value="">Akhir</option>
                    <option v-for="p in periodeOptions" :key="p" :value="p">{{ p }}</option>
                  </select>
                </div>
              </div>

              <!-- BUTTONS -->
              <div class="d-flex justify-content-end gap-3">
                <button type="submit" class="btn btn-gradient fw-semibold">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>

                <button type="button" class="btn btn-secondary fw-semibold" @click="resetFilter">
                  <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
              </div>
            </form>
          </div>

          <div class="text-center mt-3">
            <div class="ringkasan-header fw-bold text-success mb-3">
              Ringkasan Statistik
            </div>
          </div>

          <!-- Ringkasan Statistik -->
          <div class="container-fluid my-4">
            <div class="row">
              <div class="row justify-content-center">
                <div
                  v-for="(item, index) in kesehatan"
                  :key="index"
                  class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-12 mb-3 no-col-padding justify-content-center align-items-center"
                >
                  <div
                    class="card border-0 rounded-3 overflow-hidden custom-card-size shadow"
                    :class="index < 3 ? `border-start border-4 border-${item.color}` : ''"
                    style="width: 108%"
                  >
                    <!-- Card untuk index 0-2 (tampilan normal dengan border samping) -->
                    <div v-if="index < 3" class="card-body position-relative">
                      <!-- TITLE -->
                      <h5 class="fw-bold mb-1" style="font-size: 16px;">{{ item.title }}</h5>

                      <!-- VALUE -->
                      <h3 class="fw-bold mb-0" :class="`text-${item.color}`" style="font-size: 20px;">
                        {{ item.value }}
                      </h3>

                      <!-- PERCENT -->
                      <p
                        class="position-absolute bottom-0 end-0 mb-1 me-2 small"
                        :class="`text-${item.color}`"
                        style="font-size: 16px;"
                      >
                        {{ item.percent }}
                      </p>
                    </div>

                    <!-- Card untuk index 3+ (tampilan centered tanpa border samping) -->
                    <div v-else class="card-body pt-3 text-center">
                      <!-- TITLE -->
                      <h5 class="fw-bold text-muted mb-3" style="font-size: 16px;">{{ item.title }}</h5>

                      <!-- VALUE -->
                      <h1 class="fw-bold mb-0 fs-1" :class="`text-${item.color}`">
                        {{ item.value }}
                      </h1>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Table and detail Section -->
          <div class="container-fluid mt-4">
            <h5 class="table-name text-success">Data Calon Pengantin</h5>
            <div class="row mt-1">
              <div :class="selectedCatin ? 'col-md-8' : 'col-md-12'">
                <div class="card shadow-sm">
                  <div class="card-body">
                    <!-- Search + Rows per page control -->
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                      <input
                        type="text"
                        class="form-control form-control-sm"
                        style="max-width: 300px;"
                        placeholder="Cari Nama Calon Pengantin"
                        v-model="searchQuery"
                        @input="applySearch"
                      >
                    </div>

                    <!-- Easy Data Table -->
                    <easy-data-table
                      :headers="headersCatin"
                      :items="filteredCatin"
                      :search-value="searchQuery"
                      :rows-per-page="perPage"
                      header-text-direction="center"
                      table-class-name="my-custom-table"
                      header-class-name="my-custom-header"
                      show-index
                      alternating
                      border-cell
                      :loading="isLoading"
                    >
                      <!-- Custom column: Nama Perempuan (with link) -->
                      <template #item-nama_perempuan="item">
                        <a
                          href="#"
                          @click.prevent="showDetail(item)"
                          class="fw-semibold text-decoration-underline text-primary"
                        >
                          {{ item.nama_perempuan }}
                        </a>
                      </template>

                      <!-- Custom column: Anemia -->
                      <template #item-anemia="{ pemeriksaan_terakhir }">
                        <span
                          v-if="pemeriksaan_terakhir?.status_hb === 'Anemia'"
                          class="badge bg-danger text-white px-3 py-2"
                        >
                          {{ pemeriksaan_terakhir.status_hb }}
                        </span>
                        <span v-else>{{ pemeriksaan_terakhir?.status_hb || '-' }}</span>
                      </template>

                      <!-- Custom column: KEK -->
                      <template #item-kek="{ pemeriksaan_terakhir }">
                        <span
                          v-if="pemeriksaan_terakhir?.status_kek === 'KEK'"
                          class="badge bg-danger text-white px-3 py-2"
                        >
                          {{ pemeriksaan_terakhir.status_kek }}
                        </span>
                        <span v-else>{{ pemeriksaan_terakhir?.status_kek || '-' }}</span>
                      </template>

                      <!-- Custom column: Risiko Usia -->
                      <template #item-risiko="{ status_risiko }">
                        <span
                          v-if="status_risiko === 'Berisiko'"
                          class="badge bg-danger text-white px-3 py-2"
                        >
                          {{ status_risiko }}
                        </span>
                        <span v-else>{{ status_risiko }}</span>
                      </template>

                      <!-- Custom column: Tanggal Kunjungan -->
                      <template #item-tgl_kunjungan="{ tgl_kunjungan }">
                        {{ formatDate(tgl_kunjungan) }}
                      </template>

                      <!-- Custom column: Tanggal Menikah -->
                      <template #item-tgl_menikah="{ tgl_menikah }">
                        {{ formatDate(tgl_menikah) }}
                      </template>

                      <!-- Custom column: BB Perempuan -->
                      <template #item-bb="{ pemeriksaan_terakhir }">
                        {{ pemeriksaan_terakhir?.berat_perempuan || '-' }}
                      </template>

                      <!-- Custom column: TB Perempuan -->
                      <template #item-tb="{ pemeriksaan_terakhir }">
                        {{ pemeriksaan_terakhir?.tinggi_perempuan || '-' }}
                      </template>

                      <!-- Custom column: Lila Perempuan -->
                      <template #item-lila="{ pemeriksaan_terakhir }">
                        {{ pemeriksaan_terakhir?.lila_perempuan || '-' }}
                      </template>

                      <!-- Custom column: Hb Perempuan -->
                      <template #item-hb="{ pemeriksaan_terakhir }">
                        {{ pemeriksaan_terakhir?.hb_perempuan || '-' }}
                      </template>

                      <!-- Custom column: Riwayat Penyakit -->
                      <template #item-riwayat_penyakit="{ pemeriksaan_terakhir }">
                        {{ pemeriksaan_terakhir?.riwayat_penyakit ? 'Ya' : 'Tidak' }}
                      </template>

                      <!-- Custom column: Jamban Sehat -->
                      <template #item-jamban_sehat="{ pemeriksaan_terakhir }">
                        {{ pemeriksaan_terakhir?.menggunakan_jamban ? 'Ya' : 'Tidak' }}
                      </template>

                      <!-- Custom column: Sumber Air Bersih -->
                      <template #item-sumber_air_bersih="{ pemeriksaan_terakhir }">
                        {{ pemeriksaan_terakhir?.sumber_air_bersih ? 'Ya' : 'Tidak' }}
                      </template>

                      <!-- Empty state -->
                      <template #empty-message>
                        <div class="text-center py-4">
                          <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                          <p class="text-muted mt-2">Tidak ada data calon pengantin yang ditemukan</p>
                        </div>
                      </template>

                      <!-- Loading state -->
                      <template #loading>
                        <div class="text-center py-4">
                          <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                          </div>
                          <p class="text-muted mt-2">Memuat data...</p>
                        </div>
                      </template>
                    </easy-data-table>

                    <div class="d-flex justify-content-end">
            <button
              class="btn btn-sm btn-outline-primary p-2 mt-3 w-auto"
              @click="exportDataCatinExcel"
            >
              <i class="bi bi-file-earmark-excel text-primary me-1"></i>
              Export
            </button>
          </div>

                  </div>
                </div>
              </div>

              <!-- DETAIL DATA -->
              <div class="col-md-4" v-if="selectedCatin">
                <div
                  v-if="selectedCatin"
                  class="card shadow-sm p-4 text-center position-relative"
                >
                  <!-- Tombol Close -->
                  <button
                    type="button"
                    class="btn-close position-absolute top-0 end-0 m-3"
                    aria-label="Close"
                    @click="selectedCatin = null"
                  ></button>

                  <!-- Nama dan Identitas -->
                  <h2 class="fw-bold text-dark mb-1">
                    {{ selectedCatin.nama_perempuan }} / {{ selectedCatin.nama_laki }}
                  </h2>
                  <p style="font-size: 14px" class="text-muted mb-0 text-capitalize">
                    {{ selectedCatin.kelurahan || '-' }}
                  </p>
                  <p style="font-size: 14px" class="text-muted">{{ selectedCatin.kecamatan || '-' }}</p>

                  <!-- Badge Status Gizi -->
                  <div class="mb-3">
                    <span
                      class="badge px-3 py-2"
                      :class="{
                        'bg-danger text-white': ['Berisiko'].includes(selectedCatin.status_risiko),
                        'bg-success': selectedCatin.status_risiko === 'Normal'
                      }"
                    >
                      {{ selectedCatin.status_risiko }}
                    </span>
                  </div>

                  <!-- Riwayat Penimbangan -->
                  <h6 class="fw-bold text-start text-secondary mt-2">Riwayat Status 3 bulan terakhir</h6>
                  <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center" style="font-size: 12px !important">
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
                          v-for="(r, i) in (selectedCatin.pemeriksaan_terakhir || []).slice(-3)"
                          :key="i"
                        >
                          <td>{{ formatDate(r.tanggal_pemeriksaan) }}</td>

                          <!-- Hb / Anemia -->
                          <td>
                            <span class="badge"
                              :class="r.status_hb === 'Anemia' ? 'bg-danger' : 'text-dark'"
                            >
                              {{ r.status_hb }}
                            </span>
                          </td>

                          <!-- KEK -->
                          <td>
                            <span class="badge"
                              :class="r.status_kek === 'KEK' ? 'bg-danger' : 'text-dark'"
                            >
                              {{ r.status_kek }}
                            </span>
                          </td>

                          <!-- Risiko -->
                          <td>
                            <span class="badge"
                              :class="selectedCatin.status_risiko === 'Berisiko' ? 'bg-danger' : 'text-dark'"
                            >
                              {{ selectedCatin.status_risiko }}
                            </span>
                          </td>
                        </tr>
                      </tbody>

                    </table>
                  </div>

                  <!-- Tombol Download -->
                  <button
                  style="background-color: #0d8cff"
                    class="btn btn-primary rounded-pill px-4 mt-2 fw-semibold"
                    @click="downloadRiwayat"
                  >
                    Download Riwayat
                  </button>
                </div>
              </div>

              <!-- Detail Riwayat Anak -->
              <div class="col-md-12 mt-4" v-if="selectedCatin" id="detailSection">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden position-relative">
                  <!-- Tombol Close -->
                  <button
                    class="btn-close position-absolute top-0 end-0 m-3"
                    aria-label="Close"
                    @click="selectedCatin = null"
                  ></button>

                  <!-- Header -->
                  <div class="bg-danger text-white p-4 text-center rounded-top">
                    <h4 class="fw-bold mb-0">
                      {{ selectedCatin.nama_perempuan }} / {{ selectedCatin.nama_laki }}
                    </h4>
                    <p class="text-white mb-0 small">
                      {{ selectedCatin.usia_perempuan }} Tahun - {{ selectedCatin.status_risiko }}
                    </p>
                  </div>

                  <!-- Tabs -->
                  <div class="p-3">
                    <ul
                      class="nav nav-pills justify-content-center mb-4 flex-wrap gap-2"
                      id="catinDetailTab"
                      role="tablist"
                    >
                      <li class="nav-item" role="presentation">
                        <button
                          class="nav-link active"
                          id="tab-profile-catin"
                          data-bs-toggle="tab"
                          data-bs-target="#tab-pane-profile-catin"
                          type="button"
                          role="tab"
                        >
                          <i class="bi bi-person-badge me-1"></i> Profil Calon Pengantin
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
                          <i class="bi bi-clipboard-heart me-1"></i> Pendampingan TPK
                        </button>
                      </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="catinDetailTabContent">
                      <!-- Profile Anak -->
                      <div
                        class="tab-pane fade show active"
                        id="tab-pane-profile-catin"
                        role="tabpanel"
                      >
                        <div class="row g-3">
                          <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100">
                              <h6 class="tab-pane-sub-title mb-3 text-danger">Identitas Pasangan</h6>
                              <table class="table table-borderless mb-0">
                                <tbody>
                                  <tr>
                                    <td class="fw-semibold" style="width: 120px">Nama</td>
                                    <td>:</td>
                                    <td>
                                      {{ selectedCatin.nama_perempuan }} -
                                      {{ selectedCatin.nama_laki }}
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="fw-semibold">Usia</td>
                                    <td>:</td>
                                    <td>
                                      {{ selectedCatin.usia_perempuan }} Tahun -
                                      {{ selectedCatin.usia_laki }} Tahun
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="fw-semibold">Pekerjaan</td>
                                    <td>:</td>
                                    <td>
                                      {{ selectedCatin.pekerjaan_perempuan }} -
                                      {{ selectedCatin.pekerjaan_laki }}
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-3 h-100">
                              <h6 class="tab-pane-sub-title mb-3 text-danger">Alamat</h6>
                              <table class="table table-borderless mb-0">
                                <tbody>
                                  <tr>
                                    <td class="fw-semibold" style="width: 120px">Alamat</td>
                                    <td>:</td>
                                    <td>
                                      {{ selectedCatin.provinsi }}, {{ selectedCatin.kota }},
                                      {{ selectedCatin.kecamatan }}
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="fw-semibold">Desa</td>
                                    <td>:</td>
                                    <td>{{ selectedCatin.kelurahan }}</td>
                                  </tr>
                                  <tr>
                                    <td class="fw-semibold">RW</td>
                                    <td>:</td>
                                    <td>0{{ selectedCatin.rw }}</td>
                                  </tr>
                                  <tr>
                                    <td class="fw-semibold">RT</td>
                                    <td>:</td>
                                    <td>0{{ selectedCatin.rt }}</td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Data Pemeriksaan -->
                      <div class="tab-pane fade" id="tab-pane-kehamilan" role="tabpanel">
                        <div class="card bg-light border-0 shadow-sm p-3">
                          <h6 class="tab-pane-sub-title mb-3 text-danger">Riwayat Pemeriksaan</h6>
                          <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle" style="font-size: 12px">
                              <thead class="table-success">
                                <tr class="text-center align-middle">
                                  <th style="width: 150px">Tanggal</th>
                                  <th>Kader</th>
                                  <th>Risiko</th>
                                  <th>TB <span class="fw-normal">(cm)</span></th>
                                  <th>BB <span class="fw-normal">(kg)</span></th>
                                  <th>Lila <span class="fw-normal">(cm)</span></th>
                                  <th>KEK</th>
                                  <th>Hb</th>
                                  <th>Anemia</th>
                                  <th>Riwayat Penyakit</th>
                                  <th>Jamban Sehat</th>
                                  <th>Sumber Air Bersih</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr
                                  v-for="(item, i) in selectedCatin.riwayat"
                                  :key="'catin-' + i"
                                  class="text-center"
                                >
                                  <td>{{ this.formatDate(item.tanggal) }}</td>
                                  <td>{{ item.kader }}</td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="
                                        item.status_risiko === 'Berisiko' ? 'bg-danger' : 'text-dark'
                                      "
                                    >
                                      {{ item.status_risiko || '-' }}
                                    </span>
                                  </td>
                                  <td>{{ item.tb }}</td>
                                  <td>{{ item.bb }}</td>
                                  <td>{{ item.lila }}</td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="item.status_kek === 'KEK' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.status_kek || '-' }}
                                    </span>
                                  </td>
                                  <td>{{ item.hb }}</td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="
                                        item.status_hb === 'Anemia' ? 'bg-danger' : 'text-dark'
                                      "
                                    >
                                      {{ item.status_hb || '-' }}
                                    </span>
                                  </td>
                                  <td>
                                    {{ item.riwayat_penyakit }}
                                  </td>
                                  <td>
                                    <i
                                      v-if="item.menggunakan_jamban == true"
                                      class="fa fa-check text-success"
                                    ></i>
                                    <span v-else>-</span>
                                  </td>
                                  <td>
                                    <i
                                      v-if="item.sumber_air_bersih == true"
                                      class="fa fa-check text-success"
                                    ></i>
                                    <span v-else>-</span>
                                  </td>
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
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import axios from 'axios'
import { ref, computed, nextTick } from 'vue'
import Welcome from '@/components/Welcome.vue'
import EasyDataTable from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'
import { exportExcel } from "@/utils/exportExcel";
import { mapDataCatinToExcel } from "@/mappers/dataCatinMapper";
import { mapFilterToExcel } from "@/mappers/mapFilterToExcel";
import { eventBus } from '@/eventBus'

// PORT backend kamu
const API_PORT = 8000

// Bangun base URL dari window.location
const { protocol, hostname } = window.location
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Bride',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, Welcome, EasyDataTable },
  data() {
    return {
      listKelurahan: [],
      headersCatin: [
        { text: 'Nama Perempuan', value: 'nama_perempuan', sortable: true, width: 150 },
        { text: 'Nama Laki-laki', value: 'nama_laki', sortable: true, width: 150 },
        { text: 'Anemia', value: 'anemia', sortable: true, width: 100 },
        { text: 'KEK', value: 'kek', sortable: true, width: 80 },
        { text: 'Risiko Usia', value: 'risiko', sortable: true, width: 120 },
        { text: 'Usia P', value: 'usia_perempuan', sortable: true, width: 80 },
        { text: 'Usia L', value: 'usia_laki', sortable: true, width: 80 },
        { text: 'Kerja P', value: 'pekerjaan_perempuan', sortable: true, width: 120 },
        { text: 'Kerja L', value: 'pekerjaan_laki', sortable: true, width: 120 },
        { text: 'Posyandu', value: 'posyandu', sortable: true },
        { text: 'RW', value: 'rw', sortable: true, width: 60 },
        { text: 'RT', value: 'rt', sortable: true, width: 60 },
        { text: 'Tgl Kunjungan', value: 'tgl_kunjungan', sortable: true, width: 120 },
        { text: 'Tgl Menikah', value: 'tgl_menikah', sortable: true, width: 120 },
        { text: 'BB (P)', value: 'bb', sortable: true, width: 80 },
        { text: 'TB (P)', value: 'tb', sortable: true, width: 80 },
        { text: 'Lila (P)', value: 'lila', sortable: true, width: 80 },
        { text: 'Hb (P)', value: 'hb', sortable: true, width: 80 },
        { text: 'Riwayat Penyakit', value: 'riwayat_penyakit', sortable: true, width: 120 },
        { text: 'Jamban Sehat', value: 'jamban_sehat', sortable: true, width: 120 },
        { text: 'Air Bersih', value: 'sumber_air_bersih', sortable: true, width: 100 }
      ],
      periodeTitle:'',
      periodeAwalExportData: '',
      periodeAkhirExportData: '',
      desaExportData: '',
      isLoading: true,
      isCollapsed: false,
      username: '',
      dataRaw: [],
      today: '',
      thisMonth: '',
      kelurahan: '',
      windowWidth: window.innerWidth,
      configCacheKey: 'site_config_cache',
      kesehatan: [],
      bride: [], // data utama calon pengantin
      filteredCatin: [],
      totalCatin: 0,
      periodeOptions: [],
      selectedCatin: null,
      posyanduList: [],
      rwList: [],
      rtList: [],
      rwReadonly: true,
      rtReadonly: true,
      ref:'p',
      filters: {
        provinsi: '',
        kota: '',
        kecamatan: '',
        kelurahan: '',
        kelurahan_id: '',
        posyandu: '',
        rt: '',
        rw: '',
        status: [],
        usia: [],
        periodeAwal: '',
        periodeAkhir: '',
      },
      mobileFilterOpen: false,
    }
  },
  setup() {
    const searchQuery = ref('')
    const currentPage = ref(1)
    const perPage = ref(10)
    const sortKey = ref('')
    const sortDir = ref('asc')
    const filteredCatin = ref([])

    const applySearch = () => {
      const query = searchQuery.value.toLowerCase()
      filteredCatin.value = window.catin.filter((c) =>
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
      filteredCatin.value.sort((a, b) => {
        if (a[key] < b[key]) return sortDir.value === 'asc' ? -1 : 1
        if (a[key] > b[key]) return sortDir.value === 'asc' ? 1 : -1
        return 0
      })
    }



    const totalPages = computed(() => Math.ceil(filteredCatin.value.length / perPage.value))

    const paginatedData = computed(() => {
      const start = (currentPage.value - 1) * perPage.value
      const end = start + perPage.value
      return filteredCatin.value.slice(start, end)
    })

    const changePage = (page) => {
      if (page < 1 || page > totalPages.value) return
      currentPage.value = page
    }

    return {
      searchQuery,
      // eslint-disable-next-line vue/no-dupe-keys
      filteredCatin,
      currentPage,
      perPage,
      sortKey,
      sortDir,
      totalPages,
      paginatedData,
      applySearch,
      sortBy,
      changePage,
    }
  },
  computed: {
    role() {
      return localStorage.getItem('role')
    },
    isAdmin() {
      return this.role === 'Super Admin'
    },
    periodeLabel() {
      const awal = this.filters.periodeAwal
      const akhir = this.filters.periodeAkhir

      // ============================
      // DEFAULT: 12 bulan terakhir
      // ============================
      const now = new Date()

      const defaultAkhir = now.toLocaleString('id-ID', {
        month: 'long',
        year: 'numeric'
      })

      const defaultAwalDate = new Date(
        now.getFullYear(),
        now.getMonth() - 11, // mundur 11 bulan
        1
      )

      const defaultAwal = defaultAwalDate.toLocaleString('id-ID', {
        month: 'long',
        year: 'numeric'
      })

      // Jika tidak ada filter → pakai default range 12 bulan
      if (!awal || !akhir) {
        return `${defaultAwal} - ${defaultAkhir}`
      }

      // ============================
      // Jika ada filter periode
      // Format input: "Februari+2025"
      // ============================
      const awalLabel = awal.replace('+', ' ')
      const akhirLabel = akhir.replace('+', ' ')

      // Jika awal = akhir → hanya tampilkan 1
      if (awalLabel === akhirLabel) {
        return awalLabel
      }

      return `${awalLabel} - ${akhirLabel}`
    },
    visiblePages() {
    const pages = [];
    const maxVisible = 5; // jumlah maksimal tombol halaman yang muncul

    if (this.totalPages <= maxVisible) {
      // Jika halaman sedikit, tampilkan semua
      for (let i = 1; i <= this.totalPages; i++) pages.push(i);
      return pages;
    }

    pages.push(1); // halaman pertama

    if (this.currentPage > 3) pages.push("...");

    let start = Math.max(2, this.currentPage - 1);
    let end = Math.min(this.totalPages - 1, this.currentPage + 1);

    for (let i = start; i <= end; i++) pages.push(i);

    if (this.currentPage < this.totalPages - 2) pages.push("...");

    pages.push(this.totalPages); // halaman terakhir

    return pages;
  },
    filterOptions() {
      return {
        status: {
          label: 'Status',
          placeholder: 'Pilih Status',
          options: ['KEK', 'Anemia', 'Berisiko'],
          filter: 'Filter status Calon Pengantin:',
        },
        usia: {
          label: 'Usia (Tahun)',
          placeholder: 'Pilih Usia',
          options: ['< 20 Tahun', '20 - 34 Tahun', '>= 35 Tahun'],
          filter: '',
        },
      }
    },
  },
  created() {
    this.today = this.getTodayDate()
    this.thisMonth = this.getThisMonth()
  },
  methods: {
    handleRegionChange() {
      const idWilayah = this.filters.kelurahan_id

      // 🔁 DEFAULT / ALL
      if (!idWilayah) {
        this.filters.provinsi  = null
        this.filters.kota      = null
        this.filters.kecamatan = null
        this.filters.kelurahan = null

        this.kelurahan = 'Semua Desa'
        localStorage.removeItem('userWilayah')
        //localStorage.setItem('kelurahan_label', this.kelurahan)
        localStorage.removeItem('kelurahan_label')

        eventBus.emit('kelurahanChanged', null)

        // optional: load semua posyandu
        //this.fetchAllPosyandu?.()

        return
      }

      // 🔍 CARI DATA TERPILIH
      let selected = null
      for (const group of this.listKelurahan) {
        selected = group.options.find(opt => opt.id === idWilayah)
        if (selected) break
      }

      if (!selected) return

      // ✅ ASSIGN NORMAL
      this.filters.provinsi  = selected.provinsi
      this.filters.kota      = selected.kota
      this.filters.kecamatan = selected.kecamatan
      this.filters.kelurahan = selected.kelurahan

      this.kelurahan = `Desa ${selected.kelurahan}`

      localStorage.setItem('userWilayah', idWilayah)
      localStorage.setItem('kelurahan_label', selected.kelurahan)

      eventBus.emit('kelurahanChanged', selected.kelurahan)

      this.fetchPosyanduByWilayah(idWilayah)
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
      console.log('loadRegion');

      this.listKelurahan = res.data.data || []
    },
    async fetchPosyanduByWilayah(id_wilayah) {
      if (!id_wilayah) {
        console.warn('ID wilayah kosong, tidak bisa fetch posyandu')
        return
      }

      try {
        const res = await axios.get(`${baseURL}/api/posyandu/${id_wilayah}/wilayah`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        this.posyanduList = res.data?.data || res.data || []
        //console.log("Posyandu list:", this.posyanduList);
      } catch (error) {
        console.error('Gagal mengambil data posyandu:', error)
        this.posyanduList = []
      }
    },
    exportDataCatinExcel(){
      console.log(this.filteredCatin);

      let fileNameExport = '';
      if (this.periodeAwalExportData === '' || this.periodeAkhirExportData === '' ) {
        fileNameExport = `Status Risiko Calon Pengantin Desa ${this.desaExportData} 1 Tahun Terakhir.xlsx`;
      } else {
        fileNameExport = `Status Risiko Calon Pengantin Desa ${this.desaExportData} ${this.periodeAwalExportData} - ${this.periodeAkhirExportData}.xlsx`;
      }

      const excelData = mapDataCatinToExcel(this.filteredCatin);
      const filterSheetData = mapFilterToExcel(this.filters, 'Catin');

      exportExcel({
        fileName: fileNameExport,
        sheets: [
          {
            sheetName: 'Filter',
            data: filterSheetData
          },
          {
            sheetName: "Calon Pengantin",
            data: excelData,
          },

        ]
      });
    },
    formatDate(dateString) {
      if (!dateString) return '-'
      const date = new Date(dateString)
      const day = String(date.getDate()).padStart(2, '0')
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const year = date.getFullYear()
      return `${day}-${month}-${year}`
    },
    async showDetail(catin) {
      try {
        this.isLoading = true;

        const nik = catin.nik;

        const res = await axios.get(`${baseURL}/api/bride/${nik}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });

        const data = res.data;

        // BUILD selectedCatin lengkap
        this.selectedCatin = {
          ...data.catin,                      // identitas
          riwayat: data.riwayat || [],        // riwayat tabel bawah
          pemeriksaan_terakhir: data.pemeriksaan_terakhir || [] // tabel 3 kolom pertama
        };
        // tunggu DOM ter-render
        await nextTick()

        const el = document.getElementById('detailSection')
        if (el) {
          el.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          })
        }
        console.log("selected:", this.selectedCatin);

      } catch (error) {
        console.error("Gagal load detail Catin:", error);
        this.selectedCatin = null;
      } finally {
        this.isLoading = false;
      }
    },

    downloadRiwayat() {
      if (!this.selectedCatin) {
        alert('Silakan pilih calon pengantin terlebih dahulu.')
        return
      }

      const catin = this.selectedCatin

      // 🧩 Format CSV Header
      let csvContent = `DATA CALON PENGANTIN\n`
      csvContent += `Nama Perempuan,${catin.nama_perempuan || '-'}\n`
      csvContent += `NIK Perempuan,${catin.nik || '-'}\n`
      csvContent += `Nama Laki-laki,${catin.nama_laki || '-'}\n`
      csvContent += `Usia Perempuan,${catin.usia_perempuan || '-'}\n`
      csvContent += `Usia Laki-laki,${catin.usia_laki || '-'}\n`
      csvContent += `Posyandu,${catin.posyandu || '-'}\n`
      csvContent += `RW,${catin.rw || '-'}\n`
      csvContent += `RT,${catin.rt || '-'}\n`
      csvContent += `Kelurahan,${catin.kelurahan || '-'}\n`
      csvContent += `Kecamatan,${catin.kecamatan || '-'}\n`
      csvContent += `Kota,${catin.kota || '-'}\n`
      csvContent += `Provinsi,${catin.provinsi || '-'}\n`
      csvContent += `Tanggal Menikah,${this.formatDate(catin.tgl_menikah)}\n`
      csvContent += `Riwayat Penyakit,${catin.riwayat_penyakit || '-'}\n`
      csvContent += `Sumber Air Bersih,${catin.sumber_air_bersih || '-'}\n`
      csvContent += `Jamban Sehat,${catin.jamban_sehat || '-'}\n\n`

      // 🩺 Pemeriksaan Terakhir
      if (catin.pemeriksaan_terakhir) {
        const p = catin.pemeriksaan_terakhir
        csvContent += `PEMERIKSAAN TERAKHIR\n`
        csvContent += `Tanggal,${this.formatDate(p.tanggal_pendampingan)}\n`
        csvContent += `Petugas,${p.petugas || '-'}\n`
        csvContent += `Status HB,${p.status_hb || '-'}\n`
        csvContent += `Status KEK,${p.status_kek || '-'}\n`
        csvContent += `Status Risiko,${p.status_risiko || '-'}\n\n`
      }

      // 📋 Riwayat Pendampingan (riwayat_catin)
      if (Array.isArray(catin.riwayat) && catin.riwayat.length > 0) {
        csvContent += `RIWAYAT PEMERIKSAAN / PENDAMPINGAN\n`
        csvContent += `Tanggal,Petugas,Status HB,Status KEK,Status Risiko\n`
        catin.riwayat.forEach((r) => {
          csvContent += `${this.formatDate(r.tanggal_pendampingan)},${r.petugas || '-'},${r.status_hb || '-'},${r.status_kek || '-'},${r.status_risiko || '-'}\n`
        })
      } else {
        csvContent += `Tidak ada riwayat pemeriksaan\n`
      }

      // 💾 Buat file CSV dan download
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.setAttribute(
        'download',
        `Riwayat_${catin.nama_perempuan?.replace(/\s+/g, '_') || 'Catin'}.csv`,
      )
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      URL.revokeObjectURL(url)
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
      this.isLoading = true
      this.periodeAwalExportData = this.filters.periodeAwal;
      this.periodeAkhirExportData = this.filters.periodeAkhir;
      this.desaExportData = this.filters.kelurahan;
      try {
        this.periodeTitle = this.periodeLabel
        await this.loadBride()
        // await this.hitungStatusGizi()
      }catch(e){
        console.error(e);
      }finally{
        this.isLoading = false
      }
    },
    async resetFilter() {

      this.rwReadonly = true,
      this.rtReadonly = true,
      Object.keys(this.filters).forEach(k => {
        if (k=='kelurahan') this.filters[k]
        else if (Array.isArray(this.filters[k])) this.filters[k] = []
        else this.filters[k] = ''
      }),
      await this.loadBride()
      //await this.getWilayahUser()
      this.kelurahan = 'Semua Desa'
      localStorage.removeItem('userWilayah')
      //localStorage.setItem('kelurahan_label', this.kelurahan)
      localStorage.removeItem('kelurahan_label')

      eventBus.emit('kelurahanChanged', null)
      //await this.hitungStatusKesehatan()
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
        this.kelurahan = wilayah.kelurahan || 'Desa tidak diketahui'
        this.filters.kelurahan = wilayah.kelurahan || '-'
        this.kecamatan = wilayah.kecamatan || '-'
        this.kota = wilayah.kota || '-'
        this.provinsi = wilayah.provinsi || '-'
        this.filters.provinsi = wilayah.provinsi || ''
        this.filters.kota = wilayah.kota || ''
        this.filters.kecamatan = wilayah.kecamatan || ''
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
        this.filteredCatin = [...this.catin] // tampilkan semua
        return
      }

      // ✅ Gunakan field yang benar
      const filteredCatin = this.catin.filter((b) => b.posyandu === pos)

      // 🔹 Ambil daftar RW & RT unik
      const rwSet = new Set(filteredCatin.map((b) => b.rw).filter(Boolean))
      const rtSet = new Set(filteredCatin.map((b) => b.rt).filter(Boolean))

      this.rwList = Array.from(rwSet)
      this.rtList = Array.from(rtSet)

      // 🔹 Aktifkan dropdown RW & RT
      this.rwReadonly = false
      this.rtReadonly = false

      // 🔹 Update data tabel
      this.filteredCatin = filteredCatin
    },
    handleRwChange() {
      const pos = this.filters.posyandu
      const rw = this.filters.rw

      if (!rw) {
        this.rtList = []
        this.rtReadonly = true
        return
      }

      const filteredPregnancy = this.catin.filter((c) => c.posyandu === pos && c.rw === rw)

      const rtSet = new Set(filteredPregnancy.map((c) => c.rt).filter(Boolean))
      this.rtList = Array.from(rtSet)
      this.rtReadonly = false
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
    /* async hitungStatusKesehatan() {
      try {
        const params = {
          ref:this.ref || '',
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          usia: this.filters.usia || [],            // tambahan
          kelurahan: this.filters.kelurahan || '',// tambahan
          periodeAwal: this.filters.periodeAwal || '',
          periodeAkhir: this.filters.periodeAkhir || '',

        };
        //console.log(params);

        // Status bisa multiple (checkbox)
        if (this.filters.status && this.filters.status.length > 0) {
          params.status = this.filters.status
        }

        const res = await axios.get(`${baseURL}/api/bride/status`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          params,
        })

        const data = res.data
        const total = data.total || 0

        //this.totalCatin = total;
        this.kesehatan = data.counts.map((item) => ({
          title: item.title,
          value: item.value,
          percent: total > 0 ? ((item.value / total) * 100).toFixed(1) + '%' : '0%',
          color: item.color,
        }));

      } catch (e) {
        console.error('❌ hitungStatusKesehatan error:', e)
      }
    }, */
    async loadBride() {
      try {
        const res = await axios.get(`${baseURL}/api/bride`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          params: this.filters,
        })

        const data = res.data?.data || []

        // ✅ Ambil semua nama posyandu dari seluruh riwayat_pemeriksaan
        const allPosyandu = data.flatMap((catin) =>
          (catin.riwayat_pemeriksaan || []).map((r) => r.posyandu).filter(Boolean),
        )

        // ✅ Buat list unik
        const uniquePosyandu = [...new Set(allPosyandu)]
        this.posyanduList = uniquePosyandu.map((nama, idx) => ({
          id: idx + 1,
          nama_posyandu: nama,
        }))

        this.dataRaw = data;

        this.catin = data.map((item) => ({
          nik: item.nik_perempuan,
          status_risiko: item.status_risiko,
          nama_perempuan: item.nama_perempuan,
          nama_laki: item.nama_laki,
          usia_perempuan: item.usia_perempuan,
          usia_laki: item.usia_laki,
          pekerjaan_perempuan: item.kerja_perempuan,
          pekerjaan_laki: item.kerja_laki,
          posyandu: item.posyandu != '' ? item.posyandu : '-',
          provinsi: item.provinsi,
          kota: item.kota,
          kecamatan: item.kecamatan,
          kelurahan: item.kelurahan,
          rt: item.rt,
          rw: item.rw,
          tgl_kunjungan: item.tgl_kunjungan,
          tgl_menikah: item.tgl_pernikahan,
          riwayat_penyakit: item.riwayat_pemeriksaan.riwayat_penyakit,
          sumber_air_bersih: item.sumber_air_bersih == true ? 'Ya' : 'Tidak',
          jamban_sehat: item.jamban_sehat == true ? 'Ya' : 'Tidak',

          pemeriksaan_terakhir: item.riwayat_pemeriksaan?.[0] || null,

          // 🔥 FIX INI
          riwayat: item.riwayat_catin || [],
        }))


        // ✅ Set filtered dan total
        this.filteredCatin = [...this.catin]
        // 🔹 Ambil semua nama posyandu unik dari data catin
        const posSet = new Set(this.catin.map((c) => c.posyandu).filter(Boolean))
        this.posyanduList = Array.from(posSet).map((nama, i) => ({
          id: i + 1,
          nama_posyandu: nama,
        }))

        const total = res.data.total || 0

        this.kesehatan = res.data.counts.map((item) => ({
          title: item.title,
          value: item.value,
          percent:
            item.title != 'Total Catin'
              ? total > 0
                ? ((item.value / total) * 100).toFixed(1) + '%'
                : '0%'
              : '',
          color: item.color,
        }))

        this.currentPage = 1
      } catch (e) {
        console.error('Gagal ambil data pernikahan:', e);

      }
    },
  },
  async mounted() {
    this.isLoading = true
    try {
      if (this.isAdmin) {
        await this.loadRegion()
        this.kelurahan = 'Semua Desa'
      }else{
        await this.getWilayahUser()
        this.kelurahan = 'Desa '+ this.filters.kelurahan
        console.log('nama'+this.kelurahan);

        const label = this.filters.kelurahan
        localStorage.setItem('kelurahan_label', label)
        eventBus.emit('kelurahanChanged', label)
      }

      await Promise.all([

        this.periodeTitle = this.periodeLabel,
        this.loadBride(),
        this.handleResize(),
        this.generatePeriodeOptions(),

        window.addEventListener('resize', this.handleResize),
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
@import url('https://fonts.googleapis.com/css2?family=Neuton:wght@400;700&display=swap');
.filter-float-btn {
  position: fixed;
  bottom: 15px;
  left: 15px;
  z-index: 2000;
  display: none;
}

.selected-text,
.form-select.text-truncate {
  display: block;
  width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ringkasan-header,
.table-name {
  font-family: 'Neuton', serif;
  font-weight: bold;
  font-size: 24px;
}

.custom-card-size {
  height: 109px !important;
  width: 98% !important;
  margin-left: 0 !important;
  margin-right: 0 !important;
}

.no-col-padding {
  padding-left: 0 !important;
  padding-right: 0 !important;
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

/* ===== Pagination Responsive All Devices ===== */

/* MOBILE SMALL (≤576px) */
@media (max-width: 576px) {
  .pagination {
    flex-wrap: nowrap;
    overflow-x: auto;
    gap: 4px;
    padding-bottom: 6px;
  }

  .pagination .page-link {
    padding: 4px 8px;
    font-size: 0.65rem;
    min-width: 30px;
  }

  .pagination .page-item {
    flex-shrink: 0;
  }
}

/* TABLET (576px–768px) */
@media (min-width: 577px) and (max-width: 768px) {
  .pagination {
    flex-wrap: wrap;
    gap: 6px;
  }

  .pagination .page-link {
    padding: 6px 12px;
    font-size: 0.65rem;
  }
}

/* SMALL DESKTOP (768px–1200px) */
@media (min-width: 769px) and (max-width: 1200px) {
  .pagination {
    gap: 8px;
  }

  .pagination .page-link {
    padding: 7px 14px;
    font-size: 0.65rem;
  }
}

/* LARGE DESKTOP (≥1200px) */
@media (min-width: 1201px) {
  .pagination .page-link {
    padding: 8px 16px;
    font-size: 0.65rem;
  }
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

.tab-pane-sub-title {
  font-size: 17px;
  font-weight: 600;
}

.my-custom-table {
  --easy-table-header-background-color: #d1e7dd;
  --easy-table-border: 1px solid #dee2e6;
  --easy-table-row-border: 1px solid #dee2e6;
}

.my-custom-header {
  background-color: #d1e7dd !important;
  font-weight: 600;
  font-size: 13px;
}

/* Badge styling */
.badge {
  font-size: 11px;
  padding: 4px 12px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .my-custom-table {
    font-size: 11px;
  }

  .badge {
    font-size: 10px;
    padding: 4px 8px !important;
  }
}
</style>
