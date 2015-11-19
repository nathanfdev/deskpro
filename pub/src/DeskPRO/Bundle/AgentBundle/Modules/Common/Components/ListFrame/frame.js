import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import ReactDOM from 'react-dom';
import jQuery from 'jquery';
import { debounce } from 'lodash';
import classNames from 'classnames';

@connect((state) => ({
  dpWindow: state.Application.dpWindow
}))
export class ListFrameContainer extends React.Component {

  static propTypes = {
    dpWindow: PropTypes.object.isRequired,
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
    const navWidth = Math.ceil(totalWidth * this.props.dpWindow.get('columnDimensions') / 100);
    jQuery(ReactDOM.findDOMNode(this)).width(navWidth);
  };

  render() {
    const { className, children } = this.props;

    return (
      <section className={classNames(className, 'dp-list-frame')}>
        <div className="feedback-list">
          {children}
        </div>
      </section>
    );
  }
}
