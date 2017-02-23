import React, { PropTypes } from 'react';
import { Range } from 'DeskPRO/Component/Semantic/Form';

class Volume extends React.Component {

  static propTypes = {
    value:    PropTypes.number,
    onChange: PropTypes.func
  };

  render() {
    const { value, onChange } = this.props;

    return (
      <div className="volume">
        <Range value={value} onChange={onChange} />
      </div>
    );
  }
}

export default Volume;
