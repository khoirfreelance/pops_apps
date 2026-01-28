<template>
  <div class="membership-wrapper">
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
    <HeaderAdmin  />

    <div
      class="content flex-grow-1 d-flex flex-column flex-md-row"
      :class="{
        'sidebar-collapsed': isCollapsed,
        'sidebar-expanded': !isCollapsed
      }"
    >
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar"/>

      <!-- Main Content -->
      <div class="flex-grow-1 d-flex flex-column">

        <!-- Content -->
        <div class="py-4 container-fluid" >

          <div :class="isDetail?'collapse':''">
            <!-- Welcome Card -->
            <Welcome />

            <!-- Form -->
            <div class="container-fluid">
              <!-- Collapsible Form -->
              <div v-if="isFormOpen" id="formData" class="card shadow-sm">
                <div class="card-body">
                  <form class="row g-4">
                    <!-- No TPK -->
                    <div :class="modalMode === 'add' ? 'col-md-12' : 'col-md-6'">
                      <label class="form-label small fw-semibold text-secondary">No. TPK</label>
                      <template v-if="form.no_tpk === '__new__'">
                        <input
                          type="number"
                          min="0"
                          class="form-control shadow-sm"
                          v-model="form.no_tpk_new"
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
                          :disabled="kotaReadonly"
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
                          :disabled="kecamatanReadonly"
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
                          :disabled="kelurahanReadonly"
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
              <div class="card modern-card">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between gap-2 my-2">
                    <h3 class="text-primary"> Data Petugas</h3>
                    <div class="d-flex align-items-center justify-content-end gap-2">
                      <input
                        type="text"
                        class="form-control form-control-sm "
                        style="width: 220px;"
                        placeholder="Ketik Nama"
                        v-model="searchQuery_all"
                      >
                      <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        @click="toggleExpandForm"
                      >
                        <i :class="isFormOpen ? 'bi bi-dash-square' : 'bi bi-plus-square'"></i>
                        {{ isFormOpen ? 'Tutup Form' : 'Tambah Data' }}
                      </button>
                    </div>
                  </div>
                  <div class="table-responsive">
                  <easy-data-table
                      :headers="headers"
                      :items="items_all"
                      :sortable="true"
                      :rows-per-page="perPage"
                      :rows-items="perPageOptions"
                      :rows-per-page-text="'Rows per page'"
                      header-text-direction="center"
                      table-class-name="my-custom-table"
                      header-class-name="my-custom-header"
                      show-index alternating
                      border-cell
                    >
                    <template #item-index="{ index }">
                      <div class="text-center fw-semibold">
                        {{ index }}
                      </div>
                    </template>
                    <template #item-no_tpk="{ no_tpk }">
                      <div class="text-center">
                        {{ no_tpk }}
                      </div>
                    </template>
                    <template #item-action="items">
                      <div class="action-wrapper d-flex gap-1 m-1 text-center">
                        <button
                          class="btn btn-primary m-2"
                          @click="detail(items)"
                          style="font-size: small;"
                        >
                          <i class="fa fa-eye"></i>
                        </button>
                        <button
                          class="btn btn-secondary m-2"
                          @click="assign(items)"
                          style="font-size: small;"
                        >
                          <i class="fa fa-user-plus"></i>
                        </button>
                      </div>
                    </template>
                  </easy-data-table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div :class="isDetail?'':'collapse'">
            <Welcome />
            <!-- Navigation Tab -->
            <div class="container-fluid">
              <div class="d-flex justify-content-center">
                <div class="nav nav-pills gap-2 flex-wrap justify-content-center" id="nav-tab" role="tablist">
                  <button
                    class="nav-link active"
                    id="nav-anggota-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#nav-anggota"
                    type="button"
                    role="tab"
                  >
                    Anggota TPK
                  </button>

                  <button
                    class="nav-link"
                    id="nav-keluarga-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#nav-keluarga"
                    type="button"
                    role="tab"
                  >
                    Keluarga Dampingan
                  </button>

                  <button
                    class="nav-link"
                    id="nav-anak-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#nav-anak"
                    type="button"
                    role="tab"
                  >
                    Pendampingan Anak
                  </button>

                  <button
                    class="nav-link"
                    id="nav-bumil-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#nav-bumil"
                    type="button"
                    role="tab"
                  >
                    Pendampingan Bumil
                  </button>

                  <button
                    class="nav-link"
                    id="nav-catin-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#nav-catin"
                    type="button"
                    role="tab"
                  >
                    Pendampingan Catin
                  </button>
                </div>
              </div>
            </div>

            <div class="container-fluid">
              <div class="tab-content" id="nav-tabContent">
                <!-- Anggota -->
                <div class="tab-pane fade show active" id="nav-anggota" role="tabpanel" aria-labelledby="nav-anggota-tab">
                  <div class="card modern-card">
                    <div class="card-body">
                      <div class="d-flex align-items-center justify-content-between gap-2">
                        <h3 class="text-primary"> Data {{ kader }}</h3>
                        <div class="d-flex align-items-center justify-content-end gap-2">
                          <input
                            type="text"
                            class="form-control form-control-sm"
                            style="width: 220px;"
                            placeholder="Ketik Nama"
                            v-model="searchQuery_anggota"
                          >
                          <button class="btn btn-danger btn-sm" @click="backTo()">
                            X
                          </button>
                        </div>
                      </div>

                      <div class="table-responsive mt-2">
                        <easy-data-table
                          :headers="headers_tpk"
                          :items="items"
                          :sortable="true"
                          :rows-per-page="perPage"
                          :rows-items="perPageOptions"
                          :rows-per-page-text="'Rows per page'"
                          header-text-direction="center"
                          table-class-name="my-custom-table"
                          header-class-name="my-custom-header"
                          show-index alternating
                          border-cell
                        />
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Keluarga -->
                <div class="tab-pane fade" id="nav-keluarga" role="tabpanel" aria-labelledby="nav-keluarga-tab">
                  <div class="card">
                    <!-- Form -->
                    <div class="container-fluid">
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
                    <div class="card-body">
                      <!-- Table -->
                      <div class="d-flex align-items-center justify-content-between gap-2">
                          <h3 class="text-primary"> Data Keluarga Dampingan</h3>
                          <div class="d-flex align-items-center justify-content-end gap-2">
                            <!-- <button
                              type="button"
                              class="btn btn-primary btn-sm"
                              @click="toggleExpandForm"
                            >
                              <i :class="isFormOpen ? 'bi bi-dash-square' : 'bi bi-plus-square'"></i>
                              {{ isFormOpen ? 'Tutup Form' : 'Tambah Data' }}
                            </button> -->
                            <input
                              type="text"
                              class="form-control form-control-sm"
                              style="width: 220px;"
                              placeholder="Ketik Nama Kepala Keluarga"
                              v-model="searchQuery_keluarga"
                            >
                            <button class="btn btn-danger btn-sm" @click="backTo()">
                              X
                            </button>
                        </div>
                      </div>

                      <div class="table-responsive mt-2">
                        <easy-data-table
                          :headers="headers_family"
                          :items="items_family"
                          :sortable="true"
                          :rows-per-page="perPage"
                          :rows-items="perPageOptions"
                          :rows-per-page-text="'Rows per page'"
                          header-text-direction="center"
                          table-class-name="my-custom-table"
                          header-class-name="my-custom-header"
                          show-index alternating
                          border-cell
                        />
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Anak -->
                <div class="tab-pane fade" id="nav-anak" role="tabpanel" aria-labelledby="nav-anak-tab">
                  <div class="card border-0 shadow-sm p-2">
                    <!-- Table -->
                    <div class="card-body">
                      <div class="d-flex align-items-center justify-content-between gap-2">
                          <h3 class="text-primary"> Riwayat Pendampingan Anak</h3>
                          <div class="d-flex align-items-center justify-content-end gap-2">
                            <!-- <button
                              type="button"
                              class="btn btn-primary btn-sm"
                              @click="toggleExpandForm"
                            >
                              <i :class="isFormOpen ? 'bi bi-dash-square' : 'bi bi-plus-square'"></i>
                              {{ isFormOpen ? 'Tutup Form' : 'Tambah Data' }}
                            </button> -->
                            <input
                              type="text"
                              class="form-control form-control-sm"
                              style="width: 220px;"
                              placeholder="Ketik Nama Anak"
                              v-model="searchQuery_anak"
                            >
                            <button class="btn btn-danger btn-sm" @click="backTo()">
                              X
                            </button>
                        </div>
                      </div>
                      <div class="table-responsive mt-2">
                        <easy-data-table
                          :headers="headers_children"
                          :items="items_anak"
                          :sortable="true"
                          :rows-per-page="perPage"
                          :rows-items="perPageOptions"
                          :rows-per-page-text="'Rows per page'"
                          header-text-direction="center"
                          table-class-name="my-custom-table"
                          header-class-name="my-custom-header"
                          show-index alternating
                          border-cell
                        />
                      </div>
                    </div>

                  </div>
                </div>

                <!-- Bumil -->
                <div class="tab-pane fade" id="nav-bumil" role="tabpanel" aria-labelledby="nav-bumil-tab">
                  <div class="card border-0 shadow-sm p-2">
                    <!-- Table -->
                    <div class="card-body">
                      <div class="d-flex align-items-center justify-content-between gap-2">
                          <h3 class="text-primary"> Riwayat Pendampingan Ibu Hamil</h3>
                          <div class="d-flex align-items-center justify-content-end gap-2">
                            <input
                              type="text"
                              class="form-control form-control-sm"
                              style="width: 220px;"
                              placeholder="Ketik Nama Anak"
                              v-model="searchQuery_bumil"
                            >
                            <button class="btn btn-danger btn-sm" @click="backTo()">
                              X
                            </button>
                        </div>
                      </div>
                      <div class="table-responsive mt-2">
                        <easy-data-table
                          :headers="headers_pregnancy"
                          :items="items_bumil"
                          :sortable="true"
                          :rows-per-page="perPage"
                          :rows-items="perPageOptions"
                          :rows-per-page-text="'Rows per page'"
                          header-text-direction="center"
                          table-class-name="my-custom-table"
                          header-class-name="my-custom-header"
                          show-index alternating
                          border-cell
                        />
                      </div>
                    </div>

                  </div>
                </div>

                <!-- Catin -->
                <div class="tab-pane fade" id="nav-catin" role="tabpanel" aria-labelledby="nav-catin-tab">
                  <div class="card border-0 shadow-sm p-2">
                    <div class="card-body">

                      <!-- HEADER -->
                      <div class="row align-items-center g-2">
                        <div class="col-12 col-md-6">
                          <h5 class="text-primary mb-0">
                            Riwayat Pendampingan Calon Pengantin
                          </h5>
                        </div>

                        <div class="col-12 col-md-6">
                          <div class="d-flex gap-2 justify-content-md-end">
                            <input
                              type="text"
                              class="form-control form-control-sm w-100 w-md-auto"
                              placeholder="Ketik Calon Perempuan"
                              v-model="searchQuery_catin"
                            >
                            <button class="btn btn-danger btn-sm px-3" @click="backTo">
                              ✕
                            </button>
                          </div>
                        </div>
                      </div>

                      <!-- TABLE -->
                      <div class="table-responsive mt-3">
                        <easy-data-table
                          :headers="headers_bride"
                          :items="items_catin"
                          :sortable="true"
                          :rows-per-page="perPage"
                          :rows-items="perPageOptions"
                          rows-per-page-text="Rows per page"
                          header-text-direction="center"
                          table-class-name="my-custom-table"
                          header-class-name="my-custom-header"
                          show-index
                          alternating
                          border-cell
                        />
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
import axios from 'axios'
import Welcome from '@/components/Welcome.vue'
import Swal from 'sweetalert2'

// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'membership',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, EasyDataTable, Welcome },
  data() {
    return {
      kader:"",
      searchQuery_all:'',
      searchQuery_anggota:'',
      searchQuery_keluarga:'',
      searchQuery_anak:'',
      searchQuery_bumil:'',
      searchQuery_catin:'',
      currentPage: 1,
      perPage: 10,
      perPageOptions: [5, 10, 25, 50],
      kotaReadonly:true,
      kecamatanReadonly:true,
      kelurahanReadonly:true,
      progressLevel: null,
      configCacheKey: 'site_config_cache',
      // required
      isLoading: true,
      isCollapsed: false,
      username: '',
      today: '',
      thisMonth:'',
      kelurahan: '',
      logoSrc: '/cipayung.png',
      logoLoaded: true,
      windowWidth: window.innerWidth,
      // -------------------
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
        nama:'',
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
        { text: 'Role', value: 'role', align: "center"},
        { text: 'No. Telepon', value: 'phone', align: "center"},
      ],
      headers_family: [
        { text: 'No. KK/NIK', value: 'no_kk' },
        { text: 'Kepala Keluarga', value: 'nama_kepala' },
        { text: 'RT', value: 'rt' },
        { text: 'RW', value: 'rw' },
        { text: 'Pendampingan', value: 'pendampingan' },
        { text: 'Sasaran', value: 'sasaran' },
      ],
      //dummy anak
      chilList:[],
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
      pregnancy: [],
      headers_pregnancy: [
        { text: 'Kunjungan Terakhir', value: 'kunjungan' },
        { text: 'NIK', value: 'nik' },
        { text: 'Nama', value: 'nama' },
        { text: 'Usia', value: 'usia' },
        { text: 'Usia Kehamilan', value: 'usia_kehamilan' },
        { text: 'BB', value: 'bb' },
        { text: 'TB', value: 'tb' },
        { text: 'Lila', value: 'lila' },
        { text: 'Hb', value: 'hb' },
        { text: 'Riwayat Penyakit', value: 'riwayat' },
      ],
      keluarga:[],
      anak:[],
      bumil:[],
      catin:[],
      // dummy brides data
      brides: [],
      headers_bride: [
        { text: 'Tanggal', value: 'kunjungan' },
        { text: 'Tanggal Menikah', value: 'menikah' },
        { text: 'Nama Catin (P)', value: 'namaP' },
        { text: 'NIK Catin (P)', value: 'nikP' },
        { text: 'Usia Catin (P)', value: 'usiaP' },
        { text: 'BB (kg)', value: 'bbP' },
        { text: 'TB (cm)', value: 'tbP' },
        { text: 'LiLa (cm)', value: 'lilaP' },
        { text: 'Hb', value: 'hbP' },
        { text: 'Nama Pasangan', value: 'namaL' },
        { text: 'NIK Pasangan', value: 'nikL' },
        { text: 'Usia Pasangan', value: 'usiaL' },
        //{ text: 'Catatan Beresiko', value: 'catatan' },
      ],
      // filter
      filter: {
        no_tpk: '',
      },
    }
  },
  computed: {
    filteredMember() {
      //console.log('arr', this.tpkMember);
      if (!this.member) return [];


      const arr = Array.isArray(this.member)
        ? this.member
        : Object.values(this.member);
      //console.log('arr', arr);

      const q = (this.searchQuery_all ?? "").toString().toLowerCase().trim();

      return arr.filter(item => {
        const nama = (item.nama ?? "").toString().toLowerCase();

        return nama.includes(q);
      });
    },
    items_all() {
      return Object.values(this.filteredMember)
        .sort((a, b) => a.id - b.id)
        .map(item => ({
          id: item.id,
          no_tpk: item.no_tpk ?? "",
          nama: item.nama ?? "",
          action: { ...item }
        }))
    },
    filteredAnggota() {
      //console.log('arr', this.tpkMember);
      if (!this.tpkMember) return [];


      const arr = Array.isArray(this.tpkMember)
        ? this.tpkMember
        : Object.values(this.tpkMember);
      //console.log('arr', arr);

      const q = (this.searchQuery_anggota ?? "").toString().toLowerCase().trim();

      return arr.filter(item => {
        const nama = (item.nama ?? "").toString().toLowerCase();

        return nama.includes(q);
      });
    },
    items() {
      return Object.values(this.filteredAnggota)
        .sort((a, b) => a.id - b.id)
        .map(item => ({
          id: item.id,
          nama: item.nama ?? "",
          role: item.role ?? "",
          phone: item.phone ?? ""
        }))
    },
    filteredFamily() {

      if (!this.keluarga) return [];

      const arr = Array.isArray(this.keluarga)
        ? this.keluarga
        : Object.values(this.keluarga);
      //console.log('arr', arr);

      const q = (this.searchQuery_keluarga ?? "").toString().toLowerCase().trim();

      return arr.filter(item => {
        const kepala_keluarga = (item.kepala_keluarga ?? "").toString().toLowerCase();

        return kepala_keluarga.includes(q);
      });
    },
    items_family() {
      return Object.values(this.filteredFamily)
        .sort((a, b) => a.id - b.id)
        .map(item => ({
          id: item.id,
          no_kk: item.no_kk ?? "",
          nama_kepala: item.kepala_keluarga ?? "",
          rt: item.rt ?? "",
          rw: item.rw ?? "",
          pendampingan: item.tgl_pendampingan ?? "",
          sasaran: item.sasaran ?? "",
        }))
    },
    filteredAnak() {
      if (!this.anak) return [];
      const arr = Array.isArray(this.anak)
        ? this.anak
        : Object.values(this.anak);

      const q = (this.searchQuery_anak ?? "").toString().toLowerCase().trim();

      return arr.filter(item => {
        const nama_anak = (item.nama_anak ?? "").toString().toLowerCase();
        const nik_anak = (item.nik_anak ?? "").toString().toLowerCase();

        return nama_anak.includes(q) || nik_anak.includes(q);
      });
    },
    items_anak() {
      return Object.values(this.filteredAnak)
        .sort((a, b) => a.id - b.id)
        .map(item => ({
          id: item.id,
          tgl_pendampingan: item.tgl_pendampingan,
          nama: item.nama_anak,
          kepala: item.nama_ayah,
          bb: item.bb,
          tb: item.tb,
          lila: item.lila,
          asi: item.asi,
          imunisasi: item.imunisasi,
          posyandu: item.rutin_posyandu
        }))
    },
    filteredBumil() {
      if (!this.bumil) return [];
      const arr = Array.isArray(this.bumil)
        ? this.bumil
        : Object.values(this.bumil);

      const q = (this.searchQuery_bumil ?? "").toString().toLowerCase().trim();

      return arr.filter(item => {
        const nama = (item.nama_ibu ?? "").toString().toLowerCase();
        const nik = (item.nik_ibu ?? "").toString().toLowerCase();

        return nama.includes(q) || nik.includes(q);
      });
    },
    items_bumil() {
      return Object.values(this.filteredBumil)
        .sort((a, b) => a.id - b.id)
        .map(item => ({
          id: item.id,

          // header: Kunjungan Terakhir
          kunjungan: item.tanggal_pemeriksaan_terakhir
            ? this.formatDate(item.tanggal_pemeriksaan_terakhir)
            : '-',

          // header: No. KK
          nik: item.nik_ibu ?? '-',

          // header: Nama
          nama: item.nama_ibu ?? item.sasaran ?? '-',

          // header: Usia
          usia: item.usia_ibu
            ? `${item.usia_ibu} th (${item.status_risiko_usia})`
            : '-',

          // header: Usia Kehamilan
          usia_kehamilan: item.usia_kehamilan_minggu
            ? `${item.usia_kehamilan_minggu} mg`
            : '-',

          // header: BB
          bb: item.berat_badan
            ? `${item.berat_badan} kg`
            : '-',

          // header: TB
          tb: item.tinggi_badan
            ? `${item.tinggi_badan} cm`
            : '-',

          // header: Lila
          lila: item.lila
            ? `${item.lila} (${item.status_gizi_lila})`
            : '-',

          // header: Hb
          hb: item.kadar_hb
            ? `${item.kadar_hb} (${item.status_gizi_hb})`
            : '-',

          // header: Riwayat Penyakit
          riwayat: item.riwayat_penyakit ?? '-',
        }))
    },
    filteredCatin() {
      if (!this.catin) return [];
      const arr = Array.isArray(this.catin)
        ? this.catin
        : Object.values(this.catin);

      const q = (this.searchQuery_catin ?? "").toString().toLowerCase().trim();

      return arr.filter(item => {
        const nama_perempuan = (item.nama_perempuan ?? "").toString().toLowerCase();

        return nama_perempuan.includes(q);
      });
    },
    items_catin() {
      return Object.values(this.filteredCatin)
        .sort((a, b) => a.id - b.id)
        .map(item => ({
          kunjungan: item.tanggal_pendampingan
            ? this.formatDate(item.tanggal_pendampingan)
            : '-',

          menikah: item.tanggal_rencana_menikah
            ? this.formatDate(item.tanggal_rencana_menikah)
            : '-',

          namaP: item.nama_perempuan ?? '-',
          nikP: item.nik_perempuan ?? '-',
          usiaP: item.usia_perempuan ?? '-',
          bbP: item.berat_perempuan ?? '-',
          tbP: item.tinggi_perempuan ?? '-',
          lilaP: item.lila_perempuan? `${item.lila_perempuan} (${item.status_kek})`: '-',
          hbP: item.hb_perempuan? `${item.hb_perempuan} (${item.status_hb})`:'-',

          namaL: item.nama_laki ?? '-',
          nikL: item.nik_laki ?? '-',
          usiaL: item.usia_laki ?? '-',
        }))
    }
  },
  methods: {
    formatDate(dateString) {
      if (!dateString) return '-'
      const date = new Date(dateString)
      const day = String(date.getDate()).padStart(2, '0')
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const year = date.getFullYear()
      return `${day}-${month}-${year}`
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
    /* async getWilayahUser() {
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
    }, */
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
    backTo(){
      this.isDetail = false
    },
    openTambah() {
      this.modalMode = "add"
      this.form = {} // reset form
      this.isFormOpen = true
    },
    async assign(items) {
      this.modalMode = "assign";
      this.isFormOpen = true
      const id = items.id
      try {
        const res = await axios.get(`${baseURL}/api/cadre/${id}`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        const data = res.data;
        //console.log('tpkList:',data.listTPK);

        this.tpkList = Array.isArray(data.listTPK)
          ? data.listTPK
          : []

        // mapping ke form sesuai struktur
        this.form = {
          nama: data.nama,
          id: data.idTPK,
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
        const res = await axios.get(`${baseURL}/api/family`,{
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
        const res = await axios.get(`${baseURL}/api/member`,{
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
        const res = await axios.get(`${baseURL}/api/member/user`,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        this.userList = res.data

      } catch (e) {
        console.error('Gagal ambil data:', e)
      }
    },
    async loadTPK(){
      try {
        const res = await axios.get(`${baseURL}/api/member/tpk`,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        //console.log('data: '+res.data);

        this.tpkList = res.data
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
        const res = await axios.get(`${baseURL}/api/region/provinsi`);

        // isi list dari API
        this.provinsiList = res.data;

      } catch (err) {
        console.error("Error load provinsi:", err);
      }
    },
    async loadKota() {
      if (this.form.provinsi && this.form.provinsi !== "__new__") {
        const res = await axios.get(`${baseURL}/api/region/kota`, {
          params: { provinsi: this.form.provinsi }
        });
        this.kotaList = res.data;
        this.kecamatanList = [];
        this.kelurahanList = [];
        this.form.kota = "";
        this.form.kecamatan = "";
        this.form.kelurahan = "";
        this.kotaReadonly = false
      }
    },
    async loadKecamatan() {
      if (this.form.kota && this.form.kota !== "__new__") {
        const res = await axios.get(`${baseURL}/api/region/kecamatan`, {
          params: { provinsi: this.form.provinsi, kota: this.form.kota }
        });
        this.kecamatanList = res.data;
        this.kelurahanList = [];
        this.form.kecamatan = "";
        this.form.kelurahan = "";
        this.kecamatanReadonly = false
      }
    },
    async loadKelurahan() {
      if (this.form.kecamatan && this.form.kecamatan !== "__new__") {
        const res = await axios.get(`${baseURL}/api/region/kelurahan`, {
          params: {
            provinsi: this.form.provinsi,
            kota: this.form.kota,
            kecamatan: this.form.kecamatan
          }
        });
        this.kelurahanList = res.data;
        this.form.kelurahan = "";
        this.kelurahanReadonly = false
      }
    },
    async resetForm() {
      this.form = {
        id: null,
        no_tpk: '',
      },
      await this.$nextTick(),
      await this.loadProvinsi(),
      await this.loadKota(),
      await this.loadKecamatan(),
      await this.loadKelurahan(),
      this.kotaReadonly = true,
      this.kecamatanReadonly = true,
      this.kelurahanReadonly = true,
      this.isFormOpen = false
    },
    normalizeFormPayload(form) {
      const normalized = { ...form }

      Object.keys(normalized).forEach(key => {
        if (key.endsWith('_new')) {
          const baseKey = key.replace('_new', '')
          const value = normalized[key]

          if (value !== null && value !== '' && value !== undefined) {
            normalized[baseKey] = value
          }

          // optional: hapus field _new dari payload
          delete normalized[key]
        }
      })

      return normalized
    },
    async saveData() {
      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0

      try {
        const payload = this.normalizeFormPayload(this.form)
        // simpan ke backend
        await axios.post(`${baseURL}/api/member`, payload,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        this.isFormOpen = false
        this.animatedProgress = 50
        this.progressLevel = 50
        this.importProgress = 50
        // refresh table
        await this.resetForm()
        await this.loadTPK()
        this.animatedProgress = 70
        this.progressLevel = 70
        this.importProgress = 70
        await this.loadMember()

        this.importProgress = 100
        // animasi ke 100
        await new Promise(resolve => {
          const interval = setInterval(() => {
            if (this.animatedProgress >= 100) {
              clearInterval(interval)
              resolve()
            } else {
              this.animatedProgress += 5
            }
          }, 30)
        })

        Swal.fire({
          icon: 'success',
          html: `<strong>No. TPK ${this.form.no_tpk}</strong> berhasil ditambahkan`,
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-primary'
          }
        })

      } catch (e) {
        this.isLoadingImport = false
        Swal.fire({
          title: 'Error',
          html: e.data?.error || 'Terjadi kesalahan saat menghapus data',
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })
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
        const nama = this.form.nama
        const no_tpk = this.form.no_tpk === '__new__' ? this.form.no_tpk_new : this.form.no_tpk

        // simpan ke backend
        await axios.post(`${baseURL}/api/member/assign`, this.form,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        this.isFormOpen = false
        this.animatedProgress = 50
        this.progressLevel = 50
        this.importProgress = 50
        // refresh table
        await this.resetForm()
        this.animatedProgress = 70
        this.progressLevel = 70
        this.importProgress = 70
        await this.loadMember()

        this.importProgress = 100
        // animasi ke 100
        await new Promise(resolve => {
          const interval = setInterval(() => {
            if (this.animatedProgress >= 100) {
              clearInterval(interval)
              resolve()
            } else {
              this.animatedProgress += 5
            }
          }, 30)
        })

        Swal.fire({
          icon: 'success',
          html: `<strong>${nama}</strong> berhasil ditambahkan menjadi anggota TPK dengan No. TPK <strong>${no_tpk}</strong>`,
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-primary'
          }
        })

      } catch (e) {
        this.isLoadingImport = false
        const nama = this.form.nama
        Swal.fire({
          title: 'Error',
          html: e.data?.error || `Gagal menambahkan <strong>${nama}</strong> sebagai Anggota TPK`,
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })
      }finally{
        this.isLoadingImport = false
      }
    },
    async detail(items) {
      this.kader = !items.no_tpk ? 'Non Kader TPK' : 'Kader TPK'
      this.isDetail = true;
      this.isLoading = true;

      const noTPK = items.no_tpk ? items.no_tpk : 'null'

      try {
        const res = await axios.get(`${baseURL}/api/member/tpk/${noTPK}`,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        this.tpkMember = res.data

        this.dampinganKeluarga(items)
      } catch (e) {
        console.error('Gagal ambil data:', e)
      }finally {
        this.isLoading = false
      }
    },
    async dampinganKeluarga(items) {
      const id = items.id
      try {
        const res = await axios.get(`${baseURL}/api/member/family/${id}`,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        this.keluarga = res.data

        this.anak = res.data
          .filter(i => i.jenis === 'ANAK')
          .map(i => i.anak)

        this.bumil = res.data
          .filter(i => i.jenis === 'BUMIL')
          .map(i => i.bumil)

        this.catin = res.data
          .filter(i => i.jenis === 'CATIN')
          .map(i => i.catin)

      } catch (e) {
        console.error('Gagal ambil data:', e)
      }finally {
        this.isLoading = false
      }
    }
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
      await Promise.all([
        this.loadConfigWithCache(),
        this.loadMember(),
        this.loadProvinsi(),
        this.loadFamily(),
        this.loadUser(),
        this.loadTPK(),
        //this.getWilayahUser(),
        this.handleResize(),
        window.addEventListener('resize', this.handleResize)
      ])
    } catch (err) {
      console.error('Error loading data:', err)
    } finally {
      this.isLoading = false
    }
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
  },
}
</script>

<style scoped>

.spinner-overlay {
  position: fixed;
  inset: 0;
  background: rgba(255, 255, 255, 0.8);
  z-index: 9999;
  backdrop-filter: blur(2px);
  transition: opacity 0.3s ease;
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
.easy-data-table.text-center td,
.easy-data-table.text-center th {
  text-align: center;
}
/* Hilangkan garis pemisah antara sidebar dan content */
.flex-grow-1 {
  border-left: none !important;
  background-color: #f9f9fb;
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

.my-custom-table {
  --easy-table-header-background-color: #b4dbc6;
}

.my-custom-table .col-action {
  text-align: center !important;
}

.my-custom-table td.col-action {
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
}
.my-custom-table .col-action {
  text-align: center !important;
}
.action-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
