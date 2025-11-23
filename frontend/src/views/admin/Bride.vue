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

          <!-- Judul Laporan -->
          <div class="text-center mt-4">
            <div class="bg-additional text-white py-1 px-4 d-inline-block rounded-top">
              <h5 class="mb-0">
                Laporan Status Kesehatan Calon Pengantin Desa
                <span class="text-capitalize fw-bold">{{ kelurahan }}</span>
                Periode
                <span class="fw-bold">{{ thisMonth }}</span>
              </h5>
            </div>
          </div>

          <!-- Filter Form -->
          <div class="bg-light border rounded-bottom shadow-sm px-4 py-3">
            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <div
                v-for="(filter, key) in filterOptions"
                :key="key"
                class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12 position-relative"
              >
              <label
                v-if="filter.filter"
                class="text-primary"
              >
                {{ filter.filter }}
              </label>
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                  >
                    <span class="text-muted" >{{ getFilterDisplayText(key) }}</span>
                  </button>

                  <ul class="dropdown-menu w-100 p-2" style="max-height: 260px; overflow-y: auto;">
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
                      <label class="form-check-label w-100" :for="`${key}-${item}`">{{ item }}</label>
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex justify-content-between px-2">
                      <button type="button" class="btn btn-sm btn-outline-primary fw-semibold small" @click="selectAll(key)">
                        Pilih Semua
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold" @click="clearAll(key)">
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
                  <option :value="kelurahan">{{ this.filters.kelurahan }}</option>
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
                <select
                  v-model="filters.rw"
                  class="form-select text-muted"
                  :disabled="rwReadonly"
                >
                  <option class="text-muted" value="">Pilih RW</option>
                  <option v-for="rw in rwList" :key="rw" :value="rw">{{ rw }}</option>
                </select>
              </div>

              <!-- RT -->
              <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-6">
                <select
                  v-model="filters.rt"
                  class="form-select text-muted"
                  :disabled="rtReadonly"
                >
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

          <div class="text-center mt-3">
            <h5 class="fw-bold text-success mb-3">Ringkasan Statistik</h5>
          </div>

          <!-- Ringkasan Statistik -->
          <div class="container-fluid my-4">
            <div class="row">
              <div class="row justify-content-center">
                <div
                  v-for="(item, index) in kesehatan"
                  :key="index"
                  class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mb-3"
                >
                  <div
                    class="card shadow-sm border-0 rounded-2 overflow-hidden"
                    :class="`border-start border-4 border-${item.color}`"
                  >
                    <div class="card-header">
                      <h6 class="fw-bold mb-1">{{ item.title }}</h6>
                    </div>
                    <div class="card-body d-flex justify-content-between align-items-center">
                      <p
                        v-if="index !== kesehatan.length - 1"
                        class="mb-0"
                        :class="`text-${item.color}`"
                      >
                        {{ item.percent }}
                      </p>
                      <p v-else class="my-auto">
                        <i class="bi bi-people fs-5" :class="`text-${item.color}`"></i>
                      </p>
                      <!-- Hanya tampilkan persentase kalau bukan card terakhir -->
                      <h3 class="fw-bold mb-0" :class="`text-${item.color}`">
                        {{ item.value }}
                      </h3>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Table and detail Section -->
          <div class="container-fluid mt-4">
            <h5 class="fw-bold text-success">Data Calon Pengantin</h5>
            <div class="row mt-1">
              <div :class="selectedCatin ? 'col-md-8' : 'col-md-12'">
                <div class="card bg-light p-2">
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center">
                      <thead class="table-primary small">
                        <tr>
                          <th class="cursor-pointer align-middle text-center" rowspan="2">No</th>
                          <th colspan="2">Nama Pasangan</th>
                          <th colspan="3">Catatan Berisiko</th>
                          <th colspan="2">Usia</th>
                          <th colspan="2">Pekerjaan</th>
                          <th class="cursor-pointer align-middle text-center" rowspan="2">Posyandu</th>
                          <th class="cursor-pointer align-middle text-center" rowspan="2">RW</th>
                          <th class="cursor-pointer align-middle text-center" rowspan="2">RT</th>
                          <th class="cursor-pointer align-middle text-center" rowspan="2">Tanggal Kunjungan</th>
                          <th class="cursor-pointer align-middle text-center" rowspan="2">Tanggal Menikah</th>
                          <th class="cursor-pointer align-middle text-center" rowspan="2">BB <span class="fw-normal small">(Perempuan)</span></th>
                          <th class="cursor-pointer align-middle text-center" rowspan="2">TB <span class="fw-normal small">(Perempuan)</span></th>
                          <th class="cursor-pointer align-middle text-center" rowspan="2">Lila <span class="fw-normal small">(Perempuan)</span></th>
                          <th class="cursor-pointer align-middle text-center" rowspan="2">Hb <span class="fw-normal small">(Perempuan)</span></th>
                          <th class="cursor-pointer align-middle text-center" rowspan="2">Riwayat Penyakit</th>
                          <th class="cursor-pointer align-middle text-center" rowspan="2">Jamban Sehat</th>
                          <th class="cursor-pointer align-middle text-center" rowspan="2">Sumber Air Bersih</th>
                        </tr>
                        <tr>
                          <th style="width:100px" @click="sortBy('nama_perempuan')" class="cursor-pointer align-middle text-center fw-normal small">
                            Perempuan <SortIcon :field="'nama_perempuan'" />
                          </th>
                          <th style="width:100px" @click="sortBy('nama_laki')" class="cursor-pointer align-middle text-center fw-normal small">
                            Laki-laki <SortIcon :field="'nama_laki'" />
                          </th>
                          <th @click="sortBy('anemia')" class="cursor-pointer align-middle text-center fw-normal small">
                            Anemia <SortIcon :field="'anemia'" />
                          </th>
                          <th style="width:60px" @click="sortBy('kek')" class="cursor-pointer align-middle text-center fw-normal small">
                            KEK <SortIcon :field="'kek'" />
                          </th>
                          <th style="width:100px" @click="sortBy('berisiko')" class="cursor-pointer align-middle text-center fw-normal small">
                            Risiko Usia <SortIcon :field="'berisiko'" />
                          </th>
                          <th style="width:100px" @click="sortBy('usia_perempuan')" class="cursor-pointer align-middle text-center fw-normal small">
                            Perempuan <SortIcon :field="'usia_perempuan'" />
                          </th>
                          <th style="width:100px" @click="sortBy('usia_laki')" class="cursor-pointer align-middle text-center fw-normal small">
                            Laki-laki <SortIcon :field="'usia_laki'" />
                          </th>
                          <th style="width:100px" @click="sortBy('pekerjaan_perempuan')" class="cursor-pointer align-middle text-center fw-normal small">
                            Perempuan <SortIcon :field="'pekerjaan_perempuan'" />
                          </th>
                          <th style="width:100px" @click="sortBy('pekerjaan_laki')" class="cursor-pointer align-middle text-center fw-normal small">
                            Laki-laki <SortIcon :field="'pekerjaan_laki'" />
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="(catin, index) in paginatedData"
                          :key="catin.id"
                          class="small"
                        >
                          <td>
                            {{ index + 1 + (currentPage - 1) * perPage }}
                          </td>
                          <td class="text-start">
                            <a href="#" @click.prevent="showDetail(catin)" class="fw-semibold text-decoration-none text-primary">
                              {{ catin.nama_perempuan }}
                            </a>
                          </td>
                          <td>{{ catin.nama_laki }}</td>
                          <td>
                            <span v-if="catin.pemeriksaan_terakhir.status_hb === 'Anemia'" class="badge bg-danger text-white">{{catin.pemeriksaan_terakhir.status_hb}}</span>
                            <span v-else>{{ catin.pemeriksaan_terakhir.status_hb }}</span>
                          </td>

                          <td>
                            <span v-if="catin.pemeriksaan_terakhir.status_kek === 'KEK'" class="badge bg-danger text-white">{{catin.pemeriksaan_terakhir.status_kek}}</span>
                            <span v-else>{{ catin.pemeriksaan_terakhir.status_kek }}</span>
                          </td>

                          <td>
                            <span v-if="catin.status_risiko === 'Berisiko'" class="badge bg-danger text-white">{{catin.status_risiko}}</span>
                            <span v-else>{{ catin.status_risiko }}</span>
                          </td>

                          <td>{{ catin.usia_perempuan }}</td>
                          <td>{{ catin.usia_laki }}</td>

                          <td>{{ catin.pekerjaan_perempuan }}</td>
                          <td>{{ catin.pekerjaan_laki }}</td>

                          <td>{{ catin.posyandu }}</td>
                          <td>{{ catin.rw }}</td>
                          <td>{{ catin.rt }}</td>
                          <td>{{ this.formatDate(catin.tgl_kunjungan) }}</td>
                          <td>{{ this.formatDate(catin.tgl_menikah) }}</td>
                          <td>{{ catin.pemeriksaan_terakhir.berat_perempuan }}</td>
                          <td>{{ catin.pemeriksaan_terakhir.tinggi_perempuan }}</td>
                          <td>{{ catin.pemeriksaan_terakhir.lila_perempuan }}</td>
                          <td>{{ catin.pemeriksaan_terakhir.hb_perempuan }}</td>
                          <td>{{ catin.pemeriksaan_terakhir.riwayat_penyakit != ''? 'Ya':'Tidak'}}</td>
                          <td>{{ catin.pemeriksaan_terakhir.menggunakan_jamban = true ? 'Ya':'Tidak' }}</td>
                          <td>{{ catin.pemeriksaan_terakhir.sumber_air_bersih = true? 'Ya':'Tidak' }}</td>
                        </tr>
                        <!-- ✅ Jika tidak ada data -->
                        <tr v-if="!paginatedData || paginatedData.length === 0">
                          <td colspan="21" class="text-center text-muted py-3">
                            Tidak ada data yang tersedia
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Pagination -->
                  <nav>
                    <ul class="pagination justify-content-center mt-1 mb-0">
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
              <div class="col-md-4" v-if="selectedCatin">
                <div v-if="selectedCatin" class="card shadow-sm p-4 text-center small position-relative">

                  <!-- Tombol Close -->
                  <button
                    type="button"
                    class="btn-close position-absolute top-0 end-0 m-3"
                    aria-label="Close"
                    @click="selectedCatin = null"
                  ></button>

                  <!-- Nama dan Identitas -->
                  <h5 class="fw-bold text-dark mb-1">{{ selectedCatin.nama_perempuan }} / {{ selectedCatin.nama_laki }}</h5>
                  <p class="text-muted mb-0 text-capitalize">{{ selectedCatin.kelurahan || '-' }}</p>
                  <p class="text-muted">{{ selectedCatin.kecamatan || '-' }}</p>

                  <!-- Badge Status Gizi -->
                  <div class="mb-3">
                    <span
                      class="badge px-3 py-2 small"
                      :class="{
                        'bg-danger text-white': ['Berisiko'].includes(selectedCatin.status_risiko),
                        'bg-success': selectedCatin.status_risiko === 'Normal'
                      }"
                    >
                      {{ selectedCatin.status_risiko }}
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
                          v-for="(r, i) in (selectedCatin.pemeriksaan_terakhir || []).slice(-3)"
                          :key="i"
                        >
                          <td>{{ this.formatDate(r.tanggal_pemeriksaan) }}</td>
                          <td>
                            <span
                              class="badge"
                              :class="r.status_hb === 'Anemia' ? 'bg-danger' : 'text-dark'"
                            >
                              {{ r.status_hb }}
                            </span>
                          </td>
                          <td>
                            <span
                              class="badge"
                              :class="r.status_kek === 'KEK' ? 'bg-danger' : 'text-dark'"
                            >
                              {{ r.status_kek }}
                            </span>
                          </td>
                          <td>
                            <span
                              class="badge"
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
                    class="btn btn-gradient rounded-pill px-4 mt-2 fw-semibold"
                    @click="downloadRiwayat"
                  >
                    Download Riwayat
                  </button>
                </div>
              </div>

              <!-- Detail Riwayat Anak -->
              <div class="col-md-12 mt-4" v-if="selectedCatin">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden position-relative">
                  <!-- Tombol Close -->
                  <button
                    class="btn-close position-absolute top-0 end-0 m-3"
                    aria-label="Close"
                    @click="selectedCatin = null"
                  ></button>

                  <!-- Header -->
                  <div class="bg-primary text-white p-4 text-center rounded-top">
                    <h5 class="fw-bold mb-0">{{ selectedCatin.nama_perempuan }} / {{ selectedCatin.nama_laki }}</h5>
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
                          <i class="bi bi-clipboard-heart me-1"></i> Data Pemeriksaan
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
                              <h6 class="fw-bold mb-3 text-danger">Identitas Pasangan</h6>
                              <table class="table table-borderless mb-0">
                                <tbody>
                                  <tr>
                                    <td class="fw-semibold" style="width: 120px;">Nama</td>
                                    <td>:</td>
                                    <td>{{ selectedCatin.nama_perempuan }} / {{ selectedCatin.nama_laki }}</td>
                                  </tr>
                                  <tr>
                                    <td class="fw-semibold">Usia</td>
                                    <td>:</td>
                                    <td>{{ selectedCatin.usia_perempuan }} Tahun / {{ selectedCatin.usia_laki }} Tahun</td>
                                  </tr>
                                  <tr>
                                    <td class="fw-semibold">Pekerjaan</td>
                                    <td>:</td>
                                    <td>{{selectedCatin.pekerjaan_perempuan}} / {{ selectedCatin.pekerjaan_laki }}</td>
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
                                    <td class="fw-semibold" style="width: 120px;">Alamat</td>
                                    <td>:</td>
                                    <td>{{ selectedCatin.provinsi }}, {{ selectedCatin.kota }}, {{ selectedCatin.kecamatan }}</td>
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
                          <h6 class="fw-bold mb-3 text-danger">Riwayat Pemeriksaan</h6>
                          <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                              <thead class="table-success">
                                <tr class="small text-center align-middle">
                                  <th style="width: 150px;">Tanggal</th>
                                  <th>Kader</th>
                                  <th>Risiko</th>
                                  <th>TB <span class="fw-normal">(cm)</span></th>
                                  <th>BB <span class="fw-normal">(kg)</span></th>
                                  <th>Lila <span class="fw-normal">(cm)</span></th>
                                  <th>KEK</th>
                                  <th>Hb</th>
                                  <th>Anemia</th>
                                  <th>Riwayat Penyakit</th>
                                  <th>Terpapar Asap Rokok</th>
                                  <th>Jamban Sehat</th>
                                  <th>Sumber Air Bersih</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr
                                  v-for="(item, i) in selectedCatin.riwayat"
                                  :key="'kehamilan-' + i"
                                  class="small text-center"
                                >
                                  <td>{{ this.formatDate(item.tanggal_pemeriksaan) }}</td>
                                  <td>{{ item.nama_petugas }}</td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="item.status_risiko === 'Risiko' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.status_risiko || '-' }}
                                    </span>
                                  </td>
                                  <td>{{ item.tinggi_perempuan }}</td>
                                  <td>{{ item.berat_perempuan }}</td>
                                  <td>{{ item.lila_perempuan}}</td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="item.status_kek === 'KEK' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.status_kek || '-' }}
                                    </span>
                                  </td>
                                  <td>{{ item.hb_perempuan }}</td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="item.status_hb === 'anemia' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.status_hb || '-' }}
                                    </span>
                                  </td>
                                  <td>
                                    <i v-if="item.terpapar_rokok==true" class="fa fa-check text-danger"></i>
                                    <span v-else>-</span>
                                  </td>
                                  <td>
                                    <i v-if="item.mendapat_bantuan_pmt==true" class="fa fa-check text-danger"></i>
                                    <span v-else>-</span>
                                  </td>
                                  <td>
                                    <i v-if="item.menggunakan_jamban==true" class="fa fa-check text-danger"></i>
                                    <span v-else>-</span>
                                  </td>
                                  <td>
                                    <i v-if="item.sumber_air_bersih==true" class="fa fa-check text-danger"></i>
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
import { ref, computed } from 'vue'
import Welcome from '@/components/Welcome.vue'

// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Bride',
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
        kelurahan: '',
        posyandu:'',
        rt:'',
        rw:'',
        status: [],
        usia: [],
        periodeAwal: '',
        periodeAkhir: '',
      },
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
      filteredCatin.value.sort((a, b) => {
        if (a[key] < b[key]) return sortDir.value === 'asc' ? -1 : 1
        if (a[key] > b[key]) return sortDir.value === 'asc' ? 1 : -1
        return 0
      })
    }

    const totalPages = computed(() =>
      Math.ceil(filteredCatin.value.length / perPage.value)
    )

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
      changePage
    }
  },
  computed: {
    filterOptions() {
      return {
        status: { label: 'Status', placeholder: 'Pilih Status', options: ['KEK', 'Anemia', 'Berisiko'], filter:'Filter status Calon Pengantin:' },
        usia: { label: 'Usia (Tahun)', placeholder: 'Pilih Usia', options: ['<20', '20-30', '30-40', '>40'],filter:'' },
      }
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
  methods: {
    formatDate(dateString) {
      if (!dateString) return '-'
      const date = new Date(dateString)
      const day = String(date.getDate()).padStart(2, '0')
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const year = date.getFullYear()
      return `${day}-${month}-${year}`
    },
    showDetail(props) {
      //console.log(props);
      this.selectedCatin = props
      console.log(this.selectedCatin);
    },
    downloadRiwayat() {
      if (!this.selectedCatin) {
        alert('Silakan pilih calon pengantin terlebih dahulu.');
        return;
      }

      const catin = this.selectedCatin;

      // 🧩 Format CSV Header
      let csvContent = `DATA CALON PENGANTIN\n`;
      csvContent += `Nama Perempuan,${catin.nama_perempuan || '-'}\n`;
      csvContent += `NIK Perempuan,${catin.nik || '-'}\n`;
      csvContent += `Nama Laki-laki,${catin.nama_laki || '-'}\n`;
      csvContent += `Usia Perempuan,${catin.usia_perempuan || '-'}\n`;
      csvContent += `Usia Laki-laki,${catin.usia_laki || '-'}\n`;
      csvContent += `Posyandu,${catin.posyandu || '-'}\n`;
      csvContent += `RW,${catin.rw || '-'}\n`;
      csvContent += `RT,${catin.rt || '-'}\n`;
      csvContent += `Kelurahan,${catin.kelurahan || '-'}\n`;
      csvContent += `Kecamatan,${catin.kecamatan || '-'}\n`;
      csvContent += `Kota,${catin.kota || '-'}\n`;
      csvContent += `Provinsi,${catin.provinsi || '-'}\n`;
      csvContent += `Tanggal Menikah,${this.formatDate(catin.tgl_menikah)}\n`;
      csvContent += `Riwayat Penyakit,${catin.riwayat_penyakit || '-'}\n`;
      csvContent += `Sumber Air Bersih,${catin.sumber_air_bersih || '-'}\n`;
      csvContent += `Jamban Sehat,${catin.jamban_sehat || '-'}\n\n`;

      // 🩺 Pemeriksaan Terakhir
      if (catin.pemeriksaan_terakhir) {
        const p = catin.pemeriksaan_terakhir;
        csvContent += `PEMERIKSAAN TERAKHIR\n`;
        csvContent += `Tanggal,${this.formatDate(p.tanggal_pendampingan)}\n`;
        csvContent += `Petugas,${p.petugas || '-'}\n`;
        csvContent += `Status HB,${p.status_hb || '-'}\n`;
        csvContent += `Status KEK,${p.status_kek || '-'}\n`;
        csvContent += `Status Risiko,${p.status_risiko || '-'}\n\n`;
      }

      // 📋 Riwayat Pendampingan (riwayat_catin)
      if (Array.isArray(catin.riwayat) && catin.riwayat.length > 0) {
        csvContent += `RIWAYAT PEMERIKSAAN / PENDAMPINGAN\n`;
        csvContent += `Tanggal,Petugas,Status HB,Status KEK,Status Risiko\n`;
        catin.riwayat.forEach(r => {
          csvContent += `${this.formatDate(r.tanggal_pendampingan)},${r.petugas || '-'},${r.status_hb || '-'},${r.status_kek || '-'},${r.status_risiko || '-'}\n`;
        });
      } else {
        csvContent += `Tidak ada riwayat pemeriksaan\n`;
      }

      // 💾 Buat file CSV dan download
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `Riwayat_${catin.nama_perempuan?.replace(/\s+/g, '_') || 'Catin'}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
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
      await this.loadBride(),
      await this.hitungStatusKesehatan()
    },
    async resetFilter() {

      this.rwReadonly = true,
      this.rtReadonly = true,
      Object.keys(this.filters).forEach(k => {
        if (k=='kelurahan') this.filters[k]
        else if (Array.isArray(this.filters[k])) this.filters[k] = []
        else this.filters[k] = ''
      }),
      await this.loadBride(),
      await this.hitungStatusKesehatan()
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    getTodayDate() {
      const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
      const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
      const now = new Date()
      return `${hari[now.getDay()]}, ${now.getDate()} ${bulan[now.getMonth()]} ${now.getFullYear()}`
    },
    getThisMonth() {
      const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
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
        this.filters.kelurahan = wilayah.kelurahan || '-'
        this.kecamatan = wilayah.kecamatan || '-'
        this.kota = wilayah.kota || '-'
        this.provinsi = wilayah.provinsi || '-'
      } catch (e) {
        console.error('❌ getWilayahUser error:', e)
        this.kelurahan = '-'
      }
    },
    handlePosyanduChange() {
      const pos = this.filters.posyandu;

      if (!pos) {
        this.rwList = [];
        this.rtList = [];
        this.rwReadonly = true;
        this.rtReadonly = true;
        this.filteredCatin = [...this.catin]; // tampilkan semua
        return;
      }

      // ✅ Gunakan field yang benar
      const filteredCatin = this.catin.filter(b => b.posyandu === pos);

      // 🔹 Ambil daftar RW & RT unik
      const rwSet = new Set(filteredCatin.map(b => b.rw).filter(Boolean));
      const rtSet = new Set(filteredCatin.map(b => b.rt).filter(Boolean));

      this.rwList = Array.from(rwSet);
      this.rtList = Array.from(rtSet);

      // 🔹 Aktifkan dropdown RW & RT
      this.rwReadonly = false;
      this.rtReadonly = false;

      // 🔹 Update data tabel
      this.filteredCatin = filteredCatin;
    },
    handleRwChange() {
      const pos = this.filters.posyandu;
      const rw = this.filters.rw;

      if (!rw) {
        this.rtList = [];
        this.rtReadonly = true;
        return;
      }

      const filteredPregnancy = this.catin.filter(
        c => c.posyandu === pos && c.rw === rw
      );

      const rtSet = new Set(filteredPregnancy.map(c => c.rt).filter(Boolean));
      this.rtList = Array.from(rtSet);
      this.rtReadonly = false;
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
    async hitungStatusKesehatan() {
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
          params.status = this.filters.status;
        }

        const res = await axios.get(`${baseURL}/api/bride/status`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          params,
        });

        const data = res.data;
        const total = data.total || 0;

        //this.totalBumil = total;
        this.kesehatan = data.counts.map(item => ({
          title: item.title,
          value: item.value,
          percent: total > 0 ? ((item.value / total) * 100).toFixed(1) + '%' : '0%',
          color: item.color,
        }));

      } catch (e) {
        console.error('❌ hitungStatusKesehatan error:', e);
      }
    },
    async loadBride() {
      try {
        const res = await axios.get(`${baseURL}/api/bride`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          params: this.filters,
        });

        console.log('data: ', this.filters);

        // ✅ Ubah data object jadi array
        const rawData = res.data?.data || {};
        const data = Object.keys(rawData).map(nik => ({
          nik_perempuan: nik,
          ...rawData[nik],
        }));
        this.catin = data.map(item => ({
          nik: item.nik_perempuan,
          status_risiko: item.status_risiko,
          nama_perempuan: item.nama_perempuan,
          nama_laki: item.nama_laki,
          usia_perempuan: item.usia_perempuan,
          usia_laki: item.usia_laki,
          pekerjaan_perempuan: item.kerja_perempuan,
          pekerjaan_laki: item.kerja_laki,
          posyandu: item.posyandu != ''? item.posyandu : '-',
          provinsi: item.provinsi,
          kota: item.kota,
          kecamatan: item.kecamatan,
          kelurahan: item.kelurahan,
          rt: item.rt,
          rw: item.rw,
          tgl_kunjungan: item.tgl_kunjungan,
          tgl_menikah: item.tgl_pernikahan,
          riwayat_penyakit: item.riwayat_pemeriksaan.riwayat_penyakit,
          sumber_air_bersih: item.sumber_air_bersih == true? 'Ya' : 'Tidak',
          jamban_sehat: item.jamban_sehat == true? 'Ya' : 'Tidak',
          // ambil pemeriksaan terakhir
          pemeriksaan_terakhir: item.riwayat_pemeriksaan?.[0] || null,
          riwayat:item.riwayat_catin,
        }));

        // ✅ Set filtered dan total
        this.filteredCatin = [...this.catin];
        // 🔹 Ambil semua nama posyandu unik dari data catin
        const posSet = new Set(this.catin.map(c => c.posyandu).filter(Boolean));
        this.posyanduList = Array.from(posSet).map((nama, i) => ({
          id: i + 1,
          nama_posyandu: nama,
        }));

        // console.log('isi catin:', this.catin);
      } catch (e) {
        console.error('Gagal ambil data pernikahan:', e);
        /*
        this.catin = [];
        this.filteredCatin = [];
        this.posyanduList = []; */
      }
    },

  },
  async mounted() {
    this.isLoading = true
    try {
      await Promise.all([
        //this.loadConfigWithCache(),
        //this.getPendingData(),
        await this.getWilayahUser(),
        this.hitungStatusKesehatan(),
        this.loadBride(),
        this.handleResize(),
        this.generatePeriodeOptions(),

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
</style>
