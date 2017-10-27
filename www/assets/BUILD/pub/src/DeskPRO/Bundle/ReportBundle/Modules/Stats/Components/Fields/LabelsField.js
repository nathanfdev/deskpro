import React, { PropTypes } from 'react';

import { TagInput } from '@deskpro/react-components';

class LabelsField extends React.Component {

  static propTypes = {
    value:    PropTypes.array,
    onChange: PropTypes.func,
    labels:   PropTypes.object.isRequired,
  };

  render() {
    const { value, onChange, labels } = this.props;

    return (<TagInput
      tags={value}
      onChange={onChange}
      options={labels.map(label => label.get('label')).toJS()}
      inputProps={{ placeholder: 'Pick up a label' }}
      editable
    />);
  }
}

export default LabelsField;
