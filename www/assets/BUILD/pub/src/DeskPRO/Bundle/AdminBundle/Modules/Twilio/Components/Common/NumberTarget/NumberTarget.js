import React, { PropTypes } from 'react';

class NumberTarget extends React.Component {

  static propTypes = {
    id:       PropTypes.number,
    name:     PropTypes.string,
    onRemove: PropTypes.func
  };

  onRemove = (event) => {
    event.preventDefault();

    const { id, onRemove = () => {} } = this.props;
    onRemove(id);
  };

  render() {
    const { name, onRemove } = this.props;

    return (
      <div className="target">
        {name}
        {onRemove &&
          <a onClick={this.onRemove}>
            <i className="remove circle icon" />
          </a>}
      </div>
    );
  }
}

export default NumberTarget;
