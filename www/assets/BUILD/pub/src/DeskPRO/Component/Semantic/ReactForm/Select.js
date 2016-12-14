import React, { PropTypes } from 'react';
import Select from 'react-select';

class SemanticSelect extends React.Component {

  static propTypes = {
    choices:  PropTypes.array,
    onChange: PropTypes.func
  };

  onChange = (item) => {
    this.props.onChange(item && item.value);
  };

  render() {
    const { choices } = this.props;

    return (
      <Select {...this.props} onChange={this.onChange} options={choices} />
    );
  }
}

export default SemanticSelect;
