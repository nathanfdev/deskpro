import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import ReactDOM from 'react-dom';
import { messagesSelector } from '../../../../Selectors/chat';
import { widgetHeightSelector } from '../../../../../Application/Selectors/dpWindow';
import { MessagesList } from './MessagesList';
import $ from 'jquery';

@connect(state => ({
  messages: messagesSelector(state),
  widgetHeight: widgetHeightSelector(state)
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

    let height = widgetHeight - 280; // header height
    $(node).parent().children().each((i, child) => {
      if (child !== node) {
        height = height - $(child).height();
      }
    });

    $(node).css('height', height);
  }

  render() {
    return <MessagesList {...this.props} />;
  }
}
