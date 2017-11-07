import PropTypes from 'prop-types';
import React from 'react';

class AddAudioAsset extends React.Component {

  static propTypes = {
    onOpen: PropTypes.func
  };

  onClick = (event) => {
    event.preventDefault();
    this.props.onOpen();
  };

  render() {
    return (
      <div>
        <button className="ui basic button" onClick={this.onClick}>
          Choose audio source
        </button>
      </div>
    );
  }
}

export default AddAudioAsset;
