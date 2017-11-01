import PropTypes from 'prop-types';
import React from 'react';
import $ from 'jquery';
import 'jquery-serializejson';
import { TicketFormWidget } from './TicketFormWidget';

export class TicketFormContent extends React.Component {

  static propTypes = {
    saving:   PropTypes.bool,
    onSubmit: PropTypes.func,
    content:  PropTypes.string
  };

  componentDidMount() {
    this.addListeners();
  }

  componentWillReceiveProps() {
    this.removeListeners();
  }

  componentDidUpdate() {
    this.addListeners();
    this.toggleSavingSpinner();
  }

  componentWillUnmount() {
    this.removeListeners();
  }

  onSubmit = (event) => {
    event.preventDefault();

    const data = $(event.target).serializeJSON();
    this.props.onSubmit(data);
  };

  getCurrentNode() {
    return this.node;
  }

  getForm() {
    return $('.form-ticket', this.getCurrentNode());
  }

  addListeners() {
    this.getForm().on('submit', this.onSubmit);

    this.formWidget = new TicketFormWidget($(this.getCurrentNode()), null, {
      context:         [parent.document, window.widgetFrame.document],
      contentWindow:   window.widgetFrame,
      ownerDocument:   window.widgetFrame.document,
      isWidget:        true,
      getDropZoneNode: () => this.getCurrentNode()
    });

    this.formWidget.renderWhenReady();
  }

  toggleSavingSpinner() {
    const { saving } = this.props;
    const $buttonContainer = $('button[type=submit]', this.getCurrentNode()).parent();

    $buttonContainer.toggleClass('saving-form', saving);
  }

  removeListeners() {
    this.getForm().off('submit', this.onSubmit);
  }

  render() {
    return <div ref={(node) => { this.node = node; }} dangerouslySetInnerHTML={{ __html: this.props.content }} />;
  }
}
