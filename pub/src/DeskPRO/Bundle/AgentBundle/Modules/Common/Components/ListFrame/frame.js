import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import ReactDOM from 'react-dom';
import jQuery from 'jquery';
import { debounce } from 'lodash';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class ListFrameContainer extends React.Component {

  static propTypes = {
    dpWindow: PropTypes.object.required,
    children: PropTypes.node,
    className: PropTypes.string
  };

  render() {
    const { dpWindow, className, children } = this.props;

    return (
      <ListFrame className={className} dpWindow={dpWindow}>
        {children}
      </ListFrame>
    );
  }
}

export class ListFrame extends React.Component {

  static propTypes:{
    dpWindow: PropTypes.object.required,
    children: PropTypes.node,
    className: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.delayedFixWidth = debounce(this.fixWidth, 100);
  }

  componentDidMount() {
    jQuery(document).ready(this.fixWidth);
    jQuery(document).on('dpLayoutResize', this.fixWidth);
    jQuery(window).on('resize', this.delayedFixWidth);
  }

  componentWillUnmount() {
    jQuery(document).off('dpLayoutResize', this.fixWidth);
    jQuery(window).off('resize', this.delayedFixWidth);
  }

  fixWidth = () => {
    const totalWidth = jQuery(document).width() - jQuery('section.dp-nav-frame:first').width();
    const navWidth = Math.ceil(totalWidth * 0.4);
    jQuery(ReactDOM.findDOMNode(this)).width(navWidth);
  };

  render() {
    const { dpWindow, children } = this.props;
    const className = this.props.className || 'dp-list-frame';
    return (
      <section className={className} style={{display: dpWindow.get('columnMode') === 'focus' ? 'none' : ''}}>
        <div className="feedback-list">
          {children}
        </div>
      </section>
    );
  }
}
