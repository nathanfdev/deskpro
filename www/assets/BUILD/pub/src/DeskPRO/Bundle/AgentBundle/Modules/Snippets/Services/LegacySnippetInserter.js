/* eslint-disable class-methods-use-this */
import Twig from 'twig';
import $ from 'jquery';

class LegacySnippetInserter {
  getTranslation(translations, langId, isSplit, type) {
    let agentText;
    let defaultText;
    let wantText;
    let translation;

    for (let i = 0; i < translations.length; i++) {
      if (translations[i].content) {
        if (translations[i].language === parseInt(langId, 10) && (!isSplit || translations[i].type === type)) {
          wantText = translations[i];
        }
        if (translations[i].language === window.DESKPRO_PERSON_LANG_ID && (!isSplit || translations[i].type === type)) {
          agentText = translations[i];
        }
        if (translations[i].language === window.DESKPRO_DEFAULT_LANG_ID && (!isSplit || translations[i].type === type)) {
          defaultText = translations[i];
        }
        translation = translations[i];
      }
    }

    if (wantText) {
      translation = wantText;
    } else if (agentText) {
      translation = agentText;
    } else if (defaultText) {
      translation = defaultText;
    }

    return translation;
  }
  insertSnippet(snippet, blobs, langId, metadata, type, textArea, attachBlobs, recordSnippetUse) {
    const snippetId   = snippet.id;
    const isSplit     = snippet.is_split;
    const translations = snippet.translations;

    let result;
    let useText = this.getTranslation(translations, langId, isSplit, type);

    recordSnippetUse(useText.id);

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

    // redactor sometimes has empty lines, we don't need to convert them to new lines
    let data = result.replace(/<p><\/p>/g, '');
    data = data.replace(/<div><\/div>/g, '');
    // in the end, we want convert <p><br></p> to just <br>, so strip extra <br>
    data = data.replace(/<p><br><\/p>/g, '<p></p>');

    data = data.replace(/<\/p>\s*<p>/g, '<br/>');
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
      $(cursorPos).append(cursor);
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
