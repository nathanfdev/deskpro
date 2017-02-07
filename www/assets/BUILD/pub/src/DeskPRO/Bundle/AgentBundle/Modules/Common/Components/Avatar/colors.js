const colors = [
  '#d3585a',
  '#ef2695',
  '#bae074',
  '#e3f961',
  '#0456ed',
  '#70f747',
  '#4510a0',
  '#aeef8b',
  '#db320d',
  '#e5878a',
  '#f9e9a9',
  '#ffccc6',
  '#3a53af',
  '#1b34d3',
  '#f27a02',
  '#df10ea',
  '#e1ffa5',
  '#b51cc9',
  '#eaf736',
  '#96e07b',
  '#d19c4d',
  '#79f46b',
  '#c9bf04',
  '#ed5721',
  '#6220fc',
  '#6ded93',
  '#e26f8e',
  '#88baf7',
  '#83f7c3',
  '#b1f9a4',
  '#3673c9',
  '#f23053',
  '#71ed7e',
  '#efbe99',
  '#e7f473',
  '#eaa970',
  '#efa0e6',
  '#dd7f68',
  '#6c93c9',
  '#5048a5',
  '#7af4b5',
  '#46d68e',
  '#b185f7',
  '#afdb00',
  '#dca7f2',
  '#676cdb',
  '#c4038a',
  '#69e57e',
  '#6c91c9',
  '#e26f76',
  '#ffdf0f',
  '#071a66',
  '#43cc1a',
  '#b3f24d',
  '#3e2b91',
  '#e5a454',
  '#da27dd',
  '#a6aeed',
  '#8fefb6',
  '#9739d6',
  '#f7ffaa',
  '#eda3b3',
  '#53e256',
  '#f7c8bb',
  '#e8bb94',
  '#90e24d',
  '#5b48d6',
  '#14e7ff',
  '#0b2966',
  '#a4fcb7',
  '#51db98',
  '#3afcc5',
  '#fc46db',
  '#d3b045',
  '#fc79ef',
  '#b8d64f',
  '#593ca8',
  '#f2af80',
  '#1cdd1c',
  '#6bef76',
  '#a2d2ef',
  '#e041ab',
  '#dbaa76',
  '#6e1ea8',
  '#f4d430',
  '#a1f4b0',
  '#cec2f9',
  '#2d63ed',
  '#bc016e',
  '#f4ba86',
  '#1a3aaf',
  '#e8b540',
  '#e0c30b',
  '#ffa5f9',
  '#4f37ef',
  '#dbc057',
  '#4100f7',
  '#8c9307',
  '#f2b471',
  '#55dbb5'
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
