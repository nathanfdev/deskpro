import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import jQuery from 'jquery';
import { debounce } from 'lodash';

export class ListFrame extends React.Component {
  static propTypes:{
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
    const className = this.props.className || 'feedback-list-frame dp-list-frame';
    return (
      <section className={className}>
        <div className="feedback-list">
          {this.props.children}
        </div>
      </section>
    );
  }
}
