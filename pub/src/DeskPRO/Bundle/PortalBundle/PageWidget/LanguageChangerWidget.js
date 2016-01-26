import React from 'react';
import ReactDOM from 'react-dom';
import PageWidget from 'DeskPRO/Component/PageWidget/PageWidget';
import LanguageChanger from 'DeskPRO/Bundle/PortalBundle/React/LanguageChanger';
import PortalWindow from 'DeskPRO/Bundle/PortalBundle/PortalWindow';
import { portalUrlGenerator } from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';
import $ from 'jquery';

export default class LanguageChangerWidget extends PageWidget {

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
    ReactDOM.render(React.createElement(LanguageChanger, {active_lang_code: PortalWindow.lang, enabled_langs: PortalWindow.enabled_langs, clickLanguage: this.clickLanguage.bind(this) }), this.$rElement.get(0));
  }
}
