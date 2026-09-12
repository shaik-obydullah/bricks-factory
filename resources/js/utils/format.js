export function humanize(value) {
  if (value === null || value === undefined) return ''
  const str = String(value)
  if (!str) return str
  return str.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
}

const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']

function dateSuffix(day) {
  if (day % 100 >= 11 && day % 100 <= 13) return 'th'
  switch (day % 10) {
    case 1: return 'st'
    case 2: return 'nd'
    case 3: return 'rd'
    default: return 'th'
  }
}

function pad(n) {
  return String(n).padStart(2, '0')
}

export function formatDate(value) {
  if (!value) return ''
  let date
  if (typeof value === 'string') {
    const m = value.match(/^(\d{4})-(\d{2})-(\d{2})/)
    if (m) {
      date = new Date(+m[1], +m[2] - 1, +m[3])
    } else {
      date = new Date(value.includes(' ') ? value.replace(' ', 'T') : value)
    }
  } else {
    date = new Date(value)
  }
  if (isNaN(date.getTime())) return String(value)

  const format = localStorage.getItem('date_format') || 'Y-m-d'
  const tokens = {
    Y: String(date.getFullYear()),
    y: String(date.getFullYear()).slice(-2),
    m: pad(date.getMonth() + 1),
    n: String(date.getMonth() + 1),
    d: pad(date.getDate()),
    j: String(date.getDate()),
    S: dateSuffix(date.getDate()),
    F: MONTHS[date.getMonth()],
    M: MONTHS[date.getMonth()].slice(0, 3),
    l: DAYS[date.getDay()],
    D: DAYS[date.getDay()].slice(0, 3),
  }
  return format.replace(/[YymndjSFMlD]/g, (t) => tokens[t] || t)
}
