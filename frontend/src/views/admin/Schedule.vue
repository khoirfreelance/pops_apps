<template>
  <div class="schedule-wrapper">
    <!-- Header -->
    <HeaderAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

    <div class="d-flex flex-column flex-md-row">
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" />

      <!-- Main Content -->
      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <div
          class="flex-grow-1 p-4 bg-light container-fluid"
          :style="{
            backgroundImage: background ? `url(${background})` : 'none',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            backgroundAttachment: 'fixed',
          }"
        >
          <!-- Welcome Card -->
          <div class="card welcome-card shadow-sm mb-4 border-0">
            <div
              class="card-body d-flex flex-column flex-md-row align-items-start py-0 justify-content-between"
            >
              <!-- Kiri: Teks Welcome -->
              <div class="text-start">
                <div class="my-3">
                  <h2 class="fw-bold mt-3 mb-0 text-white">Jadwal Intervensi</h2>
                  <small class="text-white"> Atur jadwal intervensi anda </small>
                </div>
                <nav aria-label="breadcrumb" class="mt-auto mb-2">
                  <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                      <router-link to="/admin" class="text-decoration-none text-white-50">
                        Beranda
                      </router-link>
                    </li>
                    <li class="breadcrumb-item active text-white" aria-current="page">
                      Jadwal Intervensi
                    </li>
                  </ol>
                </nav>
              </div>

              <!-- Kanan: Gambar -->
              <div class="mt-3 mt-md-0">
                <img src="/src/assets/admin.png" alt="Welcome" class="img-fluid welcome-img" />
              </div>
            </div>
          </div>

          <!-- Kalender -->
          <div class="container-fluid">
            <div class="card modern-card my-4">
              <div class="card-body">
                <div class="moodle-calendar">
                  <div
                    class="calendar-header d-flex justify-content-between align-items-center mb-3"
                  >
                    <div class="d-flex gap-2 align-items-center">
                      <button class="btn btn-sm btn-outline-primary" @click="prevMonth()">‹</button>
                      <h4 class="mb-0">{{ monthYearLabel }}</h4>
                      <button class="btn btn-sm btn-outline-primary" @click="nextMonth()">›</button>
                    </div>

                    <div>
                      <button class="btn btn-primary" @click="openAddModal(null)">
                        <i class="bi bi-plus-square"></i> Tambah Intervensi
                      </button>
                    </div>
                  </div>

                  <div class="row g-3">
                    <div class="col-lg-8">
                      <div class="calendar-grid">
                        <div class="weekdays d-flex">
                          <div v-for="wd in weekdays" :key="wd" class="weekday text-center">
                            {{ wd }}
                          </div>
                        </div>

                        <div class="days">
                          <div
                            v-for="cell in calendarCells"
                            :key="cell.key"
                            :class="[
                              'day-cell',
                              { muted: !cell.currentMonth, today: isToday(cell.date) },
                            ]"
                            :style="getCellStyle(cell.isoDate)"
                            @click="openAddModal(cell.date)"
                          >
                            <div class="date-number">{{ cell.date.getDate() }}</div>

                            <div class="events-preview">
                              <div
                                v-for="ev in eventsByDate[cell.isoDate] || []"
                                :key="ev.id"
                                class="event-dot"
                                :style="{ backgroundColor: categoryColors[ev.category] || '#ccc' }"
                              ></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-4">
                      <div class="card">
                        <div class="card-body">
                          <h5>Event pada bulan ini</h5>
                          <div v-if="sortedEvents.length === 0">Belum ada event.</div>
                          <ul class="list-group list-group-flush mt-2">
                            <li
                              v-for="ev in sortedEvents"
                              :key="ev.id"
                              class="list-group-item d-flex justify-content-between align-items-start"
                            >
                              <div>
                                <div class="fw-semibold">{{ ev.title }}</div>
                                <small class="text-muted">{{ formatEventDate(ev) }}</small>
                              </div>
                              <div class="btn-group">
                                <button
                                  class="btn btn-sm btn-outline-secondary"
                                  @click="editEvent(ev)"
                                >
                                  Edit
                                </button>
                                <button
                                  class="btn btn-sm btn-outline-danger"
                                  @click="deleteEvent(ev.id)"
                                >
                                  Hapus
                                </button>
                              </div>
                            </li>
                          </ul>
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

  <!-- Add/Edit Modal -->
  <div class="modal-backdrop" v-if="showModal">
    <div
      class="modal-content-box"
      :style="{
        backgroundImage: background ? `url(${background})` : 'none',
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        backgroundAttachment: 'fixed',
      }"
    >
      <div class="modal-header text-primary rounded-top-4 mb-3">
        <h5 class="modal-title fw-bold text-primary">
          {{ editingEvent ? 'Edit Intervensi' : 'Tambah Intervensi' }}
        </h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          @click="closeModal"
        ></button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="saveEvent">
          <div class="mb-2">
            <label class="form-label">Judul</label>
            <input v-model="form.title" class="form-control" required />
          </div>

          <div class="mb-2 row">
            <div class="col-6">
              <label class="form-label">Tanggal</label>
              <input v-model="form.date" type="date" class="form-control" required />
            </div>
            <div class="col-6">
              <label class="form-label">Waktu (opsional)</label>
              <input v-model="form.time" type="time" class="form-control" />
            </div>
          </div>

          <div class="mb-2 row">
            <div class="col-6">
              <label class="form-label">Kategori</label>
              <select v-model="form.category" class="form-control">
                <option value="">Pilih Kategori</option>
                <option value="Gizi Anak">Gizi Anak</option>
                <option value="Ibu Hamil">Ibu Hamil</option>
                <option value="Calon Pengantin">Calon Pengantin</option>
                <option value="Lain-lain">Lain-lain</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Pilih PJ</label>
              <input
                type="text"
                class="form-control"
                v-model="form.pj"
                @input="filterNames"
                placeholder="Ketik nama..."
              />

              <!-- Suggestion dropdown -->
              <ul
                v-if="filteredNames.length > 0"
                class="list-group position-absolute"
                style="z-index: 1000; max-height: 150px; overflow-y: auto"
              >
                <li
                  v-for="(name, index) in filteredNames"
                  :key="index"
                  class="list-group-item list-group-item-action"
                  @click="selectName(name)"
                >
                  {{ name }}
                </li>
              </ul>
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label">Deskripsi</label>
            <textarea v-model="form.description" class="form-control" rows="3"></textarea>
          </div>
        </form>
      </div>

      <div class="modal-footer border-0 d-flex justify-content-between mt-4">
        <button class="btn btn-light border rounded-pill px-4" @click="closeModal">
          <i class="bi bi-x-circle me-2"></i> Batal
        </button>
        <button class="btn btn-success rounded-pill px-4" @click="saveEvent">
          <i class="bi bi-save me-2"></i> Simpan
        </button>
      </div>
    </div>
  </div>

  <!-- Modal Success -->
  <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content border-0 shadow-lg rounded-4"
        :style="{
          backgroundImage: background ? `url(${background})` : 'none',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          backgroundAttachment: 'fixed',
        }"
      >
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title">✅ Berhasil</h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body text-center">
          <p class="mb-0">Data Anak berhasil disimpan ke <strong>localStorage</strong>.</p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">
            OK
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Loader Overlay with Animated Progress -->
  <div
    v-if="isLoadingImport"
    class="position-fixed top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark bg-opacity-50"
    style="z-index: 2000"
  >
    <div class="w-50">
      <div class="progress" style="height: 1.8rem; border-radius: 1rem; overflow: hidden">
        <div
          class="progress-bar progress-bar-striped progress-bar-animated"
          role="progressbar"
          :style="{ width: importProgress + '%' }"
          :data-progress="progressLevel"
        >
          <span class="fw-bold">{{ animatedProgress }}%</span>
        </div>
      </div>
    </div>
    <p class="text-white mt-3">Mengimpor data... {{ currentRow }}/{{ totalRows }} baris</p>
  </div>
</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import { Modal } from 'bootstrap'
import { eventBus } from '@/eventBus'

function toLocalISODate(date) {
  const d = new Date(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

// eslint-disable-next-line no-unused-vars
const categoryColors = {
  'Gizi Anak': 'primary',
  'Ibu Hamil': 'danger',
  'Calon Pengantin': 'info',
  'Lain-lain': 'additional',
}

const emptyForm = () => ({
  id: null,
  title: '',
  date: toLocalISODate(new Date()), // fix di sini juga
  time: '',
  description: '',
  pj: '',
  category: '',
  color: '#006341',
})

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'schedule',
  components: { CopyRight, NavbarAdmin, HeaderAdmin },
  props: {
    initialEvents: { type: Array, default: () => [] },
    storageKey: { type: String, default: 'moodle_calendar_events' },
  },
  data() {
    return {
      isCollapsed: false,
      isLoadingImport: false,
      importProgress: 0,
      animatedProgress: 0,
      currentRow: 0,
      totalRows: 1,
      current: new Date(),
      weekdays: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
      events: [],
      showModal: false,
      editingEvent: null,
      form: emptyForm(),
      categoryColors: {
        'Gizi Anak': '#b3a369',
        'Ibu Hamil': '#0d6efd',
        'Calon Pengantin': '#b3a369',
        'Lain-lain': '#a2aaad',
      },
      allNames: ['Andi Saputra', 'Budi Santoso', 'Citra Lestari', 'Dewi Anggraini', 'Eko Prasetyo'],
      filteredNames: [],
    }
  },
  computed: {
    background() {
      try {
        return JSON.parse(localStorage.getItem('siteConfig'))?.background || null
      } catch {
        return null
      }
    },
    bgStyle() {
      return this.background
        ? {
            backgroundImage: `url(${this.background})`,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            backgroundAttachment: 'fixed',
          }
        : {}
    },
    monthYearLabel() {
      return this.current.toLocaleString('id-ID', { month: 'long', year: 'numeric' })
    },
    calendarCells() {
      const first = new Date(this.current.getFullYear(), this.current.getMonth(), 1)
      const start = new Date(first)
      start.setDate(first.getDate() - first.getDay())

      return Array.from({ length: 42 }, (_, i) => {
        const d = new Date(start)
        d.setDate(start.getDate() + i)
        return {
          key: d.toDateString(),
          date: d,
          currentMonth: d.getMonth() === this.current.getMonth(),
          isoDate: toLocalISODate(d), // ✅ pakai fungsi lokal
        }
      })
    },

    eventsByDate() {
      return this.events.reduce((acc, ev) => {
        ;(acc[ev.date] ||= []).push(ev)
        return acc
      }, {})
    },
    sortedEvents() {
      const month = this.current.getMonth()
      const year = this.current.getFullYear()
      return this.events
        .filter((e) => {
          const d = new Date(`${e.date}T${e.time || '00:00'}`)
          return d.getMonth() === month && d.getFullYear() === year
        })
        .sort((a, b) => (a.date + (a.time || '')).localeCompare(b.date + (b.time || '')))
    },
  },
  created() {
    this.loadEvents()
    if (this.initialEvents.length) {
      this.events.push(...this.initialEvents)
      this.saveEvents()
    }
  },
  mounted() {
    eventBus.on('jumpToDate', (date) => {
      this.focusDate(date) // method kamu buat untuk set tanggal aktif
    })
  },
  beforeUnmount() {
    eventBus.off('jumpToDate')
  },
  methods: {
    async runProgressSimulation() {
      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0
      this.currentRow = 0

      return new Promise((resolve) => {
        const interval = setInterval(() => {
          if (this.importProgress >= 100) {
            clearInterval(interval)
            this.isLoadingImport = false
            resolve()
          } else {
            this.importProgress += 10
            this.animatedProgress = this.importProgress
            this.currentRow = this.importProgress
          }
        }, 200) // kecepatan animasi progress
      })
    },
    filterNames() {
      const q = this.form.pj.toLowerCase()
      this.filteredNames = this.allNames.filter((n) => n.toLowerCase().includes(q))
    },
    selectName(name) {
      this.form.pj = name
      this.filteredNames = []
    },
    getCellStyle(isoDate) {
      const events = this.eventsByDate[isoDate] || []
      if (events.length === 0) return {}

      return {
        border: '2px solid var(--bs-primary)', // highlight cell
        borderRadius: '4px',
      }
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    prevMonth() {
      this.current = new Date(this.current.getFullYear(), this.current.getMonth() - 1, 1)
    },
    nextMonth() {
      this.current = new Date(this.current.getFullYear(), this.current.getMonth() + 1, 1)
    },
    isToday(d) {
      const now = new Date()
      return d.toDateString() === now.toDateString()
    },
    openAddModal(date) {
      this.editingEvent = null
      this.form = emptyForm()
      if (date) {
        this.form.date = toLocalISODate(date) // fix: pakai local date
      }
      this.showModal = true
    },
    editEvent(ev) {
      this.editingEvent = ev
      this.form = { ...ev }
      this.showModal = true
    },
    closeModal() {
      this.showModal = false
      this.editingEvent = null
    },
    async saveEvent() {
      const cat = this.form.category
      const bootstrapColor = this.categoryColors[cat] || '#ccc'

      const newEvent = {
        ...this.form,
        color: bootstrapColor,
        id: this.form.id || Date.now(),
      }

      if (this.editingEvent) {
        const idx = this.events.findIndex((e) => e.id === this.form.id)
        if (idx !== -1) this.events[idx] = newEvent
      } else {
        this.events.push(newEvent)
      }

      // Simpan ke localStorage
      this.saveEvents()

      // Tutup modal input
      this.closeModal()

      // Jalankan progress simulasi
      await this.runProgressSimulation()

      // ✅ Perbaikan di sini
      const successModal = new Modal(document.getElementById('successModal'))
      successModal.show()
    },
    deleteEvent(id) {
      if (confirm('Hapus event ini?')) {
        this.events = this.events.filter((e) => e.id !== id)
        this.saveEvents()
      }
    },
    loadEvents() {
      try {
        this.events = JSON.parse(localStorage.getItem(this.storageKey)) || []
      } catch {
        this.events = []
      }
    },
    saveEvents() {
      localStorage.setItem(this.storageKey, JSON.stringify(this.events))
      // 🔥 beritahu semua komponen lain (termasuk HeaderAdmin)
      eventBus.emit('eventsUpdated', this.events)
    },
    formatEventDate(ev) {
      return `${ev.date}${ev.time ? ` ${ev.time}` : ''} • ${ev.category || '—'}`
    },
    focusDate(date) {
      // contoh: ubah currentDate jadi date event
      this.currentDate = new Date(date)
    },
  },
}
</script>

<style scoped>
.event-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
  margin: 2px;
}

.schedule-wrapper {
  /*   tinggi navbar bootstrap default */
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  min-height: 100vh;
}
/* Gradient Banner */
.schedule-banner {
  background: linear-gradient(90deg, var(--bs-primary), var(--bs-secondary));
  border-radius: 0 0 1rem 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
.moodle-calendar {
  font-family:
    Inter,
    system-ui,
    -apple-system,
    'Segoe UI',
    Roboto,
    'Helvetica Neue',
    Arial;
}
.calendar-header {
  gap: 12px;
}
.calendar-grid {
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-radius: 6px;
  overflow: hidden;
}
.weekdays {
  background: #f8f9fa;
}
.weekday {
  flex: 1;
  padding: 10px 6px;
  font-weight: 600;
  text-align: center;
}
.days {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
}
.day-cell {
  min-height: 100px;
  border-right: 1px solid rgba(0, 0, 0, 0.04);
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
  padding: 6px;
  cursor: pointer;
}
.day-cell.muted {
  background: #fbfbfb;
  color: #999;
}
.date-number {
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 6px;
}
.events-preview {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.event-chip {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 2px 4px;
  border-radius: 4px;
  font-size: 13px;
  overflow: hidden;
}
.event-chip .dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}
.event-chip .truncate {
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
}
.day-cell.today {
  outline: 2px solid rgba(43, 140, 244, 0.12);
}
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}
.modal-content-box {
  background: #fff;
  padding: 18px;
  border-radius: 8px;
  width: 100%;
  max-width: 520px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}
@media (max-width: 992px) {
  .day-cell {
    min-height: 80px;
  }
}
</style>
