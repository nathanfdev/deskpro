import React from 'react';
import { Range } from 'DeskPRO/Component/Semantic/Form';

class Volume extends React.Component {

  render() {
    return (
      <div className="volume">
        <Range {...this.props} />
      </div>
    );
  }
}

export default Volume;
