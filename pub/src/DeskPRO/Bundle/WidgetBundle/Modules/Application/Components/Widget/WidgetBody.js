import React, { PropTypes } from 'react';

export class WidgetBody extends React.Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <div className="widget-body">
        {this.props.children}
      </div>
    );
  }
}
