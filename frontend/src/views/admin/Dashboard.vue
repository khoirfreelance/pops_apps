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
          <div class="card welcome-card shadow-sm mb-4 border-0">
            <div class="card-body d-flex flex-column flex-md-row align-items-start py-0 justify-content-between">
              <!-- Kiri: Teks Welcome -->
              <div class="text-start">
                <h3>
                  <span class="fw-normal fs-6">Selamat datang,</span> <br />
                  {{ username }}
                </h3>
                <img
                  v-if="logoLoaded"
                  :src="logoSrc"
                  alt="Logo"
                  height="50"
                  class="mt-4"
                  @error="logoLoaded = false"
                />
                <!-- jika gagal load logo, tampilkan kelurahan -->
                <span
                  v-else
                  class="text-muted fw-bold fs-5 mt-4"
                >
                  {{ kelurahan || 'Wilayah' }}
                </span>
                <p class="small d-flex align-items-center mt-1">
                  Data terakhir diperbarui pada &nbsp;<strong>{{ today }}</strong>
                </p>
              </div>

              <!-- Kanan: Gambar -->
              <div class="mt-3 mt-md-0">
                <img
                  src="/banner.png"
                  alt="Welcome"
                  class="img-fluid welcome-img"
                  style="max-width: 280px"
                />
              </div>
            </div>
          </div>

          <!-- Statistic Cards -->
          <div class="container-fluid mt-2">
            <div class="row justify-content-center">
              <div
                v-for="(stat, index) in stats"
                :key="index"
                class="stat-card col-lg-2 col-sm-5 col-12 mx-3 my-2 shadow-bottom border-0 border-top border-4 border-primary"
              >
                <div class="container-fluid">
                  <div class="card-body d-flex align-items-center justify-content-between mx-2">
                    <div class="icon-wrap d-flex align-items-center justify-content-center">
                      <span :class="['stat-icon', stat.icon]"></span>
                    </div>
                    <div class="text-end ms-2">
                      <h5 class="text-muted mb-1">{{ stat.title }}</h5>
                      <h2 class="card-title fw-bold mb-0">{{ stat.value }}</h2>
                    </div>
                  </div>

                </div>
              </div>

            </div>
          </div>

          <!-- Judul Laporan -->
          <div class="text-center mt-4">
            <div class="bg-primary text-white py-2 px-4 d-inline-block rounded-top">
              <h5 class="mb-0">
                Laporan Status Gizi <span class="text-capitalize fw-bold">{{ kelurahan }}</span> Bulan <span class="fw-bold">{{ thisMonth }}</span>
              </h5>
            </div>
          </div>

          <!-- Filter Form -->
          <div class="bg-light border rounded-bottom shadow-sm px-4 py-3 mt-0">
            <div class="mb-2 fw-bold text-primary">Filter:</div>

            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <!-- Kelurahan/Desa -->
              <div class="col-md-2">
                <label class="form-label">Kelurahan/Desa</label>
                <select v-model="filters.kelurahan" class="form-select text-muted" disabled>
                  <option :value="kelurahan">{{ kelurahan }}</option>
                </select>
              </div>

              <!-- Posyandu -->
              <div class="col-md-2">
                <label class="form-label">Posyandu</label>
                <select v-model="filters.posyandu" class="form-select text-muted">
                  <option value="">All</option>
                  <option v-for="item in posyanduList" :key="item.id" :value="item.id">
                    {{ item.nama_posyandu}}
                  </option>
                </select>
              </div>

              <!-- RW -->
              <div class="col-md-2">
                <label class="form-label">RW</label>
                <select v-model="filters.rw" class="form-select text-muted" :disabled="rwReadonly">
                  <option value="">All</option>
                  <option v-for="rw in rwList" :key="rw" :value="rw">{{ rw }}</option>
                </select>
              </div>

              <!-- RT -->
              <div class="col-md-2">
                <label class="form-label">RT</label>
                <select v-model="filters.rt" class="form-select text-muted" :disabled="rtReadonly">
                  <option value="">All</option>
                  <option v-for="rt in rtList" :key="rt" :value="rt">{{ rt }}</option>
                </select>
              </div>

              <!-- Periode -->

              <!-- Periode -->
              <div class="col-md-2">
                <label class="form-label">Periode</label>
                <select v-model="filters.periode" class="form-select text-muted">
                  <option value="">All</option>
                  <option v-for="periode in periodeOptions" :key="periode" :value="periode">
                    {{ periode }}
                  </option>
                </select>
              </div>

              <!-- Tombol Cari -->
              <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-gradient fw-semibold">
                  <i class="bi bi-search me-1"></i> Cari
                </button>
              </div>
            </form>
          </div>

          <!-- Main -->
          <div class="d-flex justify-content-center mt-5">
            <ul class="nav nav-pills mb-3 d-flex flex-wrap justify-content-center gap-2 w-100" id="myTab" role="tablist" style="max-width: 800px;">
              <li class="nav-item flex-fill text-center" role="presentation">
                <button
                  class="nav-link active w-100 text-truncate"
                  id="home-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#home-tab-pane"
                  type="button"
                  role="tab"
                  aria-controls="home-tab-pane"
                  aria-selected="true"
                >
                  Status Gizi Anak
                </button>
              </li>

              <li class="nav-item flex-fill text-center" role="presentation">
                <button
                  class="nav-link w-100 text-truncate"
                  id="profile-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#profile-tab-pane"
                  type="button"
                  role="tab"
                  aria-controls="profile-tab-pane"
                  aria-selected="false"
                >
                  Status Kesehatan Ibu Hamil
                </button>
              </li>

              <li class="nav-item flex-fill text-center" role="presentation">
                <button
                  class="nav-link w-100 text-truncate"
                  id="contact-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#contact-tab-pane"
                  type="button"
                  role="tab"
                  aria-controls="contact-tab-pane"
                  aria-selected="false"
                >
                  Calon Pengantin Berisiko
                </button>
              </li>
            </ul>
          </div>
          <div class="tab-content" id="myTabContent">
            <!-- Tab 1 -->
            <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" tabindex="0">

              <!-- Issue and Stat Card -->
              <div class="container-fluid my-4 row">
                <div class="col-md-8">
                  <h5 class="fw-bold text-success mb-4">Ringkasan Status Gizi Bulan Ini</h5>
                  <div class="row">
                    <div class="col-md-10 col-sm-12">
                      <div class="row justify-content-center">
                        <div
                          v-for="(item, index) in gizi"
                          :key="index"
                          class="col-lg-4 col-sm-6 col-12 mb-3"
                        >
                          <div
                            class="card shadow-sm border-0 rounded-3 overflow-hidden"
                            :class="`border-start border-4 border-${item.color}`"
                          >
                            <div class="card-body py-3 d-flex justify-content-between">
                              <div>
                                <h6 class="fw-bold mb-1">{{ item.title }}</h6>
                                <p class="mb-2 small" :class="`text-${item.color}`">{{ item.percent }}</p>
                              </div>
                              <h3 class="fw-bold mb-0" :class="`text-${item.color}`">{{ item.value }}</h3>
                            </div>

                            <!-- SVG LINE CHART -->
                            <div class="card-footer bg-transparent border-0 pt-0">
                              <svg
                                viewBox="0 0 100 30"
                                class="svg-line"
                                preserveAspectRatio="none"
                                :class="`stroke-${item.color}`"
                              >
                                <polyline
                                  fill="none"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  points="0,20 15,15 30,18 45,10 60,12 75,8 90,15 100,10"
                                />
                              </svg>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- TOTAL ANAK -->
                    <div class="col-md-2 col-sm-12">
                      <div class="card text-center shadow-sm border-0 h-100 d-flex flex-column justify-content-center">
                        <h6 class="text-muted my-4">Total Anak Balita</h6>
                        <div class="flex-grow-1 d-flex flex-column justify-content-center">
                          <h2 class="fw-bold text-success mb-0">111</h2>
                        </div>
                        <i class="bi bi-people fs-3 text-dark mt-2 mb-3"></i>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- INFO BOXES -->
                <div class="col-md-4 mt-3">
                  <div class="alert alert-danger p-3 mb-2">
                    <strong>Stunting naik +1.4 p.p</strong><br />
                    Dibanding bulan lalu tertinggi di Desa Wonosari
                  </div>
                  <div class="alert alert-warning p-3 mb-2">
                    <strong>Intervensi</strong><br />
                    22 Anak dalam masalah gizi. Stunting belum mendapat bantuan.
                  </div>
                  <div class="alert alert-info p-3 mb-2">
                    <strong>Kasus baru</strong><br />
                    15 Stunting, 3 Wasting, 4 BB Stagnan.
                  </div>
                  <div class="alert alert-success p-3">
                    <strong>Data Pending</strong><br />
                    10 Data tidak lengkap belum diperbarui dari bulan sebelumnya.
                  </div>
                </div>
              </div>

              <!-- Pie Chart-->
              <div class="container-fluid my-4 row">
                <div class="col-md-12">
                  <h5 class="fw-bold text-primary mb-4">Ringkasan Status Gizi Bulan Ini</h5>
                </div>
                <div class="col-12 col-md-8">
                  <!-- Berat Badan / Usia -->
                  <div class="card border border-primary shadow p-3 my-3">
                    <h4 class="fw-bold text-primary">Berat Badan / Usia</h4>
                    <div class="row">
                      <!-- Table -->
                      <div class="col-12 col-md-7 table-responsive">
                        <table class="table table-borderless align-middle">
                          <tbody>
                            <tr>
                              <td class="text-additional fw-semibold">Status</td>
                              <td class="text-additional fw-semibold">Jumlah</td>
                              <td class="text-additional fw-semibold">Persen</td>
                              <td class="text-additional fw-semibold">Tren</td>
                            </tr>
                            <tr v-for="(row, index) in dataTable_bb" :key="index">
                              <td class="text-additional small">{{ row.status }}</td>
                              <td class="text-additional small">{{ row.jumlah }}</td>
                              <td class="text-additional small">{{ row.persen }} %</td>
                              <td class="small" :class="row.trenClass">
                                <span v-if="row.tren !== '-'">
                                  <i :class="row.trenIcon"></i> {{ row.tren }}
                                </span>
                                <span v-else>-</span>
                              </td>
                            </tr>
                          </tbody>
                          <tfoot>
                            <tr>
                              <td colspan="4" class="pt-4"><a href="">Lihat Grafik Selengkapnya</a></td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>

                      <!-- Chart -->
                      <div class="col-12 col-md-5 text-center">
                        <canvas ref="pieChart_bb" class="mx-auto" style="max-width: 300px; max-height: 300px; min-width: 200px; min-height: 200px;"></canvas>
                      </div>
                    </div>
                  </div>

                  <!-- Tinggi Badan / Usia -->
                  <div class="card border border-primary shadow p-3 my-3">
                    <h4 class="fw-bold text-primary">Tinggi Badan / Usia</h4>
                    <div class="row">
                      <div class="col-12 col-md-7 table-responsive">
                        <table class="table table-borderless align-middle">
                          <tbody>
                            <tr>
                              <td class="text-additional fw-bold">Status</td>
                              <td class="text-muted fw-bold">Jumlah</td>
                              <td class="text-muted fw-bold">Persen</td>
                              <td class="text-muted fw-bold">Tren</td>
                            </tr>
                            <tr v-for="(row, index) in dataTable_tb" :key="index">
                              <td class="text-additional small">{{ row.status }}</td>
                              <td class="text-additional small">{{ row.jumlah }}</td>
                              <td class="text-additional small">{{ row.persen }} %</td>
                              <td class="small" :class="row.trenClass">
                                <span v-if="row.tren !== '-'">
                                  <i :class="row.trenIcon"></i> {{ row.tren }}
                                </span>
                                <span v-else>-</span>
                              </td>
                            </tr>
                          </tbody>
                          <tfoot>
                            <tr>
                              <td colspan="4" class="pt-4"><a href="">Lihat Grafik Selengkapnya</a></td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                      <div class="col-12 col-md-5 text-center">
                        <canvas ref="pieChart_tb" class="mx-auto" style="max-width: 300px; max-height: 300px; min-width: 200px; min-height: 200px;"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <!-- Berat Badan / Tinggi Badan -->
                  <div class="card border border-primary shadow p-3 my-3">
                    <h4 class="fw-bold text-primary">Berat Badan / Tinggi Badan</h4>
                    <div class="row">
                      <div class="col-12 table-responsive">
                        <table class="table table-borderless align-middle">
                          <tbody>
                            <tr>
                              <td class="text-additional fw-bold">Status</td>
                              <td class="text-muted fw-bold">Jumlah</td>
                              <td class="text-muted fw-bold">Persen</td>
                              <td class="text-muted fw-bold">Tren</td>
                            </tr>
                            <tr v-for="(row, index) in dataTable_status" :key="index">
                              <td class="text-additional small">{{ row.status }}</td>
                              <td class="text-additional small">{{ row.jumlah }}</td>
                              <td class="text-additional small">{{ row.persen }} %</td>
                              <td class="small" :class="row.trenClass">
                                <span v-if="row.tren !== '-'">
                                  <i :class="row.trenIcon"></i> {{ row.tren }}
                                </span>
                                <span v-else>-</span>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <div class="col-12 text-center">
                        <canvas
                          ref="pieChart_status"
                          class="mx-auto"
                          style="max-width: 300px; max-height: 300px; min-width: 200px; min-height: 200px;"
                        ></canvas>
                        <div class="d-flex flex-wrap justify-content-between mt-3">
                          <a href="">Lihat Grafik Selengkapnya</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Ringkasan -->
              <div class="container-fluid my-4">
                <div class="row">
                  <div class="col-12">
                    <h5 class="fw-bold text-primary mb-4">Anak Dengan Masalah Gizi Ganda</h5>
                  </div>

                  <!-- CARD UTAMA -->
                  <div class="col-12">
                    <div class="card shadow-sm border-0 p-4 rounded-4">

                      <!-- HEADER -->
                      <div class="text-center position-relative mb-0">
                        <h4
                          class="fw-bold text-white py-4 px-3 rounded-bottom-5 d-inline-block bg-primary w-75"
                        >
                          Jumlah {{ totalKasus }} Anak
                        </h4>

                        <!-- TAB NAV -->
                        <div
                          class="d-flex justify-content-center position-relative"
                          style="margin-top: -20px;"
                        >
                          <ul
                            class="nav nav-pills d-flex flex-wrap justify-content-center gap-2 w-50"
                            id="myTab"
                            role="tablist"
                            style="max-width: 800px;"
                          >
                            <li class="nav-item flex-fill text-center" role="presentation">
                              <button
                                class="nav-link active w-100 text-truncate fw-semibold rounded-pill border-bottom border-primary shadow-sm btn btn-outline-primary"
                                id="belum-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#belum-tab-pane"
                                type="button"
                                role="tab"
                                aria-controls="belum-tab-pane"
                                aria-selected="true"
                              >
                                Anak belum dapat Intervensi
                              </button>
                            </li>
                            <li class="nav-item flex-fill text-center" role="presentation">
                              <button
                                class="nav-link w-100 text-truncate fw-semibold rounded-pill shadow-sm btn btn-dark"
                                id="dapat-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#dapat-tab-pane"
                                type="button"
                                role="tab"
                                aria-controls="dapat-tab-pane"
                                aria-selected="false"
                              >
                                Anak sudah dapat Intervensi
                              </button>
                            </li>
                          </ul>
                        </div>
                      </div>


                      <!-- ISI GRID -->
                      <div class="row g-3 mt-3">
                        <!-- KIRI ATAS -->
                        <div class="col-md-4 col-sm-12">
                          <div class="card shadow-sm border-0 h-100 p-3">
                            <h6 class="text-center text-success mb-2">
                              Jumlah anak tidak membaik<br />
                              <span class="fw-bold text-dark">3 bulan terakhir</span>
                            </h6>
                            <div class="chart-placeholder text-muted text-center py-4">
                              <canvas ref="lineChart"></canvas>
                            </div>
                          </div>
                        </div>

                        <!-- TENGAH ATAS -->
                        <div class="col-md-4 col-sm-12">
                          <div class="card shadow-sm border-0 h-100 p-3">
                            <h6 class="text-center text-success mb-2">Diagram Intervensi</h6>
                            <div class="chart-placeholder text-muted text-center py-4">
                              <canvas ref="funnelChart"></canvas>
                            </div>
                          </div>
                        </div>

                        <!-- KANAN ATAS -->
                        <div class="col-md-4 col-sm-12">
                          <div class="card shadow-sm border-0 h-100 p-3">
                            <h6 class="text-center text-success mb-2">
                              Top 5 Posyandu<br />
                              <span class="text-dark">dengan anak tidak membaik dalam 3 bulan terakhir</span>
                            </h6>
                            <div class="chart-placeholder text-muted text-center py-4">
                              <canvas ref="barChart"></canvas>
                            </div>
                          </div>
                        </div>

                        <!-- BAWAH: TABEL -->
                        <div class="col-md-8 col-sm-12">
                          <div class="card shadow-sm border-0 h-100 p-3 table-responsive">
                            <table class="table table-sm align-middle">
                              <thead class="table-light">
                                <tr>
                                  <th>No</th>
                                  <th>Nama</th>
                                  <th>Rumusan</th>
                                  <th>Stunting</th>
                                  <th>Wasting</th>
                                  <th>Underweight</th>
                                  <th>BB Sangat</th>
                                  <th>Overweight</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="(anak, i) in sampleData" :key="i">
                                  <td>{{ i + 1 }}</td>
                                  <td>{{ anak.nama }}</td>
                                  <td>{{ anak.rumusan }}</td>
                                  <td><i v-if="anak.stunting" class="bi bi-check2"></i></td>
                                  <td><i v-if="anak.wasting" class="bi bi-check2"></i></td>
                                  <td><i v-if="anak.underweight" class="bi bi-check2"></i></td>
                                  <td><i v-if="anak.bb_sangat" class="bi bi-check2"></i></td>
                                  <td><i v-if="anak.overweight" class="bi bi-check2"></i></td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>

                        <!-- BAWAH: KARTU RINGKAS -->
                        <div class="col-md-4 col-sm-12">
                          <div class="card shadow-sm border-0 h-100 p-3 alert alert-success">
                            <h6 class="fw-bold mb-3 text-primary text-center">Kartu Ringkas Gap</h6>
                            <p class="small text-muted">
                              Dari {{ totalKasus }} Anak yang tidak membaik:
                            </p>
                            <ul class="small">
                              <li><b>24</b> (68%) belum menerima PMT</li>
                              <li><b>15</b> (41%) belum menerima bantuan apapun</li>
                              <li><b>8</b> (22%) belum dikunjungi kader</li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <!-- Tab 2 -->
            <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" tabindex="0">
              Status Kesehatan Ibu Hamil
            </div>

            <!-- Tab 3 -->
            <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" tabindex="0">
              Calon Pengantin Berisiko
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
import axios from 'axios'
import {
  Chart,
  PieController,
  ArcElement,
  Tooltip,
  Legend,
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  LineController,
  LineElement,
  PointElement,
  Filler,
} from 'chart.js'
import ChartDataLabels from 'chartjs-plugin-datalabels'
// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend)
Chart.register(PieController, ArcElement, Tooltip, Legend, ChartDataLabels)
Chart.register(
  LineController,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
  Filler,
)
export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Dashboard',
  components: { NavbarAdmin, CopyRight, HeaderAdmin },
  data() {
    return {
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
      stats: [],
      filters: {
        kelurahan: '',
        posyandu: '',
        rw: '',
        rt: '',
        periode: '',
      },
      gizi:[
        { title: "Stunting", value: 1, percent: "0%", color: "danger" },
        { title: "Wasting", value: 1, percent: "0%", color: "warning" },
        { title: "Underweight", value: 1, percent: "0%", color: "violet" },
        { title: "Normal", value: 1, percent: "0%", color: "success" },
        { title: "BB Stagnan", value: 1, percent: "0%", color: "info" },
        { title: "Overweight", value: 1, percent: "0%", color: "dark" },
      ],
      dev:0,
      posyanduList: [],
      rwList: [],
      rtList: [],
      periodeOptions: [],
      rwReadonly: true,
      rtReadonly: true,
      dataTable_bb: [
        {
          status: 'Sangat Kurang',
          jumlah: 24,
          persen: 2.8,
          tren: '2.80%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Kurang',
          jumlah: 94,
          persen: 10.96,
          tren: '10.96%',
          trenClass: 'text-success',
          trenIcon: 'bi bi-caret-down-fill',
        },
        {
          status: 'Normal',
          jumlah: 725,
          persen: 55,
          tren: '55 %',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Risiko Lebih',
          jumlah: 15,
          persen: 33.45,
          tren: '33.45%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Tidak Naik',
          jumlah: 287,
          persen: 33.45,
          tren: '33.45%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
      ],
      dataTable_tb: [
        {
          status: 'Sangat Pendek',
          jumlah: 21,
          persen: 2.45,
          tren: '2.45%',
          trenClass: 'text-success',
          trenIcon: 'bi bi-caret-down-fill',
        },
        {
          status: 'Pendek',
          jumlah: 149,
          persen: 17.37,
          tren: '17.37%',
          trenClass: 'text-success',
          trenIcon: 'bi bi-caret-down-fill',
        },
        {
          status: 'Normal',
          jumlah: 688,
          persen: 80.19,
          tren: ' 80.19%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Tinggi',
          jumlah: 34,
          persen: 2,
          tren: '2%',
          trenClass: 'text-muted',
          trenIcon: '',
        },
      ],
      dataTable_status: [
        {
          status: 'Gizi Buruk',
          jumlah: 4,
          persen: 2.80 ,
          tren: '2.80%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Gizi Kurang',
          jumlah: 20,
          persen: 10.96 ,
          tren: '10.96%',
          trenClass: 'text-success',
          trenIcon: 'bi bi-caret-down-fill',
        },
        {
          status: 'Gizi Baik',
          jumlah: 769,
          persen: 84.50 ,
          tren: ' 84.50%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Risiko Gizi Lebih',
          jumlah: 53,
          persen: 1.75,
          tren: '1.75%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Gizi Lebih',
          jumlah: 8,
          persen: 33.45,
          tren: '33.45%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Obesitas',
          jumlah: 10,
          persen: 12,
          tren: '12%',
          trenClass: 'text-muted',
          trenIcon: '',
        },
      ],
      sampleData: [
        {
          nama: "Ahsan Dimasi Aqilana",
          rumusan: "BLH, PMT, Bantuan",
          stunting: true,
          wasting: false,
          underweight: true,
          bb_sangat: false,
          overweight: false,
        },
        {
          nama: "Anak Kedua",
          rumusan: "BLH, PMT",
          stunting: false,
          wasting: true,
          underweight: false,
          bb_sangat: false,
          overweight: true,
        },
      ],
    }
  },
  methods: {
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
    renderLineChart() {
      new Chart(this.$refs.lineChart, {
        type: 'line',
        data: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr'],
          datasets: [
            {
              label: 'Tidak membaik (%)',
              data: [10, 13, 12, 11],
              borderColor: 'goldenrod',
              backgroundColor: 'rgba(255, 215, 0, 0.3)',
              fill: true,
              tension: 0.3,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } },
        },
      })
    },
    renderBarChart() {
      new Chart(this.$refs.barChart, {
        type: 'bar',
        data: {
          labels: ['Mawar', 'Tulip', 'Melati', 'Anggrek', 'Teratai'],
          datasets: [
            {
              label: 'Kasus',
              data: [10, 6, 12, 8, 10],
              backgroundColor: 'rgba(255, 99, 132, 0.6)',
              borderRadius: 8,
            },
          ],
        },
        options: {
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } },
        },
      })
    },
    renderFunnelChart() {
      new Chart(this.$refs.funnelChart, {
        type: 'bar',
        data: {
          labels: ['Ditugaskan kader', 'Dikunjungi kader', 'Dapat PMT','Belum Mendapat Bantuan'],
          datasets: [
            {
              data: [8, 20, 9, 13],
              backgroundColor: ['#006341', '#6FA287', '#6D8B7B', '#9FE0BD'],
              color: '#FFFFFF',
            },
          ],
        },
        options: {
          indexAxis: 'y',
          plugins: { legend: { display: false }, datalabels: {
          color: '#FFFFFF',        // warna teks label
          anchor: 'center',        // posisi di tengah bar
          align: 'center',         // teks di tengah
          font: { weight: 'bold' } // biar lebih jelas
        }, },
          scales: {
            x: { beginAtZero: true },
          },
        },
      })
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
        await this.fetchPosyanduByWilayah(this.id_wilayah)
      } catch (error) {
        console.error('Gagal ambil data wilayah user:', error)
        this.kelurahan = '-'
      }
    },
    async fetchPosyanduByWilayah(id_wilayah) {
      try {
        const res = await axios.get(`${baseURL}/api/posyandu/${id_wilayah}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })
        this.posyanduList = res.data
      } catch (err) {
        console.error('Gagal ambil data posyandu:', err)
      }
    },
    handlePosyanduChange() {
      console.log('Posyandu dipilih:', this.filters.posyandu)
      if (this.filters.posyandu) {
        this.rtReadonly = false
        this.rwReadonly = false
        this.fetchRwRtByPosyandu(this.filters.posyandu)
      } else {
        this.rtReadonly = true
        this.rwReadonly = true
        this.rwList = []
        this.rtList = []
      }
    },
    async fetchRwRtByPosyandu(idPosyandu) {
      try {
        const res = await axios.get(`${baseURL}/api/posyandu/${idPosyandu}/wilayah`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })
        this.rwList = res.data.rw || []
        this.rtList = res.data.rt || []
        console.log('data', res.data);

      } catch (err) {
        console.error('Gagal ambil RW/RT:', err)
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
    applyFilter() {
      console.log('Filter diterapkan:', this.filters)
      // nanti bisa dipakai buat fetch data laporan
    },
    async fetchStats() {
      try {
        const res = await axios.get(`${baseURL}/api/dashboard/stats`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        })
        const data = res.data
        this.stats = [
          { title: 'RW', value: data.rw, icon: 'bi bi-houses-fill' },
          { title: 'RT', value: data.rt, icon: 'bi bi-house-fill' },
          { title: 'Keluarga Terdaftar', value: data.keluarga, icon: 'fa-solid fa-people-roof' },
          { title: 'TPK', value: data.tpk, icon: 'bi bi-person-vcard' },
          { title: 'Ibu Hamil', value: data.ibu_hamil, icon: 'fa-solid fa-person-pregnant' },
          { title: 'Posyandu', value: data.posyandu, icon: 'bi bi-heart-pulse' },
          { title: 'Bidan', value: data.bidan, icon: 'fa-solid fa-stethoscope' },
          { title: 'Calon Pengantin', value: data.catin, icon: 'bi bi-arrow-through-heart' },
          { title: 'Anak <= 5 Tahun', value: data.anak, icon: 'fa-solid fa-baby' },
        ]
      } catch (e) {
        console.error(e)
      }
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
    renderChart(refName, dataSource, colors) {
      const ctx = this.$refs[refName]?.getContext('2d')
      if (!ctx) return

      // Hancurkan chart lama
      if (this[refName + 'Instance']) {
        this[refName + 'Instance'].destroy()
      }

      // Buat warna transparan & border
      const transparentColors = colors.map(color =>
        color.replace('rgb', 'rgba').replace(')', ', 0.8)')
      )

      this[refName + 'Instance'] = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: dataSource.map(row => row.status),
          datasets: [
            {
              data: dataSource.map(row => row.persen),
              backgroundColor: transparentColors,
              borderColor: '#fff',
              borderWidth: 1,
              borderAlign: 'inner',
              hoverOffset: 12,
            },
          ],
        },
        options: {
          responsive: true,
          layout: { padding: 20 },
          plugins: {
            legend: {
              display: false,
              position: 'bottom',
              labels: {
                usePointStyle: true,
                pointStyle: 'circle',
                padding: 16,
                font: { size: 11 },
                color: '#333',
              },
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  const label = context.label || ''
                  const value = context.parsed
                  return `${label}: ${value}%`
                },
              },
            },
            datalabels: {
              display: false, // ❌ tidak tampilkan label di dalam chart
            },
          },
          onHover: (event, elements) => {
            event.native.target.style.cursor = elements.length ? 'pointer' : 'default'
          },
          /* onClick: (event, elements) => {
            if (elements.length > 0) {
              const index = elements[0].index
              const item = dataSource[index]
              const status = item.status

              // Tentukan tipe chart berdasarkan refName
              let chartType = ''
              if (refName === 'pieChart_bb') chartType = 'Berat Badan / Usia'
              else if (refName === 'pieChart_tb') chartType = 'Tinggi Badan / Usia'
              else if (refName === 'pieChart_status') chartType = 'Berat Badan / Tinggi Badan'

              // 🔥 Update reactive data untuk show table
              this.selectedChartStatus = status
              this.selectedChartType = chartType
              this.showDetailSection = true

              // Scroll ke section detail biar user langsung lihat
              this.$nextTick(() => {
                const section = document.querySelector('#detail-section')
                if (section) section.scrollIntoView({ behavior: 'smooth' })
              })
            }
          }, */
        },
        plugins: [ChartDataLabels],
      })
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
      // Pastikan spinner sempat tampil
      await this.$nextTick()
      await this.loadConfigWithCache()
      // Ambil data dulu
      await this.getWilayahUser()
      await this.fetchStats()
      this.generatePeriodeOptions()
      this.filters.kelurahan = this.kelurahan

      // Render chart setelah data tersedia
      this.renderChart('pieChart_bb', this.dataTable_bb, [
        '#f5ebb9', '#f7db7f', '#7dae9b', '#bfbbe4', '#e87d7b',
      ])
      this.renderChart('pieChart_tb', this.dataTable_tb, [
        '#f7db7f', '#bfbbe4', '#7dae9b', '#e87d7b',
      ])
      this.renderChart('pieChart_status', this.dataTable_status, [
        '#f5ebb9', '#f7db7f', '#7dae9b', '#bfbbe4', '#e87d7b', '#eaafdd',
      ])

      this.renderLineChart()
      this.renderBarChart()
      this.renderFunnelChart()
      this.loadConfigWithCache()
      this.handleResize()
      window.addEventListener('resize', this.handleResize)
    } catch (err) {
      console.error('Error loading data:', err)
    } finally {
      // Kasih sedikit delay biar animasi fade spinner kelihatan
      setTimeout(() => {
        this.isLoading = false
      }, 300)
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
  .active-tab {
    background-color: var(--bs-primary) !important;
    color: white !important;
  }
  .chart-placeholder {
    background-color: #f8f9fa;
    border-radius: 10px;
    min-height: 150px;
  }

  .svg-line {
    width: 100%;
    height: 40px;
    opacity: 0.9;
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
