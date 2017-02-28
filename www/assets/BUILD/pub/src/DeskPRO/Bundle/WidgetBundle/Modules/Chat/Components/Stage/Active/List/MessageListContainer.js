import React, { PropTypes } from 'react';
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
    const { widgetDimensions } = this.props;
    const widgetHeight = widgetDimensions.get('height');
    const $document = $(window.widgetFrame.document);
    const $chatHeader = $document.find('.dpdesignportal-chat-header-wrapper');
    const $powered = $document.find('.dpdesignportal-powered-by-deskpro');

    const calc = (ignoreHeaderAndFooter) => {
      let height = widgetHeight;
      if (!ignoreHeaderAndFooter) {
        // increase height manually, because it isn't changing instantly after hide()/show()
        height -= $chatHeader.outerHeight(true);
        height -= $powered.outerHeight(true);
      }
      height -= $document.find('.dpdesignportal-chat-header-controls').outerHeight(true);
      height -= $document.find('.dpdesignportal-chat-footer').outerHeight(true);
      // todo .dpdesignportal-chat-footer height returns 37 at this stage instead of 107
      height -= ignoreHeaderAndFooter ? 47 : 37;
      return height;
    };

    let height = calc();

    if (height < 100) {
      height = calc(true);
      $chatHeader.hide();
      $powered.hide();
    } else {
      $chatHeader.show();
      $powered.show();
    }

    if (this.node) {
      $(this.node.node).css('height', height);
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
