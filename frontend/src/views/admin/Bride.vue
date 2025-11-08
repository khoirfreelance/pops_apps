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
                class="col-xl-3 col-lg-3 col-md-3 col-sm-4 col-6 position-relative"
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
              <div class="col-xl-2 col-lg-2 col-md-2 col-sm-4 col-6">
                <label for="filter" class="text-primary">Filter Lokasi:</label>
                <select v-model="kelurahan" class="form-select text-muted" disabled>
                  <option :value="kelurahan">{{ kelurahan }}</option>
                </select>
              </div>

              <!-- Posyandu -->
              <div class="col-xl-2 col-lg-2 col-md-2 col-sm-4 col-12">
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

              <!-- Terapkan -->
              <div class="col-xl-2 col-lg-2 col-md-2 col-sm-4 col-12 text-end">
                <button type="submit" class="btn btn-gradient w-75 fw-semibold">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>
              </div>

              <!-- RW -->
              <div class="col-xl-3 col-lg-3 col-md-4 col-sm-4 col-6">
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
              <div class="col-xl-3 col-lg-3 col-md-4 col-sm-4 col-6">
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
              <div class="col-md-4 text-center">
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

              <!-- Reset -->
              <div class="col-xl-2 col-lg-2 col-md-2 col-sm-4 col-12 text-end">
                <button type="button" class="btn btn-secondary w-75 fw-semibold" @click="resetFilter">
                  <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
              </div>
            </form>
          </div>

          <div class="text-center mt-3">
            <h5 class="mb-0 text-primary">
              Ringkasan Statistik
            </h5>
          </div>

          <!-- Ringkasan Statistik-->
          <div class="container-fluid my-4">

            <div class="row">
              <div class="row justify-content-center">
                <div
                  v-for="(item, index) in kesehatan"
                  :key="index"
                  class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mb-3"
                >
                  <div
                    class="card shadow-sm border-0 rounded-3 overflow-hidden"
                    :class="`border-start border-4 border-${item.color}`"
                  >
                    <div class="card-header">
                      <h6 class="fw-bold mb-1">{{ item.title }}</h6>
                    </div>
                    <div class="card-body d-flex justify-content-between align-items-center">
                      <h3 class="fw-bold mb-0" :class="`text-${item.color}`">{{ item.value }}</h3>
                      <p class="mb-0" :class="`text-${item.color}`">{{ item.percent }}</p>
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Table and detail Section -->
          <div class="container-fluid mt-4">
            <h5 class="fw-bold text-success mb-3">Data Calon Pengantin</h5>
            <div class="row mt-4">
              <div :class="selectedCatin ? 'col-md-8 mb-3' : 'col-md-12 mb-3'">
                <div class="card bg-light px-2 py-5">
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center">
                      <thead class="table-primary small">
                        <tr>
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
                        <tr v-for="catin in paginatedData" :key="catin.id" class="small">
                          <td class="text-start">
                            <a href="#" @click.prevent="showDetail(catin)" class="fw-semibold text-decoration-none text-primary">
                              {{ catin.nama }}
                            </a>
                          </td>
                          <td>{{ catin.anemia }}</td>
                          <td>
                            <span v-if="catin.risiko === 'Ya' || catin.risiko === 'Beresiko Tinggi' " class="badge bg-danger text-white">Ya</span>
                            <span v-else>{{ catin.risiko }}</span>
                          </td>
                          <td>
                            <span v-if="catin.kek === 'Ya' || catin.kek === 'KEK'" class="badge bg-danger text-white">Ya</span>
                            <span v-else>{{ catin.kek }}</span>
                          </td>
                          <td>
                            <span v-if="catin.intervensi === 'Ya'" class="badge bg-danger text-white">Ya</span>
                            <span v-else>{{ catin.intervensi }}</span>
                          </td>
                          <td>{{ catin.rw }}</td>
                          <td>{{ catin.rt }}</td>
                          <td>{{ catin.usia }}</td>
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
                  <h5 class="fw-bold text-dark mb-1">{{ selectedCatin.nama }}</h5>
                  <p class="text-muted mb-0 text-capitalize">{{ selectedCatin.kelurahan || '-' }}</p>
                  <p class="text-muted">{{ selectedCatin.kecamatan || '-' }}</p>

                  <!-- Badge Status Gizi -->
                  <div class="mb-3">
                    <span
                      class="badge px-3 py-2 small"
                      :class="{
                        'bg-danger text-dark': ['Kehamilan Berisiko'].includes(selectedCatin.status_gizi),
                        'bg-warning text-dark': ['KEK'].includes(selectedCatin.status_gizi),
                        'bg-success': selectedCatin.status_gizi === 'Normal'
                      }"
                    >
                      {{ selectedCatin.status_gizi }}
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
                          v-for="(r, i) in (selectedCatin.riwayat_pendampingan || []).slice(-3)"
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
                        <tr v-for="(i, idx) in selectedCatin.riwayat_intervensi || []" :key="idx">
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
                    <h5 class="fw-bold mb-0">{{ selectedCatin.nama }}</h5>
                    <p class="text-white mb-0 small">
                      {{ selectedCatin.usia }} Tahun - {{ selectedCatin.risiko }}
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
                              <h6 class="fw-bold mb-3 text-danger">Identitas Ibu Hamil</h6>
                              <table class="table table-borderless mb-0">
                                <tbody>
                                  <tr>
                                    <td class="fw-semibold" style="width: 120px;">Nama</td>
                                    <td>:</td>
                                    <td>{{ selectedCatin.nama }}</td>
                                  </tr>
                                  <tr>
                                    <td class="fw-semibold">Usia</td>
                                    <td>:</td>
                                    <td>{{ selectedCatin.usia }} Tahun</td>
                                  </tr>
                                  <tr>
                                    <td class="fw-semibold">Nama Suami</td>
                                    <td>:</td>
                                    <td>{{ selectedCatin.nama_suami }}</td>
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

                      <!-- Data Kelahiran -->
                      <div class="tab-pane fade" id="tab-pane-kehamilan" role="tabpanel">
                        <div class="card bg-light border-0 shadow-sm p-3">
                          <h6 class="fw-bold mb-3 text-danger">Data Kehamilan</h6>
                          <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                              <thead>
                                <tr class="small text-center align-middle">
                                  <th style="width: 150px;">Tanggal</th>
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
                                  v-for="(item, i) in selectedCatin.kehamilan"
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
                                  <td>{{ item.lila}}</td>
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
                                      :class="item.asap_rokok === 'Ya' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.asap_rokok || '-' }}
                                    </span>
                                  </td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="item.bantuan_sosial === 'Tidak' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.bantuan_sosial || '-' }}
                                    </span>
                                  </td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="item.jamban_sehat === 'Tidak' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.jamban_sehat || '-' }}
                                    </span>
                                  </td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="item.sumber_air_bersih === 'Tidak' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.sumber_air_bersih || '-' }}
                                    </span>
                                  </td>
                                  <td>{{ item.keluhan}}</td>
                                  <td>{{ item.intervensi}}</td>
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
      bridePending: [], // data pending catin
      filteredCatin: [],
      totalCatin: 0,
      periodeOptions: [],
      selectedCatin: null,
      posyanduList: [],
      rwList: [],
      rtList: [],
      rwReadonly: true,
      rtReadonly: true,
      filters: {
        kelurahan: '',
        posyandu:'',
        rt:'',
        rw:'',
        status: [],
        usia: [],
        intervensi: [],
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
        status: { label: 'Status', placeholder: 'Pilih Status', options: ['KEK', 'Anemia', 'Beresiko Tinggi'], filter:'Filter status catin hamil:' },
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
      Object.keys(this.filters).forEach(k => {
        if (Array.isArray(this.filters[k])) this.filters[k] = []
        else this.filters[k] = ''
      })
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
        this.kelurahan = wilayah.kelurahan || '-'
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
        this.filteredBumil = [...this.catin]; // tampilkan semua lagi
        return;
      }

      // 🔹 Ambil data catin di posyandu terpilih
      const filteredBumil = this.catin.filter(b => b.nama_posyandu === pos);

      // 🔹 Ambil daftar RW & RT unik dari mereka
      const rwSet = new Set(filteredBumil.map(b => b.rw).filter(Boolean));
      const rtSet = new Set(filteredBumil.map(b => b.rt).filter(Boolean));

      this.rwList = Array.from(rwSet);
      this.rtList = Array.from(rtSet);

      // 🔹 Aktifkan dropdown RW & RT
      this.rwReadonly = false;
      this.rtReadonly = false;

      // 🔹 Update data yang tampil di tabel
      this.filteredBumil = filteredBumil;
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
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          usia: this.filters.usia || [],            // tambahan
          intervensi: this.filters.intervensi || [],// tambahan
          periodeAwal: this.filters.periodeAwal || '',
          periodeAkhir: this.filters.periodeAkhir || '',
        };

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

        const data = res.data?.data || [];

        // ✅ Ambil semua nama posyandu dari seluruh riwayat_pendampingan
        const allPosyandu = data.flatMap(catin =>
          (catin.riwayat_pendampingan || [])
            .map(r => r.posyandu)
            .filter(Boolean)
        );

        // ✅ Buat list unik
        const uniquePosyandu = [...new Set(allPosyandu)];
        this.posyanduList = uniquePosyandu.map((nama, idx) => ({
          id: idx + 1,
          nama_posyandu: nama,
        }));

        // ✅ Format data catin
        this.catin = data.map(item => {
          const lastCheck = item.riwayat_pendampingan?.at(-1); // pemeriksaan terakhir
          return {
            nama_perempuan: item.nama_perempuan,
            nama_laki: item.nama_laki,
            usia_perempuan: item.usia_perempuan,
            usia_laki: item.usia_laki,
            pekerjaan_perempuan: item.pekerjaan_perempuan,
            pekerjaan_laki: item.pekerjaan_laki,
            tanggal_pendampingan: lastCheck?.tanggal_pendampingan || '-',
            berat_badan: lastCheck?.berat_perempuan || '-',
            tinggi_badan: lastCheck?.tinggi_perempuan || '-',
            risiko: lastCheck?.status_risiko || '-',
            anemia: lastCheck?.status_hb || '-',
            kek: lastCheck?.status_kek || '-',
            nama_posyandu: lastCheck?.posyandu || '-',
            rw: item.rw || '-',
            rt: item.rt || '-',
          };
        });

        // ✅ Set filtered dan total
        this.filteredCatin = [...this.catin];
        this.totalCarin = this.catin.length;
        //console.log('isi catin:', this.filteredBumil);


      } catch (e) {
        console.error('Gagal ambil data pernikahan:', e);
        this.catin = [];
        this.filteredBumil = [];
        this.posyanduList = [];
      }
    },

  },
  async mounted() {
    this.isLoading = true
    try {
      await Promise.all([
        this.hitungStatusKesehatan(),
        //this.loadConfigWithCache(),
        this.loadBride(),
        //this.getPendingData(),
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

.datatable-responsive {
  width: 100%;
  overflow-x: auto; /* aktifkan scroll horizontal */
}

.datatable-responsive table {
  min-width: 300px; /* sesuaikan lebar minimal tabel */
}

.bride-wrapper {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  min-height: 100vh;
}
/* Gradient Banner */
.bride-banner {
  background: linear-gradient(90deg, var(--bs-primary), var(--bs-secondary));
  border-radius: 0 0 1rem 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
.table-wrapper {
  width: 100%;
  overflow-x: auto; /* ✅ Scroll horizontal */
  -webkit-overflow-scrolling: touch; /* smooth di mobile */
}

.table-modern td {
  max-width: 180px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

form h5 {
  position: relative;
  padding-bottom: 0.5rem;
  margin-bottom: 1rem;
  border-bottom: 2px solid var(--bs-secondary); /* default pakai secondary */
}
@media (max-width: 768px) {
  .table-modern {
    min-width: auto;
  }
}
</style>
