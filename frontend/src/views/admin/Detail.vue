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

          <!-- Statistik berdasarkan kelompok usia dan gender-->
            <div class="row my-3">
              <div class="col-12 col-lg-6 col-md-6">
                <div class="card border border-primary shadow p-3 my-3">
                  <h4 class="text-primary fw-bold">Berdasarkan Kategori Usia</h4>
                  <div class="table-responsive">
                    <canvas ref="usiaChart"></canvas>
                  </div>
                </div>
              </div>
              <div class="col-12 col-lg-6 col-md-6">
                <div class="card border border-primary shadow p-3 my-3">
                  <h4 class="fw-bold mb-4 text-primary">Berdasarkan Jenis Kelamin</h4>
                  <div class="row justify-content-center">
                    <div
                      class="col-md-6 col-sm-12 col-12 mb-4"
                      v-for="(item, index) in genderData"
                      :key="index"
                    >
                      <div :class="['circle', item.circleClass]">{{ item.total }}</div>
                      <h5 class="title" :class="item.titleClass">{{ item.label }}</h5>
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
            <div class="row my-3">
              <div class="col-12">
                <div class="card border border-primary shadow p-3 my-3">
                  <div class="table-responsive"><canvas ref="indiChart"></canvas></div>
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

            <!-- sub TB Tabs-->
            <div
              class="tab-pane fade"
              id="tb-tab-pane"
              role="tabpanel"
              tabindex="0"
            >
              <!-- Title -->
              <div class="d-flex justify-content-between align-items-center my-3 mt-5">
                <h2 class="fw-bold">Tinggi Badan / Usia</h2>
              </div>

              <!-- Statistik Tinggi Badan / Usia -->
              <div class="card border border-primary shadow p-3 my-3">
                <table class="table table-borderless align-middle">
                  <tbody>
                    <tr>
                      <td class="text-additional fw-bold">Status</td>
                      <td class="text-muted fw-bold">Jumlah</td>
                      <td class="text-muted fw-bold">Persen</td>
                      <td class="text-muted fw-bold">Tren</td>
                    </tr>
                    <tr v-for="(row, index) in dataTable_tb" :key="index">
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
              <div class="row my-3">
                <div class="col-12 col-lg-6 col-md-6">
                  <div class="card border border-primary shadow p-3 my-3">
                    <h4 class="text-primary fw-bold">Berdasarkan Kategori Usia</h4>
                    <div class="table-responsive">
                      <canvas ref="usiaChart_tb"></canvas>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-lg-6 col-md-6">
                  <div class="card border border-primary shadow p-3 my-3">
                    <h4 class="fw-bold mb-4 text-primary">Berdasarkan Jenis Kelamin</h4>
                    <div class="row justify-content-center">
                      <div
                        class="col-md-6 col-sm-12 col-12 mb-4"
                        v-for="(item, index) in genderData_tb"
                        :key="index"
                      >
                        <div :class="['circle', item.circleClass]">{{ item.total }}</div>
                        <h5 class="title" :class="item.titleClass">{{ item.label }}</h5>
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
              <div class="row my-3">
                <div class="col-12">
                  <div class="card border border-primary shadow p-3 my-3">
                    <div class="table-responsive">
                      <canvas ref="indiChart_tb"></canvas>
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
                          <tr v-for="(values, indikator) in indikatorData_tb" :key="indikator">
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

            <!-- sub BB/TB Tabs-->
            <div
              class="tab-pane fade"
              id="bbtb-tab-pane"
              role="tabpanel"
              tabindex="0"
            >
              <!-- Title -->
              <div class="d-flex justify-content-between align-items-center my-3 mt-5">
                <h2 class="fw-bold">Berat Badan / Tinggi Badan</h2>
              </div>

              <!-- Statistik BB /TB -->
              <div class="card border border-primary shadow p-3 my-3">
                <table class="table table-borderless align-middle">
                  <tbody>
                    <tr>
                      <td class="text-additional fw-bold">Status</td>
                      <td class="text-muted fw-bold">Jumlah</td>
                      <td class="text-muted fw-bold">Persen</td>
                      <td class="text-muted fw-bold">Tren</td>
                    </tr>
                    <tr v-for="(row, index) in dataTable_status" :key="index">
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
              <div class="row my-3">
                <div class="col-12 col-lg-6 col-md-6">
                  <div class="card border border-primary shadow p-3 my-3">
                    <h4 class="text-primary fw-bold">Berdasarkan Kategori Usia</h4>
                    <div class="table-responsive">
                      <canvas ref="usiaChart_bbtb"></canvas>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-lg-6 col-md-6">
                  <div class="card border border-primary shadow p-3 my-3">
                    <h4 class="fw-bold mb-4 text-primary">Berdasarkan Jenis Kelamin</h4>
                    <div class="row justify-content-center">
                      <div
                        class="col-md-6 col-sm-12 col-12 mb-4"
                        v-for="(item, index) in genderData_bbtb"
                        :key="index"
                      >
                        <div :class="['circle', item.circleClass]">{{ item.total }}</div>
                        <h5 class="title" :class="item.titleClass">{{ item.label }}</h5>
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
              <div class="row my-3">
                <div class="col-12">
                  <div class="card border border-primary shadow p-3 my-3">
                    <div class="table-responsive">
                      <canvas ref="indiChart_bbtb"></canvas>
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
                          <tr v-for="(values, indikator) in indikatorData_bbtb" :key="indikator">
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

//import { Modal } from 'bootstrap'
//import axios from 'axios'

// PORT backend kamu
//const API_PORT = 8000;

// Bangun base URL dari window.location
//const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
//const baseURL = `${protocol}//${hostname}:${API_PORT}`;

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Detail',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, Welcome },
  data() {
    return {

    }
  },
  computed: {

  },
  created() {

  },
  methods: {

  },
  mounted() {

  }
}
</script>

<style scoped>
</style>
