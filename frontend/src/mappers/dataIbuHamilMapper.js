/**
 * Mapping data_anak menjadi format tabel / excel
 */
export function mapDataIbuHamilToExcel(dataIbuHamil) {
  return dataIbuHamil.map((ibuHamil, index) => {
     const tanggalPemeriksaan = ibuHamil.tanggal_pemeriksaan_terakhir
      ? ibuHamil.tanggal_pemeriksaan_terakhir.split("T")[0]
      : "-";

    return {
      No: index + 1,
      Nama: ibuHamil.nama,
      Anemia: ibuHamil.anemia === "Anemia" ?  "Ya" : "Normal",
      Kehamilan_Berisiko: ibuHamil.risiko === "Berisiko" ?  "Ya" : "Normal",
      Kek: ibuHamil.kek === "KEK" ?  "Ya" : "Normal",
      Intervensi: ibuHamil.intervensi || "-",
      Posyandu: ibuHamil.posyandu || "-",
      RW: ibuHamil.rw,
      RT: ibuHamil.rt,
      Usia_Tahun: ibuHamil.usia || "-",
      Tanggal_Pemeriksaan: tanggalPemeriksaan || "-",
    };
  });
}
