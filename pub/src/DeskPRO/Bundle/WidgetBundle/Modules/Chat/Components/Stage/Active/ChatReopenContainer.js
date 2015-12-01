import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { dateEndedSelector } from '../../../Selectors/chat';

@connect(state => ({
  dateEnded: dateEndedSelector(state)
}))
export class ChatReopenContainer extends React.Component {

  static propTypes = {
    dateEnded: PropTypes.number
  };

  render() {
    return null;
  }
}
