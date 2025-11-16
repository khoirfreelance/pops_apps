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
          <div class="mt-3">
            <div class="row justify-content-between g-2">
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
                  @change=" handleRWChange"
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
                  @click="setMenu('anak')"
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
                  @click="setMenu('bumil')"
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
                  @click="setMenu('catin')"
                >
                  Calon Pengantin Berisiko
                </button>
              </li>
            </ul>
          </div>
          <div class="tab-content" id="myTabContent">

            <!-- Tab Anak -->
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
                          v-for="(item, index) in kesehatanData.anak"
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
                <div class="col-md-4 mt-3 d-flex flex-column small">
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
                          <thead>
                            <tr class="fw-semibold text-additional">
                              <th class="text-additional">Status</th>
                              <th class="text-additional">Jumlah</th>
                              <th class="text-additional">Persen</th>
                              <th class="text-additional">Tren</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(row, index) in dataTable_bbtb" :key="index">
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
                          {{ totalKasus }} dengan Masalah Gizi Ganda
                        </h6>

                        <!-- TAB NAV -->
                        <div class="container position-relative" style="margin-top: -2.5rem;">
                          <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2">
                            <button
                              class="w-25 text-truncate fw-semibold rounded-pill border border-danger bg-light shadow-sm btn btn-outline-danger text-danger"
                              style="border-bottom-width: 5px !important;"
                              @click="toggleSudah(false)"
                            >
                              Anak belum dapat Intervensi <br> {{ totalBelum }}
                            </button>

                            <button
                              class="w-25 text-truncate fw-semibold rounded-pill border border-primary bg-light shadow-sm btn btn-outline-primary text-primary"
                              style="border-bottom-width: 5px !important;"
                              @click="toggleSudah(true)"
                            >
                              Anak sudah dapat Intervensi <br> {{ totalSudah }}
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

            <!-- Tab Bumil -->
            <div class="tab-pane fade" id="bumil-tab-pane" role="tabpanel" tabindex="0">
              <!-- Issue and Stat Card -->
              <div class="container-fluid my-4">
                <div class="row">
                  <div class="row justify-content-center">
                    <div
                      v-for="(item, index) in kesehatanData.bumil"
                      :key="index"
                      class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mb-3"
                    >
                      <div
                        class="card shadow-sm border-0 rounded-2 overflow-hidden"
                        :class="`border-start border-4 border-${item.color}`"
                      >
                        <div class="card-header">
                          <h6 class="fw-bold mb-1">{{ item.title }}</h6>
                        </div>
                        <div class="card-body d-flex justify-content-between align-items-center">
                          <p
                            v-if="index !== kesehatanData.anak.length - 1"
                            class="mb-0"
                            :class="`text-${item.color}`"
                          >
                            {{ item.percent }}
                          </p>
                          <p v-else class="my-auto">
                            <i class="bi bi-people fs-5" :class="`text-${item.color}`"></i>
                          </p>
                          <!-- Hanya tampilkan persentase kalau bukan card terakhir -->
                          <h3 class="fw-bold mb-0" :class="`text-${item.color}`">
                            {{ item.value }}
                          </h3>

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
              </div>

              <!-- Tren -->
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

            </div>

            <!-- Tab Catin -->
            <div class="tab-pane fade" id="catin-tab-pane" role="tabpanel" tabindex="0">
              <!-- Issue and Stat Card -->
              <div class="container-fluid my-4">
                <div class="row">
                  <div class="row justify-content-center">
                    <div
                      v-for="(item, index) in kesehatanData.catin"
                      :key="index"
                      class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mb-3"
                    >
                      <div
                        class="card shadow-sm border-0 rounded-2 overflow-hidden"
                        :class="`border-start border-4 border-${item.color}`"
                      >
                        <div class="card-header">
                          <h6 class="fw-bold mb-1">{{ item.title }}</h6>
                        </div>
                        <div class="card-body d-flex justify-content-between align-items-center">
                          <p
                            v-if="index !== kesehatanData.catin.length - 1"
                            class="mb-0"
                            :class="`text-${item.color}`"
                          >
                            {{ item.percent }}
                          </p>
                          <p v-else class="my-auto">
                            <i class="bi bi-people fs-5" :class="`text-${item.color}`"></i>
                          </p>
                          <!-- Hanya tampilkan persentase kalau bukan card terakhir -->
                          <h3 class="fw-bold mb-0" :class="`text-${item.color}`">
                            {{ item.value }}
                          </h3>
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
              </div>

              <!-- TREN -->
              <div class="container-fluid">
                <h5 class="fw-bold text-primary mb-3">Status Kesehatan Calon Pengantin</h5>

                <!-- Row utama: tabel-->
                <div class="row g-3">
                  <!-- Table -->
                  <div class="col-12">
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
                            <tr v-for="(row, index) in dataTable_catin" :key="index">
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
                            <tr v-for="(values, indikator) in indikatorCatin" :key="indikator">
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
      kesehatanData:
      {
        anak: [],
        bumil: [],
        catin: []
      },
      paginatedBumil: [
        {
          nama: 'Sakura Haruno',
          anemia: false,
          risiko: false,
          kek: true,
          intervensi: '-',
          rt: '9',
          rw: '2',
          usia: 22
        },
      ],
      dataTable_bumil:[],
      dataTable_bb:[],
      dataTable_tb:[],
      dataTable_bbtb:[],
      indikatorCatin:[],
      indikatorData:[],
      intervensiData: [],
      sudahBumilChart:null,
      belumBumilChart:null,
      bumilChart: null,
      //tab:'anak',
      isSudahBumil: false,
      isSudah: false,
      bulanLabels: [], // diisi daftar bulan
      rawData: [], // data asli anak
      filteredData: [], // data hasil filter
      activeMenu: 'anak', // default tab
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
    // Mandatory
    setMenu(type) {
      this.activeMenu = type;
      this.hitungStatistik();
      this.generateDataTable();
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
        await this.fetchPosyanduByWilayah(this.id_wilayah)
      } catch (error) {
        console.error('Gagal ambil data wilayah user:', error)
        this.kelurahan = '-'
      }
    },
    async fetchPosyanduByWilayah(id_wilayah) {
      if (!id_wilayah) {
        console.warn("ID wilayah kosong, tidak bisa fetch posyandu");
        return;
      }

      try {
        const res = await axios.get(`${baseURL}/api/posyandu/${id_wilayah}/wilayah`, {
          headers: {
            Accept: "application/json",
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        });

        this.posyanduList = res.data?.data || res.data || [];
        //console.log("Posyandu list:", this.posyanduList);

      } catch (error) {
        console.error("Gagal mengambil data posyandu:", error);
        this.posyanduList = [];
      }
    },
    handlePosyanduChange() {
      const selected = this.posyanduList.find(
        (p) => p.nama_posyandu === this.filters.posyandu
      );

      if (selected) {
        this.rwList = selected.rw || [];
        this.rtList = []; // reset RT
        this.filters.rw = '';
        this.filters.rt = '';
        this.rwReadonly = false;
        this.rtReadonly = true;
      } else {
        this.rwList = [];
        this.rtList = [];
        this.filters.rw = '';
        this.filters.rt = '';
        this.rwReadonly = true;
        this.rtReadonly = true;
      }
    },
    handleRWChange() {
      const selected = this.posyanduList.find(
        (p) => p.nama_posyandu === this.filters.posyandu
      );

      if (selected) {
        // RT yang terkait RW tertentu
        this.rtList = selected.rt || [];
        this.filters.rt = '';
        this.rtReadonly = false;
      } else {
        this.rtList = [];
        this.filters.rt = '';
        this.rtReadonly = true;
      }
    },
    async fetchStats() {
      try {
        const res = await axios.get(`${baseURL}/api/dashboard/stats/`, {
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
    async applyFilter() {
      await this.hitungStatistik()
      await this.generateDataTable()
      await this.masalahGanda()
    },
    async hitungStatistik() {
      try {
        const params = {
          from:'dashboard',
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          periode: this.filters.periode || '',
        };

        const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };
        let res = null;

        switch (this.activeMenu) {
          case 'anak':
            res = await axios.get(`${baseURL}/api/children/status`, { headers, params });
            break;
          case 'bumil':
            res = await axios.get(`${baseURL}/api/pregnancy/status`, { headers, params });
            break;
          case 'catin':
            res = await axios.get(`${baseURL}/api/bride/status`, { headers, params });
            break;
          default:
            return;
        }

        const data = res.data;
        const total = data.total || 0;
        this.totalAnak = total;

        this.kesehatanData[this.activeMenu] = data.counts.map(item => ({
          title: item.title,
          value: item.value,
          percent: total > 0 ? ((item.value / total) * 100).toFixed(1) + '%' : '0%',
          color: item.color,
        }));

      } catch (error) {
        console.error('❌ hitungStatusGizi error:', error);
      }
    },
    async generateDataTable() {
      try {
        const params = {
          from: 'dashboard',
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          periode: this.filters.periode || '',
        };

        const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };

        let res = null;

        switch (this.activeMenu) {
          case 'anak':
            res = await axios.get(`${baseURL}/api/children/tren`, { headers, params });
            break;
          case 'bumil':
            res = await axios.get(`${baseURL}/api/pregnancy/tren`, { headers, params });
            break;
          case 'catin':
            res = await axios.get(`${baseURL}/api/bride/tren`, { headers, params });
            break;
          default:
            return;
        }

        console.log('debug datatable :', res.data);

        // =============================
        //  B U M I L
        // =============================
        if (this.activeMenu === 'bumil') {
          this.dataTable_bumil = (res.data.dataTable_bumil || []).map(row => ({
            status: row.status || '-',
            jumlah: row.jumlah ?? 0,
            persen: row.persen ?? 0,

            tren: row.tren ?? '-',
            trenIcon: row.trenIcon ?? '',
            trenClass: row.trenClass ?? '',
          }));
        }

        if (this.activeMenu === 'catin') {
          this.dataTable_catin = (res.data.dataTable_catin || []).map(row => ({
            status: row.status || '-',
            jumlah: row.jumlah ?? 0,
            persen: row.persen ?? 0,

            tren: row.tren ?? '-',
            trenIcon: row.trenIcon ?? '',
            trenClass: row.trenClass ?? '',
          }));
        }

        if (this.activeMenu === 'anak') {

          // ==================== BB/U ====================
          const bbCurrent = res.data.bb?.data?.current || {};
          const bbLast = res.data.bb?.data?.previous || {};

          const totalCurrent = res.data.bb?.total?.current || 0;

          this.dataTable_bb = Object.entries(bbCurrent).map(([status, jumlah]) => {
            const prevValue = bbLast[status] ?? 0;
            const diff = jumlah - prevValue;

            let tren = "-";
            let trenIcon = "";
            let trenClass = "";

            if (prevValue === 0 && jumlah === 0) {
              tren = "-";
              trenIcon = "";
              trenClass = "";
            }
            else if (diff > 0) {
              if (prevValue === 0 && jumlah > 0) tren = (100).toFixed(2) + '%';
              else
                tren = ((diff / prevValue) * 100).toFixed(2) + '%';
              trenIcon = "bi bi-caret-up-fill";
              trenClass = "text-success";
            }
            else if (diff < 0) {
              if (prevValue === 0 && jumlah > 0) tren = (100).toFixed(2) + '%';
              else
                tren = ((diff / prevValue) * 100).toFixed(2) + '%';
              trenIcon = "bi bi-caret-down-fill";
              trenClass = "text-danger";
            }
            else {
              tren = "0.00%";
              trenIcon = "bi bi-caret-right-fill";
              trenClass = "text-secondary";
            }


            const persen = totalCurrent === 0 ? 0 : ((jumlah / totalCurrent) * 100).toFixed(2);

            return {
              status,
              jumlah,
              persen,
              tren,
              trenIcon,
              trenClass,
            };
          });

          const totalBB = res.data.bb?.total?.current || 0;
          this.dataTable_bb = this.dataTable_bb.map(row => ({
            ...row,
            persen: totalBB > 0 ? ((row.jumlah / totalBB) * 100).toFixed(1) : 0
          }));

          // ==================== TB/U ====================
          const tbCurrent = res.data.tb?.data?.current || {};
          const tbLast = res.data.tb?.data?.previous || {};

          const totalCurrentTB = res.data.tb?.total?.current || 0;


          this.dataTable_tb = Object.entries(tbCurrent).map(([status, jumlah]) => {
            const prevValue = tbLast[status] ?? 0;
            const diff = jumlah - prevValue;

            let tren = "-";
            let trenIcon = "";
            let trenClass = "";

            if (prevValue === 0 && jumlah === 0) {
              tren = "-";
              trenIcon = "";
              trenClass = "";
            }
            else if (diff > 0) {
              if (prevValue === 0 && jumlah > 0) tren = (100).toFixed(2) + '%';
              else
                tren = ((diff / prevValue) * 100).toFixed(2) + '%';
              trenIcon = "bi bi-caret-up-fill";
              trenClass = "text-success";
            }
            else if (diff < 0) {
              if (prevValue === 0 && jumlah > 0) tren = (100).toFixed(2) + '%';
              else
                tren = ((diff / prevValue) * 100).toFixed(2) + '%';
              trenIcon = "bi bi-caret-down-fill";
              trenClass = "text-danger";
            }
            else {
              tren = "0.00%";
              trenIcon = "bi bi-caret-right-fill";
              trenClass = "text-secondary";
            }


            const persen = totalCurrentTB === 0 ? 0 : ((jumlah / totalCurrentTB) * 100).toFixed(2);

            return {
              status,
              jumlah,
              persen,
              tren,
              trenIcon,
              trenClass,
            };
          });

          const totalTB = res.data.tb?.total?.current || 0;
          this.dataTable_tb = this.dataTable_tb.map(row => ({
            ...row,
            persen: totalTB > 0 ? ((row.jumlah / totalTB) * 100).toFixed(1) : 0
          }));

          // ==================== BB/TB ====================
          const bbtbCurrent = res.data.bbtb?.data?.current || {};
          const bbtbLast = res.data.bbtb?.data?.previous || {};

          const totalCurrentBBTB = res.data.bbtb?.total?.current || 0;

          this.dataTable_bbtb = Object.entries(bbtbCurrent).map(([status, jumlah]) => {
            const prevValue = bbtbLast[status] ?? 0;
            const diff = jumlah - prevValue;

            let tren = "-";
            let trenIcon = "";
            let trenClass = "";

            if (prevValue === 0 && jumlah === 0) {
              tren = "-";
              trenIcon = "";
              trenClass = "";
            }
            else if (diff > 0) {
              if (prevValue === 0 && jumlah > 0) tren = (100).toFixed(2) + '%';
              else
                tren = ((diff / prevValue) * 100).toFixed(2) + '%';
              trenIcon = "bi bi-caret-up-fill";
              trenClass = "text-success";
            }
            else if (diff < 0) {
              if (prevValue === 0 && jumlah > 0) tren = (100).toFixed(2) + '%';
              else
                tren = ((diff / prevValue) * 100).toFixed(2) + '%';
              trenIcon = "bi bi-caret-down-fill";
              trenClass = "text-danger";
            }
            else {
              tren = "0.00%";
              trenIcon = "bi bi-caret-right-fill";
              trenClass = "text-secondary";
            }


            const persen = totalCurrentBBTB === 0 ? 0 : ((jumlah / totalCurrentBBTB) * 100).toFixed(2);

            return {
              status,
              jumlah,
              persen,
              tren,
              trenIcon,
              trenClass,
            };
          });

          const totalBBTB = res.data.bbtb?.total?.current || 0;
          this.dataTable_bbtb = this.dataTable_bbtb.map(row => ({
            ...row,
            persen: totalBBTB > 0 ? ((row.jumlah / totalBBTB) * 100).toFixed(1) : 0
          }));

          // ==================== Render Pie Chart ====================
          this.$nextTick(() => {
            this.renderChart('pieChart_bb', this.dataTable_bb, [
              '#f5ebb9', '#f7db7f', '#7dae9b', '#bfbbe4', '#e87d7b',
            ]);
            this.renderChart('pieChart_tb', this.dataTable_tb, [
              '#f7db7f', '#bfbbe4', '#7dae9b', '#e87d7b',
            ]);
            this.renderChart('pieChart_status', this.dataTable_bbtb, [
              '#f5ebb9', '#f7db7f', '#7dae9b', '#bfbbe4', '#e87d7b', '#eaafdd',
            ]);
          });

        }
      } catch (e) {
        this.showError('Error Ambil Data', e);
      }
    },
    toggleSudah(val) {
      this.isSudah = val;
      this.isUdahBumil = val;
    },
    async masalahGanda() {
      try {
        const params = {
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          periode: this.filters.periode || '',
        };

        const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };

        let res = null;

        switch (this.activeMenu) {
          case 'anak':
            res = await axios.get(`${baseURL}/api/children/case`, { headers, params });
            break;
          case 'bumil':
            res = await axios.get(`${baseURL}/api/pregnancy/case`, { headers, params });
            break;
          default:
            return;
        }

        this.totalKasus = res.data.totalCase
        console.log('debug datatable :', this.totalKasus);

      } catch (e) {
        this.showError('Error Ambil Data', e);
      }
    },
    async hitungIntervensi() {
      try {
        const params = {
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          periode: this.filters.periode || '',
        };

        const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };

        let res = null;

        switch (this.activeMenu) {
          case 'anak':
            res = await axios.get(`${baseURL}/api/children/intervensi`, { headers, params });
            break;
          case 'bumil':
            res = await axios.get(`${baseURL}/api/pregnancy/intervensi`, { headers, params });
            break;
          default:
            return;
        }

        console.log('debug datatable :', res.data);
        const sudah = res.data.grouping.punya_keduanya;
        const belum = this.totalKasus - sudah;
        this.totalSudah = sudah;
        this.totalBelum = belum;

      } catch (e) {
        this.showError('Error Ambil Data', e);
      }
    },

    // only anak
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
    renderChart(refName, dataTable, colors, labelKey = 'status', valueKey = 'persen') {
      const ctx = this.$refs[refName]?.getContext('2d');
      if (!ctx) return;

      // Hancurkan chart lama kalau ada
      if (this[refName + 'Instance']) {
        this[refName + 'Instance'].destroy();
      }


      if (!Array.isArray(dataTable) || !dataTable.length) {
        console.log(`⚠️ Tidak ada data untuk chart ${refName}`);
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

      // 1️⃣ Ambil wilayah user
      await this.getWilayahUser();

      // 3️⃣ Generate bulan terakhir 12 bulan
      this.bulanLabels = this.getLast12Months();

      // 4️⃣ Jalankan logika indikator bulanan untuk catin

      // set menu default → 'anak'
      this.setMenu('anak');
      //await this.generateIndikatorCatinBulanan();

      // 5️⃣ Set menu default → anak
      await this.hitungStatistik(); // hitung ulang sesuai menu 'anak'
      await this.generateInfoBoxes() ;
      await this.generateDataTable();
      await this.masalahGanda();
      await this.hitungIntervensi();
      // 6️⃣ Generate data table sesuai tipe menu
      //this.generateDataTableCatin();

      // 7️⃣ Fetch stats tambahan jika perlu
      await this.fetchStats();

      // 8️⃣ Generate pilihan periode
      this.generatePeriodeOptions();

      // 9️⃣ Pastikan filter kelurahan sudah terisi
      this.filters.kelurahan = this.kelurahan;

      // 🔟 Simpan hasil awal ke filteredData sesuai tipe menu
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
      /* if (this.tipeMenu === 'anak') {
        this.filteredData = [...this.children];
      } else if (this.tipeMenu === 'bumil') {
        this.filteredData = [...this.bumil];
      } else if (this.tipeMenu === 'catin') {
        this.filteredData = [...this.catin];
      }
 */
      // 11️⃣ Setup resize listener untuk responsive
      this.handleResize();
      window.addEventListener('resize', this.handleResize);

    } catch (err) {
      console.error('❌ Error loading data:', err);
    } finally {
      // Delay kecil biar loading animation nggak blink
      setTimeout(() => {
        this.isLoading = false;
      }, 300);
    }
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
  },
  watch: {
    'filters.posyandu'(val) {
      this.handlePosyanduChange(val)
    },
    activeMenu() {
      this.generateDataTable()
      this.hitungStatistik()
    }
  }
}
</script>
