import $ from 'jquery';
import PageWidget from 'DeskPRO/Component/PageWidget/PageWidget';
import WordHighlighter from 'DeskPRO/Bundle/PortalBundle/PageWidget/Common/WordHighlighter';

export default class ArticleHighlighter extends PageWidget {

  renderWidget() {
    const $article = this.$element;
    const highlighter = new WordHighlighter;
    const glossary = window.DP_ARTICLE_GLOSSARY;
    const hightlightWords = highlighter.highlight($article[0], glossary.words, false, true);

    $(hightlightWords).each((i, node) => {
      const $el = $(node);
      const word = $el.data('word');
      const def = glossary.defs[word];
      const tooltip = $(`<div class="glossary-tooltip" style="display: none;"><h1>${word}</h1><hr><p>${def}</p></div>`);

      $el.append(tooltip);
      $el.hover(() => tooltip.show(), () => tooltip.hide());
    });
  }
}
