import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import ReactDOM from 'react-dom';
import { chatLoadedSelector, messagesSelector, lastMessageIdSelector, muteSelector, agentTypingDateSelector }
from '../../../../Selectors/chat';
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
  people:           peopleSelector(state),
  agentTypingDate:  agentTypingDateSelector(state)
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
    const { widgetHeight } = this.props;
    const node = ReactDOM.findDOMNode(this);
    const $document = $(window.widgetFrame.document);

    let height = widgetHeight;

    height -= $document.find('.dpdesignportal-header').outerHeight();
    height -= $document.find('.dpdesignportal-chat-header-wrapper').outerHeight();
    height -= $document.find('.dpdesignportal-chat-footer').outerHeight();
    height -= $document.find('.dpdesignportal-powered-by-deskpro').outerHeight();
    // lost margin of .dpdesignportal-chat-header-controls
    height -= 20;

    $(node).parent().children()
      .each((i, child) => {
        if (child !== node) {
          height -= $(child).outerHeight();
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
