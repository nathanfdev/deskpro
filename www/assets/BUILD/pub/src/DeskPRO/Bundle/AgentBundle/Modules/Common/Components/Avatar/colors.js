const colors = [
  '#FF8040',
  '#00C0FF',
  '#FFFF80',
  '#8080C0',
  '#FFFFC0',
  '#40C0C0',
  '#C0FF00',
  '#C0FFFF',
  '#80C040',
  '#C0FF80',
  '#FF80FF'
];

export function chooseColor(number) {
  return colors[Number(number) % colors.length];
}
