import React, { PropTypes } from 'react';

export class WidgetContent extends React.Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <div className="widget-container dpdesignportal mobile">
        {this.props.children}
      </div>
    );
  }
}
