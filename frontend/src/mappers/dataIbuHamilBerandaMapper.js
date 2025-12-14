/**
 * Mapping data_anak menjadi format tabel / excel
 */
export function mapDataIbuHamilBerandaToExcel(dataIbu) {
 return dataIbu.map((ibu, index) => {

    return {
      No: index + 1,
      Nama: ibu.nama ?? "-",
      Jenis_Intervensi: ibu.intervensi ?? "-",
      Anemia: ibu.anemia === true ? "✓" : "",
      Kehamilan_Berisiko: ibu.resiko === true ? "✓" : "",
      Kek: ibu.kek === true ? "✓" : "",
      RT: ibu.rt ?? "-",
      RW: ibu.rw ?? "-",
      Usia_Tahun: ibu.umur ?? "-",
    };
  });
}
