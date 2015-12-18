import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Trigger } from './Trigger/Trigger';
import { Widget } from './Widget/Widget';
import { ChatTriggers } from './ChatTriggers';
import { widgetOpenedSelector } from '../Selectors/dpWindow';
import { windowResize } from '../Actions/dpWindowActions';
import $ from 'jquery';
import debounce from 'lodash/function/debounce';

@connect(state => ({
  widgetOpened: widgetOpenedSelector(state)
}))
export class AppContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    widgetOpened: PropTypes.bool
  };

  constructor(props) {
    super(props);

    this.onWindowResize();
    this.onResize = debounce(() => {
      this.onWindowResize();
    }, 350);
  }

  componentDidMount() {
    window.widgetFrame = parent.window.widget_iframe;
    $(window.parent).on('resize', this.onResize);
  }

  componentWillUnmount() {
    $(window.parent).off('resize', this.onResize);
  }

  onWindowResize() {
    this.props.dispatch(windowResize(
      $(window.widgetFrame).width(),
      $(window.widgetFrame).height()
    ));
  }

  render() {
    const { widgetOpened } = this.props;

    return (
      <div>
        <Trigger />
        <Widget />
        <ChatTriggers isVisible={widgetOpened} />
      </div>
    );
  }
}
