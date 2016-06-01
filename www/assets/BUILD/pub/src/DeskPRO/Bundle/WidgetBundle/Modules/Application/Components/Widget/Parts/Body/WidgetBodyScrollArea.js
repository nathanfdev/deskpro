import React, { PropTypes } from 'react';
import ScrollArea from 'react-scrollbar-iframe';

export class WidgetBodyScrollArea extends React.Component {

  static propTypes = {
    height:   PropTypes.number,
    children: PropTypes.any
  };

  componentDidUpdate() {
    const scrollArea = this.refs.scrollArea;
    if (scrollArea) {
      scrollArea.setSizesToState();
      scrollArea.handleWindowResize();
    }
  }

  render() {
    const { height, children } = this.props;

    return (
      <ScrollArea
        vertical
        ref="scrollArea"
        ownerDocument={window.widgetFrame.document}
        style={{ height }}
      >
        {children}
      </ScrollArea>
    );
  }
}
