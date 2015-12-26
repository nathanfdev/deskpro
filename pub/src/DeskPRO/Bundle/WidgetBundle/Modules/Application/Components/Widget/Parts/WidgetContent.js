import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class WidgetContent extends React.Component {

  static propTypes = {
    isBubble: PropTypes.bool,
    children: PropTypes.any
  };

  render() {
    const { isBubble, children } = this.props;

    return (
      <div className={classNames(
        'widget-container',
        'dpdesignportal', {
          'chat-bubble': isBubble,
          'mobile': !isBubble
        })}>

        {children}
      </div>
    );
  }
}
