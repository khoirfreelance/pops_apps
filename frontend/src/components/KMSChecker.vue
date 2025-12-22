<template>
  <div class="kalkulator-card">
    <div class="kalkulator-card-title">
      Input Data Anak
    </div>
    <div class="kalkulator-card-desc">
      Masukkan data untuk menghitung status gizi
    </div>
    <!-- Form Input -->
    <form @submit.prevent="onHitung" class="kalkulator-card">

      <!-- GENDER -->
      <div class="mt-4">
        <div class="form-label text-white mb-2 kalkulator-label">
          Jenis Kelamin
        </div>

        <div class="row g-2">
          <div class="col-6">
            <div
              class="gender-card"
              :class="{ active: gender === 'male' }"
              @click="gender = 'male'"
            >
              <div class="gender-inner male">
                <img src="/icons/boy.png" alt="Laki-laki" />
              </div>
              <span>Laki-laki</span>
            </div>
          </div>

          <div class="col-6">
            <div
              class="gender-card"
              :class="{ active: gender === 'female' }"
              @click="gender = 'female'"
            >
              <div class="gender-inner female">
                <img src="/icons/girl.png" alt="Perempuan" />
              </div>
              <span>Perempuan</span>
            </div>
          </div>
        </div>
      </div>

      <!-- TANGGAL LAHIR -->
      <div class="mt-3">
        <label class="form-label text-white">Tanggal Lahir</label>
        <input
          type="date"
          v-model="birthDate"
          class="form-control kalkulator-input"
          required
        />
      </div>

      <!-- BB -->
      <div class="mt-3">
        <label class="form-label text-white">Berat Badan Saat Ini (kg)</label>
        <input
          type="number"
          step="0.01"
          v-model.number="currentWeight"
          class="form-control kalkulator-input"
          placeholder="Contoh: 12.5"
          required
        />
      </div>

      <!-- TB -->
      <div class="mt-3">
        <label class="form-label text-white">Tinggi Badan Saat Ini (cm)</label>
        <input
          type="number"
          step="0.01"
          v-model.number="currentHeight"
          class="form-control kalkulator-input"
          placeholder="Contoh: 86"
          required
        />
      </div>

      <!-- BUTTON -->
      <button type="submit" class="btn kalkulator-btn mt-4 w-100">
        <i class="fa-solid fa-magnifying-glass"></i> | Hitung
      </button>

    </form>

    <!-- Ringkasan -->
    <div
      class="modal fade"
      :class="{ show: showResultModal }"
      style="display: block;"
      tabindex="-1"
      v-if="showResultModal"
    >
      <div
        class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable
              d-flex align-items-center justify-content-center"
      >
        <div class="modal-content text-center">

          <!-- HEADER -->
          <div class="modal-header bg-primary text-white justify-content-center position-relative">
            <h3 class="modal-title w-100 text-center">
              Hasil Perhitungan Status Gizi
            </h3>

            <!-- close button tetap kanan atas -->
            <button
              type="button"
              class="btn-close position-absolute end-0 me-3 text-white"
              @click="showResultModal = false"
            ></button>
          </div>

          <!-- BODY -->
          <div class="modal-body text-center">

            <!-- RINGKASAN -->
            <div class="row justify-content-center text-center g-3 my-1">

              <!-- INFO UTAMA -->
              <div class="col-12 col-md-6">
                <div class="p-3 h-100 rounded-3 border bg-body">
                  <div class="h4 text-muted mb-1">Usia</div>
                  <div class="fs-4 fw-bold text-primary">
                    {{ ageMonths }} <span class="fs-6 fw-normal">bln</span>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="p-3 h-100 rounded-3 border bg-body">
                  <div class="h4 text-muted mb-1">BB Ideal</div>
                  <div class="fs-4 fw-bold text-success">
                    {{ idealWeight.toFixed(1) }}
                    <span class="fs-6 fw-normal">kg</span>
                  </div>
                </div>
              </div>

              <!-- STATUS -->
              <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 border bg-body h-100">
                  <div class="h4 text-muted mb-2">Status BB/U</div>
                  <span class="badge fs-6 px-3 py-2" :class="statusWfaBadge">
                    {{ statusWfa }}
                  </span>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 border bg-body h-100">
                  <div class="h4 text-muted mb-2">Status TB/U</div>
                  <span class="badge fs-6 px-3 py-2" :class="statusHfaBadge">
                    {{ statusHfa }}
                  </span>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 border bg-body h-100">
                  <div class="h4 text-muted mb-2">Status BB/TB</div>
                  <span class="badge fs-6 px-3 py-2" :class="statusWFHBadge">
                    {{ statusWfh }}
                  </span>
                </div>
              </div>

            </div>

            <!-- CHART BB/U -->
            <div class="card border-0 shadow-sm mb-4">
              <div class="card-body">
                <h6 class="mb-3">Berat Badan / Umur</h6>
                <div style="height: 400px" class="d-flex justify-content-center">
                  <canvas ref="chartBB"></canvas>
                </div>
              </div>
            </div>

            <!-- CHART TB/U -->
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <h6 class="mb-3">Tinggi Badan / Umur</h6>
                <div style="height: 400px" class="d-flex justify-content-center">
                  <canvas ref="chartTB"></canvas>
                </div>
              </div>
            </div>

            <!-- CHART BB/TB -->
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <h6 class="mb-3">Berat Badan / Tinggi Badan</h6>
                <div style="height: 400px" class="d-flex justify-content-center">
                  <canvas ref="chartWFH"></canvas>
                </div>
              </div>
            </div>

          </div>

          <!-- FOOTER -->
          <div class="modal-footer justify-content-center">
            <button class="btn btn-secondary" @click="showResultModal = false">
              Tutup
            </button>
          </div>
        </div>
      </div>
      <!-- OVERLAY -->
      <!-- <div class="kms-overlay" @click="showResultModal = false"></div> -->

    </div>
  </div>
</template>

<script>
import { nextTick } from 'vue'
import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  Title,
  CategoryScale,
  Legend,
  Filler,
  Tooltip,
} from 'chart.js'
// Import WHO Standards
import wfa from '@/assets/wfa.json'
import hfa from '@/assets/hfa.json'
import wfh from '@/assets/wfh.json'

Chart.register(
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  Title,
  CategoryScale,
  Legend,
  Filler,
  Tooltip,
)

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Kms',
  data() {
    return {
      whoData: {
        wfa,
        hfa,
        wfh
      },
      showResultModal: false,
      birthDate: '',
      gender: '',
      currentWeight: null,
      currentHeight: null,

      ageMonths: 0,
      idealWeight: 0,

      statusWfa: '-',
      statusHfa: '-',
      statusWfh: '-',

      calculated: false,
      chartBB: null,
      chartTB: null,
      chartWFH: null,
      // ====== WHO demo points (median & sd) untuk WFA (kg) ======
      // boys
      wfaBoys: [
        { m: 0, median: 3.3, sd: 0.3 },
        { m: 1, median: 4.5, sd: 0.35 },
        { m: 2, median: 5.6, sd: 0.4 },
        { m: 3, median: 6.4, sd: 0.45 },
        { m: 4, median: 7.0, sd: 0.5 },
        { m: 5, median: 7.5, sd: 0.55 },
        { m: 6, median: 7.9, sd: 0.6 },
        { m: 12, median: 9.6, sd: 0.8 },
        { m: 24, median: 12.2, sd: 1.1 },
        { m: 36, median: 14.3, sd: 1.4 },
        { m: 48, median: 16.3, sd: 1.6 },
        { m: 60, median: 18.3, sd: 1.8 },
      ],
      // girls
      wfaGirls: [
        { m: 0, median: 3.2, sd: 0.3 },
        { m: 1, median: 4.2, sd: 0.35 },
        { m: 2, median: 5.1, sd: 0.4 },
        { m: 3, median: 5.8, sd: 0.45 },
        { m: 4, median: 6.4, sd: 0.5 },
        { m: 5, median: 6.9, sd: 0.55 },
        { m: 6, median: 7.3, sd: 0.6 },
        { m: 12, median: 8.9, sd: 0.8 },
        { m: 24, median: 11.5, sd: 1.1 },
        { m: 36, median: 13.9, sd: 1.4 },
        { m: 48, median: 16.0, sd: 1.6 },
        { m: 60, median: 18.2, sd: 1.8 },
      ],

      // ====== WHO demo points (median & sd) untuk HFA (cm) ======
      // kira-kira dari tabel WHO (silakan ganti dengan tabel bulanan resmi untuk presisi)
      hfaBoys: [
        { m: 0, median: 49.9, sd: 1.9 },
        { m: 1, median: 54.7, sd: 2.0 },
        { m: 2, median: 58.4, sd: 2.1 },
        { m: 3, median: 61.4, sd: 2.2 },
        { m: 4, median: 63.9, sd: 2.3 },
        { m: 5, median: 65.9, sd: 2.4 },
        { m: 6, median: 67.6, sd: 2.5 },
        { m: 12, median: 75.7, sd: 2.9 },
        { m: 24, median: 87.8, sd: 3.2 },
        { m: 36, median: 96.1, sd: 3.4 },
        { m: 48, median: 103.3, sd: 3.6 },
        { m: 60, median: 110.0, sd: 3.8 },
      ],
      hfaGirls: [
        { m: 0, median: 49.1, sd: 1.9 },
        { m: 1, median: 53.7, sd: 2.0 },
        { m: 2, median: 57.1, sd: 2.1 },
        { m: 3, median: 59.8, sd: 2.2 },
        { m: 4, median: 62.1, sd: 2.3 },
        { m: 5, median: 64.0, sd: 2.4 },
        { m: 6, median: 65.7, sd: 2.5 },
        { m: 12, median: 74.0, sd: 2.8 },
        { m: 24, median: 86.4, sd: 3.2 },
        { m: 36, median: 95.1, sd: 3.4 },
        { m: 48, median: 102.7, sd: 3.6 },
        { m: 60, median: 109.4, sd: 3.8 },
      ],
      wfhBoys: [],
      wfhGirls: [],
      // Warna KMS (atas → bawah) yang kamu minta:
      kmsColors: {
        /* top: '#F2D803',
        midTop: '#84BA24',
        mid: '#2CA339',
        midMed: '#2DA83C', // dipakai utk median
        midBottom: '#80B626',
        bottom: '#DCBF1E', */
        top: '#555',
        midTop: '#cc0000',
        mid: '#444',
        midMed: '#4caf50', // dipakai utk median
        midBottom: '#ffa500',
        bottom: '#111',
      },
    }
  },
  computed: {
    statusWfaBadge() {
      switch (this.statusWfa) {
        case 'Sangat Kurus':
          return 'bg-danger'
        case 'Kurus':
          return 'bg-warning text-dark'
        case 'Normal':
          return 'bg-success'
        case 'Risiko BB Lebih':
          return 'bg-warning text-dark'
        case 'BB Lebih':
          return 'bg-dark text-white'
        default:
          return 'bg-secondary'
      }
    },
    statusHfaBadge() {
      switch (this.statusHfa) {
        case 'Sangat Pendek':
          return 'bg-danger'
        case 'Pendek':
          return 'bg-warning text-dark'
        case 'Normal':
          return 'bg-success'
        case 'Tinggi':
          return 'bg-primary'
        default:
          return 'bg-secondary'
      }
    },
    statusWFHBadge() {
      switch (this.statusWfh) {
        case 'Gizi Buruk':
          return 'bg-danger'
        case 'Gizi Kurang':
          return 'bg-warning text-dark'
        case 'Gizi Baik':
          return 'bg-success'
        case 'Risiko Gizi Lebih':
          return 'bg-warning text-dark'
        case 'Gizi Lebih':
          return 'bg-secondary text-dark'
        case 'Obesitas':
          return 'bg-danger'
        default:
          return 'bg-dark text-white'
      }
    },
  },
  methods: {
    closeModal() {
      this.showResultModal = false
    },
    async onHitung() {
      if (!this.birthDate || !this.gender || !this.currentWeight || !this.currentHeight) return

      this.ageMonths = this.calcAgeMonths(this.birthDate, new Date())

      const wfa = this.gender === 'female' ? this.wfaGirls : this.wfaBoys
      const hfa = this.gender === 'female' ? this.hfaGirls : this.hfaBoys
      const wfh = this.gender === 'female' ? this.wfhGirls : this.wfhBoys

      // ===== WFA =====
      const pW = this.getPoint(wfa, this.ageMonths)
      const zW = this.calcZScore(this.currentWeight, pW)
      this.statusWfa = this.classifyWFA(zW)
      this.idealWeight = pW.median

      // ===== HFA =====
      const pH = this.getPoint(hfa, this.ageMonths)
      const zH = this.calcZScore(this.currentHeight, pH)
      this.statusHfa = this.classifyHFA(zH)

      // === WFH ====
      const wfhNorm = this.normalizeWFH(wfh)
      const ref = this.getWFHPoint(wfhNorm, this.currentHeight)
      //const testZ = this.calcZScore(this.currentWeight, this.currentHeight)
      const zWFH =
        this.currentWeight < ref.median
          ? (this.currentWeight - ref.median) /
            (ref.median - ref.sd1neg)
          : (this.currentWeight - ref.median) /
            (ref.sd1 - ref.median)
            //console.log('testZ: ',testZ);
      //console.log('onHitung: ',zWFH);

      this.statusWfh = this.classifyWFH(zWFH)

      this.calculated = true
      this.showResultModal = true

      await nextTick()
      this.renderBB(wfa)
      this.renderTB(hfa)
      this.renderWFH(wfh)
    },
    // ====== Chart: BB/U ======
    renderBB(curve) {
      const { min, max } = this.getAgeRange()

      const labels = Array.from(
        { length: max - min + 1 },
        (_, i) => i + min
      )
      const sd = (m, key) => this.getPoint(curve, m)[key]

      const minus3 = labels.map(m => sd(m, 'sd3neg'))
      const minus2 = labels.map(m => sd(m, 'sd2neg'))
      const minus1 = labels.map(m => sd(m, 'sd1neg'))
      const median = labels.map(m => sd(m, 'median'))
      const plus1  = labels.map(m => sd(m, 'sd1'))
      const plus2  = labels.map(m => sd(m, 'sd2'))
      const plus3  = labels.map(m => sd(m, 'sd3'))

      if (this.chartBB) this.chartBB.destroy()
      const ctx = this.$refs.chartBB.getContext('2d')

      const C = this.kmsColors
      this.chartBB = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            // Titik BB anak saat ini (hanya 1 titik)
            {
              label: 'BB Anak',
              data: labels.map((m) =>
                m === this.ageMonths ? this.currentWeight : null
              ),
              borderColor: '#0066ff',
              backgroundColor: '#0066ff',
              pointRadius: labels.map((m) => (m === this.ageMonths ? 6 : 0)),
              showLine: false,
            },


            // Kurva KMS (tanpa titik, dengan fill gradasi)
            {
              label: '-3SD',
              data: minus3,
              borderColor: C.top,
              //backgroundColor: this.hexA(C.top, 0.15),
              //fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '-2SD',
              data: minus2,
              borderColor: C.midTop,
              /* backgroundColor: this.hexA(C.midTop, 0.15),
              fill: '+1', */
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '-1SD',
              data: minus1,
              borderColor: C.mid,
              /* backgroundColor: this.hexA(C.mid, 0.15),
              fill: '+1', */
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: 'Median',
              data: median,
              borderColor: C.midMed,
              borderWidth: 2,
              //fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+1SD',
              data: plus1,
              borderColor: C.midBottom,
              /* backgroundColor: this.hexA(C.midBottom, 0.15),
              fill: '+1', */
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+2SD',
              data: plus2,
              borderColor: C.bottom,
              /* backgroundColor: this.hexA(C.bottom, 0.15),
              fill: '+1', */
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+3SD',
              data: plus3,
              borderColor: C.top,
              //backgroundColor: this.hexA(C.top, 0.15),
              fill: false,
              tension: 0.2,
              pointRadius: 0,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'top' },
            title: { display: false, text: 'BB/U: Berat Badan vs Umur ' },
            tooltip: {
              enabled: true, // tetap tampil saat hover
              callbacks: {
                label: (ctx) => {
                  if (ctx.dataset.label === 'BB Anak') {
                    return `Umur: ${this.ageMonths} bln, BB: ${this.currentWeight} kg`
                  }
                  return `${ctx.dataset.label}: ${ctx.formattedValue} kg`
                },
              },
            },
            datalabels: { display: false }, // penting: hilangkan label di titik
          },
          interaction: { mode: 'index', intersect: false },
          elements: {
            point: { radius: 0 }, // tidak tampil titik di sepanjang garis
          },
          scales: {
            x: { title: { display: true, text: `Umur (bulan) ${min}–${max}` }, ticks: { stepSize: 1 } },
            y: { title: { display: true, text: 'Berat Badan (kg)' } },
          },
        },

      })
    },

    // ====== Chart: TB/U ======
    renderTB(curve) {
      const { min, max } = this.getAgeRange()

      const labels = Array.from(
        { length: max - min + 1 },
        (_, i) => i + min
      )
      const sd = (m, key) => this.getPoint(curve, m)[key]

      const minus3 = labels.map(m => sd(m, 'sd3neg'))
      const minus2 = labels.map(m => sd(m, 'sd2neg'))
      const minus1 = labels.map(m => sd(m, 'sd1neg'))
      const median = labels.map(m => sd(m, 'median'))
      const plus1  = labels.map(m => sd(m, 'sd1'))
      const plus2  = labels.map(m => sd(m, 'sd2'))
      const plus3  = labels.map(m => sd(m, 'sd3'))

      if (this.chartTB) this.chartTB.destroy()
      const ctx = this.$refs.chartTB.getContext('2d')

      const C = this.kmsColors
      this.chartTB = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            // Titik TB anak saat ini (hanya 1 titik)
            {
              label: 'TB Anak',
              data: labels.map((m) =>
                m === this.ageMonths ? this.currentHeight : null
              ),
              borderColor: '#0066ff',
              backgroundColor: '#0066ff',
              pointRadius: labels.map((m) => (m === this.ageMonths ? 6 : 0)),
              showLine: false,
            },

            // Kurva KMS (tanpa titik)
            {
              label: '-3SD',
              data: minus3,
              borderColor: C.top,
              /* backgroundColor: this.hexA(C.top, 0.15),
              fill: '+1', */
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '-2SD',
              data: minus2,
              borderColor: C.midTop,
              /* backgroundColor: this.hexA(C.midTop, 0.15),
              fill: '+1', */
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '-1SD',
              data: minus1,
              borderColor: C.mid,
              /* backgroundColor: this.hexA(C.mid, 0.15),
              fill: '+1', */
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: 'Median',
              data: median,
              borderColor: C.midMed,
              borderWidth: 2,
              //fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+1SD',
              data: plus1,
              borderColor: C.midBottom,
              /* backgroundColor: this.hexA(C.midBottom, 0.15),
              fill: '+1', */
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+2SD',
              data: plus2,
              borderColor: C.bottom,
              /* backgroundColor: this.hexA(C.bottom, 0.15),
              fill: '+1', */
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+3SD',
              data: plus3,
              borderColor: C.top,
              /* backgroundColor: this.hexA(C.top, 0.15),
              fill: false, */
              tension: 0.2,
              pointRadius: 0,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'top' },
            title: { display: false, text: 'TB/U: Tinggi Badan vs Umur' },
            tooltip: {
              enabled: true, // tetap tampil saat hover
              callbacks: {
                label: (ctx) => {
                  if (ctx.dataset.label === 'TB Anak') {
                    return `Umur: ${this.ageMonths} bln, TB: ${this.currentHeight} cm`
                  }
                  return `${ctx.dataset.label}: ${ctx.formattedValue} cm`
                },
              },
            },
            datalabels: { display: false }, // penting: hilangkan label di titik
          },
          interaction: { mode: 'index', intersect: false },
          elements: {
            point: { radius: 0 }, // tidak tampil titik di sepanjang garis
          },
          scales: {
            x: { title: {display: true, text: `Umur (bulan) ${min}–${max}` }, ticks: { stepSize: 1 }},
            y: { title: { display: true, text: 'Tinggi Badan (cm)' } },
          },
        },
      })
    },

    renderWFH(curve) {
      const range = 10 // ±10 cm → total 20 cm
      const hMin = Math.round(this.currentHeight) - range
      const hMax = Math.round(this.currentHeight) + range
      // normalize dulu
      const rawData = this.normalizeWFH(curve)

      // FILTER DATA BERDASARKAN TINGGI
      const data = rawData.filter(d => d.h >= hMin && d.h <= hMax)

      const labels = data.map(d => d.h)
      const median = data.map(d => d.median)

      const plus1 = data.map(d => d.sd1)
      const minus1 = data.map(d => d.sd1neg)
      const plus2 = data.map(d => d.sd2)
      const minus2 = data.map(d => d.sd2neg)
      const plus3 = data.map(d => d.sd3)
      const minus3 = data.map(d => d.sd3neg)

      if (this.chartWFH) this.chartWFH.destroy()
      const ctx = this.$refs.chartWFH.getContext('2d')

      const C = this.kmsColors
      this.chartWFH = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'BB Anak',
              data: labels.map(h =>
                h === Math.round(this.currentHeight)
                  ? this.currentWeight
                  : null
              ),
              borderColor: '#0066ff',
              backgroundColor: '#0066ff',
              pointRadius: 6,
              showLine: false,
            },
            {
              label: '-3SD',
              data: minus3,
              borderColor: C.top,
              /* backgroundColor: this.hexA(C.top, 0.15),
              fill: '+1', */
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '-2SD',
              data: minus2,
              borderColor: C.midTop,
              /* backgroundColor: this.hexA(C.midTop, 0.15),
              fill: '+1', */
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '-1SD',
              data: minus1,
              borderColor: C.mid,
              /* backgroundColor: this.hexA(C.mid, 0.15),
              fill: '+1', */
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: 'Median',
              data: median,
              borderColor: C.midMed,
              borderWidth: 2,
              //fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+1SD',
              data: plus1,
              borderColor: C.midBottom,
              /* backgroundColor: this.hexA(C.midBottom, 0.15),
              fill: '+1', */
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+2SD',
              data: plus2,
              borderColor: C.bottom,
              /* backgroundColor: this.hexA(C.bottom, 0.15),
              fill: '+1', */
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+3SD',
              data: plus3,
              borderColor: C.top,
              /* backgroundColor: this.hexA(C.top, 0.15),
              fill: false, */
              tension: 0.2,
              pointRadius: 0,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            title: {
              display: false,
              text: 'BB/TB (WHO)',
            },
            tooltip: {
              enabled: true,
              callbacks: {
                label: (ctx) => {
                  if (ctx.dataset.label === 'BB Anak') {
                    return `TB: ${this.currentHeight} cm, BB: ${this.currentWeight} kg`
                  }
                  return `${ctx.dataset.label}: ${ctx.formattedValue} kg`
                },
              },
            },
            datalabels: { display: false }, // penting: hilangkan label di titik
          },
          interaction: { mode: 'index', intersect: false },
          elements: {
            point: { radius: 0 }, // tidak tampil titik di sepanjang garis
          },
          scales: {
            x: {
              title: { display: true, text: 'Tinggi / Panjang Badan (cm)' },
            },
            y: {
              title: { display: true, text: 'Berat Badan (kg)' },
            },
          },
        },
      })
    },

    // ====== Util ======
    calcAgeMonths(birthDateStr, measureDate) {
      const b = new Date(birthDateStr),
        n = new Date(measureDate)
      let m = (n.getFullYear() - b.getFullYear()) * 12 + (n.getMonth() - b.getMonth())
      if (n.getDate() < b.getDate()) m -= 1
      if (m < 0) m = 0
      if (m > 60) m = 60
      return m
    },
    getAgeRange() {
      return this.ageMonths <= 24
        ? { min: 0, max: 24 }
        : { min: 25, max: 60 }
    },
    getPoint(points, month) {
      return points.find(p => p.month === month) || points[points.length - 1]
    },
    getWFHPoint(points, height) {
      return points.reduce((prev, curr) =>
        Math.abs(curr.h - height) < Math.abs(prev.h - height)
          ? curr
          : prev
      )
    },
    normalizeWFH(points) {
      return points.map(p => ({
        h: p.height ?? p.length,
        ...p
      }))
    },
    classifyWFA(z) {
      if (z < -3) return 'Sangat Kurus'
      if (z < -2) return 'Kurus'
      if (z <= 1) return 'Normal'
      if (z <= 2) return 'Risiko BB Lebih'
      return 'BB Lebih'
    },
    classifyHFA(z) {
      if (z < -3) return 'Sangat Pendek'
      if (z < -2) return 'Pendek'
      if (z <= 3) return 'Normal'
      return 'Tinggi'
    },
    classifyWFH(z) {
      console.log('classifyWFH: ',z);

      if (z < -3) return 'Gizi Buruk'
      if (z < -2) return 'Gizi Kurang'
      if (z <= 1) return 'Gizi Baik'
      if (z <= 2) return 'Risiko Gizi Lebih'
      if (z <= 3) return 'Gizi Lebih'
      return 'Obesitas'
    },

    calcZScore(value, p) {
      if (value < p.sd3neg) {
        return -3 - (p.sd3neg - value) / (p.sd2neg - p.sd3neg)
      }
      if (value < p.sd2neg) {
        return -2 - (p.sd2neg - value) / (p.sd1neg - p.sd2neg)
      }
      if (value < p.sd1neg) {
        return -1 - (p.sd1neg - value) / (p.median - p.sd1neg)
      }
      if (value <= p.median) {
        return (value - p.median) / (p.median - p.sd1neg)
      }
      if (value <= p.sd1) {
        return (value - p.median) / (p.sd1 - p.median)
      }
      if (value <= p.sd2) {
        return 1 + (value - p.sd1) / (p.sd2 - p.sd1)
      }
      if (value <= p.sd3) {
        return 2 + (value - p.sd2) / (p.sd3 - p.sd2)
      }
      return 3 + (value - p.sd3) / (p.sd3 - p.sd2)
    },
    hexA(hex, a) {
      // '#RRGGBB' -> 'rgba(r,g,b,a)'
      const v = hex.replace('#', '')
      const r = parseInt(v.substring(0, 2), 16)
      const g = parseInt(v.substring(2, 4), 16)
      const b = parseInt(v.substring(4, 6), 16)
      return `rgba(${r},${g},${b},${a})`
    },
  },
  mounted(){
    this.wfaBoys = this.whoData.wfa.wfa.L
    this.wfaGirls = this.whoData.wfa.wfa.P
    this.hfaBoys = this.whoData.hfa.hfa.L
    this.hfaGirls = this.whoData.hfa.hfa.P

    const wfh = this.whoData.wfh.wfh

    // umur menentukan range
    const rangeKey = this.ageMonths <= 24 ? '0-24' : '25-60'
    const data = wfh[rangeKey][0]

    this.wfhBoys = data.L
    this.wfhGirls = data.P
  }
}
</script>

<style scoped>
.container {
  max-width: 1024px;
}
.card h6 {
  font-weight: 600;
}
/* OVERLAY */
.kms-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(4px);
  z-index: 1049; /* ⬅️ PENTING */
  animation: fadeIn 0.25s ease;
}

/* MODAL */
.modal {
  position: fixed; /* ⬅️ PENTING */
  inset: 0;
  z-index: 1050;
}

@keyframes fadeIn {
  from { opacity: 0 }
  to { opacity: 1 }
}
.kalkulator-card-title {
  text-align: center;
  font-size: 1.3rem;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 0.25rem;
}

.kalkulator-label {
  text-align: center;
  font-size: 1rem;
  font-weight: 400;
  color: rgba(255,255,255,0.9);
  margin-bottom: 0.75rem;
}

.kalkulator-card-desc {
  text-align: center;
  font-size: 0.85rem;
  color: rgba(255,255,255,0.85);
  margin-bottom: 1.5rem;
}


/* GENDER CARD */
/* ===== KOTAK LUAR (DEFAULT) ===== */
.gender-card {
  background: #ffffff;
  border-radius: 10px;
  padding: 12px;
  text-align: center;
  cursor: pointer;
  border: 2px solid #e5e7eb;
  transition: all 0.25s ease;
  position: relative;
}

/* ===== LABEL ===== */
.gender-card span {
  display: block;
  margin-top: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #6b7280;
}

/* ===== KOTAK DALAM (ICON AREA) ===== */
.gender-inner {
  border-radius: 10px;
  padding: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.gender-inner.male {
  background: #7fae95;
}

.gender-inner.female {
  background: #bfc4c6;
}

.gender-inner img {
  width: 42px;
  height: 42px;
}

/* ===== ACTIVE / TERPILIH ===== */
.gender-card.active {
  border-color: #2f6f63;
  background: #f0f8f5;
  box-shadow: 0 8px 20px rgba(0,0,0,0.12);
  transform: translateY(-2px);
}

/* label berubah */
.gender-card.active span {
  color: #1f5f46;
}

/* icon lebih kontras */
.gender-card.active .gender-inner {
  box-shadow: inset 0 0 0 2px rgba(255,255,255,0.6);
}

/* ===== CHECK ICON (opsional tapi cakep) ===== */
.gender-card.active::after {
  content: "✓";
  position: absolute;
  top: 8px;
  right: 8px;
  background: #2f6f63;
  color: #ffffff;
  width: 22px;
  height: 22px;
  font-size: 13px;
  font-weight: 700;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}



/* INPUT */
.kalkulator-input {
  border-radius: 10px;
  font-size: 0.9rem;
}

/* BUTTON */
.kalkulator-btn {
  background: #cfe8da;
  color: #1f5f46;
  font-weight: 600;
  border-radius: 10px;
  padding: 10px;
  border: none;
}

.kalkulator-btn:hover {
  background: #70b890;
}

</style>
