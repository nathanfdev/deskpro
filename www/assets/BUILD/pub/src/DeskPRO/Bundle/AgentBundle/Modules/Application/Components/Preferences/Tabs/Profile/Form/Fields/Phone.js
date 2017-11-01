import PropTypes from 'prop-types';
import React from 'react';
import { PhoneNumber } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/PhoneNumber';

export class Phone extends React.Component {

  static propTypes = {
    value:    PropTypes.object,
    onChange: PropTypes.func.isRequired
  };

  render() {
    const { value, onChange } = this.props;

    return (
      <div className="bucket-column">
        <PhoneNumber
          number={value.number}
          extension={value.extension}
          onChange={onChange}
        />
      </div>
    );
  }
}
