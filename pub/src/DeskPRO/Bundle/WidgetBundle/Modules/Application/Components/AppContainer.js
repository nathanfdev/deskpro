import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Trigger } from './Trigger/Trigger';
import { Widget } from './Widget/Widget';
import { windowResize, openWidget } from '../Actions/dpWindowActions';
import { setChatId } from '../../Chat/Actions/chatActions';
import { widgetLoadedSelector } from '../Selectors/bootstrap';
import { onlineAgentsCountSelector } from '../Selectors/agent';
import $ from 'jquery';
import debounce from 'lodash/function/debounce';
import history from '../../../Services/history';

@connect(state => ({
  widgetLoaded: widgetLoadedSelector(state),
  onlineAgentsCount: onlineAgentsCountSelector(state)
}))
export class AppContainer extends React.Component {

  static propTypes = {
    widgetLoaded: PropTypes.bool,
    onlineAgentsCount: PropTypes.number,
    dispatch: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.onResize = debounce(() => this.onWindowResize(), 350);
  }

  componentDidMount() {
    window.widgetFrame = parent.window.widget_iframe;
    $(window.parent).on('resize', this.onResize);

    this.onWindowResize();
    this.checkStoredChatId();
  }

  componentWillUpdate() {
    this.checkStoredChatId();
  }

  componentWillUnmount() {
    $(window.parent).off('resize', this.onResize);
  }

  onWindowResize() {
    this.props.dispatch(windowResize());
  }

  checkStoredChatId() {
    const { dispatch, widgetLoaded, onlineAgentsCount } = this.props;
    const storedChatId = Number(localStorage.getItem('dpWidget.chat.chatId'));

    // If we have stored chat and online agents then force load previous chat
    if (storedChatId && widgetLoaded && onlineAgentsCount > 0) {
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
