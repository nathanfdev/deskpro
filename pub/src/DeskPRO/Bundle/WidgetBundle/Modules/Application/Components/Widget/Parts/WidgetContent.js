import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class WidgetContent extends React.Component {

  static propTypes = {
    isBubble: PropTypes.bool,
    widgetPosition: PropTypes.string,
    children: PropTypes.any
  };

  render() {
    const { isBubble, widgetPosition, children } = this.props;

    return (
      <div className={classNames(
        'widget-container',
        'dpdesignportal', {
          'chat-bubble': isBubble,
          'mobile': !isBubble,
          'position-left': widgetPosition
        })}>

        {children}
      </div>
    );
  }
}
