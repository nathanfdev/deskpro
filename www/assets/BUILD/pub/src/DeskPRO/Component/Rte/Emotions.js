import classNames from 'classnames';

export const smiles = {
  ICON_ANGEL:        'O:)',
  ICON_EVIL_GREEN:   ']:)',
  ICON_LAUGHING:     ':))',
  ICON_SMILE:        ':)',
  ICON_BLUSHING:     ':$',
  ICON_WINKING:      ';)',
  ICON_GRIN:         ':D',
  ICON_YAWN:         ':yawn:',
  ICON_DEVIL:        '(6)',
  ICON_KIKI:         '^_^',
  ICON_TONGUE:       ':p',
  ICON_TONGUE_2:     ':P',
  ICON_SAD:          ':(',
  ICON_HEART:        [':heart:'],
  ICON_INLOVE:       ':inlove:',
  ICON_KISS:         ':*',
  ICON_CRY:          ';(',
  ICON_SUPRISED:     ':O',
  ICON_CONFUSED:     ':/',
  ICON_DISAPPOINTED: ':|'
};

export const spriteMap = {
  [smiles.ICON_SMILE]:        1,
  [smiles.ICON_BLUSHING]:     2,
  [smiles.ICON_WINKING]:      3,
  [smiles.ICON_TONGUE]:       4,
  [smiles.ICON_TONGUE_2]:     5,
  [smiles.ICON_LAUGHING]:     6,
  [smiles.ICON_GRIN]:         7,
  [smiles.ICON_EVIL_GREEN]:   8,
  [smiles.ICON_DEVIL]:        9,
  [smiles.ICON_KIKI]:         10,
  [smiles.ICON_YAWN]:         11,
  [smiles.ICON_HEART]:        12,
  [smiles.ICON_INLOVE]:       13,
  [smiles.ICON_KISS]:         14,
  [smiles.ICON_ANGEL]:        15,
  [smiles.ICON_SAD]:          16,
  [smiles.ICON_CRY]:          22,
  [smiles.ICON_SUPRISED]:     23,
  [smiles.ICON_CONFUSED]:     21,
  [smiles.ICON_DISAPPOINTED]: 20
};

export function createEmotionImage(code) {
  const className = classNames('emoticon', 'sprite', `sprite-emoticon-${spriteMap[code]}`);
  return `<img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" class="${className}">`;
}

export function createCodeHtml(code) {
  return `<span class="smile">${code}</span>`;
}

export function replaceSmileCodes(content, inverse = false) {
  let text = String(content);

  Object.keys(smiles).forEach((key) => {
    const codes      = smiles[key];
    const arrayCodes = Array.isArray(codes) ? codes : [codes];
    const image      = createEmotionImage(codes);

    if (inverse) {
      text = text.replace(image, createCodeHtml(arrayCodes[0]));
    } else {
      arrayCodes.forEach(code => {
        text = text.replace(createCodeHtml(code), image);
        return null;
      });
    }
  });

  return text;
}
