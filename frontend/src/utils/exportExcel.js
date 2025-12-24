import * as XLSX from 'xlsx'

export function exportExcel({ sheets, fileName }) {
  const workbook = XLSX.utils.book_new()

  sheets.forEach(sheet => {
    const worksheet = XLSX.utils.json_to_sheet(sheet.data)
    XLSX.utils.book_append_sheet(
      workbook,
      worksheet,
      sheet.sheetName
    )
  })

  XLSX.writeFile(workbook, fileName)
}
