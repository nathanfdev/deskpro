import React, { PropTypes } from 'react';

export class Checkbox extends React.Component {

  static propTypes = {
    selected: PropTypes.bool,
    onToggle: PropTypes.func.isRequired
  };

  render() {
    const { selected, onToggle } = this.props;

    return (
      <div className="dpm--card-checkbox" onClick={onToggle}>
        {selected && <i className="fa fa-check"/>}
      </div>
    );
  }
}
