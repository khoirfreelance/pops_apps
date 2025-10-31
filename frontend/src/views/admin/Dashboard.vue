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
                  <div class="card-body d-flex align-items-stretch justify-content-between p-2">
                    <!-- Text -->
                    <div class="stat-text text-start ms-2">
                      <h6 class="text-muted small fw-semibold">{{ stat.title }}</h6>
                      <div class="spacer"></div> <!-- buat dorong value ke bawah -->
                      <h4 class="fw-bold mb-0">{{ stat.value }}</h4>
                    </div>

                    <!-- Icon -->
                    <div class="icon-wrap d-flex align-items-center justify-content-center">
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
              <div class="col-xl-2 col-lg-4 col-md-4">
                <label class="form-label fs-md-1">Kelurahan/Desa</label>
                <select v-model="filters.kelurahan" class="form-select text-muted" disabled>
                  <option :value="kelurahan">{{ kelurahan }}</option>
                </select>
              </div>

              <!-- Posyandu -->
              <div class="col-xl-2 col-lg-4 col-md-4">
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
              <div class="col-xl-2 col-lg-4 col-md-4">
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
              <div class="col-xl-2 col-lg-4 col-md-4">
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
              <div class="col-xl-2 col-lg-4 col-md-4">
                <label class="form-label">Periode</label>
                <select v-model="filters.periode" class="form-select text-muted">
                  <option value="">All</option>
                  <option v-for="periode in periodeOptions" :key="periode" :value="periode">
                    {{ periode }}
                  </option>
                </select>
              </div>

              <!-- Tombol Cari -->
              <div class="col-xl-2 col-lg-4 col-md-4 d-grid">
                <button type="submit" class="btn btn-gradient fw-semibold">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>
              </div>
            </form>
          </div>

          <!-- Main -->
          <div class="d-flex justify-content-center mt-3">
            <ul class="nav nav-pills d-flex flex-wrap justify-content-center gap-2 w-100" id="myTab" role="tablist" style="max-width: 800px;">
              <li class="nav-item flex-fill text-center" role="presentation">
                <button
                  class="nav-link active w-100 text-truncate"
                  id="home-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#home-tab-pane"
                  type="button"
                  role="tab"
                  aria-controls="home-tab-pane"
                  aria-selected="true"
                >
                  Status Gizi Anak
                </button>
              </li>

              <li class="nav-item flex-fill text-center" role="presentation">
                <button
                  class="nav-link w-100 text-truncate"
                  id="profile-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#profile-tab-pane"
                  type="button"
                  role="tab"
                  aria-controls="profile-tab-pane"
                  aria-selected="false"
                >
                  Status Kesehatan Ibu Hamil
                </button>
              </li>

              <li class="nav-item flex-fill text-center" role="presentation">
                <button
                  class="nav-link w-100 text-truncate"
                  id="contact-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#contact-tab-pane"
                  type="button"
                  role="tab"
                  aria-controls="contact-tab-pane"
                  aria-selected="false"
                >
                  Calon Pengantin Berisiko
                </button>
              </li>
            </ul>
          </div>
          <div class="tab-content" id="myTabContent">
            <!-- Tab 1 -->
            <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" tabindex="0">
              <!-- Issue and Stat Card -->
              <div class="container-fluid my-2 row">
                <div class="col-md-8">
                  <h5 class="fw-bold text-success mb-4">Ringkasan Status Gizi Bulan Ini</h5>
                  <div class="row">
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
                    <div class="col-lg-4 col-xl-2 col-md-6 col-sm-12">
                      <div class="card text-center shadow-sm border p-2 h-100 d-flex flex-column justify-content-center">
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
                <div class="col-md-4 mt-3">
                  <div
                    v-for="(box, idx) in infoBoxes"
                    :key="idx"
                    class="alert p-3 mb-2"
                    :class="`alert-${box.type}`"
                  >
                    <strong>{{ box.title }}</strong><br />
                    <span v-html="box.desc"></span> <!-- pakai v-html -->
                  </div>
                </div>

              </div>

              <!-- Pie Chart-->
              <div class="container-fluid my-4 row">
                <div class="col-md-12">
                  <h5 class="fw-bold text-primary mb-4">Ringkasan Status Gizi Bulan Ini</h5>
                </div>
                <div class="col-12 col-xl-8">
                  <!-- Berat Badan / Usia -->
                  <div class="card border border-primary shadow p-3 my-3">
                    <h4 class="fw-bold text-primary">Berat Badan / Usia</h4>
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
                              <td colspan="4" class="pt-4"><a href="/admin/anak">Lihat Grafik Selengkapnya</a></td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>

                      <!-- Chart -->
                      <div class="col-12 col-xl-5 text-center">
                        <canvas ref="pieChart_bb" class="mx-auto" style="max-width: 300px; max-height: 300px; min-width: 200px; min-height: 200px;"></canvas>
                      </div>
                    </div>
                  </div>

                  <!-- Tinggi Badan / Usia -->
                  <div class="card border border-primary shadow p-3 my-3">
                    <h4 class="fw-bold text-primary">Tinggi Badan / Usia</h4>
                    <div class="row">
                      <div class="col-12 col-xl-7 table-responsive">
                        <table class="table table-borderless align-middle">
                          <tbody>
                            <tr>
                              <td class="text-additional fw-bold">Status</td>
                              <td class="text-muted fw-bold">Jumlah</td>
                              <td class="text-muted fw-bold">Persen</td>
                              <td class="text-muted fw-bold">Tren</td>
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
                              <td colspan="4" class="pt-4"><a href="/admin/anak">Lihat Grafik Selengkapnya</a></td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                      <div class="col-12 col-xl-5 text-center">
                        <canvas ref="pieChart_tb" class="mx-auto" style="max-width: 300px; max-height: 300px; min-width: 200px; min-height: 200px;"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-xl-4">
                  <!-- Berat Badan / Tinggi Badan -->
                  <div class="card border border-primary shadow p-3 my-3">
                    <h4 class="fw-bold text-primary">Berat Badan / Tinggi Badan</h4>
                    <div class="row">
                      <div class="col-12 table-responsive">
                        <table class="table table-borderless align-middle">
                          <tbody>
                            <tr>
                              <td class="text-additional fw-bold">Status</td>
                              <td class="text-muted fw-bold">Jumlah</td>
                              <td class="text-muted fw-bold">Persen</td>
                              <td class="text-muted fw-bold">Tren</td>
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
                      <div class="col-12 text-center">
                        <canvas
                          ref="pieChart_status"
                          class="mx-auto"
                          style="max-width: 300px; max-height: 300px; min-width: 200px; min-height: 200px;"
                        ></canvas>
                        <div class="d-flex flex-wrap justify-content-between mt-3">
                          <a href="/admin/anak">Lihat Grafik Selengkapnya</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Ringkasan -->
              <div class="container-fluid my-4">
                <div class="row">
                  <div class="col-12">
                    <h5 class="fw-bold text-primary mb-4">Anak Dengan Masalah Gizi Ganda</h5>
                  </div>

                  <!-- CARD UTAMA -->
                  <div class="col-12">
                    <div class="card shadow-sm border-0 p-4 rounded-4">

                      <!-- HEADER -->
                      <div class="text-center position-relative mb-0">
                        <h4
                          class="fw-bold text-white py-4 px-3 rounded-bottom-5 d-inline-block bg-primary w-75"
                        >
                          Jumlah {{ totalAnak }} Anak
                        </h4>

                        <!-- TAB NAV -->
                        <div class="container mt-n3">
                          <div class="row justify-content-center">
                            <div class="col-12 col-md-8">
                              <ul class="nav nav-pills row g-2" id="myTab" role="tablist">
                                <li class="col-6 text-center" role="presentation">
                                  <button
                                    class="nav-link active w-100 text-truncate fw-semibold rounded-pill border-bottom border-primary shadow-sm btn btn-outline-primary"
                                    id="belum-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#belum-tab-pane"
                                    type="button"
                                    role="tab"
                                    aria-controls="belum-tab-pane"
                                    aria-selected="true"
                                  >
                                    Anak belum dapat Intervensi
                                  </button>
                                </li>
                                <li class="col-6 text-center" role="presentation">
                                  <button
                                    class="nav-link w-100 text-truncate fw-semibold rounded-pill shadow-sm btn btn-dark"
                                    id="dapat-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#dapat-tab-pane"
                                    type="button"
                                    role="tab"
                                    aria-controls="dapat-tab-pane"
                                    aria-selected="false"
                                  >
                                    Anak sudah dapat Intervensi
                                  </button>
                                </li>
                              </ul>
                            </div>
                          </div>
                        </div>

                      </div>


                      <!-- ISI GRID -->
                      <div class="row g-3 mt-3">
                        <!-- KIRI ATAS -->
                        <div class="col-md-4 col-sm-12">
                          <div class="card shadow-sm border-0 h-100 p-3">
                            <h6 class="text-center text-success mb-2">
                              Jumlah anak tidak membaik<br />
                              <span class="fw-bold text-dark">3 bulan terakhir</span>
                            </h6>
                            <div class="chart-placeholder text-muted text-center py-4">
                              <canvas ref="lineChart"></canvas>
                            </div>
                          </div>
                        </div>

                        <!-- TENGAH ATAS -->
                        <div class="col-md-4 col-sm-12">
                          <div class="card shadow-sm border-0 h-100 p-3">
                            <h6 class="text-center text-success mb-2">Diagram Intervensi</h6>
                            <div class="chart-placeholder text-muted text-center py-4">
                              <canvas ref="funnelChart"></canvas>
                            </div>
                          </div>
                        </div>

                        <!-- KANAN ATAS -->
                        <div class="col-md-4 col-sm-12">
                          <div class="card shadow-sm border-0 h-100 p-3">
                            <h6 class="text-center text-success mb-2">
                              Top 5 Posyandu<br />
                              <span class="text-dark">dengan anak tidak membaik dalam 3 bulan terakhir</span>
                            </h6>
                            <div class="chart-placeholder text-muted text-center py-4">
                              <canvas ref="barChart"></canvas>
                            </div>
                          </div>
                        </div>

                        <!-- BAWAH: TABEL -->
                        <div class="col-md-8 col-sm-12">
                          <div class="card shadow-sm border-0 h-100 p-3 table-responsive">
                            <table class="table table-sm align-middle">
                              <thead class="table-light">
                                <tr>
                                  <th>No</th>
                                  <th width="500">Nama</th>
                                  <th>Rumusan</th>
                                  <th>Stunting</th>
                                  <th>Wasting</th>
                                  <th>Underweight</th>
                                  <th>BB Sangat</th>
                                  <th>Overweight</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="(anak, i) in paginatedChildren" :key="i">
                                  <td>{{ (currentPage-1)*perPage + i + 1 }}</td>
                                  <td>{{ anak.nama }}</td>
                                  <td>{{ anak.rumusan }}</td>
                                  <td><i v-if="anak.stunting" class="bi bi-check2"></i></td>
                                  <td><i v-if="anak.wasting" class="bi bi-check2"></i></td>
                                  <td><i v-if="anak.underweight" class="bi bi-check2"></i></td>
                                  <td><i v-if="anak.bb_sangat" class="bi bi-check2"></i></td>
                                  <td><i v-if="anak.overweight" class="bi bi-check2"></i></td>
                                </tr>
                              </tbody>
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
                        </div>

                        <!-- BAWAH: KARTU RINGKAS -->
                        <div class="col-md-4 col-sm-12">
                          <div class="card shadow-sm border-0 h-100 p-3 alert alert-success">
                            <h6 class="fw-bold mb-3 text-primary text-center">Kartu Ringkas Gap</h6>
                            <p class="small text-muted">
                              Dari {{ totalKasus }} Anak yang tidak membaik:
                            </p>
                            <ul class="small">
                              <li><b>{{ belumPMT }}</b> ({{ persenBelumPMT }}%) belum menerima PMT</li>
                              <li><b>{{ belumBantuan }}</b> ({{ persenBelumBantuan }}%) belum menerima bantuan apapun</li>
                              <li><b>{{ belumKader }}</b> ({{ persenBelumKader }}%) belum dikunjungi kader</li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <!-- Tab 2 -->
            <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" tabindex="0">
              Status Kesehatan Ibu Hamil
            </div>

            <!-- Tab 3 -->
            <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" tabindex="0">
              Calon Pengantin Berisiko
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
      currentPage: 1,
      perPage: 5,
      infoBoxes: [],
      totalAnak: 0,
      configCacheKey: 'site_config_cache',
      isLoading: true,
      isCollapsed: false,
      username: '',
      today: '',
      thisMonth:'',
      kelurahan: '',
      logoSrc: null,
      logoLoaded: true,
      totalKasus: 37,
      windowWidth: window.innerWidth,
      stats: [],
      children:[],
      filters: {
        kelurahan: '',
        posyandu: '',
        rw: '',
        rt: '',
        periode: '',
      },
      gizi:[],
      dev:0,
      posyanduList: [],
      rwList: [],
      rtList: [],
      periodeOptions: [],
      rwReadonly: true,
      rtReadonly: true,
      sampleData: [
        {
          nama: "Ahsan Dimasi Aqilana",
          rumusan: "BLH, PMT, Bantuan",
          stunting: true,
          wasting: false,
          underweight: true,
          bb_sangat: false,
          overweight: false,
        },
        {
          nama: "Anak Kedua",
          rumusan: "BLH, PMT",
          stunting: false,
          wasting: true,
          underweight: false,
          bb_sangat: false,
          overweight: true,
        },
      ],
    }
  },
  methods: {
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
        console.log(items);

        this.children = items.map((item) => {
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
        // 🔹 Hitung total anak dengan usia < 60 bulan
        this.totalAnak = this.children.filter((anak) => anak.usia < 60).length;

        // 🔹 Inisialisasi filteredData
        this.filteredData = [...this.children];

        // 🔹 Hitung status gizi awal
        this.hitungStatusGizi();
        this.generateListsFromChildren()
      } catch (e) {
        console.error(e);

        //this.showError('Error Ambil Data Anak', e)
      }
    },
    generateInfoBoxes() {
      let stuntingNow = 0
      let stuntingPrev = 0
      let intervensiKurang = 0
      let kasusBaru = 0
      let dataPending = 0

      const stuntingByDesa = {}

      const data = this.filteredData || []
        data.forEach(child => {
        // --- ambil pengukuran terakhir dan sebelumnya dari raw.posyandu
        const posyandu = child.raw?.posyandu || []

        if (posyandu.length === 0) return

        const sorted = [...posyandu].sort(
          (a, b) => new Date(a.tgl_ukur) - new Date(b.tgl_ukur)
        )

        const last = sorted[sorted.length - 1]
        const prev = sorted[sorted.length - 2]

        // --- Stunting sekarang & sebelumnya
        if (last && ['Stunted', 'Severely Stunted'].includes(last.tbu)) {
          stuntingNow++
          if (child.kelurahan) {
            stuntingByDesa[child.kelurahan] = (stuntingByDesa[child.kelurahan] || 0) + 1
          }
        }
        if (prev && ['Stunted', 'Severely Stunted'].includes(prev.tbu)) stuntingPrev++

        // --- Intervensi kurang (bermasalah tapi belum dapat PMT)
        const bermasalah =
          ['Underweight', 'Severely Underweight'].includes(last?.bbu) ||
          ['Stunted', 'Severely Stunted'].includes(last?.tbu) ||
          ['Wasted', 'Severely Wasted'].includes(last?.bbtb)

        const intervensi = child.raw?.intervensi || []
        const adaPMT = intervensi.some(i => i.jenis?.toUpperCase() === 'PMT') || child.intervensi === 'PMT'

        if (bermasalah && !adaPMT) intervensiKurang++

        // --- Kasus baru (baru muncul bulan terakhir)
        if (
          prev &&
          !['Stunted', 'Severely Stunted'].includes(prev.tbu) &&
          ['Stunted', 'Severely Stunted'].includes(last.tbu)
        ) kasusBaru++

        // --- Data pending (lebih dari 2 bulan dari pengukuran terakhir)
        if (last) {
          const diffBulan =
            (new Date().getFullYear() - new Date(last.tgl_ukur).getFullYear()) * 12 +
            (new Date().getMonth() - new Date(last.tgl_ukur).getMonth())
          if (diffBulan >= 2) dataPending++
        }
      })

      const naik = stuntingPrev === 0 ? 0 : ((stuntingNow - stuntingPrev) / stuntingPrev) * 100

      let desaTertinggi = 'Tidak ada data'
      if (Object.keys(stuntingByDesa).length > 0) {
        desaTertinggi = Object.entries(stuntingByDesa).sort((a, b) => b[1] - a[1])[0][0]
      }

      function capitalizeWords(str) {
        return str.replace(/\b\w/g, c => c.toUpperCase());
      }
      this.infoBoxes = [
        {
          type: 'danger',
          title: `Stunting ${naik >= 0 ? 'naik' : 'turun'} ${Math.abs(naik).toFixed(1)}`,
          desc: `Dibanding pengukuran sebelumnya, tertinggi di Desa <strong>${capitalizeWords(desaTertinggi)}</strong>.`,
        },
        {
          type: 'warning',
          title: 'Intervensi',
          desc: `${intervensiKurang} anak bermasalah gizi belum mendapat bantuan PMT.`,
        },
        {
          type: 'info',
          title: 'Kasus baru',
          desc: `${kasusBaru} kasus stunting baru terdeteksi dari posyandu terakhir.`,
        },
        {
          type: 'success',
          title: 'Data Pending',
          desc: `${dataPending} anak belum update data pengukuran 2 bulan terakhir.`,
        },
      ]
    },

    // === Berat Badan per Umur ===
    generateDataTableBB() {
      if (!this.children?.length) return

      const categories = [
        'Severely Underweight',
        'Underweight',
        'Normal',
        'Risiko BB Lebih',
      ]

      const counts = {}
      const data = this.filteredData || []
data.forEach((child) => {
        const status = (child.bbu || 'Tidak diketahui').trim()
        counts[status] = (counts[status] || 0) + 1
      })

      const total = data.length

      const mapping = {
        'Severely Underweight': 'text-danger',
        'Underweight': 'text-warning',
        'Normal': 'text-success',
        'Risiko BB Lebih': 'text-info',
        'Tidak diketahui': 'text-muted',
      }

      this.dataTable_bb = categories.map((status) => {
        const jumlah = counts[status] || 0
        const persen = total ? ((jumlah / total) * 100).toFixed(2) : 0
        return {
          status,
          jumlah,
          persen: parseFloat(persen),
          tren: `${persen}%`,
          trenClass: mapping[status] || 'text-secondary',
          trenIcon: jumlah > 10 ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill',
        }
      })

      // Tambahan kategori lain (jika ada)
      Object.keys(counts).forEach((status) => {
        if (!categories.includes(status)) {
          const jumlah = counts[status]
          const persen = total ? ((jumlah / total) * 100).toFixed(2) : 0
          this.dataTable_bb.push({
            status,
            jumlah,
            persen: parseFloat(persen),
            tren: `${persen}%`,
            trenClass: mapping[status] || 'text-secondary',
            trenIcon: jumlah > 10 ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill',
          })
        }
      })
    },

    // === Tinggi Badan per Umur ===
    generateDataTableTB() {
      if (!this.children?.length) return

      const categories = [
        'Severely Stunted',
        'Stunted',
        'Normal',
        'Tinggi',
      ]

      const counts = {}
      const data = this.filteredData || []
data.forEach((child) => {
        const status = (child.tbu || 'Tidak diketahui').trim()
        counts[status] = (counts[status] || 0) + 1
      })

      const total = data.length

      const mapping = {
        'Severely Stunted': 'text-danger',
        'Stunted': 'text-warning',
        'Normal': 'text-success',
        'Tinggi': 'text-info',
        'Tidak diketahui': 'text-muted',
      }

      this.dataTable_tb = categories.map((status) => {
        const jumlah = counts[status] || 0
        const persen = total ? ((jumlah / total) * 100).toFixed(2) : 0
        return {
          status,
          jumlah,
          persen: parseFloat(persen),
          tren: `${persen}%`,
          trenClass: mapping[status] || 'text-secondary',
          trenIcon: jumlah > 10 ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill',
        }
      })

      Object.keys(counts).forEach((status) => {
        if (!categories.includes(status)) {
          const jumlah = counts[status]
          const persen = total ? ((jumlah / total) * 100).toFixed(2) : 0
          this.dataTable_tb.push({
            status,
            jumlah,
            persen: parseFloat(persen),
            tren: `${persen}%`,
            trenClass: mapping[status] || 'text-secondary',
            trenIcon: jumlah > 10 ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill',
          })
        }
      })
    },

    // === Berat Badan per Tinggi Badan ===
    generateDataTableStatus() {
      if (!this.children?.length) return

      const categories = [
        'Severely Wasted',
        'Wasted',
        'Normal',
        'Possible risk of Overweight',
        'Overweight',
        'Obesitas',
      ]

      const counts = {}
      const data = this.filteredData || []
data.forEach((child) => {
        const status = (child.bbtb || 'Tidak diketahui').trim()
        counts[status] = (counts[status] || 0) + 1
      })

      const total = data.length

      const mapping = {
        'Severely Wasted': 'text-danger',
        'Wasted': 'text-warning',
        'Normal': 'text-success',
        'Possible risk of Overweight': 'text-info',
        'Overweight': 'text-primary',
        'Obesitas': 'text-dark',
        'Tidak diketahui': 'text-muted',
      }

      this.dataTable_status = categories.map((status) => {
        const jumlah = counts[status] || 0
        const persen = total ? ((jumlah / total) * 100).toFixed(2) : 0
        return {
          status,
          jumlah,
          persen: parseFloat(persen),
          tren: `${persen}%`,
          trenClass: mapping[status] || 'text-secondary',
          trenIcon: jumlah > 10 ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill',
        }
      })

      Object.keys(counts).forEach((status) => {
        if (!categories.includes(status)) {
          const jumlah = counts[status]
          const persen = total ? ((jumlah / total) * 100).toFixed(2) : 0
          this.dataTable_status.push({
            status,
            jumlah,
            persen: parseFloat(persen),
            tren: `${persen}%`,
            trenClass: mapping[status] || 'text-secondary',
            trenIcon: jumlah > 10 ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill',
          })
        }
      })
    },

    hitungStatusGizi() {
      const dataAnak = this.filteredData.length ? this.filteredData : this.children;
      const total = dataAnak.length;

      const count = {
        Stunting: 0,
        Wasting: 0,
        Underweight: 0,
        Normal: 0,
        'BB Stagnan': 0,
        Overweight: 0,
      };

      dataAnak.forEach((anak) => {
        const { bbu, tbu, bbtb } = anak;

        if (tbu?.includes('Stunted')) count.Stunting++;
        if (bbtb?.includes('Wasted')) count.Wasting++;
        if (bbu?.includes('Underweight')) count.Underweight++;
        if (bbu === 'Normal' && tbu === 'Normal' && bbtb === 'Normal') count.Normal++;
        if (bbtb?.includes('Overweight')) count.Overweight++;

        // === cek stagnan (3 kali BB sama)
        const riwayat = anak.raw?.posyandu || [];
        if (riwayat.length >= 3) {
          const last3 = riwayat.slice(-3);
          const allEqual = last3.every((r) => r.bb === last3[0].bb);
          if (allEqual) count['BB Stagnan']++;
        }
      });

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
    renderLineChart() {
      new Chart(this.$refs.lineChart, {
        type: 'line',
        data: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr'],
          datasets: [
            {
              label: 'Tidak membaik (%)',
              data: [10, 13, 12, 11],
              borderColor: 'goldenrod',
              backgroundColor: 'rgba(255, 215, 0, 0.3)',
              fill: true,
              tension: 0.3,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } },
        },
      })
    },
    renderBarChart() {
      new Chart(this.$refs.barChart, {
        type: 'bar',
        data: {
          labels: ['Mawar', 'Tulip', 'Melati', 'Anggrek', 'Teratai'],
          datasets: [
            {
              label: 'Kasus',
              data: [10, 6, 12, 8, 10],
              backgroundColor: 'rgba(255, 99, 132, 0.6)',
              borderRadius: 8,
            },
          ],
        },
        options: {
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } },
        },
      })
    },
    renderFunnelChart() {
      new Chart(this.$refs.funnelChart, {
        type: 'bar',
        data: {
          labels: ['Ditugaskan kader', 'Dikunjungi kader', 'Dapat PMT','Belum Mendapat Bantuan'],
          datasets: [
            {
              data: [8, 20, 9, 13],
              backgroundColor: ['#006341', '#6FA287', '#6D8B7B', '#9FE0BD'],
              color: '#FFFFFF',
            },
          ],
        },
        options: {
          indexAxis: 'y',
          plugins: { legend: { display: false }, datalabels: {
          color: '#FFFFFF',        // warna teks label
          anchor: 'center',        // posisi di tengah bar
          align: 'center',         // teks di tengah
          font: { weight: 'bold' } // biar lebih jelas
        }, },
          scales: {
            x: { beginAtZero: true },
          },
        },
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
      console.log('🔄 Generate list dari children:', this.children.length)

      const posyanduSet = new Set(this.children.map(c => c.posyandu).filter(Boolean))
      this.posyanduList = Array.from(posyanduSet).map((nama, index) => ({
        id: index + 1,
        nama_posyandu: nama
      }))

      const rwSet = new Set(this.children.map(c => c.rw).filter(Boolean))
      const rtSet = new Set(this.children.map(c => c.rt).filter(Boolean))
      this.rwList = Array.from(rwSet)
      this.rtList = Array.from(rtSet)

      this.rwReadonly = true
      this.rtReadonly = true
    },
    handlePosyanduChange() {
      const pos = this.filters.posyandu

      if (pos) {
        const filteredChildren = this.children.filter(c => c.posyandu === pos)

        const rwSet = new Set(filteredChildren.map(c => c.rw).filter(Boolean))
        const rtSet = new Set(filteredChildren.map(c => c.rt).filter(Boolean))

        this.rwList = Array.from(rwSet)
        this.rtList = Array.from(rtSet)
        this.rwReadonly = false
        this.rtReadonly = false
      } else {
        // reset kalau posyandu dikosongin
        const rwSet = new Set(this.children.map(c => c.rw).filter(Boolean))
        const rtSet = new Set(this.children.map(c => c.rt).filter(Boolean))
        this.rwList = Array.from(rwSet)
        this.rtList = Array.from(rtSet)
        this.rwReadonly = true
        this.rtReadonly = true
        this.filters.rw = ''
        this.filters.rt = ''
      }
    },
    /* async fetchPosyanduByWilayah(id_wilayah) {
      try {
        const res = await axios.get(`${baseURL}/api/posyandu/${id_wilayah}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })
        this.posyanduList = res.data
      } catch (err) {
        console.error('Gagal ambil data posyandu:', err)
      }
    },
    handlePosyanduChange() {
      console.log('Posyandu dipilih:', this.filters.posyandu)
      if (this.filters.posyandu) {
        this.rtReadonly = false
        this.rwReadonly = false
        this.fetchRwRtByPosyandu(this.filters.posyandu)
      } else {
        this.rtReadonly = true
        this.rwReadonly = true
        this.rwList = []
        this.rtList = []
      }
    },
    async fetchRwRtByPosyandu(idPosyandu) {
      try {
        const res = await axios.get(`${baseURL}/api/posyandu/${idPosyandu}/wilayah`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })
        this.rwList = res.data.rw || []
        this.rtList = res.data.rt || []
        console.log('data', res.data);

      } catch (err) {
        console.error('Gagal ambil RW/RT:', err)
      }
    }, */
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
        const label = `${bulan[d.getMonth()]} ${d.getFullYear()}`
        result.push(label)
      }

      this.periodeOptions = result
    },
    applyFilter() {
      const f = this.filters

      this.filteredData = this.children.filter(item => {
        // Posyandu
        const posyanduMatch = !f.posyandu || item.posyandu === f.posyandu || item.nama_posyandu === f.posyandu
        // RW & RT
        const rwMatch = !f.rw || item.rw === f.rw
        const rtMatch = !f.rt || item.rt === f.rt
        // Periode (single bulan YYYY-MM)
        const periodeMatch = (() => {
          if (!f.periode) return true
          const [y, m] = f.periode.split('-')
          const periodeNum = parseInt(y) * 100 + parseInt(m)
          const tgl = new Date(item.tgl_ukur)
          const itemPeriode = tgl.getFullYear() * 100 + (tgl.getMonth() + 1)
          return itemPeriode === periodeNum
        })()

        return posyanduMatch && rwMatch && rtMatch && periodeMatch
      })

      // 🔹 Update turunan setelah filter
      this.totalAnak = this.filteredData.filter(a => a.usia < 60).length
      this.hitungStatusGizi()
      this.generateDataTableBB()
      this.generateDataTableTB()
      this.generateDataTableStatus()
      this.generateInfoBoxes()
      this.generateListsFromChildren() // update dropdown RW/RT/Posyandu
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
    renderChart(refName, dataSource, colors) {
      const ctx = this.$refs[refName]?.getContext('2d')
      if (!ctx) return

      // Hancurkan chart lama
      if (this[refName + 'Instance']) {
        this[refName + 'Instance'].destroy()
      }

      // Warna transparan & border
      const transparentColors = colors.map(color =>
        color.replace('rgb', 'rgba').replace(')', ', 0.8)')
      )

      this[refName + 'Instance'] = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: dataSource.map(row => row.status),
          datasets: [
            {
              data: dataSource.map(row => row.persen),
              backgroundColor: transparentColors,
              borderColor: '#fff',
              borderWidth: 1,
              borderAlign: 'inner',
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
                label: function (context) {
                  const label = context.label || ''
                  const value = context.parsed
                  return value > 0 ? `${label}: ${value}%` : ''
                },
              },
            },
            datalabels: {
              display: ctx => {
                const value = ctx.dataset.data[ctx.dataIndex]
                return value > 0 // ✅ tampilkan label hanya jika persen > 0
              },
              align: 'end',
              anchor: 'end',
              offset: 8,
              clamp: true,
              color: ctx => {
                const label = ctx.chart.data.labels[ctx.dataIndex]
                if (label.includes('Kurang')) return 'red'
                if (label.includes('Tidak Naik')) return '#e0b000'
                if (label.includes('Risiko')) return '#6a5acd'
                return '#333'
              },
              font: {
                size: 11,
                weight: '600',
              },
              formatter: (value, ctx) => {
                const label = ctx.chart.data.labels[ctx.dataIndex]
                return `${value}% ${label}`
              },
            },
          },
          onHover: (event, elements) => {
            event.native.target.style.cursor = elements.length ? 'pointer' : 'default'
          },
          onClick: (event, elements) => {
            if (elements.length > 0) {
              const index = elements[0].index
              const item = dataSource[index]
              const status = item.status

              let tipe = ''
              if (refName === 'pieChart_bb') tipe = 'BB/U'
              else if (refName === 'pieChart_tb') tipe = 'TB/U'
              else if (refName === 'pieChart_status') tipe = 'BB/TB'

              this.selectedChartStatus = status
              this.selectedChartType = tipe

              this.$router.push({
                path: '/admin/anak',
                query: { tipe, status },
              })
            }
          },
        },
        plugins: [ChartDataLabels],
      })
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
    this.isLoading = true

    try {
      await this.$nextTick()
      await this.loadConfigWithCache()
      await this.getWilayahUser()

      // ⏳ ambil data anak lebih dulu (supaya filter muncul)
      await this.loadChildren() // ini udah panggil generateListsFromChildren()
      this.generateInfoBoxes()
      this.generateListsFromChildren()
      this.generateDataTableBB()
      this.generateDataTableTB()
      this.generateDataTableStatus()
      // Setelah anak dimuat, baru jalanin statistik & chart
      await this.fetchStats()
      this.generatePeriodeOptions()
      this.filters.kelurahan = this.kelurahan

      // Render chart
      this.renderChart('pieChart_bb', this.dataTable_bb, [
        '#f5ebb9', '#f7db7f', '#7dae9b', '#bfbbe4', '#e87d7b',
      ])
      this.renderChart('pieChart_tb', this.dataTable_tb, [
        '#f7db7f', '#bfbbe4', '#7dae9b', '#e87d7b',
      ])
      this.renderChart('pieChart_status', this.dataTable_status, [
        '#f5ebb9', '#f7db7f', '#7dae9b', '#bfbbe4', '#e87d7b', '#eaafdd',
      ])

      this.renderLineChart()
      this.renderBarChart()
      this.renderFunnelChart()

      this.filteredData = [...this.children]
      this.handleResize()
      window.addEventListener('resize', this.handleResize)

    } catch (err) {
      console.error('Error loading data:', err)
    } finally {
      setTimeout(() => { this.isLoading = false }, 300)
    }
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
  },
  watch: {
    'filters.posyandu'(val) {
      this.handlePosyanduChange(val)
    }
  },
  computed: {
    processedChildren() {
      // pastikan filteredData valid, fallback ke children
      const data = Array.isArray(this.filteredData) && this.filteredData.length
        ? this.filteredData
        : Array.isArray(this.children) ? this.children : [];

      const lastItem = arr => Array.isArray(arr) && arr.length ? arr[arr.length - 1] : {};

      return data.map(child => {
        const lastPosyandu = lastItem(child.raw?.posyandu);
        const lastIntervensi = lastItem(child.raw?.intervensi);

        return {
          nama: child.nama || '-',
          rumusan: lastIntervensi.jenis || '-', // Ambil jenis intervensi terakhir
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
    paginatedChildren() {
      const start = (this.currentPage - 1) * this.perPage
      return this.processedChildren.slice(start, start + this.perPage)
    },
    // eslint-disable-next-line vue/no-dupe-keys
    totalKasus() {
      return this.children.length
    },
    belumPMT() {
      return this.children.filter(child => {
        const lastPosyandu = child.raw?.posyandu?.[child.raw.posyandu.length - 1] || {}
        const bermasalah = ['Underweight', 'Severely Underweight'].includes(lastPosyandu.bbu) ||
                           ['Stunted', 'Severely Stunted'].includes(lastPosyandu.tbu) ||
                           ['Wasted', 'Severely Wasted'].includes(lastPosyandu.bbtb)
        const adaPMT = child.raw?.intervensi?.some(i => i.jenis === 'PMT')
        return bermasalah && !adaPMT
      }).length
    },
    belumBantuan() {
      return this.children.filter(child => !(child.raw?.intervensi && child.raw.intervensi.length)).length
    },
    belumKader() {
      return this.children.filter(child => !(child.raw?.pendampingan && child.raw.pendampingan.length)).length
    },
    persenBelumPMT() {
      return this.totalKasus ? ((this.belumPMT / this.totalKasus) * 100).toFixed(0) : 0
    },
    persenBelumBantuan() {
      return this.totalKasus ? ((this.belumBantuan / this.totalKasus) * 100).toFixed(0) : 0
    },
    persenBelumKader() {
      return this.totalKasus ? ((this.belumKader / this.totalKasus) * 100).toFixed(0) : 0
    }
  }
}
</script>
