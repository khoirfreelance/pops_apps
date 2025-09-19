<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div>
    <!-- Navbar -->
    <NavbarUser />

    <!-- Section Cover -->
    <section id="section0" class="hero-section position-relative text-white">
      <!-- Hero Content -->
      <div class="hero-content d-flex flex-column align-items-center justify-content-center text-center py-5">
        <h5 class="fw-semibold text-dark">Selamat Datang di</h5>
        <h1 class="fw-bold display-4 text-primary">
          Pusat Operasi Penurunan Stunting (POPS)
        </h1>
        <!-- <h3 class="fw-bold text-primary">Kabupaten Brebes ▼</h3> -->
      </div>

      <!-- Hero Image -->
      <div class="hero-image text-start top-0">
        <img src="/src/assets/anak.png" alt="Anak" class="img-fluid hero-anak">
      </div>

      <!-- Stats -->
      <div class="stats-wrapper container position-relative z-2 mt-3">
        <div class="stats-grid">
          <div
            v-for="item in stats"
            :key="item.title"
            class="card stat-card h-100 py-2 position-relative shadow"
          >
            <div class="card-body p-2 d-flex flex-column justify-content-between">
              <h3 class="fw-bold text-success position-absolute top-0 end-0 m-2">
                {{ item.value }}
              </h3>
              <div class="d-flex align-items-center justify-content-between mt-auto pt-4">
                <h6 class="mb-0 text-primary fw-semibold">{{ item.title }}</h6>
                <div class="icon-wrapper text-muted mt-2">
                  <i :class="item.icon + ' fs-5'"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </section>

    <!-- Section Gizi -->
    <section class="py-5" id="section2" style="background-image: url('/src/assets/pattern_bg.png');background-position: center;background-size: contain;">
      <div class="container">
        <ul class="nav nav-pills justify-content-center mb-4" id="chartTabs" role="tablist">
          <li class="nav-item me-2">
            <button
              class="nav-link active"
              id="gizi-tab"
              data-bs-toggle="pill"
              data-bs-target="#gizi"
              type="button"
              role="tab"
            >
               Gizi Anak
            </button>
          </li>
          <li class="nav-item">
            <button
              class="nav-link"
              id="hamil-tab"
              data-bs-toggle="pill"
              data-bs-target="#hamil"
              type="button"
              role="tab"
            >
              Ibu Hamil
            </button>
          </li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade show active" id="gizi" role="tabpanel">
            <!-- Alert -->
            <!-- <div class="alert alert-info">
              <i></i>
              Total Data <strong>Gizi Anak</strong> Per Bulan {{ currentMonthYear }}:
              <strong>{{ totalData }}</strong>
            </div> -->

            <div class="mt-5">
              <h5 class="mb-0 fw-bold">Status Gizi Anak</h5>
              <h1 class="text-primary fw-bold">Berdasarkan Kategori Usia</h1>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
              <!-- Nav Pills -->
              <ul class="nav nav-pills mb-2 mb-md-0" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link active"
                    id="pills-bb-usia-tab"
                    data-bs-toggle="pill"
                    data-bs-target="#pills-bb-usia"
                    type="button"
                    role="tab"
                  >
                    Berat Badan / Usia
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link"
                    id="pills-tb-usia-tab"
                    data-bs-toggle="pill"
                    data-bs-target="#pills-tb-usia"
                    type="button"
                    role="tab"
                  >
                    Tinggi Badan / Usia
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link"
                    id="pills-bb-tb-tab"
                    data-bs-toggle="pill"
                    data-bs-target="#pills-bb-tb"
                    type="button"
                    role="tab"
                  >
                    Berat Badan / Tinggi Badan
                  </button>
                </li>
              </ul>

              <!-- Filter Form -->
              <form class="d-flex align-items-center gap-2" @submit.prevent="applyFilter">
                <select v-model="selectedMonth" class="form-select">
                  <option v-for="(m, idx) in months" :key="idx" :value="idx + 1">
                    {{ m }}
                  </option>
                </select>

                <select v-model="selectedYear" class="form-select">
                  <option v-for="y in yearOptions" :key="y" :value="y">
                    {{ y }}
                  </option>
                </select>

                <button type="submit" class="btn btn-gradient px-5">
                  Cari
                </button>
              </form>
            </div>

            <!-- content -->
            <div class="p-4 rounded shadow-sm text-center">

              <!-- Tabs Content -->
              <div class="tab-content" id="pills-tabContent">
                <!-- BB/U -->
                <div class="tab-pane fade show active" id="pills-bb-usia" role="tabpanel">
                  <h5>Berat Badan / Usia</h5>
                  <div class="bg-light chart-container">
                    <canvas id="chart-bb-usia"></canvas>
                  </div>
                </div>

                <!-- TB/U -->
                <div class="tab-pane fade" id="pills-tb-usia" role="tabpanel">
                  <h5>Tinggi Badan / Usia</h5>
                  <div class="bg-light chart-container">
                    <canvas id="chart-tb-usia"></canvas>
                  </div>
                </div>

                <!-- BB/TB -->
                <div class="tab-pane fade" id="pills-bb-tb" role="tabpanel">
                  <h5>Berat Badan / Tinggi Badan</h5>
                  <div class="bg-light chart-container">
                    <canvas id="chart-bb-tb"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-5">
              <h5 class="mb-0 fw-bold">Status Gizi Anak</h5>
              <h1 class="text-primary fw-bold">Dalam 12 Bulan Terakhir</h1>
            </div>

            <!-- content 2 -->
            <div class="p-4 rounded shadow-sm text-center">
              <!-- Nav Pills -->
              <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link active"
                    id="pills-bb-usia-tab-12"
                    data-bs-toggle="pill"
                    data-bs-target="#pills-bb-usia-12"
                    type="button"
                    role="tab"
                  >
                    Berat Badan / Usia
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link"
                    id="pills-tb-usia-tab-12"
                    data-bs-toggle="pill"
                    data-bs-target="#pills-tb-usia-12"
                    type="button"
                    role="tab"
                  >
                    Tinggi Badan / Usia
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link"
                    id="pills-bb-tb-tab-12"
                    data-bs-toggle="pill"
                    data-bs-target="#pills-bb-tb-12"
                    type="button"
                    role="tab"
                  >
                    Berat Badan / Tinggi Badan
                  </button>
                </li>
              </ul>

              <!-- Tabs Content -->
              <div class="tab-content" id="pills-tabContent">
                <!-- BB/U -->
                <div class="tab-pane fade show active" id="pills-bb-usia-12" role="tabpanel">
                  <h5>Berat Badan / Usia</h5>
                  <div class="bg-light chart-container">
                    <canvas id="chart-bb-usia-12"></canvas>
                  </div>
                </div>

                <!-- TB/U -->
                <div class="tab-pane fade" id="pills-tb-usia-12" role="tabpanel">
                  <h5>Tinggi Badan / Usia</h5>
                  <div class="bg-light chart-container">
                    <canvas id="chart-tb-usia-12"></canvas>
                  </div>
                </div>

                <!-- BB/TB -->
                <div class="tab-pane fade" id="pills-bb-tb-12" role="tabpanel">
                  <h5>Berat Badan / Tinggi Badan</h5>
                  <div class="bg-light chart-container">
                    <canvas id="chart-bb-tb-12"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- content 3 -->
            <div class="mt-5">
              <h5 class="mb-0 fw-bold">Indikator Utama (Persentase)</h5>
              <h1 class="text-primary fw-bold">Dalam 12 Bulan Terakhir</h1>
            </div>

            <div class="p-4 rounded shadow-sm text-center">
              <div class="bg-light chart-container">
                <canvas id="chart-bb-tb-indikator"></canvas>
              </div>
            </div>
          </div>
          <div class="tab-pane fade" id="hamil" role="tabpanel">
            <div class="p-4">
              <!-- Alert -->
              <div class="alert alert-info">
                <i></i>
                Total Data <strong>Kesehatan Ibu Hamil</strong> Per Bulan {{ currentMonthYear }}:
                <strong>0</strong>
              </div>

              <!-- Filter Form Content 1 -->
              <div class="my-3">
                <div class="card border-0 shadow-sm p-3">
                  <h5 class="mb-3">Filter Data</h5>
                  <form class="row g-3 align-items-center">
                    <div class="col-auto">
                      <select name="month" id="month" class="form-select">
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                      </select>
                    </div>
                    <div class="col-auto">
                      <select name="year" id="year" class="form-select">
                        <option value="2020">2020</option>
                        <option value="2021">2021</option>
                      </select>
                    </div>
                    <div class="col-auto">
                      <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Cari
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <!-- Content 1 -->
              <div class="mt-5">
                <h5 class="mb-0 fw-bold">Kesehatan Ibu Hamil</h5>
                <h1 class="text-primary fw-bold">Dalam 12 Bulan Terakhir</h1>
              </div>

              <div class="p-4 rounded shadow-sm text-center">
                <div class="bg-light chart-container">
                  <canvas id="chart-bumil"></canvas>
                </div>
              </div>
              <!-- Filter Form Content 2 -->
              <div class="my-3">
                <div class="card border-0 shadow-sm p-3">
                  <h5 class="mb-3">Filter Data</h5>
                  <form class="row g-3 align-items-center">
                    <div class="col-auto">
                      <select name="month" id="month" class="form-select">
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                      </select>
                    </div>
                    <div class="col-auto">
                      <select name="year" id="year" class="form-select">
                        <option value="2020">2020</option>
                        <option value="2021">2021</option>
                      </select>
                    </div>
                    <div class="col-auto">
                      <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Cari
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <!-- Content 2 -->
              <div class="mt-5">
                <h5 class="mb-0 fw-bold">Indikator Utama (persentase)</h5>
                <h1 class="text-primary fw-bold">Dalam 12 Bulan Terakhir</h1>
              </div>
              <div class="p-4 rounded shadow-sm text-center">
                <div class="bg-light chart-container">
                  <canvas id="chart-bumil-indikator"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section About POPS -->
    <section
      class="py-5 position-relative overflow-hidden"
      id="section1"
      style="background: linear-gradient(135deg, #6fa287 0%, #a2aaad 100%)"
    >
      <div class="container py-5">
        <!-- Heading -->
        <div class="text-center mb-5 text-white">
          <h2 class="fw-bold display-5 mb-3 animate-fade-in">Tentang POPS</h2>
          <div
            class="mx-auto mb-3"
            style="width: 90px; height: 4px; background: #fff; border-radius: 3px"
          ></div>
        </div>

        <!-- Split Layout -->
        <div class="row align-items-center mb-5 text-white">
            <p class="lh-lg" style="text-align: justify">
              <strong>Tanoto Foundation</strong> bersama <strong>BKKBN</strong> dan pemerintah daerah bekerja sama dalam mengembangkan program percepatan penurunan stunting di empat kabupaten/kota di Provinsi Jawa Barat, Kabupaten Bogor. Strategi pengembangan program tidak saja fokus kepada dukungan teknis konvergensi dan koordinasi dalam penyusunan kebijakan strategi percepatan penurunan stunting, namun juga mewujudkan layanan pencegahan stunting yang lebih berkualitas di tingkat masyarakat.
            </p>
            <p class="lh-lg" style="text-align: justify">
              Agar program pendampingan percepatan penurunan stunting tersebut dilakukan secara tepat sasaran sesuai dengan ketersediaan sumber daya hingga di tingkat desa, Tanoto Foundation berupaya menginisiasi pengelolaan program stunting berbasis data penerima sasaran di tingkat desa melalui program POPS (Pusat Operasi Penurunan Stunting). Inisiatif ini akan diujicobakan (pilot) di Desa Kluwut, Kabupaten Brebes. Dalam mempersiapkan program tersebut, beberapa tahapan akan dilakukan diantaranya ; Pertemuan assesmen kebutuhan (needs assessment) untuk menggali kebutuhan dan situasi di desa secara komprehensif sehingga kerangka program POPS yang dikembangkan tepat untuk membantu TPPS desa melakukan pengambilan keputusan intervensi stunting yang efektif, Menyepakati alur proses pengumpulan data dan informasi yang dibutuhkan, dan pengembangan website – applikasi POPS sebagai media yang akan ditampilkan dalam penyajian data.
            </p>
            <p class="lh-lg" style="text-align: justify">
              Dalam pelaksanaan Program POPS ini, Tanoto Foundation bekerjasama dengan Yayasan Satu Karsa Karya (YSKK) sebagai mitra implementasi di Lapangan, dan juga mengajak Pemerintah dan Kader Desa Kluwut untuk membantu dalam penggalian/pengumpulan data dan informasi. Pelibatan Desa Kluwut sejak awal program POPS perlu dilakukan untuk memastikan adanya jaminan keberlanjutan Program POPS ini. Terkait dengan hal tersebut untuk mempermudah memahami alur porses program POPS berikut kami susun acuan program untuk bisa dibaca dan dipahami oleh para pihak yang akan terlibat dalam implementasi program ini.
            </p>
        </div>

        <!-- Goals Section -->
        <div class="text-center pt-5 border-top border-light">
          <h2 class="fw-bold text-white mb-3">Tujuan Pelaksanaan POPS</h2>
          <p class="text-light">Dua fokus utama yang ingin dicapai:</p>
        </div>

        <!-- Cards -->
        <div class="row mt-4">
          <div class="col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-lg text-dark hover-card">
              <div class="card-body p-4">
                <div class="d-flex">
                  <div
                    class="rounded-circle bg-gradient-primary d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                    style="width: 60px; height: 60px"
                  >
                    <i class="bi bi-database-fill text-white fs-3"></i>
                  </div>
                  <p class="lh-lg mb-0" style="text-align: justify">
                    Mengkoordinasikan data dan informasi program stunting di Desa Kluwut agar selalu
                    update dan mudah diakses, melalui website berbasis data.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-lg text-dark hover-card">
              <div class="card-body p-4">
                <div class="d-flex">
                  <div
                    class="rounded-circle bg-gradient-success d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                    style="width: 60px; height: 60px"
                  >
                    <i class="bi bi-bar-chart-line-fill text-white fs-3"></i>
                  </div>
                  <p class="lh-lg mb-0" style="text-align: justify">
                    Mengembangkan <strong>Dashboard (website)</strong> berbasis data di Desa Kluwut
                    untuk mendukung pengambilan keputusan intervensi stunting.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section KMS -->
    <section id="section3" class="py-5 bg-light position-relative">
      <div class="container px-4">
        <h2 class="text-center mb-2 fw-bold text-primary">Kalkulator Mandiri</h2>
        <p class="text-center text-muted mb-4">Pantau pertumbuhan anak dengan mudah dan cepat</p>
        <hr class="my-3 w-50 mx-auto" />

        <!-- Button -->
        <div class="d-flex justify-content-end mb-4">
          <button
            class="btn btn-gradient px-4 py-2 shadow"
            data-bs-toggle="modal"
            data-bs-target="#kmsModal"
          >
            <i class="bi bi-search-heart me-2"></i> Cek Mandiri
          </button>
        </div>

        <!-- Modal -->
        <div
          class="modal fade"
          id="kmsModal"
          tabindex="-1"
          aria-labelledby="kmsModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
              <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold" id="kmsModalLabel">
                  <small class="fw-normal"> Untuk Anak Usia 0 - 5 Tahun </small><br>
                  Cek Indeks Berat dan Tinggi Badan Menurut Umur
                </h5>
                <button
                  type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                ></button>
              </div>

              <div class="modal-body p-4">
                <KMSChecker />
              </div>
            </div>
          </div>
        </div>

        <!-- KMS Cards -->
        <div class="row g-4 mt-4">
          <div class="col-md-6">
            <div class="card shadow-lg border-0 h-100 hover-scale">
              <div class="card-body text-center p-4">
                <h5 class="card-title fw-bold text-primary">KMS Anak Laki-laki</h5>
                <div class="d-flex justify-content-center mt-3 gap-3">
                  <img
                    src="/src/assets/KMS_Pria.png1_18.jpg"
                    class="img-fluid rounded-3 shadow-sm"
                    style="max-height: 200px"
                    alt="KMS Laki-laki 1-18"
                  />
                  <img
                    src="/src/assets/KMS_Pria.png18_36.jpg"
                    class="img-fluid rounded-3 shadow-sm"
                    style="max-height: 200px"
                    alt="KMS Laki-laki 18-36"
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card shadow-lg border-0 h-100 hover-scale">
              <div class="card-body text-center p-4">
                <h5 class="card-title fw-bold text-danger">KMS Anak Perempuan</h5>
                <div class="d-flex justify-content-center mt-3 gap-3">
                  <img
                    src="/src/assets/KMS_perempuan1_18.jpg"
                    class="img-fluid rounded-3 shadow-sm"
                    style="max-height: 200px"
                    alt="KMS Perempuan 1-18"
                  />
                  <img
                    src="/src/assets/KMS_perempuan18_36.jpg"
                    class="img-fluid rounded-3 shadow-sm"
                    style="max-height: 200px"
                    alt="KMS Perempuan 18-36"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <FooterUser />
  </div>
</template>
<script>
import NavbarUser from '../components/NavbarUser.vue'
import FooterUser from '../components/FooterUser.vue'
import KMSChecker from '@/components/KMSChecker.vue'
import {
  Chart,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js'

Chart.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend)

export default {
  name: 'StatusGiziTabs',
  mounted() {
    const umurGroups = [
      '0-5',
      '6-11',
      '12-17',
      '18-23',
      '24-29',
      '30-35',
      '36-41',
      '42-47',
      '48-60',
    ]

    const indi = ['-10', '0', '20', '30', '40', '50']
    const bulan = this.getLastMonths(12)

    //chart-bumil
    this.createChart('chart-bumil', bulan, {
      KEK: { data: [4, 4, 7, 4, 1, 0, 0, 5, 0, 3, 6, 2], color: '#8B4513' }, // contoh 12 data
      Anemia: { data: [3, 7, 9, 7, 1, 0, 0, 13, 0, 2, 1, 4], color: '#FFD700' },
      'Risiko Tinggi': { data: [10, 18, 14, 10, 1, 0, 0, 11, 0, 5, 8, 6], color: '#006341' },
    })

    //chart-bumil
    this.createChart('chart-bumil-indikator', bulan, {
      KEK: { data: [4, 4, 7, 4, 1, 0, 0, 5, 0, 3, 6, 2], color: '#8B4513' }, // contoh 12 data
      Anemia: { data: [3, 7, 9, 7, 1, 0, 0, 13, 0, 2, 1, 4], color: '#FFD700' },
      'Risiko Tinggi': { data: [10, 18, 14, 10, 1, 0, 0, 11, 0, 5, 8, 6], color: '#006341' },
    })

    //chart-bb-tb-indikator
    this.createChart('chart-bb-tb-indikator', indi, {
      'Balita Stunting': { data: [2, 3, 1, 4, 2, 1, 2, 1, 0], color: 'red' },
      'Balita UNderweight': { data: [3, 4, 2, 3, 3, 2, 3, 2, 1], color: 'yellow' },
      'Balita BBTN': { data: [5, 6, 8, 7, 6, 7, 6, 8, 7], color: 'green' },
      'Bumil KEK': { data: [1, 0, 1, 0, 1, 1, 0, 1, 2], color: 'blue' },
      'Bumil Anemia': { data: [1, 0, 1, 0, 1, 1, 0, 1, 8], color: 'purple' },
      'Catin Beresiko': { data: [1, 0, 1, 0, 1, 1, 0, 1, 10], color: 'cyan' },
    })

    // BB/U
    this.loadChart()

    // TB/U
    this.createChart('chart-tb-usia', umurGroups, {
      'Sangat Pendek': { data: [1, 2, 1, 3, 1, 1, 1, 0, 1], color: 'red' },
      Pendek: { data: [2, 3, 2, 2, 3, 2, 2, 1, 1], color: 'yellow' },
      Normal: { data: [6, 7, 8, 7, 7, 8, 8, 9, 8], color: 'green' },
      Tinggi: { data: [0, 0, 1, 0, 0, 1, 0, 1, 1], color: 'blue' },
    })

    // BB/TB
    this.createChart('chart-bb-tb', umurGroups, {
      'Gizi Buruk': { data: [2, 1, 1, 2, 1, 0, 1, 0, 0], color: 'red' },
      'Gizi Kurang': { data: [2, 3, 2, 2, 2, 2, 1, 2, 1], color: 'yellow' },
      Normal: { data: [7, 6, 8, 7, 8, 8, 9, 8, 8], color: 'green' },
      'Risiko Lebih': { data: [0, 1, 0, 0, 0, 1, 0, 1, 1], color: 'blue' },
    })

    // BB/U-12
    this.createChart('chart-bb-usia-12', umurGroups, {
      'Sangat Kurang': { data: [2, 3, 1, 4, 2, 1, 2, 1, 0], color: 'red' },
      Kurang: { data: [3, 4, 2, 3, 3, 2, 3, 2, 1], color: 'yellow' },
      Normal: { data: [5, 6, 8, 7, 6, 7, 6, 8, 7], color: 'green' },
      'Risiko Lebih': { data: [1, 0, 1, 0, 1, 1, 0, 1, 2], color: 'blue' },
    })

    // TB/U-12
    this.createChart('chart-tb-usia-12', umurGroups, {
      'Sangat Pendek': { data: [1, 2, 1, 3, 1, 1, 1, 0, 1], color: 'red' },
      Pendek: { data: [2, 3, 2, 2, 3, 2, 2, 1, 1], color: 'yellow' },
      Normal: { data: [6, 7, 8, 7, 7, 8, 8, 9, 8], color: 'green' },
      Tinggi: { data: [0, 0, 1, 0, 0, 1, 0, 1, 1], color: 'blue' },
    })

    // BB/TB-12
    this.createChart('chart-bb-tb-12', umurGroups, {
      'Gizi Buruk': { data: [2, 1, 1, 2, 1, 0, 1, 0, 0], color: 'red' },
      'Gizi Kurang': { data: [2, 3, 2, 2, 2, 2, 1, 2, 1], color: 'yellow' },
      Normal: { data: [7, 6, 8, 7, 8, 8, 9, 8, 8], color: 'green' },
      'Risiko Lebih': { data: [0, 1, 0, 0, 0, 1, 0, 1, 1], color: 'blue' },
    })
  },
  methods: {
    applyFilter() {
      this.loadChart() // reload chart sesuai filter
    },
    loadChart() {
      // Hapus chart lama biar gak numpuk
      if (this.chartInstance) {
        this.chartInstance.destroy()
      }
      const umurGroup = [
        '0-5',
        '6-11',
        '12-17',
        '18-23',
        '24-29',
        '30-35',
        '36-41',
        '42-47',
        '48-60',
      ]

      // Dummy data bisa diganti API sesuai bulan & tahun filter
      const datasetsObj = {
        'Sangat Kurang': { data: [2, 3, 1, 4, 2, 1, 2, 1, 0], color: 'red' },
        Kurang: { data: [3, 4, 2, 3, 3, 2, 3, 2, 1], color: 'orange' },
        Normal: { data: [5, 6, 8, 7, 6, 7, 6, 8, 7], color: 'green' },
        'Risiko Lebih': { data: [1, 0, 1, 0, 1, 1, 0, 1, 2], color: 'blue' },
      }

      this.totalData = Object.values(datasetsObj)
        .flatMap((d) => d.data)
        .reduce((a, b) => a + b, 0)

      this.createChart('chart-bb-usia', umurGroup, datasetsObj)
    },
    getLastMonths(count = 12) {
      const labels = []
      const now = new Date()

      for (let i = count - 1; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1)

        const bulan = d.toLocaleString('id-ID', { month: 'long' }) // nama bulan Indonesia
        const tahun = d.getFullYear()

        labels.push(`${bulan} ${tahun}`)
      }

      return labels
    },

    createChart(canvasId, labels, datasetsObj) {
      const datasets = Object.entries(datasetsObj).map(([label, cfg]) => ({
        label,
        data: cfg.data,
        borderColor: cfg.color,
        backgroundColor: cfg.color,
        fill: false,
        tension: 0.3,
        pointBackgroundColor: cfg.color,
        pointBorderColor: '#fff',
        pointRadius: 5,
        pointHoverRadius: 7,
      }))

      const ctx = document.getElementById(canvasId)
      new Chart(ctx, {
        type: 'line',
        data: { labels, datasets },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'top' },
            title: { display: false },
          },
          scales: {
            x: {
              title: { display: true, text: 'Kelompok Umur (bulan)' },
            },
            y: {
              title: { display: true, text: 'Jumlah Individu' },
              beginAtZero: true,
            },
          },
        },
      })
    },
  },
  computed: {
    currentMonthYear() {
      return `${this.months[this.selectedMonth - 1]} ${this.selectedYear}`
    },
    yearOptions() {
      const now = new Date().getFullYear()
      return [now - 1, now, now + 1] // contoh range tahun
    },
  },
  components: {
    NavbarUser,
    FooterUser,
    KMSChecker,
  },
  data() {
    return {
      months: [
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
      ],
      selectedMonth: new Date().getMonth() + 1, // default bulan ini
      selectedYear: new Date().getFullYear(), // default tahun ini
      chartInstance: null,
      totalData: 0,
      stats: [
        { title: 'Total RW', value: '1,000', icon: 'fa-solid fa-house-chimney-window' },
        { title: 'Total RT', value: '100,000', icon: 'bi bi-house-fill' },
        { title: 'Keluarga', value: '100 M', icon: 'fa-solid fa-people-roof' },
        { title: 'Balita', value: '1,234', icon: 'fa-solid fa-baby' },
        { title: 'Ibu Hamil', value: '56 K', icon: 'fa-solid fa-person-pregnant' },
        { title: 'Calon Pengantin', value: '12 K', icon: 'fa-solid fa-people-arrows' },
        { title: 'Posyandu', value: '8 K', icon: 'fa-solid fa-stethoscope' },
        { title: 'Bidan', value: '10 K', icon: 'fa-solid fa-user-nurse' },
        { title: 'Anggota TPK', value: '56', icon: 'bi bi-person-vcard-fill' },
      ],
    }
  },
}
</script>

<style scoped>
.hero-image {
  position: relative;
  z-index: 2;
}

.hero-anak {
  max-width: 220px;
  margin-left: 2rem;
  margin-top: -40px;
  opacity: 0;
  transform: translateY(20px); /* efek naik dikit */
  animation: fadeInUp 1s ease-out forwards;
}
/* Keyframes */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.hero-section {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  position: relative;
  width: 100%;
  overflow: hidden;
  background: url('/src/assets/bg_cover.png') center/cover no-repeat;
}

/* Konten di atas overlay */
.hero-content {
  margin-top: 100px;
  position: relative;
  z-index: 2;
  opacity: 0;
  transform: translateY(20px); /* efek naik dikit */
  animation: fadeInDown 1s ease-out forwards;
}

/* Keyframes */
@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(0px);
  }
  to {
    opacity: 1;
    transform: translateY(20);
  }
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr); /* default: 3 kolom di mobile */
  gap: 0.5rem;
}

@media (min-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(3, 1fr); /* tablet tetap 3 kolom */
  }
}

@media (min-width: 992px) {
  .stats-grid {
    grid-template-columns: repeat(9, 1fr); /* desktop full 9 sejajar */
  }
}


.stats-wrapper {
  bottom: 0; /* nempel bawah */
  padding-bottom: 2rem; /* kasih jarak dikit biar ga terlalu mepet */
}

/* bikin col-1-5 (1.5 kolom dari 12 grid = 12/8) */
@media (min-width: 992px) {
  .col-lg-1-5 {
    flex: 0 0 auto;
    width: 12.5%; /* 100% / 8 */
  }
}

/* Stat card */
.stat-card {
  border: 2px solid #198754;
  border-radius: 0.75rem;
  background: #fff;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  font-size: 0.8rem;
}

.icon-wrapper {
  width: 30px;
  height: 30px;
  background: rgba(25, 135, 84, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}
/* Chart wrapper responsive */
.chart-wrapper {
  max-width: 100%;
  height: 220px; /* default lebih kecil di mobile */
}
@media (min-width: 768px) {
  .chart-wrapper {
    height: 300px;
  }
}
@media (min-width: 1200px) {
  .chart-wrapper {
    height: 380px;
  }
}
.bg-gradient-primary {
  background: linear-gradient(135deg, #0d6efd, #6fa287);
}
.bg-gradient-success {
  background: linear-gradient(135deg, #198754, #6fa287);
}
.animate-fade-in {
  animation: fadeIn 1.2s ease-in-out;
}
.animate-fade-in-up {
  animation: fadeInUp 1.2s ease-in-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(40px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.chart-wrapper {
  max-width: 100%;
  height: 400px;
}
.big-circle {
  width: 400px; /* Biar nggak kebesaran di HP */
  height: 400px;
  background-image: url('/src/assets/cover2.png');
  background-size: cover;
  background-position: center;
  border-radius: 50%;
  box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);
}

.hover-scale {
  transition:
    transform 0.3s ease,
    box-shadow 0.3s ease;
}
.hover-scale:hover {
  transform: translateY(-5px) scale(1.02);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
}
.chart-container {
  width: 100%;
  height: 300px;
  overflow-x: auto;
}
.chart-container canvas {
  min-width: 600px;
}
</style>
