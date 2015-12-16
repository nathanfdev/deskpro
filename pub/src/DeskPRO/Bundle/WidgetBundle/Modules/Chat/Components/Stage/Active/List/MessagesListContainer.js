import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { messagesSelector } from '../../../../Selectors/chat';
import { widgetHeightSelector } from '../../../../../Application/Selectors/dpWindow';
import { MessagesList } from './MessagesList';

@connect(state => ({
  messages: messagesSelector(state),
  widgetHeight: widgetHeightSelector(state)
}))
export class MessagesListContainer extends React.Component {

  static propTypes = {
    widgetHeight: PropTypes.number
  };

  render() {
    const { widgetHeight } = this.props;

    return <MessagesList {...this.props} height={widgetHeight} />;
  }
}
