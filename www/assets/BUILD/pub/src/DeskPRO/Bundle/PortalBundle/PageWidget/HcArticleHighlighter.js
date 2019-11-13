import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { WordHighlighter } from 'DeskPRO/Bundle/PortalBundle/PageWidget/Common/WordHighlighter';

export class HcArticleHighlighter extends PageWidget {

  renderWidget() {
    const $article = this.$element;
    const highlighter = new WordHighlighter();
    const glossary = window.DP_ARTICLE_GLOSSARY;
    const hightlightWords = highlighter.highlight($article[0], glossary.words, false, true);

    $(hightlightWords).each((i, node) => {
      const $el = $(node);
      const word = $el.data('word');
      const def = glossary.defs[word];
      const tooltip = $(`<span class="dp-po-tooltip" data-toggle="tooltip" data-html="true"
      title="${def}">${word}</span>`);

      $el.replaceWith(tooltip);

      const toolOptions = {
        template: '<div class="tooltip dp-po-tip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
      };
      tooltip.tooltip(toolOptions);
    });
  }
}
