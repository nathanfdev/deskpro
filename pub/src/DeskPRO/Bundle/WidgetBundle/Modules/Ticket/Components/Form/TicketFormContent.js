import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import history from '../../../../Services/history';
import TicketForm from '../../../../../PortalBundle/PageWidget/TicketForm';

export class TicketFormContent extends React.Component {

  static propTypes = {
    content: PropTypes.string
  };

  componentDidMount() {
    const $button = this.getButton();
    $button.on('click', this.onSubmit);

    this.formWidget = new TicketForm($('#new_ticket_page', this.getCurrentNode()));
    this.formWidget.renderWhenReady();
  }

  componentWillUnmount() {
    const $button = this.getButton();
    $button.off('click', this.onSubmit);
  }

  onSubmit = event => {
    event.preventDefault();
    history.replace('ticket/form_submitted');
  };

  getCurrentNode() {
    return ReactDOM.findDOMNode(this);
  }

  getButton() {
    return $('button[type=submit]', this.getCurrentNode());
  }

  render() {
    return <div dangerouslySetInnerHTML={{__html: this.props.content}} />;
  }
}
