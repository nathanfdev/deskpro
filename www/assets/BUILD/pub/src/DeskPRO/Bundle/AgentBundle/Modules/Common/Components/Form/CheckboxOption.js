import React, {Component, PropTypes} from 'react';

import { connect } from 'react-redux';
@connect()
export class CheckboxOption extends Component {
  static propTypes = {
    label: PropTypes.string.isRequired,
    value: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
    values: PropTypes.array,
    onClick: PropTypes.func.isRequired,
    children: PropTypes.any
  };

  componentWillMount() {
    const { values, value } = this.props;
    this.setState({
      isActive: values && values.indexOf(value) > -1
    });
  }

  componentWillReceiveProps(nextProps) {
    const { values } = nextProps;
    this.setState({
      isActive: values && values.indexOf(nextProps.value) > -1
    });
  }

  render() {
    const {label, value, onClick} = this.props;

    return (
      <li>
        <div className={'dpw--popup-item-box'} onClick={onClick.bind(this, value)}>
          <span className={'dpw--checkbox-boxy'}>
            {this.state.isActive && <i className="fa fa-check"></i>}
          </span>
          <span className="dpw-popup-item-collection-name">
            {label}
          </span>
        </div>
        {this.props.children}
      </li>
    );
  }
}