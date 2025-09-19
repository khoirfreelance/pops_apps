<template>
  <div class="bride-wrapper">
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
                  <h2 class="fw-bold mt-3 mb-0 text-white">Data Calon Pengantin</h2>
                  <small class="text-white"> Daftar kunjungan calon pengantin </small>
                </div>
                <nav aria-label="breadcrumb" class="mt-auto mb-2">
                  <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                      <router-link to="/admin" class="text-decoration-none text-white-50">
                        Beranda
                      </router-link>
                    </li>
                    <li class="breadcrumb-item active text-white" aria-current="page">
                      Calon Pengantin
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

          <!-- Filter -->
          <div class="filter-wrapper bg-light rounded shadow-sm p-3 mt-3 container-fluid">
            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <!-- NIK (selalu tampil, realtime filter) -->
              <div class="col-md-12">
                <label for="nik" class="form-label">NIK Perempuan</label>
                <input
                  type="text"
                  v-model="filter.nikP"
                  id="nikP"
                  class="form-control"
                  placeholder="Cari berdasarkan NIK Pengantin Wanita"
                />
              </div>

              <!-- Expandable section -->
              <div v-if="isFilterOpen" class="row g-3 align-items-end mt-2">
                <!-- Nama -->
                <div class="col-md-6">
                  <label for="nama" class="form-label">Nama Pengantin Wanita</label>
                  <input
                    type="text"
                    v-model="advancedFilter.namaP"
                    id="namaP"
                    class="form-control"
                  />
                </div>

                <div class="col-md-6">
                  <label for="nama" class="form-label">Nama Pengantin Pria</label>
                  <input
                    type="text"
                    v-model="advancedFilter.namaL"
                    id="namaL"
                    class="form-control"
                  />
                </div>

                <!-- RT -->
                <div class="col-md-2">
                  <label for="rt" class="form-label">RT</label>
                  <input type="number" v-model="advancedFilter.rt" id="rt" class="form-control" />
                </div>

                <!-- RW -->
                <div class="col-md-2">
                  <label for="rw" class="form-label">RW</label>
                  <input type="number" v-model="advancedFilter.rw" id="rw" class="form-control" />
                </div>

                <!-- Menikah -->
                <div class="col-md-4">
                  <label for="menikah" class="form-label">Tanggal Menikah</label>
                  <input
                    type="date"
                    v-model="advancedFilter.menikah"
                    id="menikah"
                    class="form-control"
                  />
                </div>

                <!-- Kunjungan -->
                <div class="col-md-4">
                  <label for="kunjungan" class="form-label">Kunjungan</label>
                  <input
                    type="date"
                    v-model="advancedFilter.kunjungan"
                    id="kunjungan"
                    class="form-control"
                  />
                </div>

                <!-- Tombol -->
                <div class="col-md-12">
                  <button type="submit" class="btn btn-primary float-start" @click="applyFilter">
                    <i class="bi bi-search"></i> Cari
                  </button>
                  <button type="button" class="btn btn-secondary float-end" @click="resetFilter">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                  </button>
                </div>
              </div>
            </form>

            <!-- Expand/Collapse Button -->
            <div class="text-end mt-2">
              <button type="button" class="btn btn-outline-secondary btn-sm" @click="toggleExpand">
                <i :class="isFilterOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
                {{ isFilterOpen ? 'Tutup Filter Lain' : 'Filter Lain' }}
              </button>
            </div>
          </div>

          <!-- Button Group -->
          <div class="container-fluid mt-4 d-flex flex-wrap gap-2 justify-content-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
              <i class="bi bi-plus-square"></i> Tambah Data
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
              <i class="bi bi-filetype-csv"></i> Import Pendampingan TPK (catin)
            </button>
          </div>

          <!-- Alert -->
          <!-- <div class="container-fluid mt-4">
            <div class="alert alert-success shadow-sm">
              <i class="bi bi-info-circle-fill"></i>&nbsp; Daftar Calon Pengantin
            </div>
          </div> -->

          <!-- Table -->
          <div class="container-fluid">
            <div class="card modern-card mt-4">
              <div class="card-body">
                <div class="datatable-responsive">
                  <EasyDataTable
                    :headers="visibleHeaders"
                    :items="filteredCatin"
                    buttons-pagination
                    :rows-per-page="5"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
        <CopyRight class="mt-auto" />
      </div>
    </div>
  </div>

  <!-- Modal Tambah -->
  <div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div
        class="modal-content shadow-lg border-0 rounded-4"
        :style="{
          backgroundImage: background ? `url(${background})` : 'none',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          backgroundAttachment: 'fixed',
        }"
      >
        <div class="modal-header text-primary bg-light border-0 rounded-top-4">
          <h5 class="modal-title fw-bold text-primary">Tambah Data Calon Pengantin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <form class="row g-4" @submit.prevent="saveData">
            <!-- Catatan Berisiko -->
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">Catatan Berisiko</label>
              <input type="text" class="form-control shadow-sm" v-model="form.catatan" />
            </div>

            <!-- Tanggal Kunjungan -->
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">Tanggal Kunjungan</label>
              <input type="date" class="form-control shadow-sm" v-model="form.kunjungan" />
            </div>

            <!-- Tanggal Menikah -->
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">Tanggal Menikah</label>
              <input type="date" class="form-control shadow-sm" v-model="form.menikah" />
            </div>

            <!-- Catin Perempuan -->
            <div class="col-12"><h5 class="fw-bold text-primary mt-3">Catin Perempuan</h5></div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Nama</label>
              <input type="text" class="form-control shadow-sm" v-model="form.namaP" />
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">NIK</label>
              <input
                type="text"
                class="form-control shadow-sm"
                v-model="form.nikP"
                maxlength="16"
                @input="form.nikP = form.nikP.replace(/\D/g, '')"
              />
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">Usia</label>
              <input type="number" class="form-control shadow-sm" v-model="form.usiaP" />
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">Pekerjaan</label>
              <input type="text" class="form-control shadow-sm" v-model="form.pekerjaanP" />
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">Berat Badan (kg)</label>
              <input type="number" class="form-control shadow-sm" v-model="form.bbP" />
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">Tinggi Badan (cm)</label>
              <input type="number" class="form-control shadow-sm" v-model="form.tbP" />
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">LiLa (cm)</label>
              <input type="number" class="form-control shadow-sm" v-model="form.lilaP" />
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">Hb</label>
              <input type="text" class="form-control shadow-sm" v-model="form.hbP" />
            </div>

            <!-- Catin Laki-laki -->
            <div class="col-12"><h5 class="fw-bold text-primary mt-3">Catin Laki-laki</h5></div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Nama</label>
              <input type="text" class="form-control shadow-sm" v-model="form.namaL" />
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">NIK</label>
              <input
                type="text"
                class="form-control shadow-sm"
                v-model="form.nikL"
                maxlength="16"
                @input="form.nikL = form.nikL.replace(/\D/g, '')"
              />
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Usia</label>
              <input type="number" class="form-control shadow-sm" v-model="form.usiaL" />
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Pekerjaan</label>
              <input type="text" class="form-control shadow-sm" v-model="form.pekerjaanL" />
            </div>

            <!-- Lingkungan -->
            <div class="col-12"><h5 class="fw-bold text-primary mt-3">Lingkungan</h5></div>

            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">Riwayat Penyakit</label>
              <input type="text" class="form-control shadow-sm" v-model="form.riwayat" />
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">Jamban Sehat</label>
              <select class="form-select shadow-sm" v-model="form.jamban">
                <option value="">-- Pilih --</option>
                <option value="Ya">Ya</option>
                <option value="Tidak">Tidak</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">Sumber Air Bersih</label>
              <input type="text" class="form-control shadow-sm" v-model="form.air" />
            </div>

            <!-- Intervensi -->
            <div class="col-md-12">
              <label class="form-label small fw-semibold text-secondary">Intervensi</label>
              <textarea
                class="form-control shadow-sm"
                v-model="form.intervensi"
                rows="2"
              ></textarea>
            </div>
          </form>
        </div>

        <div class="modal-footer border-0 d-flex justify-content-between">
          <button class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i> Batal
          </button>
          <button class="btn btn-success rounded-pill px-4" @click="saveData">
            <i class="bi bi-save me-2"></i> Simpan
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Import -->
  <div class="modal fade" id="modalImport" ref="modalImport" tabindex="-1">
    <div class="modal-dialog">
      <div
        class="modal-content"
        :style="{
          backgroundImage: background ? `url(${background})` : 'none',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          backgroundAttachment: 'fixed',
        }"
      >
        <div class="modal-header text-primary bg-light border-0 rounded-top-4">
          <h5 class="modal-title">Import File Pendampingan TPK</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning p-2">
            <ul>
              <li>Import data untuk kunjungan kehamilan oleh pendampingan TPK</li>
              <li>Pastikan data yang diimport, berformat xlxs</li>
              <li>Pastikan data sudah lengkap sebelum di import</li>
            </ul>
          </div>
          <input type="file" class="form-control" ref="csvFile" accept=".csv" />
        </div>
        <div class="modal-footer border-0 d-flex justify-content-between">
          <button class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i> Batal
          </button>
          <button class="btn btn-success rounded-pill px-4" @click="handleImport">
            <i class="bi bi-upload me-2"></i> Unggah
          </button>
        </div>
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
import EasyDataTable from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'
import { Modal } from 'bootstrap'

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Bride',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, EasyDataTable },
  data() {
    return {
      isCollapsed: false,
      isFilterOpen: false,
      importTitle: 'Import File',
      showAlert: false,
      isLoadingImport: false,
      importProgress: 0,
      animatedProgress: 0,
      currentRow: 0,
      totalRows: 1, // default 1 agar tidak bagi 0
      visibleColumns: ['catatan', 'kunjungan', 'menikah', 'namaP', 'nikP'], // default
      form: {
        catatan: '',
        kunjungan: '',
        menikah: '',
        namaP: '',
        nikP: '',
        usiaP: 0,
        pekerjaanP: '',
        bbP: 0,
        tbP: 0,
        lilaP: 0,
        hbP: '',
        namaL: '',
        nikL: '',
        usiaL: 0,
        pekerjaanL: '',
        riwayat: '',
        jamban: '',
        air: '',
        intervensi: '',
        kelola: '',
      },
      catin: [
        {
          catatan: 'Berisiko Tinggi',
          kunjungan: '2025-08-20',
          menikah: '2024-12-12',
          namaP: 'Siti',
          nikP: '123456789',
          usiaP: 22,
          pekerjaanP: 'Mahasiswa',
          bbP: 45,
          tbP: 155,
          lilaP: 24,
          hbP: '12',
          namaL: 'Budi',
          nikL: '987654321',
          usiaL: 25,
          pekerjaanL: 'Karyawan',
          riwayat: 'Hipertensi',
          jamban: 'Ya',
          air: 'Sumur',
          intervensi: '-',
          kelola: 'Edit/Delete',
        },
      ],
      headers: [
        { text: 'Catatan Beresiko', value: 'catatan' },
        { text: 'Tanggal Kunjungan', value: 'kunjungan' },
        { text: 'Tanggal Menikah', value: 'menikah' },
        { text: 'Nama Catin (P)', value: 'namaP' },
        { text: 'NIK Catin (P)', value: 'nikP' },
        { text: 'Usia Catin (P)', value: 'usiaP' },
        { text: 'Pekerjaan Catin (P)', value: 'pekerjaanP' },
        { text: 'BB (kg)', value: 'bbP' },
        { text: 'TB (cm)', value: 'tbP' },
        { text: 'LiLa (cm)', value: 'lilaP' },
        { text: 'Hb', value: 'hbP' },
        { text: 'Nama Pasangan', value: 'namaL' },
        { text: 'NIK Pasangan', value: 'nikL' },
        { text: 'Usia Pasangan', value: 'usiaL' },
        { text: 'Pekerjaan Pasangan', value: 'pekerjaanL' },
        { text: 'Riwayat Penyakit', value: 'riwayat' },
        { text: 'Jamban Sehat', value: 'jamban' },
        { text: 'Sumber Air Bersih', value: 'air' },
        { text: 'Intervensi', value: 'intervensi' },
        { text: 'Kelola', value: 'kelola' },
      ],
      // filter
      filter: {
        nikP: '',
      },
      advancedFilter: {
        namaP: '',
        namaL: '',
        intervensi: '',
        menikah: '',
        kunjungan: '',
      },
      appliedFilter: {}, // hasil filter simpan di sini
    }
  },
  computed: {
    background() {
      try {
        const config = JSON.parse(localStorage.getItem('siteConfig'))
        return config?.background || null
      } catch {
        return null
      }
    },
    filteredCatin() {
      return this.catin.filter((item) => {
        return (
          // NIK realtime
          (!this.filter.nikP || item.nikP.includes(this.filter.nikP)) &&
          // Advanced filter hanya aktif setelah "Cari"
          (!this.appliedFilter.namaP || item.namaP === this.appliedFilter.namaP) &&
          (!this.appliedFilter.namaL || item.namaL === this.appliedFilter.namaL) &&
          (!this.appliedFilter.intervensi || item.intervensi === this.appliedFilter.intervensi) &&
          (!this.appliedFilter.menikah || item.menikah === this.appliedFilter.menikah) &&
          (!this.appliedFilter.kunjungan || item.kunjungan === this.appliedFilter.kunjungan)
        )
      })
    },
    visibleHeaders() {
      return this.headers.filter((h) => this.visibleColumns.includes(h.value))
    },
  },
  methods: {
    closeModal(id) {
      const el = document.getElementById(id)
      if (el) {
        const instance = Modal.getOrCreateInstance(el)
        instance.hide()
      }

      // jaga-jaga kalau backdrop masih nyangkut
      setTimeout(() => {
        document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove())
        document.body.classList.remove('modal-open')
        document.body.style.removeProperty('overflow')
        document.body.style.removeProperty('padding-right')
      }, 300) // delay biar nunggu animasi fade
    },
    updateProgressBar(percent, row, total) {
      this.importProgress = percent
      this.currentRow = row
      this.totalRows = total

      const start = this.animatedProgress
      const end = percent
      const step = (end - start) / 10
      let i = 0
      const interval = setInterval(() => {
        this.animatedProgress = Math.min(end, Math.round(start + step * i))
        i++
        if (this.animatedProgress >= end) clearInterval(interval)
      }, 30)
    },
    toggleExpand() {
      this.isFilterOpen = !this.isFilterOpen
    },
    applyFilter() {
      // copy isi advancedFilter ke appliedFilter saat tombol Cari ditekan
      this.appliedFilter = { ...this.advancedFilter }
    },
    resetFilter() {
      this.filter.nikP = ''
      this.advancedFilter = {
        namaP: '',
        namaL: '',
        intervensi: '',
        menikah: '',
        kunjungan: '',
      }
      this.appliedFilter = {}
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    saveData() {
      this.closeModal('modalTambah')
      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0
      this.currentRow = 0
      this.totalRows = 1 // hanya 1 record, bisa disesuaikan kalau batch

      // simulasi progress bertahap
      let step = 0
      const interval = setInterval(() => {
        step += 10
        this.importProgress = Math.min(step, 100)
        this.animatedProgress = this.importProgress
        this.currentRow = Math.round((this.totalRows * this.importProgress) / 100)

        if (this.importProgress >= 100) {
          clearInterval(interval)

          // lanjut simpan data

          this.catin.push({ ...this.form })
          this.showAlert = true
          setTimeout(() => (this.showAlert = false), 3000)

          // reset form
          this.form = {
            catatan: '',
            kunjungan: '',
            menikah: '',
            namaP: '',
            nikP: '',
            usiaP: '',
            pekerjaanP: '',
            bbP: '',
            tbP: '',
            lilaP: '',
            hbP: '',
            namaL: '',
            nikL: '',
            usiaL: '',
            pekerjaanL: '',
            riwayat: '',
            jamban: '',
            air: '',
            intervensi: '',
            kelola: '',
          }

          this.$nextTick(() => {
            const el = document.getElementById('successModal')
            if (el) {
              const instance = Modal.getOrCreateInstance(el)
              instance.show()
            }
          })

          this.isLoadingImport = false
        }
      }, 150) // jeda antar progress
    },
    openImport(title) {
      this.importTitle = title
      const el = this.$refs.modalImport
      Modal.getOrCreateInstance(el).show()
    },
    handleImport() {
      this.closeModal('modalImport')

      const fileInput = this.$refs.csvFile
      if (!fileInput || !fileInput.files.length) return

      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0

      const file = fileInput.files[0]
      const reader = new FileReader()

      reader.onload = (e) => {
        const text = e.target.result
        const rows = text
          .split('\n')
          .map((r) => r.trim())
          .filter((r) => r)
        const headers = rows[0].split(',').map((h) => h.trim())
        const total = rows.length - 1
        this.totalRows = total

        rows.slice(1).forEach((row, idx) => {
          const values = row.split(',').map((v) => v.trim())
          const obj = {}
          headers.forEach((h, i) => {
            obj[h] = values[i] || ''
          })

          this.catin.push({
            catatan: obj.catatan || '',
            kunjungan: obj.kunjungan || '',
            menikah: obj.menikah || '',
            nikP: obj.nikP || '',
            namaP: obj.namaP || '',
            usiaP: obj.usiaP || '',
            pekerjaanP: obj.pekerjaanP || '',
            bbP: obj.bbP || 0,
            tbP: obj.tbP || 0,
            lilaP: obj.lilaP || 0,
            hbP: obj.hbP || '',
            nikL: obj.nikL || '',
            namaL: obj.namaL || '',
            usiaL: obj.usiaL || '',
            pekerjaanL: obj.pekerjaanL || '',
            riwayat: obj.riwayat || '',
            jamban: obj.jamban || '',
            air: obj.air || '',
            intervensi: obj.intervensi || '',
            kelola: obj.kelola || '',
          })

          const percent = Math.round(((idx + 1) / total) * 100)
          this.updateProgressBar(percent, idx + 1, total)
        })

        setTimeout(() => {
          this.isLoadingImport = false
          const el = document.getElementById('successModal')
          const instance = Modal.getOrCreateInstance(el)
          instance.show()
        }, 500)
      }

      reader.readAsText(file)
    },
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
