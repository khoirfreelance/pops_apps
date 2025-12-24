/**
 * Mapping data_anak menjadi format tabel / excel
 */
export function mapDataAnakToExcel(dataAnak) {
  return dataAnak.map((anak, index) => {
    // const posyandu = anak.posyandu?.[0] || {};
    // const intervensi = anak.intervensi?.[0] || {};

    return {
      No: index + 1,
      Nama: anak.nama,
      NIK: anak.nik,
      Jenis_Kelamin: anak.gender,
      Posyandu: anak.posyandu || "-",
      Usia_Bulan: anak.usia || "-",
      Tgl_Ukur_Terakhir: anak.tgl_ukur || "-",
      TB_U: anak.tbu || "-",
      BB_U: anak.bbu || "-",
      BB_TB: anak.bbtb || "-",
      Intervensi: anak.intervensi !== '-'? anak.intervensi:"Belum Mendapatkan Intervensi",
      RW: anak.rw,
      RT: anak.rt,
    };
  });
}
