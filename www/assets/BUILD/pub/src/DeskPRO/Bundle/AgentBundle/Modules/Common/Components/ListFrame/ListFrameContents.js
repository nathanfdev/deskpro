import PropTypes from 'prop-types';
import React from 'react';
import { Scrollable } from '../Scrollable';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';

export class ListFrameContents extends React.Component {
  static propTypes = {
    children: PropTypes.node,
    isLoaded: PropTypes.bool.isRequired
  };

  render() {
    const isLoaded = this.props.isLoaded === !!this.props.isLoaded
                   ? this.props.isLoaded
                   : true;

    return (
      <LoadIndicator loaded={isLoaded} opacity={0} width={3} top="100px">
        <div className="dp-list-frame-contents">
          <Scrollable vertical>
            {this.props.children}
          </Scrollable>
        </div>
      </LoadIndicator>
    );
  }
}
