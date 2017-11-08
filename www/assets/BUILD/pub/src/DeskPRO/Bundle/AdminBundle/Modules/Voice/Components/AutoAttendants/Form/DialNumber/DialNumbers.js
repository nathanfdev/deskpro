import PropTypes from 'prop-types';
import React from 'react';
import range from 'lodash/range';
import { Field } from 'DeskPRO/Component/Semantic/ReactForm';
import NumberTargetSelect from '../../../Common/NumberTarget/NumberTargetSelect';

class DialNumbers extends React.Component {

  static propTypes = {
    value:         PropTypes.array,
    onChange:      PropTypes.func,
    autoAttendant: PropTypes.object
  };

  onChange = (fieldValue, select) => {
    if (fieldValue && fieldValue.type) {
      return;
    }

    const { value, onChange } = this.props;
    if (value && value[select]) {
      delete value[select];
    }

    onChange(value);
  };

  render() {
    const { autoAttendant } = this.props;

    return (
      <div>
        {range(1, 10).map((dialNum, index) =>
          <div className="dial-number-target" key={index}>
            <div className="dial-number">
              {dialNum}
            </div>
            <Field select={String(dialNum)} onChangeCallback={this.onChange}>
              <NumberTargetSelect excludeAutoAttendant={autoAttendant} />
            </Field>
          </div>
        )}
      </div>
    );
  }
}

export default DialNumbers;
