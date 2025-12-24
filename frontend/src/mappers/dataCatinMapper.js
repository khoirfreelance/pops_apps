/**
 * Mapping data_anak menjadi format tabel / excel
 */
export function mapDataCatinToExcel(dataCatin) {
  return dataCatin.map((catin, index) => {
     const tanggalKunjungan = catin.tgl_kunjungan
      ? catin.tgl_kunjungan.split("T")[0]
      : "-";
     const tanggalMenikah = catin.tgl_menikah
      ? catin.tgl_menikah.split("T")[0]
      : "-";

    return {
      No: index + 1,
      Perempuan: catin.nama_perempuan || "-",
      Laki_laki: catin.nama_laki || "-",
      Anemia: catin.pemeriksaan_terakhir.status_hb || "-",
      Kek: catin.pemeriksaan_terakhir.status_kek || "-",
      Risiko_Usia: catin.pemeriksaan_terakhir.status_risiko || "-",
      Usia_Perempuan: catin.usia_perempuan || "-",
      Usia_Laki_laki: catin.usia_laki || "-",
      Pekerjaan_Perempuan: catin.pekerjaan_perempuan || "-",
      Pekerjaan_Laki_laki: catin.pekerjaan_laki || "-",
      Posyandu: catin.posyandu || "-",
      RW: catin.rw || "-",
      RT: catin.rt || "-",
      Tanggal_Kunjungan: tanggalKunjungan || "-",
      Tanggal_Menikah : tanggalMenikah || "-",
      BB_Perempuan : catin.pemeriksaan_terakhir.berat_perempuan || "-",
      TB_Perempuan : catin.pemeriksaan_terakhir.tinggi_perempuan || "-",
      Lila_Perempuan : catin.pemeriksaan_terakhir.lila_perempuan || "-",
      Hb_Perempuan : catin.pemeriksaan_terakhir.hb_perempuan || "-",
      Riwayat_Penyakit : catin.pemeriksaan_terakhir.riwayat_penyakit != '' ? "Ya" : "Tidak",
      Jamban_Sehat : catin.pemeriksaan_terakhir.menggunakan_jamban == true ? "Ya" : "Tidak",
      Sumber_Air_Bersih : catin.pemeriksaan_terakhir.sumber_air_bersih == true ? "Ya" : "Tidak",
    };
  });
}
