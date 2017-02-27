const FORMATS = {
  h1:      { type: 'block', token: 'header-1', before: '#', re: /^#\s+/, placeholder: 'Heading' },
  h2:      { type: 'block', token: 'header-2', before: '##', re: /^##\s+/, placeholder: 'Heading' },
  h3:      { type: 'block', token: 'header-3', before: '###', re: /^###\s+/, placeholder: 'Heading' },
  bold:    { type: 'inline', token: 'strong', before: '**', after: '**', placeholder: 'bold text' },
  italic:  { type: 'inline', token: 'em', before: '_', after: '_', placeholder: 'italic text' },
  quote:   { type: 'block', token: 'quote', re: /^>\s+/, before: '>', placeholder: 'quote' },
  oList:   { type: 'block', before: '1. ', re: /^\d+\.\s+/, placeholder: 'List' },
  uList:   { type: 'block', before: '* ', re: /^[*-]\s+/, placeholder: 'List' },
  info:    { type: 'multiLineBlock', before: '::: info', after: ':::', placeholder: 'Message' },
  warning: { type: 'multiLineBlock', before: '::: warning', after: ':::', placeholder: 'Message' },
  error:   { type: 'multiLineBlock', before: '::: error', after: ':::', placeholder: 'Message' },
  code:    { type: 'multiLineBlock', before: '```', after: '```', placeholder: 'Code' },
};

const FORMAT_TOKENS = {};
Object.keys(FORMATS).forEach((key) => {
  if (FORMATS[key].token) FORMAT_TOKENS[FORMATS[key].token] = key;
});

export function getCursorState(cm, pos) {
  const position = pos || cm.getCursor('start');
  const cs = {};
  const token = cs.token = cm.getTokenAt(position);
  if (!token.type) return cs;
  const tokens = token.type.split(' ');
  tokens.forEach((t) => {
    if (FORMAT_TOKENS[t]) {
      cs[FORMAT_TOKENS[t]] = true;
      return;
    }
    switch (t) {
      case 'link': {
        cs.link = true;
        cs.link_label = true;
        break;
      }
      case 'string': {
        cs.link = true;
        cs.link_href = true;
        break;
      }
      case 'variable-2': {
        const text = cm.getLine(position.line);
        if (/^\s*\d+\.\s/.test(text)) {
          cs.oList = true;
        } else {
          cs.uList = true;
        }
        break;
      }
      default:
        break;
    }
  });
  return cs;
}

const operations = {
  inlineApply(cm, format) {
    const startPoint = cm.getCursor('start');
    const endPoint = cm.getCursor('end');

    cm.replaceSelection(format.before + cm.getSelection() + format.after);

    startPoint.ch += format.before.length;
    endPoint.ch += format.after.length;
    cm.setSelection(startPoint, endPoint);
    cm.focus();
  },
  inlineRemove(cm, format) {
    const startPoint = cm.getCursor('start');
    const endPoint = cm.getCursor('end');
    const line = cm.getLine(startPoint.line);

    let startPos = startPoint.ch;
    while (startPos) {
      if (line.substr(startPos, format.before.length) === format.before) {
        break;
      }
      startPos -= 1;
    }

    let endPos = endPoint.ch;
    while (endPos <= line.length) {
      if (line.substr(endPos, format.after.length) === format.after) {
        break;
      }
      endPos += 1;
    }

    const start = line.slice(0, startPos);
    const mid = line.slice(startPos + format.before.length, endPos);
    const end = line.slice(endPos + format.after.length);
    cm.replaceRange(
      start + mid + end,
      { line: startPoint.line, ch: 0 },
      { line: startPoint.line, ch: line.length + 1 }
    );
    cm.setSelection({ line: startPoint.line, ch: start.length }, { line: startPoint.line, ch: (start + mid).length });
    cm.focus();
  },
  blockApply(cm, format) {
    const startPoint = cm.getCursor('start');
    const line = cm.getLine(startPoint.line);
    const text = `${format.before} ${line.length ? line : format.placeholder}`;
    cm.replaceRange(text, { line: startPoint.line, ch: 0 }, { line: startPoint.line, ch: line.length + 1 });
    cm.setSelection(
      { line: startPoint.line, ch: format.before.length + 1 },
      { line: startPoint.line, ch: text.length }
    );
    cm.focus();
  },
  blockRemove(cm, format) {
    const startPoint = cm.getCursor('start');
    const line = cm.getLine(startPoint.line);
    const text = line.replace(format.re, '');
    cm.replaceRange(text, { line: startPoint.line, ch: 0 }, { line: startPoint.line, ch: line.length + 1 });
    cm.setSelection({ line: startPoint.line, ch: 0 }, { line: startPoint.line, ch: text.length });
    cm.focus();
  },
  multiLineBlockApply(cm, format) {
    const startPoint = cm.getCursor('start');
    const endPoint   = cm.getCursor('to');
    const line       = cm.getRange(cm.getCursor('from'), cm.getCursor('to'));
    const content    = line.length ? line : format.placeholder;
    const text       = `${format.before}\n${content}\n${format.after}`;
    cm.replaceRange(text, startPoint, endPoint);
    cm.setSelection(
      { line: startPoint.line + 1, ch: 0 },
      { line: endPoint.line + 1, ch: content.length }
    );
    cm.focus();
  },
  multiLineBlockRemove(cm, format) {
    const startPoint = cm.getCursor('start');
    const line = cm.getLine(startPoint.line);
    const text = line.replace(format.re, '');
    cm.replaceRange(text, { line: startPoint.line, ch: 0 }, { line: startPoint.line, ch: line.length + 1 });
    cm.setSelection({ line: startPoint.line, ch: 0 }, { line: startPoint.line, ch: text.length });
    cm.focus();
  },
};

export function applyFormat(cm, key) {
  const cs = getCursorState(cm);
  const format = FORMATS[key];
  operations[format.type + (cs[key] ? 'Remove' : 'Apply')](cm, format);
}
