import React from 'react';
import ReactDOM from 'react-dom';
import { IntlProvider } from 'react-intl';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { getUrlParameter } from 'DeskPRO/Component/Util/Url';
import { HcOmniSearch } from '../React/OmniSearch/HcOmniSearch';
import { portalPhrases } from '../PortalPhrases';

export class HcOmniSearchWidget extends PageWidget {

  renderWidget() {
    this.$rElement = $('<div class="dp-react-widget"></div>').appendTo(this.$element);

    this.locale = window.DESKPRO_LOCALE.replace(/_/, '-');

    ReactDOM.render((
      <IntlProvider
        locale={this.locale}
        messages={portalPhrases.getPhrases()}
      >
        <HcOmniSearch
          $input={this.$element.find('input[type=search]')}
          $inputSearchLogId={this.$element.find('input[type=hidden]')}
          $close={this.$element.find('.search-clear')}
          $button={this.$element.find('input[type=submit]')}
        />
      </IntlProvider>
      ), this.$rElement.get(0));

    const $input = this.$element.find('input.omnisearch');
    const $button = this.$element.find('button.search-btn');
    const $x = this.$element.find('.search-clear');

    $button.on('click', () => {
      if (!$input.val()) {
        $input.focus();
        return false;
      }
      const $form = $button.closest('form')[0];
      const action = $form.action;
      $form.action = `${action}?q=${$input.val()}`;

      return true;
    });

    $('.dpx-omnisearch-link').each((i, link) => {
      $(link).click((event) => {
        event.preventDefault();
        const term = $(link).text();

        $input.val('').change(); // clear exiting results, if any
        $input.val(term);
        $input.change().focus();
      });
    });

    setInterval(() => {
      if ($input.val().length > 0) {
        $x.show();
      } else {
        $x.hide();
      }
    }, 250);

    const query = getUrlParameter('q');
    if (query) {
      $input.val(query);
    }
  }
}
