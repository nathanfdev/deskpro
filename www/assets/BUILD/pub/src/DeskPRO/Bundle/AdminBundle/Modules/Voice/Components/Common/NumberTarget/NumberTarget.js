import PropTypes from 'prop-types';
import React from 'react';

class NumberTarget extends React.Component {

  static propTypes = {
    id:         PropTypes.number,
    targetName: PropTypes.string,
    targetType: PropTypes.string,
    withType:   PropTypes.bool,
    onRemove:   PropTypes.func
  };

  onRemove = (event) => {
    event.preventDefault();

    const { id, onRemove = () => {} } = this.props;
    onRemove(id);
  };

  render() {
    const { targetName, targetType, withType, onRemove } = this.props;
    if (!targetName) {
      return null;
    }

    return (
      <div className="target">
        {withType && `${targetType}: `}{targetName}
        {onRemove &&
          <a onClick={this.onRemove}>
            <i className="remove circle icon" />
          </a>}
      </div>
    );
  }
}

export default NumberTarget;
