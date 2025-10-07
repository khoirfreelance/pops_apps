<template>
  <div class="dashboard-wrapper">
    <!-- Header -->
    <HeaderAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />
    <div class="d-flex flex-column flex-md-row">
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" />
      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <!-- Main Content -->
        <div
          class="py-4 bg-light container-fluid"
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
                <h1 class="text-light mb-5">
                  <span class="text-light fw-normal fs-6">Selamat datang,</span> <br />
                  {{ username }}
                </h1>
                <p class="text-light small">
                  <span class="bg-light rounded-circle p-2"
                    ><i class="bi bi-calendar text-primary"></i
                  ></span>
                  &nbsp; Anda memiliki
                  <router-link to="/admin/jadwal" class="fw-bold text-light text-decoration-none">
                    1 jadwal intervensi
                  </router-link>
                  hari ini.
                </p>
              </div>

              <!-- Kanan: Gambar -->
              <div class="mt-3 mt-md-0">
                <img
                  src="/src/assets/admin.png"
                  alt="Welcome"
                  class="img-fluid welcome-img"
                  style="max-width: 280px"
                />
              </div>
            </div>
          </div>

          <!-- Statistic Cards -->
          <div class="container-fluid mt-2">
            <div class="row justify-content-center" style="gap: 0.6rem !important;">
              <div v-for="(stat, index) in stats" :key="index" class="stat-card card-shadow-bottom border-0">
                <div class="card-body p-3 position-relative h-100">
                  <p class="text-dark position-absolute top-0 end-0 fw-semibold my-1">
                    {{ stat.value }}
                  </p>
                  <small class="text-success position-absolute bottom-0 start-0 my-1" style="white-space: normal; max-width: 80%;">
                    {{ stat.title }}
                  </small>
                  <div class="position-absolute bottom-0 end-0 my-1 d-flex align-items-center justify-content-center">
                    <img :src="stat.icon" alt="icon" width="25">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Filter Form -->
          <div class="mt-5">
            <div class="card border-0 shadow-sm p-3 bg-light text-primary">
              <p class="text-primary fw-bold h5">Filter:</p>
              <form class="row g-3 align-items-end">
                <div class="col-3">
                  <label for="kecamatan">Kecamatan</label>
                  <select name="kecamatan" id="kecamatan" class="form-select">
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                  </select>
                </div>
                <div class="col-3">
                  <label for="kelurahan">Kelurahan</label>
                  <select name="kelurahan" id="kelurahan" class="form-select">
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                  </select>
                </div>
                <div class="col-4">
                  <label for="periode"> Periode</label>
                  <input
                    type="text"
                    id="daterange"
                    class="form-control"
                    placeholder="Pilih rentang tanggal"
                    readonly
                  />
                </div>
                <div class="col-2">
                  <button type="submit" class="btn btn-gradient col-12">
                    <i class="bi bi-search me-1"></i> Cari
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Tabs -->
          <div class="mt-5">
            <div class="d-flex justify-content-center">
              <ul class="nav nav-pills mb-3" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link active"
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
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link"
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
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link"
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
                <!-- Ringkasan Status Gizi -->
                <div class="container-fluid my-4">
                  <h4 class="text-primary fw-bold mb-4 ml-3">Ringkasan Status Gizi</h4>
                  <!--BARIS 1: Status Gizi -->
                  <div class="d-flex flex-wrap gap-3 justify-content-between">
                    <div class="status-card stunting">
                      <h5 class="text-dark">Stunting</h5>
                      <div class="value">215</div>
                      <div class="percent">23.7%</div>
                      <div class="status-line stunting"></div>
                    </div>

                    <div class="status-card wasting">
                      <h5 class="text-dark">Wasting</h5>
                      <div class="value">33</div>
                      <div class="percent">23.7%</div>
                      <div class="status-line wasting"></div>
                    </div>

                    <div class="status-card underweight">
                      <h5 class="text-dark">Underweight</h5>
                      <div class="value">98</div>
                      <div class="percent">10.8%</div>
                      <div class="status-line underweight"></div>
                    </div>

                    <div class="status-card normal">
                      <h5 class="text-dark">Normal</h5>
                      <div class="value">541</div>
                      <div class="percent">59.7%</div>
                      <div class="status-line normal"></div>
                    </div>

                    <div class="status-card stagnan">
                      <h5 class="text-dark">BB Stagnan</h5>
                      <div class="value">14</div>
                      <div class="percent">3.0%</div>
                      <div class="status-line stagnan"></div>
                    </div>

                    <div class="status-card overweight">
                      <h5 class="text-dark">Overweight</h5>
                      <div class="value">13</div>
                      <div class="percent">1.5%</div>
                      <div class="status-line overweight"></div>
                    </div>

                    <div class="status-card total text-center position-relative">
                      <h5 class="text-dark">Total Anak Balita</h5>
                      <div class="value">905</div>
                      <br>
                      <div class="percent position-absolute bottom-0 start-50 translate-middle-x mb-2">
                        <i class="fa fa-users fa-2x text-dark"></i>
                      </div>
                      <div class="status-line total"></div>
                    </div>
                  </div>

                  <!-- BARIS 2: Ringkasan Tambahan -->
                  <div class="d-flex flex-wrap gap-3 justify-content-center mt-4">
                    <div class="info-card shadow-sm p-3 rounded-3">
                      <div class="d-flex align-items-start">
                        <i class="fa fa-arrow-up text-danger me-2 mt-1"></i>
                        <div>
                          <h6 class="fw-bold text-danger mb-1">Stunting naik +1.4 p.p</h6>
                          <small class="text-muted">Dibanding bulan lalu tertinggi di Desa Wonosari</small>
                        </div>
                      </div>
                    </div>

                    <div class="info-card shadow-sm p-3 rounded-3">
                      <div class="d-flex align-items-start">
                        <span class="text-success fs-5 me-2 mt-1">●</span>
                        <div>
                          <h6 class="fw-bold text-success mb-1">Intervensi</h6>
                          <small class="text-muted">22% Anak dalam kategori khusus belum mendapat bantuan</small>
                        </div>
                      </div>
                    </div>

                    <div class="info-card shadow-sm p-3 rounded-3">
                      <div class="d-flex align-items-start">
                        <span class="text-success fs-5 me-2 mt-1">●</span>
                        <div>
                          <h6 class="fw-bold text-success mb-1">Kasus baru p.p</h6>
                          <small>
                            <span class="text-danger fw-bold">15</span> Kasus Baru Stunting<br>
                            <span class="text-warning fw-bold">33</span> Kasus Baru Wasting<br>
                            <span class="text-primary fw-bold">4</span> Kasus Baru BB Stagnan
                          </small>
                        </div>
                      </div>
                    </div>

                    <div class="info-card shadow-sm p-3 rounded-3">
                      <div class="d-flex align-items-start">
                        <i class="bi bi-calendar text-dark me-2 mt-1"></i>
                        <div>
                          <h6 class="fw-bold text-danger mb-1">Data Pending</h6>
                          <small class="text-muted">10 Data tidak lengkap belum diperbarui dari bulan sebelumnya</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <br>
                <!-- Title -->
                <div class="d-flex justify-content-between align-items-center mt-5">
                  <h4 class="fw-bold text-primary">Komposisi Status Gizi</h4>
                </div>

                <!-- PIE CHART-->
                <div class="row mb-4">
                  <div class="col-12 col-md-6">
                    <!-- Berat Badan / Usia -->
                    <div class="card border border-primary shadow p-3 my-3">
                      <h4 class="fw-bold text-primary">Berat Badan / Usia</h4>
                      <div class="row">
                        <!-- Table -->
                        <div class="col-12 col-md-7">
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
                                <td colspan="4" class="pt-4"><a href="">Lihat Grafik Selengkapnya</a></td>
                              </tr>
                            </tfoot>
                          </table>
                        </div>

                        <!-- Chart -->
                        <div class="col-12 col-md-5 text-center">
                          <canvas ref="pieChart_bb"></canvas>
                          <p class="mt-4">Klik Chart untuk melihat data</p>
                        </div>
                      </div>
                    </div>

                    <!-- Tinggi Badan / Usia -->
                    <div class="card border border-primary shadow p-3 my-3">
                      <h4 class="fw-bold text-primary">Tinggi Badan / Usia</h4>
                      <div class="row">
                        <div class="col-12 col-md-7">
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
                                <td colspan="4" class="pt-4"><a href="">Lihat Grafik Selengkapnya</a></td>
                              </tr>
                            </tfoot>
                          </table>
                        </div>
                        <div class="col-12 col-md-5 text-center">
                          <canvas ref="pieChart_tb"></canvas>
                          <p class="mt-4">Klik Chart untuk melihat data</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-12 col-md-6">
                    <!-- Berat Badan / Tinggi Badan -->
                    <div class="card border border-primary shadow p-3 my-3">
                      <h4 class="fw-bold text-primary">Berat Badan / Tinggi Badan</h4>
                      <div class="row">
                        <div class="col-12">
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
                            class="mx-auto d-block"
                            style="max-width: 300px; max-height: 300px"
                          ></canvas>
                          <div class="d-flex flex-wrap justify-content-between mt-3">
                            <a href="">Lihat Grafik Selengkapnya</a> <p>Klik Chart untuk melihat data</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Data Anak Dengan Status Kurang Berdasar Berat Badan / Usia -->
                <div class="mb-3">
                  <h5 class="fw-bold text-danger mb-0">
                    Data Anak dengan Status Kurang Berdasar Berat Badan / Usia
                  </h5>
                </div>
                <div class="card shadow-sm border-0 p-3">
                  <!-- Filter Section -->
                  <div class="row g-2 mb-3">
                    <div class="col-md-3 col-6"></div>
                    <div class="col-md-3 col-6"></div>
                    <div class="col-md-3 col-6">
                      <select v-model="selectedStatusGizi" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option
                          v-for="(status, i) in uniqueStatusGizi"
                          :key="'status-' + i"
                          :value="status"
                        >
                          {{ status }}
                        </option>
                      </select>
                    </div>

                    <div class="col-md-3 col-6">
                      <select v-model="selectedIntervensi" class="form-select form-select-sm">
                        <option value="">Semua Intervensi</option>
                        <option
                          v-for="(item, i) in uniqueIntervensi"
                          :key="'intervensi-' + i"
                          :value="item"
                        >
                          {{ item }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <!-- Table -->
                  <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                      <thead class="border-bottom">
                        <tr class="text-dark small fw-semibold text-center">
                          <th class="text-dark">Nama</th>
                          <th class="text-dark">NIK</th>
                          <th class="text-dark">Status BB/U</th>
                          <th class="text-dark">Status TB/U</th>
                          <th class="text-dark">Status BB/TB</th>
                          <th class="text-dark">JK</th>
                          <th class="text-dark">Usia</th>
                          <th class="text-dark">Intervensi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="(row, index) in filteredData"
                          :key="index"
                          class="small"
                        >
                          <td class="text-left">{{ row.nama }}</td>
                          <td class="text-center">{{ row.nik }}</td>
                          <td class="text-center text-capitalize">{{ row.status_bb }}</td>
                          <td class="text-capitalize text-center">{{ row.status_tb }}</td>
                          <td
                            class="text-capitalize text-center"
                            :class="row.status_gizi === 'stunting' ? 'text-danger fw-semibold' : ''"
                          >
                            {{ row.status_gizi }}
                          </td>
                          <td class="text-center">{{ row.jk }}</td>
                          <td class="text-center">{{ row.usia }}</td>
                          <td class="text-capitalize text-center">{{ row.intervensi }}</td>
                        </tr>

                        <tr v-if="filteredData.length === 0">
                          <td colspan="8" class="text-center text-muted small py-3">
                            Tidak ada data yang cocok dengan filter.
                          </td>
                        </tr>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="8" class="text-end pt-3">
                            <button
                              class="btn btn-sm btn-outline-success fw-semibold"
                              @click="exportCSV"
                            >
                              <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                              Generate Export
                            </button>
                          </td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>

                <div class="container-fluid py-3">
                  <h5 class="fw-bold text-success my-4">
                    Cohort Tracker & Stagnansi
                  </h5>

                  <!-- 3 Cards Row -->
                  <div class="row mb-4">

                    <div class="col-md-4">
                      <!-- Kasus Card -->
                      <div class="card text-center shadow-sm border-0 p-3 bg-success-subtle">
                        <h2 class="fw-bold text-success my-3 ">{{ totalKasus }}</h2>
                        <span class="p-3 small fw-bold text-primary bg-light">Kasus</span>
                      </div>
                      <!-- Funnel -->
                      <div class="card shadow-sm border-0 p-3 mt-3">
                        <h6 class="fw-semibold text-center text-success mb-3">
                          Funnel Intervensi
                        </h6>
                        <canvas ref="funnelChart"></canvas>
                      </div>
                      <!-- Summary -->
                      <div class="card border border-5 border-light shadow-sm p-4 mx-auto mt-3" style="background-color: #fff9db;">
                        <h6 class="fw-bold text-center mb-3 text-dark">Kartu Ringkas Gap</h6>

                        <p class="text-dark mb-2 small">
                          Dari {{ totalAnak }} Anak yang tidak membaik:
                        </p>

                        <ul class="mb-0 small text-dark">
                          <li>
                            <strong>{{ dataGap.pmt.jumlah }}</strong>
                            ({{ dataGap.pmt.persen }}%) belum menerima PMT
                          </li>
                          <li>
                            <strong>{{ dataGap.bantuan.jumlah }}</strong>
                            ({{ dataGap.bantuan.persen }}%) belum menerima bantuan apapun
                          </li>
                          <li>
                            <strong>{{ dataGap.kunjungan.jumlah }}</strong>
                            ({{ dataGap.kunjungan.persen }}%) belum dikunjungi kader
                          </li>
                        </ul>
                      </div>
                    </div>

                    <div class="col-md-8">
                      <div class="row">
                        <!-- Line Chart -->
                        <div class="col-md-6">
                          <div class="card shadow-sm border-0 p-3">
                            <canvas ref="lineChart"></canvas>
                            <p class="small text-center text-success fw-semibold mt-2">
                              Persentase Anak tidak membaik 3 bulan terakhir
                            </p>
                          </div>
                        </div>
                        <!-- Bar Chart -->
                        <div class="col-md-6">
                          <div class="card shadow-sm border-0 p-3">
                            <h6 class="text-success fw-bold text-center mb-2">
                              Top 5 dusun dengan kasus tidak membaik dalam 3 bulan
                            </h6>
                            <canvas ref="barChart"></canvas>
                          </div>
                        </div>
                        <!-- Table Data -->
                        <div class="col-md-12 mt-3">
                          <div class="card shadow-sm border-0 px-3 py-4">
                            <div class="table-responsive">
                              <table class="table table-bordered align-middle mb-0 text-center">
                                <thead class="table-light small text-secondary fw-semibold">
                                  <tr>
                                    <th>Nama</th>
                                    <th>Stunting</th>
                                    <th>Wasting</th>
                                    <th>Underweight</th>
                                    <th>BB Stagnant</th>
                                    <th>Overweight</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr v-for="(row, i) in dataAnak" :key="i">
                                    <td>{{ row.nama }}</td>
                                    <td>{{ row.stunting ? '✓' : '' }}</td>
                                    <td>{{ row.wasting ? '✓' : '' }}</td>
                                    <td>{{ row.underweight ? '✓' : '' }}</td>
                                    <td>{{ row.bb_stagnant ? '✓' : '' }}</td>
                                    <td>{{ row.overweight ? '✓' : '' }}</td>
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

              <!-- Tab 2 -->
              <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" tabindex="0">
                <div class="d-flex justify-content-between align-items-center my-3 mt-5">
                  <h2 class="fw-bold">Status Kesehatan Ibu Hamil</h2>
                </div>

                <div class="card border-0 shadow-sm p-3">No Data Available</div>

                <div class="card border border-primary shadow p-3 my-3">
                  <div class="table-responsive">
                    <canvas ref="indiChart_pregnancy"></canvas>
                  </div>
                </div>

                <div class="card border-0 shadow-sm p-3">
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Indikator</th>
                          <th v-for="(bulan, idx) in bulanLabels" :key="idx">
                            {{ bulan }}
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(indikator, idx) in pregnancy_data" :key="idx">
                          <td>{{ indikator.nama }}</td>
                          <td v-for="(value, i) in indikator.values" :key="i">{{ value }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Tab 3 -->
              <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" tabindex="0">
                <!-- Title -->
                <div class="d-flex justify-content-between align-items-center my-3 mt-5">
                  <h2 class="fw-bold">Status Kesehatan Calon Pengantin</h2>
                </div>

                <div class="card border-0 shadow-sm p-3">
                  <p class="text-warning mb-3">
                    *Data perhitungan berdasarkan tanggal terdaftar > filter bulan > tanggal
                    menikah.
                  </p>
                  <table class="table table-borderless align-middle">
                    <tbody>
                      <tr>
                        <td class="text-muted fw-bold">Status</td>
                        <td class="text-muted fw-bold">Jumlah</td>
                        <td class="text-muted fw-bold">Persen</td>
                        <td class="text-muted fw-bold">Tren</td>
                      </tr>
                      <tr>
                        <td>Calon Pengantin Beresiko</td>
                        <td>0</td>
                        <td>0 %</td>
                        <td>0 %</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <CopyRight />
      </div>
    </div>
  </div>
</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
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

document.addEventListener('DOMContentLoaded', function () {
  // eslint-disable-next-line no-undef
  new Litepicker({
    element: document.getElementById('daterange'),
    singleMode: false, // biar bisa pilih range (awal - akhir)
    format: 'DD MMM YYYY',
    numberOfMonths: 2, // tampil 2 bulan
    numberOfColumns: 2,
    lang: 'id-ID', // bahasa Indonesia
  })
})

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Dashboard',
  components: { NavbarAdmin, CopyRight, HeaderAdmin },
  data() {
    return {
      totalAnak: 37,
      dataGap: {
        pmt: { jumlah: 24, persen: 65 },
        bantuan: { jumlah: 15, persen: 41 },
        kunjungan: { jumlah: 8, persen: 22 },
      },
      selectedStatusGizi: '',
      selectedIntervensi: '',
      username: '',
      genderData: [
        {
          label: 'Laki - Laki',
          total: 589,
          circleClass: 'male-circle',
          titleClass: 'text-success',
          valueClass: 'text-success',
          categories: [
            { name: 'Sangat Kurang', value: 20 },
            { name: 'Kurang', value: 74 },
            { name: 'Normal', value: 393 },
            { name: 'Risiko Lebih', value: 11 },
            { name: 'Tidak Naik', value: 71 },
          ],
        },
        {
          label: 'Perempuan',
          total: 553,
          circleClass: 'female-circle',
          titleClass: 'text-warning',
          valueClass: 'text-warning',
          categories: [
            { name: 'Sangat Kurang', value: 17 },
            { name: 'Kurang', value: 71 },
            { name: 'Normal', value: 389 },
            { name: 'Risiko Lebih', value: 9 },
            { name: 'Tidak Naik', value: 67 },
          ],
        },
      ],
      genderData_tb: [
        {
          label: 'Laki - Laki',
          total: 589,
          circleClass: 'male-circle',
          titleClass: 'text-success',
          valueClass: 'text-success',
          categories: [
            { name: 'Sangat Pendek', value: 20 },
            { name: 'Pendek', value: 74 },
            { name: 'Normal', value: 393 },
            { name: 'Tinggi', value: 11 },
          ],
        },
        {
          label: 'Perempuan',
          total: 553,
          circleClass: 'female-circle',
          titleClass: 'text-warning',
          valueClass: 'text-warning',
          categories: [
            { name: 'Sangat Pendek', value: 17 },
            { name: 'Pendek', value: 71 },
            { name: 'Normal', value: 389 },
            { name: 'Tinggi', value: 9 },
          ],
        },
      ],
      genderData_bbtb: [
        {
          label: 'Laki - Laki',
          total: 589,
          circleClass: 'male-circle',
          titleClass: 'text-success',
          valueClass: 'text-success',
          categories: [
            { name: 'Gizi Buruk', value: 20 },
            { name: 'Gizi Kurang', value: 74 },
            { name: 'Gizi Baik', value: 393 },
            { name: 'Risiko Gizi Lebih', value: 11 },
            { name: 'Obesitas', value: 11 },
          ],
        },
        {
          label: 'Perempuan',
          total: 553,
          circleClass: 'female-circle',
          titleClass: 'text-warning',
          valueClass: 'text-warning',
          categories: [
            { name: 'Gizi Buruk', value: 20 },
            { name: 'Gizi Kurang', value: 74 },
            { name: 'Gizi Baik', value: 393 },
            { name: 'Risiko Gizi Lebih', value: 11 },
            { name: 'Obesitas', value: 11 },          ],
        },
      ],
      stats: [
        { title: 'RW', value: '1,000', icon: '/icons/icon1.png' },
        { title: 'RT', value: '100,000', icon: '/icons/icon2.png'},
        { title: 'Keluarga Terdaftar', value: '100 M', icon: '/icons/icon3.png' },
        { title: 'TPK', value: '1,234', icon: '/icons/icon4.png' },
        { title: 'Ibu Hamil', value: '56 K', icon: '/icons/icon5.png' },
        { title: 'Posyandu', value: '8 K', icon: '/icons/icon6.png' },
        { title: 'Bidan', value: '1,234', icon: '/icons/icon7.png' },
        { title: 'Calon Pengantin', value: '12 K', icon: '/icons/icon8.png' },
        { title: 'Anak <= 5 Tahun', value: '56', icon: '/icons/icon9.png' },
      ],
      activities: [
        { user: 'Alice', action: 'Created new project', date: '2025-08-13' },
        { user: 'Bob', action: 'Updated profile', date: '2025-08-12' },
        { user: 'Charlie', action: 'Added new user', date: '2025-08-11' },
      ],
      months: [
        'Juli 2025',
        'Juni 2025',
        'Mei 2025',
        'April 2025',
        'Maret 2025',
        'Februari 2025',
        'Januari 2025',
        'Desember 2024',
        'November 2024',
        'Oktober 2024',
        'September 2024',
        'Agustus 2024',
      ],
      pregnancy_data: [
        { nama: 'KEK', values: [29, 37, 20, 26, 24, 22, 24, 23, 56, 79, 10, 0] },
        { nama: 'Anemia', values: [134, 134, 126, 129, 134, 110, 94, 23, 67, 80, 12, 45] },
        { nama: 'Resiko', values: [711, 702, 684, 723, 716, 732, 725, 706, 712, 450, 711, 734] },
        { nama: 'Tinggi', values: [25, 20, 23, 22, 16, 14, 15, 90, 16, 50, 11, 23] },
      ],
      dataTable_bb: [
        {
          status: 'Sangat Kurang',
          jumlah: 24,
          persen: 2.8,
          tren: '2.80%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Kurang',
          jumlah: 94,
          persen: 10.96,
          tren: '10.96%',
          trenClass: 'text-success',
          trenIcon: 'bi bi-caret-down-fill',
        },
        {
          status: 'Normal',
          jumlah: 725,
          persen: 55,
          tren: '55 %',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Risiko Lebih',
          jumlah: 15,
          persen: 33.45,
          tren: '33.45%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Tidak Naik',
          jumlah: 287,
          persen: 33.45,
          tren: '33.45%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
      ],
      dataTable_tb: [
        {
          status: 'Sangat Pendek',
          jumlah: 21,
          persen: 2.45,
          tren: '2.45%',
          trenClass: 'text-success',
          trenIcon: 'bi bi-caret-down-fill',
        },
        {
          status: 'Pendek',
          jumlah: 149,
          persen: 17.37,
          tren: '17.37%',
          trenClass: 'text-success',
          trenIcon: 'bi bi-caret-down-fill',
        },
        {
          status: 'Normal',
          jumlah: 688,
          persen: 80.19,
          tren: ' 80.19%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Tinggi',
          jumlah: 34,
          persen: 2,
          tren: '2%',
          trenClass: 'text-muted',
          trenIcon: '',
        },
      ],
      dataTable_status: [
        {
          status: 'Gizi Buruk',
          jumlah: 4,
          persen: 2.80 ,
          tren: '2.80%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Gizi Kurang',
          jumlah: 20,
          persen: 10.96 ,
          tren: '10.96%',
          trenClass: 'text-success',
          trenIcon: 'bi bi-caret-down-fill',
        },
        {
          status: 'Gizi Baik',
          jumlah: 769,
          persen: 84.50 ,
          tren: ' 84.50%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Risiko Gizi Lebih',
          jumlah: 53,
          persen: 1.75,
          tren: '1.75%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Gizi Lebih',
          jumlah: 8,
          persen: 33.45,
          tren: '33.45%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Obesitas',
          jumlah: 10,
          persen: 12,
          tren: '12%',
          trenClass: 'text-muted',
          trenIcon: '',
        },
      ],
      dataTable_kurang: [
        {
          nama:'Monkey D. Garp',
          nik:'332198387456670',
          status_bb:'kurang',
          status_tb:'kurang',
          status_gizi:'stunting',
          jk:'L',
          usia:'36 Bulan',
          intervensi:'Sudah dapat bantuan'
        },
        {
          nama:'Aluna Daneen Azqiara',
          nik:'3403012012930002',
          status_bb:'sangat kurang',
          status_tb:'kurang',
          status_gizi:'gizi buruk',
          jk:'P',
          usia:'38 Bulan',
          intervensi:'Sudah dapat bantuan'
        },
        {
          nama:'Syiffa Azahra',
          nik:'3403012806910002',
          status_bb:'kurang',
          status_tb:'kurang',
          status_gizi:'stunting',
          jk:'P',
          usia:'42 Bulan',
          intervensi:'belum dapat'
        },
      ],
      isCollapsed: false,
      usiaChartInstance: null,
      usiaChartInstance_tb: null,
      usiaChartInstance_bbtb: null,
      kelompokUmur: ['0-5', '6-11', '12-17', '18-23', '24-35', '36-47', '48-60'],
      statusData: {
        'Sangat Kurang': [2, 3, 5, 6, 8, 4, 3],
        Kurang: [4, 6, 7, 8, 10, 7, 6],
        Normal: [10, 20, 75, 68, 170, 150, 110],
        'Risiko Lebih': [1, 1, 2, 2, 3, 2, 1],
        'Tidak Naik': [3, 5, 20, 22, 70, 60, 55],
      },
      statusData_tb: {
        'Sangat Pendek': [2, 3, 5, 6, 8, 4, 3],
        'Pendek': [4, 6, 7, 8, 10, 7, 6],
        Normal: [10, 20, 75, 68, 170, 150, 110],
        'Tinggi': [1, 1, 2, 2, 3, 2, 1],
      },
      statusData_bbtb: {
        'Gizi Buruk': [2, 3, 5, 6, 8, 4, 3],
        'Gizi Kurang': [4, 6, 7, 8, 10, 7, 6],
        'Gizi Baik': [10, 20, 75, 68, 170, 150, 110],
        'Risiko Gizi Lebih': [1, 1, 2, 2, 3, 2, 1],
        'Gizi Lebih': [1, 1, 2, 2, 3, 2, 1],
        'Obesitas': [1, 1, 2, 2, 3, 2, 1],
      },
      indiChartInstance_tb: null,
      indiChartInstance_bbtb: null,
      indiChartInstance: null,
      indiChartPregnancy: null,
      bulanLabels: [],
      indikatorData: {
        'Sangat Kurang': [29, 37, 20, 26, 24, 22, 24, 23, 56, 79, 10, 0],
        Kurang: [134, 134, 126, 129, 134, 110, 94, 23, 67, 80, 12, 45],
        Normal: [711, 702, 684, 723, 716, 732, 725, 706, 712, 450, 711, 734],
        'Risiko Lebih': [25, 20, 23, 22, 16, 14, 15, 90, 16, 50, 11, 23],
        'Tidak Naik': [199, 290, 206, 286, 294, 238, 0, 0, 0, 0, 90, 40],
      },
      indikatorData_tb: {
        'Sangat Pendek': [29, 37, 20, 26, 24, 22, 24, 23, 56, 79, 10, 0],
        'Pendek': [134, 134, 126, 129, 134, 110, 94, 23, 67, 80, 12, 45],
        'Normal': [711, 702, 684, 723, 716, 732, 725, 706, 712, 450, 711, 734],
        'Tinggi': [25, 20, 23, 22, 16, 14, 15, 90, 16, 50, 11, 23],
      },
      indikatorData_bbtb: {
        'Gizi Buruk': [29, 37, 20, 26, 24, 22, 24, 23, 56, 79, 10, 0],
        'Gizi Kurang': [134, 134, 126, 129, 134, 110, 94, 23, 67, 80, 12, 45],
        'Gizi Baik': [711, 702, 684, 723, 716, 732, 725, 706, 712, 450, 711, 734],
        'Risiko Gizi Lebih': [25, 20, 23, 22, 16, 14, 15, 90, 16, 50, 11, 23],
        'Gizi Lebih': [215, 20, 23, 22, 136, 164, 15, 90, 16, 50, 11, 23],
        'Obesitas': [205, 20, 23, 122, 216, 14, 15, 90, 16, 50, 11, 23],
      },
      totalKasus: 215,
      dataAnak: [
        {
          nama: 'Aluna Daneen Azqiara',
          nik: '3403012012930002',
          stunting: true,
          wasting: false,
          underweight: false,
          bb_stagnant: false,
          overweight: false,
        },
        {
          nama: 'Arkhanza Raffasya Pamulat',
          nik: '3403010508980002',
          stunting: false,
          wasting: true,
          underweight: false,
          bb_stagnant: false,
          overweight: false,
        },
        {
          nama: 'Askara Gedhe Manah Sinatrya',
          nik: '3403011105950001',
          stunting: false,
          wasting: false,
          underweight: true,
          bb_stagnant: false,
          overweight: false,
        },
        {
          nama: 'Azka Maulana Fadil',
          nik: '3403011212980002',
          stunting: false,
          wasting: false,
          underweight: false,
          bb_stagnant: true,
          overweight: false,
        },
        {
          nama: 'Irshad Ghani Arvarizi',
          nik: '3403012507930001',
          stunting: false,
          wasting: false,
          underweight: false,
          bb_stagnant: false,
          overweight: true,
        },
        {
          nama: 'Syiffa Azahra',
          nik: '3403012806910002',
          stunting: false,
          wasting: false,
          underweight: false,
          bb_stagnant: true,
          overweight: false,
        },
      ],
    }
  },
  methods: {
    exportCSV() {
      if (this.filteredData.length === 0) {
        alert('Tidak ada data untuk diexport.')
        return
      }

      const header = [
        'Nama',
        'NIK',
        'Status BB/U',
        'Status TB/U',
        'Status BB/TB',
        'JK',
        'Usia',
        'Intervensi',
      ]

      const rows = this.filteredData.map(row => [
        row.nama,
        row.nik,
        row.status_bb,
        row.status_tb,
        row.status_gizi,
        row.jk,
        row.usia,
        row.intervensi,
      ])

      // Gabungkan ke CSV format
      const csvContent =
        'data:text/csv;charset=utf-8,' +
        [header, ...rows].map(e => e.join(',')).join('\n')

      const encodedUri = encodeURI(csvContent)
      const link = document.createElement('a')
      link.setAttribute('href', encodedUri)
      const filename =
        'data-anak-kurang-' +
        new Date().toISOString().split('T')[0] +
        '.csv'
      link.setAttribute('download', filename)
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    renderChart(refName, dataSource, colors) {
      const ctx = this.$refs[refName]?.getContext('2d')
      if (!ctx) return

      // Hancurkan chart lama
      if (this[refName + 'Instance']) {
        this[refName + 'Instance'].destroy()
      }

      // Buat warna transparan & border
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
          layout: { padding: 20 },
          plugins: {
            legend: {
              display: true,
              position: 'bottom',
              labels: {
                usePointStyle: true,
                pointStyle: 'circle',
                padding: 16,
                font: { size: 11 },
                color: '#333',
              },
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  const label = context.label || ''
                  const value = context.parsed
                  return `${label}: ${value}%`
                },
              },
            },
            datalabels: {
              display: false, // ❌ tidak tampilkan label di dalam chart
            },
          },
          onHover: (event, elements) => {
            event.native.target.style.cursor = elements.length ? 'pointer' : 'default'
          },
          onClick: (event, elements) => {
            if (elements.length > 0) {
              const index = elements[0].index
              const item = dataSource[index]
              console.log(`Klik pada: ${item.status} (${item.persen}%)`)
            }
          },
        },
        plugins: [ChartDataLabels],
      })
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
    renderIndiChart_pregnancy() {
      const ctx = this.$refs.indiChart_pregnancy.getContext('2d')

      if (this.indiChartInstance_pregnancy) {
        this.indiChartInstance_pregnancy.destroy()
      }

      // Warna beda2 untuk tiap dataset
      const colors = {
        KEK: { border: '#d9534f', bg: 'rgba(217, 83, 79, 0.2)' },
        Anemia: { border: '#f0ad4e', bg: 'rgba(240, 173, 78, 0.2)' },
        Resiko: { border: '#5cb85c', bg: 'rgba(92, 184, 92, 0.3)' },
        Tinggi: { border: '#0275d8', bg: 'rgba(2, 117, 216, 0.2)' },
      }

      const datasets = this.pregnancy_data.map(item => {
        return {
          label: item.nama,
          data: item.values,
          borderColor: colors[item.nama]?.border || '#999',
          backgroundColor: colors[item.nama]?.bg || 'rgba(153,153,153,0.2)',
          fill: item.nama === 'Resiko', // khusus "Resiko" di-fill area
          tension: 0.3,
          pointRadius: 4,
          pointHoverRadius: 6,
        }
      })

      this.indiChartInstance_pregnancy = new Chart(ctx, {
        type: 'line',
        data: {
          labels: this.bulanLabels,
          datasets: datasets,
        },
        options: {
          responsive: true,
          interaction: { mode: 'nearest', intersect: false },
          plugins: {
            legend: { position: 'top' },
            tooltip: { enabled: true },
          },
          scales: {
            y: {
              beginAtZero: true,
              title: { display: true, text: 'Jumlah Individu' },
            },
            x: {
              title: { display: true, text: 'Bulan' },
            },
          },
        },
      })
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
          labels: ['Ditugaskan kader', 'Dikunjungi kader', 'Dapat PMT'],
          datasets: [
            {
              data: [8, 20, 9],
              backgroundColor: ['#5B9BD5', '#4472C4', '#305496'],
            },
          ],
        },
        options: {
          indexAxis: 'y',
          plugins: { legend: { display: false } },
          scales: {
            x: { beginAtZero: true },
          },
        },
      })
    },
  },
  computed: {
    background() {
      const config = JSON.parse(localStorage.getItem('siteConfig'))
      return config && config.background ? config.background : null
    },
    uniqueStatusGizi() {
        return [...new Set(this.dataTable_kurang.map(row => row.status_gizi))]
      },
      uniqueIntervensi() {
        return [...new Set(this.dataTable_kurang.map(row => row.intervensi))]
      },
      filteredData() {
        return this.dataTable_kurang.filter(row => {
          const byStatus =
            this.selectedStatusGizi === '' ||
            row.status_gizi === this.selectedStatusGizi
          const byIntervensi =
            this.selectedIntervensi === '' ||
            row.intervensi === this.selectedIntervensi
          return byStatus && byIntervensi
        })
      },
  },
  created() {
    const storedEmail = localStorage.getItem('userEmail')
    if (storedEmail) {
      // Ambil bagian sebelum @
      let namePart = storedEmail.split('@')[0]

      // Ganti titik/underscore jadi spasi
      namePart = namePart.replace(/[._]/g, ' ')

      // Ubah huruf pertama tiap kata jadi kapital
      this.username = namePart
        .split(' ')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ')
    } else {
      this.username = 'User'
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.bulanLabels = this.getLast12Months() // <- generate bulan realtime
      this.renderIndiChart_pregnancy()
      this.renderChart('pieChart_bb', this.dataTable_bb, [
        '#E6C200',
        '#F3BC07',
        '#006341',
        '#0E00AE',
        '#D60000',
      ])
      this.renderChart('pieChart_tb', this.dataTable_tb, [
        '#E6C200',
        '#F3BC07',
        '#006341',
        '#0E00AE',
        '#D60000',
      ])
      this.renderChart('pieChart_status', this.dataTable_status, [
        '#E6C200',
        '#F3BC07',
        '#006341',
        '#0E00AE',
        '#D60000',
        'violet',
      ])
    })
    this.renderLineChart()
    this.renderBarChart()
    this.renderFunnelChart()
  },
}
</script>

<style scoped>
.circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  font-weight: bold;
  margin: 0 auto 15px auto;
  color: #fff;
}
.male-circle {
  background-color: rgba(0, 128, 96, 0.8);
}
.female-circle {
  background-color: rgba(204, 170, 85, 0.6);
}
.title {
  font-weight: bold;
  text-align: center;
  margin-bottom: 15px;
}
@media (min-width: 1200px) {
  .stat-col {
    flex: 0 0 10%;
    max-width: 10%;
  }
}

.dashboard-wrapper {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  color: #333;
}

/* Card Statistik */
.stat-card {
  width: 130px;
  height: 80px;
  border-radius: 1rem;
  background-color: #fff;
  transition: all 0.3s ease;
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 18px rgba(0, 0, 0, 0.25);
}

/* Icon wrapper */
.icon-wrapper {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(0, 123, 255, 0.08);
}

/* Responsif: sedikit lebih besar di layar kecil */
@media (max-width: 1200px) {
  .stat-card {
    width: 140px;
  }
}
@media (max-width: 992px) {
  .stat-card {
    width: 160px;
  }
}

/* Form & Select */
.form-select,
.btn {
  /* border-radius: 0.75rem; */
  transition: all 0.2s ease-in-out;
}
.form-select:focus,
.btn:focus {
  box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
}

/* Card konten */
.card {
  border-radius: 1rem !important;
  border: none;
  background: #fff;
  transition: box-shadow 0.2s ease;
}
.card:hover {
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

/* Table */
.table {
  font-size: 0.95rem;
}
.table th {
  color: #6c757d;
  font-weight: 600;
}
.table td {
  color: #333;
}

.status-card {
  background-color: #FAF9F6;
  box-shadow: 0 7px 4px rgba(0,0,0,0.1);
  text-align: left;
  padding: 20px 20px;
  position: relative;
  overflow: hidden;
  flex: 1;
  min-width: 140px;
}

.status-card h5 {
  font-size: 1.3rem;
  font-weight: 700;
  /* margin-bottom: 10px; */
}

.status-card .value {
  font-size: 2rem;
  font-weight: 700;
}

.status-card .percent {
  font-size: 1rem;
  font-weight: 500;
  margin-bottom: 25px;
}

.status-line {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 35px;
  opacity: 0.5;
}

/* warna khusus per kategori */
.stunting { color: #d33; }
.wasting { color: #e6b800; }
.underweight { color: #0033cc; }
.normal { color: #007a33; }
.stagnan { color: #e6b800; }
.overweight { color: #1e90ff; }
.total { color: #0077cc; }

.status-line.stunting { background-image: url('/chart/card1.png'); background-repeat: no-repeat; background-size:cover;}
.status-line.wasting { background-image: url('/chart/card2.png'); background-repeat: no-repeat; background-size:cover; }
.status-line.underweight { background-image: url('/chart/card3.png'); background-repeat: no-repeat; background-size:cover; }
.status-line.normal { background-image: url('/chart/card4.png'); background-repeat: no-repeat; background-size:cover; }
.status-line.stagnan { background-image: url('/chart/card5.png'); background-repeat: no-repeat; background-size:cover; }
.status-line.overweight { background-image: url('/chart/card6.png'); background-repeat: no-repeat; background-size:cover; }
.status-line.total { background: linear-gradient(to top, #e6f2ff 0%, transparent 100%); }

.info-card {
  background-color: #FAF9F6;
  width: 280px;
  min-height: 100px;
  box-shadow: 0 7px 5px rgba(0, 0, 0, 0.1) !important;
}
</style>
