import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { LanguageChanger } from '../React/LanguageChanger';
import { portalWindow } from '../PortalWindow';
import { portalUrlGenerator } from '../Http/PortalUrlGenerator';

export class LanguageChangerWidget extends PageWidget {

  clickLanguage(langCode) {
    const action = portalUrlGenerator.path('/change-language');
    const $form = $(`<form><input type="hidden" name="lang_code" value="${langCode}" /></form>`);

    $form.attr('action', action);
    $form.attr('method', 'POST');
    $('body').append($form);
    $form.submit();
  }

  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget"></div>').insertAfter(this.$element);

    const component = React.createElement(LanguageChanger, {
      activeLangCode: portalWindow.lang,
      enabledLangs:   portalWindow.enabled_langs,
      clickLanguage:  this.clickLanguage.bind(this)
    });

    ReactDOM.render(component, this.$rElement.get(0));
  }
}
