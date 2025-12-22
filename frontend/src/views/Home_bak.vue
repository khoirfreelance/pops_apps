<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div>
    <!-- Navbar -->
    <NavbarUser />

    <!-- Section Cover -->
    <section id="section0" class="hero-section position-relative text-white">
      <!-- Hero Content -->
      <div class="hero-content d-flex flex-column align-items-center justify-content-center text-center ms-auto col-md-6 col-12">
        <h5 class="fw-regular text-dark" style="font-size: 24px !important;">Selamat Datang di</h5>
        <h1 class="fw-bold display-4 text-primary text-shadow">
          Pusat Operasi Penurunan Stunting (POPS)
        </h1>
      </div>
    </section>

    <!-- Statistic Cards -->
    <section id="section0_1" class="container-fluid mt-2">
      <div class="row justify-content-center g-2">
        <div v-for="(stat, index) in stats" :key="index"
          class="col-xl-1_9 col-lg-2 col-md-1 col-sm-3 col-6">
          <div class="stat-card shadow rounded h-100">
            <h6 class="text-muted pt-2 ps-2" style="font-size: 14px;">{{ stat.title }}</h6>
            <div class="card-body d-flex align-items-center justify-content-between px-2">
              <!-- Text -->
              <h4 class="fw-bold mb-0">{{ stat.value }}</h4>
              <!-- Icon -->
              <div class="icon-wrap d-flex align-items-center justify-content-center mb-1">
                <i :class="[stat.icon]"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section Gizi -->
    <section class="py-3" id="section2">
      <div class="container-fluid mt-5">
        <ul class="nav nav-pills justify-content-center" id="chartTabs" role="tablist">
          <li class="nav-item me-2">
            <button
              class="nav-link active col-12"
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
              class="nav-link col-12"
              id="hamil-tab"
              data-bs-toggle="pill"
              data-bs-target="#hamil"
              type="button"
              role="tab"
            >
              Kesehatan Ibu Hamil
            </button>
          </li>
        </ul>
        <div class="tab-content">
          <!-- Anak -->
          <div class="tab-pane fade show active" id="gizi" role="tabpanel">
            <div class="">
              <h2 class="mb-0">Status Gizi Anak</h2>
              <h5 class="text-primary">Berdasarkan Usia</h5>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-end mb-2">
              <!-- Nav Pills -->
              <ul class="nav nav-pills-custom mb-2 mb-md-0" id="pills-tab" role="tablist">
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
            </div>

            <!-- content -->
            <div class="p-4 bg-light rounded shadow-sm text-center">
              <!-- Tabs Content -->
              <div class="tab-content" id="pills-tabContent">
                <!-- BB/U -->
                <div class="tab-pane fade show active" id="pills-bb-usia" role="tabpanel">
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

            <!-- content 3 -->
            <!-- <div class="mt-5">
              <h5 class="mb-0 fw-bold">Indikator Utama (Persentase)</h5>
              <h1 class="text-primary fw-bold">Dalam 12 Bulan Terakhir</h1>
            </div>

            <div class="p-4 rounded shadow-sm text-center">
              <div class="bg-light chart-container">
                <canvas id="chart-bb-tb-indikator"></canvas>
              </div>
            </div> -->
          </div>

          <!-- Ibu Hamil -->
          <div class="tab-pane fade" id="hamil" role="tabpanel">
            <div class="">
              <!-- Alert -->
              <!-- <div class="alert alert-info">
                <p class="fs-4 mb-0">
                  <i class="fa fa-info-circle"></i>
                  Total Data <strong>Kesehatan Ibu Hamil</strong> Per Bulan {{ currentMonthYear }}:
                  <strong>{{countBumil}}</strong>
                </p>
              </div> -->

              <!-- Content 1 -->
              <div class="">
                <h2 class="mb-0">Data Kesehatan Ibu Hamil</h2>
                <h5 class="text-primary">Dalam 12 Bulan Terakhir</h5>
              </div>

              <div class="p-4 bg-light rounded shadow-sm text-center">
                <div class="bg-light table-responsive">
                  <canvas id="chart-bumil"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section About POPS -->
    <section class="about-pops-section mb-3" id="section1">
      <div class="blur-bg"></div>
      <h2 class="text-center display-5 mb-4">Tentang POPS</h2>
      <div class="container">
        <div class="about-card shadow-lg">
          <!-- Description -->
           <img src="\src\assets\tf_box.png" alt="" class="mb-2" style="max-width: 100px;">
          <div class="about-body">

            <p class="lh-lg text-justify">
              <strong>Tanoto Foundation</strong> bersama <strong>BKKBN</strong> dan pemerintah daerah bekerja sama dalam mengembangkan program percepatan penurunan stunting di empat kabupaten/kota di Provinsi Jawa Barat, Kabupaten Bogor. Strategi pengembangan program tidak saja fokus kepada dukungan teknis konvergensi dan koordinasi dalam penyusunan kebijakan strategi percepatan penurunan stunting, namun juga mewujudkan layanan pencegahan stunting yang lebih berkualitas di tingkat masyarakat.
            </p>

            <p class="lh-lg text-justify">
              Agar program pendampingan percepatan penurunan stunting tersebut dilakukan secara tepat sasaran sesuai dengan ketersediaan sumber daya hingga di tingkat desa, Tanoto Foundation berupaya menginisiasi pengelolaan program stunting berbasis data penerima sasaran di tingkat desa melalui program POPS (Pusat Operasi Penurunan Stunting). Inisiatif ini akan diujicobakan (pilot) di Desa Kluwut, Kabupaten Brebes. Dalam mempersiapkan program tersebut, beberapa tahapan akan dilakukan diantaranya ; Pertemuan assesmen kebutuhan (needs assessment) untuk menggali kebutuhan dan situasi di desa secara komprehensif sehingga kerangka program POPS yang dikembangkan tepat untuk membantu TPPS desa melakukan pengambilan keputusan intervensi stunting yang efektif, Menyepakati alur proses pengumpulan data dan informasi yang dibutuhkan, dan pengembangan website - applikasi POPS sebagai media yang akan ditampilkan dalam penyajian data.
            </p>

            <p class="lh-lg text-justify">
              Dalam pelaksanaan Program POPS ini, Tanoto Foundation bekerjasama dengan Yayasan Satu Karsa Karya (YSKK) sebagai mitra implementasi di Lapangan, dan juga mengajak Pemerintah dan Kader Desa Kluwut untuk membantu dalam penggalian/pengumpulan data dan informasi. Pelibatan Desa Kluwut sejak awal program POPS perlu dilakukan untuk memastikan adanya jaminan keberlanjutan Program POPS ini. Terkait dengan hal tersebut untuk mempermudah memahami alur porses program POPS berikut kami susun acuan program untuk bisa dibaca dan dipahami oleh para pihak yang akan terlibat dalam implementasi program ini.
            </p>

          </div>

          <!-- Goals Section -->
          <div class="text-center mt-5 pt-4 border-top">
            <h3 class="fw-bold">Tujuan Pelaksanaan POPS</h3>
            <p class="text-muted mb-3">Dua fokus utama yang ingin dicapai:</p>
          </div>

          <div class="row mt-4">

            <!-- Card 1 -->
            <div class="col-md-6 mb-4">
              <div class="mini-card shadow-sm">
                <div class="d-flex align-items-center">
                  <img src="/report.png" alt="">
                  <p class="lh-lg mb-0 ms-3">
                    Mengkoordinasikan data dan informasi sasaran program stunting yang ada didesa Kluwut agar selalu update dan mudah diakses banyak pihak, melalui pengembangan website berbasis data.
                  </p>
                </div>
              </div>
            </div>

            <!-- Card 2 -->
            <div class="col-md-6 mb-4">
              <div class="mini-card shadow-sm">
                <div class="d-flex align-items-center">
                  <img src="/chart.png" alt="" style="width: 100px;">
                  <p class="lh-lg mb-0 ms-3">
                    Mengembangkan Dashboard (website) berbasis data di Desa Kluwut
                  </p>
                </div>
              </div>
            </div>

          </div>

        </div>
      </div>
    </section>

    <!-- Section KMS -->
    <section id="section3" class="py-5 bg-light">
      <div class="container px-4 mt-3">
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
          <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
              <div class="modal-header bg-primary text-white rounded-top-4">
                <h3 class="modal-title" id="kmsModalLabel">
                  <small class="fw-normal text-secondary"> Untuk Anak Usia 0 - 5 Tahun </small><br>
                  Cek indeks berat dan tinggi badan menurut umur
                </h3>
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
import axios from 'axios'
Chart.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend)
// PORT backend kamu
const API_PORT = 8000

// Bangun base URL dari window.location
const { protocol, hostname } = window.location
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`

export default {
  name: 'StatusGiziTabs',
  data() {
    return {
      countBumil: '',
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
  async mounted() {
    await this.fetchStats()
    await this.loadBumil()
    await this.loadAnak()
    await this.Indikator()
    // BB/U-12

    /* const indi = ['-10', '0', '20', '30', '40', '50']

    //chart-bb-tb-indikator
    this.createChart('chart-bb-tb-indikator', indi, {
      'Balita Stunting': { data: [2, 3, 1, 4, 2, 1, 2, 1, 0], color: 'red' },
      'Balita Underweight': { data: [3, 4, 2, 3, 3, 2, 3, 2, 1], color: 'yellow' },
      'Balita BBTN': { data: [5, 6, 8, 7, 6, 7, 6, 8, 7], color: 'green' },
      'Bumil KEK': { data: [1, 0, 1, 0, 1, 1, 0, 1, 2], color: 'blue' },
      'Bumil Anemia': { data: [1, 0, 1, 0, 1, 1, 0, 1, 8], color: 'purple' },
      'Bumil Beresiko': { data: [1, 0, 1, 0, 1, 1, 0, 1, 10], color: 'cyan' },
    }) */
  },
  methods: {
    async fetchStats() {
      try {
        const res = await axios.get(`${baseURL}/api/dashboard/stats/`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
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

    // BUMIL
    async loadBumil() {
      try {
        const res = await axios.get(`${baseURL}/api/home/pregnancy/`)
        const data = res.data

        this.countBumil= data.countBumil

        //chart-bumil
        this.createChart('chart-bumil', data.labels, data.indikator)

      } catch (e) {
        console.error(e)
      }
    },

    // Anak
    async loadAnak(){
      try {
        const res = await axios.get(`${baseURL}/api/home/children/`)
        const data = res.data
        console.log(data);

        //chart-usia
        this.createChart('chart-bb-usia', data.labels, data.bbu)
        this.createChart('chart-tb-usia', data.labels, data.tbu)
        this.createChart('chart-bb-tb', data.labels, data.bbtb)
      } catch (e) {
        console.error(e)
      }
    },

    async Indikator(){
      try {
        const res = await axios.get(`${baseURL}/api/home/indicator/`)
        const data = res.data
        // chart per bulan
        this.createChart('chart-bb-usia-12', data.labels, data.bbu)
        this.createChart('chart-tb-usia-12', data.labels, data.tbu)
        this.createChart('chart-bb-tb-12', data.labels, data.bbtb)
        console.log(data);
      } catch (e) {
        console.error(e)
      }
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
              title: { display: false},
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
}
</script>

<style scoped>
   /* Hilangkan background default */
  .nav-pills-custom .nav-link {
    background: none !important;
    color: #4A707A; /* Warna teks default */
    border-radius: 0 !important;
    padding-bottom: 6px;
    position: relative;
    font-weight: 500;
  }

  /* Warna teks aktif */
  .nav-pills-custom .nav-link.active {
    color: #1E6F5C !important; /* hijau gelap seperti gambar */
    background: none !important;
  }

  /* Underline untuk item aktif */
  .nav-pills-custom .nav-link.active::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    height: 3px;
    bottom: -1px;
    background-color: #1E6F5C;
    border-radius: 4px;
  }

  /* Jarak antar nav item biar mirip gambar */
  .nav-pills-custom .nav-item {
    margin-right: 20px;
  }
  /* other */
  .about-pops-section {
    position: relative;
    padding: 60px 0;
  }

  .blur-bg {
    position: absolute;
    inset: 0;
    height: 30%;
    background-image: url('/banner_1520.png');
    background-size: cover;
    background-position: center;
    filter: blur(8px);
    opacity: 0.6;
    z-index: 1;
  }

  .about-card {
    position: relative;
    z-index: 2;
    background: white;
    border-radius: 20px;
    padding: 40px;
  }

  .about-header .logo-tanoto {
    width: 80px;
    height: auto;
  }

  .text-justify {
    text-align: justify;
  }

/* Mini cards seperti di gambar */
.mini-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 20px;
  border: 1px solid rgba(0,0,0,0.05);
}

.icon-circle {
  width: 55px;
  height: 55px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
}


  .stat-card {
    background-color: #fff;
    border-top: 4px solid var(--bs-secondary);
    height: 90px;
    /* proporsional */
    transition: all 0.2s ease-in-out;
    max-width: 150px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
    }

    .icon-wrap {
      background-color: var(--bs-secondary);
      color: #fff;
      border-radius: 8px;
      width: 34px;
      height: 34px;
      font-size: 16px;
      flex-shrink: 0;
    }

    h6 {
      font-family: 'Inter', sans-serif;
      font-size: 0.75rem;
      margin: 0;
    }

    h4 {
      font-family: 'Inter', sans-serif;
      color: #000;
      font-size: 1.1rem;
      margin: 0;
    }
  }

  .stat-text {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: flex-start;
    height: 60px;

    .spacer {
      flex: 1;
    }
  }

  /* custom kolom 9 per baris */
  @media (min-width: 1400px) {
    .col-xl-1_9 {
      flex: 0 0 auto;
      width: 11.11%;
    }
  }

  /* fallback grid untuk ukuran lain */
  @media (min-width: 992px) and (max-width: 1399.98px) {
    .col-lg-2_custom {
      flex: 0 0 auto;
      width: 11.11%;
      /* 5 kolom */
    }

    .stat-card {
      background-color: #fff;
      border-top: 4px solid var(--bs-secondary);
      height: 90px;
      /* proporsional */
      transition: all 0.2s ease-in-out;
      max-width: 120px;

      &:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
      }

      .icon-wrap {
        background-color: var(--bs-secondary);
        color: #fff;
        border-radius: 8px;
        width: 25px !important;
        height: 25px !important;
        font-size: 12px !important;
        flex-shrink: 0;
      }

      h6 {
        font-family: 'Inter', sans-serif;
        font-size: 10px !important;
        margin: 0;
      }

      h4 {
        font-family: 'Inter', sans-serif;
        color: #000;
        font-size: 14px !important;
        margin: 0;
      }
    }

    .stat-text {
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      align-items: flex-start;
      height: 60px;

      .spacer {
        flex: 1;
      }
    }
  }

  @media (min-width: 768px) and (max-width: 991.98px) {
    .col-md-3 {
      flex: 0 0 auto;
      /* width: 11.11%; */
    }

    .stat-card {
      background-color: #fff;
      border-top: 4px solid var(--bs-secondary);
      height: 90px;
      /* proporsional */
      transition: all 0.2s ease-in-out;
      max-width: 80px;

      &:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
      }

      .icon-wrap {
        background-color: var(--bs-secondary);
        color: #fff;
        border-radius: 8px;
        width: 25px !important;
        height: 25px !important;
        font-size: 12px !important;
        flex-shrink: 0;
      }

      h6 {
        font-family: 'Inter', sans-serif;
        font-size: 6px !important;
        margin: 0;
      }

      h4 {
        font-family: 'Inter', sans-serif;
        color: #000;
        font-size: 12px !important;
        margin: 0;
      }
    }

    .stat-text {
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      align-items: flex-start;
      height: 60px;

      .spacer {
        flex: 1;
      }
    }
  }

  @media (max-width: 767.98px) {
    .col-sm-4 {
      flex: 0 0 auto;
      /* width: 33.33%; */
      /* 3 kolom */
    }

    .stat-card {
      background-color: #fff;
      border-top: 4px solid var(--bs-secondary);
      height: 90px;
      /* proporsional */
      transition: all 0.2s ease-in-out;

      &:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
      }

      .icon-wrap {
        background-color: var(--bs-secondary);
        color: #fff;
        border-radius: 8px;
        width: 30px !important;
        height: 30px !important;
        font-size: 12px !important;
        flex-shrink: 0;
      }

      h6 {
        font-family: 'Inter', sans-serif;
        font-size: 7px !important;
        margin: 0;
      }

      h4 {
        font-family: 'Inter', sans-serif;
        color: #000;
        font-size: 12px !important;
        margin: 0;
      }
    }

    @media (min-width: 768px) {
      .sticky-filter {
        position: sticky;
        top: 70px;
        z-index: 10;
      }
    }

    @media (max-width: 767px) {
      .sticky-filter {
        top: 100px;
      }
    }

    .filter-floating-btn {
      position: fixed;
      bottom: 20px;
      right: 20px;
      border-radius: 50%;
      width: 55px;
      height: 55px;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 999;
    }
  }

  .hero-section {
    margin-top: 40px;
    min-height: 80vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    width: 100%;
    overflow: hidden;
    background: url('/banner_1520.png') center/cover no-repeat;
  }

  /* Konten di atas overlay */
  .hero-content {
    margin-top: 100px;
    /* position: relative;
    z-index: 2; */
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

  /* bikin col-1-5 (1.5 kolom dari 12 grid = 12/8) */
  @media (min-width: 992px) {
    .col-lg-1-5 {
      flex: 0 0 auto;
      width: 12.5%; /* 100% / 8 */
    }
  }
  #section2{
    background-image: url('/pattern.png') !important;
    background-size: cover;
    background-repeat: no-repeat;
    padding-top: 10%;
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
    background-image: url('/banner1520.png');
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
