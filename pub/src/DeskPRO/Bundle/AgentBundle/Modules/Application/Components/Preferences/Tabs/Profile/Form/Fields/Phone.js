import React, { PropTypes } from 'react';
import { PhoneNumber } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/PhoneNumber';

export class Phone extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  render() {
    const { value, onChange } = this.props;

    return (
      <div className="bucket-column">
        <PhoneNumber value={value} onChange={onChange} />
      </div>
    );
  }
}
