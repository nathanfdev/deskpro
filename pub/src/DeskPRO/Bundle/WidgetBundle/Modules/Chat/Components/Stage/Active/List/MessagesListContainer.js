import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import ReactDOM from 'react-dom';
import { messagesSelector, lastMessageIdSelector, muteSelector } from '../../../../Selectors/chat';
import { widgetDimensionsSelector, widgetHeightSelector } from '../../../../../Application/Selectors/dpWindow';
import { MessagesList } from './MessagesList';
import $ from 'jquery';

@connect(state => ({
  messages: messagesSelector(state),
  lastMessageId: lastMessageIdSelector(state),
  widgetDimensions: widgetDimensionsSelector(state),
  widgetHeight: widgetHeightSelector(state),
  mute: muteSelector(state)
}))
export class MessagesListContainer extends React.Component {

  static propTypes = {
    widgetHeight: PropTypes.number
  };

  componentDidMount() {
    this.reCalcHeight();
  }

  componentDidUpdate() {
    this.reCalcHeight();
  }

  reCalcHeight() {
    const { widgetHeight } = this.props;
    const node = ReactDOM.findDOMNode(this);

    let height = widgetHeight - 230; // header height
    $(node).parent().children().each((i, child) => {
      if (child !== node) {
        height = height - $(child).outerHeight();
      }
    });

    $(node).css('height', height);
    this.refs.list.refresh();
  }

  render() {
    return <MessagesList ref="list" {...this.props} />;
  }
}
