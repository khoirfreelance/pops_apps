<template>
  <div class="family-wrapper">
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
                  <h2 class="fw-bold mt-3 mb-0 text-white">Data Keluarga</h2>
                  <small class="text-white">
                    List daftar keluarga yang terdaftar di dalam posyandu</small
                  >
                </div>
                <nav aria-label="breadcrumb" class="mt-auto mb-2">
                  <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                      <router-link to="/admin" class="text-decoration-none text-white-50">
                        Beranda
                      </router-link>
                    </li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Keluarga</li>
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
              <div class="col-md-6">
                <label for="nik" class="form-label">NIK</label>
                <input
                  type="text"
                  v-model="filter.nik"
                  id="nik"
                  class="form-control"
                  placeholder="Cari berdasarkan NIK"
                />
              </div>
              <div class="col-md-6">
                <label for="no_kk" class="form-label">No. Kartu Keluarga</label>
                <input
                  type="text"
                  v-model="filter.no_kk"
                  id="no_kk"
                  class="form-control"
                  placeholder="Cari berdasarkan No. Kartu Keluarga"
                />
              </div>

              <!-- Expandable section -->
              <div v-if="isFilterOpen" class="row g-3 align-items-end mt-2">
                <!-- Nama -->
                <div class="col-md-6">
                  <label for="kepala" class="form-label">Nama Kepala Keluarga</label>
                  <input
                    type="text"
                    v-model="advancedFilter.kepala"
                    id="kepala"
                    class="form-control"
                  />
                </div>

                <!-- RT -->
                <div class="col-md-3">
                  <label for="rt" class="form-label">RT</label>
                  <input type="number" v-model="advancedFilter.rt" id="rt" class="form-control" />
                </div>

                <!-- RW -->
                <div class="col-md-3">
                  <label for="rw" class="form-label">RW</label>
                  <input type="number" v-model="advancedFilter.rw" id="rw" class="form-control" />
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
              <i class="bi bi-filetype-csv"></i> Import Data Keluarga
            </button>
          </div>

          <!-- Table -->
          <div class="container-fluid">
            <div class="card modern-card mt-4">
              <div class="card-body">
                <div class="table-responsive">
                  <EasyDataTable
                    :headers="headers"
                    :items="filteredFamily"
                    :loading="isLoadingImport"
                    buttons-pagination
                    :rows-per-page="5"
                    table-class="table-modern"
                    theme-color="var(--bs-primary)"
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div
        class="modal-content shadow-lg border-0 rounded-4"
        :style="{
          backgroundImage: background ? `url(${background})` : 'none',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          backgroundAttachment: 'fixed',
        }"
      >
        <!-- Header -->
        <div class="modal-header text-primary bg-light border-0 rounded-top-4">
          <h5 class="modal-title fw-bold text-primary">Tambah Data Keluarga</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <form class="row g-4">
            <!-- No KK -->
            <div class="col-md-12">
              <label class="form-label small fw-semibold text-secondary">No KK</label>
              <input
                type="text"
                class="form-control shadow-sm"
                v-model="form.no_kk"
                @input="form.no_kk = form.no_kk.replace(/\D/g, '')"
                maxlength="16"
                @blur="checkNoKK"
              />
            </div>

            <!-- Alamat -->
            <div class="col-md-12">
              <label class="form-label small fw-semibold text-secondary">Alamat</label>
              <textarea class="form-control shadow-sm" rows="2" v-model="form.alamat" :readonly="isKKChecked"></textarea>
            </div>

            <!-- Provinsi, Kecamatan & Kota -->
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">Provinsi</label>
              <div v-if="form.provinsi === '__new__'">
                <input
                  type="text"
                  class="form-control shadow-sm"
                  v-model="form.provinsi_new"
                  placeholder="Tambah provinsi baru"
                />
              </div>
              <div v-else>
                <select class="form-select shadow-sm" v-model="form.provinsi" @change="loadKota" :readonly="isKKChecked">
                  <option value="">Pilih</option>
                  <option v-for="item in provinsiList" :key="item.nama" :value="item.nama">
                    {{ item.nama }}
                  </option>
                  <option value="__new__">+ Tambah baru</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">Kota</label>
              <div v-if="form.kota === '__new__'">
                <input
                  type="text"
                  class="form-control shadow-sm"
                  v-model="form.kota_new"
                  placeholder="Tambah kota baru"
                />
              </div>
              <div v-else>
                <select class="form-select shadow-sm" v-model="form.kota" @change="loadKecamatan" :readonly="isKKChecked">
                  <option value="">Pilih</option>
                  <option v-for="item in kotaList" :key="item.nama" :value="item.nama">
                    {{ item.nama }}
                  </option>
                  <option value="__new__">+ Tambah baru</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">Kecamatan</label>
              <div v-if="form.kecamatan === '__new__'">
                <input
                  type="text"
                  class="form-control shadow-sm"
                  v-model="form.kecamatan_new"
                  placeholder="Tambah kecamatan baru"
                />
              </div>
              <div v-else>
                <select class="form-select shadow-sm" v-model="form.kecamatan" @change="loadKelurahan">
                  <option value="">Pilih</option>
                  <option v-for="item in kecamatanList" :key="item.nama" :value="item.nama">
                    {{ item.nama }}
                  </option>
                  <option value="__new__">+ Tambah baru</option>
                </select>
              </div>
            </div>

            <!-- RT, RW , Kelurahan -->
            <div class="col-md-4">
               <label class="form-label small fw-semibold text-secondary">Kelurahan</label>
              <div v-if="form.kelurahan === '__new__'">
                <input
                  type="text"
                  class="form-control shadow-sm"
                  v-model="form.kelurahan_new"
                  placeholder="Tambah kelurahan baru"
                />
              </div>
              <div v-else>
                <select class="form-select shadow-sm" v-model="form.kelurahan" :readonly="isKKChecked">
                  <option value="">Pilih</option>
                  <option v-for="item in kelurahanList" :key="item.nama" :value="item.nama">
                    {{ item.nama }}
                  </option>
                  <option value="__new__">+ Tambah baru</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">RT</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form.rt" :readonly="isKKChecked"/>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold text-secondary">RW</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form.rw" :readonly="isKKChecked"/>
            </div>

            <div class="col-md-12">
              <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
              <input type="text" class="form-control shadow-sm" v-model="form.nama" />
            </div>

            <!-- NIK -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">NIK</label>
              <input
                type="text"
                class="form-control shadow-sm"
                v-model="form.nik"
                maxlength="16"
                @input="form.nik = form.nik.replace(/\D/g, '')"
              />
            </div>

            <!-- Jenis Kelamin -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Jenis Kelamin</label>
              <select class="form-select shadow-sm" v-model="form.gender">
                <option value="">L/P</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>

            <!-- Tempat Lahir -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Tempat Lahir</label>
              <input
                type="text"
                class="form-control shadow-sm"
                v-model="form.tmpt_lahir"
                maxlength="16"
                @input="form.tmpt_lahir = form.tmpt_lahir"
              />
            </div>

            <!-- Tanggal lahir-->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Tanggal Lahir</label>
              <input type="date" class="form-control shadow-sm" v-model="form.tgl_lahir" />
            </div>

            <!-- Pendidikan -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Pendidikan</label>
              <select class="form-select shadow-sm" v-model="form.pendidikan">
                <option value="">Pendidikan</option>
                <option value="TK">TK</option>
                <option value="SD">SD</option>
                <option value="SMP">SMP</option>
                <option value="SMA/SMK">SMA/sederajat</option>
                <option value="SARJANA">Sarjana</option>
                <option value="MAGISTER">Magister</option>
                <option value="DOKTORAL">Doktoral</option>
                <option value="PROFESSOR">Professor</option>
              </select>
            </div>

            <!-- Pekerjaan -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Pekerjaan</label>
              <input type="text" class="form-control shadow-sm" v-model="form.pekerjaan" />
            </div>

            <!-- Status Hubungan dalam Keluarga -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Status Hubungan</label>
              <select class="form-select shadow-sm" v-model="form.status_hubungan">
                <option value="">Pilih</option>
                <option value="Kepala Keluarga">Kepala Keluarga</option>
                <option value="Istri">Istri</option>
                <option value="Anak">Anak</option>
                <option value="Famili Lain">Famili Lain</option>
              </select>
            </div>

            <!-- Agama -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Agama</label>
              <select class="form-select shadow-sm" v-model="form.agama">
                <option value="">Pilih</option>
                <option value="Islam">Islam</option>
                <option value="Kristen">Kristen</option>
                <option value="Katolik">Katolik</option>
                <option value="Hindu">Hindu</option>
                <option value="Budha">Budha</option>
                <option value="Konghucu">Konghucu</option>
              </select>
            </div>

            <!-- Status Perkawinan -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Status Perkawinan</label>
              <select class="form-select shadow-sm" v-model="form.status_perkawinan">
                <option value="">Pilih</option>
                <option value="Belum Kawin">Belum Kawin</option>
                <option value="Kawin">Kawin</option>
                <option value="Cerai Hidup">Cerai Hidup</option>
                <option value="Cerai Mati">Cerai Mati</option>
              </select>
            </div>

            <!-- Kewarganegaraan -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Kewarganegaraan</label>
              <input type="text" class="form-control shadow-sm" v-model="form.kewarganegaraan" placeholder="Contoh: WNI" />
            </div>
          </form>
        </div>

        <!-- Footer -->
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
          <h5 class="modal-title">Import File Ibu Hamil</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning p-2">
            <ul>
              <li>Import data untuk kunjungan kehamilan oleh pendampingan TPK</li>
              <li>Pastikan data yang diimport, berformat csv</li>
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
import axios from 'axios'

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Family',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, EasyDataTable },
  data() {
    return {
      isKKChecked: false,
      isCollapsed: false,
      isFilterOpen: false,
      showAlert: false,
      isLoadingImport: false,
      importProgress: 0,
      animatedProgress: 0,
      currentRow: 0,
      totalRows: 1,
      provinsiList: [],
      kotaList: [],
      kecamatanList: [],
      kelurahanList: [],
      form: {
        nik: '',
        no_kk: '',
        nama: '',
        alamat: '',
        rt: '',
        rw: '',
        kelurahan: '',
        kecamatan: '',
        kota: '',
        provinsi: '',
        kelurahan_new: '',
        kecamatan_new: '',
        kota_new: '',
        provinsi_new: '',
        tmpt_lahir: '',
        gender: '',
        tgl_lahir: '',
        pendidikan: '',
        pekerjaan: '',
        status_hubungan: '',
        agama: '',
        status_perkawinan: '',
        kewarganegaraan: ''
      },
      family: [],
      headers: [
        { text: 'No KK', value: 'no_kk' },
        { text: 'NIK', value: 'nik_kepala' },
        { text: 'Kepala Keluarga', value: 'nama_kepala' },
        { text: 'RT', value: 'rt' },
        { text: 'RW', value: 'rw' },
        { text: 'Tanggal Lahir', value: 'tgl_lahir' },
        { text: 'Pendidikan', value: 'pendidikan' },
      ],
      filter: {
        nik: '',
        no_kk: '',
      },
      advancedFilter: {
        kepala: '',
        rt: '',
        rw: '',
      },
      appliedFilter: {},
    }
  },
  computed: {
    filteredFamily() {
      return this.family.filter((item) => {
        return (
          (!this.filter.nik || item.nik.includes(this.filter.nik)) &&
          (!this.filter.no_kk || item.no_kk.includes(this.filter.no_kk)) &&
          (!this.appliedFilter.kepala ||
            item.kepala.toLowerCase().includes(this.appliedFilter.kepala.toLowerCase())) &&
          (!this.appliedFilter.rt || Number(item.rt) === Number(this.appliedFilter.rt)) &&
          (!this.appliedFilter.rw || Number(item.rw) === Number(this.appliedFilter.rw))
        )
      })
    },
  },
  methods: {
    async checkNoKK() {
      if (!this.form.no_kk) return;

      try {
        const res = await axios.get("http://localhost:8000/api/family/check", {
          params: {  // ⬅️ pake params biar masuk ke query string
            no_kk: this.form.no_kk,
          },
        });

        if (res.data.exists) {
          const data = res.data.keluarga;

          // isi form
          this.form.alamat = data.alamat;
          this.form.rt = data.rt;
          this.form.rw = data.rw;
          this.form.provinsi = data.provinsi;
          this.form.kota = data.kota;
          this.form.kecamatan = data.kecamatan;
          this.form.kelurahan = data.kelurahan;

          // pastikan dropdown punya value-nya
          if (data.kota && !this.kotaList.some(k => k.nama === data.kota)) {
            this.kotaList.push({ nama: data.kota });
          }
          if (data.kecamatan && !this.kecamatanList.some(k => k.nama === data.kecamatan)) {
            this.kecamatanList.push({ nama: data.kecamatan });
          }
          if (data.kelurahan && !this.kelurahanList.some(k => k.nama === data.kelurahan)) {
            this.kelurahanList.push({ nama: data.kelurahan });
          }

          // set flag supaya field jadi readonly
          this.isKKChecked = true;
        } else {
          this.isKKChecked = false;
        }
      } catch (e) {
        console.error("Error check KK:", e);
        this.isKKChecked = false;
      }
    },
    async loadProvinsi() {
      try {
        const res = await axios.get("http://localhost:8000/api/region/provinsi");
        console.log("Provinsi API response:", res.data); // debug

        // isi list dari API
        this.provinsiList = res.data;

      } catch (err) {
        console.error("Error load provinsi:", err);
      }
    },
    async loadKota() {
      if (this.form.provinsi && this.form.provinsi !== "__new__") {
        const res = await axios.get("http://localhost:8000/api/region/kota", {
          params: { provinsi: this.form.provinsi }
        });
        this.kotaList = res.data;
        this.kecamatanList = [];
        this.kelurahanList = [];
        this.form.kota = "";
        this.form.kecamatan = "";
        this.form.kelurahan = "";
      }
    },
    async loadKecamatan() {
      if (this.form.kota && this.form.kota !== "__new__") {
        const res = await axios.get("http://localhost:8000/api/region/kecamatan", {
          params: { provinsi: this.form.provinsi, kota: this.form.kota }
        });
        this.kecamatanList = res.data;
        this.kelurahanList = [];
        this.form.kecamatan = "";
        this.form.kelurahan = "";
      }
    },
    async loadKelurahan() {
      if (this.form.kecamatan && this.form.kecamatan !== "__new__") {
        const res = await axios.get("http://localhost:8000/api/region/kelurahan", {
          params: {
            provinsi: this.form.provinsi,
            kota: this.form.kota,
            kecamatan: this.form.kecamatan
          }
        });
        this.kelurahanList = res.data;
        this.form.kelurahan = "";
      }
    },
    async loadFamily() {
      try {
        const res = await axios.get('http://localhost:8000/api/family')
        this.family = res.data
        console.log(this.family);
      } catch (e) {
        console.error('Gagal ambil data:', e)
      }
    },
    closeModal(id) {
      const el = document.getElementById(id)
      if (el) {
        const instance = Modal.getOrCreateInstance(el)
        instance.hide()
      }
      setTimeout(() => {
        document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove())
        document.body.classList.remove('modal-open')
        document.body.style.removeProperty('overflow')
        document.body.style.removeProperty('padding-right')
      }, 300)
    },
    async saveData() {

      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0

      try {
        this.form.provinsi = this.form.provinsi === "__new__" ? this.form.provinsi_new : this.form.provinsi;
        this.form.kota = this.form.kota === "__new__" ? this.form.kota_new : this.form.kota;
        this.form.kecamatan = this.form.kecamatan === "__new__" ? this.form.kecamatan_new : this.form.kecamatan;
        this.form.kelurahan= this.form.kelurahan === "__new__" ? this.form.kelurahan_new : this.form.kelurahan;

        // simpan ke backend
        await axios.post('http://localhost:8000/api/family', this.form)

        // refresh table
        await this.loadFamily()

        // reset form
        this.form = {
          nik: '',
          no_kk: '',
          kepala: '',
          alamat: '',
          rt: '',
          rw: '',
          kelurahan: '',
          kecamatan: '',
          kota: '',
          tmpt_lahir: '',
          gender: '',
          tgl_lahir: '',
          pendidikan: '',
          pekerjaan: '',
        }

        this.closeModal('modalTambah')
        this.showAlert = true
        setTimeout(() => (this.showAlert = false), 3000)
      } catch (e) {
        console.error('Gagal simpan data:', e)
      } finally {
        this.isLoadingImport = false
      }
    },
    applyFilter() {
      this.appliedFilter = { ...this.advancedFilter }
    },
    resetFilter() {
      this.filter = { nik: '', no_kk: '' }
      this.advancedFilter = { kepala: '', rt: '', rw: '' }
      this.appliedFilter = {}
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
  },
  mounted() {
    this.loadFamily()
    this.loadProvinsi()
  },
}
</script>

<style scoped>
.family-wrapper {
  /* tinggi navbar bootstrap default */
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  min-height: 100vh;
}
/* Gradient Banner */
.family-banner {
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
