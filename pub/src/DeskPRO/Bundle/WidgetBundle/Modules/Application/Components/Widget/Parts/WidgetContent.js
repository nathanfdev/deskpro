import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class WidgetContent extends React.Component {

  static propTypes = {
    widgetType: PropTypes.string,
    children: PropTypes.any
  };

  render() {
    const { widgetType, children } = this.props;

    return (
      <div className={classNames(
        'widget-container',
        'dpdesignportal', {
          'chat-bubble': widgetType === 'bubble',
          'mobile': widgetType !== 'bubble'
        })}>

        {children}
      </div>
    );
  }
}
