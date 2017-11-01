import PropTypes from 'prop-types';
import React from 'react';
import Select from 'react-select';

class SemanticSelect extends React.Component {

  static propTypes = {
    addNewLabel: PropTypes.string,
    onAddNew:    PropTypes.func,
    choices:     PropTypes.array,
    onChange:    PropTypes.func
  };

  onChange = (item) => {
    const { onAddNew, onChange } = this.props;

    if (item && item.addNew) {
      onAddNew();
    } else {
      onChange(item && item.value);
    }
  };

  render() {
    const { choices, addNewLabel, onAddNew } = this.props;

    const newChoices = [...choices];
    if (onAddNew) {
      newChoices.unshift({
        label:  addNewLabel || 'Add new',
        value:  null,
        addNew: true
      });
    }

    return (
      <Select
        {...this.props}
        onChange={this.onChange}
        options={newChoices}
      />
    );
  }
}

export default SemanticSelect;
