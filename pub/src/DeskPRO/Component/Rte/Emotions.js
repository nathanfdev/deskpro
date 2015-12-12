import classNames from 'classnames';

const smiles = {
  ICON_ANGEL: 'O:)',
  ICON_EVIL_GREEN: ']:)',
  ICON_LAUGHING: ':))',
  ICON_SMILE: ':)',
  ICON_BLUSHING: ':$',
  ICON_WINKING: ';)',
  ICON_GRIN: ':D',
  ICON_YAWN: ':yawn:',
  ICON_DEVIL: '(6)',
  ICON_KIKI: '^_^',
  ICON_TONGUE: ':p',
  ICON_TONGUE_2: ':P',
  ICON_SAD: ':(',
  ICON_HEART: ['<3', '&lt;3'],
  ICON_INLOVE: ':inlove:',
  ICON_KISS: ':*',
  ICON_CRY: ';(',
  ICON_SUPRISED: ':O',
  ICON_CONFUSED: ':/',
  ICON_DISAPPOINTED: ':|'
};

const SPRITE_MAP = {
  [smiles.ICON_SMILE]: 1,
  [smiles.ICON_BLUSHING]: 2,
  [smiles.ICON_WINKING]: 3,
  [smiles.ICON_TONGUE]: 4,
  [smiles.ICON_TONGUE_2]: 5,
  [smiles.ICON_LAUGHING]: 6,
  [smiles.ICON_GRIN]: 7,
  [smiles.ICON_EVIL_GREEN]: 8,
  [smiles.ICON_DEVIL]: 9,
  [smiles.ICON_KIKI]: 10,
  [smiles.ICON_YAWN]: 11,
  [smiles.ICON_HEART]: 12,
  [smiles.ICON_INLOVE]: 13,
  [smiles.ICON_KISS]: 14,
  [smiles.ICON_ANGEL]: 15,
  [smiles.ICON_SAD]: 16,
  [smiles.ICON_CRY]: 22,
  [smiles.ICON_SUPRISED]: 23,
  [smiles.ICON_CONFUSED]: 21,
  [smiles.ICON_DISAPPOINTED]: 20
};

function createEmotionImage(code) {
  const className = classNames('emoticon', 'sprite', `sprite-emoticon-${SPRITE_MAP[code]}`);
  return `<img class="${className}">`;
}

function replaceSmileCodes(content, inverse = false) {
  let text = String(content);
  let num;

  for (num in smiles) {
    if (smiles.hasOwnProperty(num)) {
      const codes = smiles[num];
      const arrayCodes = Array.isArray(codes) ? codes : [codes];

      const image = createEmotionImage(codes);
      if (inverse) {
        text = text.replace(image, arrayCodes[0]);
      } else {
        arrayCodes.forEach(code => text = text.replace(code, image));
      }
    }
  }

  return text;
}

export default {
  ...smiles,

  SPRITE_MAP,

  createEmotionImage,
  replaceSmileCodes
};
