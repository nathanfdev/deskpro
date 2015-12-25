import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class WidgetBody extends React.Component {

  static propTypes = {
    isBubble: PropTypes.bool,
    children: PropTypes.any
  };

  render() {
    const { isBubble, children } = this.props;
    const bodyStyles = {};
    if (isBubble) {
      bodyStyles.height = 500;
      bodyStyles.paddingBottom = 37;
    }

    return (
      <div style={bodyStyles}
           className={classNames('dpdesignportal-widget-body', {'chat-bubble-content': isBubble})}>

        {children}
      </div>
    );
  }
}
