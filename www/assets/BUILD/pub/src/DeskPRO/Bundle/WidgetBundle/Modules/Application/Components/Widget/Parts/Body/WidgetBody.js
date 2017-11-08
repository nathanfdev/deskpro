import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import $ from 'jquery';

export class WidgetBody extends React.Component {

  static propTypes = {
    isBubble: PropTypes.bool,
    children: PropTypes.any // eslint-disable-line react/forbid-prop-types
  };

  render() {
    const { isBubble, children } = this.props;
    const bodyStyles = {};

    if (isBubble) {
      bodyStyles.height = $(parent.window).height() / 2;
      if (bodyStyles.height > 550) {
        bodyStyles.height = 550;
      }
      if (bodyStyles.height < 300) {
        bodyStyles.height = 350;
      }
    }

    return (
      <div
        style={bodyStyles}
        className={classNames('dpdesignportal-widget-body', { 'chat-bubble-content': isBubble })}
      >
        {children}
      </div>
    );
  }
}
