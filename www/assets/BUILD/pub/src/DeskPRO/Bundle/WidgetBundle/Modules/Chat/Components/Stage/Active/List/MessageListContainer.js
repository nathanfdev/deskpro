import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import $ from 'jquery';
import { MessageList } from './MessageList';
import { MessageListSpinner } from './MessageListSpinner';
import {
  chatLoadedSelector,
  messagesSelector,
  muteSelector,
  agentSelector
} from '../../../../Selectors/chat';
import { widgetDimensionsSelector, isBubbleSelector } from '../../../../../Application/Selectors/dpWindow';
import { windowResize } from '../../../../../Application/Actions/dpWindowActions';

@connect(state => ({
  chatAgent:        agentSelector(state),
  chatLoaded:       chatLoadedSelector(state),
  messages:         messagesSelector(state),
  widgetDimensions: widgetDimensionsSelector(state),
  mute:             muteSelector(state),
  isBubble:         isBubbleSelector(state)
}))
export class MessageListContainer extends React.Component {

  static propTypes = {
    isBubble:         PropTypes.bool,
    dispatch:         PropTypes.func,
    chatLoaded:       PropTypes.bool,
    chatAgent:        PropTypes.object,
    widgetDimensions: PropTypes.object
  };

  componentDidMount() {
    this.reCalcHeight();
  }

  componentDidUpdate() {
    setTimeout(() => this.reCalcHeight(), 0);
  }

  reCalcHeight() {
    const { widgetDimensions, chatAgent, isBubble, dispatch } = this.props;
    const widgetHeight = widgetDimensions.get('height');
    const $document = $(window.widgetFrame.document);
    const $chatHeader = $document.find('.dpdesignportal-chat-header, .dpdesignportal-collect-user-info-header');
    const $powered = $document.find('.dpdesignportal-powered-by-deskpro');

    const calc = (ignoreHeaderAndFooter) => {
      let height = widgetHeight;

      height -= $document.find('.dpdesignportal-header').outerHeight(true);
      height -= $document.find('.dpdesignportal-chat-header-controls').outerHeight(true);
      height -= $document.find('.dpdesignportal-chat-footer').outerHeight(true);

      if (!ignoreHeaderAndFooter) {
        // increase height manually, because it isn't changing instantly after hide()/show()
        height -= $chatHeader.outerHeight(true);
        height -= $powered.outerHeight(true);
      }

      if (isBubble) {
        height -= 10;
      }

      return height;
    };

    let height = calc();
    if (chatAgent && height < 100) {
      $chatHeader.hide();
      $powered.hide();

      // re-calc one more time
      height = calc(true);
    } else {
      $chatHeader.show();
      $powered.show();

      // re-calc one more time
      height = calc();
    }

    if (this.node) {
      $(this.node.node).css('height', height);

      // re-calc one more time to set elements in DOM properly
      if (this.prevHeight !== height) {
        this.prevHeight = height;
        setTimeout(() => dispatch(windowResize()), 0);
      }

      if (this.node.scrollBottom) {
        this.node.scrollBottom();
      }
    }
  }

  render() {
    const { chatLoaded } = this.props;

    return chatLoaded
      ? <MessageList ref={(c) => { this.node = c; }} {...this.props} />
      : <MessageListSpinner ref={(c) => { this.node = c; }} />;
  }
}
export default MessageListContainer;
