import valid from 'card-validator';

export function isCardVisa(number) {
  const numberValidation = valid.number(number);
  if (numberValidation.card) {
    return numberValidation.card.type === 'visa';
  }
  return false;
}

export function isCardMasterCard(number) {
  const numberValidation = valid.number(number);
  if (numberValidation.card) {
    return numberValidation.card.type === 'master-card';
  }
  return false;
}

export function isCardAmex(number) {
  const numberValidation = valid.number(number);
  if (numberValidation.card) {
    return numberValidation.card.type === 'american-express';
  }
  return false;
}

export function formatCardNumber(value) {
  const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
  const matches = v.match(/\d{1,16}/g);
  const match = (matches && matches[0]) || '';
  const parts = [];

  const numberValidation = valid.number(value);
  let gaps = [];
  if (numberValidation.card) {
    gaps = numberValidation.card.gaps;
  }

  for (let i = 0, len = match.length; i < len; i += 1) {
    if (gaps.indexOf(i) !== -1) {
      parts.push(' ');
    }
    parts.push(match[i]);
  }

  return parts.join('');
}

export function validateCard(value) {
  const numberValidation = valid.number(value);
  return numberValidation.isPotentiallyValid || numberValidation.isValid;
}

export function validateMonth(value) {
  return valid.expirationMonth(value).isPotentiallyValid;
}

export function validateYear(value) {
  return valid.expirationYear(value).isPotentiallyValid;
}

export function validateExpiry(value) {
  return valid.expirationDate(value).isPotentiallyValid;
}

export function ccvLength(value) {
  const numberValidation = valid.number(value);
  if (numberValidation.card) {
    return numberValidation.card.code.size;
  }
  return 3;
}
