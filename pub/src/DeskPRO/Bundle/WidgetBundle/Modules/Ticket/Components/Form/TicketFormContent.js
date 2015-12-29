import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import history from '../../../../Services/history';

export class TicketFormContent extends React.Component {

  static propTypes = {
    content: PropTypes.string
  };

  componentDidMount() {
    const $button = this.getButton();
    $button.on('click', this.onSubmit);
  }

  componentWillUnmount() {
    const $button = this.getButton();
    $button.off('click', this.onSubmit);
  }

  onSubmit = event => {
    event.preventDefault();
    history.replace('ticket/form_submitted');
  };

  getButton() {
    return $('button[type=submit]', ReactDOM.findDOMNode(this));
  }

  render() {
    return <div dangerouslySetInnerHTML={{__html: this.props.content}} />;
  }
}
