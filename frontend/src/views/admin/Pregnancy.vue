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
            <div class="bg-primary text-white py-1 px-4 d-inline-block rounded-top">
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
                class="col-md-3 position-relative"
              >
                <label class="form-label text-primary">{{ filter.label }}</label>
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                  >
                    <span v-if="!filters[key].length" class="text-muted">{{ filter.placeholder }}</span>
                    <span v-else>{{ filters[key].join(', ') }}</span>
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
                      <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" @click="selectAll(key)">
                        Pilih Semua
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold" @click="clearAll(key)">
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Periode -->
              <div class="col-md-6 text-center">
                <label class="form-label text-primary">Pilih Periode:</label>
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

              <!-- Tombol Aksi -->
              <div class="col-md-3 d-flex justify-content-between">
                <button type="submit" class="btn btn-gradient fw-semibold">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>
                <button type="button" class="btn btn-secondary fw-semibold" @click="resetFilter">
                  <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
              </div>
            </form>
          </div>

          <!-- Ringkasan Statistik-->
          <div class="container-fluid my-4">

            <div class="row">
              <div class="col-xl-10 col-sm-12">
                <div class="row justify-content-center">
                  <div
                    v-for="(item, index) in kesehatan"
                    :key="index"
                    class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12 mb-3"
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

              <!-- TOTAL BUMIL -->
              <div class="col-xl-2 col-sm-12">
                <div class="card text-center shadow-sm border p-2 h-100 d-flex flex-column justify-content-center">
                  <h6 class="text-muted fw-bold">Total Ibu Hamil</h6>
                  <div class="flex-grow-1 d-flex flex-column justify-content-center">
                    <h1 class="fw-bold text-success mb-0">{{totalBumil}}</h1>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Table and detail Section -->
          <div class="container-fluid mt-4">
            <h5 class="fw-bold text-success mb-3">Data Ibu Hamil</h5>
            <div class="row mt-4">
              <div :class="selectedBumil ? 'col-md-8 mb-3' : 'col-md-12 mb-3'">
                <div class="card bg-light px-2 py-5">
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center">
                      <thead class="table-light small">
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
                          <td>{{ bumil.anemia }}</td>
                          <td>{{ bumil.kek }}</td>
                          <td>{{ bumil.intervensi || '-' }}</td>
                          <td>{{ bumil.rw }}</td>
                          <td>{{ bumil.rt }}</td>
                          <td>{{ bumil.usia }}</td>
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
                  <p class="text-muted mb-0 text-capitalize">{{ selectedBumil.kelurahan || 'Desa Wonosari, Kec. Bojong Gede' }}</p>
                  <p class="text-muted">{{ selectedBumil.kecamatan || 'Posyandu Mawar' }}</p>

                  <!-- Badge Status Gizi -->
                  <div class="mb-3">
                    <span
                      class="badge px-3 py-2 small"
                      :class="{
                        'bg-danger': ['Kehamilan Berisiko'].includes(selectedBumil.status_gizi),
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
                              :class="{
                                'bg-danger': r.anemia === 'Ya',
                              }"
                            >
                              {{ r.anemia }}
                            </span>
                          </td>
                          <td>
                            <span
                              class="badge"
                              :class="{
                                'bg-danger': r.kek === 'Ya',
                              }"
                            >
                              {{ r.kek }}
                            </span>
                          </td>
                          <td>
                            <span
                              class="badge"
                              :class="{
                                'bg-danger': r.risiko === 'Tinggi',
                              }"
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
                      {{ selectedBumil.usia }} Tahun - {{ selectedBumil.risiko }} Tahun
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
                          <i class="bi bi-person-badge me-1"></i> Profile Ibu Hamil
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
                            <div class="card bg-light border-0 shadow-sm p-3 h-100">
                              <h6 class="fw-bold mb-3 text-danger">Identitas Anak</h6>
                              <p class="mb-1"><strong>Nama:</strong> {{ selectedBumil.nama }}</p>
                              <p class="mb-1"><strong>NIK:</strong> {{ selectedBumil.nik }}</p>
                              <p class="mb-1"><strong>Usia:</strong> {{ selectedBumil.usia }} Tahun</p>
                              <p class="mb-1"><strong>Nama Suami:</strong> {{ selectedBumil.nama_suami }}</p>

                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="card bg-light border-0 shadow-sm p-3 h-100">
                              <h6 class="fw-bold mb-3 text-danger">Alamat</h6>
                              <p class="mb-1"><strong>Alamat:</strong> {{ selectedBumil.alamat }}</p>
                              <p class="mb-1"><strong>Desa:</strong> {{ selectedBumil.kelurahan }}</p>
                              <p class="mb-1"><strong>RW:</strong> {{ selectedBumil.rw }}</p>
                              <p class="mb-1"><strong>RT:</strong> {{ selectedBumil.rt }}</p>
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
                                <tr>
                                  <th>Tanggal Pemeriksaan</th>
                                  <th>Kehamilan ke</th>
                                  <th>Risiko</th>
                                  <th>TB (cm)</th>
                                  <th>BB (kg)</th>
                                  <th>Lila (cm)</th>
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
                                >
                                  <td>{{ item.tgl_pendampingan }}</td>
                                  <td>{{ item.kehamilan_ke }}</td>
                                  <td>{{ item.risiko }}</td>
                                  <td>{{ item.tb }}</td>
                                  <td>{{ item.bb }}</td>
                                  <td>{{ item.lila}}</td>
                                  <td>{{ item.kek}}</td>
                                  <td>{{ item.hb }}</td>
                                  <td>{{ item.anemia }}</td>
                                  <td>{{ item.asap_rokok }}</td>
                                  <td>{{ item.bantuan_sosial }}</td>
                                  <td>{{ item.jamban_sehat}}</td>
                                  <td>{{ item.sumber_air_bershi}}</td>
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
      filters: {
        kek: [],
        anemia: [],
        beresiko: [],
        usia: [],
        intervensi: [],
        periodeAwal: '',
        periodeAkhir: '',
      },

      periodeOptions: [],
    }
  },
  computed: {
    filterOptions() {
      return {
        anemia: { label: 'Anemia', placeholder: 'Pilih Anemia', options: ['Ya', 'Tidak'] },
        kek: { label: 'KEK', placeholder: 'Pilih KEK', options: ['Ya', 'Tidak'] },
        beresiko: { label: 'Beresiko', placeholder: 'Pilih Risiko', options: ['Tinggi', 'Rendah', 'Normal'] },
        usia: { label: 'Usia', placeholder: 'Pilih Usia', options: ['<20', '20-35', '>35'] },
        intervensi: { label: 'Intervensi', placeholder: 'Pilih Intervensi', options: ["MBG","KIE","Bansos", "PMT", "Bantuan Lainnya", "Belum mendapatkan intervensi"] },
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
    async loadConfigWithCache() {
      try {
        const cached = localStorage.getItem(this.configCacheKey)
        if (cached) {
          const parsed = JSON.parse(cached)
          this.logoSrc = parsed.logo || this.logoSrc
          return
        }

        const res = await axios.get(`${baseURL}/api/config`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })

        const data = res.data?.data
        if (data) {
          this.logoSrc = data.logo || this.logoSrc
          localStorage.setItem(this.configCacheKey, JSON.stringify(data))
        }
      } catch (e) {
        console.warn('Gagal load config:', e)
      }
    },

    async getWilayahUser() {
      try {
        const res = await axios.get(`${baseURL}/api/user/region`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })
        const wilayah = res.data
        this.kelurahan = wilayah.kelurahan || '-'
      } catch (e) {
        console.error('Gagal ambil wilayah user:', e)
        this.kelurahan = '-'
      }
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

    applyFilter() {
      // TODO: Tambahkan logika filter data
      this.filteredBumil = this.bumil
    },

    resetFilter() {
      Object.keys(this.filters).forEach(k => {
        if (Array.isArray(this.filters[k])) this.filters[k] = []
        else this.filters[k] = ''
      })
      this.filteredBumil = this.bumil
    },

    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },

    getTodayDate() {
      const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
      const bulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
      ]
      const now = new Date()
      return `${hari[now.getDay()]}, ${now.getDate()} ${bulan[now.getMonth()]} ${now.getFullYear()}`
    },

    getThisMonth() {
      const bulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
      ]
      const now = new Date()
      return `${bulan[now.getMonth()]} ${now.getFullYear()}`
    },

    hitungStatusKesehatan() {
      //const dataBumil = this.filteredData.length ? this.filteredData : this.bumil;
      const total = 0; //dataBumil.length;

      const count = {
        KEK: 0,
        Anemia: 0,
        Berisiko: 0,
        Normal: 0,
      };

      /* dataBumil.forEach((ibu_hamil) => {
        const { bbu, tbu, bbtb } = ibu_hamil;

        if (tbu?.includes('Stunted')) count.Stunting++;
        if (bbtb?.includes('Wasted')) count.Wasting++;
        if (bbu?.includes('Underweight')) count.Underweight++;
        if (bbu === 'Normal' && tbu === 'Normal' && bbtb === 'Normal') count.Normal++;
        if (bbtb?.includes('Overweight')) count.Overweight++;

        // === cek stagnan (3 kali BB sama)
        const riwayat = ibu_hamil.raw?.posyandu || [];
        if (riwayat.length >= 3) {
          const last3 = riwayat.slice(-3);
          const allEqual = last3.every((r) => r.bb === last3[0].bb);
          if (allEqual) count['BB Stagnan']++;
        }
      }); */

      this.kesehatan = Object.entries(count).map(([title, value]) => {
        const percent = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
        const colorMap = {
          KEK: 'danger',
          Anemia: 'warning',
          Beresiko: 'violet',
          Normal: 'success',
        };
        return { title, value, percent, color: colorMap[title] };
      });
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
      await this.loadConfigWithCache()
      this.hitungStatusKesehatan()
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
