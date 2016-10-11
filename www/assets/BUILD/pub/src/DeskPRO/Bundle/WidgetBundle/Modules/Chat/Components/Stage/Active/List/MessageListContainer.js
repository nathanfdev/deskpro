import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import $ from 'jquery';
import Immutable from 'immutable';
import { MessageList } from './MessageList';
import { MessageListSpinner } from './MessageListSpinner';
import {
  chatLoadedSelector,
  messagesSelector,
  muteSelector
} from '../../../../Selectors/chat';
import { widgetDimensionsSelector, isBubbleSelector } from '../../../../../Application/Selectors/dpWindow';

@connect(state => ({
  chatLoaded:       chatLoadedSelector(state),
  messages:         messagesSelector(state),
  widgetDimensions: widgetDimensionsSelector(state),
  mute:             muteSelector(state),
  isBubble:         isBubbleSelector(state)
}))
export class MessageListContainer extends React.Component {

  static propTypes = {
    chatLoaded:       PropTypes.bool,
    messages:         PropTypes.object,
    widgetDimensions: PropTypes.object,
    mute:             PropTypes.bool,
    isBubble:         PropTypes.bool
  };

  componentDidMount() {
    this.reCalcHeight();
  }

  shouldComponentUpdate(props) {
    const { chatLoaded, messages, widgetDimensions, mute, isBubble } = this.props;
    const dimensionsChanged = !Immutable.is(widgetDimensions, props.widgetDimensions);

    return props.chatLoaded !== chatLoaded
      || !Immutable.is(messages, props.messages)
      || dimensionsChanged
      || props.mute !== mute
      || props.isBubble !== isBubble;
  }

  componentDidUpdate() {
    this.reCalcHeight();
  }

  reCalcHeight() {
    const widgetHeight = this.props.widgetDimensions.get('height');
    const $document = $(window.widgetFrame.document);

    let height = widgetHeight;

    height -= $document.find('.dpdesignportal-header').outerHeight();
    height -= $document.find('.dpdesignportal-chat-header-wrapper').outerHeight();
    height -= $document.find('.dpdesignportal-chat-footer').outerHeight();
    height -= $document.find('.dpdesignportal-powered-by-deskpro').outerHeight();
    // lost margin of .dpdesignportal-chat-header-controls
    height -= 20;

    $(this.list).parent().children()
      .each((i, child) => {
        if (child !== this.list) {
          height -= $(child).outerHeight();
        }
      });

    if (height < 100) {
      height = 100;
    }

    $(this.list).css('height', height);

    if (this.list) {
      this.list.scrollBottom();
    }
  }

  render() {
    return this.props.chatLoaded
      ? <MessageList ref={(c) => { this.list = c; }} {...this.props} />
      : <MessageListSpinner />;
  }
}
export default MessageListContainer;
