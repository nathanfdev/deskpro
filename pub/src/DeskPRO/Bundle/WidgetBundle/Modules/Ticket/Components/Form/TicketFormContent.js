import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import TicketForm from '../../../../../PortalBundle/PageWidget/TicketForm';
import serializeJSON from 'jquery.serializejson';

export class TicketFormContent extends React.Component {

  static propTypes = {
    onSubmit: PropTypes.func,
    content: PropTypes.string
  };

  componentDidMount() {
    this.getForm().on('submit', this.onSubmit);
    this.formWidget = new TicketForm($('#new_ticket_page', this.getCurrentNode()));
    this.formWidget.renderWhenReady();
  }

  componentWillUnmount() {
    this.getForm().off('submit', this.onSubmit);
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

  render() {
    return <div dangerouslySetInnerHTML={{__html: this.props.content}} />;
  }
}
