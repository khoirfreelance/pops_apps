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

      <div class="flex-grow-1 d-flex flex-column">
        <!-- Content -->
        <div class="py-4 container-fluid" >
          <!-- Welcome Card -->
          <Welcome />

          <!-- Statistik Berat Badan / Usia -->
          <div class="card border border-primary shadow p-3 my-3">
            <table class="table table-borderless align-middle">
              <tbody>
                <tr>
                  <td class="text-additional fw-bold">Status</td>
                  <td class="text-muted fw-bold">Jumlah</td>
                  <td class="text-muted fw-bold">Persen</td>
                  <td class="text-muted fw-bold">Tren</td>
                </tr>
                <tr v-for="(row, index) in dataTable_bb" :key="index">
                  <td>{{ row.status }}</td>
                  <td>{{ row.jumlah }}</td>
                  <td>{{ row.persen }} %</td>
                  <td :class="row.trenClass">
                    <span v-if="row.tren !== '-'">
                      <i :class="row.trenIcon"></i> {{ row.tren }}
                    </span>
                    <span v-else>-</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Statistik berdasarkan kelompok usia dan gender-->
          <div class="row mt-3">
            <div class="col-12 col-lg-6 col-md-6">
              <div class="card border border-primary shadow p-3 my-3">
                <h6 class="text-primary fw-bold">Berdasarkan Kategori Usia</h6>
                <div class="table-responsive">
                  <canvas ref="usiaChart"></canvas>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-6 col-md-6">
              <div class="card border border-primary shadow p-3 my-3">
                <h6 class="fw-bold mb-4 text-primary">Berdasarkan Jenis Kelamin</h6>
                <div class="row justify-content-center">
                  <div
                    class="col-md-6 col-sm-12 col-12 mb-4"
                    v-for="(item, index) in genderData_bb"
                    :key="index"
                  >
                    <div :class="['circle', item.circleClass]">{{ item.total }}</div>
                    <h6 class="title" :class="item.titleClass">{{ item.label }}</h6>
                    <div class="d-flex justify-content-between px-5">
                      <div>
                        <p v-for="(cat, i) in item.categories" :key="i">{{ cat.name }}</p>
                      </div>
                      <div class="fw-bold text-end" :class="item.valueClass">
                        <p v-for="(cat, i) in item.categories" :key="i">
                          {{ cat.value }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Statistik berdasarkan 1 tahun terkahir -->
          <div class="row mt-3">
            <div class="col-12">
              <div class="card border border-primary shadow p-3 my-3">
                <div class="table-responsive">
                  <canvas ref="indiChart_bb"></canvas>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card border border-primary shadow p-3 my-3">
                <div class="table-responsive">
                  <table class="table table-bordered table-sm align-middle text-center">
                    <thead class="table-light">
                      <tr>
                        <th>Indikator</th>
                        <th v-for="(bulan, idx) in bulanLabels" :key="idx">
                          {{ bulan }}
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(values, indikator) in indikatorData" :key="indikator">
                        <td class="fw-bold">{{ indikator }}</td>
                        <td v-for="(val, idx) in values" :key="idx">{{ val }}</td>
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
  <!-- Footer -->
    <CopyRight />
  </div>
</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import Welcome from '@/components/Welcome.vue'
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

// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Detail',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, Welcome },
  data() {
    return {
      filteredData: [], // data hasil filter
      rawData:[],
      configCacheKey: 'site_config_cache',
      isLoading: true,
      isCollapsed: false,
      windowWidth: window.innerWidth,
      dataTable_bb:[],
      dataTable_tb:[],
      dataTable_bbtb:[],
      genderData_bb: [],
      genderData_tb: [],
      genderData_bbtb: [],
      kelompokUmur: ['0-5', '6-11', '12-17', '18-23', '24-35', '36-47', '48-60'],
      indikatorData: {}, // diisi dari loadChildren
      bulanLabels: [], // diisi daftar bulan
      usiaChartInstance_bb: null,
      usiaChartInstance_tb: null,
      usiaChartInstance_bbtb: null,
      statusData: {
        'Sangat Kurang': [0, 0, 0, 0, 0, 0, 0],
        'Kurang': [0, 0, 0, 0, 0, 0, 0],
        'Normal': [0, 0, 0, 0, 0, 0, 0],
        'Risiko Lebih': [0, 0, 0, 0, 0, 0, 0],
        'Tidak Naik': [0, 0, 0, 0, 0, 0, 0],
      },
      children:[],
      kelurahan:'',
      indiChartInstance_tb: null,
      indiChartInstance_bbtb: null,
      indiChartInstance_bb: null,
    }
  },
  methods: {
    async loadChildren() {
      try {
        const res = await axios.get(`${baseURL}/api/children`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        // API structure: { status, message, data_anak: [...] }
        const items = res.data.data_anak || []
        this.rawData = items
        //console.log(this.rawData);

         // 🔹 Filter langsung berdasarkan kelurahan user
        const filteredByKelurahan = items.filter(
          (item) =>
            item.kelurahan?.toLowerCase() === this.kelurahan?.toLowerCase()
        );

        this.children = filteredByKelurahan.map((item) => {
          const kelahiran = item.kelahiran?.[0] || {}
          const keluarga = item.keluarga?.[0] || {}
          const pendamping = item.pendampingan?.[item.pendampingan.length - 1] || {}
          const posyandu = item.posyandu?.[item.posyandu.length - 1] || {}
          const intervensi = item.intervensi?.[item.intervensi.length - 1] // ambil intervensi terakhir

          return {
            // === Identitas Anak ===
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

            // === Data Posyandu terakhir ===
            posyandu: posyandu.posyandu || '-',
            usia: posyandu.usia || '-',
            tgl_ukur: posyandu.tgl_ukur || '-',
            bbu: posyandu.bbu || '-',
            tbu: posyandu.tbu || '-',
            bbtb: posyandu.bbtb || '-',
            bb: posyandu.bb || '-',
            tb: posyandu.tb || '-',
            bb_naik: posyandu.bb_naik || false,

            // === Data Intervensi ===
            intervensi: intervensi ? intervensi.jenis : 'Belum dapat Intervensi',

            // === Tambahan opsional ===
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

            // === Simpan data mentah (buat showDetail nanti) ===
            raw: {
              kelahiran: item.kelahiran || [],
              keluarga: item.keluarga || [],
              pendampingan: item.pendampingan || [],
              posyandu: item.posyandu || [],
              intervensi: item.intervensi || []
            }
          }

        })

        // 🔹 Hitung total anak dengan usia < 60 bulan
        this.totalAnak = this.children.filter((anak) => anak.usia < 60).length;

        this.generateIndikatorDataBB();
        this.generateGenderDataBB();
        this.generateStatusUsiaData();
        this.generateDataTableBB();
        this.renderUsiaChart_bb();
        this.renderIndiChart_bb();
      } catch (e) {
        console.error(e);

        //this.showError('Error Ambil Data Anak', e)
      }
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
    getLast12Months() {
      const monthNames = [
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
      const labels = []
      const now = new Date()
      for (let i = 11; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
        labels.push(`${monthNames[d.getMonth()]} ${d.getFullYear()}`)
      }
      return labels
    },
    generateGenderDataBB() {
      const kategoriStatus = [
        'Severely Underweight',
        'Underweight',
        'Normal',
        'Risiko BB Lebih',
        'BB Stagnan'
      ];

      // Pisahkan data anak laki-laki & perempuan
      const male = this.children.filter(c => c.gender?.toUpperCase() === 'L');
      const female = this.children.filter(c => c.gender?.toUpperCase() === 'P');

      // Fungsi bantu untuk hitung jumlah tiap kategori
      const countByCategory = (list) => {
        const counts = {};
        kategoriStatus.forEach(k => counts[k] = 0);

        list.forEach((anak) => {
          const riwayat = anak.raw.posyandu || [];
          const last = riwayat[riwayat.length - 1];
          const prev = riwayat[riwayat.length - 2];
          if (!last) return;

          // Status BBU
          const status = last.bbu;
          if (kategoriStatus.includes(status)) {
            counts[status]++;
          }

          // BB stagnan → cek bb_naik atau perbandingan last & prev
          if (last.bb_naik === false || (prev && last.bb && prev.bb && parseFloat(last.bb) <= parseFloat(prev.bb))) {
            counts['BB Stagnan']++;
          }
        });

        return kategoriStatus.map((name) => ({
          name,
          value: counts[name] || 0
        }));
      };

      const maleCategories = countByCategory(male);
      const femaleCategories = countByCategory(female);

      this.genderData_bb = [
        {
          label: 'Laki-laki',
          total: male.length,
          circleClass: 'circle-male bg-primary text-white',
          titleClass: 'text-primary',
          valueClass: 'text-primary',
          categories: maleCategories
        },
        {
          label: 'Perempuan',
          total: female.length,
          circleClass: 'circle-female bg-danger text-white',
          titleClass: 'text-danger',
          valueClass: 'text-danger',
          categories: femaleCategories
        }
      ];
    },
    generateStatusUsiaData() {
      const kategoriStatus = [
        'Severely Underweight',
        'Underweight',
        'Normal',
        'Risiko BB Lebih',
        'BB Stagnan'
      ];

      const statusData = {};
      kategoriStatus.forEach(k => {
        statusData[k] = Array(this.kelompokUmur.length).fill(0);
      });

      this.children.forEach(anak => {
        if (!anak.raw?.posyandu) return; // skip kalau ga ada data

        anak.raw.posyandu.forEach(p => {
          const usia = parseInt(p.usia);
          const bbu = p.bbu;

          let groupIndex = -1;
          if (usia >= 0 && usia <= 5) groupIndex = 0;
          else if (usia >= 6 && usia <= 11) groupIndex = 1;
          else if (usia >= 12 && usia <= 17) groupIndex = 2;
          else if (usia >= 18 && usia <= 23) groupIndex = 3;
          else if (usia >= 24 && usia <= 35) groupIndex = 4;
          else if (usia >= 36 && usia <= 47) groupIndex = 5;
          else if (usia >= 48 && usia <= 60) groupIndex = 6;

          if (groupIndex !== -1 && kategoriStatus.includes(bbu)) {
            statusData[bbu][groupIndex]++;
          }
        });
      });

      this.statusData = statusData;
    },
    generateDataTableBB() {
      const kategoriStatus = [
        'Severely Underweight',
        'Underweight',
        'Normal',
        'Risiko BB Lebih',
        'BB Stagnan'
      ];

      // Inisialisasi counter
      const counts = {};
      kategoriStatus.forEach((k) => (counts[k] = 0));

      const totalAnak = this.children.length;

      this.children.forEach((anak) => {
        // Cek apakah ada riwayat posyandu
        const riwayat = anak.raw.posyandu || [];

        // Cek status BBU dari pengukuran terakhir
        const last = riwayat[riwayat.length - 1];
        const prev = riwayat[riwayat.length - 2];

        if (!last) return;

        const statusBBU = last.bbu;

        // === Logika kategori BBU ===
        if (kategoriStatus.includes(statusBBU)) {
          counts[statusBBU]++;
        }

        // === Logika tambahan: BB Stagnan ===
        // Kalau ada dua pengukuran terakhir dan beratnya tidak naik
        if (last.bb && prev && prev.bb && parseFloat(last.bb) <= parseFloat(prev.bb)) {
          counts['BB Stagnan']++;
        } else if (last.bb_naik === false) {
          counts['BB Stagnan']++;
        }
      });

      // Hitung persentase per kategori
      const table = kategoriStatus.map((status) => {
        const jumlah = counts[status] || 0;
        const persen = totalAnak > 0 ? (jumlah / totalAnak) * 100 : 0;

        return {
          status,
          jumlah,
          persen: +persen.toFixed(2),
          tren: `${persen.toFixed(2)}%`,
          trenClass: persen > 20 ? 'text-danger' : 'text-success',
          trenIcon: persen > 20 ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill',
        };
      });

      this.dataTable_bb = table;
    },
    renderIndiChart_bb() {
      const ctx = this.$refs.indiChart_bb.getContext('2d'); // pastikan ref sama

      if (this.indiChartInstance_bb) {
        this.indiChartInstance_bb.destroy();
      }

      this.indiChartInstance_bb = new Chart(ctx, {
        type: 'line',
        data: {
          labels: this.bulanLabels,
          datasets: [
            {
              label: 'Severely Underweight',
              data: this.indikatorData['Severely Underweight'],
              borderColor: '#d9534f',
              backgroundColor: 'rgba(217, 83, 79, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Underweight',
              data: this.indikatorData['Underweight'],
              borderColor: '#f0ad4e',
              backgroundColor: 'rgba(240, 173, 78, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Normal',
              data: this.indikatorData['Normal'],
              borderColor: '#5cb85c',
              backgroundColor: 'rgba(92, 184, 92, 0.3)',
              fill: true,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Risiko BB Lebih',
              data: this.indikatorData['Risiko BB Lebih'],
              borderColor: '#0275d8',
              backgroundColor: 'rgba(2, 117, 216, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'BB Stagnan',
              data: this.indikatorData['BB Stagnan'],
              borderColor: '#9370db',
              backgroundColor: 'rgba(147, 112, 219, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
          ],
        },
        options: {
          responsive: true,
          interaction: { mode: 'nearest', intersect: false },
          plugins: {
            legend: { position: 'top' },
            tooltip: { enabled: true },
          },
          scales: {
            y: {
              beginAtZero: true,
              title: { display: true, text: 'Jumlah Individu' },
            },
            x: {
              title: { display: true, text: 'Bulan Pengukuran' },
            },
          },
        },
      });
    },
    renderUsiaChart_bb() {
      const ctx = this.$refs.usiaChart.getContext('2d');

      if (this.usiaChartInstance_bb) {
        this.usiaChartInstance_bb.destroy();
      }

      this.usiaChartInstance_bb = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: this.kelompokUmur,
          datasets: [
            {
              label: 'Severely Underweight',
              data: this.statusData['Severely Underweight'],
              backgroundColor: '#d9534f',
            },
            {
              label: 'Underweight',
              data: this.statusData['Underweight'],
              backgroundColor: '#f0ad4e',
            },
            {
              label: 'Normal',
              data: this.statusData['Normal'],
              backgroundColor: '#5cb85c',
            },
            {
              label: 'Risiko BB Lebih',
              data: this.statusData['Risiko BB Lebih'],
              backgroundColor: '#0275d8',
            },
            {
              label: 'BB Stagnan',
              data: this.statusData['BB Stagnan'],
              backgroundColor: '#9370db',
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'right' },
          },
          scales: {
            x: {
              title: {
                display: true,
                text: 'Kategori Usia (bulan)',
              },
            },
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Total Individu',
              },
            },
          },
        },
      });
    },
    generateIndikatorDataBB() {
      const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
      ];

      // 🟢 Generate 12 bulan terakhir (FIX)
      const today = new Date();
      let bulanUnik = [];

      for (let i = 11; i >= 0; i--) {
        const d = new Date(today.getFullYear(), today.getMonth() - i, 1);
        bulanUnik.push(`${monthNames[d.getMonth()]} ${d.getFullYear()}`);
      }

      this.bulanLabels = bulanUnik;

      const kategoriStatus = [
        'Severely Underweight',
        'Underweight',
        'Normal',
        'Risiko BB Lebih',
        'BB Stagnan'
      ];

      const indikatorData = {};
      kategoriStatus.forEach((k) => (indikatorData[k] = Array(bulanUnik.length).fill(0)));

      // 🟡 Hitung data sesuai bulan 12 bulan terakhir
      this.children.forEach((anak) => {
        const riwayat = anak.raw.posyandu || [];

        for (let i = 0; i < riwayat.length; i++) {
          const r = riwayat[i];
          if (!r.tgl_ukur) continue;

          const d = new Date(r.tgl_ukur);
          if (isNaN(d)) continue;

          const label = `${monthNames[d.getMonth()]} ${d.getFullYear()}`;

          // Lewati jika tidak ada pada 12 bulan terakhir
          const idx = bulanUnik.indexOf(label);
          if (idx === -1) continue;

          const status = r.bbu;

          // Tentukan stagnan
          let stagnan = false;
          if (i > 0) {
            const prev = riwayat[i - 1];
            if (prev.bb && r.bb && parseFloat(r.bb) <= parseFloat(prev.bb)) stagnan = true;
          } else if (r.bb_naik === false) stagnan = true;

          // Masukkan kategori
          if (stagnan) {
            indikatorData['BB Stagnan'][idx]++;
          } else if (kategoriStatus.includes(status)) {
            indikatorData[status][idx]++;
          }
        }
      });

      this.indikatorData = indikatorData;
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
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    handleResize() {
      this.windowWidth = window.innerWidth
      if (this.windowWidth < 992) {
        this.isCollapsed = true // auto collapse di tablet/mobile
      } else {
        this.isCollapsed = false // normal lagi di desktop
      }
    },
  },
  async mounted() {
    this.isLoading = true
    const params = new URLSearchParams(window.location.search);
    this.tipe = params.get('tipe'); // hasilnya 'bbu', 'tbu', atau 'bbtb'

    try {
      await this.$nextTick()
      await this.loadConfigWithCache()
      await this.getWilayahUser()
      // ⏳ ambil data anak lebih dulu (supaya filter muncul)
      await this.loadChildren() // ini udah panggil generateListsFromChildren()

      this.bulanLabels = this.getLast12Months() // <- generate bulan realtime
      this.filteredData = [...this.children]
      this.handleResize()
      window.addEventListener('resize', this.handleResize)

    } catch (err) {
      console.error('Error loading data:', err)
    } finally {
      setTimeout(() => { this.isLoading = false }, 300)
    }
  },
}
</script>

<style scoped>
.circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  font-weight: bold;
  margin: 0 auto 15px auto;
  color: #fff;
}
.male-circle {
  background-color: var(--bs-primary)
}
.female-circle {
  background-color: var(--bs-secondary)
}
.title {
  font-weight: bold;
  text-align: center;
  margin-bottom: 15px;
}
@media (min-width: 1200px) {
  .stat-col {
    flex: 0 0 10%;
    max-width: 10%;
  }
}

.dashboard-wrapper {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  color: #333;
}

/* Card Statistik */
.stat-card {
  border-radius: 1rem;
  transition: all 0.3s ease;
  /* background: #fff; */
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
}

.icon-wrapper {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(0, 123, 255, 0.08); /* soft primary */
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1a7f37;
}

/* Form & Select */
.form-select,
.btn {
  /* border-radius: 0.75rem; */
  transition: all 0.2s ease-in-out;
}
.form-select:focus,
.btn:focus {
  box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
}

/* Card konten */
.card {
  border-radius: 1rem !important;
  border: none;
  background: #fff;
  transition: box-shadow 0.2s ease;
}
.card:hover {
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

/* Table */
.table {
  font-size: 0.95rem;
}
.table th {
  color: #6c757d;
  font-weight: 600;
}
.table td {
  color: #333;
}
</style>
