import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Trigger } from './Trigger/Trigger';
import { Widget } from './Widget/Widget';
import { windowResize, openWidget } from '../Actions/dpWindowActions';
import { setChatId } from '../../Chat/Actions/chatActions';
import $ from 'jquery';
import debounce from 'lodash/function/debounce';
import history from '../../../Services/history';

@connect()
export class AppContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.onResize = debounce(() => {
      this.onWindowResize();
    }, 350);
  }

  componentDidMount() {
    window.widgetFrame = parent.window.widget_iframe;
    $(window.parent).on('resize', this.onResize);

    this.onWindowResize();
    this.checkStoredChatId();
  }

  componentWillUnmount() {
    $(window.parent).off('resize', this.onResize);
  }

  onWindowResize() {
    this.props.dispatch(windowResize());
  }

  checkStoredChatId() {
    const { dispatch } = this.props;
    const storedChatId = Number(localStorage.getItem('dpWidget.chat.chatId'));

    if (storedChatId) {
      history.replace('/chat/active');

      dispatch(setChatId(storedChatId));
      dispatch(openWidget());
    }
  }

  render() {
    return (
      <div>
        <Trigger />
        <Widget />
      </div>
    );
  }
}
