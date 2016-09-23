export function isCardVisa(number) {
  return number.match(/^4/);
}

export function isCardMasterCard(number) {
  return number.match(/^5[0-5]/);
}

export function isCardAmex(number) {
  return number.match(/^3[4,7]/);
}

export function formatCardNumber(value) {
  const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
  const matches = v.match(/\d{1,16}/g);
  const match = (matches && matches[0]) || '';
  const parts = [];

  const spaces = isCardAmex(value) ? [4, 10] : [4, 8, 12];

  for (let i = 0, len = match.length; i < len; i += 1) {
    if (spaces.indexOf(i) !== -1) {
      parts.push(' ');
    }
    parts.push(match[i]);
  }

  return parts.join('');
}
