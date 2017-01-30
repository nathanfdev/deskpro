import React, { PropTypes } from 'react';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';

class ArchivePopUp extends React.Component {
  static propTypes = {
    authId:   PropTypes.string,
    text:     PropTypes.string,
    children: PropTypes.node
  };

  render() {
    return (
      <PopUp
        id={this.props.authId.toInt()}
        positionMy="left top-5px"
        positionAt="left bottom"
        content={this.props.children}
        opened
        zIndex={100}
      >
        <a>{this.props.text}</a>
      </PopUp>
    );
  }
}
export default ArchivePopUp;
