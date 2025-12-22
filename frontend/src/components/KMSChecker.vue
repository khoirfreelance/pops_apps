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
          <div class="modal-header justify-content-center position-relative">
            <h3 class="modal-title w-100 text-center">
              Hasil Perhitungan Status Gizi
            </h3>

            <!-- close button tetap kanan atas -->
            <button
              type="button"
              class="btn-close position-absolute end-0 me-3"
              @click="showResultModal = false"
            ></button>
          </div>

          <!-- BODY -->
          <div class="modal-body text-center">

            <!-- RINGKASAN -->
            <div class="row g-3 justify-content-center text-center alert alert-info mb-4">
              <div class="col-md-3">
                <div class="small text-muted">Usia</div>
                <div class="fs-5 fw-semibold">{{ ageMonths }} bln</div>
              </div>

              <div class="col-md-3">
                <div class="small text-muted">BB Ideal (Median WHO)</div>
                <div class="fs-5 fw-semibold">
                  {{ idealWeight.toFixed(1) }} kg
                </div>
              </div>

              <div class="col-md-3">
                <div class="small text-muted">Status BB/U</div>
                <span class="badge px-3 py-2" :class="statusWfaBadge">
                  {{ statusWfa }}
                </span>
              </div>

              <div class="col-md-3">
                <div class="small text-muted">Status TB/U</div>
                <span class="badge px-3 py-2" :class="statusHfaBadge">
                  {{ statusHfa }}
                </span>
              </div>
            </div>

            <!-- CHART BB/U -->
            <div class="card border-0 shadow-sm mb-4">
              <div class="card-body">
                <h6 class="mb-3">BB/U (0–60 bulan)</h6>
                <div style="height: 400px">
                  <canvas ref="chartBB"></canvas>
                </div>
              </div>
            </div>

            <!-- CHART TB/U -->
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <h6 class="mb-3">TB/U (0–60 bulan)</h6>
                <div style="height: 400px">
                  <canvas ref="chartTB"></canvas>
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
      showResultModal: false,
      birthDate: '',
      gender: '',
      currentWeight: null,
      currentHeight: null,

      ageMonths: 0,
      idealWeight: 0,

      statusWfa: '-',
      statusHfa: '-',

      calculated: false,
      chartBB: null,
      chartTB: null,

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

      // Warna KMS (atas → bawah) yang kamu minta:
      kmsColors: {
        top: '#F2D803',
        midTop: '#84BA24',
        mid: '#2CA339',
        midMed: '#2DA83C', // dipakai utk median
        midBottom: '#80B626',
        bottom: '#DCBF1E',
      },
    }
  },
  computed: {
    statusWfaBadge() {
      switch (this.statusWfa) {
        case 'Gizi Buruk':
          return 'bg-danger'
        case 'Gizi Kurang':
          return 'bg-warning text-dark'
        case 'Normal':
          return 'bg-success'
        case 'Gizi Lebih':
          return 'bg-primary'
        case 'Gizi Obesitas':
          return 'bg-dark'
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

      // === WFA
      this.idealWeight = this.interpolateMedian(wfa, this.ageMonths)
      const sdW = this.interpolateSD(wfa, this.ageMonths)
      const zW = (this.currentWeight - this.idealWeight) / sdW
      this.statusWfa = this.classifyWFA(zW)

      // === HFA
      const medianH = this.interpolateMedian(hfa, this.ageMonths)
      const sdH = this.interpolateSD(hfa, this.ageMonths)
      const zH = (this.currentHeight - medianH) / sdH
      this.statusHfa = this.classifyHFA(zH)

      this.calculated = true
      this.showResultModal = true   // 🔥 buka modal

      await nextTick()              // 🔥 tunggu modal render
      this.renderBB(wfa)
      this.renderTB(hfa)
    },

    // ====== Chart: BB/U ======
    renderBB(curve) {
      const labels = Array.from({ length: 61 }, (_, i) => i)
      const median = labels.map((m) => this.interpolateMedian(curve, m))
      const sdAt = (m) => this.interpolateSD(curve, m)
      const plus1 = labels.map((m, i) => median[i] + sdAt(m))
      const minus1 = labels.map((m, i) => median[i] - sdAt(m))
      const plus2 = labels.map((m, i) => median[i] + 2 * sdAt(m))
      const minus2 = labels.map((m, i) => median[i] - 2 * sdAt(m))
      const plus3 = labels.map((m, i) => median[i] + 3 * sdAt(m))
      const minus3 = labels.map((m, i) => median[i] - 3 * sdAt(m))

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
              data: labels.map((m) => (m === this.ageMonths ? this.currentWeight : null)),
              borderColor: '#000',
              backgroundColor: '#000',
              pointRadius: labels.map((m) => (m === this.ageMonths ? 6 : 0)),
              pointHoverRadius: 8,
              showLine: false,
            },

            // Kurva KMS (tanpa titik, dengan fill gradasi)
            {
              label: '-3SD',
              data: minus3,
              borderColor: C.top,
              backgroundColor: this.hexA(C.top, 0.15),
              fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '-2SD',
              data: minus2,
              borderColor: C.midTop,
              backgroundColor: this.hexA(C.midTop, 0.15),
              fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '-1SD',
              data: minus1,
              borderColor: C.mid,
              backgroundColor: this.hexA(C.mid, 0.15),
              fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: 'Median',
              data: median,
              borderColor: C.midMed,
              borderWidth: 2,
              fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+1SD',
              data: plus1,
              borderColor: C.midBottom,
              backgroundColor: this.hexA(C.midBottom, 0.15),
              fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+2SD',
              data: plus2,
              borderColor: C.bottom,
              backgroundColor: this.hexA(C.bottom, 0.15),
              fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+3SD',
              data: plus3,
              borderColor: C.top,
              backgroundColor: this.hexA(C.top, 0.15),
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
    title: { display: true, text: 'BB/U: Berat Badan vs Umur (0–60 bln)' },
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
    x: { title: { display: true, text: 'Umur (bulan)' }, ticks: { stepSize: 2 } },
    y: { title: { display: true, text: 'Berat Badan (kg)' } },
  },
},

      })
    },

    // ====== Chart: TB/U ======
    renderTB(curve) {
      const labels = Array.from({ length: 61 }, (_, i) => i)
      const median = labels.map((m) => this.interpolateMedian(curve, m))
      const sdAt = (m) => this.interpolateSD(curve, m)
      const plus1 = labels.map((m, i) => median[i] + sdAt(m))
      const minus1 = labels.map((m, i) => median[i] - sdAt(m))
      const plus2 = labels.map((m, i) => median[i] + 2 * sdAt(m))
      const minus2 = labels.map((m, i) => median[i] - 2 * sdAt(m))
      const plus3 = labels.map((m, i) => median[i] + 3 * sdAt(m))
      const minus3 = labels.map((m, i) => median[i] - 3 * sdAt(m))

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
              data: labels.map((m) => (m === this.ageMonths ? this.currentHeight : null)),
              borderColor: '#000',
              backgroundColor: '#000',
              pointRadius: labels.map((m) => (m === this.ageMonths ? 6 : 0)),
              pointHoverRadius: 8,
              showLine: false,
            },

            // Kurva KMS (tanpa titik)
            {
              label: '-3SD',
              data: minus3,
              borderColor: C.top,
              backgroundColor: this.hexA(C.top, 0.15),
              fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '-2SD',
              data: minus2,
              borderColor: C.midTop,
              backgroundColor: this.hexA(C.midTop, 0.15),
              fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '-1SD',
              data: minus1,
              borderColor: C.mid,
              backgroundColor: this.hexA(C.mid, 0.15),
              fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: 'Median',
              data: median,
              borderColor: C.midMed,
              borderWidth: 2,
              fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+1SD',
              data: plus1,
              borderColor: C.midBottom,
              backgroundColor: this.hexA(C.midBottom, 0.15),
              fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+2SD',
              data: plus2,
              borderColor: C.bottom,
              backgroundColor: this.hexA(C.bottom, 0.15),
              fill: '+1',
              tension: 0.2,
              pointRadius: 0,
            },
            {
              label: '+3SD',
              data: plus3,
              borderColor: C.top,
              backgroundColor: this.hexA(C.top, 0.15),
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
    title: { display: true, text: 'TB/U: Tinggi Badan vs Umur (0–60 bln)' },
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
    x: { title: { display: true, text: 'Umur (bulan)' }, ticks: { stepSize: 2 } },
    y: { title: { display: true, text: 'Berat Badan (kg)' } },
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

    interpolateMedian(points, month) {
      for (let i = 0; i < points.length - 1; i++) {
        const a = points[i],
          b = points[i + 1]
        if (month >= a.m && month <= b.m) {
          const t = (month - a.m) / (b.m - a.m)
          return a.median + t * (b.median - a.median)
        }
      }
      if (month < points[0].m) return points[0].median
      return points[points.length - 1].median
    },
    interpolateSD(points, month) {
      for (let i = 0; i < points.length - 1; i++) {
        const a = points[i],
          b = points[i + 1]
        if (month >= a.m && month <= b.m) {
          const t = (month - a.m) / (b.m - a.m)
          return a.sd + t * (b.sd - a.sd)
        }
      }
      if (month < points[0].m) return points[0].sd
      return points[points.length - 1].sd
    },

    classifyWFA(z) {
      if (z < -3) return 'Gizi Buruk'
      if (z < -2) return 'Gizi Kurang'
      if (z <= 1) return 'Normal'
      if (z <= 2) return 'Gizi Lebih'
      return 'Gizi Obesitas'
    },
    classifyHFA(z) {
      if (z < -3) return 'Sangat Pendek'
      if (z < -2) return 'Pendek'
      if (z <= 3) return 'Normal'
      return 'Tinggi'
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
