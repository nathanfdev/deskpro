export function timeAgoFormatter(value, unit, suffix) {
  if (unit === 'second') {
    return 'a moment ago';
  }

  return value + ' ' + (value !== 1 ? (unit + 's') : unit) + ' ' + suffix;
}
