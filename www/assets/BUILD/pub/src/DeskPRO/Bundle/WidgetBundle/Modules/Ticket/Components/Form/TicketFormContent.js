import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { TicketFormWidget } from './TicketFormWidget';
import $ from 'jquery';
import 'jquery.serializejson';

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

  onSubmit = event => {
    event.preventDefault();

    const data = $(event.target).serializeJSON();
    this.props.onSubmit(data);
  };

  getCurrentNode() {
    return ReactDOM.findDOMNode(this);
  }

  getForm() {
    return $('.form-ticket', this.getCurrentNode());
  }

  addListeners() {
    this.getForm().on('submit', this.onSubmit);

    this.formWidget = new TicketFormWidget($(this.getCurrentNode()), null, {
      context:       [parent.document, window.widgetFrame.document],
      contentWindow: window.widgetFrame,
      ownerDocument: window.widgetFrame.document
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
    return <div dangerouslySetInnerHTML={{ __html: this.props.content }}></div>;
  }
}
