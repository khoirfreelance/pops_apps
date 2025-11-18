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
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

      <!-- Main Content -->
      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <div class="py-4 container-fluid">
          <!-- Welcome Card -->
          <Welcome />

          <!-- Judul Laporan -->
          <div class="text-center mt-4">
            <div class="bg-additional text-white py-1 px-4 d-inline-block rounded-top">
              <h5 class="mb-0">
                Laporan Status Kesehatan Ibu Hamil Desa
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
                class="col-xl-2 col-lg-3 col-md-4 col-sm-4 col-6 position-relative"
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
                      class="small dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input small me-2"
                        :id="`${key}-${item}`"
                        :value="item"
                        v-model="filters[key]"
                      />
                      <label class="form-check-label small w-100" :for="`${key}-${item}`">{{ item }}</label>
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex small justify-content-between px-2">
                      <button type="button" class="btn btn-sm small btn-outline-primary fw-semibold" @click="selectAll(key)">
                        Pilih Semua
                      </button>
                      <button type="button" class="btn btn-sm small btn-outline-secondary fw-semibold" @click="clearAll(key)">
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Kelurahan/Desa -->
              <div class="col-xl-6 col-lg-3 col-md-4 col-sm-4 col-6">
                <label for="filter" class="text-primary">Desa:</label>
                <select v-model="kelurahan" class="form-select text-muted" disabled>
                  <option :value="kelurahan">{{ kelurahan }}</option>
                </select>
              </div>

              <!-- Posyandu -->
              <div class="col-xl-2 col-lg-2 col-md-4 col-sm-4 col-12">
                <label for="filter" class="text-primary">Filter Lokasi:</label>
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
              <div class="col-xl-2 col-lg-2 col-md-4 col-sm-4 col-6">
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
              <div class="col-xl-2 col-lg-2 col-md-4 col-sm-4 col-6">
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
              <div class="col-xl-6 col-lg-6 col-md-6 col-sm-8 col-12 text-center">
                <label for="filter" class="text-primary"> Filter Periode:</label>
                <div class="d-flex justify-content-between gap-2">
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

              <!-- Tombol Aksi -->
              <div class="d-flex justify-content-end">
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
            <h5 class="fw-bold text-success">Data Ibu Hamil</h5>
            <div class="row mt-4">
              <div :class="selectedBumil ? 'col-md-8' : 'col-md-12'">
                <div class="card bg-light p-2">
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center">
                      <thead class="table-success small">
                        <tr>
                          <th @click="sortBy('nama')" class="cursor-pointer align-middle text-center">
                            Nama <SortIcon :field="'nama'" />
                          </th>
                          <th @click="sortBy('anemia')" class="cursor-pointer align-middle text-center">
                            Anemia <SortIcon :field="'anemia'" />
                          </th>
                          <th style="width:100px" @click="sortBy('berisiko')" class="cursor-pointer align-middle text-center">
                            Kehamilan Berisiko <SortIcon :field="'berisiko'" />
                          </th>
                          <th style="width:60px" @click="sortBy('kek')" class="cursor-pointer align-middle text-center">
                            KEK <SortIcon :field="'kek'" />
                          </th>
                          <th @click="sortBy('intervensi')" class="cursor-pointer align-middle text-center">
                            Intervensi <SortIcon :field="'intervensi'" />
                          </th>
                          <th @click="sortBy('rw')" class="cursor-pointer align-middle text-center">
                            RW <SortIcon :field="'rw'" />
                          </th>
                          <th @click="sortBy('rt')" class="cursor-pointer align-middle text-center">
                            RT <SortIcon :field="'rt'" />
                          </th>
                          <th style="width:100px" @click="sortBy('usia')" class="cursor-pointer align-middle text-center">
                            Usia (Tahun) <SortIcon :field="'usia'" />
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="bumil in paginatedData" :key="bumil.id" class="small">
                          <td class="text-start">
                            <a href="#" @click.prevent="showDetail(bumil)" class="fw-semibold text-decoration-none text-primary">
                              {{ bumil.nama }}
                            </a>
                          </td>
                          <td>
                            <span v-if="bumil.anemia === 'Anemia' " class="badge bg-danger text-white">Ya</span>
                            <span v-else>{{ bumil.anemia }}</span>
                          </td>
                          <td>
                            <span v-if="bumil.risiko === 'Berisiko' " class="badge bg-danger text-white">Ya</span>
                            <span v-else>{{ bumil.risiko }}</span>
                          </td>
                          <td>
                            <span v-if="bumil.kek === 'Ya' || bumil.kek === 'KEK'" class="badge bg-danger text-white">Ya</span>
                            <span v-else>{{ bumil.kek }}</span>
                          </td>
                          <td>
                            <span v-if="bumil.intervensi === 'Ya'" class="badge bg-danger text-white">Ya</span>
                            <span v-else>{{ bumil.intervensi }}</span>
                          </td>
                          <td>{{ bumil.rw }}</td>
                          <td>{{ bumil.rt }}</td>
                          <td>{{ bumil.usia }}</td>
                        </tr>
                      </tbody>

                    </table>
                  </div>
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
              <div class="col-md-4" v-if="selectedBumil">
                <div v-if="selectedBumil" class="card shadow-sm p-4 text-center small position-relative">

                  <!-- Tombol Close -->
                  <button
                    type="button"
                    class="btn-close position-absolute top-0 end-0 m-3"
                    aria-label="Close"
                    @click="selectedBumil = null"
                  ></button>

                  <!-- Nama dan Identitas -->
                  <h5 class="fw-bold text-dark mb-1">{{ selectedBumil.nama }}</h5>
                  <p class="text-muted mb-0 text-capitalize">{{ selectedBumil.kelurahan || '-' }}</p>
                  <p class="text-muted">{{ selectedBumil.kecamatan || '-' }}</p>

                  <!-- Badge Status Gizi -->
                  <div class="mb-3">
                    <span
                      class="badge px-3 py-2 small"
                      :class="{
                        'bg-danger text-dark': ['Kehamilan Berisiko'].includes(selectedBumil.status_gizi),
                        'bg-warning text-dark': ['KEK'].includes(selectedBumil.status_gizi),
                        'bg-success': selectedBumil.status_gizi === 'Normal'
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
                  <div class="bg-primary text-white p-4 text-center rounded-top">
                    <h5 class="fw-bold mb-0">{{ selectedBumil.nama }}</h5>
                    <p class="text-white mb-0 small">
                      {{ selectedBumil.usia }} Tahun - {{ selectedBumil.risiko }}
                    </p>
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
                                    <td class="fw-semibold" style="width: 120px;">Nama</td>
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
                                    <td class="fw-semibold" style="width: 120px;">Alamat</td>
                                    <td>:</td>
                                    <td>{{ selectedBumil.provinsi }}, {{ selectedBumil.kota }}, {{ selectedBumil.kecamatan }}</td>
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
                                      :class="item.asap_rokok === '1' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.asap_rokok === '0' ? 'Ya' : 'Tidak' }}
                                    </span>
                                  </td>
                                  <td>
                                    <span
                                      class="badge"
                                      :class="item.bantuan_sosial === '1' ? 'bg-danger' : 'text-dark'"
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
                                      :class="item.sumber_air_bersih === '0' ? 'bg-danger' : 'text-dark'"
                                    >
                                      {{ item.sumber_air_bersih === '0' ? 'Tidak' : 'Ya' }}
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

const API_PORT = 8000
const { protocol, hostname } = window.location
const baseURL = `${protocol}//${hostname}:${API_PORT}`

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
      kesehatan:[],
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
        posyandu:'',
        rt:'',
        rw:'',
        status: [],
        usia: [],
        intervensi: [],
        periodeAwal: '',
        periodeAkhir: '',
      },
      periodeOptions: [],
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
      filteredData.value =bumil.value.filter((c) =>
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
  computed: {
    filterOptions() {
      return {
        status: { label: 'Status', placeholder: 'Pilih Status', options: ['KEK', 'Anemia', 'Berisiko'], filter:'Filter status ibu hamil:' },
        intervensi: { label: 'Intervensi', placeholder: 'Pilih Intervensi', options: ["MBG","KIE","Bansos", "PMT", "Bantuan Lainnya", "Belum mendapatkan intervensi"],filter:'' },
        usia: { label: 'Usia (Tahun)', placeholder: 'Pilih Usia', options: ['<20', '20-30', '30-40', '>40'],filter:'' },
      }
    },
    intervesiDisplayText() {
      const total = this.filters.intervensi.length;
      const allSelected = total === this.intervensiOptions.length;

      if (allSelected) return 'All';
      if (total === 1) return this.filters.intervensi[0];
      return `${total} Selected`;
    },
  },
  methods: {
    async loadPregnancy() {
      try {
        const res = await axios.get(`${baseURL}/api/pregnancy`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          params: this.filters,
        });

        const data = res.data?.data || [];

        // ✅ Ambil semua nama posyandu dari seluruh riwayat_pemeriksaan
        const allPosyandu = data.flatMap(ibu =>
          (ibu.riwayat_pemeriksaan || [])
            .map(r => r.posyandu)
            .filter(Boolean)
        );

        // ✅ Buat list unik
        const uniquePosyandu = [...new Set(allPosyandu)];
        this.posyanduList = uniquePosyandu.map((nama, idx) => ({
          id: idx + 1,
          nama_posyandu: nama,
        }));

        // ✅ Format data bumil
        this.bumil = data.map(item => {
          const lastCheck = item.riwayat_pemeriksaan?.at(-1); // pemeriksaan terakhir
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
          };
        });

        //console.log(this.bumil);

        // ✅ Set filtered dan total
        this.filteredData = [...this.bumil];
        this.totalBumil = this.bumil.length;

      } catch (e) {
        console.error('Gagal ambil data pregnancy:', e);
        this.bumil = [];
        this.filteredData = [];
        this.posyanduList = [];
      }
    },
    async showDetail(bumil) {
      try {
        this.isLoading = true;
        //console.log('detail:', bumil);

        const nik = bumil.id;

        const res = await axios.get(`${baseURL}/api/pregnancy/${nik}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        const data = res.data;
        // Ambil riwayat kehamilan
        const riwayatKehamilan = data.kehamilan || [];

        // Ambil record terakhir
        const lastKehamilan = riwayatKehamilan.length
          ? riwayatKehamilan[riwayatKehamilan.length - 1]
          : {};

          this.selectedBumil = {
          ...data.ibu,
          riwayat_pemeriksaan: data.riwayat_pemeriksaan || [],
          riwayat_intervensi: data.riwayat_intervensi || [],
          kehamilan: data.kehamilan || [],
          risiko: lastKehamilan.risiko || '-', // ← ini tambahan
        };

        // optional console debug
        //console.log('selectedBumil:', this.selectedBumil);
      } catch (error) {
        console.error('Gagal load detail bumil:', error);
        this.selectedBumil = null;
      } finally {
        this.isLoading = false;
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
      await this.loadPregnancy(),
      await this.hitungStatusKesehatan()
    },
    async resetFilter() {
      Object.keys(this.filters).forEach(k => {
        if (Array.isArray(this.filters[k])) this.filters[k] = []
        else this.filters[k] = ''
      })
      await this.loadPregnancy(),
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
        this.filteredBumil = [...this.bumil]; // tampilkan semua
        return;
      }

      // ✅ Gunakan field yang benar
      const filteredBumil = this.bumil.filter(b => b.posyandu === pos);

      // 🔹 Ambil daftar RW & RT unik
      const rwSet = new Set(filteredBumil.map(b => b.rw).filter(Boolean));
      const rtSet = new Set(filteredBumil.map(b => b.rt).filter(Boolean));

      this.rwList = Array.from(rwSet);
      this.rtList = Array.from(rtSet);

      // 🔹 Aktifkan dropdown RW & RT
      this.rwReadonly = false;
      this.rtReadonly = false;

      // 🔹 Update data tabel
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

      const filteredBumil = this.bumil.filter(
        c => c.posyandu === pos && c.rw === rw
      );

      const rtSet = new Set(filteredBumil.map(c => c.rt).filter(Boolean));
      this.rtList = Array.from(rtSet);
      this.rtReadonly = false;
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

        const res = await axios.get(`${baseURL}/api/pregnancy/status`, {
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
  },
  created() {
    const storedEmail = localStorage.getItem('userEmail')
    this.username = storedEmail
      ? storedEmail.split('@')[0].replace(/[._]/g, ' ')
          .split(' ')
          .map(w => w.charAt(0).toUpperCase() + w.slice(1))
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
      setTimeout(() => { this.isLoading = false }, 300)
    }
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
  },
}
</script>

<style scoped>
.dropdown-menu .form-check-label {
    white-space: normal !important;
    word-break: break-word;
  }
.filter-wrapper {
  position: relative; /* biar ikut alur layout */
  z-index: 0; /* pastikan di bawah sidebar */
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
</style>
