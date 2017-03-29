// Process block-level custom containers
//
/* eslint no-continue: "warn"*/

function tabsPlugin(md, options) {
  function validateDefault(params) {
    return params.trim().split(' ', 2)[0] === 'tab:';
  }

  function renderDefault(tokens, idx) {
    let buffer = '';
    if (!this.tabGroup) {
      this.tabGroup = 1;
    }
    if (!this.tabs) {
      this.tabs = [];
      for (let i = idx; i < tokens.length; i += 1) {
        const token = tokens[i];
        if (token.type === 'tab_open') {
          if (typeof this.active === 'undefined') {
            this.active = i;
          }
          let active = '';
          if (this.active === i) {
            active = 'active';
          }
          this.tabs.push(`<a class="item ${active}" data-tab="tab_${this.tabGroup}_${i}">${token.info.replace(/^\s*tab:/, '')}</a>`);
        }
        if (token.type === 'tab_close' && (i === tokens.length - 1 || tokens[i + 1].type !== 'tab_open')) {
          this.lastToken = i;
          break;
        }
      }
      if (this.tabs.length) {
        buffer = `<div class="tabular menu">${this.tabs.join('\n')}</div>`;
      }
    }
    if (this.lastToken && this.lastToken === idx) {
      this.tabGroup = this.tabGroup + 1;
      delete this.tabs;
      delete this.active;
    }
    let active = '';
    if (this.active === idx) {
      active = 'active';
    }
    return tokens[idx].nesting === 1
      ? `${buffer}<div class="tab ${active}" data-tab="tab_${this.tabGroup}_${idx}">\n`
      : '</div>\n';
  }

  const localOptions = options || {};

  const minMarkers = 3;
  const markerStr  = localOptions.marker || ':';
  const markerChar = markerStr.charCodeAt(0);
  const markerLen  = markerStr.length;
  const validate   = localOptions.validate || validateDefault;
  const render     = localOptions.render || renderDefault;

  function container(state, startLine, endLine, silent) {
    let pos;
    let nextLine;
    let token;
    let autoClosed = false;
    let start = state.bMarks[startLine] + state.tShift[startLine];
    let max = state.eMarks[startLine];

    // Check out the first character quickly,
    // this should filter out most of non-containers
    //
    if (markerChar !== state.src.charCodeAt(start)) { return false; }

    // Check out the rest of the marker string
    //
    for (pos = start + 1; pos <= max; pos += 1) {
      if (markerStr[(pos - start) % markerLen] !== state.src[pos]) {
        break;
      }
    }

    const markerCount = Math.floor((pos - start) / markerLen);
    if (markerCount < minMarkers) { return false; }
    pos -= (pos - start) % markerLen;

    const markup = state.src.slice(start, pos);
    const params = state.src.slice(pos, max);
    if (!validate(params)) { return false; }

    // Since start is found, we can report success here in validation mode
    //
    if (silent) { return true; }

    // Search for the end of the block
    //
    nextLine = startLine;

    for (;;) {
      nextLine += 1;
      if (nextLine >= endLine) {
        // unclosed block should be autoclosed by end of document.
        // also block seems to be autoclosed by end of parent
        break;
      }

      start = state.bMarks[nextLine] + state.tShift[nextLine];
      max = state.eMarks[nextLine];

      if (start < max && state.sCount[nextLine] < state.blkIndent) {
        // non-empty line with negative indent should stop the list:
        // - ```
        //  test
        break;
      }

      if (markerChar !== state.src.charCodeAt(start)) { continue; }

      if (state.sCount[nextLine] - state.blkIndent >= 4) {
        // closing fence should be indented less than 4 spaces
        continue;
      }

      for (pos = start + 1; pos <= max; pos += 1) {
        if (markerStr[(pos - start) % markerLen] !== state.src[pos]) {
          break;
        }
      }

      // closing code fence must be at least as long as the opening one
      if (Math.floor((pos - start) / markerLen) < markerCount) { continue; }

      // make sure tail has spaces only
      pos -= (pos - start) % markerLen;
      pos = state.skipSpaces(pos);

      if (pos < max) { continue; }

      // found!
      autoClosed = true;
      break;
    }

    const oldParent = state.parentType;
    const oldLineMax = state.lineMax;
    state.parentType = 'container';

    // this will prevent lazy continuations from ever going past our end marker
    state.lineMax = nextLine;

    token        = state.push('tab_open', 'div', 1);
    token.markup = markup;
    token.block  = true;
    token.info   = params;
    token.map    = [startLine, nextLine];

    state.md.block.tokenize(state, startLine + 1, nextLine);

    token        = state.push('tab_close', 'div', -1);
    token.markup = state.src.slice(start, pos);
    token.block  = true;

    state.parentType = oldParent;
    state.lineMax = oldLineMax;
    state.line = nextLine + (autoClosed ? 1 : 0);

    return true;
  }

  md.block.ruler.before('fence', 'tab', container, {
    alt: ['paragraph', 'reference', 'blockquote', 'list']
  });
  md.renderer.rules.tab_open = render;
  md.renderer.rules.tab_close = render;
}

export default tabsPlugin;
