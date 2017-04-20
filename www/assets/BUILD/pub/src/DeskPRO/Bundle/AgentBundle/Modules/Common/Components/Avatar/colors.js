const colors = [
  '#b8c3e0',
  '#cdd7e0',
  '#a3e4e8',
  '#5fabaf',
  '#62b1cd',
  '#41b1ad',
  '#579baa',
  '#4f91a3',
  '#74d6df',
  '#6ecad9',
  '#68bdd3',
  '#bff7e0',
  '#3ac7a6',
  '#69e0b5',
  '#4ac35f',
  '#50d066',
  '#4fa85a',
  '#57aa85',
  '#a2f2ac',
  '#23c48a',
  '#35d8a1',
  '#56dd6c',
  '#62f678',
  '#b1eb69',
  '#db7b8c',
  '#c38dcf',
  '#a972b7',
  '#e1a9cd',
  '#cda1d7',
  '#c277a2',
  '#f9d9e4',
  '#f7c9d7',
  '#efdece',
  '#f4b0c2',
  '#ec9a7e',
  '#ffdebb',
  '#ffbd7b',
  '#ffd345',
  '#ffe161',
  '#fecc65',
  '#ffe98a',
  '#feab50'
];

function normalizeHex(hex) {
  let newHex = String(hex).replace(/[^0-9a-f]/gi, '');
  if (newHex.length < 6) {
    newHex = newHex[0] + newHex[0] + newHex[1] + newHex[1] + newHex[2] + newHex[2];
  }

  return newHex;
}

export function colorLuminance(hex, lum = 0) {
  const newHex = normalizeHex(hex);
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

// see https://www.w3.org/TR/AERT#color-contrast
export function isDarkBg(hex) {
  const newHex = normalizeHex(hex);

  const r = parseInt(newHex.substr(0, 2), 16);
  const g = parseInt(newHex.substr(2, 2), 16);
  const b = parseInt(newHex.substr(4, 2), 16);
  const o = Math.round(((r * 299) + (g * 587) + (b * 114)) / 1000);

  return o < 125;
}
