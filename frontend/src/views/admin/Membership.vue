<template>
  <div class="membership-wrapper">
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

          <div :class="isDetail?'collapse':''">
            <!-- Welcome Card -->
            <div class="card welcome-card shadow-sm mb-4 border-0">
              <div
                class="card-body d-flex flex-column flex-md-row align-items-start py-0 justify-content-between"
              >
                <!-- Kiri: Teks Welcome -->
                <div class="text-start">
                  <div class="my-3">
                    <h2 class="fw-bold mt-3 mb-0 text-white">Nomor Registrasi TPK</h2>
                    <small class="text-white">
                      Nomor registrasi anggota TPK terdaftar beserta jumlah anggota per No TPK
                    </small>
                  </div>
                  <nav aria-label="breadcrumb" class="mt-auto mb-2">
                    <ol class="breadcrumb mb-0">
                      <li class="breadcrumb-item">
                        <router-link to="/admin" class="text-decoration-none text-white-50">
                          Beranda
                        </router-link>
                      </li>
                      <li class="breadcrumb-item active text-white" aria-current="page">TPK</li>
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
                <!-- No TPK -->
                <div class="col-md-12">
                  <label for="no_tpk" class="form-label">No TPK</label>
                  <input
                    type="text"
                    v-model="filter.no_tpk"
                    id="no_tpk"
                    class="form-control"
                    placeholder="Cari berdasarkan No TPK"
                  />
                </div>
              </form>
            </div>

            <!-- Form -->
            <div class="container-fluid mt-4">
              <!-- Expand/Collapse Button -->
              <div class="text-end mb-3">
                <button
                  type="button"
                  class="btn btn-primary mx-3"
                  @click="toggleExpandForm"
                >
                  <i :class="isFormOpen ? 'bi bi-dash-square' : 'bi bi-plus-square'"></i>
                  {{ isFormOpen ? 'Tutup Form' : 'Tambah Data' }}
                </button>
              </div>

              <!-- Collapsible Form -->
              <div v-if="isFormOpen" id="formData" class="card shadow-sm">
                <div class="card-body">
                  <form class="row g-4">
                    <!-- No TPK -->
                    <div :class="modalMode === 'add' ? 'col-md-12' : 'col-md-6'">
                      <label class="form-label small fw-semibold text-secondary">No. TPK</label>
                      <template v-if="modalMode === '__new__'">
                        <input
                          type="number"
                          min="0"
                          class="form-control shadow-sm"
                          v-model="form.no_tpk"
                          placeholder="Tambah No. TPK baru"
                        />
                      </template>
                      <template v-else>
                        <select
                          class="form-select shadow-sm"
                          v-model="form.no_tpk"
                          @change="loadTPK"
                        >
                          <option value="">Pilih</option>
                          <option v-for="item in tpkList" :key="item.no_tpk" :value="item.no_tpk">
                            {{ item.no_tpk }}
                          </option>
                          <option value="__new__">+ Tambah baru</option>
                        </select>
                      </template>
                    </div>

                    <!-- User -->
                    <div v-if="modalMode === 'assign'" class="col-md-6">
                      <label class="form-label small fw-semibold text-secondary">Kader</label>
                      <select
                          class="form-select shadow-sm"
                          v-model="form.nik"
                          @change="loadUser"
                          :style="modalMode === 'assign' ? 'pointer-events: none; background-color:#e9ecef;' : ''"
                        >
                          <option value="">Pilih</option>
                          <option v-for="item in userList" :key="item.nik" :value="item.nik">
                            {{ item.nik }} - {{ item.name }}
                          </option>
                        </select>
                    </div>

                    <!-- Provinsi -->
                    <div class="col-md-3">
                      <label class="form-label small fw-semibold text-secondary">Provinsi</label>
                      <template v-if="form.provinsi === '__new__'">
                        <input
                          type="text"
                          class="form-control shadow-sm"
                          v-model="form.provinsi_new"
                          placeholder="Tambah provinsi baru"
                        />
                      </template>
                      <template v-else>
                        <select
                          class="form-select shadow-sm"
                          v-model="form.provinsi"
                          @change="loadKota"
                          :style="modalMode === 'assign' ? 'pointer-events: none; background-color:#e9ecef;' : ''"
                        >
                          <option value="">Pilih</option>
                          <option v-for="item in provinsiList" :key="item.nama" :value="item.nama">
                            {{ item.nama }}
                          </option>
                          <option value="__new__">+ Tambah baru</option>
                        </select>
                      </template>
                    </div>

                    <!-- Kota -->
                    <div class="col-md-3">
                      <label class="form-label small fw-semibold text-secondary">Kota</label>
                      <template v-if="form.kota === '__new__'">
                        <input
                          type="text"
                          class="form-control shadow-sm"
                          v-model="form.kota_new"
                          placeholder="Tambah kota baru"
                        />
                      </template>
                      <template v-else>
                        <select
                          class="form-select shadow-sm"
                          v-model="form.kota"
                          @change="loadKecamatan"
                          :style="modalMode === 'assign' ? 'pointer-events: none; background-color:#e9ecef;' : ''"
                        >
                          <option value="">Pilih</option>
                          <option v-for="item in kotaList" :key="item.nama" :value="item.nama">
                            {{ item.nama }}
                          </option>
                          <option value="__new__">+ Tambah baru</option>
                        </select>
                      </template>
                    </div>

                    <!-- Kecamatan -->
                    <div class="col-md-3">
                      <label class="form-label small fw-semibold text-secondary">Kecamatan</label>
                      <template v-if="form.kecamatan === '__new__'">
                        <input
                          type="text"
                          class="form-control shadow-sm"
                          v-model="form.kecamatan_new"
                          placeholder="Tambah kecamatan baru"
                        />
                      </template>
                      <template v-else>
                        <select
                          class="form-select shadow-sm"
                          v-model="form.kecamatan"
                          @change="loadKelurahan"
                          :style="modalMode === 'assign' ? 'pointer-events: none; background-color:#e9ecef;' : ''"
                        >
                          <option value="">Pilih</option>
                          <option v-for="item in kecamatanList" :key="item.nama" :value="item.nama">
                            {{ item.nama }}
                          </option>
                          <option value="__new__">+ Tambah baru</option>
                        </select>
                      </template>
                    </div>

                    <!-- Kelurahan -->
                    <div class="col-md-3">
                      <label class="form-label small fw-semibold text-secondary">Kelurahan</label>
                      <template v-if="form.kelurahan === '__new__'">
                        <input
                          type="text"
                          class="form-control shadow-sm"
                          v-model="form.kelurahan_new"
                          placeholder="Tambah kelurahan baru"
                        />
                      </template>
                      <template v-else>
                        <select
                          class="form-select shadow-sm"
                          v-model="form.kelurahan"
                          :style="modalMode === 'assign' ? 'pointer-events: none; background-color:#e9ecef;' : ''"
                        >
                          <option value="">Pilih</option>
                          <option v-for="item in kelurahanList" :key="item.nama" :value="item.nama">
                            {{ item.nama }}
                          </option>
                          <option value="__new__">+ Tambah baru</option>
                        </select>
                      </template>
                    </div>
                  </form>
                </div>

                <!-- Actions -->
                <div class="card-footer bg-white d-flex justify-content-between">
                  <button
                    class="btn btn-light border rounded-pill px-4"
                    @click="resetForm"
                  >
                    <i class="bi bi-x-circle me-2"></i> Batal
                  </button>
                  <button
                    class="btn btn-success rounded-pill px-4"
                    @click="modalMode === 'add' ? saveData() : assignData()"
                  >
                    <i class="bi bi-save me-2"></i> {{ modalMode === 'add' ? 'Simpan' : 'Assign' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Table -->
            <div class="container-fluid">
              <div class="card modern-card mt-4">
                <div class="card-body">
                  <div class="table-responsive">

                    <EasyDataTable
                      :headers="headers"
                      :items="filteredMember"
                    >
                    <template #item-action="{ id,no_tpk }">
                      <button
                        class="btn btn-primary m-2"
                        @click="detail(no_tpk)"
                        style="font-size: small;"
                      >
                        <i class="fa fa-eye"></i>
                      </button>
                      <button
                        class="btn btn-secondary m-2"
                        @click="assign(id)"
                        style="font-size: small;"
                      >
                        <i class="fa fa-user-plus"></i>
                      </button>
                    </template>
                  </EasyDataTable>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div :class="isDetail?'':'collapse'">
            <!-- Welcome Card -->
            <div class="card welcome-card shadow-sm mb-4 border-0">
              <div
                class="card-body d-flex flex-column flex-md-row align-items-start py-0 justify-content-between"
              >
                <!-- Kiri: Teks Welcome -->
                <div class="text-start">
                  <div class="my-3">
                    <h2 class="fw-bold mt-3 mb-0 text-white">Detail Data TPK</h2>
                    <small class="text-white">
                      Detail data keluarga yang terdaftar di dalam posyandu
                    </small>
                  </div>
                  <nav aria-label="breadcrumb" class="mt-auto mb-2">
                    <ol class="breadcrumb mb-0">
                      <li class="breadcrumb-item">
                        <router-link to="/admin" class="text-decoration-none text-white-50">
                          Beranda
                        </router-link>
                      </li>
                      <li class="breadcrumb-item">
                        <span class="text-decoration-none text-white-50">TPK</span>
                      </li>
                      <li class="breadcrumb-item active text-white" aria-current="page">Detail</li>
                    </ol>
                  </nav>
                </div>

                <!-- Kanan: Gambar -->
                <div class="mt-3 mt-md-0">
                  <img src="/src/assets/admin.png" alt="Welcome" class="img-fluid welcome-img" />
                </div>
              </div>
            </div>

            <!-- Navigation Tab -->
            <nav class="filter-wrapper bg-light rounded shadow-sm p-3 mt-3 container-fluid">
              <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-anggota-tab" data-bs-toggle="tab" data-bs-target="#nav-anggota" type="button" role="tab" aria-controls="nav-anggota" aria-selected="true">Anggota TPK</button>
                <button class="nav-link" id="nav-keluarga-tab" data-bs-toggle="tab" data-bs-target="#nav-keluarga" type="button" role="tab" aria-controls="nav-keluarga" aria-selected="false">Keluarga Dampingan</button>
                <button class="nav-link" id="nav-anak-tab" data-bs-toggle="tab" data-bs-target="#nav-anak" type="button" role="tab" aria-controls="nav-anak" aria-selected="false">Pendampingan Anak</button>
                <button class="nav-link" id="nav-bumil-tab" data-bs-toggle="tab" data-bs-target="#nav-bumil" type="button" role="tab" aria-controls="nav-bumil" aria-selected="false">Pendampingan Bumil</button>
                <button class="nav-link" id="nav-catin-tab" data-bs-toggle="tab" data-bs-target="#nav-catin" type="button" role="tab" aria-controls="nav-catin" aria-selected="false">Pendampingan Catin</button>
              </div>
            </nav>

            <div class="container-fluid mt-4">
              <div class="tab-content" id="nav-tabContent">
                <!-- Anggota -->
                <div class="tab-pane fade show active" id="nav-anggota" role="tabpanel" aria-labelledby="nav-anggota-tab">
                  <div class="card modern-card mt-4">
                    <div class="card-body">
                      <div class="table-responsive">
                        <EasyDataTable
                          :headers="headers_tpk"
                          :items="tpkMember"
                          table-class="text-center"
                        />
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Keluarga -->
                <div class="tab-pane fade" id="nav-keluarga" role="tabpanel" aria-labelledby="nav-keluarga-tab">
                  <div class="card bg-light border-0 shadow-sm p-2">

                    <!-- Form -->
                    <div class="container-fluid">
                      <!-- Expand/Collapse Button -->
                      <div class="text-end mb-3">
                        <button
                          type="button"
                          class="btn btn-primary mx-3"
                          @click="toggleExpandForm"
                        >
                          <i :class="isFormOpen ? 'bi bi-dash-square' : 'bi bi-plus-square'"></i>
                          {{ isFormOpen ? 'Tutup Form' : 'Tambah Data' }}
                        </button>
                      </div>

                      <!-- Collapsible Form -->
                      <div v-if="isFormOpen" id="formData" class="card shadow-sm">
                        <div class="card-body">
                          <form class="row g-4">
                            <div class="col-md-6">
                              <!-- Keluarga -->
                              <div class="col-md-12">
                                <label class="form-label small fw-semibold text-secondary">No. KK</label>
                                <select
                                    class="form-select shadow-sm"
                                    v-model="form_pendampingan.no_kk"
                                    @change="loadFamily"
                                  >
                                    <option value="">Pilih</option>
                                    <option v-for="item in family" :key="item.no_kk" :value="item.no_kk">
                                      {{ item.no_kk }} - {{ item.nama_kepala }}
                                    </option>
                                  </select>
                              </div>

                              <!-- Jadwal -->
                              <div class="col-md-12">
                                <label class="form-label small fw-semibold text-secondary">Jadwal Pendampingan</label>
                                <select class="form-select shadow-sm" v-model="form_pendampingan.pendampingan">
                                  <option value="">Pilih</option>
                                  <option value="minggu-1">Minggu ke 1</option>
                                  <option value="minggu-2">Minggu ke 2</option>
                                  <option value="minggu-3">Minggu ke 3</option>
                                  <option value="minggu-4">Minggu ke 4</option>
                                </select>
                              </div>
                            </div>

                            <!-- Sasaran -->
                            <div class="col-md-6">
                              <label class="form-label small fw-semibold text-secondary">Sasaran Pendampingan</label>
                              <textarea name="sasaran" id="sasaran" class="form-control" rows="4" v-model="form_pendampingan.sasaran"></textarea>
                            </div>
                          </form>
                        </div>

                        <!-- Actions -->
                        <div class="card-footer bg-white d-flex justify-content-between">
                          <button
                            class="btn btn-light border rounded-pill px-4"
                            @click="resetForm"
                          >
                            <i class="bi bi-x-circle me-2"></i> Batal
                          </button>
                          <button
                            class="btn btn-success rounded-pill px-4"
                            @click="modalMode === 'add' ? saveData() : assignData()"
                          >
                            <i class="bi bi-save me-2"></i> {{ modalMode === 'add' ? 'Simpan' : 'Assign' }}
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- Table -->
                    <div class="container-fluid">
                      <div class="card modern-card mt-4">
                        <div class="card-body">
                          <div class="table-responsive">
                            <EasyDataTable
                              :headers="headers_family"
                              :items="family"
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Anak -->
                <div class="tab-pane fade" id="nav-anak" role="tabpanel" aria-labelledby="nav-anak-tab">
                  <div class="card bg-light border-0 shadow-sm p-2">
                    <!-- Table -->
                    <div class="container-fluid">
                      <div class="card modern-card mt-4">
                        <div class="card-body">
                          <div class="table-responsive">
                            <EasyDataTable
                              :headers="headers_children"
                              :items="chilList"
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Bumil -->
                <div class="tab-pane fade" id="nav-bumil" role="tabpanel" aria-labelledby="nav-bumil-tab">
                  <div class="card bg-light border-0 shadow-sm p-2">
                    <!-- Table -->
                    <div class="container-fluid">
                      <div class="card modern-card mt-4">
                        <div class="card-body">
                          <div class="table-responsive">
                            <EasyDataTable
                              :headers="headers_pregnancy"
                              :items="pregnancy"
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Catin -->
                <div class="tab-pane fade" id="nav-catin" role="tabpanel" aria-labelledby="nav-catin-tab">
                  <div class="card bg-light border-0 shadow-sm p-2">
                    <!-- Table -->
                    <div class="container-fluid">
                      <div class="card modern-card mt-4">
                        <div class="card-body">
                          <div class="table-responsive">
                            <EasyDataTable
                              :headers="headers_bride"
                              :items="brides"
                            />
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
  name: 'membership',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, EasyDataTable },
  data() {
    return {
      isDetail:false,
      isFormOpen: false,
      showAlert: false,
      isLoadingImport: false,
      importProgress: 0,
      animatedProgress: 0,
      currentRow: 0,
      totalRows: 1,
      tpkList:[],
      userList:[],
      provinsiList: [],
      kotaList: [],
      kecamatanList: [],
      kelurahanList: [],
      //familyList:[],
      family:[],
      form: {
        nik:'',
        no_tpk: '',
        kelurahan: '',
        kecamatan: '',
        kota: '',
        provinsi: '',
        kelurahan_new: '',
        kecamatan_new: '',
        kota_new: '',
        provinsi_new: '',
        no_tpk_new: '',
      },
      form_pendampingan: {
        id:'',
        no_kk: '',
        pendampingan:'',
        sasaran:'',
      },
      member: [],
      pendampingan:[],
      headers: [
        { text: 'No TPK', value: 'no_tpk' },
        { text: 'Nama', value: 'nama' },
        { text: 'Action', value: 'action' },
      ],
      tpkMember:[],
      headers_tpk: [
        { text: 'Nama', value: 'nama', align: "center"},
        { text: 'Jabatan', value: 'role', align: "center"},
        { text: 'No. Telepon', value: 'phone', align: "center"},
      ],
      headers_family: [
        { text: 'No. KK', value: 'no_kk' },
        { text: 'Kepala Keluarga', value: 'nama_kepala' },
        { text: 'RT', value: 'rt' },
        { text: 'RW', value: 'rw' },
        { text: 'Pendampingan', value: 'pendampingan' },
        { text: 'Sasaran', value: 'sasaran' },
      ],
      //dummy anak
      chilList:[
        {
          tgl_pendampingan:'2025-10-04',
          nama:'Agustian Pratama',
          kepala:'1871031205950003 - Monkey D. Dragon',
          petugas:'Super Admin',
          bb:'13',
          tb:'94',
          lila:'12',
          asi:true,
          imunisasi:true,
          posyandu:true,
        },
      ],
      headers_children: [
        { text: 'Tanggal', value: 'tgl_pendampingan' },
        { text: 'Nama', value: 'nama' },
        { text: 'Ayah', value: 'kepala' },
        { text: 'Petugas', value: 'petugas' },
        { text: 'BB', value: 'bb' },
        { text: 'TB', value: 'tb' },
        { text: 'Lila', value: 'lila' },
        { text: 'Asi Eksklusif', value: 'asi' },
        { text: 'Imunisasi Dasar', value: 'imunisasi' },
        { text: 'Rutin Posyandu', value: 'posyandu' },
      ],
      //dummy bumil
      pregnancy: [
        {
          kunjungan: '2025-08-10',
          no_kk: '3326167001090001 - Dono Pradana Putra',
          pic:'Super Admin',
          phone_pic:'082198435667',
          nama: 'Nur Dini Waini',
          kehamilan_ke: '3',
          kunjungan_ke:'3',
          usia_kehamilan: '28',
          bb:'50',
          tb:'150',
          lila:'12',
          hb:'',
          riwayat:'-',
          anemia: '-',
          kek: '-',
        },
      ],
      headers_pregnancy: [
        { text: 'Kunjungan Terakhir', value: 'kunjungan' },
        { text: 'No. KK', value: 'no_kk' },
        { text: 'PIC', value: 'pic' },
        { text: 'No. Telepon PIC', value: 'phone_pic' },
        { text: 'Nama', value: 'nama' },
        { text: 'Kehamilan Ke', value: 'kehamilan_ke' },
        { text: 'Kunjungan ke', value: 'kunjungan_ke' },
        { text: 'Usia Kehamilan', value: 'usia_kehamilan' },
        { text: 'BB', value: 'bb' },
        { text: 'TB', value: 'tb' },
        { text: 'Lila', value: 'lila' },
        { text: 'Hb', value: 'hb' },
        { text: 'Riwayat Penyakit', value: 'riwayat' },
        { text: 'Anemia', value: 'anemia' },
        { text: 'Kek', value: 'kek' },

      ],
      // dummy brides data
      brides: [
        {
          //catatan: 'Berisiko Tinggi',
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
        },
      ],
      headers_bride: [
        { text: 'Tanggal', value: 'kunjungan' },
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
        //{ text: 'Catatan Beresiko', value: 'catatan' },
      ],
      // filter
      filter: {
        no_tpk: '',
      },
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
    filteredMember() {
      return this.member.filter((item) => {
        return (
          // NIK realtime
          !this.filter.no_tpk || item.no_tpk.includes(this.filter.no_tpk)
        )
      })
    },
  },
  methods: {
    openTambah() {
      this.modalMode = "add"
      this.form = {} // reset form
      this.isFormOpen = true
    },
    async assign(id) {
      this.modalMode = "assign";
      this.isFormOpen = true
      try {
        const res = await axios.get(`http://localhost:8000/api/cadre/${id}`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        const data = res.data;

        // mapping ke form sesuai struktur
        this.form = {
          id: data.id,
          nik: data.nik,
          no_tpk: data.no_tpk,
          kelurahan: data.kelurahan,
          kecamatan: data.kecamatan,
          kota: data.kota,
          provinsi: data.provinsi,
        };

        // pastikan listnya ke-load dulu sebelum set value
        await this.loadKota()
        this.form.kota = data.kota

        await this.loadKecamatan()
        this.form.kecamatan = data.kecamatan

        await this.loadKelurahan()
        this.form.kelurahan = data.kelurahan

        this.isFormOpen = true
      } catch (err) {
        console.error("Gagal load data kader:", err);
      }
    },
    async loadFamily() {
      try {
        const res = await axios.get('http://localhost:8000/api/family',{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        this.family = res.data
        //console.log(this.family);
      } catch (e) {
        console.error('Gagal ambil data:', e)
      }
    },
    async loadMember() {
      try {
        const res = await axios.get('http://localhost:8000/api/member',{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        this.member = res.data
        //console.log('anggota: '+ this.member);

      } catch (e) {
        console.error('Gagal ambil data:', e)
      }
    },
    async loadUser() {
      try {
        const res = await axios.get('http://localhost:8000/api/member/user',{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        this.userList = res.data
        //console.log('User: '+ this.userList);

      } catch (e) {
        console.error('Gagal ambil data:', e)
      }
    },
    toggleExpandForm() {
      this.modalMode = "add"
      this.isFormOpen = !this.isFormOpen
      if (!this.isFormOpen) this.resetForm()
      console.log('modal mode: '+this.modalMode);

    },
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
    applyFilter() {
      // copy isi advancedFilter ke appliedFilter saat tombol Cari ditekan
      this.appliedFilter = { ...this.advancedFilter }
    },
    resetFilter() {
      this.filter.no_tpk = ''
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    async loadProvinsi() {
      try {
        const res = await axios.get("http://localhost:8000/api/region/provinsi");

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
    resetForm() {
      this.form = {
        id: null,
        no_tpk: '',
      },
      this.isFormOpen = false
    },
    async saveData() {
      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0

      try {
        console.log("Payload sebelum dikirim:", this.form)

        // simpan ke backend
        await axios.post('http://localhost:8000/api/member', this.form,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        // refresh table
        await this.resetForm()
        await this.loadMember()

        setTimeout(() => (this.showAlert = false), 3000)

      } catch (e) {
        console.error('Gagal simpan data:', e)
      }finally{
        this.isLoadingImport = false
      }
    },
    async assignData() {
      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0

      try {
        console.log("Payload sebelum dikirim:", this.form)

        // simpan ke backend
        await axios.post('http://localhost:8000/api/member/assign', this.form,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        // refresh table
        await this.resetForm()
        await this.loadMember()


        setTimeout(() => (this.showAlert = false), 3000)

      } catch (e) {
        console.error('Gagal assign data:', e)
      }finally{
        this.isLoadingImport = false
      }
    },
    async detail(no_tpk) {
      this.isDetail = true;

      try {
        const res = await axios.get(`http://localhost:8000/api/member/tpk/${no_tpk}`,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        this.tpkMember = res.data
        console.log('anggota: '+ this.tpkMember);

      } catch (e) {
        console.error('Gagal ambil data:', e)
      }
    },
  },
  mounted() {
    this.loadMember()
    this.loadProvinsi()
    this.loadFamily()
    this.loadUser()
  },
}
</script>

<style scoped>
.easy-data-table.text-center td,
.easy-data-table.text-center th {
  text-align: center;
}
.membership-wrapper {
  /* tinggi navbar bootstrap default */
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  min-height: 100vh;
}
/* Gradient Banner */
.membership-banner {
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
