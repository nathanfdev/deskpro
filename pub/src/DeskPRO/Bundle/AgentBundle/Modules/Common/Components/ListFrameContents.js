import React, { PropTypes } from 'react';
import { Scrollable } from './Scrollable';

export default class ListFrameContents extends React.Component {
  static propTypes = {
    children: PropTypes.node
  };

  render() {
    return (
      <div className="dp-list-frame-contents">
        <Scrollable vertical>
          {this.props.children}
        </Scrollable>
      </div>
    );
  }
}
