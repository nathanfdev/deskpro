import PropTypes from 'prop-types';
import React from 'react';
import ScrollArea from 'react-scrollbar';

export class WidgetBodyScrollArea extends React.Component {

  static propTypes = {
    height:   PropTypes.number,
    children: PropTypes.oneOfType([PropTypes.object, PropTypes.array])
  };

  componentDidUpdate() {
    const scrollArea = this.scrollArea;
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
        ref={(c) => { this.scrollArea = c; }}
        ownerDocument={window.widgetFrame.document}
        style={{ height }}
      >
        {children}
      </ScrollArea>
    );
  }
}

export default WidgetBodyScrollArea;
