import React from 'react';
import ReactDOM from 'react-dom';
import jQuery from 'jquery';
import datepicker from 'jquery-ui/datepicker';

export class Calendar extends React.Component {

  componentDidMount() {
    jQuery(ReactDOM.findDOMNode(this)).datepicker({
      showButtonPanel: true
    });
  }

  render() {
    return (
      <div />
    );
  }
}
