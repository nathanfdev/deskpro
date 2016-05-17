import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import ReactDOM from 'react-dom';
import { chatLoadedSelector, messagesSelector, lastMessageIdSelector, muteSelector } from '../../../../Selectors/chat';
import { widgetDimensionsSelector, widgetHeightSelector, isBubbleSelector } from '../../../../../Application/Selectors/dpWindow';
import { peopleSelector } from '../../../../../Application/Selectors/peopleSelectors';
import { MessageList } from './MessageList';
import { MessageListSpinner } from './MessageListSpinner';
import $ from 'jquery';

@connect(state => ({
  chatLoaded:       chatLoadedSelector(state),
  messages:         messagesSelector(state),
  lastMessageId:    lastMessageIdSelector(state),
  widgetDimensions: widgetDimensionsSelector(state),
  widgetHeight:     widgetHeightSelector(state),
  mute:             muteSelector(state),
  isBubble:         isBubbleSelector(state),
  people:           peopleSelector(state)
}))
export class MessageListContainer extends React.Component {

  static propTypes = {
    chatLoaded:   PropTypes.bool,
    isBubble:     PropTypes.bool,
    widgetHeight: PropTypes.number
  };

  componentDidMount() {
    this.reCalcHeight();
  }

  componentDidUpdate() {
    this.reCalcHeight();
  }

  reCalcHeight() {
    const { widgetHeight, isBubble } = this.props;
    const node = ReactDOM.findDOMNode(this);

    let height = widgetHeight - (isBubble ? 242 : 230); // header height
    $(node).parent().children().each((i, child) => {
      if (child !== node) {
        height = height - $(child).outerHeight();
      }
    });
    if (height < 100) {
      height = 100;
    }

    $(node).css('height', height);
    if (this.refs.list) {
      this.refs.list.refresh();
    }
  }

  render() {
    return this.props.chatLoaded ? <MessageList ref="list" {...this.props} /> : <MessageListSpinner />;
  }
}
