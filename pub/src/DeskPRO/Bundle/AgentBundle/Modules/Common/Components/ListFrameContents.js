import React from 'react';
import { Scrollable } from './Scrollable';
import jQuery from 'jquery';

export default class ListFrameContents extends React.Component {

  render() {
    return (
      <Scrollable vertical style={{height: this.getHeight() + 'px'}}>
        <div className="dp-list-frame-contents" {...this.props} />
      </Scrollable>
    );
  }

  // Scrollable area height equal to the window height reduced
  // by height of the list control bar and header top bar
  getHeight() {
    return jQuery(window).height() - 93;
  }
}
