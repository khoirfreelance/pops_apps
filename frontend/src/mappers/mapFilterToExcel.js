import { FILTER_CONFIG } from './filterConfig'

export function mapFilterToExcel(filters, type = 'Anak') {
  const rows = []
  const config = FILTER_CONFIG[type]

  if (!config) {
    console.warn(`Filter config untuk tipe "${type}" tidak ditemukan`)
    return rows
  }

  config.forEach(item => {
    let value = filters[item.key]

    if (item.type === 'array') {
      value = Array.isArray(value) && value.length
        ? value.join(', ')
        : 'All'
    } else {
      value = value && value !== '' ? value : 'All'
    }

    rows.push({
      Filter: item.label,
      Nilai: value
    })
  })

  return rows
}
