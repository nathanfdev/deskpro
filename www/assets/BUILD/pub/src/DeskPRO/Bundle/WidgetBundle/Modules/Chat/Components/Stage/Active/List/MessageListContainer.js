import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { connect } from 'react-redux';
import $ from 'jquery';
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
    widgetDimensions: PropTypes.object
  };

  componentDidMount() {
    this.reCalcHeight();
  }

  componentDidUpdate() {
    setTimeout(() => this.reCalcHeight(), 0);
  }

  reCalcHeight() {
    const node = ReactDOM.findDOMNode(this);
    const widgetHeight = this.props.widgetDimensions.get('height');
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
