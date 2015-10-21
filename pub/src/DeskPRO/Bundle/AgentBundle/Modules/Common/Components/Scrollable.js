import React, { PropTypes } from 'react';
import ScrollArea from 'react-scrollbar';

export class Scrollable extends React.Component {
  static propTypes = {
    children: PropTypes.node,
    horizontal: PropTypes.bool,
    vertical: PropTypes.bool
  };

  render() {
    return (
      <ScrollArea
        style={{height: '100%', width: '100%'}}
        contentStyle={{height: 'auto', width: 'auto'}}
        horizontal={this.props.horizontal || false}
        vertical={this.props.vertical || false}
      >
        {this.props.children}
      </ScrollArea>
    );
  }
}
