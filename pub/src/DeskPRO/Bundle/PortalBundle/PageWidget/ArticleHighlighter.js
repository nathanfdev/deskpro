import _ from "lodash";
import $ from "jquery";
import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import WordHighlighter from "DeskPRO/Bundle/PortalBundle/PageWidget/Common/WordHighlighter";

//######################################################################################################################
//# Page widget
//######################################################################################################################

export default class ArticleHighlighter extends PageWidget {
  renderWidget() {
    const $article = this.$element;
    const highlighter = new WordHighlighter;
    const glossary = window.DP_ARTICLE_GLOSSARY;
    const hightlight_words = highlighter.highlight(
        $article[0],
        glossary.words,
        false,
        true
    );

    $(hightlight_words).each(function() {
      const $el = $(this);
      const word = $el.data('word');
      const def = glossary.defs[word];
      const tooltip = $(`<div class="glossary-tooltip" style="display: none;"><h1>${word}</h1><hr><p>${def}</p></div>`);
      $el.append(tooltip);

      $el.hover(function() {
        tooltip.show();
      }, function() {
        tooltip.hide();
      })
    });
  }
}
