import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Trigger } from './Trigger/Trigger';
import { Widget } from './Widget/Widget';
import { windowResize } from '../Actions/dpWindowActions';
import $ from 'jquery';
import debounce from 'lodash/function/debounce';

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

    this.onWindowResize();
    $(window.parent).on('resize', this.onResize);
  }

  componentWillUnmount() {
    $(window.parent).off('resize', this.onResize);
  }

  onWindowResize() {
    this.props.dispatch(windowResize());
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
