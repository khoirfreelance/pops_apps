/**
 * Mapping data_anak menjadi format tabel / excel
 */
export function mapDataAnakBerandaToExcel(dataAnak) {
 return dataAnak.map((anak, index) => {

    return {
      No: index + 1,
      Nama: anak.nama,
      Jenis_Intervensi: anak.rumusan,
      Stunting: anak.stunting === true ? "✓" : "",
      Wasting: anak.wasting === true ? "✓" : "",
      Underweight: anak.underweight === true ? "✓" : "",
      BB_Stagnan: anak.bb_stagnan === true ? "✓" : "",
      Overweight: anak.overweight === true ? "✓" : "",
    };
  });
}
