/* eslint-disable class-methods-use-this */
import Twig from 'twig';

class LegacySnippetInserter {
  getTranslation(snippetCode, langId) {
    let agentText;
    let defaultText;
    let wantText;
    let useText;

    for (let i = 0; i < snippetCode.length; i++) {
      if (snippetCode[i].content) {
        if (snippetCode[i].language === parseInt(langId, 10)) {
          wantText = snippetCode[i];
        }
        if (snippetCode[i].language === window.DESKPRO_PERSON_LANG_ID) {
          agentText = snippetCode[i];
        }
        if (snippetCode[i].language === window.DESKPRO_DEFAULT_LANG_ID) {
          defaultText = snippetCode[i];
        }
        useText = snippetCode[i];
      }
    }

    if (wantText) {
      useText = wantText;
    } else if (agentText) {
      useText = agentText;
    } else if (defaultText) {
      useText = defaultText;
    }

    return useText;
  }
  insertSnippet(snippet, blobs, langId, metadata, textArea, attachBlobs, recordSnippetUse) {
    const snippetId = snippet.id;
    const snippetCode = snippet.translations;

    recordSnippetUse(snippetId);

    let result;
    let useText = this.getTranslation(snippetCode, langId);

    if (useText.blobs.length) {
      attachBlobs(useText.blobs, blobs);
    }

    useText = useText.content;

    useText = this.insertSubSnippets(useText, langId, attachBlobs, blobs);

    if (metadata) {
      try {
        const tpl = Twig.twig({
          data:             useText,
          strict_variables: true
        });
        if (tpl) {
          result = tpl.render({
            entity: metadata,
            ticket: metadata,
          }, {
            strict_variables: true
          });
        }
        if (!result) {
          result = useText;
        }
      } catch (e) {
        console.log('Snippet render failed: %o', e);
        result = useText;
      }
    } else {
      result = useText;
    }

    let data = result.replace(/<\/p>\s*<p>/g, '<br/>');
    data = data.replace(/^<p>/, '');
    data = data.replace(/<\/p>$/, '');
    const div = document.createElement('div');
    div.innerHTML = data;

    const api = textArea.data('redactor');
    const el = api.$editor.find(`.editor-inserting-var.snippet-${snippetId}`);
    if (el.length) {
      const cursor = document.createElement('span');
      cursor.className = '_cursor';
      let cursorPos = [].filter.call(div.childNodes, node => node.tagName === 'P');
      if (cursorPos.length < 1) {
        cursorPos = div;
      }

      el.after(div);
      cursorPos.append(cursor);
      el.remove();

      const next = div.nextElementSibling;
      if (next && next.tagName === 'BR') {
        next.remove();
      }
      if (cursor.nextElementSibling && cursor.nextElementSibling.tagName === 'BR') {
        cursor.nextElementSibling.remove();
      }
      if (cursor.previousElementSibling && cursor.previousElementSibling.tagName === 'BR') {
        cursor.previousElementSibling.remove();
      }
      api.setSelection(cursor, 0, cursor, 0);
    } else {
      try {
        api.restoreSelection();
        api.setBuffer();
      } catch (e) {
        console.error(e);
      }
      api.insertHtml(div.innerHTML);

      const event = new CustomEvent('dpLeftDrawerClose');
      window.document.dispatchEvent(event);
    }
    api.syncCode();
  }

  insertSubSnippets(text, langId, attachBlobs, blobs) {
    const re = new RegExp(/%([-_a-z0-9]+)%/gi);
    let snippetId;
    let snippetIds;
    let snippet;
    let useText;
    let result = text;
    let shortcut = re.exec(text);
    while (shortcut) {
      if (window.DESKPRO_TICKET_SNIPPET_SHORTCODES && window.DESKPRO_TICKET_SNIPPET_SHORTCODES[shortcut[1]]) {
        snippetIds = window.DESKPRO_TICKET_SNIPPET_SHORTCODES[shortcut[1]];
        snippetId = snippetIds[0];
        snippet = window.LegacyStoreProvider.getSnippets().get(snippetId);
        if (snippet) {
          useText = this.getTranslation(snippet.get('translations').toJS(), langId);
          result = result.replace(`%${snippet.get('shortcut_code')}%`, useText.content);
          attachBlobs(useText.blobs, blobs);
        }
      }
      shortcut = re.exec(text);
    }

    return result;
  }
}
export default LegacySnippetInserter;
