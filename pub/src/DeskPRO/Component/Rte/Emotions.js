const smiles = {
  ICON_SMILE: ':)',
  ICON_WINKING: ';)',
  ICON_BIG_GRIN: ':D',
  ICON_ANGEL: 'O:)',
  ICON_DISAPPOINTED: ':|'
};

const SPRITE_MAP = {
  [smiles.ICON_SMILE]: 1,
  2: 2,
  [smiles.ICON_WINKING]: 3,
  4: 4,
  5: 5,
  6: 6,
  [smiles.ICON_BIG_GRIN]: 7,
  8: 8,
  9: 9,
  10: 10,
  11: 11,
  12: 12,
  13: 13,
  14: 14,
  [smiles.ICON_ANGEL]: 15,
  16: 16,
  17: 17,
  18: 18,
  19: 19,
  [smiles.ICON_DISAPPOINTED]: 20
};

function replaceSmileCodes() {

}

export default {
  ...smiles,

  SPRITE_MAP,
  replaceSmileCodes
};
