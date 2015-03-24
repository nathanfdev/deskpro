import PageWidget from "DeskPRO/Component/PageWidget/PageWidget";
import React from "react";
import $ from "jquery";
import _ from "lodash";

export default class DpLevelSelect extends PageWidget {
  renderWidget() {
    this.element.hide();
    this.rElement = $('<div class="dp-react-widget"></div>').insertAfter(this.element);

    let props = {
      options: [],
      initialValue: null
    };

    this.element.find('option').each((_, el) => {
      el = $(el);
      if (!el.data('id')) {
        el.data('id', _.uniqueId('opt_'));
      }
      props.options.push({
        id:     el.data('id'),
        parent: el.data('parent'),
        value:  el.val(),
        title:  el.data('name') || el.text()
      });

      if (el.is(':selected')) {
        props.initialValue = el.data('id');
      }
    });

    if (props.initialValue === null && props.options.length) {
      props.initialValue = props.options[0];
    }

    let levelSelectModule = this.container.get("ReactReg").get("DpLevelSelect");
    let levelSelect = levelSelectModule.components.DpLevelSelect;
    React.render(React.createElement(levelSelect, props), this.rElement.get(0));
  }
}