import React, { PropTypes } from 'react';
import { PhoneNumber } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/PhoneNumber';

export class Phone extends React.Component {

  static propTypes = {
    number: PropTypes.string,
    extension: PropTypes.string,
    onChangeNumber: PropTypes.func.isRequired,
    onChangeExtension: PropTypes.func.isRequired
  };

  render() {
    const { number, extension, onChangeNumber, onChangeExtension } = this.props;

    return (
      <div className="bucket-column">
        <PhoneNumber number={number}
                     extension={extension}
                     onChangeNumber={onChangeNumber}
                     onChangeExtension={onChangeExtension}/>
      </div>
    );
  }
}
