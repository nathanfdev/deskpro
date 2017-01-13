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

export function colorLuminance(hex, lum = 0) {
  let newHex = String(hex).replace(/[^0-9a-f]/gi, '');
  if (newHex.length < 6) {
    newHex = newHex[0] + newHex[0] + newHex[1] + newHex[1] + newHex[2] + newHex[2];
  }

  let rgb = '#';
  let c;
  let i;
  for (i = 0; i < 3; i += 1) {
    c = parseInt(newHex.substr(i * 2, 2), 16);
    c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
    rgb += (`00${c}`).substr(c.length);
  }

  return rgb;
}

export function chooseColor(number) {
  return colors[Number(number) % colors.length];
}

export function darkerColor(number, darker = 0.2) {
  return colorLuminance(colors[Number(number) % colors.length], -darker);
}

export function lighterColor(number, lighter = 0.2) {
  return colorLuminance(colors[Number(number) % colors.length], lighter);
}

