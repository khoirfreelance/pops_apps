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

          <!-- Statistic Cards -->
          <div class="container-fluid mt-3">
            <div class="row g-4 justify-content-center">
              <div
                v-for="(stat, index) in stats"
                :key="index"
                class="col-xl-1_9 col-lg-2 col-md-3 col-sm-6 col-6"
              >
                <div class="stat-card shadow-sm rounded h-100">
                  <h6 class="text-muted small pt-2 ps-2" style="font-size: 10px;">{{ stat.title }}</h6>
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
          </div>

          <!-- Judul Laporan -->
          <div class="text-center mt-3">
            <div class="bg-additional text-white py-1 px-4 d-inline-block rounded-top">
              <h5 class="title mb-0 text-capitalize fw-bold">
                Laporan Status Gizi {{ kelurahan }} Bulan {{ thisMonth }}
              </h5>
            </div>
          </div>

          <!-- Filter Form -->
          <div class="bg-light border rounded-bottom shadow-sm px-4 py-3 mt-0 sticky-filter">
            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <!-- Kelurahan/Desa -->
              <div class="col-xl-2 col-lg-4 col-md-4 col-sm-4 col-6">
                <label class="form-label fs-md-1">Kel/Desa</label>
                <select v-model="filters.kelurahan" class="form-select text-muted" disabled>
                  <option :value="kelurahan">{{ kelurahan }}</option>
                </select>
              </div>

              <!-- Posyandu -->
              <div class="col-xl-2 col-lg-4 col-md-4 col-sm-4 col-6">
                <label class="form-label">Posyandu</label>
                <select
                  v-model="filters.posyandu"
                  class="form-select text-muted"
                  @change="handlePosyanduChange"
                >
                  <option value="">All</option>
                  <option v-for="item in posyanduList" :key="item.id" :value="item.nama_posyandu">
                    {{ item.nama_posyandu }}
                  </option>
                </select>
              </div>

              <!-- RW -->
              <div class="col-xl-2 col-lg-4 col-md-4 col-sm-4 col-6">
                <label class="form-label">RW</label>
                <select
                  v-model="filters.rw"
                  class="form-select text-muted"
                  :disabled="rwReadonly"
                >
                  <option value="">All</option>
                  <option v-for="rw in rwList" :key="rw" :value="rw">{{ rw }}</option>
                </select>
              </div>

              <!-- RT -->
              <div class="col-xl-2 col-lg-4 col-md-4 col-sm-4 col-6">
                <label class="form-label">RT</label>
                <select
                  v-model="filters.rt"
                  class="form-select text-muted"
                  :disabled="rtReadonly"
                >
                  <option value="">All</option>
                  <option v-for="rt in rtList" :key="rt" :value="rt">{{ rt }}</option>
                </select>
              </div>

              <!-- Periode -->
              <div class="col-xl-2 col-lg-4 col-md-4 col-sm-4 col-6">
                <label class="form-label">Periode</label>
                <select v-model="filters.periode" class="form-select">
                  <option value="">All</option>
                  <option
                    v-for="p in periodeOptions"
                    :key="p.value"
                    :value="p.value"
                  >
                    {{ p.label }}
                  </option>
                </select>

              </div>

              <!-- Tombol Cari -->
              <div class="col-xl-2 col-lg-4 col-md-4 col-6 col-sm-4 d-grid">
                <button type="submit" class="btn btn-gradient fw-semibold">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>
              </div>
            </form>
          </div>

          <!-- Main -->
          <div class="d-flex justify-content-center mt-4">
            <ul
              class="nav nav-pills d-flex flex-wrap justify-content-center gap-2 w-100"
              id="myTab"
              role="tablist"
              style="max-width: 800px;"
            >
              <li class="nav-item flex-fill text-center" role="presentation">
                <button
                  class="nav-link active w-100 text-truncate"
                  id="anak-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#anak-tab-pane"
                  type="button"
                  role="tab"
                  aria-controls="anak-tab-pane"
                  aria-selected="true"
                  @click="menu('anak')"
                >
                  Status Gizi Anak
                </button>
              </li>

              <li class="nav-item flex-fill text-center" role="presentation">
                <button
                  class="nav-link w-100 text-truncate"
                  id="bumil-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#bumil-tab-pane"
                  type="button"
                  role="tab"
                  aria-controls="bumil-tab-pane"
                  aria-selected="false"
                  @click="menu('bumil')"
                >
                  Status Kesehatan Ibu Hamil
                </button>
              </li>

              <li class="nav-item flex-fill text-center" role="presentation">
                <button
                  class="nav-link w-100 text-truncate"
                  id="catin-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#catin-tab-pane"
                  type="button"
                  role="tab"
                  aria-controls="catin-tab-pane"
                  aria-selected="false"
                  @click="menu('catin')"
                >
                  Calon Pengantin Berisiko
                </button>
              </li>
            </ul>
          </div>
          <div class="tab-content" id="myTabContent">
            <!-- Tab 1 -->
            <div class="tab-pane fade show active" id="anak-tab-pane" role="tabpanel" tabindex="0">
              <!-- Issue and Stat Card -->
              <div class="container-fluid my-2 row">
                <!-- RINGKASAN STATUS GIZI -->
                <div class="col-md-8 d-flex flex-column">
                  <!-- judul tetap di atas -->
                  <h5 class="fw-bold text-success mb-3">Ringkasan Status Gizi Bulan Ini</h5>

                  <!-- row isi gizi & total anak -->
                  <div class="row flex-grow-1 align-items-center">
                    <!-- GIZI CARDS -->
                    <div class="col-lg-8 col-xl-10 col-md-6 col-sm-12">
                      <div class="row justify-content-center">
                        <div
                          v-for="(item, index) in gizi"
                          :key="index"
                          class="col-xl-4 col-lg-6 col-sm-6 col-12 mb-3"
                        >
                          <div
                            class="card shadow-sm border-0 rounded-3 overflow-hidden"
                            :class="`border-start border-4 border-${item.color}`"
                          >
                            <div class="card-body py-3 d-flex justify-content-between">
                              <div>
                                <h6 class="fw-bold mb-1">{{ item.title }}</h6>
                                <p class="mb-2 small" :class="`text-${item.color}`">{{ item.percent }}</p>
                              </div>
                              <h3 class="fw-bold mb-0" :class="`text-${item.color}`">{{ item.value }}</h3>
                            </div>

                            <!-- SVG LINE CHART -->
                            <div class="card-footer bg-transparent border-0 pt-0">
                              <svg
                                viewBox="0 0 100 30"
                                class="svg-line"
                                preserveAspectRatio="none"
                                :class="`stroke-${item.color}`"
                              >
                                <polyline
                                  fill="none"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  points="0,20 15,15 30,18 45,10 60,12 75,8 90,15 100,10"
                                />
                              </svg>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- TOTAL ANAK -->
                    <div class="col-lg-4 col-xl-2 col-md-6 col-sm-12 d-flex align-items-end">
                      <div class="card text-center shadow-sm border p-2 w-100">
                        <h6 class="text-muted my-4 fw-bold">Total Anak Balita</h6>
                        <div class="flex-grow-1 d-flex flex-column justify-content-center">
                          <h2 class="fw-bold text-success mb-0">{{totalAnak}}</h2>
                        </div>
                        <i class="bi bi-people fs-3 text-dark mt-2 mb-3"></i>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- INFO BOXES -->
                <div class="col-md-4 mt-3 d-flex flex-column">
                  <div
                    v-for="(box, idx) in infoBoxes"
                    :key="idx"
                    class="alert mb-2"
                    :class="`alert-${box.type}`"
                  >
                    <strong>{{ box.title }}</strong><br />
                    <span v-html="box.desc"></span> <!-- pakai v-html -->
                  </div>
                </div>
              </div>

              <!-- Pie Chart-->
              <div class="container-fluid row">
                <div class="col-md-12">
                  <h5 class="fw-bold text-primary">Ringkasan Status Gizi Bulan Ini</h5>
                </div>
                <div class="col-12 col-xl-8">
                  <!-- Berat Badan / Usia -->
                  <div class="card border border-primary shadow p-3 my-3">
                    <h6 class="fw-bold text-primary">Berat Badan / Usia</h6>
                    <div class="row">
                      <!-- Table -->
                      <div class="col-12 col-xl-7 table-responsive">
                        <table class="table table-borderless align-middle">
                          <tbody>
                            <tr>
                              <td class="text-additional fw-semibold">Status</td>
                              <td class="text-additional fw-semibold">Jumlah</td>
                              <td class="text-additional fw-semibold">Persen</td>
                              <td class="text-additional fw-semibold">Tren</td>
                            </tr>
                            <tr v-for="(row, index) in dataTable_bb" :key="index">
                              <td class="text-additional small">{{ row.status }}</td>
                              <td class="text-additional small">{{ row.jumlah }}</td>
                              <td class="text-additional small">{{ row.persen }} %</td>
                              <td class="small" :class="row.trenClass">
                                <span v-if="row.tren !== '-'">
                                  <i :class="row.trenIcon"></i> {{ row.tren }}
                                </span>
                                <span v-else>-</span>
                              </td>
                            </tr>
                          </tbody>
                          <tfoot>
                            <tr>
                              <td colspan="4" class="pt-4"><a href="/admin/dashboard/detail?tipe=bbu">Lihat Grafik Selengkapnya</a></td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>

                      <!-- Chart -->
                      <div class="col-12 col-xl-5 table-responsive text-center">
                        <canvas ref="pieChart_bb" class="mx-auto" style="max-width: 300px; max-height: 300px; min-width: 200px; min-height: 200px;"></canvas>
                      </div>
                    </div>
                  </div>

                  <!-- Tinggi Badan / Usia -->
                  <div class="card border border-primary shadow p-3 my-3">
                    <h6 class="fw-bold text-primary">Tinggi Badan / Usia</h6>
                    <div class="row">
                      <div class="col-12 col-xl-7 table-responsive">
                        <table class="table table-borderless align-middle">
                          <tbody>
                            <tr>
                              <td class="text-additional fw-bold">Status</td>
                              <td class="text-additional fw-bold">Jumlah</td>
                              <td class="text-additional fw-bold">Persen</td>
                              <td class="text-additional fw-bold">Tren</td>
                            </tr>
                            <tr v-for="(row, index) in dataTable_tb" :key="index">
                              <td class="text-additional small">{{ row.status }}</td>
                              <td class="text-additional small">{{ row.jumlah }}</td>
                              <td class="text-additional small">{{ row.persen }} %</td>
                              <td class="small" :class="row.trenClass">
                                <span v-if="row.tren !== '-'">
                                  <i :class="row.trenIcon"></i> {{ row.tren }}
                                </span>
                                <span v-else>-</span>
                              </td>
                            </tr>
                          </tbody>
                          <tfoot>
                            <tr>
                              <td colspan="4" class="pt-4"><a href="/admin/dashboard/detail?tipe=tbu">Lihat Grafik Selengkapnya</a></td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                      <div class="col-12 col-xl-5 table-responsive text-center">
                        <canvas ref="pieChart_tb" class="mx-auto" style="max-width: 300px; max-height: 300px; min-width: 200px; min-height: 200px;"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-xl-4">
                  <!-- Berat Badan / Tinggi Badan -->
                  <div class="card border border-primary shadow p-3 my-3">
                    <h6 class="fw-bold text-primary">Berat Badan / Tinggi Badan</h6>
                    <div class="row">
                      <div class="col-12 table-responsive">
                        <table class="table table-borderless align-middle">
                          <tbody>
                            <tr>
                              <td class="text-additional fw-bold">Status</td>
                              <td class="text-additional fw-bold">Jumlah</td>
                              <td class="text-additional fw-bold">Persen</td>
                              <td class="text-additional fw-bold">Tren</td>
                            </tr>
                            <tr v-for="(row, index) in dataTable_status" :key="index">
                              <td class="text-additional small">{{ row.status }}</td>
                              <td class="text-additional small">{{ row.jumlah }}</td>
                              <td class="text-additional small">{{ row.persen }} %</td>
                              <td class="small" :class="row.trenClass">
                                <span v-if="row.tren !== '-'">
                                  <i :class="row.trenIcon"></i> {{ row.tren }}
                                </span>
                                <span v-else>-</span>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <div class="col-12 table-responsive text-center">
                        <canvas
                          ref="pieChart_status"
                          class="mx-auto"
                          style="max-width: 300px; max-height: 300px; min-width: 200px; min-height: 200px;"
                        ></canvas>
                        <div class="d-flex flex-wrap justify-content-between mt-3">
                          <a href="/admin/dashboard/detail?tipe=bbtb">Lihat Grafik Selengkapnya</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Ringkasan -->
              <div class="container-fluid mt-3">
                <div class="row">
                  <div class="col-12 d-flex justify-content-between mb-2">
                    <h5 class="fw-bold text-primary">Anak Dengan Masalah Gizi Ganda</h5>
                    <select v-model="filterPeriode" @change="renderRingkasan(filterPeriode)" class="form-select w-auto">
                      <option :value="3">3 Bulan Terakhir</option>
                      <option :value="6">6 Bulan Terakhir</option>
                      <option :value="9">9 Bulan Terakhir</option>
                      <option :value="12">1 Tahun Terakhir</option>
                    </select>
                  </div>

                  <!-- CARD UTAMA -->
                  <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-4">

                     <!-- HEADER -->
                      <div class="text-center position-relative mb-0">
                        <h6
                          class="fw-bold text-white pt-2 pb-5 px-2 rounded-bottom-5 d-inline-block bg-primary "
                          style="width: 55% !important;"
                        >
                          {{ totalAnak }} dengan Masalah Gizi Ganda
                        </h6>

                        <!-- TAB NAV -->
                        <div class="container position-relative" style="margin-top: -2.5rem;">
                          <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2">
                            <button
                              class="w-25 text-truncate fw-semibold rounded-pill border border-danger bg-light shadow-sm btn btn-outline-danger text-danger"
                              style="border-bottom-width: 5px !important;"
                              @click="toggleSudah(false)"
                            >
                              Anak belum dapat Intervensi <br> {{ belum }}
                            </button>

                            <button
                              class="w-25 text-truncate fw-semibold rounded-pill border border-primary bg-light shadow-sm btn btn-outline-primary text-primary"
                              style="border-bottom-width: 5px !important;"
                              @click="toggleSudah(true)"
                            >
                              Anak sudah dapat Intervensi <br> {{ sudah }}
                            </button>
                          </div>
                        </div>

                      </div>

                      <!-- ISI GRID -->
                      <div class="row g-3 mt-3">
                        <!-- KIRI ATAS -->
                        <div class="col-md-4 col-sm-12">
                          <div class="card shadow-sm border-0 h-100 p-3 d-flex flex-column justify-content-between">
                            <div>
                              <h6 class="text-center text-success mb-2">
                                Jumlah anak tidak membaik<br />
                                <span class="fw-semibold">dalam {{ filterPeriode }} bulan terakhir</span>
                              </h6>
                            </div>
                            <div class="chart-placeholder text-muted text-center mt-auto">
                              <canvas ref="lineChart" style="max-height: 250px;"></canvas>
                            </div>
                          </div>
                        </div>

                        <!-- TENGAH ATAS -->
                        <div class="col-md-4 col-sm-12">
                          <div class="card shadow-sm border-0 h-100 p-3 d-flex flex-column justify-content-between">
                            <h6 class="text-center text-success mb-2">Diagram Intervensi</h6>
                            <div class="chart-placeholder text-muted text-center py-4">
                              <canvas v-if="isSudah" ref="sudahChart"></canvas>
                              <canvas v-else ref="funnelChart"></canvas>
                            </div>
                          </div>
                        </div>

                        <!-- KANAN ATAS -->
                        <div class="col-md-4 col-sm-12">
                          <div class="card shadow-sm border-0 h-100 p-3 d-flex flex-column justify-content-between">
                            <h6 class="text-center text-success mb-2">
                              Top 5 Posyandu<br />
                              <span class="fw-semibold">dengan anak tidak membaik dalam {{filterPeriode}} bulan terakhir</span>
                            </h6>
                            <div class="chart-placeholder text-muted text-center py-4">
                              <canvas ref="barChart"></canvas>
                            </div>
                          </div>
                        </div>

                        <!-- BAWAH: TABEL -->
                        <div class="card shadow-sm border-0 h-100 p-3 table-responsive">
                          <div v-if="isSudah">
                            <table class="table table-striped table-sm align-middle p-2">
                              <thead class="table-success">
                                <tr>
                                  <th class="text-center p-2">No</th>
                                  <th class="text-center p-2" width="300">Nama</th>
                                  <th class="text-center p-2">Jenis Intervensi</th>
                                  <th class="text-center p-2">Stunting</th>
                                  <th class="text-center p-2">Wasting</th>
                                  <th class="text-center p-2">Underweight</th>
                                  <th class="text-center p-2">BB Sangat</th>
                                  <th class="text-center p-2">Overweight</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="(anak, i) in paginatedChildren" :key="i">
                                  <td class="text-center">{{ (currentPage - 1) * perPage + i + 1 }}</td>
                                  <td>{{ anak.nama }}</td>
                                  <td class="text-center">{{ anak.rumusan }}</td>
                                  <td class="text-center"><i v-if="anak.stunting" class="bi bi-check2"></i></td>
                                  <td class="text-center"><i v-if="anak.wasting" class="bi bi-check2"></i></td>
                                  <td class="text-center"><i v-if="anak.underweight" class="bi bi-check2"></i></td>
                                  <td class="text-center"><i v-if="anak.bb_sangat" class="bi bi-check2"></i></td>
                                  <td class="text-center"><i v-if="anak.overweight" class="bi bi-check2"></i></td>
                                </tr>
                              </tbody>
                              <tfoot>
                                <tr>
                                  <td colspan="100%" class="text-end">
                                    <button
                                      class="btn btn-sm btn-outline-primary p-2 mt-2"
                                      @click="exportToCSV(true)"
                                    >
                                      <i class="bi bi-file-earmark-excel text-primary me-1"></i>
                                      Export CSV
                                    </button>
                                  </td>
                                </tr>
                              </tfoot>
                            </table>
                            <!-- Pagination -->
                            <nav>
                              <ul class="pagination justify-content-center">
                                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                  <button class="page-link" @click="currentPage--">Previous</button>
                                </li>
                                <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: currentPage === page }">
                                  <button class="page-link" @click="currentPage = page">{{ page }}</button>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                  <button class="page-link" @click="currentPage++">Next</button>
                                </li>
                              </ul>
                            </nav>
                          </div>
                          <div v-else>
                            <table class="table table-striped table-sm align-middle p-2">
                              <thead class="table-success">
                                <tr>
                                  <th class="text-center p-2">No</th>
                                  <th class="text-center p-2" width="300">Nama</th>
                                  <th class="text-center p-2">Jenis Intervensi</th>
                                  <th class="text-center p-2">Stunting</th>
                                  <th class="text-center p-2">Wasting</th>
                                  <th class="text-center p-2">Underweight</th>
                                  <th class="text-center p-2">BB Sangat</th>
                                  <th class="text-center p-2">Overweight</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="(anak, i) in paginatedChildren_belum" :key="i">
                                  <td class="text-center">{{ (currentPage - 1) * perPage + i + 1 }}</td>
                                  <td>{{ anak.nama }}</td>
                                  <td class="text-center">{{ anak.rumusan }}</td>
                                  <td class="text-center"><i v-if="anak.stunting" class="bi bi-check2"></i></td>
                                  <td class="text-center"><i v-if="anak.wasting" class="bi bi-check2"></i></td>
                                  <td class="text-center"><i v-if="anak.underweight" class="bi bi-check2"></i></td>
                                  <td class="text-center"><i v-if="anak.bb_sangat" class="bi bi-check2"></i></td>
                                  <td class="text-center"><i v-if="anak.overweight" class="bi bi-check2"></i></td>
                                </tr>
                              </tbody>
                              <tfoot>
                                <tr>
                                  <td colspan="100%" class="text-end">
                                    <button
                                      class="btn btn-sm btn-outline-primary p-2 mt-2"
                                      @click="exportToCSV(false)"
                                    >
                                      <i class="bi bi-file-earmark-excel text-primary me-1"></i>
                                      Export CSV
                                    </button>
                                  </td>
                                </tr>
                              </tfoot>
                            </table>
                            <!-- Pagination -->
                            <nav>
                              <ul class="pagination justify-content-center">
                                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                  <button class="page-link" @click="currentPage--">Previous</button>
                                </li>
                                <li class="page-item" v-for="page in totalPages_belum" :key="page" :class="{ active: currentPage === page }">
                                  <button class="page-link" @click="currentPage = page">{{ page }}</button>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages_belum }">
                                  <button class="page-link" @click="currentPage++">Next</button>
                                </li>
                              </ul>
                            </nav>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <!-- Tab 2 -->
            <div class="tab-pane fade" id="bumil-tab-pane" role="tabpanel" tabindex="0">
              <!-- Issue and Stat Card -->
              <div class="container-fluid my-2 row">
                <div class="d-flex flex-column">
                  <!-- judul tetap di atas -->
                  <h5 class="fw-bold text-primary mb-3">Ringkasan Status Gizi Bulan Ini</h5>

                  <!-- row isi gizi & total anak -->
                  <div class="row flex-grow-1 align-items-center">
                    <!-- GIZI CARDS -->
                    <div class="row justify-content-center">
                      <div
                        v-for="(item, index) in kesehatan_bumil"
                        :key="index"
                        class="col-xl-2 col-lg-2 col-md-2 col-sm-6 col-12 mb-3"
                      >
                        <div
                          class="card shadow-sm border-0 rounded-3 overflow-hidden h-100"
                          :class="`border-start border-4 border-${item.color}`"
                        >
                          <div class="card-body d-flex justify-content-between">
                            <div>
                              <h6 class="fw-bold mb-1">{{ item.title }}</h6>
                              <p class="mb-2 small" :class="`text-${item.color}`">{{ item.percent }}</p>
                            </div>
                            <h3 class="fw-bold mb-0" :class="`text-${item.color}`">{{ item.value }}</h3>
                          </div>

                          <!-- SVG LINE CHART -->
                          <div class="card-footer bg-transparent border-0 pt-0">
                            <svg
                              viewBox="0 0 100 30"
                              class="svg-line"
                              preserveAspectRatio="none"
                              :class="`stroke-${item.color}`"
                            >
                              <polyline
                                fill="none"
                                stroke-width="2"
                                stroke-linecap="round"
                                points="0,20 15,15 30,18 45,10 60,12 75,8 90,15 100,10"
                              />
                            </svg>
                          </div>
                        </div>
                      </div>

                      <!-- TOTAL IBU HAMIL -->
                      <div class="col-xl-4 col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
                        <div class="card text-center shadow-sm border rounded-3 h-100">
                          <div class="card-body d-flex flex-column justify-content-center">
                            <h6 class="text-muted fw-bold mb-2">Total Ibu Hamil</h6>
                            <h2 class="fw-bold text-success mb-2">{{ totalBumil }}</h2>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>

                <div class="container-fluid">
                  <h5 class="fw-bold text-primary mb-3">Status Kesehatan Ibu Hamil</h5>

                  <!-- Row utama: tabel & chart berdampingan -->
                  <div class="row g-3">
                    <!-- Table -->
                    <div class="col-12 col-xl-8">
                      <div class="card border border-primary shadow p-3 h-100">
                        <div class="table-responsive">
                          <table class="table table-borderless align-middle">
                            <thead>
                              <tr class="fw-semibold text-additional">
                                <th class="text-additional">Status</th>
                                <th class="text-additional">Jumlah</th>
                                <th class="text-additional">Persen</th>
                                <th class="text-additional">Tren</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="(row, index) in dataTable_bumil" :key="index">
                                <td class="text-additional small">{{ row.status }}</td>
                                <td class="text-additional small">{{ row.jumlah ?? 0 }}</td>
                                <td class="text-additional small">
                                  {{ row.persen ? row.persen + ' %' : '0 %' }}
                                </td>
                                <td class="small" :class="row.trenClass">
                                  <span v-if="row.tren && row.tren !== '-'">
                                    <i :class="row.trenIcon"></i> {{ row.tren }}
                                  </span>
                                  <span v-else>-</span>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <!-- Canvas Chart -->
                    <div class="col-12 col-xl-4">
                      <div class="card border border-primary shadow p-3 h-100 d-flex align-items-center justify-content-center">
                        <canvas ref="bumilChart" style="width: 100%; height: 280px !important;"></canvas>
                      </div>
                    </div>
                  </div>

                  <!-- Tabel tambahan bawah -->
                  <div class="row mt-4">
                    <div class="col-12">
                      <div class="card border border-primary shadow p-3">
                        <div class="table-responsive">
                          <table class="table table-bordered table-sm align-middle text-center mb-0">
                            <thead class="table-light">
                              <tr>
                                <th>Indikator</th>
                                <th v-for="(bulan, idx) in bulanLabels" :key="idx">{{ bulan }}</th>
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

                <!-- Ringkasan -->
                <div class="container-fluid mt-3">
                  <div class="row">
                    <div class="col-12 d-flex justify-content-start mb-2">
                      <h5 class="fw-bold text-primary">Ibu Hamil dengan masalah Kesehatan Ganda</h5>
                    </div>

                    <!-- CARD UTAMA -->
                    <div class="col-12">
                      <div class="card shadow-sm border-0 rounded-4">

                      <!-- HEADER -->
                        <div class="text-center position-relative mb-0">
                          <h6
                            class="fw-bold text-white pt-2 pb-5 px-2 rounded-bottom-5 d-inline-block bg-primary "
                            style="width: 55% !important;"
                          >
                            {{ totalBumil }} dengan Masalah Gizi Ganda
                          </h6>

                          <!-- TAB NAV -->
                          <div class="container position-relative" style="margin-top: -2.5rem;">
                            <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2">
                              <button
                                class="w-25 text-truncate fw-semibold rounded-pill border border-danger bg-light shadow-sm btn btn-outline-danger text-danger"
                                style="border-bottom-width: 5px !important;"
                                @click="toggleSudahBumil(false)"
                              >
                                Ibu Hamil belum dapat Intervensi <br> {{ belumBumil }}
                              </button>

                              <button
                                class="w-25 text-truncate fw-semibold rounded-pill border border-primary bg-light shadow-sm btn btn-outline-primary text-primary"
                                style="border-bottom-width: 5px !important;"
                                @click="toggleSudahBumil(true)"
                              >
                                Ibu Hamil sudah dapat Intervensi <br> {{ sudahBumil }}
                              </button>
                            </div>
                          </div>

                        </div>

                        <!-- ISI GRID -->
                        <div class="row g-2 mt-3">
                          <!-- KIRI ATAS -->
                          <div class="col-md-6 col-sm-12">
                            <div class="card shadow-sm border-0 h-100 p-3 d-flex flex-column justify-content-between">
                              <div>
                                <h6 class="text-center text-success mb-2">
                                  Grafik tren Ibu Hamil
                                </h6>
                              </div>
                              <div class="chart-placeholder text-muted text-center mt-auto">
                                <canvas ref="bumilTrendChart" style="max-height: 280px; min-height: 200px !important;height: 100% !important;width: 100% !important;"></canvas>
                              </div>
                            </div>
                          </div>

                          <!-- TENGAH ATAS -->
                          <div class="col-md-6 col-sm-12">
                            <div class="card shadow-sm border-0 h-100 p-3 d-flex flex-column justify-content-between">
                              <h6 class="text-center text-success mb-2">Diagram Intervensi</h6>
                              <div class="chart-placeholder text-muted text-center py-4">
                                <canvas v-if="isSudahBumil" ref="sudahBumilChart"></canvas>
                                <canvas v-else ref="belumBumilChart"></canvas>
                              </div>
                            </div>
                          </div>

                          <!-- BAWAH: TABEL -->
                          <div class="card shadow-sm border-0 h-100 p-3 table-responsive">
                            <div v-if="isSudah">
                              <table class="table table-striped table-sm align-middle p-2">
                                <thead class="table-success">
                                  <tr>
                                    <th class="text-center p-2">No</th>
                                    <th class="text-center p-2" width="300">Nama</th>
                                    <th class="text-center p-2">Anemia</th>
                                    <th class="text-center p-2">Kehamilan Berisiko</th>
                                    <th class="text-center p-2">KEK</th>
                                    <th class="text-center p-2">Intervensi</th>
                                    <th class="text-center p-2">RT</th>
                                    <th class="text-center p-2">RW</th>
                                    <th class="text-center p-2">Usia (Tahun)</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr v-for="(ibu, i) in paginatedBumil" :key="i">
                                    <td class="text-center">{{ (currentPage - 1) * perPage + i + 1 }}</td>
                                    <td>{{ ibu.nama }}</td>
                                    <td class="text-center"><i v-if="ibu.anemia" class="bi bi-check2"></i></td>
                                    <td class="text-center"><i v-if="ibu.berisiko" class="bi bi-check2"></i></td>
                                    <td class="text-center"><i v-if="ibu.kek" class="bi bi-check2"></i></td>
                                    <td class="text-center">{{ ibu.intervensi }}</td>
                                    <td class="text-center">{{ ibu.rt }}</td>
                                    <td class="text-center">{{ibu.rw}}</td>
                                    <td class="text-center">{{ibu.usia}}</td>
                                  </tr>
                                </tbody>
                                <tfoot>
                                  <tr>
                                    <td colspan="100%" class="text-end">
                                      <button
                                        class="btn btn-sm btn-outline-primary p-2 mt-2"
                                        @click="exportToCSV(true)"
                                      >
                                        <i class="bi bi-file-earmark-excel text-primary me-1"></i>
                                        Export CSV
                                      </button>
                                    </td>
                                  </tr>
                                </tfoot>
                              </table>
                              <!-- Pagination -->
                              <nav>
                                <ul class="pagination justify-content-center">
                                  <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                    <button class="page-link" @click="currentPage--">Previous</button>
                                  </li>
                                  <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: currentPage === page }">
                                    <button class="page-link" @click="currentPage = page">{{ page }}</button>
                                  </li>
                                  <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <button class="page-link" @click="currentPage++">Next</button>
                                  </li>
                                </ul>
                              </nav>
                            </div>
                            <div v-else>
                              <table class="table table-striped table-sm align-middle p-2">
                                <thead class="table-success">
                                  <tr>
                                    <th class="text-center p-2">No</th>
                                    <th class="text-center p-2" width="300">Nama</th>
                                    <th class="text-center p-2">Jenis Intervensi</th>
                                    <th class="text-center p-2">Stunting</th>
                                    <th class="text-center p-2">Wasting</th>
                                    <th class="text-center p-2">Underweight</th>
                                    <th class="text-center p-2">BB Sangat</th>
                                    <th class="text-center p-2">Overweight</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr v-for="(anak, i) in paginatedChildren_belum" :key="i">
                                    <td class="text-center">{{ (currentPage - 1) * perPage + i + 1 }}</td>
                                    <td>{{ anak.nama }}</td>
                                    <td class="text-center">{{ anak.rumusan }}</td>
                                    <td class="text-center"><i v-if="anak.stunting" class="bi bi-check2"></i></td>
                                    <td class="text-center"><i v-if="anak.wasting" class="bi bi-check2"></i></td>
                                    <td class="text-center"><i v-if="anak.underweight" class="bi bi-check2"></i></td>
                                    <td class="text-center"><i v-if="anak.bb_sangat" class="bi bi-check2"></i></td>
                                    <td class="text-center"><i v-if="anak.overweight" class="bi bi-check2"></i></td>
                                  </tr>
                                </tbody>
                                <tfoot>
                                  <tr>
                                    <td colspan="100%" class="text-end">
                                      <button
                                        class="btn btn-sm btn-outline-primary p-2 mt-2"
                                        @click="exportToCSV(false)"
                                      >
                                        <i class="bi bi-file-earmark-excel text-primary me-1"></i>
                                        Export CSV
                                      </button>
                                    </td>
                                  </tr>
                                </tfoot>
                              </table>
                              <!-- Pagination -->
                              <nav>
                                <ul class="pagination justify-content-center">
                                  <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                    <button class="page-link" @click="currentPage--">Previous</button>
                                  </li>
                                  <li class="page-item" v-for="page in totalPages_belum" :key="page" :class="{ active: currentPage === page }">
                                    <button class="page-link" @click="currentPage = page">{{ page }}</button>
                                  </li>
                                  <li class="page-item" :class="{ disabled: currentPage === totalPages_belum }">
                                    <button class="page-link" @click="currentPage++">Next</button>
                                  </li>
                                </ul>
                              </nav>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab 3 -->
            <div class="tab-pane fade" id="catin-tab-pane" role="tabpanel" tabindex="0">
              <!-- Issue and Stat Card -->
              <div class="container-fluid my-2 row">
                <!-- judul tetap di atas -->
                  <h5 class="fw-bold text-success mb-3 text-center">Ringkasan Status Kesehatan Calon Pengantin</h5>

                  <!-- row isi gizi & total anak -->
                  <div class="row flex-grow-1 align-items-center">
                    <!-- GIZI CARDS -->
                    <div class="col-lg-8 col-xl-10 col-md-6 col-sm-12">
                      <div class="row justify-content-center">
                        <div
                          v-for="(item, index) in kesehatan_catin"
                          :key="index"
                          class="col-xl-4 col-lg-6 col-sm-6 col-12 mb-3"
                        >
                          <div
                            class="card shadow-sm border-0 rounded-3 overflow-hidden"
                            :class="`border-start border-4 border-${item.color}`"
                          >
                            <div class="card-body py-3 d-flex justify-content-between">
                              <div>
                                <h6 class="fw-bold mb-1">{{ item.title }}</h6>
                                <p class="mb-2 small" :class="`text-${item.color}`">{{ item.percent }}</p>
                              </div>
                              <h3 class="fw-bold mb-0" :class="`text-${item.color}`">{{ item.value }}</h3>
                            </div>

                            <!-- SVG LINE CHART -->
                            <div class="card-footer bg-transparent border-0 pt-0">
                              <svg
                                viewBox="0 0 100 30"
                                class="svg-line"
                                preserveAspectRatio="none"
                                :class="`stroke-${item.color}`"
                              >
                                <polyline
                                  fill="none"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  points="0,20 15,15 30,18 45,10 60,12 75,8 90,15 100,10"
                                />
                              </svg>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- TOTAL ANAK -->
                    <div class="col-lg-4 col-xl-2 col-md-6 col-sm-12 d-flex align-items-end">
                      <div class="card text-center shadow-sm border p-2 w-100">
                        <h6 class="text-muted my-4 fw-bold">Total Calon Pengantin</h6>
                        <div class="flex-grow-1 d-flex flex-column justify-content-center">
                          <h2 class="fw-bold text-success mb-0">{{totalCatin}}</h2>
                        </div>
                        <i class="bi bi-people fs-3 text-dark mt-2 mb-3"></i>
                      </div>
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

<style scoped>
  .sticky-filter {
    position: sticky;
    top: 75px; /* sesuaikan dengan tinggi HeaderAdmin kamu */
    z-index: 1020; /* supaya tetap di atas card/chart */
    background: #f8f9fa; /* warna bg-light */
  }

  .filter-wrapper {
    position: sticky;
    top: 70px;
    z-index: 1050;
    background-color: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  }

  .active-tab {
    background-color: var(--bs-primary) !important;
    color: white !important;
  }
  .chart-placeholder {
    background-color: #f8f9fa;
    border-radius: 10px;
    min-height: 150px;
  }

  .svg-line {
    width: 100%;
    height: 40px;
    opacity: 0.9;
  }

  .border-violet { border-color: #4f0891 !important; } /* Overweight - Hitam */
  .text-violet {color: #4f0891 !important;}

  /* === Warna garis sesuai item.color === */
  .stroke-danger polyline {
    stroke: #dc3545;
  }
  .stroke-warning polyline {
    stroke: #ffc107;
  }
  .stroke-success polyline {
    stroke: #198754;
  }
  .stroke-info polyline {
    stroke: #0dcaf0;
  }
  .stroke-dark polyline {
    stroke: #343a40;
  }
  .stroke-violet polyline {
    stroke: #4f0891;
  }
  /* Hover efek */
  .card:hover .svg-line polyline {
    opacity: 1;
    stroke-width: 3;
    transition: all 0.3s ease;
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
  .col-lg-2 {
    flex: 0 0 auto;
    width: 20%; /* 5 kolom */
  }
}

@media (min-width: 768px) and (max-width: 991.98px) {
  .col-md-3 {
    flex: 0 0 auto;
    width: 25%; /* 4 kolom */
  }
}

@media (max-width: 767.98px) {
  .col-sm-4 {
    flex: 0 0 auto;
    width: 33.33%; /* 3 kolom */
  }
}

.stat-card {
  background-color: #fff;
  border-top: 4px solid var(--bs-secondary);
  height: 90px; /* proporsional */
  transition: all 0.2s ease-in-out;
  min-width: 120px;

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
    font-family: "Inter", sans-serif;
    font-size: 0.75rem;
    margin: 0;
  }

  h4 {
    font-family: "Inter", sans-serif;
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
  h6 {
    font-family: 'Inter', sans-serif;
    font-size: 0.75rem;
    line-height: 1.1;
    margin: 0;
  }

  h4 {
    font-family: 'Inter', sans-serif;
    font-size: 1.25rem;
    line-height: 1;
    color: #000;
    margin: 0;
  }

  .spacer {
    flex: 1;
  }
}


</style>

<script>
import CopyRight from '@/components/CopyRight.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
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

// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;

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
export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Dashboard',
  components: { NavbarAdmin, CopyRight, HeaderAdmin, Welcome },
  data() {
    return {
      indikatorBumil:[],
      indikatorData:[],
      intervensiData: [],
      sudahBumilChart:null,
      belumBumilChart:null,
      bumilChart: null,
      tab:'anak',
      isSudahBumil: false,
      isSudah: false,
      bulanLabels: [], // diisi daftar bulan
      rawData: [], // data asli anak
      filteredData: [], // data hasil filter
      tipeMenu: 'anak', // default tab
      gizi: [],
      totalBumil: 0,
      totalCatin: 0,
      totalAnak: 0,
      sudah: 0,
      belum: 0,
      sudahBumil: 0,
      belumBumil: 0,
      anakMasalah: [],
      filterPeriode: 3,
      lineChart: null,
      funnelChart: null,
      sudahChart:null,
      barChart: null,
      currentPage: 1,
      perPage: 5,
      infoBoxes: [],
      configCacheKey: 'site_config_cache',
      isLoading: true,
      isCollapsed: false,
      username: '',
      today: '',
      thisMonth:'',
      kelurahan: '',
      windowWidth: window.innerWidth,
      stats: [],
      children:[],
      bride:[],
      filters: {
        kelurahan: '',
        posyandu: '',
        rw: '',
        rt: '',
        periode: '',
      },
      dev:0,
      posyanduList: [],
      rwList: [],
      rtList: [],
      periodeOptions: [],
      rwReadonly: true,
      rtReadonly: true,
      kesehatan_catin:[],
      kesehatan_bumil:[],
      intervensi_bumil:[],
    }
  },
  methods: {
    menu(tipe = 'anak') {
      this.tipeMenu = tipe;

      if (tipe === 'anak') this.hitungStatusGizi();
      else if (tipe === 'bumil') this.hitungStatusBumil();
      else if (tipe === 'catin') this.hitungStatusCatin();
    },
    //Data Bumil
    async generateDataTableBumil() {
      try {
        const res = await axios.get(`${baseURL}/api/pregnancy/tren`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        });
        //console.log('debug datatable bumil:', res.data);

        this.dataTable_bumil = res.data.dataTable_bumil || [];
      } catch (e) {
        this.showError('Error Ambil Data', e);
      }
    },
    async hitungStatusBumil() {
      try {
        const res = await axios.get(`${baseURL}/api/pregnancy/status`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })
        const data = res.data
        //console.log('📊 hitungStatusKesehatan ->', data)
        const total = data.total || 0

        this.kesehatan_bumil = data.counts.map(item => ({
          title: item.title,
          value: item.value,
          percent: total > 0 ? ((item.value / total) * 100).toFixed(1) + '%' : '0%',
          color: item.color,
        }))
        this.totalBumil = total

      } catch (e) {
        console.error('❌ hitungStatusKesehatan error:', e)
      }
    },
    async generateIndikatorBumilBulanan() {
      try {
        this.isLoading = true;

        const params = {
          kelurahan: this.filters.kelurahan || '',
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          periode: this.filters.periode || '', // contoh: 'Agu 2025'
        };

        const res = await axios.get(`${baseURL}/api/pregnancy/indikator-bulanan`, {
          params,
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })
        const { labels, indikator } = res.data || {};

        // kalau backend kirim kosong, tetap buat struktur default
        if (!labels?.length || !indikator) {
          this.bulanLabels = this.getLast12Months();
          this.indikatorData = {
            KEK: Array(12).fill(0),
            Anemia: Array(12).fill(0),
            Berisiko: Array(12).fill(0),
            Normal: Array(12).fill(0),
          };
          return;
        }

        this.bulanLabels = labels;
        this.indikatorData = indikator;

        // render chart
        this.$nextTick(() => {
          this.renderBumilTrendChart();
        });
        //console.log('✅ indikatorData:', this.indikatorData);
      } catch (err) {
        console.error('❌ Gagal memuat indikator bumil bulanan:', err);
        this.bulanLabels = this.getLast12Months();
        this.indikatorData = {
          KEK: Array(12).fill(0),
          Anemia: Array(12).fill(0),
          Berisiko: Array(12).fill(0),
          Normal: Array(12).fill(0),
        };
      } finally {
        this.isLoading = false;
      }
    },
    async loadIntervensiBumil() {
      try {
        this.isLoading = true

        const params = {
          kelurahan: this.filters.kelurahan || '',
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          periode: this.filters.periode || '', // contoh: 'Agu 2025'
        }

        const res = await axios.get(`${baseURL}/api/pregnancy/intervensi-summary`, {
          params,
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        // backend kirim dalam field dataTable_bumil
        const list = res.data?.dataTable_bumil || []
        const interv = list
        const summary = {
          KEK: { sudahBumil: 0, belumBumil: 0 },
          ANEMIA: { sudahBumil: 0, belumBumil: 0 },
          BERISIKO: { sudahBumil: 0, belumBumil: 0 },
        }

        list.forEach((ibu) => {
          // pastikan nilai huruf kecil biar aman (Ya / ya / YA)
          const kek = (ibu.kek || '').toLowerCase() === 'ya'
          const anemia = (ibu.anemia || '').toLowerCase() === 'ya'
          const risti = (ibu.risti || '').toLowerCase() === 'ya'

          // intervensi
          const kekInterv = (ibu.kek_intervensi || '').toLowerCase() === 'ya'
          const anemiaInterv = (ibu.anemia_intervensi || '').toLowerCase() === 'ya'
          const ristiInterv = (ibu.risti_intervensi || '').toLowerCase() === 'ya'

          if (kek) kekInterv ? summary.KEK.sudahBumil++ : summary.KEK.belumBumil++
          if (anemia) anemiaInterv ? summary.ANEMIA.sudahBumil++ : summary.ANEMIA.belumBumil++
          if (risti) ristiInterv ? summary.BERISIKO.sudahBumil++ : summary.BERISIKO.belumBumil++
        })

        //console.log('📊 Ringkasan intervensi:', summary)
        this.renderBumilChart(summary)
        const { belumBumil, sudahBumil } = this.hitungIntervensiBumil(interv)
        this.belumBumil = belumBumil
        this.sudahBumil = sudahBumil
      } catch (e) {
        console.error('❌ loadIntervensiBumil error:', e)
      } finally {
        this.isLoading = false
      }
    },
    renderBumilChart(summary) {
      const ctx = this.$refs.bumilChart?.getContext('2d')
      if (!ctx) return
      if (this.bumilChart) this.bumilChart.destroy()

       // pastikan summary tidak null
      const safeSummary = summary && Object.keys(summary).length ? summary : {
        KEK: { sudahBumil: 0, belumBumil: 0 },
        ANEMIA: { sudahBumil: 0, belumBumil: 0 },
        BERISIKO: { sudahBumil: 0, belumBumil: 0 },
      }

      const labels = Object.keys(safeSummary)
      const sudahBumil = labels.map((key) => safeSummary[key].sudahBumil)
      const belumBumil = labels.map((key) => safeSummary[key].belumBumil)

      this.bumilChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              label: 'Sudah Dapat Intervensi',
              data: sudahBumil,
              backgroundColor: '#1B5E20',
              borderRadius: 8,
              borderSkipped: false,
            },
            {
              label: 'Belum Dapat Intervensi',
              data: belumBumil,
              backgroundColor: '#C62828',
              borderRadius: 8,
              borderSkipped: false,
            },
          ],
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          scales: {
            x: {
              stacked: true,
              beginAtZero: true,
              ticks: { precision: 0 },
            },
            y: { stacked: true },
          },
          plugins: {
            legend: {
              position: 'bottom',
              labels: { boxWidth: 14, padding: 16 },
            },
            tooltip: {
              callbacks: {
                label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.x}`,
              },
            },
          },
        },
      })
    },
    toggleSudahBumil(val) {
      this.isSudahBumil = val;
    },
    hitungIntervensiBumil(interv = []) {
      if (!Array.isArray(interv) || interv.length === 0)
        return { belumBumil: 0, sudahBumil: 0 }

      let belumBumil = 0
      let sudahBumil = 0

      interv.forEach((intBumil) => {
        // Ambil semua flag intervensi
        const anemia = (intBumil.anemia_intervensi || '').toLowerCase()
        const kek = (intBumil.kek_intervensi || '').toLowerCase()
        const risti = (intBumil.risti_intervensi || '').toLowerCase()

        // Cek apakah sudah ada intervensi
        const punyaIntervensi =
          anemia === 'ya' || kek === 'ya' || risti === 'ya'

        if (punyaIntervensi) {
          sudahBumil++
        } else {
          belumBumil++
        }
      })

      console.log('yang sudah: ',sudahBumil,'yang belum',belumBumil );

      return { belumBumil, sudahBumil }
    },
    renderBumilTrendChart() {
      const ctx = this.$refs.bumilTrendChart?.getContext('2d');

      if (!ctx || !this.indikatorData || !this.bulanLabels) return;

      // hapus chart lama kalau ada
      if (this.bumilTrendChartInstance) {
        this.bumilTrendChartInstance.destroy();
      }

      this.bumilTrendChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: this.bulanLabels,
          datasets: [
            {
              label: 'KEK',
              data: this.indikatorData.KEK || [],
              borderColor: '#e63946', // merah
              backgroundColor: 'transparent',
              tension: 0.3,
              borderWidth: 2,
              pointRadius: 4,
              pointBackgroundColor: '#e63946',
            },
            {
              label: 'Anemia',
              data: this.indikatorData.Anemia || [],
              borderColor: '#d4a017', // kuning
              backgroundColor: 'transparent',
              tension: 0.3,
              borderWidth: 2,
              pointRadius: 4,
              pointBackgroundColor: '#d4a017',
            },
            {
              label: 'Risiko Tinggi',
              data: this.indikatorData.Berisiko || [],
              borderColor: '#3b3bda', // biru
              backgroundColor: 'transparent',
              tension: 0.3,
              borderWidth: 2,
              pointRadius: 4,
              pointBackgroundColor: '#3b3bda',
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: true,
              position: 'bottom',
              labels: {
                boxWidth: 20,
                padding: 15,
              },
            },
            title: {display: false},
          },
          scales: {
            y: {
              beginAtZero: true,
              suggestedMax: 60,
              ticks: { stepSize: 10 },
              grid: { color: '#eee' },
            },
            x: {
              grid: { display: false },
            },
          },
        },
      });
    },

    //Data Catin
    async loadBride() {
      try {
        const res = await axios.get(`${baseURL}/api/bride`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        this.bride = res.data.map(item => {
        const pendamping = item.catin?.pendampingan?.[0] || {} // ambil pendampingan pertama

        return {
          id: item.id,
          tgl_daftar: item.tgl_daftar,
          tgl_rencana_menikah: item.tgl_rencana_menikah,
          rencana_tinggal: item.rencana_tinggal,

          // --- Data Catin Perempuan ---
          nama_perempuan: item.catin?.nama || '',
          nik_perempuan: item.catin?.nik || '',
          pekerjaan_perempuan: item.catin?.pekerjaan || '',
          tgl_lahir_perempuan: item.catin?.tgl_lahir || '',
          usia_perempuan: item.catin?.usia || '',
          hp_perempuan: item.catin?.no_hp || '',

          // --- Data Catin Pria ---
          nama_pria: item.catin?.pasangan?.nama || '',
          nik_pria: item.catin?.pasangan?.nik || '',
          pekerjaan_pria: item.catin?.pasangan?.pekerjaan || '',
          tgl_lahir_pria: item.catin?.pasangan?.tgl_lahir || '',
          usia_pria: item.catin?.pasangan?.usia || '',
          hp_pria: item.catin?.pasangan?.no_hp || '',

          // --- Data Pendampingan ---
          tgl_pemeriksaan: pendamping.tgl_pemeriksaan || '',
          tgl_pendampingan: pendamping.tgl_pendampingan || '',
          dampingan_ke: pendamping.dampingan_ke || '',
          bb: pendamping.bb || '',
          tb: pendamping.tb || '',
          lila: pendamping.lila || '',
          hb: pendamping.hb || '',
          imt: pendamping.imt || '',
          status_hb: pendamping.anemia || '',
          status_gizi: pendamping.kek || '',
          catin_terpapar_rokok: pendamping.catin_terpapar_rokok || '',
          catin_ttd: pendamping.catin_ttd || '',
          punya_riwayat_penyakit: pendamping.punya_riwayat_penyakit || '',
          riwayat_penyakit: pendamping.riwayat_penyakit || '',
          fasilitas_rujukan: pendamping.fasilitas_rujukan || '',
          edukasi: pendamping.edukasi || '',
          pmt: pendamping.pmt || '',
          catatan: pendamping.catatan ?? ''
        }})

      } catch (e) {
        //console.error('Gagal ambil data:', e)
        this.showError('Error Ambil Data', e)
      }
    },
    //Data Anak
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
        //console.log(items);

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

        // Setelah data dimuat, langsung apply filter awal
        this.applyFilter()

        // 🔹 Inisialisasi filteredData
        this.filteredData = [...this.children];

        // 🔹 Hitung total anak dengan usia < 60 bulan
        this.totalAnak = this.children.filter((anak) => anak.usia < 60).length;

        // 🔹 Hitung status gizi awal
        this.hitungStatusGizi();
        this.generateListsFromChildren()
      } catch (e) {
        console.error(e);

        //this.showError('Error Ambil Data Anak', e)
      }
    },
    generateInfoBoxes() {
      let intervensiKurang = 0
      let kasusBaru = 0
      let dataPending = 0
      let maxDiffBulan = 0

      const stuntingByDesa = {}
      const stuntingPerBulan = {} // 🌟 key: 'YYYY-MM', value: jumlah anak stunting
      const data = this.filteredData || []

      // ambil periode filter (format: 'YYYY-MM')
      const periodeFilter = this.filters?.periode
      const periodeDate = periodeFilter ? new Date(periodeFilter + '-01') : null

      data.forEach(child => {
        const posyandu = child.raw?.posyandu || []
        if (posyandu.length === 0) return

        // --- Hitung stunting per bulan per anak
        posyandu.forEach(p => {
          const d = new Date(p.tgl_ukur)
          const ym = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
          const isStunting = ['Stunted', 'Severely Stunted'].includes(p.tbu)
          if (isStunting) {
            stuntingPerBulan[ym] = (stuntingPerBulan[ym] || 0) + 1
            if (child.kelurahan) {
              stuntingByDesa[child.kelurahan] = (stuntingByDesa[child.kelurahan] || 0) + 1
            }
          }
        })

        // sort tanggal
        const sorted = [...posyandu].sort(
          (a, b) => new Date(a.tgl_ukur) - new Date(b.tgl_ukur)
        )

        // cari current (periode sekarang) & prev (bulan sebelumnya)
        let current = null
        let prev = null

        if (periodeDate) {
          // cari data di bulan sesuai filter
          const match = sorted.find(p => {
            const t = new Date(p.tgl_ukur)
            return (
              t.getFullYear() === periodeDate.getFullYear() &&
              t.getMonth() === periodeDate.getMonth()
            )
          })
          if (match) {
            const idx = sorted.indexOf(match)
            current = sorted[idx]
            prev = sorted[idx - 1] || null
          }
        } else {
          current = sorted[sorted.length - 1]
          prev = sorted[sorted.length - 2]
        }

        // --- Intervensi kurang
        const bermasalah =
          ['Underweight', 'Severely Underweight'].includes(current?.bbu) ||
          ['Stunted', 'Severely Stunted'].includes(current?.tbu) ||
          ['Wasted', 'Severely Wasted'].includes(current?.bbtb)

        const intervensi = child.raw?.intervensi || []
        const adaPMT =
          intervensi.some(i => i.jenis?.toUpperCase() === 'PMT') ||
          child.intervensi === 'PMT'

        if (bermasalah && !adaPMT) intervensiKurang++

        // --- Kasus baru
        if (
          prev &&
          !['Stunted', 'Severely Stunted'].includes(prev.tbu) &&
          ['Stunted', 'Severely Stunted'].includes(current?.tbu)
        ) kasusBaru++

        // --- Data pending
        if (current) {
          const refDate = periodeDate || new Date()
          const diffBulan =
            (refDate.getFullYear() - new Date(current.tgl_ukur).getFullYear()) * 12 +
            (refDate.getMonth() - new Date(current.tgl_ukur).getMonth())

          if (diffBulan > maxDiffBulan) maxDiffBulan = diffBulan
          if (diffBulan >= 2) dataPending++
        }
      })

      // --- 🚀 Hitung tren stunting per bulan
      const bulanSorted = Object.keys(stuntingPerBulan).sort()
      let stuntingNow = 0
      let stuntingPrev = 0

      if (periodeDate) {
        const ymNow = `${periodeDate.getFullYear()}-${String(periodeDate.getMonth() + 1).padStart(2, '0')}`
        const ymPrev = `${periodeDate.getFullYear()}-${String(periodeDate.getMonth()).padStart(2, '0')}`
        stuntingNow = stuntingPerBulan[ymNow] || 0
        stuntingPrev = stuntingPerBulan[ymPrev] || 0
      } else if (bulanSorted.length > 0) {
        const last = bulanSorted[bulanSorted.length - 1]
        const prev = bulanSorted[bulanSorted.length - 2]
        stuntingNow = stuntingPerBulan[last] || 0
        stuntingPrev = stuntingPerBulan[prev] || 0
      }

      const naik =
        stuntingPrev === 0 ? 0 : ((stuntingNow - stuntingPrev) / stuntingPrev) * 100

      // --- Desa dengan stunting tertinggi
      let desaTertinggi = this.kelurahan
      if (Object.keys(stuntingByDesa).length > 0) {
        desaTertinggi = Object.entries(stuntingByDesa).sort((a, b) => b[1] - a[1])[0][0]
      }

      function capitalizeWords(str) {
        return str.replace(/\b\w/g, c => c.toUpperCase())
      }

      const bulanTerakhir =
        maxDiffBulan > 1 ? `${maxDiffBulan} bulan terakhir` : 'bulan ini'

      this.infoBoxes = [
        {
          type: 'danger',
          title: `Stunting ${naik >= 0 ? 'naik' : 'turun'} ${Math.abs(naik).toFixed(1)}%`,
          desc: `Dibanding periode sebelumnya, tertinggi di Desa <strong>${capitalizeWords(
            desaTertinggi
          )}</strong>.`,
        },
        {
          type: 'warning',
          title: 'Intervensi',
          desc: `${intervensiKurang} anak bermasalah gizi belum mendapat bantuan.`,
        },
        {
          type: 'info',
          title: 'Kasus baru',
          desc: `${kasusBaru} kasus stunting baru terdeteksi pada periode ini.`,
        },
        {
          type: 'success',
          title: 'Data Pending',
          desc: `${dataPending} anak belum update data pengukuran ${bulanTerakhir}.`,
        },
      ]
    },
    exportToCSV(val) {
      this.isSudah = val;
      const data = this.isSudah ? this.processedChildren : this.processedChildren_belum;

      if (!data.length) {
        alert('Tidak ada data untuk diexport!');
        return;
      }

      // 🔹 Header CSV
      const headers = [
        'No',
        'Nama',
        'Jenis Intervensi',
        'Stunting',
        'Wasting',
        'Underweight',
        'BB Sangat',
        'Overweight',
      ];

      // 🔹 Isi CSV sesuai tabel
      const rows = data.map((anak, index) => [
        (this.currentPage - 1) * this.perPage + index + 1, // Nomor urut tabel
        anak.nama,
        anak.rumusan,
        anak.stunting ? '✔' : '',
        anak.wasting ? '✔' : '',
        anak.underweight ? '✔' : '',
        anak.bb_sangat ? '✔' : '',
        anak.overweight ? '✔' : '',
      ]);

      // 🔹 Gabung jadi string CSV
      const csvContent = [headers.join(','), ...rows.map(r => r.join(','))].join('\n');

      // 🔹 Download otomatis
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      const filename = this.isSudah ? 'anak_sudah_intervensi.csv' : 'anak_belum_intervensi.csv';
      link.href = url;
      link.setAttribute('download', filename);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    toggleSudah(val) {
      this.isSudah = val;
    },
    hitungIntervensi() {
      const data = this.filteredData || []
      if (!data.length) return { belum: 0, dapat: 0 }

      let belum = 0
      let sudah = 0

      data.forEach((anak) => {
        const intervensi = anak.raw?.intervensi || []
        // cek apakah punya intervensi valid
        const punyaIntervensi = intervensi.some((i) => i?.jenis && i.jenis !== 'Belum Mendapatkan Bantuan')

        if (punyaIntervensi) {
          sudah++
        } else {
          belum++
        }
      })

      return { belum, sudah }
    },
    generateDataTableBB() {
      const data = (this.filteredData?.length ? this.filteredData : this.children) || [];
      if (!data.length) return;

      const categories = ['Severely Underweight', 'Underweight', 'Normal', 'Risiko BB Lebih'];
      const perPeriode = {};

      // --- 1️⃣ Kelompokkan data per bulan berdasarkan hasil BBU
      data.forEach(child => {
        const posyandu = child.raw?.posyandu || [];
        posyandu.forEach(p => {
          const t = new Date(p.tgl_ukur);
          const periode = `${t.getFullYear()}-${String(t.getMonth() + 1).padStart(2, '0')}`;
          const status = (p.bbu || 'Tidak diketahui').trim();
          if (!perPeriode[periode]) perPeriode[periode] = {};
          perPeriode[periode][status] = (perPeriode[periode][status] || 0) + 1;
        });
      });

      const periodeList = Object.keys(perPeriode).sort();
      const periodeAktif = this.filters?.periode || periodeList.at(-1);
      const periodeSebelum = periodeList[periodeList.indexOf(periodeAktif) - 1] || null;

      const totalNow = Object.values(perPeriode[periodeAktif] || {}).reduce((a, b) => a + b, 0);
      const totalPrev = Object.values(perPeriode[periodeSebelum] || {}).reduce((a, b) => a + b, 0);

      const buildRow = (status, jumlahNow, jumlahPrev) => {
        const persenNow = totalNow ? ((jumlahNow / totalNow) * 100).toFixed(2) : 0;
        const persenPrev = totalPrev ? ((jumlahPrev / totalPrev) * 100).toFixed(2) : 0;
        const delta = persenNow - persenPrev;

        let trenClass = 'text-muted';
        let trenIcon = '';
        let tren = '-';

        if (delta !== 0) {
          const naik = delta > 0;
          const isNormal = status === 'Normal';
          if (isNormal) {
            trenClass = naik ? 'text-success' : 'text-danger';
          } else {
            trenClass = naik ? 'text-danger' : 'text-additional';
          }
          trenIcon = naik ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill';
          tren = `${naik ? '+' : ''}${delta.toFixed(1)}%`;
        }

        return {
          status,
          jumlah: jumlahNow,
          persen: parseFloat(persenNow),
          tren,
          trenClass,
          trenIcon,
        };
      };

      this.dataTable_bb = categories.map(status => {
        const jumlahNow = perPeriode[periodeAktif]?.[status] || 0;
        const jumlahPrev = perPeriode[periodeSebelum]?.[status] || 0;
        return buildRow(status, jumlahNow, jumlahPrev);
      });

      const allStatuses = new Set(Object.keys(perPeriode[periodeAktif] || {}));
      allStatuses.forEach(status => {
        if (!categories.includes(status)) {
          const jumlahNow = perPeriode[periodeAktif]?.[status] || 0;
          const jumlahPrev = perPeriode[periodeSebelum]?.[status] || 0;
          this.dataTable_bb.push(buildRow(status, jumlahNow, jumlahPrev));
        }
      });
    },

    generateDataTableTB() {
      const data = (this.filteredData?.length ? this.filteredData : this.children) || [];
      if (!data.length) return;

      const categories = ['Severely Stunted', 'Stunted', 'Normal', 'Tinggi'];
      const perPeriode = {};

      data.forEach(child => {
        const posyandu = child.raw?.posyandu || [];
        posyandu.forEach(p => {
          const t = new Date(p.tgl_ukur);
          const periode = `${t.getFullYear()}-${String(t.getMonth() + 1).padStart(2, '0')}`;
          const status = (p.tbu || 'Tidak diketahui').trim();
          if (!perPeriode[periode]) perPeriode[periode] = {};
          perPeriode[periode][status] = (perPeriode[periode][status] || 0) + 1;
        });
      });

      const periodeList = Object.keys(perPeriode).sort();
      const periodeAktif = this.filters?.periode || periodeList.at(-1);
      const periodeSebelum = periodeList[periodeList.indexOf(periodeAktif) - 1] || null;

      const totalNow = Object.values(perPeriode[periodeAktif] || {}).reduce((a, b) => a + b, 0);
      const totalPrev = Object.values(perPeriode[periodeSebelum] || {}).reduce((a, b) => a + b, 0);

      const buildRow = (status, jumlahNow, jumlahPrev) => {
        const persenNow = totalNow ? ((jumlahNow / totalNow) * 100).toFixed(2) : 0;
        const persenPrev = totalPrev ? ((jumlahPrev / totalPrev) * 100).toFixed(2) : 0;
        const delta = persenNow - persenPrev;

        let trenClass = 'text-muted';
        let trenIcon = '';
        let tren = '-';

        if (delta !== 0) {
          const naik = delta > 0;
          const isNormal = status === 'Normal';
          if (isNormal) {
            trenClass = naik ? 'text-success' : 'text-danger';
          } else {
            trenClass = naik ? 'text-danger' : 'text-additional';
          }
          trenIcon = naik ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill';
          tren = `${naik ? '+' : ''}${delta.toFixed(1)}%`;
        }

        return {
          status,
          jumlah: jumlahNow,
          persen: parseFloat(persenNow),
          tren,
          trenClass,
          trenIcon,
        };
      };

      this.dataTable_tb = categories.map(status => {
        const jumlahNow = perPeriode[periodeAktif]?.[status] || 0;
        const jumlahPrev = perPeriode[periodeSebelum]?.[status] || 0;
        return buildRow(status, jumlahNow, jumlahPrev);
      });

      const allStatuses = new Set(Object.keys(perPeriode[periodeAktif] || {}));
      allStatuses.forEach(status => {
        if (!categories.includes(status)) {
          const jumlahNow = perPeriode[periodeAktif]?.[status] || 0;
          const jumlahPrev = perPeriode[periodeSebelum]?.[status] || 0;
          this.dataTable_tb.push(buildRow(status, jumlahNow, jumlahPrev));
        }
      });
    },

    generateDataTableStatus() {
      const data = (this.filteredData?.length ? this.filteredData : this.children) || [];
      if (!data.length) return;

      const categories = [
        'Severely Wasted',
        'Wasted',
        'Normal',
        'Possible risk of Overweight',
        'Overweight',
        'Obesitas',
      ];
      const perPeriode = {};

      data.forEach(child => {
        const posyandu = child.raw?.posyandu || [];
        posyandu.forEach(p => {
          const t = new Date(p.tgl_ukur);
          const periode = `${t.getFullYear()}-${String(t.getMonth() + 1).padStart(2, '0')}`;
          const status = (p.bbtb || 'Tidak diketahui').trim();
          if (!perPeriode[periode]) perPeriode[periode] = {};
          perPeriode[periode][status] = (perPeriode[periode][status] || 0) + 1;
        });
      });

      const periodeList = Object.keys(perPeriode).sort();
      const periodeAktif = this.filters?.periode || periodeList.at(-1);
      const periodeSebelum = periodeList[periodeList.indexOf(periodeAktif) - 1] || null;

      const totalNow = Object.values(perPeriode[periodeAktif] || {}).reduce((a, b) => a + b, 0);
      const totalPrev = Object.values(perPeriode[periodeSebelum] || {}).reduce((a, b) => a + b, 0);

      const buildRow = (status, jumlahNow, jumlahPrev) => {
        const persenNow = totalNow ? ((jumlahNow / totalNow) * 100).toFixed(2) : 0;
        const persenPrev = totalPrev ? ((jumlahPrev / totalPrev) * 100).toFixed(2) : 0;
        const delta = persenNow - persenPrev;

        let trenClass = 'text-muted';
        let trenIcon = '';
        let tren = '-';

        if (delta !== 0) {
          const naik = delta > 0;
          const isNormal = status === 'Normal';
          if (isNormal) {
            trenClass = naik ? 'text-success' : 'text-danger';
          } else {
            trenClass = naik ? 'text-danger' : 'text-additional';
          }
          trenIcon = naik ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill';
          tren = `${naik ? '+' : ''}${delta.toFixed(1)}%`;
        }

        return {
          status,
          jumlah: jumlahNow,
          persen: parseFloat(persenNow),
          tren,
          trenClass,
          trenIcon,
        };
      };

      this.dataTable_status = categories.map(status => {
        const jumlahNow = perPeriode[periodeAktif]?.[status] || 0;
        const jumlahPrev = perPeriode[periodeSebelum]?.[status] || 0;
        return buildRow(status, jumlahNow, jumlahPrev);
      });

      const allStatuses = new Set(Object.keys(perPeriode[periodeAktif] || {}));
      allStatuses.forEach(status => {
        if (!categories.includes(status)) {
          const jumlahNow = perPeriode[periodeAktif]?.[status] || 0;
          const jumlahPrev = perPeriode[periodeSebelum]?.[status] || 0;
          this.dataTable_status.push(buildRow(status, jumlahNow, jumlahPrev));
        }
      });
    },

    hitungStatusGizi() {
      // 🔹 Ambil data anak yang sudah difilter
      const dataAnak = this.filteredData.length ? this.filteredData : this.children;
      const f = this.filters;

      // 🔹 Bikin range periode (jika difilter)
      const [y, m] = f.periode ? f.periode.split('-') : [];
      const periodeNum = f.periode ? parseInt(y) * 100 + parseInt(m) : null;

      const count = {
        Stunting: 0,
        Wasting: 0,
        Underweight: 0,
        Normal: 0,
        'BB Stagnan': 0,
        Overweight: 0,
      };

      let total = 0;

      dataAnak.forEach((anak) => {
        const riwayat = anak.raw?.posyandu || [];
        if (!riwayat.length) return;

        // 🔹 Filter riwayat berdasarkan periode (jika difilter)
        const filteredRiwayat = periodeNum
          ? riwayat.filter((r) => {
              if (!r.tgl_ukur) return false;
              const tgl = new Date(r.tgl_ukur);
              const itemPeriode = tgl.getFullYear() * 100 + (tgl.getMonth() + 1);
              return itemPeriode === periodeNum;
            })
          : riwayat;

        // 🔹 Ambil data terakhir di periode itu
        const latest = filteredRiwayat[filteredRiwayat.length - 1];
        if (!latest) return;

        total++;

        // eslint-disable-next-line no-unused-vars
        const { bbu, tbu, bbtb, bb } = latest;

        if (tbu?.includes('Stunted')) count.Stunting++;
        if (bbtb?.includes('Wasted')) count.Wasting++;
        if (bbu?.includes('Underweight')) count.Underweight++;
        if (bbu === 'Normal' && tbu === 'Normal' && bbtb === 'Normal') count.Normal++;
        if (bbtb?.includes('Overweight')) count.Overweight++;

        // 🔹 Cek stagnan (3 kali BB terakhir sama — dari seluruh riwayat anak)
        if (riwayat.length >= 3) {
          const last3 = riwayat.slice(-3);
          const allEqual = last3.every((r) => r.bb === last3[0].bb);
          if (allEqual) count['BB Stagnan']++;
        }
      });

      // 🔹 Buat array untuk ditampilkan di ringkasan
      this.gizi = Object.entries(count).map(([title, value]) => {
        const percent = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
        const colorMap = {
          Stunting: 'danger',
          Wasting: 'warning',
          Underweight: 'violet',
          Normal: 'success',
          'BB Stagnan': 'info',
          Overweight: 'dark',
        };
        return { title, value, percent, color: colorMap[title] };
      });

      this.totalAnak = total;
    },
    hitungStatusCatin() {
      const dataCatin = this.filteredData.length ? this.filteredData : this.bride;
      //console.log('Data Flatten:', dataCatin)
      const f = this.filters;

      const [y, m] = f.periode ? f.periode.split('-') : [];
      const periodeNum = f.periode ? parseInt(y) * 100 + parseInt(m) : null;

      const count = {
        KEK: 0,
        Anemia: 0,
      };
      let total = 0;

      dataCatin.forEach((ctn) => {
        const riwayat = ctn.raw?.pemeriksaan || [];
        if (!riwayat.length) return;

        const filtered = periodeNum
          ? riwayat.filter((r) => {
              if (!r.tgl_periksa) return false;
              const tgl = new Date(r.tgl_periksa);
              const periode = tgl.getFullYear() * 100 + (tgl.getMonth() + 1);
              return periode === periodeNum;
            })
          : riwayat;

        const latest = filtered[filtered.length - 1];
        if (!latest) return;

        total++;
        if (latest.kek) count.KEK++;
        if (latest.anemia) count.Anemia++;
      });

      this.gizi = Object.entries(count).map(([title, value]) => {
        const percent = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
        return { title, value, percent };
      });
      this.totalCatin = total;
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
    renderRingkasan(periodeBulan = 3) {
      const n = parseInt(periodeBulan) || 3

      // Kirim filter ke semua chart
      this.renderLineChart(n)
      this.renderBarChart(n)
      this.renderFunnelChart(n)
      this.renderSudahChart(n)
    },
    renderLineChart(periodeBulan = 3) {
      const data = this.filteredData || []
      if (!data.length) return

      const now = new Date()
      const startDate = new Date()
      startDate.setMonth(now.getMonth() - periodeBulan + 1) // +1 biar include bulan sekarang

      // 🔸 Filter hanya anak tanpa intervensi (null atau 0)
      const tanpaIntervensi = data.filter(a => a.intervensi == null || a.intervensi == 0)

      // Flatten semua data posyandu dari anak tanpa intervensi
      const allPosyandu = tanpaIntervensi.flatMap((anak) =>
        (anak.raw?.posyandu || []).map((p) => ({
          tanggal: new Date(p.tgl_ukur),
          bb_naik: p.bb_naik,
        }))
      )

      // Filter hanya data dalam periode
      const recent = allPosyandu.filter(
        (p) => p.tanggal >= startDate && p.tanggal <= now
      )

      const monthNames = [
        'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
      ]

      // Siapkan struktur bulan
      const monthlyData = {}
      for (let i = 0; i < periodeBulan; i++) {
        const temp = new Date(startDate)
        temp.setMonth(startDate.getMonth() + i)
        const key = `${temp.getFullYear()}-${String(temp.getMonth() + 1).padStart(2, '0')}`
        monthlyData[key] = { total: 0, tidakMembaik: 0 }
      }

      // Isi data sesuai bulan
      recent.forEach((p) => {
        const key = `${p.tanggal.getFullYear()}-${String(p.tanggal.getMonth() + 1).padStart(2, '0')}`
        if (monthlyData[key]) {
          monthlyData[key].total++
          if (!p.bb_naik) monthlyData[key].tidakMembaik++
        }
      })

      const sortedKeys = Object.keys(monthlyData).sort()
      const labels = sortedKeys.map((key) => {
        const [, month] = key.split('-')
        return monthNames[parseInt(month) - 1]
      })
      const dataTidakMembaik = sortedKeys.map((key) => monthlyData[key].tidakMembaik)

      // Hapus chart lama
      if (this.lineChart) this.lineChart.destroy()

      // Render Chart.js
      const ctx = this.$refs.lineChart.getContext('2d')
      this.lineChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'Jumlah Anak Tidak Membaik (Tanpa Intervensi)',
              data: dataTidakMembaik,
              borderColor: 'goldenrod',
              backgroundColor: 'rgba(255, 215, 0, 0.3)',
              fill: true,
              tension: 0.3,
              pointRadius: 4,
              pointBackgroundColor: 'goldenrod',
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: (ctx) => `${ctx.parsed.y} anak tidak membaik`,
              },
            },
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { stepSize: 1 },
            },
          },
        },
      })
    },
    renderBarChart(periodeBulan = 3) {
      const data = this.filteredData || []
      if (!data.length) return

      // 🔹 Tentukan rentang waktu (default 3 bulan terakhir)
      const now = new Date()
      const startDate = new Date()
      startDate.setMonth(now.getMonth() - periodeBulan)

      // 🔹 Ambil semua entri posyandu dari setiap anak
      const allPosyandu = data.flatMap((anak) =>
        (anak.raw?.posyandu || []).map((p) => ({
          posyandu: p.posyandu || 'Tidak Diketahui',
          tanggal: new Date(p.tgl_ukur),
          bb_naik: p.bb_naik,
        }))
      )

      // 🔹 Ambil daftar unik semua posyandu (agar yang 0 juga muncul)
      const allPosyanduNames = [
        ...new Set(allPosyandu.map((p) => p.posyandu || 'Tidak Diketahui')),
      ]

      // 🔹 Filter hanya data dalam periode waktu
      const recent = allPosyandu.filter(
        (p) => p.tanggal >= startDate && p.tanggal <= now
      )

      // 🔹 Kelompokkan berdasarkan nama posyandu
      const posyanduCounts = {}
      allPosyanduNames.forEach((name) => (posyanduCounts[name] = 0)) // inisialisasi 0

      recent.forEach((p) => {
        const key = p.posyandu || 'Tidak Diketahui'
        if (!p.bb_naik) posyanduCounts[key]++
      })

      // 🔹 Siapkan data untuk Chart.js
      const labels = Object.keys(posyanduCounts)
      const values = Object.values(posyanduCounts)

      // 🔹 Hancurkan chart lama kalau ada
      if (this.barChart) this.barChart.destroy()

      // 🔹 Render chart baru
      const ctx = this.$refs.barChart.getContext('2d')
      this.barChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              label: 'Anak Tidak Membaik',
              data: values,
              backgroundColor: 'rgba(255, 99, 132, 0.6)',
              borderRadius: 8,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            title: {
              display: false,
            },
            tooltip: {
              callbacks: {
                label: (ctx) => `${ctx.parsed.y} anak tidak membaik`,
              },
            },
          },
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Jumlah Anak',
              },
            },
            x: {
              title: {
                display: true,
                text: 'Posyandu',
              },
            },
          },
        },
      })
    },
    renderFunnelChart(periodeBulan = 3) {
      this.$nextTick(() => {
        const canvas = this.$refs.funnelChart
        if (!canvas) return // belum dirender

        const ctx = canvas.getContext('2d')
        const data = this.filteredData || []
        if (!data.length) return

        const now = new Date()
        const startDate = new Date()
        startDate.setMonth(now.getMonth() - periodeBulan)

        // 🔹 Flatten semua intervensi dari semua anak
        const allIntervensi = data.flatMap(anak =>
          (anak.raw?.intervensi || []).map(i => ({
            tanggal: new Date(i.tanggal),
            jenis: i.jenis && i.jenis.trim() !== "" ? i.jenis : "Belum Mendapatkan Bantuan"
          }))
        )

        // 🔹 Filter intervensi dalam periode
        const recentIntervensi = allIntervensi.filter(
          i => i.tanggal >= startDate && i.tanggal <= now
        )

        // 🔹 Daftar jenis tetap (supaya tampil meski 0)
        const jenisList = ['MBG', 'KIE', 'Bansos', 'PMT', 'Bantuan Lainnya', 'Belum Mendapatkan Bantuan']
        const counts = jenisList.map(jenis =>
          recentIntervensi.filter(i => i.jenis === jenis).length
        )

        // 🔹 Hapus chart lama
        if (this.funnelChart) this.funnelChart.destroy()

        // 🔹 Render Chart.js horizontal bar
        this.funnelChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: jenisList,
            datasets: [
              {
                data: counts,
                backgroundColor: [
                  '#006341',
                  '#007d52',
                  '#009562',
                  '#6fa287',
                  '#6d8b7b',
                  '#ea7f7f'
                ],
                color: '#FFFFFF'
              }
            ]
          },
          options: {
            indexAxis: 'y',
            plugins: {
              legend: { display: false },
              datalabels: {
                color: '#FFFFFF',
                anchor: 'center',
                align: 'center',
                font: { weight: 'bold' },
                formatter: value => value || '0'
              },
              title: { display: false }
            },
            scales: { x: { beginAtZero: true } }
          }
        })
      })
    },
    renderSudahChart(periodeBulan = 3) {
      this.$nextTick(() => {
        const canvas = this.$refs.sudahChart
        if (!canvas) return

        const ctx = canvas.getContext('2d')
        const data = this.filteredData || []
        if (!data.length) return

        const now = new Date()
        const startDate = new Date()
        startDate.setMonth(now.getMonth() - periodeBulan)

        // 🔹 Flatten intervensi valid (jenis tidak null/0/kosong)
        const allIntervensi = data.flatMap(anak =>
          (anak.raw?.intervensi || [])
            .filter(i => i && i.jenis && i.jenis !== "0" && i.jenis.trim() !== "")
            .map(i => ({
              tanggal: new Date(i.tanggal),
              jenis: i.jenis
            }))
        )

        // 🔹 Filter dalam periode
        const recentIntervensi = allIntervensi.filter(
          i => i.tanggal >= startDate && i.tanggal <= now
        )

        // 🔹 Pakai daftar tetap supaya muncul meski kosong
        const jenisList = ['MBG', 'KIE', 'Bansos', 'PMT', 'Bantuan Lainnya']
        const counts = jenisList.map(jenis =>
          recentIntervensi.filter(i => i.jenis === jenis).length
        )

        // 🔹 Hapus chart lama
        if (this.sudahChart) this.sudahChart.destroy()

        // 🔹 Render chart
        this.sudahChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: jenisList,
            datasets: [
              {
                data: counts,
                backgroundColor: [
                  '#006341',
                  '#007d52',
                  '#009562',
                  '#6fa287',
                  '#6d8b7b'
                ],
                color: '#FFFFFF'
              }
            ]
          },
          options: {
            indexAxis: 'y',
            plugins: {
              legend: { display: false },
              datalabels: {
                color: '#FFFFFF',
                anchor: 'center',
                align: 'center',
                font: { weight: 'bold' },
                formatter: value => value || '0'
              },
              title: { display: false }
            },
            scales: { x: { beginAtZero: true } }
          }
        })
      })
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
    generateListsFromChildren() {
      if (!this.children.length) return

      // 🔹 Ambil daftar Posyandu unik
      const posyanduSet = new Set(this.children.map(c => c.posyandu).filter(Boolean))
      this.posyanduList = Array.from(posyanduSet).map((nama, index) => ({
        id: index + 1,
        nama_posyandu: nama
      }))

      // 🔹 Jika filter posyandu dipilih, ambil RW & RT berdasarkan posyandu tersebut
      if (this.filters.posyandu) {
        const filteredByPosyandu = this.children.filter(
          c => c.posyandu === this.filters.posyandu
        )

        const rwSet = new Set(filteredByPosyandu.map(c => c.rw).filter(Boolean))
        this.rwList = Array.from(rwSet).map((nama, index) => ({
          id: index + 1,
          nama_rw: nama
        }))

        // Jika filter RW dipilih, ambil RT
        if (this.filters.rw) {
          const filteredByRW = filteredByPosyandu.filter(c => c.rw === this.filters.rw)
          const rtSet = new Set(filteredByRW.map(c => c.rt).filter(Boolean))
          this.rtList = Array.from(rtSet).map((nama, index) => ({
            id: index + 1,
            nama_rt: nama
          }))
        } else {
          this.rtList = []
        }
      } else {
        // Kalau belum pilih posyandu, kosongkan RW & RT
        this.rwList = []
        this.rtList = []
      }
    },
    handlePosyanduChange() {
      const pos = this.filters.posyandu;
      if (!pos) {
        this.rwList = [];
        this.rtList = [];
        this.rwReadonly = true;
        this.rtReadonly = true;
        return;
      }

      const filteredChildren = this.children.filter(c => c.posyandu === pos);

      // Ambil nama_rw & nama_rt dari object
      const rwSet = new Set(
        filteredChildren
          .map(c => {
            if (typeof c.rw === 'object') return c.rw.nama_rw;
            if (typeof c.nama_rw !== 'undefined') return c.nama_rw;
            return c.rw;
          })
          .filter(Boolean)
      );

      const rtSet = new Set(
        filteredChildren
          .map(c => {
            if (typeof c.rt === 'object') return c.rt.nama_rt;
            if (typeof c.nama_rt !== 'undefined') return c.nama_rt;
            return c.rt;
          })
          .filter(Boolean)
      );

      this.rwList = Array.from(rwSet);
      this.rtList = Array.from(rtSet);
      this.rwReadonly = false;
      this.rtReadonly = false;
    },
    handleRwChange() {
      const pos = this.filters.posyandu;
      const rw = this.filters.rw;

      if (!rw) {
        this.rtList = [];
        this.rtReadonly = true;
        return;
      }

      const filteredChildren = this.children.filter(c => {
        const rwVal =
          typeof c.rw === 'object' ? c.rw.nama_rw :
          c.nama_rw ?? c.rw;
        return c.posyandu === pos && rwVal === rw;
      });

      const rtSet = new Set(
        filteredChildren
          .map(c => {
            if (typeof c.rt === 'object') return c.rt.nama_rt;
            if (typeof c.nama_rt !== 'undefined') return c.nama_rt;
            return c.rt;
          })
          .filter(Boolean)
      );

      this.rtList = Array.from(rtSet);
      this.rtReadonly = false;
    },
    generatePeriodeOptions() {
      const bulan = [
        'Januari', 'Februari', 'Maret', 'April',
        'Mei', 'Juni', 'Juli', 'Agustus',
        'September', 'Oktober', 'November', 'Desember'
      ]

      const now = new Date()
      const result = []

      for (let i = 0; i < 12; i++) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
        const value = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}` // ✅ pakai tanda "-"
        const label = `${bulan[d.getMonth()]} ${d.getFullYear()}`
        result.push({ label, value })
      }

      this.periodeOptions = result
      //console.log('Periode options:', this.periodeOptions)

    },
    async applyFilter() {
      const f = this.filters;

      // 🔹 Atur readonly untuk RT/RW sesuai filter aktif
      this.rtReadonly = true
      this.rwReadonly = true

      // 🔹 Pilih dataset sumber sesuai tipe menu
      let sourceData = [];
      switch (this.tipeMenu) {
        case 'anak':
          sourceData = this.children;
          break;
        case 'bumil':
          sourceData = this.bumil;
          break;
        case 'catin':
          sourceData = this.bride;
          break;
      }

      // 🔹 Pastikan dataset ada
      if (!Array.isArray(sourceData) || !sourceData.length) {
        this.filteredData = [];
        return;
      }

      // 🔹 Filter utama (Posyandu, RW, RT, Periode)
      this.filteredData = sourceData.filter(item => {
        const posyanduMatch =
          !f.posyandu ||
          item.posyandu === f.posyandu ||
          item.nama_posyandu === f.posyandu;

        const rwMatch = !f.rw || item.rw === f.rw;
        const rtMatch = !f.rt || item.rt === f.rt;

        const periodeMatch = (() => {
          if (!f.periode) return true;
          const [y, m] = f.periode.split('-').map(Number);
          const periodeNum = y * 100 + m;

          const tanggalKey = this.tipeMenu === 'anak' ? 'tgl_ukur' : 'tgl_pendampingan';
          if (!item[tanggalKey]) return false;

          const [iy, im] = item[tanggalKey].split('-').map(Number);
          const itemPeriode = iy * 100 + im;
          return itemPeriode === periodeNum;
        })();

        return posyanduMatch && rwMatch && rtMatch && periodeMatch;
      });

      // 🔹 Jika tidak ada data hasil filter, reset agar tidak error di render
      if (!this.filteredData.length) {
        console.warn('⚠️ Tidak ada data setelah filter diterapkan');
      }

      // 🔹 Jalankan logika sesuai tipe menu
      if (this.tipeMenu === 'anak') {
        this.totalAnak = this.filteredData.filter(a => a.usia < 60).length;
        this.hitungStatusGizi();
        this.generateDataTableBB();
        this.generateDataTableTB();
        this.generateDataTableStatus();
        this.generateInfoBoxes();
        this.generateListsFromChildren();

        // Render grafik anak
        this.renderChart('pieChart_bb', this.dataTable_bb, [
          '#f5ebb9', '#f7db7f', '#7dae9b', '#bfbbe4', '#e87d7b',
        ]);
        this.renderChart('pieChart_tb', this.dataTable_tb, [
          '#f7db7f', '#bfbbe4', '#7dae9b', '#e87d7b',
        ]);
        this.renderChart('pieChart_status', this.dataTable_status, [
          '#f5ebb9', '#f7db7f', '#7dae9b', '#bfbbe4', '#e87d7b', '#eaafdd',
        ]);
      }

      else if (this.tipeMenu === 'bumil') {
        this.hitungStatusBumil();
        await this.loadIntervensiBumil();
      }

      else if (this.tipeMenu === 'catin') {
        this.hitungStatusCatin();
        this.generateListsFromCatin?.();
        this.renderChart('pieChart_status', this.gizi, [
          '#f5ebb9', '#f7db7f', '#7dae9b',
        ]);
      }
    },
    async fetchStats() {
      try {
        const res = await axios.get(`${baseURL}/api/dashboard/stats`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
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
          { title: 'Anak <= 5 Tahun', value: this.totalAnak, icon: 'fa-solid fa-baby' },
        ]
      } catch (e) {
        console.error(e)
      }
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
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
    renderChart(refName, dataTable, colors, labelKey = 'status', valueKey = 'persen') {
      const ctx = this.$refs[refName]?.getContext('2d');
      if (!ctx) return;

      // Hancurkan chart lama kalau ada
      if (this[refName + 'Instance']) {
        this[refName + 'Instance'].destroy();
      }

      if (!Array.isArray(dataTable) || !dataTable.length) {
        console.warn(`⚠️ Tidak ada data untuk chart ${refName}`);
        return;
      }

      const labels = dataTable.map(row => row[labelKey]);
      const values = dataTable.map(row => parseFloat(row[valueKey]) || 0);

      // Solid colors langsung dari array colors
      const solidColors = colors;

      this[refName + 'Instance'] = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: labels,
          datasets: [
            {
              data: values,
              backgroundColor: solidColors,
              borderWidth: 0, // ✅ tanpa border putih
              hoverOffset: 12,
            },
          ],
        },
        options: {
          responsive: true,
          layout: { padding: 25 },
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.parsed;
                  return value > 0 ? [`${label}`, `${value}%`] : [];
                },
              },
            },
            datalabels: {
            display: ctx => ctx.dataset.data[ctx.dataIndex] > 0,
            color: 'rgb(0, 0, 0)',      // solid hitam
            font: {
              size: 11,
              weight: 'bold',
              opacity: 1,               // pastikan tidak transparan
            },
            backgroundColor: 'rgba(255,255,255,0)', // transparan di belakang teks
            align: 'center',
            anchor: 'center',
            clamp: true,
            offset: 0,
            formatter: (value, ctx) => {
              const label = ctx.chart.data.labels[ctx.dataIndex];
              return [label, `${value}%`];
            },
          },
          },
          onHover: (event, elements) => {
            event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
          },
          onClick: (event, elements) => {
            if (!elements.length) return;
            const index = elements[0].index;
            const item = dataTable[index];
            const status = item.status;

            let tipe = '';
            if (refName === 'pieChart_bb') tipe = 'BB/U';
            else if (refName === 'pieChart_tb') tipe = 'TB/U';
            else if (refName === 'pieChart_status') tipe = 'BB/TB';

            this.selectedChartStatus = status;
            this.selectedChartType = tipe;

            this.$router.push({
              path: '/admin/anak',
              query: { tipe, status },
            });
          },
        },
        plugins: [ChartDataLabels],
      });
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
    this.isLoading = true;

    try {
      await this.$nextTick();
      await this.getWilayahUser();
      await this.loadConfigWithCache();
      this.bulanLabels = this.getLast12Months() // <- generate bulan realtime

      // ⏳ Ambil semua data (anak, bumil, catin)
      await this.loadChildren(); // sudah generateListsFromChildren()
      await this.loadBride(); // untuk catin
      await this.loadBumil?.(); // kalau ada load ibu hamil, opsional
      await this.generateIndikatorBumilBulanan();

      // 🔹 Set menu default
      this.menu('anak'); // otomatis set this.tipeMenu = 'anak' & hitungStatusGizi()

      // 🔹 Jalankan logika tambahan setelah data dasar siap
      const { belum, sudah } = this.hitungIntervensi();
      const { belumBumil, sudahBumil } = this.hitungIntervensiBumil();
      this.belum = belum;
      this.sudah = sudah;
      this.belumBumil = belumBumil;
      this.sudahBumil = sudahBumil;

      // 🔹 Generate filter & tabel awal sesuai tipe menu aktif
      this.generateInfoBoxes();
      this.generateListsFromChildren();
      this.generateDataTableBB();
      this.generateDataTableTB();
      this.generateDataTableStatus();
      this.generateDataTableBumil();

      //await this.generateIndikatorBumilBulanan();
      await this.fetchStats();
      this.generatePeriodeOptions();

      // Pastikan filter kelurahan & periode sudah siap
      this.filters.kelurahan = this.kelurahan;
      if (!this.filters.periode && this.bulanLabels.length)
        this.filters.periode = this.bulanLabels[this.bulanLabels.length - 1]; // ambil bulan terakhir

      await this.loadIntervensiBumil();

      // 🔹 Render chart awal (sesuai tipe menu aktif)
      this.renderChart('pieChart_bb', this.dataTable_bb, [
          '#f5ebb9', '#f7db7f', '#7dae9b', '#bfbbe4', '#e87d7b',
        ]);
        this.renderChart('pieChart_tb', this.dataTable_tb, [
          '#f7db7f', '#bfbbe4', '#7dae9b', '#e87d7b',
        ]);
        this.renderChart('pieChart_status', this.dataTable_status, [
          '#f5ebb9', '#f7db7f', '#7dae9b', '#bfbbe4', '#e87d7b', '#eaafdd',
        ]);

      // 🔹 Grafik tambahan
      this.renderLineChart();
      this.renderBarChart();
      this.renderFunnelChart();
      this.renderSudahChart();

      // 🔹 Simpan hasil awal
      if (this.tipeMenu === 'anak') {
        this.filteredData = [...this.children];
      } else if (this.tipeMenu === 'bumil') {
        this.filteredData = [...this.bumil];
      } else if (this.tipeMenu === 'catin') {
        this.filteredData = [...this.catin];
      }

      this.handleResize();
      window.addEventListener('resize', this.handleResize);

    } catch (err) {
      console.error('Error loading data:', err);
    } finally {
      setTimeout(() => { this.isLoading = false; }, 300);
    }
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
  },
  watch: {
    'filters.posyandu'(val) {
      this.handlePosyanduChange(val)
    },
    isSudah(newVal) {
      this.$nextTick(() => {
        if (newVal) {
          this.renderSudahChart();
        } else {
          this.renderFunnelChart();
        }
      });
    }
  },
  computed: {
    processedChildren() {
      const data = Array.isArray(this.filteredData) && this.filteredData.length
        ? this.filteredData
        : Array.isArray(this.children) ? this.children : [];

      const lastItem = arr => Array.isArray(arr) && arr.length ? arr[arr.length - 1] : {};

      // 🔸 Filter hanya anak dengan intervensi yang memiliki jenis valid (tidak null/kosong/"0")
      const denganIntervensi = data.filter(child => {
        const intervensiList = Array.isArray(child.raw?.intervensi) ? child.raw.intervensi : [];

        // Ambil hanya anak yang punya intervensi dengan jenis tidak null, tidak kosong, dan tidak "0"
        return intervensiList.some(i => i && i.jenis && i.jenis !== "0" && i.jenis.trim() !== "");
      });


      return denganIntervensi.map(child => {
        const intervensiList = Array.isArray(child.raw?.intervensi) ? child.raw.intervensi : [];

        // Ambil semua jenis intervensi unik
        const jenisUnik = [...new Set(
          intervensiList
            .map(i => i.jenis)
            .filter(j => j && j.trim() !== '')
        )];

        // Jika ada, gabungkan dengan koma
        const rumusan = jenisUnik.length ? jenisUnik.join(', ') : '-';

        const lastPosyandu = lastItem(child.raw?.posyandu);

        return {
          nama: child.nama || '-',
          rumusan, // tampilkan semua intervensi unik
          stunting: ['Stunted', 'Severely Stunted'].includes(lastPosyandu.tbu),
          wasting: ['Wasted', 'Severely Wasted'].includes(lastPosyandu.bbtb),
          underweight: ['Underweight', 'Severely Underweight'].includes(lastPosyandu.bbu),
          bb_sangat: lastPosyandu.bbu === 'Severely Underweight',
          overweight: lastPosyandu.bbu === 'Overweight',
        };
      });
    },
    processedChildren_belum() {
      // Pastikan data valid
      const data = Array.isArray(this.filteredData) && this.filteredData.length
        ? this.filteredData
        : Array.isArray(this.children)
          ? this.children
          : [];

      const lastItem = arr => Array.isArray(arr) && arr.length ? arr[arr.length - 1] : {};

      // 🔸 Filter: hanya anak dengan intervensi kosong/null ATAU semua jenis = "0"
      const tanpaIntervensi = data.filter(child => {
        const intervensiList = Array.isArray(child.raw?.intervensi) ? child.raw.intervensi : [];
        // Belum pernah intervensi
        if (intervensiList.length === 0) return true;
        // Semua intervensinya bernilai jenis = "0"
        return intervensiList.every(i => !i || i.jenis === "0" || i.jenis == null || i.jenis.trim() === "");
      });

      // 🔸 Lanjut proses data yang tersaring
      return tanpaIntervensi.map(child => {
        const intervensiList = Array.isArray(child.raw?.intervensi) ? child.raw.intervensi : [];

        // Ambil semua jenis intervensi unik
        const jenisUnik = [...new Set(
          intervensiList
            .map(i => i.jenis)
            .filter(j => j && j.trim() !== '')
        )];

        const rumusan = jenisUnik.length ? jenisUnik.join(', ') : '-';
        const lastPosyandu = lastItem(child.raw?.posyandu);

        return {
          nama: child.nama || '-',
          rumusan,
          stunting: ['Stunted', 'Severely Stunted'].includes(lastPosyandu.tbu),
          wasting: ['Wasted', 'Severely Wasted'].includes(lastPosyandu.bbtb),
          underweight: ['Underweight', 'Severely Underweight'].includes(lastPosyandu.bbu),
          bb_sangat: lastPosyandu.bbu === 'Severely Underweight',
          overweight: lastPosyandu.bbu === 'Overweight',
        };
      });
    },
    totalPages() {
      return Math.ceil(this.processedChildren.length / this.perPage)
    },
    totalPages_belum() {
      return Math.ceil(this.processedChildren_belum.length / this.perPage)
    },
    paginatedChildren() {
      const start = (this.currentPage - 1) * this.perPage
      return this.processedChildren.slice(start, start + this.perPage)
    },
    paginatedChildren_belum() {
      const start = (this.currentPage - 1) * this.perPage
      return this.processedChildren_belum.slice(start, start + this.perPage)
    },
  }
}
</script>
